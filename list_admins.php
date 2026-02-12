<?php
require_once 'config.php';
require_once 'db_connect.php';

$sql = "SELECT user_id, email, first_name, last_name, role FROM users WHERE role = 'admin'";
$result = $conn->query($sql);

echo "<h1>List of Admins</h1>";
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<p>ID: " . $row['user_id'] . " | " . $row['first_name'] . " " . $row['last_name'] . " (" . $row['email'] . ")</p>";
    }
} else {
    echo "No admins found.";
}
?>
