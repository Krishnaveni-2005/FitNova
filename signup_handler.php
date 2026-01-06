<?php
session_start();
header("Content-Type: application/json");
require "db_connect.php";

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
        case "elite": return "eliteuser_dashboard.php";
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
            $response["redirect"] = getDashboardRedirect($role);
        } else {
            // SIGNING UP again from signup page
            $response["status"] = "success";
            $response["message"] = "Account already exists with this email. Please log in.";
            $response["redirect"] = "login.php"; 
        }
    } else {
        // NEW USER - SIGNING UP VIA GOOGLE
        $insertStmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, auth_provider, oauth_provider_id, is_email_verified) VALUES (?, ?, ?, 'google', ?, 1)");
        $insertStmt->bind_param("ssss", $firstName, $lastName, $email, $googleId);
        
        if ($insertStmt->execute()) {
            $response["status"] = "success";
            $response["message"] = "New account created via Google! Please log in to your account.";
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
        $insertStmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, password_hash, auth_provider, role, account_status, trainer_specialization, trainer_experience, trainer_certification) VALUES (?, ?, ?, ?, ?, 'local', ?, ?, ?, ?, ?)");
        $insertStmt->bind_param("ssssssssis", $firstName, $lastName, $email, $phone, $hashed_password, $role, $account_status, $trainerSpecialization, $trainerExperience, $trainerCertificationPath);
    } else {
        // Normal user
        $insertStmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, password_hash, auth_provider, role, account_status) VALUES (?, ?, ?, ?, ?, 'local', 'free', 'active')");
        $insertStmt->bind_param("sssss", $firstName, $lastName, $email, $phone, $hashed_password);
    }

    if ($insertStmt->execute()) {
        $response["status"] = "success";
        if ($isTrainer) {
            $response["message"] = "Application submitted! Your trainer account is pending approval.";
            $response["redirect"] = "login.php"; // Or a specific 'pending' page if desired
        } else {
            $response["message"] = "Account created successfully. Please login.";
            $response["redirect"] = "login.php"; 
        }
    } else {
        $response["status"] = "error";
        $response["message"] = "Database execution error: " . $conn->error;
    }
}

$conn->close();
echo json_encode($response);
