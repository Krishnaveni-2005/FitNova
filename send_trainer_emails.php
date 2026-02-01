<?php
/**
 * Send Approval Emails to Existing Trainers
 * This script sends approval/welcome emails to trainers who were approved before the email feature
 */

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

echo "<!DOCTYPE html>
<html>
<head>
    <title>Send Emails to Existing Trainers</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        h1 { color: #0F2C59; }
        .trainer { padding: 10px; margin: 5px 0; background: #f8f9fa; border-left: 4px solid #0F2C59; }
        .success { border-left-color: #28a745; background: #d4edda; }
        .error { border-left-color: #dc3545; background: #f8d7da; }
        .pending { border-left-color: #ffc107; background: #fff3cd; }
        .stats { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0; }
        button { background: #0F2C59; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin: 5px; }
        button:hover { background: #0a1f40; }
        .btn-pending { background: #ffc107; color: #000; }
        .btn-pending:hover { background: #e0a800; }
    </style>
</head>
<body>
    <h1>📧 Send Emails to Existing Trainers</h1>";

if (isset($_POST['send_emails'])) {
    $status_filter = $_POST['status_filter'] ?? 'active';
    
    echo "<h2>Sending Emails...</h2>";
    
    // Get trainers based on status
    if ($status_filter === 'all') {
        $query = "SELECT user_id, first_name, last_name, email, account_status, created_at FROM users 
                  WHERE role = 'trainer'
                  ORDER BY user_id ASC";
    } else {
        $query = "SELECT user_id, first_name, last_name, email, account_status, created_at FROM users 
                  WHERE role = 'trainer' AND account_status = '$status_filter'
                  ORDER BY user_id ASC";
    }
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $total = $result->num_rows;
        $sent = 0;
        $failed = 0;
        
        echo "<div class='stats'>";
        echo "<strong>Total trainers to email: $total</strong>";
        echo "</div>";
        
        while ($trainer = $result->fetch_assoc()) {
            $email = $trainer['email'];
            $firstName = $trainer['first_name'];
            $status = $trainer['account_status'];
            
            echo "<div class='trainer'>";
            echo "Sending to: <strong>$firstName ($email)</strong> - Status: <em>$status</em>... ";
            
            if (sendTrainerApprovalEmail($email, $firstName)) {
                echo "<span style='color: green;'>✅ Sent!</span>";
                $sent++;
            } else {
                echo "<span style='color: red;'>❌ Failed</span>";
                $failed++;
            }
            
            echo "</div>";
            flush();
            ob_flush();
            
            // Small delay to avoid overwhelming SMTP server
            usleep(500000); // 0.5 second delay
        }
        
        echo "<div class='stats'>";
        echo "<h3>Summary:</h3>";
        echo "<p>✅ Successfully sent: <strong>$sent</strong></p>";
        echo "<p>❌ Failed: <strong>$failed</strong></p>";
        echo "<p>📊 Total: <strong>$total</strong></p>";
        echo "</div>";
        
        echo "<p><a href='send_trainer_emails.php'><button>Back</button></a></p>";
        
    } else {
        echo "<p>No trainers found matching the criteria.</p>";
        echo "<p><a href='send_trainer_emails.php'><button>Back</button></a></p>";
    }
    
} else {
    // Show trainer lists by status
    echo "<h2>Select Trainers to Email</h2>";
    
    // Get counts by status
    $activeQuery = "SELECT COUNT(*) as count FROM users WHERE role = 'trainer' AND account_status = 'active'";
    $pendingQuery = "SELECT COUNT(*) as count FROM users WHERE role = 'trainer' AND account_status = 'pending'";
    $allQuery = "SELECT COUNT(*) as count FROM users WHERE role = 'trainer'";
    
    $activeCount = $conn->query($activeQuery)->fetch_assoc()['count'];
    $pendingCount = $conn->query($pendingQuery)->fetch_assoc()['count'];
    $allCount = $conn->query($allQuery)->fetch_assoc()['count'];
    
    echo "<div class='stats'>";
    echo "<h3>📊 Trainer Statistics</h3>";
    echo "<p>✅ Active Trainers: <strong>$activeCount</strong></p>";
    echo "<p>⏳ Pending Trainers: <strong>$pendingCount</strong></p>";
    echo "<p>📊 Total Trainers: <strong>$allCount</strong></p>";
    echo "</div>";
    
    // Show active trainers
    echo "<h3>✅ Active Trainers ($activeCount)</h3>";
    $activeResult = $conn->query("SELECT user_id, first_name, last_name, email, trainer_specialization, created_at 
                                   FROM users WHERE role = 'trainer' AND account_status = 'active' 
                                   ORDER BY first_name");
    
    if ($activeResult && $activeResult->num_rows > 0) {
        echo "<div style='max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 5px; margin-bottom: 20px;'>";
        while ($trainer = $activeResult->fetch_assoc()) {
            echo "<div class='trainer success'>";
            echo "<strong>{$trainer['first_name']} {$trainer['last_name']}</strong><br>";
            echo "Email: {$trainer['email']}<br>";
            echo "Specialization: {$trainer['trainer_specialization']}<br>";
            echo "Joined: {$trainer['created_at']}";
            echo "</div>";
        }
        echo "</div>";
        
        echo "<form method='POST' style='margin-bottom: 30px;'>";
        echo "<input type='hidden' name='status_filter' value='active'>";
        echo "<p><strong>Send welcome emails to all $activeCount active trainers?</strong></p>";
        echo "<button type='submit' name='send_emails'>📧 Send to Active Trainers ($activeCount)</button>";
        echo "</form>";
    } else {
        echo "<p>No active trainers found.</p>";
    }
    
    // Show pending trainers
    if ($pendingCount > 0) {
        echo "<h3>⏳ Pending Trainers ($pendingCount)</h3>";
        $pendingResult = $conn->query("SELECT user_id, first_name, last_name, email, trainer_specialization, created_at 
                                       FROM users WHERE role = 'trainer' AND account_status = 'pending' 
                                       ORDER BY first_name");
        
        echo "<div style='max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 5px; margin-bottom: 20px;'>";
        while ($trainer = $pendingResult->fetch_assoc()) {
            echo "<div class='trainer pending'>";
            echo "<strong>{$trainer['first_name']} {$trainer['last_name']}</strong><br>";
            echo "Email: {$trainer['email']}<br>";
            echo "Specialization: {$trainer['trainer_specialization']}<br>";
            echo "Applied: {$trainer['created_at']}";
            echo "</div>";
        }
        echo "</div>";
        
        echo "<form method='POST' style='margin-bottom: 30px;'>";
        echo "<input type='hidden' name='status_filter' value='pending'>";
        echo "<p><strong>⚠️ Note:</strong> Pending trainers will receive \"application under review\" notification.</p>";
        echo "<button type='submit' name='send_emails' class='btn-pending'>📧 Send to Pending Trainers ($pendingCount)</button>";
        echo "</form>";
    }
    
    // Send to all option
    echo "<hr>";
    echo "<form method='POST'>";
    echo "<input type='hidden' name='status_filter' value='all'>";
    echo "<p><strong>Send emails to ALL $allCount trainers (both active and pending)?</strong></p>";
    echo "<button type='submit' name='send_emails'>📧 Send to All Trainers ($allCount)</button>";
    echo "</form>";
}

echo "</body></html>";

$conn->close();
?>
