<?php
session_start();
include 'config/database.php';
include 'includes/functions.php';

$success_message = '';
$error_message = '';

// Handle form submission
if ($_POST) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        // Save message to database
        if (saveContactMessage($conn, $name, $email, $subject, $message)) {
            $success_message = 'Thank you for your message! We will get back to you soon.';
            
            // Send email notification
            $email_subject = 'New Contact Message - PhotoLens';
            $email_message = "
                <h2>New Contact Message</h2>
                <p><strong>Name:</strong> $name</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Subject:</strong> $subject</p>
                <p><strong>Message:</strong></p>
                <p>$message</p>
            ";
            
            sendEmail('admin@photolens.com', $email_subject, $email_message);
            
            // Clear form data
            $_POST = [];
        } else {
            $error_message = 'There was an error sending your message. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - PhotoLens</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
                <li><a href="contact.php" class="active">Contact</a></li>
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
                <h1>Get In Touch</h1>
                <p>We'd love to hear from you. Let's discuss your photography needs.</p>
            </div>
        </div>
    </section>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <ul class="breadcrumb-list">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item">Contact</li>
            </ul>
        </div>
    </div>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <div class="contact-content">
                <!-- Contact Information -->
                <div class="contact-info">
                    <h2>Contact Information</h2>
                    <p>Ready to capture your special moments? Get in touch with us today!</p>
                    
                    <div class="contact-details">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Address</h3>
                                <p>123 Photography Street<br>Creative District, City<br>State 12345</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Phone</h3>
                                <p>Main: (555) 123-4567<br>Mobile: (555) 987-6543</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Email</h3>
                                <p>info@photolens.com<br>bookings@photolens.com</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Business Hours</h3>
                                <p>Monday - Friday: 9:00 AM - 6:00 PM<br>Saturday: 10:00 AM - 4:00 PM<br>Sunday: By appointment</p>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="contact-social">
                        <h3>Follow Us</h3>
                        <div class="social-links">
                            <a href="#" class="social-link">
                                <i class="fab fa-facebook"></i>
                                <span>Facebook</span>
                            </a>
                            <a href="#" class="social-link">
                                <i class="fab fa-instagram"></i>
                                <span>Instagram</span>
                            </a>
                            <a href="#" class="social-link">
                                <i class="fab fa-twitter"></i>
                                <span>Twitter</span>
                            </a>
                            <a href="#" class="social-link">
                                <i class="fab fa-linkedin"></i>
                                <span>LinkedIn</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="contact-form-container">
                    <h2>Send Us a Message</h2>
                    
                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                        <div class="alert alert-error">
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>

                    <form class="contact-form" method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name *</label>
                                <input type="text" id="name" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject *</label>
                            <select id="subject" name="subject" class="form-control" required>
                                <option value="">Select a subject</option>
                                <option value="General Inquiry" <?php echo ($_POST['subject'] ?? '') === 'General Inquiry' ? 'selected' : ''; ?>>General Inquiry</option>
                                <option value="Wedding Photography" <?php echo ($_POST['subject'] ?? '') === 'Wedding Photography' ? 'selected' : ''; ?>>Wedding Photography</option>
                                <option value="Portrait Session" <?php echo ($_POST['subject'] ?? '') === 'Portrait Session' ? 'selected' : ''; ?>>Portrait Session</option>
                                <option value="Event Photography" <?php echo ($_POST['subject'] ?? '') === 'Event Photography' ? 'selected' : ''; ?>>Event Photography</option>
                                <option value="Commercial Photography" <?php echo ($_POST['subject'] ?? '') === 'Commercial Photography' ? 'selected' : ''; ?>>Commercial Photography</option>
                                <option value="Pricing Information" <?php echo ($_POST['subject'] ?? '') === 'Pricing Information' ? 'selected' : ''; ?>>Pricing Information</option>
                                <option value="Technical Support" <?php echo ($_POST['subject'] ?? '') === 'Technical Support' ? 'selected' : ''; ?>>Technical Support</option>
                                <option value="Other" <?php echo ($_POST['subject'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message *</label>
                            <textarea id="message" name="message" class="form-control" rows="6" 
                                      placeholder="Tell us about your photography needs, preferred dates, location, or any questions you have..."
                                      required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-large">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="container">
            <h2>Find Us</h2>
            <div class="map-container">
                <!-- Google Maps Embed -->
                <div class="map-embed">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3024.2219901290355!2d-74.00369368526311!3d40.71312937933185!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c25a22a3bda30d%3A0xb89d1fe6bc499443!2sDowntown%20Conference%20Center!5e0!3m2!1sen!2sus!4v1584975727558!5m2!1sen!2sus"
                        width="100%" 
                        height="400" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>
                </div>
                
                <!-- Map Info -->
                <div class="map-info">
                    <h3>Studio Location</h3>
                    <p>Our studio is conveniently located in the heart of the creative district, easily accessible by public transportation and with ample parking available.</p>
                    
                    <div class="map-features">
                        <div class="map-feature">
                            <i class="fas fa-parking"></i>
                            <span>Free Parking Available</span>
                        </div>
                        <div class="map-feature">
                            <i class="fas fa-train"></i>
                            <span>Near Public Transit</span>
                        </div>
                        <div class="map-feature">
                            <i class="fas fa-wheelchair"></i>
                            <span>Wheelchair Accessible</span>
                        </div>
                        <div class="map-feature">
                            <i class="fas fa-coffee"></i>
                            <span>Refreshments Available</span>
                        </div>
                    </div>
                    
                    <a href="https://maps.google.com" target="_blank" class="btn btn-outline">
                        <i class="fas fa-directions"></i> Get Directions
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <h2>Frequently Asked Questions</h2>
            <div class="faq-grid">
                <div class="faq-item">
                    <h3>How do I book a session?</h3>
                    <p>You can book a session through our online booking form, by calling us directly, or by sending us an email. We'll respond within 24 hours to confirm availability.</p>
                </div>
                <div class="faq-item">
                    <h3>What's your response time?</h3>
                    <p>We typically respond to all inquiries within 24 hours during business days. For urgent requests, please call us directly.</p>
                </div>
                <div class="faq-item">
                    <h3>Do you travel for sessions?</h3>
                    <p>Yes! We offer on-location photography services. Travel fees may apply depending on the distance from our studio.</p>
                </div>
                <div class="faq-item">
                    <h3>What's included in your packages?</h3>
                    <p>Each package includes professional editing, an online gallery, and print release. Specific details vary by package type.</p>
                </div>
                <div class="faq-item">
                    <h3>Can I see examples of your work?</h3>
                    <p>Absolutely! Check out our gallery page to see our latest work, or follow us on social media for regular updates.</p>
                </div>
                <div class="faq-item">
                    <h3>What's your cancellation policy?</h3>
                    <p>We require 48 hours notice for cancellations. Please contact us as soon as possible if you need to reschedule.</p>
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
    <script src="assets/js/main.js"></script>

    <style>
        /* Contact-specific styles */
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

        .contact-section {
            padding: 80px 0;
        }

        .contact-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: start;
        }

        .contact-info h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #2c3e50;
        }

        .contact-info > p {
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .contact-details {
            margin-bottom: 3rem;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 2rem;
        }

        .contact-icon {
            background: #e74c3c;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            flex-shrink: 0;
            font-size: 1.2rem;
        }

        .contact-text h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        .contact-text p {
            color: #666;
            line-height: 1.6;
        }

        .contact-social {
            margin-top: 2rem;
        }

        .contact-social h3 {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: #2c3e50;
        }

        .social-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            background: #f8f9fa;
            border-radius: 8px;
            text-decoration: none;
            color: #666;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: #e74c3c;
            color: white;
            transform: translateX(5px);
        }

        .social-link i {
            font-size: 1.2rem;
            width: 20px;
        }

        .contact-form-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .contact-form-container h2 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            color: #2c3e50;
            text-align: center;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .btn-large {
            padding: 15px 40px;
            font-size: 1.1rem;
            width: 100%;
        }

        .map-section {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .map-section h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            color: #2c3e50;
        }

        .map-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            align-items: start;
        }

        .map-embed {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .map-embed iframe {
            width: 100%;
            height: 400px;
            border: none;
        }

        .map-info {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .map-info h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #2c3e50;
        }

        .map-info p {
            color: #666;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .map-features {
            margin-bottom: 2rem;
        }

        .map-feature {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            color: #666;
        }

        .map-feature i {
            color: #e74c3c;
            width: 20px;
        }

        .faq-section {
            padding: 80px 0;
        }

        .faq-section h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            color: #2c3e50;
        }

        .faq-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }

        .faq-item {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .faq-item h3 {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: #2c3e50;
        }

        .faq-item p {
            color: #666;
            line-height: 1.6;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .contact-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .map-container {
                grid-template-columns: 1fr;
            }

            .faq-grid {
                grid-template-columns: 1fr;
            }

            .contact-item {
                flex-direction: column;
                text-align: center;
            }

            .contact-icon {
                margin-right: 0;
                margin-bottom: 1rem;
            }
        }
    </style>
</body>
</html>