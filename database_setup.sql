-- PhotoLens Photography Website Database Setup
-- This script creates the complete database structure with sample data

-- Create database
CREATE DATABASE IF NOT EXISTS photography_db;
USE photography_db;

-- Set charset
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =============================================
-- TABLE STRUCTURE
-- =============================================

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Photos table
CREATE TABLE IF NOT EXISTS photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image_path VARCHAR(500) NOT NULL,
    category_id INT,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Testimonials table
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(100) NOT NULL,
    event_type VARCHAR(100),
    content TEXT NOT NULL,
    is_approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(100) NOT NULL,
    client_email VARCHAR(100) NOT NULL,
    client_phone VARCHAR(20),
    event_type VARCHAR(100) NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    location VARCHAR(255),
    message TEXT,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Contact messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Blog posts table
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    excerpt TEXT,
    featured_image VARCHAR(500),
    author_id INT,
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Products table (for shop)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_path VARCHAR(500),
    category VARCHAR(100),
    is_digital BOOLEAN DEFAULT FALSE,
    stock_quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Newsletter subscribers table
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Photo likes table (for user favorites)
CREATE TABLE IF NOT EXISTS photo_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    photo_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (photo_id) REFERENCES photos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_like (user_id, photo_id)
);

-- Comments table (for blog posts)
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT,
    user_id INT,
    content TEXT NOT NULL,
    is_approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Settings table (for site configuration)
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- SAMPLE DATA
-- =============================================

