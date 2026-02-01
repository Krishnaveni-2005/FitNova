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
                    <p style='font-size: 16px; line-height: 1.6;'>Congratulations! You have successfully created an account with <strong>FitNova</strong> - Your Health & Wellness Ecosystem.</p>
                    
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
                    
                    <p style='font-size: 15px; line-height: 1.6;'>Let's start your fitness journey together!</p>
                    
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
        error_log("Failed to send welcome email to $email: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Send trainer pending approval email
 */
function sendTrainerPendingEmail($email, $firstName) {
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
        $mail->Subject = 'FitNova Trainer Application - Under Review';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                <div style='background: linear-gradient(135deg, #0F2C59 0%, #4FACFE 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                    <h1 style='margin: 0; font-size: 28px;'>📋 Application Received</h1>
                </div>
                <div style='padding: 30px; background: #f8f9fa;'>
                    <h2 style='color: #0F2C59;'>Hi $firstName,</h2>
                    <p style='font-size: 16px; line-height: 1.6;'>Thank you for applying to become a trainer at <strong>FitNova</strong>!</p>
                    
                    <div style='background: #e3f2fd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #0F2C59;'>
                        <p style='margin: 0; font-size: 15px;'><strong>📌 Application Status:</strong> Under Review</p>
                    </div>
                    
                    <p style='font-size: 15px; line-height: 1.6;'>Your application is currently being reviewed by our admin team. We'll carefully review your credentials and experience.</p>
                    
                    <p style='font-size: 15px; line-height: 1.6;'><strong>What happens next?</strong></p>
                    <ul style='font-size: 15px; line-height: 1.8;'>
                        <li>Our team will review your application and certification</li>
                        <li>You'll receive an email notification once reviewed</li>
                        <li>This typically takes 1-2 business days</li>
                    </ul>
                    
                    <p style='font-size: 15px; line-height: 1.6;'>Thank you for your patience!</p>
                    
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
        error_log("Failed to send trainer pending email to $email: " . $mail->ErrorInfo);
        return false;
    }
}

// 0. Parse Input (Handle both JSON and FormData)
$data = null;
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

if (strpos($contentType, 'application/json') !== false) {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);
}

if (!$data) {
    $data = $_POST;
}

$response = array();

// Helper function to get dashboard redirect based on role
function getDashboardRedirect($role) {
    switch ($role) {
        case "admin": return "admin_dashboard.php";
        case "trainer": return "trainer_dashboard.php";
        case "pro": return "prouser_dashboard.php";
        case "lite": return "liteuser_dashboard.php";
        default: return "freeuser_dashboard.php";
    }
}

