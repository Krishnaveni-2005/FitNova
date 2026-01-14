<?php
// Script to delete all users with role = 'trainer'
// WARNING: This deletes data permanently.

require 'db_connect.php';

// Check if confirmed via GET parameter
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    $sql = "DELETE FROM users WHERE role = 'trainer'";
    if ($conn->query($sql) === TRUE) {
        $affected_rows = $conn->affected_rows;
        echo "<div style='font-family:sans-serif; padding:50px; text-align:center;'>";
        echo "<h1 style='color:green;'>Success</h1>";
        echo "<p>Successfully deleted <strong>$affected_rows</strong> trainer(s) from the database.</p>";
        echo "<br><a href='home.php' style='text-decoration:none; background:#0F2C59; color:white; padding:10px 20px; border-radius:5px;'>Return Home</a>";
        echo "</div>";
    } else {
        echo "Error deleting records: " . $conn->error;
    }
} else {
    // Show confirmation button
    echo "<div style='font-family:sans-serif; padding:50px; text-align:center;'>";
    echo "<h1 style='color:#0F2C59;'>Delete All Trainers?</h1>";
    echo "<p style='color:red;'>Warning: This will permanently remove all users with the role of 'trainer' from the database.</p>";
    echo "<p>This action cannot be undone.</p>";
    echo "<br>";
    echo "<a href='?confirm=yes' style='text-decoration:none; background:red; color:white; padding:15px 30px; border-radius:5px; font-weight:bold;'>YES, DELETE ALL TRAINERS</a>";
    echo "</div>";
}

$conn->close();
?>
