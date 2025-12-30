<?php
session_start();
header("Content-Type: application/json");
require "db_connect.php";

$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (!$data) {
    $data = $_POST;
}

$email = $conn->real_escape_string($data["email"] ?? "");
$password = $data["password"] ?? "";

if (empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Please fill in all fields"]);
    exit();
}

$sql = "SELECT * FROM users WHERE email = \"$email\"";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user["password_hash"])) {
        $_SESSION["user_id"] = $user["user_id"];
        $_SESSION["user_name"] = $user["first_name"];
        $_SESSION["user_email"] = $user["email"];
        
        // Gracefully handle missing role column
        $role = isset($user["role"]) ? $user["role"] : "free";
        $_SESSION["user_role"] = $role;
        
        $redirect = "freeuser_dashboard.php"; 
        
        switch ($role) {
            case "admin":
                $redirect = "admin_dashboard.html";
                break;
            case "trainer":
                $redirect = "trainer_dashboard.html";
                break;
            case "pro":
                $redirect = "prouser_dashboard.html";
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
