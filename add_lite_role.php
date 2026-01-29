<?php
require 'db_connect.php';

// Add 'lite' to the ENUM
$sql = "ALTER TABLE users MODIFY COLUMN role ENUM('free', 'pro', 'trainer', 'admin', 'elite', 'lite') DEFAULT 'free'";

if ($conn->query($sql) === TRUE) {
    echo "Role column updated successfully to include 'lite'.\n";
} else {
    echo "Error updating role column: " . $conn->error . "\n";
}

$conn->close();
?>
