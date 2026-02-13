<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect.php';

// Set Timezone to IST (Indian Standard Time) for consistent day tracking
date_default_timezone_set('Asia/Kolkata');

function checkAndAwardBadges($userId) {
    global $conn;
    
    // Strict Check: Role
    $roleCheck = $conn->query("SELECT role FROM users WHERE user_id = $userId");
    if (!$roleCheck || $roleCheck->num_rows == 0) return; // User not found
    $userRole = $roleCheck->fetch_assoc()['role'];

    // Specific Logic for Trainers
    if ($userRole === 'trainer') {
        checkAndAwardTrainerBadges($userId);
        return;
    }

    // Client Logic: Only 'free', 'lite', 'pro', 'elite' participate
    if (!in_array($userRole, ['free', 'lite', 'pro', 'elite'])) return; 
    
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    
    // 1. Check/Create User Stats Record
    $checkStats = $conn->query("SELECT * FROM user_gamification_stats WHERE user_id = $userId");
    if ($checkStats->num_rows == 0) {
        $conn->query("INSERT INTO user_gamification_stats (user_id, total_points, current_streak, last_login_date) VALUES ($userId, 0, 1, '$today')");
    } else {
        $stats = $checkStats->fetch_assoc();
        
        // Update Streak Logic
        $lastLogin = $stats['last_login_date']; 
        
        if ($lastLogin != $today) {
            if ($lastLogin == $yesterday) {
                // Streak continues!
                $newStreak = $stats['current_streak'] + 1;
                $conn->query("UPDATE user_gamification_stats SET current_streak = $newStreak, last_login_date = '$today', total_points = total_points + 10 WHERE user_id = $userId");
            } else {
                // Streak broken
                $conn->query("UPDATE user_gamification_stats SET current_streak = 1, last_login_date = '$today', total_points = total_points + 5 WHERE user_id = $userId");
            }
        }
    }
    
    // 2. Fetch User Badges
    $userBadges = [];
    $res = $conn->query("SELECT badge_id FROM user_badges WHERE user_id = $userId");
    while ($row = $res->fetch_assoc()) {
        $userBadges[] = $row['badge_id'];
    }

    // 3. Check Badge Criteria (Clients)
    $allBadges = $conn->query("SELECT * FROM gamification_badges WHERE target_role = 'client' OR target_role = 'all'");
    
    while ($badge = $allBadges->fetch_assoc()) {
        if (in_array($badge['badge_id'], $userBadges)) continue; // Already earned

        $earned = false;
        
        switch ($badge['criteria_type']) {
            case 'join': $earned = true; break;
            case 'profile_complete':
                $checkProfile = $conn->query("SELECT user_id FROM client_profiles WHERE user_id = $userId");
                if ($checkProfile->num_rows > 0) $earned = true;
                break;
            case 'gym_member':
                $checkGym = $conn->query("SELECT gym_membership_status FROM users WHERE user_id = $userId AND gym_membership_status = 'active'");
                if ($checkGym->num_rows > 0) $earned = true;
                break;
            case 'workout_milestone':
                $currentStats = getUserStats($userId);
                if ($currentStats['completed_workouts'] >= $badge['criteria_value']) $earned = true;
                break;
            case 'calories_milestone':
                $checkCyr = $conn->query("SELECT total_calories FROM user_gamification_stats WHERE user_id = $userId");
                $currCals = $checkCyr->fetch_assoc()['total_calories'] ?? 0;
                if ($currCals >= $badge['criteria_value']) $earned = true;
                break;
            case 'late_workout': if (date('H') >= 21) $earned = true; break;
            case 'weekend_workout': if (date('N') >= 6) $earned = true; break;
            case 'streak':
                $checkStreak = $conn->query("SELECT current_streak FROM user_gamification_stats WHERE user_id = $userId");
                $streak = $checkStreak->fetch_assoc()['current_streak'];
                if ($streak >= $badge['criteria_value']) $earned = true;
                break;
            case 'early_login': if (date('H') < 8) $earned = true; break;
        }
        
        if ($earned) {
            $stmt = $conn->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $userId, $badge['badge_id']);
            $stmt->execute();
            $conn->query("UPDATE user_gamification_stats SET total_points = total_points + 50 WHERE user_id = $userId");
        }
    }
}

function checkAndAwardTrainerBadges($trainerId) {
    global $conn;

    // 1. Fetch Earned Badges
    $earnedBadges = [];
    $res = $conn->query("SELECT badge_id FROM user_badges WHERE user_id = $trainerId");
    while ($row = $res->fetch_assoc()) {
        $earnedBadges[] = $row['badge_id'];
    }

    // 2. Fetch Trainer Badges
    $allBadges = $conn->query("SELECT * FROM gamification_badges WHERE target_role = 'trainer'");
    
    // 3. Pre-fetch Trainer Stats
    $clientCount = 0;
    $res = $conn->query("SELECT COUNT(*) as cnt FROM users WHERE assigned_trainer_id = $trainerId AND assignment_status = 'approved'");
    if ($res) $clientCount = $res->fetch_assoc()['cnt'];

    $sessionCount = 0;
    $res = $conn->query("SELECT COUNT(*) as cnt FROM trainer_schedules WHERE trainer_id = $trainerId"); 
    if ($res) $sessionCount = $res->fetch_assoc()['cnt'];

    // Rating (Scaled by 10)
    $rating = 0;
    $res = $conn->query("SELECT AVG(rating) as avg_rating FROM trainer_ratings WHERE trainer_id = $trainerId");
    if ($res) $rating = $res->fetch_assoc()['avg_rating'] ?? 0;

    while ($badge = $allBadges->fetch_assoc()) {
        if (in_array($badge['badge_id'], $earnedBadges)) continue;

        $earned = false;

        switch ($badge['criteria_type']) {
            case 'trainer_join': $earned = true; break;
            case 'client_count': if ($clientCount >= $badge['criteria_value']) $earned = true; break;
            case 'session_count': if ($sessionCount >= $badge['criteria_value']) $earned = true; break;
            case 'rating_milestone': if (($rating * 10) >= $badge['criteria_value']) $earned = true; break;
        }

        if ($earned) {
            $stmt = $conn->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $trainerId, $badge['badge_id']);
            $stmt->execute();
        }
    }
}

function getUserBadges($userId) {
    global $conn;
    $sql = "SELECT b.*, ub.earned_at 
            FROM gamification_badges b 
            JOIN user_badges ub ON b.badge_id = ub.badge_id 
            WHERE ub.user_id = $userId 
            ORDER BY ub.earned_at DESC";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

function getUserStats($userId) {
    global $conn;
    $res = $conn->query("SELECT * FROM user_gamification_stats WHERE user_id = $userId");
    if ($res->num_rows > 0) return $res->fetch_assoc();
    return ['total_points' => 0, 'current_streak' => 0];
}

function getLeaderboard($limit = 5) {
    global $conn;
    // Get top free users by points
    $sql = "SELECT u.first_name, u.last_name, s.total_points, s.current_streak, u.user_id
            FROM user_gamification_stats s
            JOIN users u ON s.user_id = u.user_id
            WHERE u.role = 'free'
            ORDER BY s.total_points DESC
            LIMIT $limit";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}
?>
