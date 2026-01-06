<?php
require 'db_connect.php';

// Check what's actually stored in the database
$result = $conn->query("SELECT product_id, name, image_url FROM products WHERE name LIKE '%Multivitamin%'");

echo "<h2>Current Database State</h2>";
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<strong>Product ID:</strong> " . $row['product_id'] . "<br>";
        echo "<strong>Name:</strong> " . $row['name'] . "<br>";
        echo "<strong>Image URL in DB:</strong> " . htmlspecialchars($row['image_url']) . "<br>";
        echo "<hr>";
        
        // Try to display the image
        echo "<h3>Image Test:</h3>";
        echo "<img src='" . htmlspecialchars($row['image_url']) . "' style='max-width:300px; border:2px solid red;' onerror=\"this.style.border='5px solid red'; this.alt='IMAGE FAILED TO LOAD';\">";
        echo "<br><br>";
        
        // Also show what the HTML looks like
        echo "<h3>Raw HTML:</h3>";
        echo "<pre>" . htmlspecialchars('<img src="' . $row['image_url'] . '" alt="Multivitamin Pack">') . "</pre>";
    }
} else {
    echo "No product found!";
}

$conn->close();
?>
