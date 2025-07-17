<?php
/**
 * PhotoLens Database Installation Script
 * This script will create the database and populate it with sample data
 */

// Database configuration
$host = 'localhost';
$username = 'root';  // Change this to your MySQL username
$password = '';      // Change this to your MySQL password
$database = 'photography_db';

echo "<h1>PhotoLens Database Installation</h1>";
echo "<p>Starting database installation...</p>";

try {
    // Connect to MySQL server (without selecting database)
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✓ Connected to MySQL server</p>";
    
    // Read the SQL file
    $sql_file = 'database_setup.sql';
    if (!file_exists($sql_file)) {
        throw new Exception("SQL file not found: $sql_file");
    }
    
    $sql_content = file_get_contents($sql_file);
    echo "<p>✓ SQL file loaded</p>";
    
    // Split SQL content into individual statements
    $statements = explode(';', $sql_content);
    
    $executed = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        
        // Skip empty statements and comments
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        // Skip delimiter statements
        if (strpos($statement, 'DELIMITER') !== false) {
            continue;
        }
        
        try {
            $conn->exec($statement);
            $executed++;
        } catch (PDOException $e) {
            // Some statements might fail (like CREATE DATABASE IF NOT EXISTS when it already exists)
            // We'll only count critical errors
            if (strpos($e->getMessage(), 'already exists') === false && 
                strpos($e->getMessage(), 'Duplicate entry') === false) {
                echo "<p style='color: orange;'>Warning: " . $e->getMessage() . "</p>";
                $errors++;
            }
        }
    }
    
    echo "<p>✓ Executed $executed SQL statements</p>";
    
    if ($errors > 0) {
        echo "<p style='color: orange;'>⚠ $errors warnings occurred (this is usually normal)</p>";
    }
    
    // Verify installation by checking some tables
    $conn->exec("USE $database");
    
    $tables_to_check = ['users', 'categories', 'photos', 'bookings', 'testimonials'];
    echo "<h3>Verifying Installation:</h3>";
    
    foreach ($tables_to_check as $table) {
        try {
            $stmt = $conn->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p>✓ Table '$table': $count records</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Error checking table '$table': " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h3>🎉 Installation Complete!</h3>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>Default Login Credentials:</h4>";
    echo "<p><strong>Admin Account:</strong><br>";
    echo "Username: <code>admin</code><br>";
    echo "Password: <code>admin123</code></p>";
    echo "<p><strong>Demo User Account:</strong><br>";
    echo "Username: <code>demo</code><br>";
    echo "Password: <code>demo123</code></p>";
    echo "</div>";
    
    echo "<h4>Next Steps:</h4>";
    echo "<ol>";
    echo "<li>Make sure the 'uploads' directory exists and is writable</li>";
    echo "<li>Visit your website homepage to see the installation in action</li>";
    echo "<li>Login to the admin panel to start managing your photography website</li>";
    echo "<li>Upload some photos to populate your gallery</li>";
    echo "</ol>";
    
    echo "<p><a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Website</a></p>";
    echo "<p><a href='login.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Login to Admin</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<h4>Troubleshooting:</h4>";
    echo "<ul>";
    echo "<li>Make sure MySQL server is running</li>";
    echo "<li>Check your database credentials in this file</li>";
    echo "<li>Ensure you have permission to create databases</li>";
    echo "<li>Make sure the database_setup.sql file exists</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><small>PhotoLens Photography Website Database Installer</small></p>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background: #f8f9fa;
}

h1 {
    color: #2c3e50;
    text-align: center;
}

h3 {
    color: #34495e;
    border-bottom: 2px solid #e74c3c;
    padding-bottom: 5px;
}

p {
    line-height: 1.6;
}

code {
    background: #f1f1f1;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: monospace;
}

ol, ul {
    line-height: 1.8;
}

a {
    display: inline-block;
    margin: 5px;
}
</style>