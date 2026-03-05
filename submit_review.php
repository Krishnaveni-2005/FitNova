<?php
session_start();
header("Content-Type: application/json");
require "db_connect.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "You must be logged in to review."]);
    exit();
}

$userId = $_SESSION['user_id'];
$productId = isset($data['product_id']) ? intval($data['product_id']) : 0;
$rating = isset($data['rating']) ? intval($data['rating']) : 0;
$reviewText = isset($data['review_text']) ? trim($data['review_text']) : "";

if ($productId <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode(["success" => false, "message" => "Invalid product or rating."]);
    exit();
}

// Check if user already reviewed this product
$check = $conn->prepare("SELECT review_id FROM product_reviews WHERE product_id = ? AND user_id = ?");
$check->bind_param("ii", $productId, $userId);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    // Update existing review
    $sql = "UPDATE product_reviews SET rating = ?, review_text = ?, updated_at = NOW() WHERE product_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isii", $rating, $reviewText, $productId, $userId);
} else {
    // Insert new review
    $sql = "INSERT INTO product_reviews (product_id, user_id, rating, review_text, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $productId, $userId, $rating, $reviewText);
}

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Review submitted successfully!"]);
} else {
    echo json_encode(["success" => false, "message" => "Database error: " . $stmt->error]);
}

$stmt->close();
$check->close();
$conn->close();
?>