-- Insert default users
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@photolens.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('demo', 'demo@photolens.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Insert categories
INSERT INTO categories (name, description) VALUES
('Weddings', 'Wedding photography and ceremonies'),
('Portraits', 'Individual and family portraits'),
('Events', 'Corporate and social events'),
('Commercial', 'Business and product photography'),
('Nature', 'Landscape and nature photography'),
('Street', 'Street photography and urban scenes');

-- Insert sample photos
INSERT INTO photos (title, description, image_path, category_id, is_featured) VALUES
('Elegant Wedding Ceremony', 'Beautiful outdoor wedding ceremony captured in golden hour light', 'uploads/wedding1.jpg', 1, 1),
('Romantic Couple Portrait', 'Intimate portrait session with natural lighting', 'uploads/wedding2.jpg', 1, 1),
('Corporate Headshot', 'Professional business portrait for executive team', 'uploads/portrait1.jpg', 2, 0),
('Family Portrait Session', 'Joyful family portrait in natural outdoor setting', 'uploads/portrait2.jpg', 2, 1),
('Conference Photography', 'Dynamic event coverage of annual tech conference', 'uploads/event1.jpg', 3, 0),
('Product Photography', 'High-end product photography for luxury brand', 'uploads/commercial1.jpg', 4, 0),
('Sunset Landscape', 'Breathtaking sunset over mountain landscape', 'uploads/nature1.jpg', 5, 1),
('Urban Street Scene', 'Vibrant street photography capturing city life', 'uploads/street1.jpg', 6, 0),
('Wedding Reception', 'Celebration moments from wedding reception', 'uploads/wedding3.jpg', 1, 0),
('Senior Portrait', 'Graduation portrait session in urban setting', 'uploads/portrait3.jpg', 2, 0),
('Corporate Event', 'Annual company meeting and awards ceremony', 'uploads/event2.jpg', 3, 0),
('Fashion Photography', 'High-fashion editorial photography session', 'uploads/commercial2.jpg', 4, 0);

-- Insert testimonials
INSERT INTO testimonials (client_name, event_type, content, is_approved) VALUES
('Sarah Johnson', 'Wedding', 'The photos from our wedding day are absolutely stunning! Every moment was captured perfectly. The photographer was professional, creative, and made us feel comfortable throughout the entire day.', 1),
('Michael Chen', 'Corporate Event', 'Professional service and amazing results. The team captured our corporate event beautifully, and the photos perfectly represent our company culture. Highly recommend for any business event.', 1),
('Emma Wilson', 'Family Portrait', 'Beautiful family portraits that we will treasure forever. The photographer was great with our kids and managed to capture genuine smiles and natural moments. Thank you for these precious memories!', 1),
('David Rodriguez', 'Wedding', 'Outstanding wedding photography! The attention to detail and artistic vision exceeded our expectations. We received our photos quickly and the quality was exceptional.', 1),
('Lisa Thompson', 'Portrait Session', 'Amazing portrait session! The photographer made me feel comfortable and confident. The final images are beautiful and I love how natural they look.', 1),
('James Parker', 'Commercial', 'Excellent commercial photography for our product launch. The images perfectly captured our brand aesthetic and helped increase our sales significantly.', 1);

-- Insert sample bookings
INSERT INTO bookings (client_name, client_email, client_phone, event_type, event_date, event_time, location, message, status) VALUES
('Alice Brown', 'alice@example.com', '555-0101', 'Wedding', '2024-06-15', '14:00:00', 'Central Park, New York', 'Looking for full day wedding coverage including ceremony and reception', 'confirmed'),
('Robert Davis', 'robert@example.com', '555-0102', 'Corporate', '2024-05-20', '09:00:00', 'Downtown Convention Center', 'Annual company conference, need event photography coverage', 'pending'),
('Maria Garcia', 'maria@example.com', '555-0103', 'Portrait', '2024-05-10', '16:00:00', 'Studio Location', 'Family portrait session for 6 people including grandparents', 'confirmed'),
('Thomas Wilson', 'thomas@example.com', '555-0104', 'Wedding', '2024-07-22', '13:30:00', 'Beachside Resort', 'Destination wedding, need photographer for 2 days', 'pending'),
('Jennifer Lee', 'jennifer@example.com', '555-0105', 'Graduation', '2024-05-25', '11:00:00', 'University Campus', 'Graduation photos for my daughter', 'confirmed');

-- Insert contact messages
INSERT INTO contact_messages (name, email, subject, message, is_read) VALUES
('Alex Johnson', 'alex@example.com', 'Wedding Photography', 'Hi, I am interested in your wedding photography packages. Could you please send me more information about pricing and availability for September 2024?', 0),
('Rachel Green', 'rachel@example.com', 'Portrait Session', 'I would like to book a family portrait session. We have 4 adults and 2 children. What would be the best package for us?', 1),
('Mark Taylor', 'mark@example.com', 'Commercial Photography', 'Our company needs product photography for our new catalog. Can you handle bulk product photography? Please send pricing details.', 0),
('Susan White', 'susan@example.com', 'Event Photography', 'We are organizing a charity gala and need event photography coverage. The event is on March 15th. Are you available?', 1);

-- Insert blog posts
INSERT INTO blog_posts (title, content, excerpt, featured_image, author_id, status) VALUES
('10 Tips for Perfect Wedding Photography', 'Wedding photography is an art that requires skill, patience, and creativity. Here are my top 10 tips for capturing perfect wedding moments...', 'Essential tips for capturing beautiful wedding moments that couples will treasure forever.', 'uploads/blog1.jpg', 1, 'published'),
('The Art of Portrait Photography', 'Portrait photography is about capturing the essence of a person. It goes beyond just taking a photo - it is about telling a story...', 'Discover the techniques and approaches that make portrait photography truly compelling.', 'uploads/blog2.jpg', 1, 'published'),
('Behind the Scenes: Corporate Event Photography', 'Corporate events require a different approach than other types of photography. Here is what goes into creating compelling corporate event photos...', 'A look into the world of corporate event photography and what makes it unique.', 'uploads/blog3.jpg', 1, 'published'),
('Lighting Techniques for Stunning Photos', 'Lighting is the foundation of great photography. Understanding how to work with natural and artificial light can transform your images...', 'Master the art of lighting to create professional-quality photographs.', 'uploads/blog4.jpg', 1, 'draft');

-- Insert products (for shop)
INSERT INTO products (name, description, price, image_path, category, is_digital, stock_quantity) VALUES
('Wedding Photo Package - Digital', 'Complete wedding photo collection with 200+ edited photos in high resolution', 299.99, 'uploads/product1.jpg', 'Digital Downloads', 1, 999),
('Portrait Session Print Package', 'Professional portrait prints - 8x10 and 5x7 sizes, premium paper', 89.99, 'uploads/product2.jpg', 'Prints', 0, 50),
('Canvas Print - Large', 'High-quality canvas print of your favorite photo - 24x36 inches', 149.99, 'uploads/product3.jpg', 'Canvas Prints', 0, 25),
('Photo Album - Premium Leather', 'Handcrafted leather photo album with 50 pages', 199.99, 'uploads/product4.jpg', 'Albums', 0, 15),
('Event Photography Package', 'Complete event coverage with 100+ edited photos', 199.99, 'uploads/product5.jpg', 'Digital Downloads', 1, 999),
('Metal Print - Medium', 'Vibrant metal print with modern finish - 16x20 inches', 79.99, 'uploads/product6.jpg', 'Metal Prints', 0, 30);

-- Insert sample orders
INSERT INTO orders (user_id, total_amount, status, shipping_address) VALUES
(2, 299.99, 'delivered', '123 Main St, New York, NY 10001'),
(3, 149.99, 'processing', '456 Oak Ave, Los Angeles, CA 90210'),
(4, 89.99, 'shipped', '789 Pine Rd, Chicago, IL 60601');

-- Insert order items
INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
(1, 1, 1, 299.99),
(2, 3, 1, 149.99),
(3, 2, 1, 89.99);

-- Insert newsletter subscribers
INSERT INTO newsletter_subscribers (email) VALUES
('subscriber1@example.com'),
('subscriber2@example.com'),
('subscriber3@example.com'),
('subscriber4@example.com'),
('subscriber5@example.com');

-- Insert sample comments
INSERT INTO comments (post_id, user_id, content, is_approved) VALUES
(1, 2, 'Great tips! I especially found the lighting advice helpful for my own photography.', 1),
(1, 3, 'Thank you for sharing these insights. As someone planning a wedding, this is very informative.', 1),
(2, 4, 'Beautiful work! Your portrait style is exactly what I am looking for.', 1),
(3, 2, 'Very professional approach to corporate photography. Well written article.', 1);

-- Insert site settings
INSERT INTO settings (setting_key, setting_value) VALUES
('site_title', 'PhotoLens - Professional Photography Services'),
('site_description', 'Professional photography services for weddings, portraits, events, and commercial projects.'),
('contact_email', 'info@photolens.com'),
('contact_phone', '(555) 123-4567'),
('contact_address', '123 Photography St, Creative District, City, State 12345'),
('business_hours', 'Monday - Friday: 9:00 AM - 6:00 PM, Saturday: 10:00 AM - 4:00 PM, Sunday: By appointment'),
('facebook_url', 'https://facebook.com/photolens'),
('instagram_url', 'https://instagram.com/photolens'),
('twitter_url', 'https://twitter.com/photolens'),
('linkedin_url', 'https://linkedin.com/company/photolens');

-- =============================================
-- INDEXES FOR PERFORMANCE
-- =============================================

-- Add indexes for better performance
CREATE INDEX idx_photos_category ON photos(category_id);
CREATE INDEX idx_photos_featured ON photos(is_featured);
CREATE INDEX idx_bookings_date ON bookings(event_date);
CREATE INDEX idx_bookings_status ON bookings(status);
CREATE INDEX idx_blog_posts_status ON blog_posts(status);
CREATE INDEX idx_blog_posts_author ON blog_posts(author_id);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_contact_messages_read ON contact_messages(is_read);
CREATE INDEX idx_testimonials_approved ON testimonials(is_approved);

-- =============================================
-- VIEWS FOR COMMON QUERIES
-- =============================================

-- View for featured photos with category names
CREATE VIEW featured_photos AS
SELECT 
    p.id,
    p.title,
    p.description,
    p.image_path,
    p.created_at,
    c.name as category_name
FROM photos p
LEFT JOIN categories c ON p.category_id = c.id
WHERE p.is_featured = 1
ORDER BY p.created_at DESC;

-- View for recent blog posts with author names
CREATE VIEW recent_blog_posts AS
SELECT 
    bp.id,
    bp.title,
    bp.excerpt,
    bp.featured_image,
    bp.created_at,
    u.username as author_name
FROM blog_posts bp
LEFT JOIN users u ON bp.author_id = u.id
WHERE bp.status = 'published'
ORDER BY bp.created_at DESC;

-- View for pending bookings
CREATE VIEW pending_bookings AS
SELECT 
    id,
    client_name,
    client_email,
    event_type,
    event_date,
    event_time,
    location,
    created_at
FROM bookings
WHERE status = 'pending'
ORDER BY event_date ASC;

-- =============================================
-- STORED PROCEDURES
-- =============================================

-- Procedure to get photo statistics
DELIMITER //
CREATE PROCEDURE GetPhotoStatistics()
BEGIN
    SELECT 
        COUNT(*) as total_photos,
        COUNT(CASE WHEN is_featured = 1 THEN 1 END) as featured_photos,
        COUNT(DISTINCT category_id) as categories_used
    FROM photos;
END //
DELIMITER ;

-- Procedure to get monthly booking statistics
DELIMITER //
CREATE PROCEDURE GetMonthlyBookingStats(IN year_param INT)
BEGIN
    SELECT 
        MONTH(event_date) as month,
        COUNT(*) as total_bookings,
        COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_bookings,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_bookings
    FROM bookings
    WHERE YEAR(event_date) = year_param
    GROUP BY MONTH(event_date)
    ORDER BY month;
END //
DELIMITER ;

-- =============================================
-- TRIGGERS
-- =============================================

-- Trigger to update photo count when photos are added/removed
DELIMITER //
CREATE TRIGGER update_category_photo_count
AFTER INSERT ON photos
FOR EACH ROW
BEGIN
    -- This is a placeholder for category photo count update
    -- You can implement actual counting logic here
    UPDATE categories SET description = CONCAT(description, ' - Updated') WHERE id = NEW.category_id;
END //
DELIMITER ;

-- =============================================
-- COMPLETION MESSAGE
-- =============================================

SELECT 'Database setup completed successfully!' as message;
SELECT 'Default admin credentials: admin / admin123' as admin_info;
SELECT 'Default demo credentials: demo / demo123' as demo_info;
SELECT COUNT(*) as total_photos FROM photos;
SELECT COUNT(*) as total_categories FROM categories;
SELECT COUNT(*) as total_users FROM users;
SELECT COUNT(*) as total_bookings FROM bookings;