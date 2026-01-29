<?php
require 'db_connect.php';

$result = $conn->query("DESCRIBE users");

while ($row = $result->fetch_assoc()) {
    if ($row['Field'] === 'role') {
        echo "Role Column Type: " . $row['Type'] . "\n";
    }
}
$conn->close();
?>