// 1. HANDLE GOOGLE AUTH (Sign In or Sign Up)
if (isset($data["action"]) && $data["action"] === "google_auth") {
    $email = $data["email"] ?? "";
    $firstName = $data["firstName"] ?? "";
    $lastName = $data["lastName"] ?? "";
    $googleId = $data["sub"] ?? "";
    $emailVerified = $data["emailVerified"] ?? false;

    if (empty($email)) {
        echo json_encode(["status" => "error", "message" => "Google email missing from request"]);
        exit();
    }

    if (!$emailVerified) {
        echo json_encode(["status" => "error", "message" => "This Google account email is not verified by Google."]);
        exit();
    }

    // Use Prepared Statements
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    $source = $data["source"] ?? "signup";

    if ($result->num_rows > 0) {
        // EXISTING USER
        $user = $result->fetch_assoc();
        
        // Update provider if it was local before or missing oauth id
        if ($user["auth_provider"] !== "google" || empty($user["oauth_provider_id"])) {
             $updateStmt = $conn->prepare("UPDATE users SET auth_provider='google', oauth_provider_id=?, is_email_verified=1 WHERE email=?");
             $updateStmt->bind_param("ss", $googleId, $email);
             $updateStmt->execute();
        }
        
        if ($source === "login") {
            // ACTUALLY LOGGING IN
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["user_name"] = $user["first_name"];
            $_SESSION["user_email"] = $user["email"];
            $role = $user["role"] ?? "free";
            $_SESSION["user_role"] = $role;
            
            $response["status"] = "success";
            $response["message"] = "Login successful";
            // Special redirect for Gym Owner
            if ($user["email"] === 'ashakayaplackal@gmail.com') {
                $response["redirect"] = "gym_owner_dashboard.php";
            } else {
                $response["redirect"] = getDashboardRedirect($role);
            }
        } else {
            // SIGNING UP again from signup page
            $response["status"] = "success";
            $response["message"] = "Account already exists with this email. Please log in.";
            $response["redirect"] = "login.php"; 
        }
    } else {
        // NEW USER - SIGNING UP VIA GOOGLE
        
        // Strict Login Rule: If trying to login but account doesn't exist, deny.
        if ($source === "login") {
            echo json_encode(["status" => "error", "message" => "Account does not exist. Please sign up first."]);
            exit();
        }

        $insertStmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, auth_provider, oauth_provider_id, is_email_verified) VALUES (?, ?, ?, 'google', ?, 1)");
        $insertStmt->bind_param("ssss", $firstName, $lastName, $email, $googleId);
        
        if ($insertStmt->execute()) {
            // Send welcome email
            @sendWelcomeEmail($email, $firstName);
            
            $response["status"] = "success";
            $response["message"] = "New account created via Google! Welcome email sent. Please log in.";
            $response["redirect"] = "login.php"; 
        } else {
            $response["status"] = "error";
            $response["message"] = "Database Error: " . $conn->error;
        }
    }
} 
// 2. HANDLE MANUAL SIGNUP
else {
    $firstName = $data["firstName"] ?? "";
    $lastName = $data["lastName"] ?? "";
    $email = $data["email"] ?? "";
    $phone = $data["phone"] ?? "";
    $password = $data["password"] ?? "";

    $isTrainer = isset($data["isTrainer"]) ? true : false;
    $trainerSpecialization = $data["trainerSpecialization"] ?? null;
    $trainerExperience = $data["trainerExperience"] ?? null;
    $trainerCertificationPath = null;
    
    // Handle File Upload if Trainer
    if ($isTrainer && isset($_FILES['trainerCertification']) && $_FILES['trainerCertification']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['trainerCertification']['tmp_name'];
        $fileName = $_FILES['trainerCertification']['name'];
        $fileSize = $_FILES['trainerCertification']['size'];
        $fileType = $_FILES['trainerCertification']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'pdf');
        
        if (in_array($fileExtension, $allowedfileExtensions)) {
            $uploadFileDir = './uploads/certifications/';
            // Create dir if not exists (though planner did it, good for robustness)
            if (!is_dir($uploadFileDir)) {
                 mkdir($uploadFileDir, 0777, true);
            }
            // Generate unique name
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $dest_path = $uploadFileDir . $newFileName;
            
            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $trainerCertificationPath = $dest_path; 
            } else {
                 echo json_encode(["status" => "error", "message" => "Error moving uploaded file."]);
                 exit();
            }
        } else {
             echo json_encode(["status" => "error", "message" => "Upload failed. Allowed file types: " . implode(',', $allowedfileExtensions)]);
             exit();
        }
    } elseif ($isTrainer) {
        // If trainer but no file uploaded (and strict requirements demanded)
        echo json_encode(["status" => "error", "message" => "Certification file is required for trainers."]);
        exit();
    }

    if (empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Please provide an email and password."]);
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "An account already exists with this email."]);
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    if ($isTrainer) {
        $role = 'trainer';
        $account_status = 'pending';
        // Insert with trainer details (save path to certification)
        $trainerType = $data["trainerType"] ?? 'online';
        $insertStmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, password_hash, auth_provider, role, account_status, trainer_specialization, trainer_experience, trainer_certification, trainer_type) VALUES (?, ?, ?, ?, ?, 'local', ?, ?, ?, ?, ?, ?)");
        $insertStmt->bind_param("ssssssssiss", $firstName, $lastName, $email, $phone, $hashed_password, $role, $account_status, $trainerSpecialization, $trainerExperience, $trainerCertificationPath, $trainerType);
    } else {
        // Normal user
        $insertStmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, password_hash, auth_provider, role, account_status) VALUES (?, ?, ?, ?, ?, 'local', 'free', 'active')");
        $insertStmt->bind_param("sssss", $firstName, $lastName, $email, $phone, $hashed_password);
    }

    if ($insertStmt->execute()) {
        $response["status"] = "success";
        if ($isTrainer) {
            // Send pending approval email to trainer
            @sendTrainerPendingEmail($email, $firstName);
            $response["message"] = "Application submitted! Your trainer account is pending approval. Check your email for details.";
            $response["redirect"] = "login.php";
        } else {
            // Send welcome email to new user
            @sendWelcomeEmail($email, $firstName);
            $response["message"] = "Account created successfully! Welcome email sent. Please login.";
            $response["redirect"] = "login.php"; 
        }
    } else {
        $response["status"] = "error";
        $response["message"] = "Database execution error: " . $conn->error;
    }
}

$conn->close();
echo json_encode($response);
