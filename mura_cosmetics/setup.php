<?php
// Mura Cosmetics Setup Script
// Run this once to check system requirements and setup

$errors = [];
$warnings = [];
$success = [];

// Check PHP version
if (version_compare(PHP_VERSION, '7.4.0') >= 0) {
    $success[] = "PHP version: " . PHP_VERSION . " ✓";
} else {
    $errors[] = "PHP version too old. Requires 7.4+, current: " . PHP_VERSION;
}

// Check required extensions
$required_extensions = ['mysqli', 'session', 'fileinfo'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        $success[] = "Extension '$ext' loaded ✓";
    } else {
        $errors[] = "Required extension '$ext' not loaded";
    }
}

// Check uploads directory
if (is_dir('uploads')) {
    if (is_writable('uploads')) {
        $success[] = "Uploads directory writable ✓";
    } else {
        $errors[] = "Uploads directory not writable";
    }
} else {
    $warnings[] = "Uploads directory doesn't exist - will be created";
    if (!mkdir('uploads', 0777, true)) {
        $errors[] = "Could not create uploads directory";
    } else {
        $success[] = "Uploads directory created ✓";
    }
}

// Check database connection
try {
    $conn = new mysqli('localhost', 'root', '', 'mura_cosmetics');
    if ($conn->connect_error) {
        $warnings[] = "Database 'mura_cosmetics' not found - please import create_tables.sql";
    } else {
        $success[] = "Database connection successful ✓";
        
        // Check if tables exist
        $tables = ['admins', 'categories', 'products'];
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows > 0) {
                $success[] = "Table '$table' exists ✓";
            } else {
                $warnings[] = "Table '$table' not found - please import create_tables.sql";
            }
        }
    }
    $conn->close();
} catch (Exception $e) {
    $errors[] = "Database connection failed: " . $e->getMessage();
}

// Check file permissions
$files_to_check = ['db.php', 'login.php', 'admin_dashboard.php', 'logout.php'];
foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        if (is_readable($file)) {
            $success[] = "File '$file' readable ✓";
        } else {
            $errors[] = "File '$file' not readable";
        }
    } else {
        $errors[] = "File '$file' missing";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mura Cosmetics - Setup Check</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #764ba2;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 1.1em;
        }

        .section {
            margin-bottom: 30px;
        }

        .section h2 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 10px;
            border-left: 4px solid #28a745;
        }

        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 10px;
            border-left: 4px solid #ffc107;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 10px;
            border-left: 4px solid #dc3545;
        }

        .status-summary {
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            font-size: 1.2em;
            font-weight: bold;
        }

        .status-ready {
            background: #d4edda;
            color: #155724;
            border: 2px solid #28a745;
        }

        .status-issues {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #dc3545;
        }

        .next-steps {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #2196f3;
        }

        .next-steps h3 {
            color: #1976d2;
            margin-bottom: 15px;
        }

        .next-steps ol {
            margin-left: 20px;
        }

        .next-steps li {
            margin-bottom: 8px;
            color: #333;
        }

        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            margin: 10px 5px;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Mura Cosmetics</h1>
            <p>Setup & System Check</p>
        </div>

        <?php if (empty($errors)): ?>
            <div class="status-summary status-ready">
                🎉 System Ready! All checks passed successfully.
            </div>
        <?php else: ?>
            <div class="status-summary status-issues">
                ⚠️ Issues Found! Please resolve the errors below.
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="section">
                <h2 style="color: #28a745;">✅ Successful Checks</h2>
                <?php foreach ($success as $item): ?>
                    <div class="success"><?php echo htmlspecialchars($item); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($warnings)): ?>
            <div class="section">
                <h2 style="color: #ffc107;">⚠️ Warnings</h2>
                <?php foreach ($warnings as $item): ?>
                    <div class="warning"><?php echo htmlspecialchars($item); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="section">
                <h2 style="color: #dc3545;">❌ Errors</h2>
                <?php foreach ($errors as $item): ?>
                    <div class="error"><?php echo htmlspecialchars($item); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="next-steps">
            <h3>Next Steps:</h3>
            <ol>
                <li><strong>Import Database:</strong> Go to phpMyAdmin and import <code>create_tables.sql</code></li>
                <li><strong>Set Permissions:</strong> Ensure <code>uploads/</code> folder is writable</li>
                <li><strong>Start Services:</strong> Make sure Apache and MySQL are running in XAMPP</li>
                <li><strong>Access Website:</strong> Go to <a href="login.php">login.php</a> to start using the system</li>
                <li><strong>Default Login:</strong> admin@muracosmetics.com / admin123</li>
            </ol>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <?php if (empty($errors)): ?>
                <a href="login.php" class="btn">Go to Login Page</a>
            <?php endif; ?>
            <a href="?refresh=1" class="btn">Refresh Check</a>
        </div>

        <div class="footer">
            <p>Mura Cosmetics Admin System - Setup Complete</p>
            <p><small>Delete this file (setup.php) after successful setup for security.</small></p>
        </div>
    </div>
</body>
</html>