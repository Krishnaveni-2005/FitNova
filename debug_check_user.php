<?php
require 'db_connect.php';

$sql = "SELECT user_id, first_name, last_name, email, role, assignment_status, assigned_trainer_id FROM users WHERE first_name LIKE '%Alen%' OR last_name LIKE '%Chacko%'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "No user found with name Alen or Chacko";
}
$conn->close();
?>
