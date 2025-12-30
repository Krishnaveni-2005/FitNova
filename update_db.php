<?php
require "db_connect.php";

echo "<h2>Database Update Utility</h2>";

$sql = "ALTER TABLE users ADD COLUMN role ENUM('free', 'pro', 'trainer', 'admin') DEFAULT 'free' AFTER email";

if ($conn->query($sql) === TRUE) {
    echo "<p style='color: green;'>? Success: 'role' column added to 'users' table.</p>";
} else {
    if (strpos($conn->error, "Duplicate column name") !== false) {
        echo "<p style='color: blue;'>?? Info: 'role' column already exists.</p>";
    } else {
        echo "<p style='color: red;'>? Error updating database: " . $conn->error . "</p>";
        echo "<p>Try running this SQL manually in phpMyAdmin: <br><code>ALTER TABLE users ADD COLUMN role ENUM('free', 'pro', 'trainer', 'admin') DEFAULT 'free' AFTER email;</code></p>";
    }
}

$conn->close();
?>
