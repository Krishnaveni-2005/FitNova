<?php
require 'db_connect.php';

$sql = "SELECT user_id, first_name, last_name, email, role FROM users";
$result = $conn->query($sql);

echo "<h3>Current Users in DB</h3>";
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo $row['role'] . ": " . $row['first_name'] . " " . $row['last_name'] . " (" . $row['email'] . ")<br>";
    }
} else {
    echo "No users found.";
}
?>
