<?php
require 'db_connect.php';
// Update Stability ball to use ball icon
$conn->query("UPDATE gym_equipment SET icon='fas fa-volleyball-ball' WHERE name LIKE '%ball%'");
echo "Updated icons for balls.";
?>
