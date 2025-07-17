<?php
// Helper functions for the photography website

// Photo functions
function getFeaturedPhotos($conn, $limit = 3) {
    $sql = "SELECT p.*, c.name as category 
            FROM photos p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.is_featured = 1 
            ORDER BY p.created_at DESC 
            LIMIT :limit";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getRecentPhotos($conn, $limit = 6) {
    $sql = "SELECT p.*, c.name as category 
            FROM photos p 
            LEFT JOIN categories c ON p.category_id = c.id 
            ORDER BY p.created_at DESC 
            LIMIT :limit";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getPhotosByCategory($conn, $category_id, $limit = null) {
    $sql = "SELECT p.*, c.name as category 
            FROM photos p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.category_id = :category_id 
            ORDER BY p.created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT :limit";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    
    if ($limit) {
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    return $stmt->fetchAll();
}

function getAllPhotos($conn, $offset = 0, $limit = 12) {
    $sql = "SELECT p.*, c.name as category 
            FROM photos p 
            LEFT JOIN categories c ON p.category_id = c.id 
            ORDER BY p.created_at DESC 
            LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getPhotoById($conn, $id) {
    $sql = "SELECT p.*, c.name as category 
            FROM photos p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

// Category functions
function getAllCategories($conn) {
    $sql = "SELECT * FROM categories ORDER BY name";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getCategoryById($conn, $id) {
    $sql = "SELECT * FROM categories WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

// Testimonial functions
function getTestimonials($conn, $approved_only = true) {
    $sql = "SELECT * FROM testimonials";
    if ($approved_only) {
        $sql .= " WHERE is_approved = 1";
    }
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

function addTestimonial($conn, $client_name, $event_type, $content) {
    $sql = "INSERT INTO testimonials (client_name, event_type, content) VALUES (:client_name, :event_type, :content)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':client_name', $client_name);
    $stmt->bindParam(':event_type', $event_type);
    $stmt->bindParam(':content', $content);
    return $stmt->execute();
}

// Booking functions
function createBooking($conn, $data) {
    $sql = "INSERT INTO bookings (client_name, client_email, client_phone, event_type, event_date, event_time, location, message) 
            VALUES (:client_name, :client_email, :client_phone, :event_type, :event_date, :event_time, :location, :message)";
    $stmt = $conn->prepare($sql);
    return $stmt->execute($data);
}

function getBookings($conn, $status = null) {
    $sql = "SELECT * FROM bookings";
    if ($status) {
        $sql .= " WHERE status = :status";
    }
    $sql .= " ORDER BY event_date DESC";
    
    $stmt = $conn->prepare($sql);
    if ($status) {
        $stmt->bindParam(':status', $status);
    }
    $stmt->execute();
    return $stmt->fetchAll();
}

function updateBookingStatus($conn, $id, $status) {
    $sql = "UPDATE bookings SET status = :status WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    return $stmt->execute();
}

// Contact functions
function saveContactMessage($conn, $name, $email, $subject, $message) {
    $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES (:name, :email, :subject, :message)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':subject', $subject);
    $stmt->bindParam(':message', $message);
    return $stmt->execute();
}

function getContactMessages($conn, $unread_only = false) {
    $sql = "SELECT * FROM contact_messages";
    if ($unread_only) {
        $sql .= " WHERE is_read = 0";
    }
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

// User functions
function createUser($conn, $username, $email, $password, $role = 'user') {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':role', $role);
    return $stmt->execute();
}

function authenticateUser($conn, $username, $password) {
    $sql = "SELECT * FROM users WHERE username = :username OR email = :username";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

function getUserById($conn, $id) {
    $sql = "SELECT * FROM users WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

// Blog functions
function getBlogPosts($conn, $published_only = true, $limit = null) {
    $sql = "SELECT b.*, u.username as author 
            FROM blog_posts b 
            LEFT JOIN users u ON b.author_id = u.id";
    
    if ($published_only) {
        $sql .= " WHERE b.status = 'published'";
    }
    
    $sql .= " ORDER BY b.created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT :limit";
    }
    
    $stmt = $conn->prepare($sql);
    if ($limit) {
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    }
    $stmt->execute();
    return $stmt->fetchAll();
}

function getBlogPostById($conn, $id) {
    $sql = "SELECT b.*, u.username as author 
            FROM blog_posts b 
            LEFT JOIN users u ON b.author_id = u.id 
            WHERE b.id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

// Product functions (for shop)
function getProducts($conn, $category = null, $limit = null) {
    $sql = "SELECT * FROM products";
    
    if ($category) {
        $sql .= " WHERE category = :category";
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT :limit";
    }
    
    $stmt = $conn->prepare($sql);
    if ($category) {
        $stmt->bindParam(':category', $category);
    }
    if ($limit) {
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    }
    $stmt->execute();
    return $stmt->fetchAll();
}

function getProductById($conn, $id) {
    $sql = "SELECT * FROM products WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

// Utility functions
function uploadImage($file, $upload_dir = 'uploads/') {
    $target_dir = $upload_dir;
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Check if image file is actual image
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        return false;
    }
    
    // Check file size (5MB limit)
    if ($file['size'] > 5000000) {
        return false;
    }
    
    // Allow certain file formats
    $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
    if (!in_array($file_extension, $allowed_extensions)) {
        return false;
    }
    
    // Create upload directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return $target_file;
    } else {
        return false;
    }
}

function resizeImage($source, $destination, $width, $height) {
    $info = getimagesize($source);
    $mime = $info['mime'];
    
    switch ($mime) {
        case 'image/jpeg':
            $image_create_func = 'imagecreatefromjpeg';
            $image_save_func = 'imagejpeg';
            break;
        case 'image/png':
            $image_create_func = 'imagecreatefrompng';
            $image_save_func = 'imagepng';
            break;
        case 'image/gif':
            $image_create_func = 'imagecreatefromgif';
            $image_save_func = 'imagegif';
            break;
        default:
            return false;
    }
    
    $image = $image_create_func($source);
    $new_image = imagecreatetruecolor($width, $height);
    
    imagecopyresampled($new_image, $image, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);
    
    $image_save_func($new_image, $destination);
    
    imagedestroy($image);
    imagedestroy($new_image);
    
    return true;
}

function sendEmail($to, $subject, $message, $from = 'noreply@photolens.com') {
    $headers = "From: $from\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('F j, Y g:i A', strtotime($datetime));
}

function truncateText($text, $length = 100) {
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }
    return $text;
}

function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

// Initialize sample data
function initializeSampleData($conn) {
    // Check if data already exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM categories");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        return; // Data already exists
    }
    
    // Insert sample categories
    $categories = [
        ['name' => 'Weddings', 'description' => 'Wedding photography and ceremonies'],
        ['name' => 'Portraits', 'description' => 'Individual and family portraits'],
        ['name' => 'Events', 'description' => 'Corporate and social events'],
        ['name' => 'Commercial', 'description' => 'Business and product photography']
    ];
    
    foreach ($categories as $category) {
        $sql = "INSERT INTO categories (name, description) VALUES (:name, :description)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($category);
    }
    
    // Insert sample testimonials
    $testimonials = [
        [
            'client_name' => 'Sarah Johnson',
            'event_type' => 'Wedding',
            'content' => 'The photos from our wedding day are absolutely stunning! Every moment was captured perfectly.',
            'is_approved' => 1
        ],
        [
            'client_name' => 'Michael Chen',
            'event_type' => 'Corporate Event',
            'content' => 'Professional service and amazing results. Highly recommend for any business event.',
            'is_approved' => 1
        ],
        [
            'client_name' => 'Emma Wilson',
            'event_type' => 'Family Portrait',
            'content' => 'Beautiful family portraits that we will treasure forever. Thank you for capturing our memories!',
            'is_approved' => 1
        ]
    ];
    
    foreach ($testimonials as $testimonial) {
        $sql = "INSERT INTO testimonials (client_name, event_type, content, is_approved) VALUES (:client_name, :event_type, :content, :is_approved)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($testimonial);
    }
    
    // Create admin user
    createUser($conn, 'admin', 'admin@photolens.com', 'admin123', 'admin');
}

// Initialize sample data when functions are loaded
initializeSampleData($conn);
?>