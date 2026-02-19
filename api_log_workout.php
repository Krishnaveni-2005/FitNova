<?php
session_start();
require 'db_connect.php';
require 'gamification_helper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

// Handle GET Request (Fetch Logs)
if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'fetch_logs') {
        // Fetch last 50 logs
        $sql = "SELECT * FROM user_activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 50";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Fetch User Stats
        $stats = getUserStats($userId);
        
        echo json_encode([
            'success' => true, 
            'logs' => $logs,
            'stats' => $stats
        ]);
        exit();
    }
}

// Handle POST Request (Save Log)
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['action']) && $input['action'] === 'log_workout') {
    $activity = $conn->real_escape_string($input['activity']);
    $duration = intval($input['duration']);
    $calories = intval($input['calories']);
    $date = date('Y-m-d'); // Today's date

    if (empty($activity) || $duration <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid Access']);
        exit();
    }

    // 1. Insert detailed log
    $stmt = $conn->prepare("INSERT INTO user_activity_logs (user_id, activity_type, duration_minutes, calories_burned, log_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isiis", $userId, $activity, $duration, $calories, $date);
    
    if ($stmt->execute()) {
        // 2. Update Progress Stats
        // We increment `completed_workouts` and add calories to `total_calories` (if schema supports it, otherwise just tracking points)
        // Note: gamification_helper might need update to track total calories if not already
        
        // Check if total_calories column exists in user_gamification_stats, if not add it dynamically? 
        // Better to assume it might not exist and handle potential error or update logic.
        // For now, let's update what we know exists: completed_workouts, total_points.
        
        // Let's check if we can update total_calories. The original gamification_helper didn't show the schema explicitly but implied it.
        // I will assume it exists or add it blindly. actually, wait.
        // gamification_helper.php line 82: "SELECT total_calories FROM user_gamification_stats..."
        // So `total_calories` DOES exist.
        
        $updateSql = "UPDATE user_gamification_stats 
                      SET completed_workouts = completed_workouts + 1, 
                          total_points = total_points + 50,
                          total_calories = total_calories + $calories
                      WHERE user_id = $userId";
        $conn->query($updateSql);

        // 3. Award Badges
        checkAndAwardBadges($userId);

        // 4. Return new stats
        $newStats = getUserStats($userId);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Workout logged successfully!',
            'stats' => $newStats
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Action']);
}
?>
