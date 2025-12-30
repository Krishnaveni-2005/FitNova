<?php
session_start();
header("Content-Type: application/json");
require "db_connect.php";

$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (!$data) {
    $data = $_POST;
}

$response = array();

// Helper function to get dashboard redirect based on role
function getDashboardRedirect($role) {
    switch ($role) {
        case "admin": return "admin_dashboard.html";
        case "trainer": return "trainer_dashboard.html";
        case "pro": return "prouser_dashboard.html";
        default: return "freeuser_dashboard.php";
    }
}

// 1. HANDLE GOOGLE AUTH (Sign In or Sign Up)
if (isset($data["action"]) && $data["action"] === "google_auth") {
    $email = $conn->real_escape_string($data["email"]);
    $firstName = $conn->real_escape_string($data["firstName"]);
    $lastName = $conn->real_escape_string($data["lastName"]);
    $googleId = $conn->real_escape_string($data["sub"]);
    $emailVerified = $data["emailVerified"];

    if (!$emailVerified) {
        echo json_encode(["status" => "error", "message" => "Email not verified"]);
        exit();
    }

    $sql = "SELECT * FROM users WHERE email = \"$email\"";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // EXISTING USER - LOGGING IN
        $user = $result->fetch_assoc();
        if ($user["auth_provider"] == "local") {
             $update = "UPDATE users SET auth_provider=\"google\", oauth_provider_id=\"$googleId\" WHERE email=\"$email\"";
             $conn->query($update);
        }
        
        $_SESSION["user_id"] = $user["user_id"];
        $_SESSION["user_name"] = $user["first_name"];
        $_SESSION["user_email"] = $user["email"];
        $role = isset($user["role"]) ? $user["role"] : "free";
        $_SESSION["user_role"] = $role;
        
        $response["status"] = "success";
        $response["message"] = "Login successful";
        // If they already have an account, take them to the dashboard
        $response["redirect"] = getDashboardRedirect($role); 
    } else {
        // NEW USER - SIGNING UP VIA GOOGLE
        $insertSql = "INSERT INTO users (first_name, last_name, email, auth_provider, oauth_provider_id, is_email_verified) 
                      VALUES (\"$firstName\", \"$lastName\", \"$email\", \"google\", \"$googleId\", 1)";
        
        if ($conn->query($insertSql) === TRUE) {
            $response["status"] = "success";
            $response["message"] = "Account created successfully";
            // For a NEW signup, redirect to login page as requested
            $response["redirect"] = "login.html"; 
        } else {
            $response["status"] = "error";
            $response["message"] = "Database error: " . $conn->error;
        }
    }
} 
// 2. HANDLE MANUAL SIGNUP
else {
    $firstName = $conn->real_escape_string($data["firstName"] ?? "");
    $lastName = $conn->real_escape_string($data["lastName"] ?? "");
    $email = $conn->real_escape_string($data["email"] ?? "");
    $phone = $conn->real_escape_string($data["phone"] ?? "");
    $password = $data["password"] ?? "";

    if (empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit();
    }

    $checkEmail = "SELECT * FROM users WHERE email = \"$email\"";
    if ($conn->query($checkEmail)->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Email already exists"]);
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (first_name, last_name, email, phone, password_hash, auth_provider) 
            VALUES (\"$firstName\", \"$lastName\", \"$email\", \"phone\", \"$hashed_password\", \"local\")";

    if ($conn->query($sql) === TRUE) {
        $response["status"] = "success";
        $response["message"] = "Account created successfully";
        $response["redirect"] = "login.html"; // Manual signup goes to login page
    } else {
        $response["status"] = "error";
        $response["message"] = "Error: " . $conn->error;
    }
}

$conn->close();
echo json_encode($response);
?>
