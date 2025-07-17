# PhotoLens Database Setup Guide

This guide will help you set up the database for your PhotoLens photography website.

## 📋 Prerequisites

- MySQL server installed and running
- PHP with PDO MySQL extension
- Web server (Apache/Nginx) or local development environment (XAMPP/WAMP)

## 🗃️ Database Structure

The database includes the following tables:

### Core Tables
- **users** - User accounts (admin and regular users)
- **categories** - Photo categories (Weddings, Portraits, Events, etc.)
- **photos** - Photo gallery with metadata
- **bookings** - Client booking requests
- **testimonials** - Client testimonials
- **contact_messages** - Contact form submissions

### Additional Tables
- **blog_posts** - Blog articles and posts
- **products** - Shop items (prints, albums, etc.)
- **orders** & **order_items** - E-commerce functionality
- **newsletter_subscribers** - Email newsletter list
- **photo_likes** - User favorites system
- **comments** - Blog post comments
- **settings** - Site configuration

## 🚀 Installation Methods

### Method 1: Web-Based Installation (Recommended)

1. **Upload files to your web server**
2. **Visit the installer in your browser:**
   ```
   http://yourwebsite.com/install_database.php
   ```
3. **Follow the on-screen instructions**

### Method 2: Command Line Installation

1. **Make the script executable:**
   ```bash
   chmod +x install_db.sh
   ```

2. **Run the installation script:**
   ```bash
   ./install_db.sh
   ```

3. **Follow the prompts**

### Method 3: Manual MySQL Installation

1. **Connect to MySQL:**
   ```bash
   mysql -u root -p
   ```

2. **Run the SQL file:**
   ```sql
   source database_setup.sql;
   ```

## 🔧 Configuration

### Database Connection Settings

Edit the database configuration in your files:

**config/database.php:**
```php
$host = 'localhost';
$username = 'root';
$password = 'your_password';
$database = 'photography_db';
```

**install_database.php:**
```php
$host = 'localhost';
$username = 'root';
$password = 'your_password';
$database = 'photography_db';
```

## 👤 Default Accounts

After installation, you can login with these default accounts:

### Admin Account
- **Username:** `admin`
- **Password:** `admin123`
- **Access:** Full admin panel access

### Demo User Account
- **Username:** `demo`
- **Password:** `demo123`
- **Access:** Regular user features

> ⚠️ **Important:** Change these default passwords immediately after installation!

## 📊 Sample Data Included

The database comes pre-populated with:

- ✅ **4 user accounts** (including admin and demo)
- ✅ **6 photo categories** (Weddings, Portraits, Events, Commercial, Nature, Street)
- ✅ **12 sample photos** with descriptions
- ✅ **6 client testimonials**
- ✅ **5 booking requests** with different statuses
- ✅ **4 contact messages**
- ✅ **4 blog posts** (3 published, 1 draft)
- ✅ **6 shop products** (digital and physical)
- ✅ **3 sample orders**
- ✅ **Site configuration settings**

## 🔍 Database Features

### Performance Optimizations
- **Indexes** on frequently queried columns
- **Views** for common data queries
- **Stored procedures** for statistics

### Security Features
- **Foreign key constraints** for data integrity
- **Prepared statement support** (SQL injection protection)
- **Password hashing** for user accounts

### Advanced Features
- **Triggers** for automatic updates
- **Views** for simplified queries
- **Stored procedures** for complex operations

## 🛠️ Post-Installation Steps

### 1. Create Uploads Directory
```bash
mkdir -p uploads
chmod 755 uploads
```

### 2. Verify Installation
Check that all tables were created:
```sql
USE photography_db;
SHOW TABLES;
```

### 3. Test Database Connection
Visit your website and check if it loads without errors.

### 4. Admin Panel Access
1. Go to `login.php`
2. Login with admin credentials
3. Access the admin dashboard

## 📁 File Structure

```
your-website/
├── database_setup.sql          # Complete SQL schema and data
├── install_database.php        # Web-based installer
├── install_db.sh              # Command-line installer
├── DATABASE_SETUP.md          # This documentation
├── config/
│   └── database.php           # Database configuration
├── includes/
│   └── functions.php          # Database helper functions
└── uploads/                   # Photo upload directory
```

## 🔧 Troubleshooting

### Common Issues

**1. "Access denied" error**
- Check MySQL username/password
- Ensure MySQL server is running
- Verify user has database creation privileges

**2. "Table already exists" warnings**
- These are normal if re-running installation
- The script uses `IF NOT EXISTS` to prevent conflicts

**3. "File not found" error**
- Ensure `database_setup.sql` is in the same directory
- Check file permissions

**4. Connection timeout**
- Increase MySQL timeout settings
- Check firewall settings

### Database Permissions

Make sure your MySQL user has these permissions:
```sql
GRANT ALL PRIVILEGES ON photography_db.* TO 'your_user'@'localhost';
FLUSH PRIVILEGES;
```

## 📈 Database Maintenance

### Regular Tasks

**1. Backup Database**
```bash
mysqldump -u root -p photography_db > backup_$(date +%Y%m%d).sql
```

**2. Optimize Tables**
```sql
OPTIMIZE TABLE photos, bookings, users;
```

**3. Check Statistics**
```sql
CALL GetPhotoStatistics();
CALL GetMonthlyBookingStats(2024);
```

## 🔄 Updates and Migrations

To update the database structure in the future:

1. **Backup existing data**
2. **Create migration scripts**
3. **Test on development environment**
4. **Apply to production**

## 📞 Support

If you encounter issues:

1. Check the error logs in your web server
2. Verify MySQL error logs
3. Ensure all prerequisites are met
4. Check file permissions

## 🎯 Next Steps

After successful database installation:

1. **Configure your web server**
2. **Upload photos to the gallery**
3. **Customize site settings**
4. **Set up email configuration**
5. **Configure payment gateway** (if using shop features)

---

**🎉 Congratulations!** Your PhotoLens photography website database is now ready to use!

For more information, see the main `SETUP.md` file for complete website configuration.