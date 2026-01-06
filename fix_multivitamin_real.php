<?php
require 'db_connect.php';

// Find the Multivitamin Pack product and use a more professional image
$result = $conn->query("SELECT product_id, name, image_url FROM products WHERE name LIKE '%Multivitamin%'");

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    echo "Found product:<br>";
    echo "ID: " . $product['product_id'] . "<br>";
    echo "Name: " . $product['name'] . "<br>";
    echo "Current Image: " . ($product['image_url'] ? $product['image_url'] : 'NONE') . "<br><br>";
    
    // Try using a different reliable image source - Pixabay CDN
    $newImageUrl = 'https://cdn.pixabay.com/photo/2017/08/10/03/47/pills-2617576_960_720.jpg';
    
    $updateStmt = $conn->prepare("UPDATE products SET image_url = ? WHERE product_id = ?");
    $updateStmt->bind_param("si", $newImageUrl, $product['product_id']);
    
    if ($updateStmt->execute()) {
        echo "<strong>✓ Image updated with actual product photo!</strong><br>";
        echo "New Image URL: <a href='$newImageUrl' target='_blank'>$newImageUrl</a><br>";
        echo "<br>Testing image load:<br>";
        echo "<img src='$newImageUrl' style='max-width:300px; margin-top:20px; border: 2px solid #ddd; border-radius: 8px;'>";
    } else {
        echo "Error updating: " . $conn->error;
    }
    $updateStmt->close();
} else {
    echo "Product not found.";
}

$conn->close();
?>
