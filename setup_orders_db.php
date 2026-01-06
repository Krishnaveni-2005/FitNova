<?php
require 'db_connect.php';

$sql1 = "CREATE TABLE IF NOT EXISTS shop_orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    zip VARCHAR(20) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'card',
    order_status VARCHAR(50) DEFAULT 'Placed',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delivery_date VARCHAR(50)
)";

$sql2 = "CREATE TABLE IF NOT EXISTS shop_order_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255),
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    size VARCHAR(20),
    image_url VARCHAR(255),
    FOREIGN KEY (order_id) REFERENCES shop_orders(order_id) ON DELETE CASCADE
)";

if ($conn->query($sql1) === TRUE) {
    echo "Table shop_orders created successfully.<br>";
} else {
    echo "Error creating table shop_orders: " . $conn->error . "<br>";
}

if ($conn->query($sql2) === TRUE) {
    echo "Table shop_order_items created successfully.<br>";
} else {
    echo "Error creating table shop_order_items: " . $conn->error . "<br>";
}

$conn->close();
?>
