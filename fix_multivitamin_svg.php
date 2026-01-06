<?php
require 'db_connect.php';

// Use a data URI with a simple SVG placeholder
$svgImage = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="500" height="500"%3E%3Crect fill="%23FF9800" width="500" height="500"/%3E%3Ctext x="50%25" y="50%25" font-family="Arial" font-size="40" fill="white" text-anchor="middle" dy=".3em"%3EMultivitamin%3C/text%3E%3C/svg%3E';

$result = $conn->query("SELECT product_id FROM products WHERE name LIKE '%Multivitamin%'");

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    
    $updateStmt = $conn->prepare("UPDATE products SET image_url = ? WHERE product_id = ?");
    $updateStmt->bind_param("si", $svgImage, $product['product_id']);
    
    if ($updateStmt->execute()) {
        echo "✓ Updated with embedded SVG image<br>";
        echo "This will work 100% of the time!<br><br>";
        echo "<img src='$svgImage' style='width:200px;'>";
    }
    $updateStmt->close();
}

$conn->close();
?>
