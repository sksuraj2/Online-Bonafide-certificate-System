<?php
include 'connection.php';

echo "<h2>Database Setup</h2>";

// Create users table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    PASSWORD VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "<p style='color: green;'>✓ Users table created/verified successfully!</p>";
} else {
    echo "<p style='color: red;'>✗ Error creating table: " . $conn->error . "</p>";
}

// Create form table if it doesn't exist
$sql_form = "CREATE TABLE IF NOT EXISTS form (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    gender VARCHAR(10) NOT NULL,
    dob DATE NOT NULL,
    mobile VARCHAR(15) NOT NULL,
    email VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    roll_no VARCHAR(50) NOT NULL,
    department VARCHAR(100) NOT NULL,
    course VARCHAR(100) NOT NULL,
    year_sem VARCHAR(50) NOT NULL,
    purpose TEXT NOT NULL,
    id_card VARCHAR(255) DEFAULT NULL,
    fee_receipt VARCHAR(255) DEFAULT NULL,
    signature VARCHAR(255) DEFAULT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql_form) === TRUE) {
    echo "<p style='color: green;'>✓ Form table created/verified successfully!</p>";
} else {
    echo "<p style='color: red;'>✗ Error creating form table: " . $conn->error . "</p>";
}

// Check if status column exists in form table, if not add it
$check_column = $conn->query("SHOW COLUMNS FROM form LIKE 'status'");
if($check_column->num_rows == 0) {
    $add_status = "ALTER TABLE form ADD COLUMN status VARCHAR(20) DEFAULT 'pending'";
    if($conn->query($add_status) === TRUE) {
        echo "<p style='color: green;'>✓ Status column added to form table!</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Note: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: blue;'>✓ Status column already exists in form table!</p>";
}

// Check table structure
echo "<h3>Current Table Structure:</h3>";
$result = $conn->query('DESCRIBE users');
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr style='background: #667eea; color: white;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td><strong>" . $row['Field'] . "</strong></td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . ($row['Key'] ? $row['Key'] : '-') . "</td>";
    echo "<td>" . ($row['Default'] ? $row['Default'] : 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Check if any users exist
$result = $conn->query('SELECT COUNT(*) as total FROM users');
$row = $result->fetch_assoc();
echo "<h3>Total Users: " . $row['total'] . "</h3>";

if($row['total'] > 0) {
    echo "<h3>Sample User Data:</h3>";
    $result = $conn->query('SELECT id, full_name, email, phone, created_at FROM users LIMIT 3');
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr style='background: #667eea; color: white;'><th>ID</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Created At</th></tr>";
    while($user = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['full_name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . ($user['phone'] ? htmlspecialchars($user['phone']) : 'N/A') . "</td>";
        echo "<td>" . $user['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<br><p><a href='login.php' style='color: #667eea; font-weight: bold;'>← Back to Login</a></p>";
?>
