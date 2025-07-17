<?php
session_start();
include 'config/database.php';
include 'includes/functions.php';

$success_message = '';
$error_message = '';

// Handle form submission
if ($_POST) {
    $client_name = $_POST['client_name'] ?? '';
    $client_email = $_POST['client_email'] ?? '';
    $client_phone = $_POST['client_phone'] ?? '';
    $event_type = $_POST['event_type'] ?? '';
    $event_date = $_POST['event_date'] ?? '';
    $event_time = $_POST['event_time'] ?? '';
    $location = $_POST['location'] ?? '';
    $message = $_POST['message'] ?? '';
    
    // Validate required fields
    if (empty($client_name) || empty($client_email) || empty($event_type) || empty($event_date) || empty($event_time)) {
        $error_message = 'Please fill in all required fields.';
    } else {
        // Check if date is in the future
        if (strtotime($event_date) <= time()) {
            $error_message = 'Please select a future date.';
        } else {
            // Check availability
            $stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE event_date = ? AND status != 'cancelled'");
            $stmt->execute([$event_date]);
            $existing_bookings = $stmt->fetchColumn();
            
            if ($existing_bookings >= 1) { // Assuming only one booking per day
                $error_message = 'Sorry, this date is already booked. Please choose another date.';
            } else {
                // Create booking
                $booking_data = [
                    'client_name' => $client_name,
                    'client_email' => $client_email,
                    'client_phone' => $client_phone,
                    'event_type' => $event_type,
                    'event_date' => $event_date,
                    'event_time' => $event_time,
                    'location' => $location,
                    'message' => $message
                ];
                
                if (createBooking($conn, $booking_data)) {
                    $success_message = 'Your booking request has been submitted successfully! We will contact you soon to confirm the details.';
                    
                    // Send email notification (optional)
                    $email_subject = 'New Booking Request - PhotoLens';
                    $email_message = "
                        <h2>New Booking Request</h2>
                        <p><strong>Client:</strong> $client_name</p>
                        <p><strong>Email:</strong> $client_email</p>
                        <p><strong>Phone:</strong> $client_phone</p>
                        <p><strong>Event Type:</strong> $event_type</p>
                        <p><strong>Date:</strong> $event_date</p>
                        <p><strong>Time:</strong> $event_time</p>
                        <p><strong>Location:</strong> $location</p>
                        <p><strong>Message:</strong> $message</p>
                    ";
                    
                    sendEmail('admin@photolens.com', $email_subject, $email_message);
                    
                    // Clear form data
                    $_POST = [];
                } else {
                    $error_message = 'There was an error submitting your booking. Please try again.';
                }
            }
        }
    }
}

