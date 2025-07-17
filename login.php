<?php
session_start();
include 'config/database.php';
include 'includes/functions.php';

$error_message = '';
$success_message = '';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: dashboard.php');
    }
    exit;
}

// Handle login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error_message = 'Please fill in all fields.';
    } else {
        $user = authenticateUser($conn, $username, $password);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: dashboard.php');
            }
            exit;
        } else {
            $error_message = 'Invalid username or password.';
        }
    }
}

// Handle registration
if (isset($_POST['register'])) {
    $username = $_POST['reg_username'];
    $email = $_POST['reg_email'];
    $password = $_POST['reg_password'];
    $confirm_password = $_POST['reg_confirm_password'];
    
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = 'Please fill in all fields.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters long.';
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error_message = 'Username or email already exists.';
        } else {
            if (createUser($conn, $username, $email, $password)) {
                $success_message = 'Account created successfully! You can now login.';
            } else {
                $error_message = 'Error creating account. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PhotoLens</title>
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
                <li><a href="login.php" class="active">Login</a></li>
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Login Section -->
    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <div class="auth-header">
                    <h1>Welcome Back</h1>
                    <p>Sign in to your account or create a new one</p>
                </div>

                <!-- Alerts -->
                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div class="alert alert-error"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <!-- Auth Forms -->
                <div class="auth-forms">
                    <!-- Login Form -->
                    <div class="auth-form login-form active">
                        <h2>Sign In</h2>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="username">Username or Email</label>
                                <div class="input-group">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="username" name="username" class="form-control" 
                                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <div class="input-group">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="password" name="password" class="form-control" required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-options">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="remember">
                                    <span>Remember me</span>
                                </label>
                                <a href="forgot-password.php" class="forgot-link">Forgot password?</a>
                            </div>
                            <button type="submit" name="login" class="btn btn-primary btn-full">
                                <i class="fas fa-sign-in-alt"></i> Sign In
                            </button>
                        </form>
                        <div class="form-switch">
                            <p>Don't have an account? <a href="#" onclick="switchForm('register')">Sign up</a></p>
                        </div>
                    </div>

                    <!-- Registration Form -->
                    <div class="auth-form register-form">
                        <h2>Create Account</h2>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="reg_username">Username</label>
                                <div class="input-group">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="reg_username" name="reg_username" class="form-control" 
                                           value="<?php echo htmlspecialchars($_POST['reg_username'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="reg_email">Email Address</label>
                                <div class="input-group">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email" id="reg_email" name="reg_email" class="form-control" 
                                           value="<?php echo htmlspecialchars($_POST['reg_email'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="reg_password">Password</label>
                                <div class="input-group">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="reg_password" name="reg_password" class="form-control" required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('reg_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="password-strength">
                                    <div class="strength-bar">
                                        <div class="strength-fill"></div>
                                    </div>
                                    <span class="strength-text">Password strength</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="reg_confirm_password">Confirm Password</label>
                                <div class="input-group">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="reg_confirm_password" name="reg_confirm_password" class="form-control" required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('reg_confirm_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" required>
                                    <span>I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="privacy.php" target="_blank">Privacy Policy</a></span>
                                </label>
                            </div>
                            <button type="submit" name="register" class="btn btn-primary btn-full">
                                <i class="fas fa-user-plus"></i> Create Account
                            </button>
                        </form>
                        <div class="form-switch">
                            <p>Already have an account? <a href="#" onclick="switchForm('login')">Sign in</a></p>
                        </div>
                    </div>
                </div>

                <!-- Social Login -->
                <div class="social-login">
                    <div class="divider">
                        <span>Or continue with</span>
                    </div>
                    <div class="social-buttons">
                        <button class="social-btn google-btn">
                            <i class="fab fa-google"></i>
                            <span>Google</span>
                        </button>
                        <button class="social-btn facebook-btn">
                            <i class="fab fa-facebook-f"></i>
                            <span>Facebook</span>
                        </button>
                        <button class="social-btn twitter-btn">
                            <i class="fab fa-twitter"></i>
                            <span>Twitter</span>
                        </button>
                    </div>
                </div>

                <!-- Demo Accounts -->
                <div class="demo-accounts">
                    <h3>Demo Accounts</h3>
                    <div class="demo-buttons">
                        <button class="demo-btn" onclick="fillDemo('admin')">
                            <i class="fas fa-user-shield"></i>
                            <span>Admin Demo</span>
                        </button>
                        <button class="demo-btn" onclick="fillDemo('user')">
                            <i class="fas fa-user"></i>
                            <span>User Demo</span>
                        </button>
                    </div>
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
        // Switch between login and register forms
        function switchForm(formType) {
            const loginForm = document.querySelector('.login-form');
            const registerForm = document.querySelector('.register-form');
            
            if (formType === 'register') {
                loginForm.classList.remove('active');
                registerForm.classList.add('active');
            } else {
                registerForm.classList.remove('active');
                loginForm.classList.add('active');
            }
        }

        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const toggle = field.nextElementSibling;
            const icon = toggle.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Password strength checker
        document.getElementById('reg_password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.querySelector('.strength-fill');
            const strengthText = document.querySelector('.strength-text');
            
            let strength = 0;
            let text = 'Very Weak';
            
            // Check password criteria
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            // Update strength indicator
            const percentage = (strength / 5) * 100;
            strengthBar.style.width = percentage + '%';
            
            switch (strength) {
                case 0:
                case 1:
                    text = 'Very Weak';
                    strengthBar.style.backgroundColor = '#e74c3c';
                    break;
                case 2:
                    text = 'Weak';
                    strengthBar.style.backgroundColor = '#f39c12';
                    break;
                case 3:
                    text = 'Fair';
                    strengthBar.style.backgroundColor = '#f1c40f';
                    break;
                case 4:
                    text = 'Good';
                    strengthBar.style.backgroundColor = '#27ae60';
                    break;
                case 5:
                    text = 'Strong';
                    strengthBar.style.backgroundColor = '#2ecc71';
                    break;
            }
            
            strengthText.textContent = text;
        });

        // Demo account filler
        function fillDemo(type) {
            if (type === 'admin') {
                document.getElementById('username').value = 'admin';
                document.getElementById('password').value = 'admin123';
            } else {
                document.getElementById('username').value = 'demo';
                document.getElementById('password').value = 'demo123';
            }
        }

        // Social login (mock functionality)
        document.querySelectorAll('.social-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const provider = this.classList.contains('google-btn') ? 'Google' : 
                               this.classList.contains('facebook-btn') ? 'Facebook' : 'Twitter';
                alert(`${provider} login is not implemented in this demo. Please use the demo accounts or create a new account.`);
            });
        });
    </script>

    <style>
        /* Auth-specific styles */
        .auth-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 100px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .auth-container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .auth-header {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .auth-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .auth-header p {
            opacity: 0.9;
            font-size: 1rem;
        }

        .auth-forms {
            padding: 2rem;
            position: relative;
        }

        .auth-form {
            display: none;
        }

        .auth-form.active {
            display: block;
        }

        .auth-form h2 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            color: #666;
            z-index: 2;
        }

        .input-group .form-control {
            padding-left: 45px;
            padding-right: 45px;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            z-index: 2;
        }

        .password-toggle:hover {
            color: #2c3e50;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .checkbox-label input[type="checkbox"] {
            margin-right: 0.5rem;
        }

        .forgot-link {
            color: #e74c3c;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .btn-full {
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            margin-bottom: 1rem;
        }

        .form-switch {
            text-align: center;
            margin-top: 1rem;
        }

        .form-switch a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: 500;
        }

        .form-switch a:hover {
            text-decoration: underline;
        }

        .password-strength {
            margin-top: 0.5rem;
        }

        .strength-bar {
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 0.25rem;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-text {
            font-size: 0.8rem;
            color: #666;
        }

        .social-login {
            padding: 0 2rem 2rem;
        }

        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e9ecef;
        }

        .divider span {
            background: white;
            padding: 0 1rem;
            color: #666;
            font-size: 0.9rem;
        }

        .social-buttons {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
        }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            background: white;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .social-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .google-btn:hover {
            border-color: #db4437;
            color: #db4437;
        }

        .facebook-btn:hover {
            border-color: #4267b2;
            color: #4267b2;
        }

        .twitter-btn:hover {
            border-color: #1da1f2;
            color: #1da1f2;
        }

        .demo-accounts {
            padding: 0 2rem 2rem;
            border-top: 1px solid #e9ecef;
            margin-top: 1rem;
        }

        .demo-accounts h3 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .demo-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .demo-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #2c3e50;
        }

        .demo-btn:hover {
            background: #e74c3c;
            color: white;
            border-color: #e74c3c;
            transform: translateY(-2px);
        }

        .demo-btn i {
            font-size: 1.5rem;
        }

        .demo-btn span {
            font-size: 0.9rem;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .auth-container {
                margin: 1rem;
                border-radius: 15px;
            }

            .auth-header {
                padding: 1.5rem;
            }

            .auth-forms {
                padding: 1.5rem;
            }

            .social-login {
                padding: 0 1.5rem 1.5rem;
            }

            .demo-accounts {
                padding: 0 1.5rem 1.5rem;
            }

            .social-buttons {
                grid-template-columns: 1fr;
            }

            .demo-buttons {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>