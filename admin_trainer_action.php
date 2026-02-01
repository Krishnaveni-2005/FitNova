<?php
session_start();
header("Content-Type: application/json");
require "db_connect.php";
require "config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailsever/PHPMailer-master/PHPMailer-master/src/Exception.php';
require 'phpmailsever/PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
require 'phpmailsever/PHPMailer-master/PHPMailer-master/src/SMTP.php';

/**
 * Send trainer approval email
 */
function sendTrainerApprovalEmail($email, $firstName) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
        $mail->addAddress($email, $firstName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Congratulations! Your FitNova Trainer Account Has Been Approved';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                <div style='background: linear-gradient(135deg, #0F2C59 0%, #4FACFE 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                    <h1 style='margin: 0; font-size: 28px;'>🎊 Congratulations!</h1>
                </div>
                <div style='padding: 30px; background: #f8f9fa;'>
                    <h2 style='color: #0F2C59;'>Hi $firstName,</h2>
                    <p style='font-size: 16px; line-height: 1.6;'>Great news! Your trainer account has been <strong>approved</strong> by our admin team.</p>
                    
                    <div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>
                        <p style='margin: 0; font-size: 15px; color: #155724;'><strong>✅ Account Status:</strong> Active & Approved</p>
                    </div>
                    
                    <p style='font-size: 15px; line-height: 1.6;'>You can now access your trainer dashboard and start:</p>
                    <ul style='font-size: 15px; line-height: 1.8;'>
                        <li>✅ Managing your clients</li>
                        <li>✅ Creating workout and diet plans</li>
                        <li>✅ Scheduling training sessions</li>
                        <li>✅ Tracking client progress</li>
                        <li>✅ Building your fitness community</li>
                    </ul>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='http://localhost/fitnova/login.php' style='display: inline-block; background: #0F2C59; color: white; padding: 15px 40px; text-decoration: none; border-radius: 50px; font-weight: bold; font-size: 16px;'>Access Your Trainer Dashboard</a>
                    </div>
                    
                    <p style='font-size: 15px; line-height: 1.6;'>Welcome to the FitNova trainer community! We're excited to have you on board.</p>
                    
                    <p style='font-size: 15px;'>Best regards,<br><strong>The FitNova Team</strong></p>
                </div>
                <div style='text-align: center; padding: 20px; color: #666; font-size: 12px; background: #f0f0f0; border-radius: 0 0 10px 10px;'>
                    <p style='margin: 5px 0;'>© 2026 FitNova. All rights reserved.</p>
                </div>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Failed to send trainer approval email to $email: " . $mail->ErrorInfo);
        return false;
    }
}

// Check Admin Auth or Gym Owner Auth
$allowed_gym_owner = 'ashakayaplackal@gmail.com';
if (
    !isset($_SESSION['user_id']) || 
    !isset($_SESSION['user_role']) || 
    ($_SESSION['user_role'] !== 'admin' && strtolower($_SESSION['user_email']) !== $allowed_gym_owner)
) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

// Read JSON Input
$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit();
}

$action = $data['action'] ?? '';
$trainer_id = $data['trainer_id'] ?? 0;

if (empty($action) || empty($trainer_id)) {
    echo json_encode(["status" => "error", "message" => "Missing parameters"]);
    exit();
}

$status = '';
if ($action === 'approve') {
    $status = 'active';
} elseif ($action === 'reject') {
    $status = 'inactive'; // Or 'rejected' if ENUM supports it, sticking to inactive for now or delete
    // For now, let's just use 'inactive' as rejection basically
    // If you want proper rejection tracking, add 'rejected' to ENUM
    // Checking previous steps, I added 'suspended' and 'pending'. 'inactive' is fine.
} elseif ($action === 'delete') {
    // Optional: hard delete
    $delStmt = $conn->prepare("UPDATE users SET account_status = 'deleted' WHERE user_id = ? AND role = 'trainer'");
    $delStmt->bind_param("i", $trainer_id);
    if ($delStmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Trainer deleted"]);
        exit();
    } else {
        echo json_encode(["status" => "error", "message" => "Error deleting trainer"]);
        exit();
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid action"]);
    exit();
}

// Update Status
$stmt = $conn->prepare("UPDATE users SET account_status = ? WHERE user_id = ? AND role = 'trainer'");
$stmt->bind_param("si", $status, $trainer_id);

if ($stmt->execute()) {
    // If approved, send approval email
    if ($action === 'approve') {
        // Get trainer details
        $trainerStmt = $conn->prepare("SELECT first_name, email FROM users WHERE user_id = ?");
        $trainerStmt->bind_param("i", $trainer_id);
        $trainerStmt->execute();
        $trainerResult = $trainerStmt->get_result();
        
        if ($trainer = $trainerResult->fetch_assoc()) {
            // Send approval email
            @sendTrainerApprovalEmail($trainer['email'], $trainer['first_name']);
        }
        $trainerStmt->close();
    }
    
    echo json_encode(["status" => "success", "message" => "Trainer request " . $action . "d"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database error"]);
}

$stmt->close();
$conn->close();
?>

