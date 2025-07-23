# 🌟 Mura Cosmetics - Installation Guide

## Quick Start (5 Minutes Setup)

### Step 1: Install XAMPP
1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP to `C:\xampp\` (Windows) or `/Applications/XAMPP/` (Mac)
3. Start XAMPP Control Panel

### Step 2: Copy Project Files
1. Copy the entire `mura_cosmetics` folder to:
   - **Windows**: `C:\xampp\htdocs\mura_cosmetics\`
   - **Mac**: `/Applications/XAMPP/htdocs/mura_cosmetics/`
   - **Linux**: `/opt/lampp/htdocs/mura_cosmetics/`

### Step 3: Start Services
1. Open XAMPP Control Panel
2. Click **Start** for:
   - ✅ Apache
   - ✅ MySQL

### Step 4: Setup Database
1. Open browser: `http://localhost/phpmyadmin`
2. Click **Import** tab
3. Choose file: `create_tables.sql`
4. Click **Go**

### Step 5: Run Setup Check
1. Open browser: `http://localhost/mura_cosmetics/setup.php`
2. Verify all checks pass ✅
3. Click "Go to Login Page"

### Step 6: Login & Start Using
- **URL**: `http://localhost/mura_cosmetics`
- **Email**: `admin@muracosmetics.com`
- **Password**: `admin123`

---

## Detailed Installation

### System Requirements
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **Apache**: 2.4 or higher
- **Extensions**: mysqli, session, fileinfo
- **Disk Space**: 50MB minimum

### File Structure Overview
```
mura_cosmetics/
├── 📄 db.php                 # Database connection
├── 🔐 login.php             # Admin login page
├── 📊 admin_dashboard.php   # Main dashboard
├── 🚪 logout.php           # Session cleanup
├── 🏠 index.php            # Entry point
├── 🗄️ create_tables.sql    # Database schema
├── 📁 uploads/             # Product images
├── ⚙️ setup.php            # System checker
├── 🔑 generate_password.php # Password utility
├── 🛡️ .htaccess            # Security config
├── 📖 README.md            # Documentation
└── 📋 INSTALL.md           # This file
```

### Database Setup (Manual)

If automatic import doesn't work:

1. **Create Database**:
   ```sql
   CREATE DATABASE mura_cosmetics;
   USE mura_cosmetics;
   ```

2. **Create Tables**:
   ```sql
   -- Copy and paste content from create_tables.sql
   ```

3. **Verify Tables**:
   - ✅ admins (1 record)
   - ✅ categories (5 records)
   - ✅ products (empty)

### Permissions Setup

#### Windows:
1. Right-click `uploads` folder
2. Properties → Security → Edit
3. Add "Everyone" with Full Control

#### Linux/Mac:
```bash
chmod 755 mura_cosmetics/
chmod 777 mura_cosmetics/uploads/
```

### Troubleshooting

#### ❌ "Connection failed" Error
- **Solution**: Start MySQL in XAMPP
- **Check**: XAMPP Control Panel shows MySQL as "Running"

#### ❌ "Database not found" Error
- **Solution**: Import `create_tables.sql` in phpMyAdmin
- **Check**: Database `mura_cosmetics` exists

#### ❌ "Image upload failed" Error
- **Solution**: Set write permissions on `uploads/` folder
- **Check**: Folder exists and is writable

#### ❌ "Login invalid" Error
- **Solution**: Check admin credentials in database
- **Default**: admin@muracosmetics.com / admin123

#### ❌ "Page not found" Error
- **Solution**: Verify Apache is running
- **Check**: `http://localhost/` shows XAMPP dashboard

### Security Considerations

🔒 **After Setup**:
1. Delete `setup.php`
2. Delete `generate_password.php`
3. Change default admin password
4. Backup database regularly

### Advanced Configuration

#### Change Database Settings
Edit `db.php`:
```php
$host = 'localhost';      // Database host
$username = 'root';       // Database user
$password = '';           // Database password
$database = 'mura_cosmetics'; // Database name
```

#### Add New Admin User
```sql
INSERT INTO admins (email, password, name) VALUES 
('new@admin.com', '$2y$10$hash_here', 'New Admin');
```

#### Customize Categories
```sql
INSERT INTO categories (name) VALUES ('New Category');
```

### Performance Optimization

#### Enable Caching
The `.htaccess` file includes:
- ✅ Gzip compression
- ✅ Browser caching
- ✅ Security headers

#### Image Optimization
- Use JPG for photos
- Use PNG for graphics
- Keep images under 2MB

### Backup & Restore

#### Backup Database
```bash
mysqldump -u root -p mura_cosmetics > backup.sql
```

#### Backup Files
Copy entire `mura_cosmetics` folder

#### Restore
1. Import SQL backup in phpMyAdmin
2. Copy files back to htdocs

---

## 🎉 Success!

If everything works:
- ✅ Login page loads
- ✅ Admin can login
- ✅ Dashboard shows statistics
- ✅ Products can be added/edited/deleted
- ✅ Images upload successfully

**Your Mura Cosmetics admin system is ready!**

---

## Support & Resources

- 📧 **Issues**: Check troubleshooting section
- 🔧 **Customization**: Edit PHP/CSS files
- 📚 **Documentation**: See README.md
- 🌐 **XAMPP Help**: [Apache Friends Community](https://community.apachefriends.org/)

**Happy managing! 💄✨**