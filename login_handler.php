<?php
session_start();
header("Content-Type: application/json");
require "db_connect.php";

$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (!$data) {
    $data = $_POST;
}

$email = $data["email"] ?? "";
$password = $data["password"] ?? "";

if (empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Please fill in all fields"]);
    exit();
}

// Prepared Statement for Login
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user["password_hash"])) {
        $_SESSION["user_id"] = $user["user_id"];
        $_SESSION["user_name"] = $user["first_name"];
        $_SESSION["user_email"] = $user["email"];
        
        $role = $user["role"] ?? "free";
        $_SESSION["user_role"] = $role;
        
        $redirect = "freeuser_dashboard.php"; 
        
        switch ($role) {
            case "admin":
                $redirect = "admin_dashboard.php";
                break;
            case "trainer":
                if (isset($user['account_status']) && $user['account_status'] === 'pending') {
                    echo json_encode(["status" => "error", "message" => "Your trainer account is pending approval."]);
                    exit();
                }
                if (isset($user['account_status']) && $user['account_status'] === 'inactive') {
                    echo json_encode(["status" => "error", "message" => "Your account is inactive."]);
                    exit();
                }
                $redirect = "trainer_dashboard.php";
                break;
            case "pro":
                $redirect = "prouser_dashboard.php";
                break;
            case "elite":
                $redirect = "eliteuser_dashboard.php";
                break;
            default:
                $redirect = "freeuser_dashboard.php";
                break;
        }
        
        echo json_encode([
            "status" => "success",
            "redirect" => $redirect
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid password"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "User not found"]);
}

$conn->close();
?>
