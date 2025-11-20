<?php
/**
 * System Verification Script
 * This script checks if the Online Bonafide Certificate System is properly set up
 * Run this after cloning the repository to verify everything works
 */

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>System Verification - Online Bonafide Certificate System</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .container { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info { color: #17a2b8; font-weight: bold; }
        .check-item { padding: 10px; margin: 5px 0; border-left: 4px solid #ddd; background: #f8f9fa; }
        .check-item.pass { border-left-color: #28a745; }
        .check-item.fail { border-left-color: #dc3545; }
        .check-item.warn { border-left-color: #ffc107; }
        h1 { color: #667eea; }
        h2 { color: #764ba2; margin-top: 30px; }
        .badge { font-size: 0.9em; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔍 System Verification</h1>
    <p class='lead'>Online Bonafide Certificate System - Installation Check</p>
    <hr>";

$allChecks = true;
$warnings = 0;
$errors = 0;
$success = 0;

// Check 1: PHP Version
echo "<h2>1. PHP Environment</h2>";
$phpVersion = phpversion();
echo "<div class='check-item " . (version_compare($phpVersion, '7.4.0', '>=') ? 'pass' : 'fail') . "'>";
echo "<strong>PHP Version:</strong> " . $phpVersion;
if (version_compare($phpVersion, '7.4.0', '>=')) {
    echo " <span class='badge bg-success'>✓ OK</span>";
    $success++;
} else {
    echo " <span class='badge bg-danger'>✗ FAIL - Requires PHP 7.4+</span>";
    $allChecks = false;
    $errors++;
}
echo "</div>";

// Check required extensions
$requiredExtensions = ['mysqli', 'session', 'json', 'mbstring'];
foreach ($requiredExtensions as $ext) {
    echo "<div class='check-item " . (extension_loaded($ext) ? 'pass' : 'fail') . "'>";
    echo "<strong>Extension {$ext}:</strong> ";
    if (extension_loaded($ext)) {
        echo "<span class='badge bg-success'>✓ Loaded</span>";
        $success++;
    } else {
        echo "<span class='badge bg-danger'>✗ Not Loaded</span>";
        $allChecks = false;
        $errors++;
    }
    echo "</div>";
}

// Check 2: File System
echo "<h2>2. File System</h2>";
$requiredFiles = [
    'connection.php' => 'Database connection',
    'login.php' => 'Login page',
    'register.php' => 'Registration page',
    'admin.php' => 'Admin dashboard',
    'user.php' => 'User dashboard',
    'form.php' => 'Application form',
    'setup_database.php' => 'Database setup script'
];

foreach ($requiredFiles as $file => $description) {
    echo "<div class='check-item " . (file_exists($file) ? 'pass' : 'fail') . "'>";
    echo "<strong>{$description} ({$file}):</strong> ";
    if (file_exists($file)) {
        echo "<span class='badge bg-success'>✓ Exists</span>";
        $success++;
    } else {
        echo "<span class='badge bg-danger'>✗ Missing</span>";
        $allChecks = false;
        $errors++;
    }
    echo "</div>";
}

// Check 3: Database Connection
echo "<h2>3. Database Connection</h2>";
$dbConfigFile = 'connection.php';
if (file_exists($dbConfigFile)) {
    // Read database configuration
    $configContent = file_get_contents($dbConfigFile);
    preg_match('/\$servername\s*=\s*["\']([^"\']+)["\']/', $configContent, $serverMatch);
    preg_match('/\$username\s*=\s*["\']([^"\']+)["\']/', $configContent, $userMatch);
    preg_match('/\$dbname\s*=\s*["\']([^"\']+)["\']/', $configContent, $dbMatch);
    
    $servername = $serverMatch[1] ?? 'localhost';
    $username = $userMatch[1] ?? 'root';
    $dbname = $dbMatch[1] ?? 'bonafide';
    
    echo "<div class='check-item info'>";
    echo "<strong>Database Configuration:</strong><br>";
    echo "Server: {$servername}<br>";
    echo "Username: {$username}<br>";
    echo "Database: {$dbname}";
    echo "</div>";
    
    // Try to connect
    try {
        @include_once 'connection.php';
        if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
            echo "<div class='check-item pass'>";
            echo "<strong>Database Connection:</strong> <span class='badge bg-success'>✓ Connected</span>";
            echo "</div>";
            $success++;
            
            // Check if database exists and has tables
            $tablesResult = $conn->query("SHOW TABLES");
            if ($tablesResult) {
                $tableCount = $tablesResult->num_rows;
                echo "<div class='check-item " . ($tableCount > 0 ? 'pass' : 'warn') . "'>";
                echo "<strong>Database Tables:</strong> ";
                if ($tableCount > 0) {
                    echo "<span class='badge bg-success'>✓ {$tableCount} table(s) found</span><br>";
                    echo "<small>Tables: ";
                    $tables = [];
                    while ($row = $tablesResult->fetch_array()) {
                        $tables[] = $row[0];
                    }
                    echo implode(', ', $tables);
                    echo "</small>";
                    $success++;
                } else {
                    echo "<span class='badge bg-warning'>⚠ No tables found</span><br>";
                    echo "<small>Run <a href='setup_database.php'>setup_database.php</a> to create tables</small>";
                    $warnings++;
                }
                echo "</div>";
                
                // Check specific required tables
                $requiredTables = ['users', 'form'];
                foreach ($requiredTables as $table) {
                    $checkTable = $conn->query("SHOW TABLES LIKE '{$table}'");
                    echo "<div class='check-item " . ($checkTable && $checkTable->num_rows > 0 ? 'pass' : 'warn') . "'>";
                    echo "<strong>Table '{$table}':</strong> ";
                    if ($checkTable && $checkTable->num_rows > 0) {
                        echo "<span class='badge bg-success'>✓ Exists</span>";
                        $success++;
                    } else {
                        echo "<span class='badge bg-warning'>⚠ Not found</span>";
                        $warnings++;
                    }
                    echo "</div>";
                }
            }
        } else {
            echo "<div class='check-item fail'>";
            echo "<strong>Database Connection:</strong> <span class='badge bg-danger'>✗ Failed</span><br>";
            echo "<small>Error: " . (isset($conn) ? $conn->connect_error : "Could not establish connection") . "</small><br>";
            echo "<small>Make sure MySQL is running and database '{$dbname}' exists</small>";
            echo "</div>";
            $allChecks = false;
            $errors++;
        }
    } catch (Exception $e) {
        echo "<div class='check-item fail'>";
        echo "<strong>Database Connection:</strong> <span class='badge bg-danger'>✗ Exception</span><br>";
        echo "<small>" . htmlspecialchars($e->getMessage()) . "</small>";
        echo "</div>";
        $allChecks = false;
        $errors++;
    }
} else {
    echo "<div class='check-item fail'>";
    echo "<strong>Database Configuration File:</strong> <span class='badge bg-danger'>✗ Missing connection.php</span>";
    echo "</div>";
    $allChecks = false;
    $errors++;
}

// Check 4: Web Server
echo "<h2>4. Web Server</h2>";
echo "<div class='check-item pass'>";
echo "<strong>Web Server:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . " <span class='badge bg-success'>✓ Running</span>";
echo "</div>";
$success++;

echo "<div class='check-item pass'>";
echo "<strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown');
echo "</div>";
$success++;

echo "<div class='check-item pass'>";
echo "<strong>Current URL:</strong> " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $_SERVER['REQUEST_URI'];
echo "</div>";
$success++;

// Check 5: Git Repository
echo "<h2>5. Git Repository</h2>";
if (is_dir('.git')) {
    echo "<div class='check-item pass'>";
    echo "<strong>Git Repository:</strong> <span class='badge bg-success'>✓ Initialized</span>";
    echo "</div>";
    $success++;
    
    // Get remote URL
    if (file_exists('.git/config')) {
        $gitConfig = file_get_contents('.git/config');
        if (preg_match('/url\s*=\s*(.+)/', $gitConfig, $matches)) {
            $remoteUrl = trim($matches[1]);
            echo "<div class='check-item pass'>";
            echo "<strong>Remote URL:</strong> <code>{$remoteUrl}</code> <span class='badge bg-success'>✓ Configured</span>";
            echo "</div>";
            $success++;
        }
    }
} else {
    echo "<div class='check-item warn'>";
    echo "<strong>Git Repository:</strong> <span class='badge bg-warning'>⚠ Not a git repository</span><br>";
    echo "<small>Clone from: <code>git clone https://github.com/sksuraj2/Online-Bonafide-certificate-System.git</code></small>";
    echo "</div>";
    $warnings++;
}

// Summary
echo "<h2>📊 Summary</h2>";
echo "<div class='row text-center'>";
echo "<div class='col-md-4'><div class='alert alert-success'><h3>{$success}</h3><p>Checks Passed</p></div></div>";
echo "<div class='col-md-4'><div class='alert alert-warning'><h3>{$warnings}</h3><p>Warnings</p></div></div>";
echo "<div class='col-md-4'><div class='alert alert-danger'><h3>{$errors}</h3><p>Errors</p></div></div>";
echo "</div>";

if ($allChecks && $errors === 0) {
    echo "<div class='alert alert-success text-center'>";
    echo "<h4>✅ System is Ready!</h4>";
    echo "<p>All critical checks passed. You can proceed to use the system.</p>";
    echo "<p><a href='login.php' class='btn btn-primary'>Go to Login Page</a> ";
    echo "<a href='register.php' class='btn btn-success'>Register New User</a></p>";
    echo "</div>";
} else {
    echo "<div class='alert alert-danger text-center'>";
    echo "<h4>❌ System Setup Incomplete</h4>";
    echo "<p>Please fix the errors above before using the system.</p>";
    if ($warnings > 0 || $errors > 0) {
        echo "<p><strong>Next Steps:</strong></p>";
        echo "<ul class='text-start'>";
        if ($errors > 0) {
            echo "<li>Ensure XAMPP (or similar) is installed and running</li>";
            echo "<li>Verify MySQL service is started</li>";
            echo "<li>Create database 'bonafide' if it doesn't exist</li>";
            echo "<li>Run <a href='setup_database.php'>setup_database.php</a> to create tables</li>";
        }
        if ($warnings > 0) {
            echo "<li>Review warnings and take necessary actions</li>";
        }
        echo "</ul>";
    }
    echo "</div>";
}

echo "<hr>";
echo "<div class='text-center text-muted'>";
echo "<p><small>System Verification Script v1.0 | Online Bonafide Certificate System</small></p>";
echo "<p><small>For more help, visit: <a href='README.md' target='_blank'>README.md</a></small></p>";
echo "</div>";

echo "</div>
</body>
</html>";
?>
