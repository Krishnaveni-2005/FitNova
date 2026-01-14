<?php
session_start();
header('Content-Type: application/json');
require 'db_connect.php';

// Authentication check
$allowed_roles = ['gym_admin', 'admin'];
$allowed_emails = ['ashakayaplackal@gmail.com'];
$user_email = $_SESSION['user_email'] ?? '';

if (!in_array($_SESSION['user_role'] ?? 'guest', $allowed_roles) && !in_array($user_email, $allowed_emails)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['gym_open_time']) || !isset($data['gym_close_time']) || !isset($data['gym_status'])) {
    echo json_encode(['success' => false, 'message' => 'Incomplete data']);
    exit();
}

$settings = [
    'gym_open_time' => $data['gym_open_time'],
    'gym_close_time' => $data['gym_close_time'],
    'gym_status' => $data['gym_status']
];

foreach ($settings as $key => $val) {
    // Upsert logic
    $check = $conn->query("SELECT setting_key FROM gym_settings WHERE setting_key = '$key'");
    if ($check->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE gym_settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->bind_param("ss", $val, $key);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO gym_settings (setting_key, setting_value) VALUES (?, ?)");
        $stmt->bind_param("ss", $key, $val);
        $stmt->execute();
    }
}

echo json_encode(['success' => true]);
?>
