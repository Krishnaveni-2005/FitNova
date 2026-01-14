<?php
require 'db_connect.php';

$trainersToSet = ['Joshua Joseph', 'David John', 'Elis Reji'];
$zone = "Gym Floor";

echo "<h2>Setting On-Site Trainers</h2>";

foreach ($trainersToSet as $fullName) {
    // Split name
    $parts = explode(' ', $fullName);
    $firstName = $parts[0];
    $lastName = isset($parts[1]) ? $parts[1] : '';

    echo "Processing $fullName...<br>";

    // Find User ID
    $sql = "SELECT user_id, first_name, last_name FROM users WHERE first_name LIKE ? AND last_name LIKE ?";
    $stmt = $conn->prepare($sql);
    $fLike = "%$firstName%";
    $lLike = "%$lastName%";
    $stmt->bind_param("ss", $fLike, $lLike);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        $userId = $user['user_id'];
        echo "Found ID: " . $userId . " (" . $user['first_name'] . " " . $user['last_name'] . ")<br>";

        // Check if already checked in today
        $checkSql = "SELECT id FROM trainer_attendance WHERE trainer_id = ? AND DATE(check_in_time) = CURDATE()";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("i", $userId);
        $checkStmt->execute();
        
        if ($checkStmt->get_result()->num_rows > 0) {
            echo "Already checked in today. Updating status to checked_in just in case.<br>";
            $updSql = "UPDATE trainer_attendance SET status = 'checked_in', check_out_time = NULL WHERE trainer_id = ? AND DATE(check_in_time) = CURDATE()";
            $updStmt = $conn->prepare($updSql);
            $updStmt->bind_param("i", $userId);
            $updStmt->execute();
        } else {
            $insSql = "INSERT INTO trainer_attendance (trainer_id, check_in_time, zone, status) VALUES (?, NOW(), ?, 'checked_in')";
            $insStmt = $conn->prepare($insSql);
            $insStmt->bind_param("is", $userId, $zone);
            if ($insStmt->execute()) {
                echo "Successfully checked in.<br>";
            } else {
                echo "Error checking in: " . $insStmt->error . "<br>";
            }
        }
    } else {
        echo "Trainer not found.<br>";
    }
    echo "<hr>";
}
?>
