<?php
require 'db_connect.php';

// Check the structure of the users table
$sql = "DESCRIBE users";
$result = $conn->query($sql);

echo "Users Table Structure:\n";
echo str_repeat("=", 80) . "\n\n";

if ($result) {
    while($row = $result->fetch_assoc()) {
        echo "Field: " . $row['Field'] . "\n";
        echo "Type: " . $row['Type'] . "\n";
        echo "Null: " . $row['Null'] . "\n";
        echo "Default: " . ($row['Default'] ?? 'NULL') . "\n";
        echo str_repeat("-", 80) . "\n";
    }
}

// Check if there are any users at all
$sql2 = "SELECT COUNT(*) as total FROM users";
$result2 = $conn->query($sql2);
$total = $result2->fetch_assoc()['total'];
echo "\nTotal users in database: " . $total . "\n";

// Check trainers specifically
$sql3 = "SELECT COUNT(*) as total FROM users WHERE role = 'trainer'";
$result3 = $conn->query($sql3);
$trainers = $result3->fetch_assoc()['total'];
echo "Total trainers (any status): " . $trainers . "\n";

$conn->close();
?>
