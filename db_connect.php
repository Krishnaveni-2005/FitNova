<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fitnova_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Auto-check and add role column if missing to prevent "Unknown column" errors
$checkColumn = $conn->query("SHOW COLUMNS FROM `users` LIKE 'role'");
if ($checkColumn->num_rows == 0) {
    $conn->query("ALTER TABLE `users` ADD COLUMN `role` ENUM('free', 'pro', 'trainer', 'admin') DEFAULT 'free' AFTER email");
}
?>
