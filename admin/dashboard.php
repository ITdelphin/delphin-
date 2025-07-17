<?php
session_start();
include '../config/database.php';
include '../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Handle photo upload
if (isset($_POST['upload_photo'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $image_path = uploadImage($_FILES['photo'], '../uploads/');
        if ($image_path) {
            $sql = "INSERT INTO photos (title, description, image_path, category_id, is_featured) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute([$title, $description, $image_path, $category_id, $is_featured])) {
                $success_message = 'Photo uploaded successfully!';
            } else {
                $error_message = 'Error uploading photo to database.';
            }
        } else {
            $error_message = 'Error uploading image file.';
        }
    }
}

// Handle photo deletion
if (isset($_POST['delete_photo'])) {
    $photo_id = $_POST['photo_id'];
    
    // Get photo info first
    $stmt = $conn->prepare("SELECT image_path FROM photos WHERE id = ?");
    $stmt->execute([$photo_id]);
    $photo = $stmt->fetch();
    
    if ($photo) {
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM photos WHERE id = ?");
        if ($stmt->execute([$photo_id])) {
            // Delete file
            if (file_exists('../' . $photo['image_path'])) {
                unlink('../' . $photo['image_path']);
            }
            $success_message = 'Photo deleted successfully!';
        } else {
            $error_message = 'Error deleting photo.';
        }
    }
}

