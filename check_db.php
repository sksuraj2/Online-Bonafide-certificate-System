<?php
include 'connection.php';

echo "<h2>Users Table Structure:</h2>";
$result = $conn->query('DESCRIBE users');
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><h2>Sample User Data:</h2>";
$result = $conn->query('SELECT * FROM users LIMIT 1');
if($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "<pre>";
    print_r($user);
    echo "</pre>";
} else {
    echo "No users found in database.";
}
?>
