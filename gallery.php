<?php
session_start();
include 'config/database.php';
include 'includes/functions.php';

// Get categories for filter
$categories = getAllCategories($conn);

// Get current category filter
$current_category = isset($_GET['category']) ? (int)$_GET['category'] : null;

// Get photos based on category
if ($current_category) {
    $photos = getPhotosByCategory($conn, $current_category);
    $page_title = "Gallery - " . getCategoryById($conn, $current_category)['name'];
} else {
    $photos = getAllPhotos($conn, 0, 12);
    $page_title = "Photo Gallery";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - PhotoLens</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/lightbox.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="index.php">PhotoLens</a>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="gallery.php" class="active">Gallery</a></li>
                <li><a href="services.php">Services</a></li>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="blog.php">Blog</a></li>
                <li><a href="contact.php">Contact</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="page-header-content">
                <h1><?php echo $page_title; ?></h1>
                <p>Explore our collection of stunning photography from various events and sessions</p>
            </div>
        </div>
    </section>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <ul class="breadcrumb-list">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item">Gallery</li>
                <?php if ($current_category): ?>
                    <li class="breadcrumb-item"><?php echo getCategoryById($conn, $current_category)['name']; ?></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Gallery Section -->
    <section class="gallery-section">
        <div class="container">
            <!-- Search and Filter -->
            <div class="gallery-controls">
                <div class="search-bar">
                    <input type="text" class="search-input" placeholder="Search photos...">
                    <button type="button"><i class="fas fa-search"></i></button>
                    <div class="search-results"></div>
                </div>
                
                <div class="filter-controls">
                    <button class="filter-btn <?php echo !$current_category ? 'active' : ''; ?>" data-filter="all">
                        All Photos
                    </button>
                    <?php foreach ($categories as $category): ?>
                        <button class="filter-btn <?php echo $current_category == $category['id'] ? 'active' : ''; ?>" 
                                data-filter="<?php echo $category['id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Gallery Grid -->
            <div class="gallery-grid" id="gallery-grid">
                <?php foreach ($photos as $photo): ?>
                    <div class="gallery-item" data-category="<?php echo $photo['category_id']; ?>">
                        <img src="<?php echo htmlspecialchars($photo['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($photo['title']); ?>"
                             loading="lazy">
                        <div class="gallery-overlay">
                            <div class="gallery-info">
                                <h4><?php echo htmlspecialchars($photo['title']); ?></h4>
                                <p><?php echo htmlspecialchars($photo['category']); ?></p>
                                <span class="photo-date"><?php echo formatDate($photo['created_at']); ?></span>
                            </div>
                            <div class="gallery-actions">
                                <a href="<?php echo htmlspecialchars($photo['image_path']); ?>" 
                                   class="gallery-link" 
                                   data-lightbox="gallery"
                                   data-caption="<?php echo htmlspecialchars($photo['title']); ?>">
                                    <i class="fas fa-expand"></i>
                                </a>
                                <a href="photo-details.php?id=<?php echo $photo['id']; ?>" 
                                   class="gallery-link">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <button class="gallery-link favorite-btn" 
                                            data-photo-id="<?php echo $photo['id']; ?>"
                                            data-tooltip="Add to favorites">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Load More Button -->
            <div class="gallery-footer">
                <button class="btn btn-primary load-more-btn" data-offset="12">
                    Load More Photos
                </button>
            </div>
        </div>
    </section>

    <!-- Gallery Stats -->
    <section class="gallery-stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">
                        <?php
                        $stmt = $conn->prepare("SELECT COUNT(*) FROM photos");
                        $stmt->execute();
                        echo $stmt->fetchColumn();
                        ?>
                    </div>
                    <div class="stat-label">Total Photos</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">
                        <?php echo count($categories); ?>
                    </div>
                    <div class="stat-label">Categories</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">
                        <?php
                        $stmt = $conn->prepare("SELECT COUNT(DISTINCT YEAR(created_at)) FROM photos");
                        $stmt->execute();
                        echo $stmt->fetchColumn();
                        ?>
                    </div>
                    <div class="stat-label">Years Active</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Happy Clients</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Showcase -->
    <section class="categories-showcase">
        <div class="container">
            <h2>Explore by Category</h2>
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                    <?php
                    $category_photos = getPhotosByCategory($conn, $category['id'], 1);
                    $sample_photo = !empty($category_photos) ? $category_photos[0] : null;
                    ?>
                    <div class="category-card">
                        <div class="category-image">
                            <?php if ($sample_photo): ?>
                                <img src="<?php echo htmlspecialchars($sample_photo['image_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($category['name']); ?>">
                            <?php else: ?>
                                <div class="category-placeholder">
                                    <i class="fas fa-camera"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="category-info">
                            <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                            <p><?php echo htmlspecialchars($category['description']); ?></p>
                            <div class="category-stats">
                                <?php
                                $stmt = $conn->prepare("SELECT COUNT(*) FROM photos WHERE category_id = ?");
                                $stmt->execute([$category['id']]);
                                $photo_count = $stmt->fetchColumn();
                                ?>
                                <span><?php echo $photo_count; ?> Photos</span>
                            </div>
                            <a href="gallery.php?category=<?php echo $category['id']; ?>" 
                               class="btn btn-outline">View Category</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Newsletter Subscription -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-content">
                <h2>Stay Updated</h2>
                <p>Subscribe to our newsletter to get notified about new photos and updates</p>
                <form class="newsletter-form">
                    <div class="newsletter-input">
                        <input type="email" placeholder="Enter your email address" required>
                        <button type="submit">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>PhotoLens</h3>
                    <p>Professional photography services for all your special moments.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="about.php">About</a></li>
                        <li><a href="gallery.php">Gallery</a></li>
                        <li><a href="services.php">Services</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Services</h4>
                    <ul>
                        <li><a href="services.php#weddings">Weddings</a></li>
                        <li><a href="services.php#portraits">Portraits</a></li>
                        <li><a href="services.php#events">Events</a></li>
                        <li><a href="services.php#commercial">Commercial</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <p><i class="fas fa-phone"></i> (555) 123-4567</p>
                    <p><i class="fas fa-envelope"></i> info@photolens.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Photography St, City, State 12345</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 PhotoLens. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/lightbox.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/gallery.js"></script>

    <style>
        /* Gallery-specific styles */
        .page-header {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 120px 0 80px;
            text-align: center;
        }

        .page-header h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .gallery-section {
            padding: 80px 0;
        }

        .gallery-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            flex-wrap: wrap;
            gap: 2rem;
        }

        .search-bar {
            flex: 1;
            max-width: 400px;
            position: relative;
        }

        .filter-controls {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 0.5rem 1rem;
            border: 2px solid #e74c3c;
            background: transparent;
            color: #e74c3c;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: #e74c3c;
            color: white;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            aspect-ratio: 4/3;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .gallery-item:hover {
            transform: translateY(-5px);
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(0,0,0,0.3), rgba(0,0,0,0.8));
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1.5rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }

        .gallery-item:hover img {
            transform: scale(1.05);
        }

        .gallery-info {
            color: white;
        }

        .gallery-info h4 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .gallery-info p {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 0.25rem;
        }

        .photo-date {
            font-size: 0.8rem;
            opacity: 0.7;
        }

        .gallery-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .gallery-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 50%;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .gallery-link:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .gallery-footer {
            text-align: center;
        }

        .gallery-stats {
            background: #f8f9fa;
            padding: 60px 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            text-align: center;
        }

        .stat-item {
            padding: 1.5rem;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #e74c3c;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.1rem;
            color: #666;
        }

        .categories-showcase {
            padding: 80px 0;
        }

        .categories-showcase h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            color: #2c3e50;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .category-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .category-card:hover {
            transform: translateY(-5px);
        }

        .category-image {
            height: 200px;
            overflow: hidden;
        }

        .category-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .category-card:hover .category-image img {
            transform: scale(1.05);
        }

        .category-placeholder {
            width: 100%;
            height: 100%;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ccc;
            font-size: 3rem;
        }

        .category-info {
            padding: 1.5rem;
        }

        .category-info h3 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        .category-info p {
            color: #666;
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .category-stats {
            margin-bottom: 1rem;
        }

        .category-stats span {
            background: #e74c3c;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.875rem;
        }

        .newsletter-section {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        .newsletter-content h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .newsletter-content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }

        .newsletter-input {
            display: flex;
            max-width: 500px;
            margin: 0 auto;
            gap: 1rem;
        }

        .newsletter-input input {
            flex: 1;
            padding: 12px 15px;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
        }

        .newsletter-input button {
            padding: 12px 30px;
            background: white;
            color: #e74c3c;
            border: none;
            border-radius: 25px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .newsletter-input button:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .gallery-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .search-bar {
                max-width: none;
            }

            .filter-controls {
                justify-content: center;
            }

            .gallery-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .categories-grid {
                grid-template-columns: 1fr;
            }

            .newsletter-input {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>