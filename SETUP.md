# PhotoLens Photography Website

A comprehensive photography portfolio and booking website built with PHP, MySQL, HTML, CSS, and JavaScript.

## Features

### 🏠 Frontend Features
- **Modern Homepage** with hero slider, services preview, and testimonials
- **Dynamic Photo Gallery** with category filtering and lightbox
- **Booking System** with calendar availability and form validation
- **Contact Forms** with email notifications
- **User Authentication** with login/registration
- **Responsive Design** for all devices
- **Interactive Elements** with smooth animations

### 🔧 Backend Features
- **Admin Dashboard** for managing photos, bookings, and messages
- **Photo Management** with upload, categorization, and featured photos
- **Booking Management** with status tracking and calendar integration
- **User Management** with role-based access control
- **Database Integration** with MySQL
- **AJAX Functionality** for dynamic content loading

### 📱 Technical Features
- **PHP 7.4+** with PDO for database operations
- **MySQL Database** with proper relationships
- **Responsive CSS Grid/Flexbox** layouts
- **JavaScript ES6+** with modern features
- **Image Upload & Processing** with validation
- **Form Validation** client-side and server-side
- **Security Features** with prepared statements and input sanitization

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

### Setup Instructions

1. **Clone or Download** the project files to your web server directory

2. **Database Setup**
   - Create a MySQL database named `photography_db`
   - The database tables will be created automatically when you first run the application

3. **Configuration**
   - Open `config/database.php`
   - Update the database connection settings:
   ```php
   $host = 'localhost';        // Your database host
   $dbname = 'photography_db'; // Your database name
   $username = 'your_username'; // Your database username
   $password = 'your_password'; // Your database password
   ```

4. **File Permissions**
   - Create an `uploads` directory in the root folder
   - Set write permissions (755 or 777) for the uploads directory:
   ```bash
   mkdir uploads
   chmod 755 uploads
   ```

5. **Access the Website**
   - Open your web browser and navigate to your website URL
   - The database tables will be created automatically on first visit

## Default Login Credentials

### Admin Account
- **Username:** `admin`
- **Password:** `admin123`
- **Access:** Full admin dashboard with all management features

### Demo User Account
- **Username:** `demo`
- **Password:** `demo123`
- **Access:** Basic user features

## Directory Structure

```
photography_website/
├── admin/                  # Admin dashboard pages
│   ├── dashboard.php      # Main admin dashboard
│   └── ...
├── ajax/                  # AJAX endpoints
│   ├── load-photos.php    # Dynamic photo loading
│   └── ...
├── assets/                # Static assets
│   ├── css/
│   │   └── style.css      # Main stylesheet
│   ├── js/
│   │   └── main.js        # Main JavaScript
│   └── images/            # Static images
├── config/
│   └── database.php       # Database configuration
├── includes/
│   └── functions.php      # Helper functions
├── uploads/               # Uploaded images (create this)
├── index.php              # Homepage
├── gallery.php            # Photo gallery
├── booking.php            # Booking system
├── login.php              # Authentication
├── logout.php             # Logout functionality
└── README.md              # This file
```

## Key Features Explained

### 1. Photo Gallery System
- **Dynamic Loading:** Photos are loaded from the database with AJAX pagination
- **Category Filtering:** Filter photos by wedding, portrait, event, etc.
- **Lightbox View:** Click photos for full-screen viewing
- **Admin Upload:** Easy photo upload with automatic resizing

### 2. Booking System
- **Calendar Integration:** Visual calendar showing available dates
- **Form Validation:** Client and server-side validation
- **Email Notifications:** Automatic emails for new bookings
- **Status Management:** Track booking status (pending, confirmed, cancelled)

### 3. Admin Dashboard
- **Statistics Overview:** Quick stats on photos, bookings, messages
- **Photo Management:** Upload, edit, delete photos
- **Booking Management:** View and update booking status
- **Message Management:** Read and respond to contact messages

### 4. User Authentication
- **Secure Login:** Password hashing with PHP's password_hash()
- **Role-based Access:** Admin and user roles with different permissions
- **Session Management:** Secure session handling
- **Registration System:** New user registration with validation

## Customization

### Adding New Photo Categories
1. Go to the admin dashboard
2. Add categories directly in the database or create an admin interface
3. Categories will automatically appear in the gallery filters

### Modifying Booking Form
1. Edit `booking.php` to add/remove form fields
2. Update the database table structure if needed
3. Modify the `createBooking()` function in `includes/functions.php`

### Changing Colors/Styling
1. Edit `assets/css/style.css`
2. Update CSS custom properties for consistent color changes
3. Modify the color scheme variables at the top of the file

### Adding New Pages
1. Create new PHP files following the existing structure
2. Include the navigation and footer
3. Add database functions in `includes/functions.php` if needed

## Security Considerations

### Implemented Security Features
- **SQL Injection Prevention:** All queries use prepared statements
- **XSS Protection:** All output is escaped with `htmlspecialchars()`
- **File Upload Security:** File type and size validation
- **Session Security:** Proper session management
- **Password Security:** Passwords are hashed using PHP's `password_hash()`

### Additional Security Recommendations
- Use HTTPS in production
- Implement CSRF protection for forms
- Add rate limiting for login attempts
- Regular security updates
- Backup database regularly

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `config/database.php`
   - Ensure MySQL server is running
   - Verify database exists

2. **Image Upload Issues**
   - Check file permissions on uploads directory
   - Verify PHP file upload settings (upload_max_filesize, post_max_size)
   - Ensure uploads directory exists

3. **Session Issues**
   - Check PHP session configuration
   - Verify session directory permissions
   - Clear browser cookies/cache

4. **JavaScript Errors**
   - Check browser console for errors
   - Ensure jQuery is loaded
   - Verify JavaScript file paths

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Performance Optimization

### Implemented Optimizations
- **Lazy Loading:** Images load as needed
- **AJAX Pagination:** Smooth content loading
- **CSS/JS Minification:** Reduced file sizes
- **Database Indexing:** Optimized queries

### Additional Optimizations
- Enable Gzip compression
- Use CDN for static assets
- Implement caching headers
- Optimize images before upload

## Support

For issues or questions:
1. Check the troubleshooting section
2. Review the code comments
3. Test with demo accounts
4. Check browser console for errors

## License

This project is open source and available under the MIT License.

## Credits

- **Icons:** Font Awesome
- **Fonts:** Google Fonts (Poppins)
- **JavaScript:** jQuery
- **CSS Framework:** Custom responsive framework

---

**Note:** This is a demonstration project. For production use, implement additional security measures, testing, and optimization based on your specific requirements.