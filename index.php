<?php
session_start();
include 'config/database.php';
include 'includes/functions.php';

// Get featured photos for hero section
$featured_photos = getFeaturedPhotos($conn, 3);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PhotoLens - Professional Photography Services</title>
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
                <li><a href="gallery.php">Gallery</a></li>
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-slider">
            <?php foreach ($featured_photos as $index => $photo): ?>
                <div class="hero-slide <?php echo $index === 0 ? 'active' : ''; ?>">
                    <img src="<?php echo htmlspecialchars($photo['image_path']); ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>">
                    <div class="hero-content">
                        <h1><?php echo htmlspecialchars($photo['title']); ?></h1>
                        <p><?php echo htmlspecialchars($photo['description']); ?></p>
                        <a href="gallery.php" class="btn btn-primary">View Gallery</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="hero-nav">
            <button class="prev-slide"><i class="fas fa-chevron-left"></i></button>
            <button class="next-slide"><i class="fas fa-chevron-right"></i></button>
        </div>
    </section>

    <!-- About Preview -->
    <section class="about-preview">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>Capturing Life's Beautiful Moments</h2>
                    <p>Welcome to PhotoLens, where every photograph tells a story. I'm passionate about capturing the essence of life's most precious moments, from intimate portraits to grand celebrations.</p>
                    <p>With over 10 years of experience in professional photography, I specialize in weddings, events, portraits, and commercial photography. My goal is to create timeless images that you'll treasure forever.</p>
                    <a href="about.php" class="btn btn-secondary">Learn More About Me</a>
                </div>
                <div class="about-image">
                    <img src="assets/images/photographer-portrait.jpg" alt="Professional Photographer">
                </div>
            </div>
        </div>
    </section>

    <!-- Services Preview -->
    <section class="services-preview">
        <div class="container">
            <h2>Photography Services</h2>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Wedding Photography</h3>
                    <p>Capture your special day with elegant and timeless wedding photography that tells your unique love story.</p>
                    <a href="services.php#weddings" class="btn btn-outline">Learn More</a>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-camera"></i>
                    </div>
                    <h3>Portrait Sessions</h3>
                    <p>Professional portrait photography for individuals, families, and corporate headshots.</p>
                    <a href="services.php#portraits" class="btn btn-outline">Learn More</a>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3>Event Photography</h3>
                    <p>Document your special events, parties, and corporate functions with professional event photography.</p>
                    <a href="services.php#events" class="btn btn-outline">Learn More</a>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3>Commercial Photography</h3>
                    <p>Professional commercial photography for businesses, products, and marketing materials.</p>
                    <a href="services.php#commercial" class="btn btn-outline">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Preview -->
    <section class="gallery-preview">
        <div class="container">
            <h2>Recent Work</h2>
            <div class="gallery-grid">
                <?php
                $recent_photos = getRecentPhotos($conn, 6);
                foreach ($recent_photos as $photo):
                ?>
                    <div class="gallery-item">
                        <img src="<?php echo htmlspecialchars($photo['image_path']); ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>">
                        <div class="gallery-overlay">
                            <div class="gallery-info">
                                <h4><?php echo htmlspecialchars($photo['title']); ?></h4>
                                <p><?php echo htmlspecialchars($photo['category']); ?></p>
                            </div>
                            <a href="<?php echo htmlspecialchars($photo['image_path']); ?>" class="gallery-link" data-lightbox="gallery">
                                <i class="fas fa-expand"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="gallery-cta">
                <a href="gallery.php" class="btn btn-primary">View Full Gallery</a>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials">
        <div class="container">
            <h2>What Clients Say</h2>
            <div class="testimonial-slider">
                <?php
                $testimonials = getTestimonials($conn);
                foreach ($testimonials as $testimonial):
                ?>
                    <div class="testimonial-item">
                        <div class="testimonial-content">
                            <p>"<?php echo htmlspecialchars($testimonial['content']); ?>"</p>
                            <div class="testimonial-author">
                                <strong><?php echo htmlspecialchars($testimonial['client_name']); ?></strong>
                                <span><?php echo htmlspecialchars($testimonial['event_type']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Contact CTA -->
    <section class="contact-cta">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Capture Your Moments?</h2>
                <p>Let's discuss your photography needs and create something beautiful together.</p>
                <div class="cta-buttons">
                    <a href="contact.php" class="btn btn-primary">Get In Touch</a>
                    <a href="booking.php" class="btn btn-secondary">Book a Session</a>
                </div>
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
</body>
</html>