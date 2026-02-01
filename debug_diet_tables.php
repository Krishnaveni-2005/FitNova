<?php
require 'db_connect.php';
echo "--- USERS ---\n";
$r = $conn->query("DESCRIBE users");
while($row = $r->fetch_assoc()) { echo $row['Field']."\n"; }

echo "\n--- CLIENT_PROFILES ---\n";
$r = $conn->query("DESCRIBE client_profiles");
while($row = $r->fetch_assoc()) { echo $row['Field']."\n"; }

echo "\n--- CLIENT_TRAINER_REQUESTS ---\n";
$r = $conn->query("DESCRIBE client_trainer_requests");
while($row = $r->fetch_assoc()) { echo $row['Field']."\n"; }
?>
