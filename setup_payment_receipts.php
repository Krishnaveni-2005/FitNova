<?php
require 'db_connect.php';

echo "<h2>Setting up Payment Receipts System...</h2>";

// Create or update payments table with all necessary fields
$sql = "CREATE TABLE IF NOT EXISTS payment_receipts (
    receipt_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_name VARCHAR(50) NOT NULL,
    billing_cycle VARCHAR(20) NOT NULL,
    base_amount DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'INR',
    razorpay_payment_id VARCHAR(255),
    razorpay_order_id VARCHAR(255),
    payment_method VARCHAR(50) DEFAULT 'razorpay',
    payment_status VARCHAR(20) DEFAULT 'completed',
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    receipt_number VARCHAR(50) UNIQUE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";

if ($conn->query($sql)) {
    echo "<span style='color:green'>✓ payment_receipts table created/verified.</span><br>";
} else {
    echo "<span style='color:red'>Error: " . $conn->error . "</span><br>";
}

echo "<h3>Payment Receipts System Setup Complete!</h3>";
echo "<p>You can now:</p>";
echo "<ul>";
echo "<li>Store payment transaction details</li>";
echo "<li>Generate receipt numbers automatically</li>";
echo "<li>Display receipts to users after payment</li>";
echo "</ul>";

$conn->close();
?>
