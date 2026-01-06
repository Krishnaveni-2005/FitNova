<?php
require 'db_connect.php';

// Find the Multivitamin Pack product and try different images
$result = $conn->query("SELECT product_id, name, image_url FROM products WHERE name LIKE '%Multivitamin%'");

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    echo "Found product:<br>";
    echo "ID: " . $product['product_id'] . "<br>";
    echo "Name: " . $product['name'] . "<br>";
    echo "Current Image: " . ($product['image_url'] ? $product['image_url'] : 'NONE') . "<br><br>";
    
    // Try a different, more reliable image for vitamins/supplements
    $newImageUrl = 'https://images.unsplash.com/photo-1550572017-4691e90e4239?w=500&h=500&fit=crop&q=80';
    
    $updateStmt = $conn->prepare("UPDATE products SET image_url = ? WHERE product_id = ?");
    $updateStmt->bind_param("si", $newImageUrl, $product['product_id']);
    
    if ($updateStmt->execute()) {
        echo "<strong>✓ Image updated with alternative URL!</strong><br>";
        echo "New Image URL: <a href='$newImageUrl' target='_blank'>$newImageUrl</a><br>";
        echo "<img src='$newImageUrl' style='width:200px; margin-top:20px;'>";
    } else {
        echo "Error updating: " . $conn->error;
    }
    $updateStmt->close();
} else {
    echo "Product not found.";
}

$conn->close();
?>
