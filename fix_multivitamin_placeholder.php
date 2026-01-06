<?php
require 'db_connect.php';

// Find the Multivitamin Pack product and use a placeholder/generic image
$result = $conn->query("SELECT product_id, name, image_url FROM products WHERE name LIKE '%Multivitamin%'");

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    echo "Found product:<br>";
    echo "ID: " . $product['product_id'] . "<br>";
    echo "Name: " . $product['name'] . "<br>";
    echo "Current Image: " . ($product['image_url'] ? $product['image_url'] : 'NONE') . "<br><br>";
    
    // Use a reliable placeholder service with supplement/pills theme
    $newImageUrl = 'https://via.placeholder.com/500x500/FF9800/FFFFFF?text=Multivitamin+Pack';
    
    $updateStmt = $conn->prepare("UPDATE products SET image_url = ? WHERE product_id = ?");
    $updateStmt->bind_param("si", $newImageUrl, $product['product_id']);
    
    if ($updateStmt->execute()) {
        echo "<strong>✓ Image updated with placeholder!</strong><br>";
        echo "New Image URL: <a href='$newImageUrl' target='_blank'>$newImageUrl</a><br>";
        echo "<img src='$newImageUrl' style='width:200px; margin-top:20px; border: 1px solid #ddd;'>";
    } else {
        echo "Error updating: " . $conn->error;
    }
    $updateStmt->close();
} else {
    echo "Product not found.";
}

$conn->close();
?>
