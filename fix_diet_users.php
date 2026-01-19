<?php
require 'db_connect.php';

echo "Updating old diet plans to link to user_ids...<br>";
$sql = "UPDATE trainer_diet_plans d 
        JOIN users u ON CONCAT(u.first_name, ' ', u.last_name) = d.client_name 
        SET d.user_id = u.user_id 
        WHERE d.user_id IS NULL OR d.user_id = 0";

if ($conn->query($sql)) {
    echo "Updated rows: " . $conn->affected_rows;
} else {
    echo "Error: " . $conn->error;
}
?>
