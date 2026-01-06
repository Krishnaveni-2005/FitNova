<?php
session_start();
header('Content-Type: application/json');

// Destroy all session data
session_unset();
session_destroy();

echo json_encode([
    'success' => true,
    'message' => 'Logged out successfully'
]);
?>
