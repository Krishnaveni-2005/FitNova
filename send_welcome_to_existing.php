<?php
/**
 * Send Welcome Emails to Existing Users
 * This script sends welcome emails to all users who signed up before the email feature was added
 */

require "db_connect.php";
require "config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailsever/PHPMailer-master/PHPMailer-master/src/Exception.php';
require 'phpmailsever/PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
require 'phpmailsever/PHPMailer-master/PHPMailer-master/src/SMTP.php';

/**
 * Send welcome email using PHPMailer
 */
function sendWelcomeEmail($email, $firstName) {
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
        $mail->Subject = 'Welcome to FitNova - Account Created Successfully!';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                <div style='background: linear-gradient(135deg, #0F2C59 0%, #4FACFE 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                    <h1 style='margin: 0; font-size: 28px;'>🎉 Welcome to FitNova!</h1>
                </div>
                <div style='padding: 30px; background: #f8f9fa;'>
                    <h2 style='color: #0F2C59;'>Hi $firstName,</h2>
                    <p style='font-size: 16px; line-height: 1.6;'>Thank you for being part of <strong>FitNova</strong> - Your Health & Wellness Ecosystem.</p>
                    
                    <p style='font-size: 16px; line-height: 1.6;'>We're excited to have you on board! Here's what you can do with your account:</p>
                    <ul style='font-size: 15px; line-height: 1.8;'>
                        <li>✅ Access personalized fitness and nutrition plans</li>
                        <li>✅ Connect with certified trainers</li>
                        <li>✅ Track your progress and achievements</li>
                        <li>✅ Shop premium fitness equipment</li>
                        <li>✅ Learn from expert articles and resources</li>
                    </ul>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='http://localhost/fitnova/login.php' style='display: inline-block; background: #0F2C59; color: white; padding: 15px 40px; text-decoration: none; border-radius: 50px; font-weight: bold; font-size: 16px;'>Login to Your Account</a>
                    </div>
                    
                    <p style='font-size: 15px; line-height: 1.6;'>If you have any questions or need assistance, feel free to reach out to our support team.</p>
                    
                    <p style='font-size: 15px; line-height: 1.6;'>Let's continue your fitness journey together!</p>
                    
                    <p style='font-size: 15px;'>Best regards,<br><strong>The FitNova Team</strong></p>
                </div>
                <div style='text-align: center; padding: 20px; color: #666; font-size: 12px; background: #f0f0f0; border-radius: 0 0 10px 10px;'>
                    <p style='margin: 5px 0;'>© 2026 FitNova. All rights reserved.</p>
                    <p style='margin: 5px 0;'>This is an automated message. Please do not reply to this email.</p>
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
    <title>Send Welcome Emails to Existing Users</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        h1 { color: #0F2C59; }
        .user { padding: 10px; margin: 5px 0; background: #f8f9fa; border-left: 4px solid #0F2C59; }
        .success { border-left-color: #28a745; background: #d4edda; }
        .error { border-left-color: #dc3545; background: #f8d7da; }
        .stats { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0; }
        button { background: #0F2C59; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #0a1f40; }
    </style>
</head>
<body>
    <h1>📧 Send Welcome Emails to Existing Users</h1>";

if (isset($_POST['send_emails'])) {
    echo "<h2>Sending Emails...</h2>";
    
    // Get all non-trainer users who don't have pending status
    $query = "SELECT user_id, first_name, last_name, email, role FROM users 
              WHERE role != 'trainer' AND (account_status = 'active' OR account_status IS NULL)
              ORDER BY user_id ASC";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $total = $result->num_rows;
        $sent = 0;
        $failed = 0;
        
        echo "<div class='stats'>";
        echo "<strong>Total users to email: $total</strong>";
        echo "</div>";
        
        while ($user = $result->fetch_assoc()) {
            $email = $user['email'];
            $firstName = $user['first_name'];
            $userId = $user['user_id'];
            
            echo "<div class='user'>";
            echo "Sending to: <strong>$firstName ($email)</strong>... ";
            
            if (sendWelcomeEmail($email, $firstName)) {
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
        
        echo "<p><a href='send_welcome_to_existing.php'><button>Back</button></a></p>";
        
    } else {
        echo "<p>No users found to send emails to.</p>";
    }
    
} else {
    // Show user list and confirmation
    $query = "SELECT user_id, first_name, last_name, email, role, created_at FROM users 
              WHERE role != 'trainer' AND (account_status = 'active' OR account_status IS NULL)
              ORDER BY user_id ASC";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $total = $result->num_rows;
        
        echo "<div class='stats'>";
        echo "<h3>📊 Users to Receive Welcome Email</h3>";
        echo "<p>Total: <strong>$total users</strong></p>";
        echo "</div>";
        
        echo "<h3>User List:</h3>";
        echo "<div style='max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 5px;'>";
        
        while ($user = $result->fetch_assoc()) {
            echo "<div class='user'>";
            echo "<strong>{$user['first_name']} {$user['last_name']}</strong><br>";
            echo "Email: {$user['email']}<br>";
            echo "Role: {$user['role']}<br>";
            echo "Joined: {$user['created_at']}";
            echo "</div>";
        }
        
        echo "</div>";
        
        echo "<form method='POST' style='margin-top: 20px;'>";
        echo "<p><strong>⚠️ This will send welcome emails to all $total users listed above.</strong></p>";
        echo "<p>Are you sure you want to proceed?</p>";
        echo "<button type='submit' name='send_emails'>📧 Send Welcome Emails to All Users</button>";
        echo "</form>";
        
    } else {
        echo "<p>No users found in the database.</p>";
    }
}

echo "</body></html>";

$conn->close();
?>
