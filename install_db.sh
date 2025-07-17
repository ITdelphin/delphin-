#!/bin/bash

# PhotoLens Database Installation Script
# This script will create the database using MySQL command line

echo "==================================="
echo "PhotoLens Database Installation"
echo "==================================="
echo ""

# Database configuration
DB_HOST="localhost"
DB_USER="root"
DB_PASSWORD=""
DB_NAME="photography_db"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

# Check if MySQL is installed
if ! command -v mysql &> /dev/null; then
    print_error "MySQL is not installed or not in PATH"
    exit 1
fi

print_status "MySQL found"

# Check if SQL file exists
if [ ! -f "database_setup.sql" ]; then
    print_error "database_setup.sql file not found"
    exit 1
fi

print_status "SQL file found"

# Prompt for database credentials if needed
echo ""
echo "Database Configuration:"
echo "Host: $DB_HOST"
echo "User: $DB_USER"
echo "Database: $DB_NAME"
echo ""

read -p "Press Enter to continue with these settings or Ctrl+C to cancel..."

# Test MySQL connection
echo ""
print_info "Testing MySQL connection..."

if [ -z "$DB_PASSWORD" ]; then
    mysql -h "$DB_HOST" -u "$DB_USER" -e "SELECT 1;" > /dev/null 2>&1
else
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASSWORD" -e "SELECT 1;" > /dev/null 2>&1
fi

if [ $? -ne 0 ]; then
    print_error "Cannot connect to MySQL. Please check your credentials."
    echo ""
    echo "To fix this:"
    echo "1. Make sure MySQL server is running"
    echo "2. Check your username and password"
    echo "3. Edit this script to update DB_USER and DB_PASSWORD variables"
    exit 1
fi

print_status "MySQL connection successful"

# Run the SQL file
echo ""
print_info "Installing database..."

if [ -z "$DB_PASSWORD" ]; then
    mysql -h "$DB_HOST" -u "$DB_USER" < database_setup.sql
else
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASSWORD" < database_setup.sql
fi

if [ $? -eq 0 ]; then
    print_status "Database installation completed successfully!"
else
    print_error "Database installation failed"
    exit 1
fi

# Verify installation
echo ""
print_info "Verifying installation..."

TABLES=("users" "categories" "photos" "bookings" "testimonials" "contact_messages")

for table in "${TABLES[@]}"; do
    if [ -z "$DB_PASSWORD" ]; then
        COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -D "$DB_NAME" -se "SELECT COUNT(*) FROM $table;" 2>/dev/null)
    else
        COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASSWORD" -D "$DB_NAME" -se "SELECT COUNT(*) FROM $table;" 2>/dev/null)
    fi
    
    if [ $? -eq 0 ]; then
        print_status "Table '$table': $COUNT records"
    else
        print_warning "Could not verify table '$table'"
    fi
done

echo ""
echo "==================================="
echo -e "${GREEN}🎉 Installation Complete!${NC}"
echo "==================================="
echo ""
echo "Default Login Credentials:"
echo "-------------------------"
echo "Admin Account:"
echo "  Username: admin"
echo "  Password: admin123"
echo ""
echo "Demo User Account:"
echo "  Username: demo"
echo "  Password: demo123"
echo ""
echo "Next Steps:"
echo "----------"
echo "1. Make sure the 'uploads' directory exists and is writable:"
echo "   mkdir -p uploads"
echo "   chmod 755 uploads"
echo ""
echo "2. Start your web server (Apache/Nginx)"
echo ""
echo "3. Visit your website to see it in action"
echo ""
echo "4. Login to admin panel to manage your photography website"
echo ""
echo "Files created:"
echo "- database_setup.sql (SQL schema and data)"
echo "- install_database.php (Web-based installer)"
echo "- install_db.sh (Command-line installer)"
echo ""
print_status "Ready to use!"