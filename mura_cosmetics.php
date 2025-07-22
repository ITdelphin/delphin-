<?php
// Start PHP session and handle admin authentication
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');    // Default XAMPP username
define('DB_PASS', '');        // Default XAMPP password is empty
define('DB_NAME', 'mura_cosmetics');

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create tables if they don't exist
function initializeDatabase($conn) {
    $queries = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            reset_token VARCHAR(255),
            reset_token_expires DATETIME,
            is_admin BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            image_url VARCHAR(255),
            stock INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            customer_name VARCHAR(100) NOT NULL,
            customer_email VARCHAR(100) NOT NULL,
            customer_phone VARCHAR(20) NOT NULL,
            customer_address TEXT NOT NULL,
            payment_method VARCHAR(50) NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            status VARCHAR(20) DEFAULT 'pending',
            order_details TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )"
    ];
    
    foreach ($queries as $query) {
        if (!$conn->query($query)) {
            die("Error creating table: " . $conn->error);
        }
    }
    
    // Create admin user if not exists
    $adminCheck = $conn->query("SELECT * FROM users WHERE username = 'mura'");
    if ($adminCheck->num_rows == 0) {
        $hashedPassword = password_hash('Delphin@1gisenyi', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, is_admin) VALUES (?, ?, ?, TRUE)");
        $username = 'mura';
        $email = 'ngarambedelp@icloud.com';
        $stmt->bind_param("sss", $username, $hashedPassword, $email);
        $stmt->execute();
        $stmt->close();
    }
}

initializeDatabase($conn);

// Handle admin login
if (isset($_POST['admin_login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND is_admin = TRUE");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $user['username'];
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } else {
            $login_error = "Invalid password";
        }
    } else {
        $login_error = "Admin user not found";
    }
    $stmt->close();
}

// Handle admin logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Handle password reset request
if (isset($_POST['reset_request'])) {
    $email = $_POST['email'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND is_admin = TRUE");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));
        
        $updateStmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
        $updateStmt->bind_param("ssi", $token, $expires, $user['id']);
        $updateStmt->execute();
        $updateStmt->close();
        
        // In a real application, you would send an email with this link
        $reset_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]?token=$token";
        
        $reset_success = "Password reset link has been generated. In a real application, this would be emailed to you. For now, here's the link: <a href='$reset_link'>$reset_link</a>";
    } else {
        $reset_error = "No admin account found with that email";
    }
    $stmt->close();
}

// Handle password reset
if (isset($_POST['reset_password'])) {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $reset_error = "Passwords do not match";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $updateStmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
            $updateStmt->bind_param("si", $hashedPassword, $user['id']);
            $updateStmt->execute();
            $updateStmt->close();
            
            $reset_success = "Password has been reset successfully. You can now <a href='".$_SERVER['PHP_SELF']."'>login</a>.";
        } else {
            $reset_error = "Invalid or expired token";
        }
        $stmt->close();
    }
}

// Check if we're viewing admin section
$admin_section = isset($_GET['admin']) || (isset($_SESSION['admin_logged_in']) && basename($_SERVER['PHP_SELF']) == basename(__FILE__));

