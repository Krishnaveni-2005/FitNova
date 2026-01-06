<?php
session_start();
header("Content-Type: application/json");
require "db_connect.php";

// Check Admin Auth
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'delete') {
    $product_id = $_POST['product_id'] ?? 0;
    if (!$product_id) {
        echo json_encode(["status" => "error", "message" => "Invalid product ID"]);
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Product deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
    }
    $stmt->close();

} elseif ($action === 'save') {
    $product_id = $_POST['product_id'] ?? ''; // Empty for new
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? 'men';
    $price = $_POST['price'] ?? 0;
    $image_url = $_POST['image_url'] ?? 'https://via.placeholder.com/300';
    
    // Basic validation
    if (empty($name) || empty($price)) {
        echo json_encode(["status" => "error", "message" => "Name and Price are required"]);
        exit();
    }

    if (!empty($product_id)) {
        // Update
        $stmt = $conn->prepare("UPDATE products SET name=?, category=?, price=?, image_url=? WHERE product_id=?");
        $stmt->bind_param("ssdsi", $name, $category, $price, $image_url, $product_id);
    } else {
        // Insert
        // Defaulting ratings for new products to 0 or 4.5
        $rating = 4.5; 
        $review_count = 0;
        $stmt = $conn->prepare("INSERT INTO products (name, category, price, image_url, rating, review_count) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsdi", $name, $category, $price, $image_url, $rating, $review_count);
    }

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Product saved successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid action"]);
}

$conn->close();
?>
