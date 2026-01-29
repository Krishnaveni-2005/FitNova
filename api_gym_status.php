<?php
require 'db_connect.php';
header('Content-Type: application/json');

// Fetch Settings
$settings = [];
$res = $conn->query("SELECT * FROM gym_settings");
while($row = $res->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Fetch Equipment
$equipment = [];
$resEq = $conn->query("SELECT * FROM gym_equipment");
while($row = $resEq->fetch_assoc()) {
    $equipment[] = $row;
}

echo json_encode(['settings' => $settings, 'equipment' => $equipment]);
$conn->close();
?>
