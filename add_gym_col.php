<?php
require 'db_connect.php';

$sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS gym_membership_status ENUM('inactive', 'active') DEFAULT 'inactive'";
if ($conn->query($sql) === TRUE) {
    echo "Column gym_membership_status added successfully";
} else {
    echo "Error adding column: " . $conn->error;
}
?>
