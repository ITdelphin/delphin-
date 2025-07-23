# Mura Cosmetics - Admin Dashboard

A fully functional cosmetics e-commerce admin panel built with HTML, CSS, JavaScript, PHP, and MySQL.

## Features

- **Secure Admin Authentication**: Login with email and password using PHP sessions and password hashing
- **Product Management**: Add, view, edit, and delete cosmetic products
- **Image Upload**: Upload and manage product images with real-time preview
- **Categories**: Pre-loaded cosmetic categories (Skincare, Makeup, Fragrance, Hair Care, Body Care)
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Dashboard Overview**: Statistics and recent products view
- **Single Page Management**: All functions accessible from one centralized dashboard

## Requirements

- XAMPP (Apache + MySQL + PHP)
- Web browser
- VS Code (recommended for development)

## Installation & Setup

### 1. Install XAMPP
Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)

### 2. Copy Files
Copy all project files to: `C:/xampp/htdocs/mura_cosmetics/`

### 3. Start XAMPP Services
- Open XAMPP Control Panel
- Start **Apache** and **MySQL** services

### 4. Create Database
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click "Import" tab
3. Choose file: `create_tables.sql`
4. Click "Go" to create the database and tables

### 5. Set Permissions
Ensure the `uploads/` folder has write permissions:
- Right-click on `uploads` folder
- Properties → Security → Edit
- Give "Full Control" to "Everyone" (Windows)

### 6. Access the Website
Open your browser and go to: `http://localhost/mura_cosmetics`

## Default Login Credentials

- **Email**: admin@muracosmetics.com
- **Password**: admin123

## File Structure

```
mura_cosmetics/
├── db.php                 # Database connection
├── login.php             # Admin login page
├── admin_dashboard.php   # Main admin dashboard
├── logout.php           # Logout functionality
├── index.php            # Redirects to login
├── create_tables.sql    # Database setup script
├── uploads/             # Product images folder
└── README.md            # This file
```

## Database Structure

### Tables Created:
- **admins**: Admin user accounts
- **categories**: Product categories
- **products**: Product information and images

## Usage Instructions

### Logging In
1. Go to `http://localhost/mura_cosmetics`
2. Use the default credentials or create new admin account
3. Click "Login to Dashboard"

### Dashboard Overview
- View total products, categories, and recent additions
- See recently added products with images
- Quick statistics at a glance

### Adding Products
1. Click "Add Product" in navigation
2. Fill in product details:
   - Product Name (required)
   - Category (required)
   - Price (required)
   - Description (optional)
   - Image (optional)
3. Click "Add Product"

### Managing Products
1. Click "Manage Products" in navigation
2. View all products in grid layout
3. Use "Edit" button to modify products
4. Use "Delete" button to remove products
5. Confirm deletion when prompted

### Image Upload
- Supported formats: JPG, JPEG, PNG, GIF
- Images are automatically resized for display
- Real-time preview before upload
- Old images are automatically deleted when updating

## Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection prevention with prepared statements
- XSS protection with `htmlspecialchars()`
- Session-based authentication
- File upload validation
- CSRF protection on forms

## Customization

### Adding New Categories
Edit the `create_tables.sql` file and add new categories:
```sql
INSERT INTO categories (name) VALUES ('New Category');
```

### Changing Admin Credentials
1. Go to phpMyAdmin
2. Select `mura_cosmetics` database
3. Click on `admins` table
4. Edit the admin record or insert new one
5. Use password hash generator for new passwords

### Styling Modifications
All CSS is embedded in the PHP files. Look for `<style>` sections to modify:
- Colors: Change gradient values and color codes
- Layout: Modify grid templates and flexbox properties
- Responsive: Adjust media queries for different screen sizes

## Troubleshooting

### Common Issues:

1. **Database Connection Error**
   - Ensure MySQL is running in XAMPP
   - Check database name in `db.php`
   - Verify database exists

2. **Image Upload Not Working**
   - Check `uploads/` folder permissions
   - Ensure folder exists
   - Verify file size limits in PHP

3. **Login Not Working**
   - Verify database tables exist
   - Check admin credentials
   - Clear browser cache and cookies

4. **Page Not Loading**
   - Ensure Apache is running
   - Check file paths are correct
   - Verify PHP syntax

### File Permissions (Linux/Mac):
```bash
chmod 755 mura_cosmetics/
chmod 777 mura_cosmetics/uploads/
```

## Support

For issues or questions:
1. Check XAMPP error logs
2. Enable PHP error reporting
3. Verify database connections
4. Check browser console for JavaScript errors

## License

This project is for educational and personal use. Feel free to modify and distribute as needed.