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


} elseif ($action === 'update_stock') {
    $product_id = $_POST['product_id'] ?? 0;
    $stock_quantity = $_POST['stock_quantity'] ?? 0;

    if (!$product_id) {
        echo json_encode(["status" => "error", "message" => "Invalid product ID"]);
        exit();
    }

    $stmt = $conn->prepare("UPDATE products SET stock_quantity = ? WHERE product_id = ?");
    $stmt->bind_param("ii", $stock_quantity, $product_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Stock updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
    }
    $stmt->close();

} elseif ($action === 'save') {
    $product_id = $_POST['product_id'] ?? ''; // Empty for new
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? 'men';
    $price = $_POST['price'] ?? 0;
    $description = $_POST['description'] ?? '';
    
    // Default image URL from text input
    $image_url = $_POST['image_url'] ?? ''; 

    // Handle File Upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $uploadDir = 'uploads/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Generate unique name to prevent overwrites
            $newFilename = uniqid('prod_') . '.' . $ext;
            $destPath = $uploadDir . $newFilename;
            
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $destPath)) {
                $image_url = $destPath; // Use local path
            } else {
                 echo json_encode(["status" => "error", "message" => "Failed to move uploaded file"]);
                 exit();
            }
        } else {
             echo json_encode(["status" => "error", "message" => "Invalid file type. Only JPG, PNG, GIF, WEBP allowed"]);
             exit();
        }
    }
    
    // Fallback if image_url is still empty and it's a new product
    if (empty($image_url) && empty($product_id)) {
        $image_url = 'https://via.placeholder.com/300';
    } elseif (empty($image_url) && !empty($product_id)) {
        // If updating and no new image provided, fetch existing to be safe, 
        // OR simply don't update the image column if it's empty?
        // Actually, if the user cleared the text input and didn't upload a file, maybe they want to clear it?
        // But usually in these forms, empty input means "keep existing".
        // Let's check if we should keep existing.
        
        // HOWEVER, the standard way in simple CRUD is usually:
        // If $image_url is empty here, it means the user submitted an empty text field AND didn't upload a file.
        // We probably want to keep the old image in that case.
        // Let's do a fetch to get the old image if we are updating and $image_url is empty.
        
        $stmt_check = $conn->prepare("SELECT image_url FROM products WHERE product_id = ?");
        $stmt_check->bind_param("i", $product_id);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();
        if ($row_check = $res_check->fetch_assoc()) {
            $image_url = $row_check['image_url'];
        }
        $stmt_check->close();
    }
    
    // Basic validation
    if (empty($name) || empty($price)) {
        echo json_encode(["status" => "error", "message" => "Name and Price are required"]);
        exit();
    }

    // Default stock to 50 if not set
    $stock = isset($_POST['stock_quantity']) ? intval($_POST['stock_quantity']) : 50;

    if (!empty($product_id)) {
        // Update
        $stmt = $conn->prepare("UPDATE products SET name=?, category=?, price=?, image_url=?, description=?, stock_quantity=? WHERE product_id=?");
        $stmt->bind_param("ssdssii", $name, $category, $price, $image_url, $description, $stock, $product_id);
    } else {
        // Insert
        $rating = 4.5; 
        $review_count = 0;
        $stmt = $conn->prepare("INSERT INTO products (name, category, price, image_url, rating, review_count, description, stock_quantity) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsdisi", $name, $category, $price, $image_url, $rating, $review_count, $description, $stock);
    }

    if ($stmt->execute()) {
        // Notify Admin of New Product (only on INSERT, i.e., empty product_id)
        if (empty($product_id)) {
            require_once 'admin_notifications.php';
            $currency = '₹'; // Assuming INR based on context, or use generic symbol
            $msg = "New Product Added: $name\nCategory: $category\nPrice: $currency$price";
            
            if (function_exists('sendAdminNotification')) {
                sendAdminNotification($conn, $msg);
            }
        }

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
