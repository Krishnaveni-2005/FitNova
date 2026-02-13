<?php
require 'db_connect.php';

echo "<h2>Adding Subscription Expiry Tracking...</h2>";

// Add subscription_start and subscription_end columns to payment_receipts table
$alterSql = "ALTER TABLE payment_receipts 
    ADD COLUMN IF NOT EXISTS subscription_start DATE DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS subscription_end DATE DEFAULT NULL";

if ($conn->query($alterSql)) {
    echo "<span style='color:green'>✓ Subscription tracking columns added successfully.</span><br>";
} else {
    echo "<span style='color:red'>Error: " . $conn->error . "</span><br>";
}

// Add a user_subscriptions table to track current active subscription
$createSubSql = "CREATE TABLE IF NOT EXISTS user_subscriptions (
    subscription_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    current_plan VARCHAR(50) NOT NULL,
    billing_cycle VARCHAR(20) NOT NULL,
    subscription_start DATE NOT NULL,
    subscription_end DATE NOT NULL,
    can_switch_after DATE NOT NULL,
    last_payment_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (last_payment_id) REFERENCES payment_receipts(receipt_id) ON DELETE SET NULL
)";

if ($conn->query($createSubSql)) {
    echo "<span style='color:green'>✓ user_subscriptions table created/verified.</span><br>";
} else {
    echo "<span style='color:red'>Error: " . $conn->error . "</span><br>";
}

echo "<h3>Subscription Expiry Tracking Setup Complete!</h3>";
echo "<p>System now tracks:</p>";
echo "<ul>";
echo "<li>Subscription start and end dates</li>";
echo "<li>Earliest date users can switch plans</li>";
echo "<li>Active subscription status per user</li>";
echo "</ul>";

$conn->close();
?>
