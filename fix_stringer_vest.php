<?php
require 'db_connect.php';

// Find the Bodybuilding Stringer Vest product
$result = $conn->query("SELECT product_id, name, image_url FROM products WHERE name LIKE '%Stringer Vest%'");

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    echo "Found product:<br>";
    echo "ID: " . $product['product_id'] . "<br>";
    echo "Name: " . $product['name'] . "<br>";
    echo "Current Image: " . ($product['image_url'] ? $product['image_url'] : 'NONE') . "<br><br>";
    
    // Update with a proper stringer vest image
    $newImageUrl = 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=500&h=500&fit=crop';
    
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
    echo "Product not found. Searching for all vest products...<br><br>";
    $allVests = $conn->query("SELECT product_id, name, image_url FROM products WHERE name LIKE '%Vest%'");
    while($row = $allVests->fetch_assoc()) {
        echo "ID: {$row['product_id']} | Name: {$row['name']} | Image: " . ($row['image_url'] ? 'Has Image' : 'NO IMAGE') . "<br>";
    }
}

$conn->close();
?>
