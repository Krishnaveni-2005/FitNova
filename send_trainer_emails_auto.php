<?php
/**
 * Send Welcome Emails to Existing Trainers - ONE TIME ONLY
 * This script tracks which trainers have already received emails
 */

require "db_connect.php";
require "config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailsever/PHPMailer-master/PHPMailer-master/src/Exception.php';
require 'phpmailsever/PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
require 'phpmailsever/PHPMailer-master/PHPMailer-master/src/SMTP.php';

// Create email tracking table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS email_sent_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email_type VARCHAR(50) NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_email (user_id, email_type)
)");

function sendTrainerApprovalEmail($email, $firstName) {
    $mail = new PHPMailer(true);
    
    try {
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
        $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
        $mail->addAddress($email, $firstName);
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to FitNova Trainer Community!';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                <div style='background: linear-gradient(135deg, #0F2C59 0%, #4FACFE 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                    <h1 style='margin: 0; font-size: 28px;'>🎊 Welcome to FitNova!</h1>
                </div>
                <div style='padding: 30px; background: #f8f9fa;'>
                    <h2 style='color: #0F2C59;'>Hi $firstName,</h2>
                    <p style='font-size: 16px; line-height: 1.6;'>Thank you for being part of the <strong>FitNova Trainer Community</strong>!</p>
                    <div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>
                        <p style='margin: 0; font-size: 15px; color: #155724;'><strong>✅ Account Status:</strong> Active & Approved</p>
                    </div>
                    <p style='font-size: 15px; line-height: 1.6;'>As an approved FitNova trainer, you have access to:</p>
                    <ul style='font-size: 15px; line-height: 1.8;'>
                        <li>✅ Client management dashboard</li>
                        <li>✅ Workout and diet plan creation tools</li>
                        <li>✅ Session scheduling system</li>
                        <li>✅ Client progress tracking</li>
                        <li>✅ Professional trainer profile</li>
                    </ul>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='http://localhost/fitnova/login.php' style='display: inline-block; background: #0F2C59; color: white; padding: 15px 40px; text-decoration: none; border-radius: 50px; font-weight: bold; font-size: 16px;'>Access Your Trainer Dashboard</a>
                    </div>
                    <p style='font-size: 15px; line-height: 1.6;'>Continue making a difference in your clients' fitness journeys!</p>
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
        return false;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Send Trainer Emails - FitNova</title>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap' rel='stylesheet'>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #e5ddd5;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: #075e54;
            color: white;
            padding: 20px;
        }
        .header h1 {
            font-size: 18px;
            font-weight: 600;
        }
        .header p {
            font-size: 13px;
            opacity: 0.9;
            margin-top: 5px;
        }
        .info-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            margin: 20px;
            border-radius: 5px;
        }
        .info-box h3 {
            color: #856404;
            font-size: 15px;
            margin-bottom: 8px;
        }
        .info-box p {
            color: #856404;
            font-size: 14px;
        }
        .btn-container {
            padding: 20px;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #075e54;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            margin: 5px;
            cursor: pointer;
            border: none;
        }
        .btn:hover {
            background: #064e47;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .progress-bar {
            background: #f0f2f5;
            height: 4px;
        }
        .progress-fill {
            background: #25d366;
            height: 100%;
            width: 0%;
            transition: width 0.3s ease;
        }
        .chat-area {
            background: #e5ddd5;
            padding: 20px;
            min-height: 300px;
            max-height: 500px;
            overflow-y: auto;
        }
        .message {
            display: flex;
            margin-bottom: 15px;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .message-left { justify-content: flex-start; }
        .message-right { justify-content: flex-end; }
        .message-bubble {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .bubble-left {
            background: white;
            border-radius: 0 8px 8px 8px;
        }
        .bubble-right {
            background: #dcf8c6;
            border-radius: 8px 0 8px 8px;
        }
        .sender-name {
            font-weight: 600;
            font-size: 14px;
            color: #075e54;
            margin-bottom: 4px;
        }
        .message-text {
            font-size: 14px;
            color: #303030;
            line-height: 1.4;
        }
        .message-time {
            font-size: 11px;
            color: #667781;
            margin-top: 4px;
            text-align: right;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 5px;
        }
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        .status-skip {
            background: #fff3cd;
            color: #856404;
        }
        .summary-card {
            background: white;
            padding: 20px;
            margin: 15px 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .summary-card h3 {
            color: #075e54;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .summary-stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
        }
        .stat {
            text-align: center;
        }
        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #075e54;
        }
        .stat-label {
            font-size: 12px;
            color: #667781;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>📧 Email Campaign Manager</h1>
            <p>Send welcome emails to trainers (one-time only)</p>
        </div>

<?php
if (!isset($_GET['confirm'])) {
    // Show confirmation page
    $query = "SELECT u.user_id, u.first_name, u.last_name, u.email, 
              (SELECT COUNT(*) FROM email_sent_log WHERE user_id = u.user_id AND email_type = 'trainer_welcome') as already_sent
              FROM users u
              WHERE u.role = 'trainer' AND u.account_status = 'active'
              ORDER BY u.first_name";
    
    $result = $conn->query($query);
    $total = 0;
    $alreadySent = 0;
    $toSend = 0;
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $total++;
            if ($row['already_sent'] > 0) {
                $alreadySent++;
            } else {
                $toSend++;
            }
        }
    }
    
    echo "<div class='info-box'>";
    echo "<h3>📊 Campaign Summary</h3>";
    echo "<p><strong>Total trainers:</strong> $total<br>";
    echo "<strong>Already received email:</strong> $alreadySent<br>";
    echo "<strong>Will receive email:</strong> $toSend</p>";
    echo "</div>";
    
    if ($toSend > 0) {
        echo "<div class='btn-container'>";
        echo "<a href='?confirm=yes' class='btn'>✅ Send Emails to $toSend Trainers</a>";
        echo "<a href='admin_dashboard.php' class='btn btn-danger'>❌ Cancel</a>";
        echo "</div>";
    } else {
        echo "<div class='info-box' style='background: #d4edda; border-color: #28a745;'>";
        echo "<h3 style='color: #155724;'>✅ All Done!</h3>";
        echo "<p style='color: #155724;'>All trainers have already received their welcome emails.</p>";
        echo "</div>";
        echo "<div class='btn-container'>";
        echo "<a href='admin_dashboard.php' class='btn'>Back to Dashboard</a>";
        echo "</div>";
    }
    
} else {
    // Send emails
    echo "<div class='progress-bar'><div class='progress-fill' id='progressBar'></div></div>";
    echo "<div class='chat-area' id='chatArea'>";
    
    $query = "SELECT u.user_id, u.first_name, u.last_name, u.email
              FROM users u
              LEFT JOIN email_sent_log e ON u.user_id = e.user_id AND e.email_type = 'trainer_welcome'
              WHERE u.role = 'trainer' AND u.account_status = 'active' AND e.id IS NULL
              ORDER BY u.first_name";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $total = $result->num_rows;
        $sent = 0;
        $current = 0;
        
        while ($trainer = $result->fetch_assoc()) {
            $current++;
            $userId = $trainer['user_id'];
            $email = $trainer['email'];
            $firstName = $trainer['first_name'];
            $lastName = $trainer['last_name'];
            
            // Left message
            echo "<div class='message message-left'>";
            echo "<div class='message-bubble bubble-left'>";
            echo "<div class='sender-name'>$firstName $lastName</div>";
            echo "<div class='message-text'>$email</div>";
            echo "<div class='message-time'>" . date('g:i A') . "</div>";
            echo "</div></div>";
            
            // Send email
            $success = sendTrainerApprovalEmail($email, $firstName);
            
            if ($success) {
                // Log the email
                $logStmt = $conn->prepare("INSERT INTO email_sent_log (user_id, email_type) VALUES (?, 'trainer_welcome')");
                $logStmt->bind_param("i", $userId);
                $logStmt->execute();
                
                echo "<div class='message message-right'>";
                echo "<div class='message-bubble bubble-right'>";
                echo "<div class='message-text'>✅ Welcome email sent successfully!</div>";
                echo "<span class='status-badge status-success'>Delivered</span>";
                echo "<div class='message-time'>" . date('g:i A') . "</div>";
                echo "</div></div>";
                $sent++;
            }
            
            $percentage = ($current / $total) * 100;
            echo "<script>";
            echo "document.getElementById('progressBar').style.width = '$percentage%';";
            echo "document.getElementById('chatArea').scrollTop = document.getElementById('chatArea').scrollHeight;";
            echo "</script>";
            
            flush();
            ob_flush();
            usleep(500000);
        }
        
        echo "</div>";
        
        echo "<div class='summary-card'>";
        echo "<h3>✅ Campaign Complete!</h3>";
        echo "<div class='summary-stats'>";
        echo "<div class='stat'><div class='stat-value'>$sent</div><div class='stat-label'>Emails Sent</div></div>";
        echo "<div class='stat'><div class='stat-value'>$total</div><div class='stat-label'>Total Processed</div></div>";
        echo "</div>";
        echo "<a href='admin_dashboard.php' class='btn'>Back to Dashboard</a>";
        echo "</div>";
        
    } else {
        echo "<div class='message message-left'>";
        echo "<div class='message-bubble bubble-left'>";
        echo "<div class='message-text'>✅ All trainers have already received emails!</div>";
        echo "</div></div>";
        echo "</div>";
        echo "<div class='summary-card'>";
        echo "<a href='admin_dashboard.php' class='btn'>Back to Dashboard</a>";
        echo "</div>";
    }
}

echo "</div></body></html>";
$conn->close();
?>