// Handle admin actions
if (isset($_SESSION['admin_logged_in'])) {
    // Add new product
    if (isset($_POST['add_product'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = floatval($_POST['price']);
        $stock = intval($_POST['stock']);
        $image_url = $_POST['image_url'];
        
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, image_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdis", $name, $description, $price, $stock, $image_url);
        $stmt->execute();
        $stmt->close();
        
        $admin_message = "Product added successfully!";
    }
    
    // Update product
    if (isset($_POST['update_product'])) {
        $id = intval($_POST['id']);
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = floatval($_POST['price']);
        $stock = intval($_POST['stock']);
        $image_url = $_POST['image_url'];
        
        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, image_url=? WHERE id=?");
        $stmt->bind_param("ssdisi", $name, $description, $price, $stock, $image_url, $id);
        $stmt->execute();
        $stmt->close();
        
        $admin_message = "Product updated successfully!";
    }
    
    // Delete product
    if (isset($_GET['delete_product'])) {
        $id = intval($_GET['delete_product']);
        $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        $admin_message = "Product deleted successfully!";
    }
    
    // Update order status
    if (isset($_POST['update_order_status'])) {
        $id = intval($_POST['order_id']);
        $status = $_POST['status'];
        
        $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        $stmt->close();
        
        $admin_message = "Order status updated successfully!";
    }
}

// Get products for the store front
$products = [];
$result = $conn->query("SELECT * FROM products WHERE stock > 0");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Handle order submission from store
if (isset($_POST['place_order'])) {
    $name = $_POST['fullName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $payment_method = $_POST['paymentMethod'];
    $order_details = $_POST['orderDetails'];
    
    // Calculate total from order details
    $details = json_decode($order_details, true);
    $total = 0;
    if ($details) {
        foreach ($details as $item) {
            $total += $item['price'] * $item['quantity'];
        }
    }
    
    // Insert order
    $stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_email, customer_phone, customer_address, payment_method, total_amount, order_details) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssds", $name, $email, $phone, $address, $payment_method, $total, $order_details);
    $stmt->execute();
    $stmt->close();
    
    // Reduce stock (in a real application, you'd want to do this in a transaction)
    if ($details) {
        foreach ($details as $item) {
            $product_id = intval($item['id']);
            $quantity = intval($item['quantity']);
            $updateStmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $updateStmt->bind_param("ii", $quantity, $product_id);
            $updateStmt->execute();
            $updateStmt->close();
        }
    }
    
    $order_success = true;
}

// Handle contact form submission
if (isset($_POST['contact_submit'])) {
    $name = $_POST['contactName'];
    $email = $_POST['contactEmail'];
    $message = $_POST['contactMessage'];
    
    // In a real application, you would send an email here
    $contact_success = "Thank you for your message, " . htmlspecialchars($name) . "! We'll get back to you soon.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($_SESSION['admin_logged_in']) ? 'Admin Panel - ' : ''; ?>Mura Cosmetics</title>
    <style>
        /* General Styling */
        :root {
            --primary-color: #FFDDCC; /* Light peach/beige */
            --secondary-color: #F8C8DC; /* Soft pink */
            --accent-color: #E0BBE4;   /* Muted lavender */
            --dark-text: #333333;
            --light-text: #ffffff;
            --border-color: #DDDDDD;
            --button-bg: #884433; /* Darker brown/red for contrast */
            --button-hover-bg: #AA5544;
            --admin-primary: #4a6fa5;
            --admin-secondary: #3a5a8a;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: var(--dark-text);
            background-color: #fdfaf7; /* Off-white for body */
            scroll-behavior: smooth;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        a {
            color: var(--button-bg);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .btn {
            display: inline-block;
            background-color: var(--button-bg);
            color: var(--light-text);
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .admin-btn {
            background-color: var(--admin-primary);
        }

        .admin-btn:hover {
            background-color: var(--admin-secondary);
        }

        .btn:hover {
            background-color: var(--button-hover-bg);
        }

        h1, h2, h3 {
            margin-bottom: 20px;
            color: var(--dark-text);
        }

        /* Header */
        header {
            background-color: var(--primary-color);
            color: var(--dark-text);
            padding: 15px 0;
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--button-bg);
        }

        header nav ul {
            list-style: none;
        }

        header nav ul li {
            display: inline-block;
            margin-left: 20px;
        }

        header nav ul li a {
            color: var(--dark-text);
            font-weight: bold;
            font-size: 1.1rem;
            transition: color 0.3s ease;
        }

        header nav ul li a:hover {
            color: var(--button-bg);
            text-decoration: none;
        }

        #cart-icon {
            display: flex;
            align-items: center;
        }

        #cart-icon span {
            margin-left: 5px;
        }

        /* Hero Section */
        .hero-section {
            background: url('https://via.placeholder.com/1920x1080/FFDDCC/884433?text=Mura+Cosmetics+Banner') no-repeat center center/cover;
            color: var(--light-text);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            margin-top: 70px; /* Adjust for fixed header */
        }

        .hero-content {
            background-color: rgba(0, 0, 0, 0.4);
            padding: 40px;
            border-radius: 10px;
        }

        .hero-content h2 {
            font-size: 3.5rem;
            margin-bottom: 15px;
            color: var(--light-text);
        }

        .hero-content p {
            font-size: 1.5rem;
            margin-bottom: 30px;
        }

        .shop-now-btn {
            font-size: 1.2rem;
            padding: 15px 30px;
        }

        /* Products Section */
        .products-section {
            padding: 80px 0;
            background-color: var(--primary-color);
            text-align: center;
        }

        .products-section h2 {
            font-size: 2.5rem;
            margin-bottom: 40px;
            color: var(--button-bg);
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .product-item {
            background-color: var(--light-text);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .product-item:hover {
            transform: translateY(-5px);
        }

        .product-item img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .product-item h3 {
            font-size: 1.4rem;
            margin-bottom: 10px;
            color: var(--dark-text);
        }

        .product-item .price {
            font-size: 1.2rem;
            color: var(--button-bg);
            font-weight: bold;
            margin-bottom: 15px;
        }

        .add-to-cart-btn {
            width: 100%;
        }

        /* Cart Section (Side Panel) */
        .cart-panel {
            position: fixed;
            top: 0;
            right: -400px; /* Hidden by default */
            width: 350px;
            height: 100%;
            background-color: var(--light-text);
            box-shadow: -5px 0 15px rgba(0,0,0,0.2);
            transition: right 0.3s ease-in-out;
            z-index: 2000;
            display: flex;
            flex-direction: column;
        }

        .cart-panel.open {
            right: 0;
        }

        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background-color: var(--secondary-color);
            color: var(--dark-text);
            border-bottom: 1px solid var(--border-color);
        }

        .cart-header h3 {
            margin: 0;
            color: var(--dark-text);
        }

        .close-cart-btn {
            background: none;
            border: none;
            font-size: 1.8rem;
            cursor: pointer;
            color: var(--dark-text);
        }

        .cart-items {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .cart-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px dashed var(--border-color);
        }

        .cart-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .cart-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }

        .cart-item-details {
            flex-grow: 1;
        }

        .cart-item-details h4 {
            margin: 0 0 5px 0;
            font-size: 1.1rem;
        }

        .cart-item-details .price {
            font-size: 0.95rem;
            color: #666;
        }

        .cart-item-actions {
            display: flex;
            align-items: center;
        }

        .cart-item-actions input[type="number"] {
            width: 50px;
            padding: 5px;
            border: 1px solid var(--border-color);
            border-radius: 3px;
            text-align: center;
            margin-right: 10px;
        }

        .remove-item-btn {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .remove-item-btn:hover {
            background-color: #d32f2f;
        }

        .cart-summary {
            padding: 20px;
            border-top: 1px solid var(--border-color);
            text-align: right;
            background-color: var(--secondary-color);
        }

        .cart-summary p {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .checkout-btn {
            width: 100%;
        }

        /* Checkout Section */
        .checkout-section, .about-section, .contact-section {
            padding: 80px 0;
            background-color: #fdfaf7;
            text-align: center;
        }

        .checkout-section h2, .about-section h2, .contact-section h2 {
            font-size: 2.5rem;
            margin-bottom: 40px;
            color: var(--button-bg);
        }

        .checkout-section form, .contact-section form {
            max-width: 600px;
            margin: 0 auto;
            background-color: var(--primary-color);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            text-align: left;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--dark-text);
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 1rem;
            background-color: var(--light-text);
        }

        .form-group textarea {
            resize: vertical;
        }

        .submit-order-btn, .submit-contact-btn {
            width: auto;
            margin-top: 10px;
        }

        #order-message, #contact-message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            font-weight: bold;
        }

        #order-message.success, #contact-message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        #order-message.error, #contact-message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* About Section */
        .about-section {
            background-color: var(--secondary-color);
            color: var(--dark-text);
        }

        .about-section p {
            max-width: 800px;
            margin: 0 auto 20px auto;
            font-size: 1.1rem;
            line-height: 1.8;
        }

        /* Contact Section */
        .contact-section {
            background-color: var(--primary-color);
        }

        .contact-info {
            margin-bottom: 40px;
        }

        .contact-info p {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .contact-info a {
            color: var(--button-bg);
            font-weight: bold;
        }

        .social-links a {
            display: inline-block;
            margin: 0 10px;
            font-size: 1.1rem;
            padding: 8px 15px;
            border-radius: 5px;
            background-color: var(--button-bg);
            color: var(--light-text);
            transition: background-color 0.3s ease;
        }

        .social-links a:hover {
            background-color: var(--button-hover-bg);
            text-decoration: none;
        }

        /* Footer */
        footer {
            background-color: var(--dark-text);
            color: var(--light-text);
            padding: 30px 0;
            text-align: center;
        }

        footer p {
            margin-bottom: 10px;
            font-size: 0.9rem;
        }

        footer a {
            color: var(--accent-color);
            margin: 0 10px;
            font-size: 0.9rem;
        }

        footer a:hover {
            text-decoration: underline;
        }

        /* Admin Panel Styles */
        .admin-panel {
            margin-top: 70px;
            padding: 20px;
        }

        .admin-nav {
            background-color: var(--admin-primary);
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .admin-nav ul {
            list-style: none;
            display: flex;
        }

        .admin-nav ul li {
            margin-right: 20px;
        }

        .admin-nav ul li a {
            color: white;
            font-weight: bold;
        }

        .admin-section {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .admin-table th, .admin-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .admin-table th {
            background-color: var(--admin-primary);
            color: white;
        }

        .admin-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .admin-form {
            max-width: 600px;
            margin: 0 auto;
        }

        .admin-form .form-group {
            margin-bottom: 15px;
        }

        .admin-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .admin-form input, .admin-form textarea, .admin-form select {
            width: 100%;
            padding: 8px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Login Form */
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: var(--admin-primary);
        }

        .login-form .form-group {
            margin-bottom: 15px;
        }

        .login-form label {
            display: block;
            margin-bottom: 5px;
        }

        .login-form input {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }

        .login-btn {
            width: 100%;
            padding: 10px;
            background-color: var(--admin-primary);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .login-btn:hover {
            background-color: var(--admin-secondary);
        }

        .reset-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: var(--admin-primary);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .stat-card {
            background-color: var(--primary-color);
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            margin-bottom: 10px;
            color: var(--dark-text);
        }

        .stat-card p {
            font-size: 2rem;
            font-weight: bold;
            color: var(--button-bg);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            header .container {
                flex-direction: column;
                text-align: center;
            }

            header nav ul li {
                margin: 0 10px;
                display: block;
                padding: 5px 0;
            }

            .logo {
                margin-bottom: 10px;
            }

            .hero-content h2 {
                font-size: 2.5rem;
            }

            .hero-content p {
                font-size: 1.2rem;
            }

            .product-grid {
                grid-template-columns: 1fr;
            }

            .cart-panel {
                width: 100%;
                right: -100%;
            }

            .cart-panel.open {
                right: 0;
            }

            .checkout-section form, .contact-section form {
                padding: 20px;
            }

            .admin-nav ul {
                flex-direction: column;
            }

            .admin-nav ul li {
                margin-bottom: 10px;
            }
        }

        @media (max-width: 480px) {
            header nav ul li a {
                font-size: 1rem;
            }

            .hero-content {
                padding: 20px;
            }

            .hero-content h2 {
                font-size: 2rem;
            }

            .hero-content p {
                font-size: 1rem;
            }

            .shop-now-btn {
                padding: 10px 20px;
                font-size: 1rem;
            }

            .products-section h2, .checkout-section h2, .about-section h2, .contact-section h2 {
                font-size: 2rem;
            }

            .contact-info p {
                font-size: 0.95rem;
            }

            .social-links a {
                padding: 6px 10px;
                font-size: 0.9rem;
                margin: 5px;
            }
        }
    </style>
</head>
<body>

<?php if (isset($_SESSION['admin_logged_in'])): ?>
    <!-- Admin Panel -->
    <header>
        <div class="container">
            <h1 class="logo">Mura Cosmetics Admin</h1>
            <nav>
                <ul>
                    <li><a href="?admin">Dashboard</a></li>
                    <li><a href="?admin=products">Products</a></li>
                    <li><a href="?admin=orders">Orders</a></li>
                    <li><a href="?logout">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="admin-panel">
        <div class="container">
            <?php if (isset($admin_message)): ?>
                <div class="message success"><?php echo $admin_message; ?></div>
            <?php endif; ?>

            <?php if (!isset($_GET['admin']) || $_GET['admin'] === ''): ?>
                <!-- Admin Dashboard -->
                <div class="admin-section">
                    <h2>Dashboard</h2>
                    <p>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</p>
                    
                    <div class="stats-grid">
                        <?php
                        // Get product count
                        $product_count = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
                        
                        // Get order count
                        $order_count = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
                        
                        // Get pending orders
                        $pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];
                        
                        // Get total revenue
                        $revenue = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'")->fetch_assoc()['total'];
                        $revenue = $revenue ? number_format($revenue, 2) : '0.00';
                        ?>
                        
                        <div class="stat-card">
                            <h3>Total Products</h3>
                            <p><?php echo $product_count; ?></p>
                        </div>
                        
                        <div class="stat-card">
                            <h3>Total Orders</h3>
                            <p><?php echo $order_count; ?></p>
                        </div>
                        
                        <div class="stat-card">
                            <h3>Pending Orders</h3>
                            <p><?php echo $pending_orders; ?></p>
                        </div>
                        
                        <div class="stat-card">
                            <h3>Total Revenue</h3>
                            <p>$<?php echo $revenue; ?></p>
                        </div>
                    </div>
                </div>
                
            <?php elseif ($_GET['admin'] === 'products'): ?>
                <!-- Products Management -->
                <div class="admin-section">
                    <h2>Products Management</h2>
                    
                    <div class="admin-nav">
                        <ul>
                            <li><a href="?admin=products">All Products</a></li>
                            <li><a href="?admin=products&action=add">Add New Product</a></li>
                        </ul>
                    </div>
                    
                    <?php if (isset($_GET['action']) && $_GET['action'] === 'add'): ?>
                        <!-- Add Product Form -->
                        <div class="admin-section">
                            <h3>Add New Product</h3>
                            <form method="POST" class="admin-form">
                                <input type="hidden" name="add_product" value="1">
                                <div class="form-group">
                                    <label for="name">Product Name:</label>
                                    <input type="text" id="name" name="name" required>
                                </div>
                                <div class="form-group">
                                    <label for="description">Description:</label>
                                    <textarea id="description" name="description" rows="4"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="price">Price:</label>
                                    <input type="number" id="price" name="price" step="0.01" min="0" required>
                                </div>
                                <div class="form-group">
                                    <label for="stock">Stock Quantity:</label>
                                    <input type="number" id="stock" name="stock" min="0" required>
                                </div>
                                <div class="form-group">
                                    <label for="image_url">Image URL:</label>
                                    <input type="text" id="image_url" name="image_url" placeholder="https://example.com/image.jpg">
                                </div>
                                <button type="submit" class="btn admin-btn">Add Product</button>
                            </form>
                        </div>
                        
                    <?php elseif (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])): ?>
                        <!-- Edit Product Form -->
                        <?php
                        $product_id = intval($_GET['id']);
                        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                        $stmt->bind_param("i", $product_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $product = $result->fetch_assoc();
                        $stmt->close();
                        ?>
                        <div class="admin-section">
                            <h3>Edit Product</h3>
                            <form method="POST" class="admin-form">
                                <input type="hidden" name="update_product" value="1">
                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                <div class="form-group">
                                    <label for="name">Product Name:</label>
                                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="description">Description:</label>
                                    <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="price">Price:</label>
                                    <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo $product['price']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="stock">Stock Quantity:</label>
                                    <input type="number" id="stock" name="stock" min="0" value="<?php echo $product['stock']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="image_url">Image URL:</label>
                                    <input type="text" id="image_url" name="image_url" value="<?php echo htmlspecialchars($product['image_url']); ?>" placeholder="https://example.com/image.jpg">
                                </div>
                                <button type="submit" class="btn admin-btn">Update Product</button>
                            </form>
                        </div>
                        
                    <?php else: ?>
                        <!-- Products List -->
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $products = $conn->query("SELECT * FROM products ORDER BY id DESC");
                                while ($product = $products->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo $product['id']; ?></td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td>$<?php echo number_format($product['price'], 2); ?></td>
                                    <td><?php echo $product['stock']; ?></td>
                                    <td>
                                        <a href="?admin=products&action=edit&id=<?php echo $product['id']; ?>" class="btn admin-btn">Edit</a>
                                        <a href="?admin=products&delete_product=<?php echo $product['id']; ?>" class="btn" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                
            <?php elseif ($_GET['admin'] === 'orders'): ?>
                <!-- Orders Management -->
                <div class="admin-section">
                    <h2>Orders Management</h2>
                    
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
                            while ($order = $orders->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <input type="hidden" name="update_order_status" value="1">
                                    </form>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <a href="?admin=orders&view=<?php echo $order['id']; ?>" class="btn admin-btn">View</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    
                    <?php if (isset($_GET['view'])): ?>
                        <!-- Order Details -->
                        <?php
                        $order_id = intval($_GET['view']);
                        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
                        $stmt->bind_param("i", $order_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $order = $result->fetch_assoc();
                        $stmt->close();
                        $order_details = json_decode($order['order_details'], true);
                        ?>
                        <div class="admin-section">
                            <h3>Order Details #<?php echo $order['id']; ?></h3>
                            
                            <div class="order-info">
                                <h4>Customer Information</h4>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                                <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($order['customer_address'])); ?></p>
                                <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                                <p><strong>Order Date:</strong> <?php echo date('M j, Y H:i', strtotime($order['created_at'])); ?></p>
                                <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
                            </div>
                            
                            <div class="order-items">
                                <h4>Order Items</h4>
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($order_details): ?>
                                            <?php foreach ($order_details as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3">Total</th>
                                            <th>$<?php echo number_format($order['total_amount'], 2); ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php elseif (isset($_GET['token'])): ?>
    <!-- Password Reset Form -->
    <div class="login-container">
        <h2>Reset Your Password</h2>
        
        <?php if (isset($reset_error)): ?>
            <div class="message error"><?php echo $reset_error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($reset_success)): ?>
            <div class="message success"><?php echo $reset_success; ?></div>
        <?php else: ?>
            <form method="POST" class="login-form">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                <div class="form-group">
                    <label for="password">New Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" name="reset_password" class="login-btn">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>

<?php elseif (!isset($_SESSION['admin_logged_in'])): ?>
    <!-- Admin Login Form -->
    <div class="login-container">
        <h2>Admin Login</h2>
        
        <?php if (isset($login_error)): ?>
            <div class="message error"><?php echo $login_error; ?></div>
        <?php endif; ?>
        
        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" name="admin_login" class="login-btn">Login</button>
        </form>
        
        <a href="#" onclick="document.getElementById('reset-form').style.display='block'; return false;" class="reset-link">Forgot Password?</a>
        
        <div id="reset-form" style="display: none; margin-top: 20px;">
            <h3>Reset Password</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="email">Admin Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <button type="submit" name="reset_request" class="login-btn">Request Reset Link</button>
            </form>
            
            <?php if (isset($reset_success)): ?>
                <div class="message success"><?php echo $reset_success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($reset_error)): ?>
                <div class="message error"><?php echo $reset_error; ?></div>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>
    <!-- Main Store Front -->
    <header>
        <div class="container">
            <h1 class="logo">Mura Cosmetics</h1>
            <nav>
                <ul>
                    <li><a href="#hero">Home</a></li>
                    <li><a href="#products">Products</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <li><a href="#" id="cart-icon">🛒 Cart (<span id="cart-count">0</span>)</a></li>
                    <li><a href="?admin">Admin</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section id="hero" class="hero-section">
            <div class="hero-content">
                <h2>Welcome to Mura Cosmetics</h2>
                <p>Discover your natural beauty with our exquisite collection.</p>
                <a href="#products" class="btn shop-now-btn">Shop Now</a>
            </div>
        </section>

        <section id="products" class="products-section">
            <div class="container">
                <h2>Our Products</h2>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                    <div class="product-item" data-id="<?php echo $product['id']; ?>" data-name="<?php echo htmlspecialchars($product['name']); ?>" data-price="<?php echo $product['price']; ?>">
                        <img src="<?php echo $product['image_url'] ? htmlspecialchars($product['image_url']) : 'https://via.placeholder.com/200x200/FFDDCC/884433?text='.urlencode($product['name']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                        <button class="add-to-cart-btn">Add to Cart</button>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($products)): ?>
                        <p>No products available at the moment. Please check back later.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <div id="cart-panel" class="cart-panel">
            <div class="cart-header">
                <h3>Your Cart</h3>
                <button class="close-cart-btn">&times;</button>
            </div>
            <div class="cart-items" id="cart-items-container">
                <p>Your cart is empty.</p>
            </div>
            <div class="cart-summary">
                <p>Total: $<span id="cart-total">0.00</span></p>
                <button class="btn checkout-btn">Proceed to Checkout</button>
            </div>
        </div>

        <section id="checkout" class="checkout-section">
            <div class="container">
                <h2>Checkout</h2>
                <?php if (isset($order_success)): ?>
                    <div id="order-message" class="success">
                        Thank you for your order! We'll process it shortly and contact you for confirmation.
                    </div>
                <?php else: ?>
                    <form id="checkout-form" method="POST">
                        <input type="hidden" name="place_order" value="1">
                        <div class="form-group">
                            <label for="fullName">Full Name:</label>
                            <input type="text" id="fullName" name="fullName" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone:</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                        <div class="form-group">
                            <label for="address">Address:</label>
                            <textarea id="address" name="address" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="paymentMethod">Payment Method:</label>
                            <select id="paymentMethod" name="paymentMethod" required>
                                <option value="Cash on Delivery">Cash on Delivery</option>
                            </select>
                        </div>
                        <input type="hidden" id="order-details-input" name="orderDetails">
                        <button type="submit" class="btn submit-order-btn">Place Order</button>
                    </form>
                <?php endif; ?>
            </div>
        </section>

        <section id="about" class="about-section">
            <div class="container">
                <h2>About Mura Cosmetics</h2>
                <p>At <strong>Mura Cosmetics</strong>, we believe in enhancing your natural beauty through high-quality, ethically sourced products. Our mission is to provide cosmetics that make you feel confident, radiant, and empowered.</p>
                <p>We are committed to using premium ingredients, ensuring sustainable practices, and creating products that are gentle on your skin and the environment. Choose Mura Cosmetics for a beauty experience that's truly personal and responsible.</p>
                <p>Our values are rooted in <strong>quality, integrity, and customer satisfaction</strong>. We strive to innovate and bring you the best in beauty, always with your well-being in mind.</p>
            </div>
        </section>

        <section id="contact" class="contact-section">
            <div class="container">
                <h2>Contact Us</h2>
                <div class="contact-info">
                    <p>📧 Email: <a href="mailto:mura.cosmetics@gmail.com">mura.cosmetics@gmail.com</a></p>
                    <p>📱 WhatsApp: <a href="https://wa.me/250790405655" target="_blank">+250790405655</a></p>
                    <div class="social-links">
                        <a href="https://www.instagram.com/muracosmetics" target="_blank">Instagram</a>
                        <a href="https://www.facebook.com/muracosmetics" target="_blank">Facebook</a>
                        <a href="https://www.tiktok.com/@muracosmetics" target="_blank">TikTok</a>
                    </div>
                </div>

                <?php if (isset($contact_success)): ?>
                    <div id="contact-message" class="success">
                        <?php echo $contact_success; ?>
                    </div>
                <?php else: ?>
                    <form id="contact-form" method="POST">
                        <h3>Send us a message</h3>
                        <div class="form-group">
                            <label for="contactName">Name:</label>
                            <input type="text" id="contactName" name="contactName" required>
                        </div>
                        <div class="form-group">
                            <label for="contactEmail">Email:</label>
                            <input type="email" id="contactEmail" name="contactEmail" required>
                        </div>
                        <div class="form-group">
                            <label for="contactMessage">Message:</label>
                            <textarea id="contactMessage" name="contactMessage" rows="5" required></textarea>
                        </div>
                        <button type="submit" name="contact_submit" class="btn submit-contact-btn">Send Message</button>
                    </form>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Mura Cosmetics. All rights reserved.</p>
            <p><a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>
        </div>
    </footer>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Smooth scrolling for navigation links and "Shop Now" button
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();

                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    const headerOffset = document.querySelector('header').offsetHeight; // Get header height
                    const elementPosition = targetElement.getBoundingClientRect().top + window.pageYOffset;
                    const offsetPosition = elementPosition - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });

                    // Close cart panel if open and clicked on navigation link
                    const cartPanel = document.getElementById('cart-panel');
                    if (cartPanel && cartPanel.classList.contains('open') && targetId !== '#cart') {
                        cartPanel.classList.remove('open');
                    }
                }
            });
        });

        // Only run cart functionality if we're on the store front
        if (document.getElementById('cart-icon')) {
            // Cart functionality
            const cartIcon = document.getElementById('cart-icon');
            const cartPanel = document.getElementById('cart-panel');
            const closeCartBtn = document.querySelector('.close-cart-btn');
            const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
            const cartItemsContainer = document.getElementById('cart-items-container');
            const cartTotalSpan = document.getElementById('cart-total');
            const cartCountSpan = document.getElementById('cart-count');
            const checkoutBtn = document.querySelector('.checkout-btn');
            const orderDetailsInput = document.getElementById('order-details-input');

            let cart = JSON.parse(localStorage.getItem('muraCart')) || [];

            function saveCart() {
                localStorage.setItem('muraCart', JSON.stringify(cart));
            }

            function updateCartCount() {
                cartCountSpan.textContent = cart.reduce((sum, item) => sum + item.quantity, 0);
            }

            function renderCart() {
                cartItemsContainer.innerHTML = '';
                if (cart.length === 0) {
                    cartItemsContainer.innerHTML = '<p>Your cart is empty.</p>';
                    cartTotalSpan.textContent = '0.00';
                    return;
                }

                let total = 0;
                cart.forEach(item => {
                    const cartItemDiv = document.createElement('div');
                    cartItemDiv.classList.add('cart-item');
                    cartItemDiv.innerHTML = `
                        <img src="${item.image}" alt="${item.name}">
                        <div class="cart-item-details">
                            <h4>${item.name}</h4>
                            <p class="price">$${item.price.toFixed(2)}</p>
                        </div>
                        <div class="cart-item-actions">
                            <input type="number" value="${item.quantity}" min="1" data-id="${item.id}" class="item-quantity">
                            <button class="remove-item-btn" data-id="${item.id}">Remove</button>
                        </div>
                    `;
                    cartItemsContainer.appendChild(cartItemDiv);
                    total += item.price * item.quantity;
                });
                cartTotalSpan.textContent = total.toFixed(2);
                updateCartCount();
                saveCart();
            }

            function addToCart(product) {
                const existingItem = cart.find(item => item.id === product.id);
                if (existingItem) {
                    existingItem.quantity++;
                } else {
                    cart.push({ ...product, quantity: 1 });
                }
                renderCart();
            }

            function updateQuantity(id, newQuantity) {
                const item = cart.find(item => item.id === id);
                if (item) {
                    item.quantity = parseInt(newQuantity);
                    if (item.quantity <= 0) {
                        removeFromCart(id);
                    }
                    renderCart();
                }
            }

            function removeFromCart(id) {
                cart = cart.filter(item => item.id !== id);
                renderCart();
            }

            // Event listeners for cart
            cartIcon.addEventListener('click', (e) => {
                e.preventDefault();
                cartPanel.classList.toggle('open');
                renderCart(); // Re-render cart every time it opens to ensure freshness
            });

            closeCartBtn.addEventListener('click', () => {
                cartPanel.classList.remove('open');
            });

            addToCartButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    const productElement = e.target.closest('.product-item');
                    const product = {
                        id: productElement.dataset.id,
                        name: productElement.dataset.name,
                        price: parseFloat(productElement.dataset.price),
                        image: productElement.querySelector('img').src
                    };
                    addToCart(product);
                    cartPanel.classList.add('open'); // Open cart panel when item is added
                });
            });

            cartItemsContainer.addEventListener('change', (e) => {
                if (e.target.classList.contains('item-quantity')) {
                    const id = e.target.dataset.id;
                    const newQuantity = e.target.value;
                    updateQuantity(id, newQuantity);
                }
            });

            cartItemsContainer.addEventListener('click', (e) => {
                if (e.target.classList.contains('remove-item-btn')) {
                    const id = e.target.dataset.id;
                    removeFromCart(id);
                }
            });

            checkoutBtn.addEventListener('click', () => {
                if (cart.length === 0) {
                    alert('Your cart is empty. Please add products before checking out.');
                    return;
                }
                cartPanel.classList.remove('open');
                const headerOffset = document.querySelector('header').offsetHeight;
                const checkoutSection = document.getElementById('checkout');
                const elementPosition = checkoutSection.getBoundingClientRect().top + window.pageYOffset;
                const offsetPosition = elementPosition - headerOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });

                // Populate hidden input with cart details
                orderDetailsInput.value = JSON.stringify(cart);
            });

            // Initialize cart on page load
            renderCart();
        }
    });
</script>
</body>
</html>