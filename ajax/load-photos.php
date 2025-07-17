<?php
header('Content-Type: application/json');
session_start();
include '../config/database.php';
include '../includes/functions.php';

$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$limit = 6;
$category = isset($_GET['category']) ? (int)$_GET['category'] : null;

try {
    // Get photos based on category
    if ($category) {
        $sql = "SELECT p.*, c.name as category 
                FROM photos p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.category_id = :category 
                ORDER BY p.created_at DESC 
                LIMIT :limit OFFSET :offset";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':category', $category, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    } else {
        $sql = "SELECT p.*, c.name as category 
                FROM photos p 
                LEFT JOIN categories c ON p.category_id = c.id 
                ORDER BY p.created_at DESC 
                LIMIT :limit OFFSET :offset";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    $photos = $stmt->fetchAll();
    
    // Check if there are more photos
    $total_count_sql = $category ? 
        "SELECT COUNT(*) FROM photos WHERE category_id = :category" :
        "SELECT COUNT(*) FROM photos";
    $count_stmt = $conn->prepare($total_count_sql);
    if ($category) {
        $count_stmt->bindParam(':category', $category, PDO::PARAM_INT);
    }
    $count_stmt->execute();
    $total_count = $count_stmt->fetchColumn();
    
    $has_more = ($offset + $limit) < $total_count;
    
    // Generate HTML for photos
    $html = '';
    foreach ($photos as $photo) {
        $html .= '<div class="gallery-item" data-category="' . htmlspecialchars($photo['category_id']) . '">';
        $html .= '<img src="' . htmlspecialchars($photo['image_path']) . '" alt="' . htmlspecialchars($photo['title']) . '" loading="lazy">';
        $html .= '<div class="gallery-overlay">';
        $html .= '<div class="gallery-info">';
        $html .= '<h4>' . htmlspecialchars($photo['title']) . '</h4>';
        $html .= '<p>' . htmlspecialchars($photo['category']) . '</p>';
        $html .= '<span class="photo-date">' . formatDate($photo['created_at']) . '</span>';
        $html .= '</div>';
        $html .= '<div class="gallery-actions">';
        $html .= '<a href="' . htmlspecialchars($photo['image_path']) . '" class="gallery-link" data-lightbox="gallery" data-caption="' . htmlspecialchars($photo['title']) . '">';
        $html .= '<i class="fas fa-expand"></i>';
        $html .= '</a>';
        $html .= '<a href="photo-details.php?id=' . $photo['id'] . '" class="gallery-link">';
        $html .= '<i class="fas fa-info-circle"></i>';
        $html .= '</a>';
        if (isset($_SESSION['user_id'])) {
            $html .= '<button class="gallery-link favorite-btn" data-photo-id="' . $photo['id'] . '" data-tooltip="Add to favorites">';
            $html .= '<i class="fas fa-heart"></i>';
            $html .= '</button>';
        }
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }
    
    echo json_encode([
        'success' => true,
        'html' => $html,
        'hasMore' => $has_more,
        'total' => $total_count,
        'loaded' => $offset + count($photos)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error loading photos: ' . $e->getMessage()
    ]);
}
?>