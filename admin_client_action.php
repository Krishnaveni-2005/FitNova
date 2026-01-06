<?php
session_start();
header("Content-Type: application/json");
require "db_connect.php";

// Check Admin Auth
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
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
$client_id = $data['client_id'] ?? 0;

if (empty($action) || empty($client_id)) {
    echo json_encode(["status" => "error", "message" => "Missing parameters"]);
    exit();
}

if ($action === 'suspend') {
    $stmt = $conn->prepare("UPDATE users SET account_status = 'inactive' WHERE user_id = ? AND role IN ('free', 'pro', 'elite')");
    $stmt->bind_param("i", $client_id);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Client suspended successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error during suspension."]);
    }
    $stmt->close();
} elseif ($action === 'delete') {
    // Hard delete or verify if you want soft delete
    $stmt = $conn->prepare("UPDATE users SET account_status = 'deleted' WHERE user_id = ? AND role IN ('free', 'pro', 'elite')");
    $stmt->bind_param("i", $client_id);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Client deleted successfully."]);
    } else {
         echo json_encode(["status" => "error", "message" => "Database error during deletion."]);
    }
    $stmt->close();
} elseif ($action === 'activate') {
    // In case we want to reactivate a suspended user
    $stmt = $conn->prepare("UPDATE users SET account_status = 'active' WHERE user_id = ? AND role IN ('free', 'pro', 'elite')");
    $stmt->bind_param("i", $client_id);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Client activated successfully."]);
    } else {
         echo json_encode(["status" => "error", "message" => "Database error during activation."]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid action"]);
    exit();
}

$conn->close();
?>