// Handle booking status update
if (isset($_POST['update_booking_status'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];
    
    if (updateBookingStatus($conn, $booking_id, $status)) {
        $success_message = 'Booking status updated successfully!';
    } else {
        $error_message = 'Error updating booking status.';
    }
}

// Get statistics
$stats = [
    'total_photos' => $conn->query("SELECT COUNT(*) FROM photos")->fetchColumn(),
    'total_bookings' => $conn->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
    'pending_bookings' => $conn->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn(),
    'total_messages' => $conn->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn(),
    'unread_messages' => $conn->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn()
];

// Get recent data
$recent_photos = getAllPhotos($conn, 0, 5);
$recent_bookings = getBookings($conn);
$recent_messages = getContactMessages($conn, false);
$categories = getAllCategories($conn);

// Limit recent data
$recent_bookings = array_slice($recent_bookings, 0, 5);
$recent_messages = array_slice($recent_messages, 0, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PhotoLens</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Admin Navigation -->
    <nav class="admin-navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="dashboard.php">PhotoLens Admin</a>
            </div>
            <ul class="nav-menu">
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="photos.php">Photos</a></li>
                <li><a href="bookings.php">Bookings</a></li>
                <li><a href="messages.php">Messages</a></li>
                <li><a href="settings.php">Settings</a></li>
                <li><a href="../index.php">View Site</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="admin-container">
        <!-- Page Header -->
        <div class="admin-header">
            <h1>Dashboard</h1>
            <p>Welcome back! Here's what's happening with your photography business.</p>
        </div>

        <!-- Alerts -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-images"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_photos']; ?></h3>
                    <p>Total Photos</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_bookings']; ?></h3>
                    <p>Total Bookings</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['pending_bookings']; ?></h3>
                    <p>Pending Bookings</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['unread_messages']; ?></h3>
                    <p>Unread Messages</p>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="admin-content-grid">
            <!-- Photo Upload Section -->
            <div class="admin-section">
                <div class="section-header">
                    <h2>Upload New Photo</h2>
                    <p>Add a new photo to your gallery</p>
                </div>
                <form method="POST" enctype="multipart/form-data" class="upload-form">
                    <div class="form-group">
                        <label for="title">Photo Title</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_featured">
                            <span>Featured Photo</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label for="photo">Photo File</label>
                        <div class="upload-area">
                            <input type="file" id="photo" name="photo" accept="image/*" required>
                            <div class="upload-preview"></div>
                            <div class="upload-text">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click to upload or drag and drop</p>
                                <span>JPG, PNG, GIF up to 5MB</span>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="upload_photo" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload Photo
                    </button>
                </form>
            </div>

            <!-- Recent Photos -->
            <div class="admin-section">
                <div class="section-header">
                    <h2>Recent Photos</h2>
                    <a href="photos.php" class="btn btn-outline">View All</a>
                </div>
                <div class="photos-grid">
                    <?php foreach ($recent_photos as $photo): ?>
                        <div class="photo-item">
                            <img src="../<?php echo htmlspecialchars($photo['image_path']); ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>">
                            <div class="photo-info">
                                <h4><?php echo htmlspecialchars($photo['title']); ?></h4>
                                <p><?php echo htmlspecialchars($photo['category']); ?></p>
                                <div class="photo-actions">
                                    <button class="btn-icon" onclick="editPhoto(<?php echo $photo['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this photo?')">
                                        <input type="hidden" name="photo_id" value="<?php echo $photo['id']; ?>">
                                        <button type="submit" name="delete_photo" class="btn-icon btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="admin-section">
                <div class="section-header">
                    <h2>Recent Bookings</h2>
                    <a href="bookings.php" class="btn btn-outline">View All</a>
                </div>
                <div class="bookings-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Event Type</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_bookings as $booking): ?>
                                <tr>
                                    <td>
                                        <div class="client-info">
                                            <strong><?php echo htmlspecialchars($booking['client_name']); ?></strong>
                                            <span><?php echo htmlspecialchars($booking['client_email']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($booking['event_type']); ?></td>
                                    <td><?php echo formatDate($booking['event_date']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $booking['status']; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <select name="status" onchange="this.form.submit()">
                                                <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                            <input type="hidden" name="update_booking_status" value="1">
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Messages -->
            <div class="admin-section">
                <div class="section-header">
                    <h2>Recent Messages</h2>
                    <a href="messages.php" class="btn btn-outline">View All</a>
                </div>
                <div class="messages-list">
                    <?php foreach ($recent_messages as $message): ?>
                        <div class="message-item <?php echo $message['is_read'] ? '' : 'unread'; ?>">
                            <div class="message-header">
                                <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                                <span class="message-date"><?php echo formatDateTime($message['created_at']); ?></span>
                            </div>
                            <div class="message-subject">
                                <?php echo htmlspecialchars($message['subject']); ?>
                            </div>
                            <div class="message-preview">
                                <?php echo truncateText($message['message'], 100); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="admin-section">
                <div class="section-header">
                    <h2>Quick Actions</h2>
                </div>
                <div class="quick-actions">
                    <a href="photos.php" class="action-btn">
                        <i class="fas fa-images"></i>
                        <span>Manage Photos</span>
                    </a>
                    <a href="bookings.php" class="action-btn">
                        <i class="fas fa-calendar"></i>
                        <span>View Bookings</span>
                    </a>
                    <a href="messages.php" class="action-btn">
                        <i class="fas fa-envelope"></i>
                        <span>Read Messages</span>
                    </a>
                    <a href="settings.php" class="action-btn">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Edit photo function
        function editPhoto(photoId) {
            // You can implement a modal or redirect to edit page
            window.location.href = `edit-photo.php?id=${photoId}`;
        }

        // Auto-refresh for real-time updates
        setInterval(function() {
            // Refresh unread message count
            fetch('ajax/get-unread-count.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector('.stat-card:last-child .stat-content h3').textContent = data.count;
                    }
                });
        }, 30000); // Refresh every 30 seconds
    </script>

    <style>
        /* Admin-specific styles */
        .admin-navbar {
            background: #2c3e50;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .admin-navbar .nav-logo a {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .admin-navbar .nav-menu a {
            color: #bdc3c7;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .admin-navbar .nav-menu a:hover,
        .admin-navbar .nav-menu a.active {
            background: #34495e;
            color: white;
        }

        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .admin-header {
            margin-bottom: 2rem;
        }

        .admin-header h1 {
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .admin-header p {
            color: #666;
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            background: #e74c3c;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-content h3 {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 0.25rem;
        }

        .stat-content p {
            color: #666;
            font-size: 0.9rem;
        }

        .admin-content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
        }

        .admin-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        .section-header h2 {
            color: #2c3e50;
            font-size: 1.5rem;
        }

        .upload-form .form-group {
            margin-bottom: 1.5rem;
        }

        .upload-area {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .upload-area:hover {
            border-color: #e74c3c;
            background-color: #f8f9fa;
        }

        .upload-area input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .upload-text i {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 1rem;
        }

        .upload-text p {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 0.5rem;
        }

        .upload-text span {
            font-size: 0.9rem;
            color: #999;
        }

        .photos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }

        .photo-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .photo-item img {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }

        .photo-info {
            padding: 0.75rem;
            background: white;
        }

        .photo-info h4 {
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
            color: #2c3e50;
        }

        .photo-info p {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 0.5rem;
        }

        .photo-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-icon {
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 3px;
            transition: all 0.3s ease;
        }

        .btn-icon:hover {
            background: #f8f9fa;
            color: #2c3e50;
        }

        .btn-icon.btn-danger:hover {
            background: #e74c3c;
            color: white;
        }

        .bookings-table {
            overflow-x: auto;
        }

        .bookings-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .bookings-table th,
        .bookings-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .bookings-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }

        .client-info {
            display: flex;
            flex-direction: column;
        }

        .client-info strong {
            color: #2c3e50;
            margin-bottom: 0.25rem;
        }

        .client-info span {
            font-size: 0.9rem;
            color: #666;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .messages-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .message-item {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .message-item:hover {
            background: #f8f9fa;
        }

        .message-item.unread {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .message-header strong {
            color: #2c3e50;
        }

        .message-date {
            font-size: 0.8rem;
            color: #666;
        }

        .message-subject {
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .message-preview {
            font-size: 0.9rem;
            color: #666;
            line-height: 1.4;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 8px;
            text-decoration: none;
            color: #2c3e50;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background: #e74c3c;
            color: white;
            transform: translateY(-2px);
        }

        .action-btn i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .action-btn span {
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .admin-content-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>