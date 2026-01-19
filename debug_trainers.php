<?php
require 'db_connect.php';
$res = $conn->query("SELECT user_id, first_name, role FROM users WHERE role='trainer'");
while($row = $res->fetch_assoc()) {
    echo "ID: " . $row['user_id'] . " - " . $row['first_name'] . "<br>";
}
?>
