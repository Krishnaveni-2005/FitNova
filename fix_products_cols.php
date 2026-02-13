<?php
require 'db_connect.php';

echo "Adding missing columns to products table...\n";

// Add rating column
$sql = "ALTER TABLE products ADD COLUMN IF NOT EXISTS rating DECIMAL(2,1) DEFAULT 4.5";
if ($conn->query($sql) === TRUE) {
    echo "✔ Added rating column.\n";
} else {
    echo "❌ Error adding rating column: " . $conn->error . "\n";
}

// Add review_count column
$sql = "ALTER TABLE products ADD COLUMN IF NOT EXISTS review_count INT DEFAULT 0";
if ($conn->query($sql) === TRUE) {
    echo "✔ Added review_count column.\n";
} else {
    echo "❌ Error adding review_count column: " . $conn->error . "\n";
}

// Check columns again
$res = $conn->query("SHOW COLUMNS FROM products");
echo "\nUpdated Columns:\n";
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . "\n";
}

$conn->close();
?>
