# Mura Cosmetics E-Commerce Application

A complete PHP-based e-commerce solution for cosmetics retail with admin panel, shopping cart, and order management.

## Features

### Customer Features
- **Product Catalog**: Browse cosmetics with images, descriptions, and pricing
- **Shopping Cart**: Add/remove items, adjust quantities, local storage persistence
- **Checkout System**: Complete order form with customer details
- **Contact Form**: Direct communication with the business
- **Responsive Design**: Mobile-friendly interface
- **Smooth Navigation**: Single-page experience with smooth scrolling

### Admin Features
- **Secure Login**: Password-protected admin access with reset functionality
- **Product Management**: Add, edit, delete products with stock tracking
- **Order Management**: View orders, update status, detailed order information
- **Dashboard**: Statistics overview (products, orders, revenue)
- **Inventory Tracking**: Real-time stock management

### Security Features
- **SQL Injection Prevention**: Prepared statements throughout
- **Password Hashing**: Secure bcrypt password storage
- **XSS Protection**: HTML escaping for all user inputs
- **Session Management**: Secure admin authentication
- **Input Validation**: Server-side validation for all forms

## Installation

### Prerequisites
- **Web Server**: Apache/Nginx with PHP support
- **PHP**: Version 7.4 or higher
- **MySQL**: Version 5.7 or higher
- **Extensions**: mysqli, json, session

### Setup Instructions

1. **Clone/Download the Application**
   ```bash
   # Place files in your web server directory
   # For XAMPP: htdocs/mura-cosmetics/
   # For other servers: public_html/ or www/
   ```

2. **Database Setup**
   ```bash
   # Method 1: Using MySQL Command Line
   mysql -u root -p < database_setup.sql
   
   # Method 2: Using phpMyAdmin
   # - Open phpMyAdmin
   # - Import database_setup.sql file
   # - Or copy and paste the SQL content
   ```

3. **Configure Database Connection**
   
   Edit the database configuration in `mura_cosmetics.php` if needed:
   ```php
   define('DB_HOST', 'localhost');     // Your MySQL host
   define('DB_USER', 'root');          // Your MySQL username
   define('DB_PASS', '');              // Your MySQL password
   define('DB_NAME', 'mura_cosmetics'); // Database name
   ```

4. **Set File Permissions**
   ```bash
   # On Linux/Mac, ensure proper permissions
   chmod 644 mura_cosmetics.php
   chmod 644 database_setup.sql
   ```

5. **Access the Application**
   - **Customer Store**: `http://localhost/mura-cosmetics/mura_cosmetics.php`
   - **Admin Panel**: `http://localhost/mura-cosmetics/mura_cosmetics.php?admin`

## Default Admin Credentials

- **Username**: `mura`
- **Password**: `Delphin@1gisenyi`
- **Email**: `ngarambedelp@icloud.com`

⚠️ **Important**: Change the default admin password after first login!

## Database Structure

### Tables Created
- **users**: Admin user management
- **products**: Product catalog and inventory
- **orders**: Customer orders and details
- **order_items**: Detailed order line items (optional)
- **contact_messages**: Customer inquiries (optional)

### Sample Data Included
- 10 sample cosmetic products
- 3 sample customer orders
- 1 admin user account

## Usage Guide

### For Customers
1. **Browse Products**: Scroll to the products section
2. **Add to Cart**: Click "Add to Cart" on desired products
3. **View Cart**: Click the cart icon in the header
4. **Checkout**: Click "Proceed to Checkout" and fill out the form
5. **Contact**: Use the contact form for inquiries

### For Administrators
1. **Login**: Visit `?admin` URL and login with credentials
2. **Dashboard**: View business statistics and metrics
3. **Manage Products**: 
   - Add new products with details and stock
   - Edit existing product information
   - Delete products no longer available
4. **Manage Orders**:
   - View all customer orders
   - Update order status (pending → processing → shipped → completed)
   - View detailed order information
5. **Password Reset**: Use the "Forgot Password" feature if needed

## File Structure

```
mura-cosmetics/
├── mura_cosmetics.php      # Main application file
├── database_setup.sql      # Database schema and sample data
└── README.md              # This documentation
```

## Customization

### Styling
- All CSS is embedded in the main PHP file
- Color scheme uses CSS custom properties (variables)
- Responsive design with mobile breakpoints

### Adding Features
- Payment gateway integration
- Email notifications
- Product categories and filtering
- User registration and accounts
- Product reviews and ratings
- Inventory alerts

### Color Scheme
```css
:root {
    --primary-color: #FFDDCC;    /* Light peach/beige */
    --secondary-color: #F8C8DC;  /* Soft pink */
    --accent-color: #E0BBE4;     /* Muted lavender */
    --button-bg: #884433;        /* Darker brown/red */
    --admin-primary: #4a6fa5;    /* Admin blue */
}
```

## Security Considerations

1. **Change Default Credentials**: Update admin password immediately
2. **Database Security**: Use strong MySQL passwords
3. **File Permissions**: Ensure proper server file permissions
4. **HTTPS**: Use SSL certificates in production
5. **Backup**: Regular database backups recommended
6. **Updates**: Keep PHP and MySQL updated

## Troubleshooting

### Common Issues

**Database Connection Error**
- Check MySQL service is running
- Verify database credentials
- Ensure database exists

**Products Not Displaying**
- Check database has sample products
- Verify MySQL connection
- Check for PHP errors

**Admin Login Issues**
- Verify username: `mura`
- Verify password: `Delphin@1gisenyi`
- Check database users table

**Cart Not Working**
- Enable JavaScript in browser
- Check browser localStorage support
- Clear browser cache

## Support

For technical support or questions:
- **Email**: ngarambedelp@icloud.com
- **WhatsApp**: +250790405655

## License

This project is created for Mura Cosmetics. All rights reserved.

## Version History

- **v1.0**: Initial release with core e-commerce functionality
- **v1.1**: Added security improvements and bug fixes
- **v1.2**: Enhanced admin panel and responsive design
