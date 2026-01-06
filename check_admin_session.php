<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    echo json_encode([
        'logged_in' => true,
        'email' => $_SESSION['admin_email'] ?? '',
        'username' => $_SESSION['admin_username'] ?? '',
        'login_time' => $_SESSION['admin_login_time'] ?? 0
    ]);
} else {
    echo json_encode([
        'logged_in' => false
    ]);
}
?>
