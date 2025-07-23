<?php
// Utility script to generate password hashes
// Run this script once to get the proper hash for "admin123"

$password = "admin123";
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password: " . $password . "\n";
echo "Hash: " . $hash . "\n";
echo "\nCopy this hash and use it in the create_tables.sql file or directly in the database.\n";
?>

<!-- 
To use this script:
1. Save this file as generate_password.php
2. Run it in your browser: http://localhost/mura_cosmetics/generate_password.php
3. Copy the generated hash
4. Update the create_tables.sql file or database directly
-->

<!DOCTYPE html>
<html>
<head>
    <title>Password Hash Generator - Mura Cosmetics</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f7fa; }
        .container { background: white; padding: 30px; border-radius: 10px; max-width: 600px; margin: 0 auto; }
        .hash-result { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #667eea; }
        code { background: #e9ecef; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Password Hash Generator</h1>
        <p>This utility generates secure password hashes for admin accounts.</p>
        
        <div class="hash-result">
            <h3>Generated Hash for "admin123":</h3>
            <p><strong>Password:</strong> <code>admin123</code></p>
            <p><strong>Hash:</strong> <code><?php echo password_hash("admin123", PASSWORD_DEFAULT); ?></code></p>
        </div>
        
        <h3>How to use:</h3>
        <ol>
            <li>Copy the hash above</li>
            <li>Replace the hash in <code>create_tables.sql</code></li>
            <li>Or update directly in phpMyAdmin</li>
            <li>Delete this file after use for security</li>
        </ol>
        
        <p><strong>Note:</strong> Delete this file after generating the hash for security purposes.</p>
    </div>
</body>
</html>