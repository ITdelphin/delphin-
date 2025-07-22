-- Mura Cosmetics Database Setup
-- This file creates the database and all necessary tables for the cosmetics e-commerce application

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS mura_cosmetics CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mura_cosmetics;

-- Create users table for admin authentication
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_token_expires DATETIME DEFAULT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create products table for cosmetics inventory
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(500),
    stock INT DEFAULT 0,
    category VARCHAR(50) DEFAULT 'general',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_stock (stock),
    INDEX idx_category (category),
    INDEX idx_active (is_active)
);

-- Create orders table for customer orders
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_address TEXT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'completed', 'cancelled') DEFAULT 'pending',
    order_details TEXT,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_customer_email (customer_email),
    INDEX idx_created_at (created_at)
);

-- Create order_items table for detailed order tracking (optional enhancement)
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
);

-- Create contact_messages table for customer inquiries
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Insert default admin user
-- Password: Delphin@1gisenyi (will be hashed by PHP)
INSERT INTO users (username, password, email, is_admin) VALUES 
('mura', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ngarambedelp@icloud.com', TRUE)
ON DUPLICATE KEY UPDATE 
username = VALUES(username),
email = VALUES(email),
is_admin = VALUES(is_admin);

-- Insert sample products for demonstration
INSERT INTO products (name, description, price, image_url, stock, category) VALUES 
('Hydrating Face Cream', 'A luxurious moisturizing cream that deeply hydrates and nourishes your skin for a radiant glow.', 29.99, 'https://via.placeholder.com/300x300/FFDDCC/884433?text=Face+Cream', 50, 'skincare'),
('Matte Lipstick - Ruby Red', 'Long-lasting matte lipstick in a stunning ruby red shade. Perfect for all occasions.', 19.99, 'https://via.placeholder.com/300x300/F8C8DC/884433?text=Ruby+Lipstick', 75, 'makeup'),
('Vitamin C Serum', 'Brightening vitamin C serum that helps reduce dark spots and evens skin tone.', 39.99, 'https://via.placeholder.com/300x300/E0BBE4/884433?text=Vitamin+C+Serum', 30, 'skincare'),
('Eyeshadow Palette - Sunset', 'A beautiful 12-shade eyeshadow palette with warm sunset tones.', 45.99, 'https://via.placeholder.com/300x300/FFDDCC/884433?text=Eyeshadow+Palette', 25, 'makeup'),
('Gentle Cleanser', 'A mild, sulfate-free cleanser suitable for all skin types.', 24.99, 'https://via.placeholder.com/300x300/F8C8DC/884433?text=Gentle+Cleanser', 60, 'skincare'),
('Foundation - Medium', 'Full coverage foundation with a natural finish. Available in multiple shades.', 34.99, 'https://via.placeholder.com/300x300/E0BBE4/884433?text=Foundation', 40, 'makeup'),
('Anti-Aging Night Cream', 'Rich night cream with retinol and peptides to reduce signs of aging.', 54.99, 'https://via.placeholder.com/300x300/FFDDCC/884433?text=Night+Cream', 20, 'skincare'),
('Mascara - Volume Boost', 'Volumizing mascara that gives your lashes dramatic length and fullness.', 22.99, 'https://via.placeholder.com/300x300/F8C8DC/884433?text=Mascara', 80, 'makeup'),
('Exfoliating Scrub', 'Gentle exfoliating scrub with natural ingredients to reveal smooth, glowing skin.', 27.99, 'https://via.placeholder.com/300x300/E0BBE4/884433?text=Exfoliating+Scrub', 35, 'skincare'),
('Lip Gloss Set', 'Set of 3 high-shine lip glosses in complementary shades.', 32.99, 'https://via.placeholder.com/300x300/FFDDCC/884433?text=Lip+Gloss+Set', 45, 'makeup')
ON DUPLICATE KEY UPDATE 
name = VALUES(name),
description = VALUES(description),
price = VALUES(price),
image_url = VALUES(image_url),
stock = VALUES(stock),
category = VALUES(category);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_products_name ON products(name);
CREATE INDEX IF NOT EXISTS idx_products_price ON products(price);
CREATE INDEX IF NOT EXISTS idx_orders_total ON orders(total_amount);
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);

-- Create a view for order statistics (optional)
CREATE OR REPLACE VIEW order_statistics AS
SELECT 
    DATE(created_at) as order_date,
    COUNT(*) as total_orders,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as average_order_value,
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_orders,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders
FROM orders 
GROUP BY DATE(created_at)
ORDER BY order_date DESC;

-- Create a view for product inventory status
CREATE OR REPLACE VIEW inventory_status AS
SELECT 
    id,
    name,
    price,
    stock,
    category,
    CASE 
        WHEN stock = 0 THEN 'Out of Stock'
        WHEN stock <= 10 THEN 'Low Stock'
        WHEN stock <= 50 THEN 'Medium Stock'
        ELSE 'In Stock'
    END as stock_status,
    created_at,
    updated_at
FROM products
WHERE is_active = TRUE
ORDER BY stock ASC, name;

-- Insert some sample orders for demonstration (optional)
INSERT INTO orders (customer_name, customer_email, customer_phone, customer_address, payment_method, total_amount, status, order_details) VALUES 
('Jane Smith', 'jane.smith@email.com', '+250788123456', '123 Beauty Street, Kigali, Rwanda', 'Cash on Delivery', 89.97, 'completed', '[{"id":"1","name":"Hydrating Face Cream","price":29.99,"quantity":1},{"id":"3","name":"Vitamin C Serum","price":39.99,"quantity":1},{"id":"2","name":"Matte Lipstick - Ruby Red","price":19.99,"quantity":1}]'),
('Alice Johnson', 'alice.j@email.com', '+250789654321', '456 Glow Avenue, Kigali, Rwanda', 'Cash on Delivery', 45.99, 'processing', '[{"id":"4","name":"Eyeshadow Palette - Sunset","price":45.99,"quantity":1}]'),
('Marie Claire', 'marie.c@email.com', '+250787456123', '789 Beauty Boulevard, Kigali, Rwanda', 'Cash on Delivery', 57.98, 'shipped', '[{"id":"6","name":"Foundation - Medium","price":34.99,"quantity":1},{"id":"8","name":"Mascara - Volume Boost","price":22.99,"quantity":1}]')
ON DUPLICATE KEY UPDATE id = id;

-- Show table information
SHOW TABLES;

-- Display table structures
DESCRIBE users;
DESCRIBE products;
DESCRIBE orders;
DESCRIBE order_items;
DESCRIBE contact_messages;

-- Display sample data
SELECT 'Users Table:' as info;
SELECT id, username, email, is_admin, created_at FROM users;

SELECT 'Products Table:' as info;
SELECT id, name, price, stock, category FROM products LIMIT 5;

SELECT 'Orders Table:' as info;
SELECT id, customer_name, total_amount, status, created_at FROM orders LIMIT 3;

-- Show database size and table counts
SELECT 
    'Database Statistics:' as info,
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM products) as total_products,
    (SELECT COUNT(*) FROM orders) as total_orders,
    (SELECT SUM(stock) FROM products) as total_inventory;

COMMIT;