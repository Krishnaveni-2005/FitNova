<?php
session_start();
require 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$clientId = $input['client_id'] ?? 0;
$trainerId = $input['trainer_id'] ?? 0;

if ($clientId && $trainerId) {
    // Ensure ENUM has 'admin_suggested'
    $conn->query("ALTER TABLE trainer_applications MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'admin_suggested') DEFAULT 'pending'");

    // Insert or Update Application
    // We utilize ON DUPLICATE KEY UPDATE to handle re-suggestions
    $stmt = $conn->prepare("INSERT INTO trainer_applications (client_id, trainer_id, status) VALUES (?, ?, 'admin_suggested') ON DUPLICATE KEY UPDATE status = 'admin_suggested'");
    $stmt->bind_param("ii", $clientId, $trainerId);
    
    if($stmt->execute()) {
        // Notify Trainer
        $cRes = $conn->query("SELECT first_name, last_name FROM users WHERE user_id = $clientId");
        $cName = "Client";
        if($r = $cRes->fetch_assoc()) $cName = $r['first_name'] . ' ' . $r['last_name'];

        $msg = "Admin has matched you with a potential client: $cName. Please review in Client Opportunities.";
        $conn->query("INSERT INTO user_notifications (user_id, notification_type, message) VALUES ($trainerId, 'admin_suggestion', '$msg')");
        
        // --- WHATSAPP NOTIFICATION TO TRAINER ---
        require_once 'twilio_helper.php';
        
        // Fetch Trainer's Phone Number
        $tRes = $conn->query("SELECT phone_number FROM users WHERE user_id = $trainerId");
        if ($tRow = $tRes->fetch_assoc()) {
            $trainerPhone = $tRow['phone_number'];
            if (!empty($trainerPhone)) {
                // Format phone number if necessary (ensure +91 or similar country code)
                // Assuming stored number might not have +, add if missing. For now, trust stored format or just prepend if standard length.
                // Better approach: User helper function to format or assume user entered correctly.
                
                // IMPORTANT: For Twilio Sandbox, the user MUST have joined the sandbox.
                // Sending message:
                $waMsg = "New Client Opportunity!\nAdmin has matched you with potential client: $cName.\nCheck your dashboard under 'Client Opportunities' to accept.";
                
                if (function_exists('sendWhatsAppNotification')) {
                    sendWhatsAppNotification($waMsg, $trainerPhone);
                }
            }
        }
        // ----------------------------------------
        
        echo json_encode(['success' => true]);
    } else {
         echo json_encode(['success' => false, 'message' => 'Database Error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
}
?>
