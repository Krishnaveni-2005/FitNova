<?php
// update_product_images.php
// Updates specific products to use the newly generated images.

require 'db_connect.php';

// Map product names to their new image filenames
$updates = [
    'Bodybuilding Stringer Vest' => 'stringer_vest.png',
    'Performance Hoodie' => 'performance_hoodie.png',
    'Gym Joggers' => 'gym_joggers.png'
];

echo "<h3>Updating Product Images...</h3>";

foreach ($updates as $productName => $imageName) {
    // 1. Prepare Statement
    $sql = "UPDATE products SET image_url = ? WHERE name = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ss", $imageName, $productName);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            echo "<p style='color:green'>✓ Updated image for: <strong>$productName</strong> to <em>$imageName</em></p>";
        } elseif ($stmt->affected_rows == 0) {
            echo "<p style='color:orange'>• No changes for: <strong>$productName</strong> (maybe name mismatch or already updated)</p>";
        } else {
             echo "<p style='color:red'>✗ Error updating: $productName</p>";
        }
        $stmt->close();
    } else {
        echo "<p style='color:red'>✗ Prepare failed: " . $conn->error . "</p>";
    }
}

echo "<hr><a href='fitshop.php' style='padding:10px 20px; background:#0F2C59; color:white; text-decoration:none; border-radius:5px;'>Return to FitShop</a>";

$conn->close();
?>
