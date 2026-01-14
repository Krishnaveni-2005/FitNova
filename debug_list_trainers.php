<?php
require 'db_connect.php';

$sql = "SELECT user_id, first_name, last_name, email, role FROM users WHERE role = 'trainer'";
$result = $conn->query($sql);

echo "<pre>";
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "No trainers found.";
}
echo "</pre>";
?>
