<?php
session_start();
require 'db_connect.php';
require 'gamification_helper.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die("Unauthorized");
}

$userId = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['action']) && $input['action'] === 'log_workout') {
    // 1. Update Workout Count
    $conn->query("UPDATE user_gamification_stats SET completed_workouts = completed_workouts + 1, total_points = total_points + 50 WHERE user_id = $userId");
    
    // 2. Check for Badges (Helper function handles the logic)
    checkAndAwardBadges($userId);
    
    // 3. Return New Status
    $stats = getUserStats($userId);
    echo json_encode(['success' => true, 'workouts' => $stats['completed_workouts'], 'points' => $stats['total_points']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Action']);
}
?>
