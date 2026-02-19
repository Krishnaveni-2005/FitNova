<?php
// list_admins.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connect.php';

echo "<h1>Listing Admins</h1>";

$result = $conn->query("SELECT * FROM users WHERE role='admin'");

if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<p>";
            echo "ID: " . $row['user_id'] . "<br>";
            echo "Name: " . $row['first_name'] . " " . $row['last_name'] . "<br>";
            echo "Email: " . $row['email'] . "<br>";
            echo "Phone: " . $row['phone'] . "<br>";
            echo "</p>";
            echo "<hr>";
        }
    } else {
        echo "<p>No admins found!</p>";
    }
} else {
    echo "<p>Error executing query: " . $conn->error . "</p>";
}
?>
