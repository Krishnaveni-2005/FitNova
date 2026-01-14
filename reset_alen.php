<?php
require 'db_connect.php';

// Reset Alen Chacko's assignment
$sql = "UPDATE users SET assigned_trainer_id = NULL, assignment_status = 'none' WHERE first_name LIKE 'ALEN CHACKO'";

if ($conn->query($sql) === TRUE) {
    echo "Reset successful. Alen Chacko is now free to hire a trainer.";
} else {
    echo "Error resetting user: " . $conn->error;
}
$conn->close();
?>