// Get existing bookings for calendar
$stmt = $conn->prepare("SELECT event_date FROM bookings WHERE status != 'cancelled' AND event_date >= CURDATE()");
$stmt->execute();
$booked_dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Session - PhotoLens</title>
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
                <h1>Book a Photography Session</h1>
                <p>Schedule your perfect photography session with us</p>
            </div>
        </div>
    </section>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <ul class="breadcrumb-list">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item">Booking</li>
            </ul>
        </div>
    </div>

    <!-- Booking Section -->
    <section class="booking-section">
        <div class="container">
            <div class="booking-content">
                <div class="booking-info">
                    <h2>Why Choose PhotoLens?</h2>
                    <div class="booking-features">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-award"></i>
                            </div>
                            <div class="feature-content">
                                <h3>Professional Quality</h3>
                                <p>High-end equipment and expert techniques for stunning results</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="feature-content">
                                <h3>Flexible Scheduling</h3>
                                <p>We work around your schedule to find the perfect time</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div class="feature-content">
                                <h3>Personalized Experience</h3>
                                <p>Every session is tailored to your unique vision and needs</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-images"></i>
                            </div>
                            <div class="feature-content">
                                <h3>Quick Delivery</h3>
                                <p>Edited photos delivered within 1-2 weeks</p>
                            </div>
                        </div>
                    </div>

                    <div class="pricing-info">
                        <h3>Session Packages</h3>
                        <div class="pricing-grid">
                            <div class="pricing-card">
                                <h4>Portrait Session</h4>
                                <div class="price">$150</div>
                                <ul>
                                    <li>1 hour session</li>
                                    <li>20 edited photos</li>
                                    <li>Online gallery</li>
                                    <li>Print release</li>
                                </ul>
                            </div>
                            <div class="pricing-card">
                                <h4>Event Coverage</h4>
                                <div class="price">$300</div>
                                <ul>
                                    <li>3 hour coverage</li>
                                    <li>50+ edited photos</li>
                                    <li>Online gallery</li>
                                    <li>Print release</li>
                                </ul>
                            </div>
                            <div class="pricing-card">
                                <h4>Wedding Package</h4>
                                <div class="price">$800</div>
                                <ul>
                                    <li>Full day coverage</li>
                                    <li>200+ edited photos</li>
                                    <li>Online gallery</li>
                                    <li>Print release</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="booking-form-container">
                    <div class="booking-form-header">
                        <h2>Book Your Session</h2>
                        <p>Fill out the form below and we'll get back to you within 24 hours</p>
                    </div>

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

                    <form class="booking-form" method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="client_name">Full Name *</label>
                                <input type="text" id="client_name" name="client_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['client_name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="client_email">Email Address *</label>
                                <input type="email" id="client_email" name="client_email" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['client_email'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="client_phone">Phone Number</label>
                                <input type="tel" id="client_phone" name="client_phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['client_phone'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="event_type">Event Type *</label>
                                <select id="event_type" name="event_type" class="form-control" required>
                                    <option value="">Select Event Type</option>
                                    <option value="Wedding" <?php echo ($_POST['event_type'] ?? '') === 'Wedding' ? 'selected' : ''; ?>>Wedding</option>
                                    <option value="Portrait" <?php echo ($_POST['event_type'] ?? '') === 'Portrait' ? 'selected' : ''; ?>>Portrait Session</option>
                                    <option value="Family" <?php echo ($_POST['event_type'] ?? '') === 'Family' ? 'selected' : ''; ?>>Family Photos</option>
                                    <option value="Corporate" <?php echo ($_POST['event_type'] ?? '') === 'Corporate' ? 'selected' : ''; ?>>Corporate Event</option>
                                    <option value="Birthday" <?php echo ($_POST['event_type'] ?? '') === 'Birthday' ? 'selected' : ''; ?>>Birthday Party</option>
                                    <option value="Graduation" <?php echo ($_POST['event_type'] ?? '') === 'Graduation' ? 'selected' : ''; ?>>Graduation</option>
                                    <option value="Other" <?php echo ($_POST['event_type'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="event_date">Preferred Date *</label>
                                <input type="date" id="event_date" name="event_date" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['event_date'] ?? ''); ?>" 
                                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                                <div class="availability-status"></div>
                            </div>
                            <div class="form-group">
                                <label for="event_time">Preferred Time *</label>
                                <select id="event_time" name="event_time" class="form-control" required>
                                    <option value="">Select Time</option>
                                    <option value="09:00" <?php echo ($_POST['event_time'] ?? '') === '09:00' ? 'selected' : ''; ?>>9:00 AM</option>
                                    <option value="10:00" <?php echo ($_POST['event_time'] ?? '') === '10:00' ? 'selected' : ''; ?>>10:00 AM</option>
                                    <option value="11:00" <?php echo ($_POST['event_time'] ?? '') === '11:00' ? 'selected' : ''; ?>>11:00 AM</option>
                                    <option value="12:00" <?php echo ($_POST['event_time'] ?? '') === '12:00' ? 'selected' : ''; ?>>12:00 PM</option>
                                    <option value="13:00" <?php echo ($_POST['event_time'] ?? '') === '13:00' ? 'selected' : ''; ?>>1:00 PM</option>
                                    <option value="14:00" <?php echo ($_POST['event_time'] ?? '') === '14:00' ? 'selected' : ''; ?>>2:00 PM</option>
                                    <option value="15:00" <?php echo ($_POST['event_time'] ?? '') === '15:00' ? 'selected' : ''; ?>>3:00 PM</option>
                                    <option value="16:00" <?php echo ($_POST['event_time'] ?? '') === '16:00' ? 'selected' : ''; ?>>4:00 PM</option>
                                    <option value="17:00" <?php echo ($_POST['event_time'] ?? '') === '17:00' ? 'selected' : ''; ?>>5:00 PM</option>
                                    <option value="18:00" <?php echo ($_POST['event_time'] ?? '') === '18:00' ? 'selected' : ''; ?>>6:00 PM</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="location">Event Location</label>
                            <input type="text" id="location" name="location" class="form-control" 
                                   placeholder="Enter the location for your session"
                                   value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="message">Additional Details</label>
                            <textarea id="message" name="message" class="form-control" rows="4" 
                                      placeholder="Tell us more about your vision, special requests, or any other details..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" required>
                                <span class="checkmark"></span>
                                I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="privacy.php" target="_blank">Privacy Policy</a>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-large">
                            <i class="fas fa-calendar-check"></i> Submit Booking Request
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Calendar Availability -->
    <section class="calendar-section">
        <div class="container">
            <h2>Check Availability</h2>
            <div class="calendar-container">
                <div class="calendar-header">
                    <button class="calendar-nav" id="prevMonth">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <h3 id="currentMonth"></h3>
                    <button class="calendar-nav" id="nextMonth">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <div class="calendar-grid" id="calendarGrid">
                    <!-- Calendar will be generated by JavaScript -->
                </div>
                <div class="calendar-legend">
                    <div class="legend-item">
                        <span class="legend-color available"></span>
                        <span>Available</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color booked"></span>
                        <span>Booked</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color past"></span>
                        <span>Past Date</span>
                    </div>
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
                    <h3>How far in advance should I book?</h3>
                    <p>We recommend booking at least 2-3 weeks in advance, especially for weddings and special events. However, we can often accommodate last-minute requests.</p>
                </div>
                <div class="faq-item">
                    <h3>What happens if it rains on my session day?</h3>
                    <p>We monitor weather forecasts closely and will contact you if rescheduling is necessary. We have backup indoor locations and covered areas for most situations.</p>
                </div>
                <div class="faq-item">
                    <h3>How long does it take to receive my photos?</h3>
                    <p>You'll receive a sneak peek within 48 hours, and the full gallery of edited photos within 1-2 weeks of your session.</p>
                </div>
                <div class="faq-item">
                    <h3>Can I request specific shots or poses?</h3>
                    <p>Absolutely! We encourage you to share your vision and any specific shots you'd like. We'll work together to create the perfect session.</p>
                </div>
                <div class="faq-item">
                    <h3>What should I wear for my session?</h3>
                    <p>We'll provide a style guide with outfit suggestions based on your session type and location. Comfort is key!</p>
                </div>
                <div class="faq-item">
                    <h3>Do you offer payment plans?</h3>
                    <p>Yes, we offer flexible payment options including installment plans for wedding packages. Contact us to discuss your needs.</p>
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
    <script>
        // Booked dates from PHP
        const bookedDates = <?php echo json_encode($booked_dates); ?>;
        
        // Initialize calendar
        document.addEventListener('DOMContentLoaded', function() {
            initBookingCalendar();
        });

        function initBookingCalendar() {
            const calendarGrid = document.getElementById('calendarGrid');
            const currentMonthElement = document.getElementById('currentMonth');
            const prevMonthBtn = document.getElementById('prevMonth');
            const nextMonthBtn = document.getElementById('nextMonth');
            
            let currentDate = new Date();
            let currentMonth = currentDate.getMonth();
            let currentYear = currentDate.getFullYear();
            
            const months = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            
            function generateCalendar(month, year) {
                const firstDay = new Date(year, month, 1).getDay();
                const daysInMonth = new Date(year, month + 1, 0).getDate();
                const today = new Date();
                
                currentMonthElement.textContent = `${months[month]} ${year}`;
                
                let calendarHTML = '';
                
                // Add day headers
                const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                dayHeaders.forEach(day => {
                    calendarHTML += `<div class="calendar-day-header">${day}</div>`;
                });
                
                // Add empty cells for days before the first day of the month
                for (let i = 0; i < firstDay; i++) {
                    calendarHTML += '<div class="calendar-day empty"></div>';
                }
                
                // Add days of the month
                for (let day = 1; day <= daysInMonth; day++) {
                    const date = new Date(year, month, day);
                    const dateString = date.toISOString().split('T')[0];
                    
                    let dayClass = 'calendar-day';
                    
                    if (date < today) {
                        dayClass += ' past';
                    } else if (bookedDates.includes(dateString)) {
                        dayClass += ' booked';
                    } else {
                        dayClass += ' available';
                    }
                    
                    calendarHTML += `<div class="${dayClass}" data-date="${dateString}">${day}</div>`;
                }
                
                calendarGrid.innerHTML = calendarHTML;
                
                // Add click event listeners to available days
                document.querySelectorAll('.calendar-day.available').forEach(day => {
                    day.addEventListener('click', function() {
                        const selectedDate = this.getAttribute('data-date');
                        document.getElementById('event_date').value = selectedDate;
                        
                        // Remove previous selections
                        document.querySelectorAll('.calendar-day.selected').forEach(d => {
                            d.classList.remove('selected');
                        });
                        
                        // Add selection to clicked day
                        this.classList.add('selected');
                    });
                });
            }
            
            // Navigation event listeners
            prevMonthBtn.addEventListener('click', function() {
                currentMonth--;
                if (currentMonth < 0) {
                    currentMonth = 11;
                    currentYear--;
                }
                generateCalendar(currentMonth, currentYear);
            });
            
            nextMonthBtn.addEventListener('click', function() {
                currentMonth++;
                if (currentMonth > 11) {
                    currentMonth = 0;
                    currentYear++;
                }
                generateCalendar(currentMonth, currentYear);
            });
            
            // Generate initial calendar
            generateCalendar(currentMonth, currentYear);
        }
    </script>

    <style>
        /* Booking-specific styles */
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

        .booking-section {
            padding: 80px 0;
        }

        .booking-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: start;
        }

        .booking-info h2 {
            font-size: 2rem;
            margin-bottom: 2rem;
            color: #2c3e50;
        }

        .booking-features {
            margin-bottom: 3rem;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }

        .feature-icon {
            background: #e74c3c;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .feature-content h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        .feature-content p {
            color: #666;
            line-height: 1.6;
        }

        .pricing-info h3 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #2c3e50;
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .pricing-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .pricing-card:hover {
            border-color: #e74c3c;
            transform: translateY(-2px);
        }

        .pricing-card h4 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        .pricing-card .price {
            font-size: 1.5rem;
            font-weight: 600;
            color: #e74c3c;
            margin-bottom: 1rem;
        }

        .pricing-card ul {
            list-style: none;
        }

        .pricing-card ul li {
            padding: 0.25rem 0;
            color: #666;
        }

        .pricing-card ul li::before {
            content: '✓';
            color: #27ae60;
            font-weight: bold;
            margin-right: 0.5rem;
        }

        .booking-form-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .booking-form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .booking-form-header h2 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        .booking-form-header p {
            color: #666;
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

        .checkbox-label {
            display: flex;
            align-items: flex-start;
            cursor: pointer;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .checkbox-label input[type="checkbox"] {
            margin-right: 0.5rem;
            margin-top: 0.2rem;
        }

        .availability-status {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .availability-status.available {
            color: #27ae60;
        }

        .availability-status.unavailable {
            color: #e74c3c;
        }

        /* Calendar Styles */
        .calendar-section {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .calendar-section h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            color: #2c3e50;
        }

        .calendar-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .calendar-header h3 {
            font-size: 1.5rem;
            color: #2c3e50;
        }

        .calendar-nav {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 0.5rem;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .calendar-nav:hover {
            background: #c0392b;
            transform: scale(1.1);
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: #e9ecef;
            border-radius: 8px;
            overflow: hidden;
        }

        .calendar-day-header {
            background: #2c3e50;
            color: white;
            padding: 0.75rem;
            text-align: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .calendar-day {
            background: white;
            padding: 0.75rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            min-height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .calendar-day.empty {
            cursor: default;
        }

        .calendar-day.past {
            background: #ecf0f1;
            color: #95a5a6;
            cursor: not-allowed;
        }

        .calendar-day.booked {
            background: #e74c3c;
            color: white;
            cursor: not-allowed;
        }

        .calendar-day.available:hover {
            background: #3498db;
            color: white;
        }

        .calendar-day.selected {
            background: #27ae60;
            color: white;
        }

        .calendar-legend {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 1rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .legend-color {
            width: 15px;
            height: 15px;
            border-radius: 3px;
        }

        .legend-color.available {
            background: #27ae60;
        }

        .legend-color.booked {
            background: #e74c3c;
        }

        .legend-color.past {
            background: #95a5a6;
        }

        /* FAQ Styles */
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
            .booking-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .calendar-legend {
                flex-direction: column;
                align-items: center;
                gap: 1rem;
            }

            .faq-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>