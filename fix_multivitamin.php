<?php
require 'db_connect.php';

// Find the Multivitamin Pack product
$result = $conn->query("SELECT product_id, name, image_url FROM products WHERE name LIKE '%Multivitamin%'");

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    echo "Found product:<br>";
    echo "ID: " . $product['product_id'] . "<br>";
    echo "Name: " . $product['name'] . "<br>";
    echo "Current Image: " . ($product['image_url'] ? $product['image_url'] : 'NONE') . "<br><br>";
    
    // Update with a proper multivitamin/supplement bottle image
    $newImageUrl = 'https://images.unsplash.com/photo-1610648391607-7591a1e99b2e?w=500&h=500&fit=crop';
    
    $updateStmt = $conn->prepare("UPDATE products SET image_url = ? WHERE product_id = ?");
    $updateStmt->bind_param("si", $newImageUrl, $product['product_id']);
    
    if ($updateStmt->execute()) {
        echo "<strong>✓ Image updated successfully!</strong><br>";
        echo "New Image URL: <a href='$newImageUrl' target='_blank'>$newImageUrl</a>";
    } else {
        echo "Error updating: " . $conn->error;
    }
    $updateStmt->close();
} else {
    echo "Product not found. Searching for all supplement products...<br><br>";
    $allSupps = $conn->query("SELECT product_id, name, image_url FROM products WHERE category = 'supplements'");
    while($row = $allSupps->fetch_assoc()) {
        echo "ID: {$row['product_id']} | Name: {$row['name']} | Image: " . ($row['image_url'] ? 'Has Image' : 'NO IMAGE') . "<br>";
    }
}

$conn->close();
?>
