<?php
require 'db_connect.php';

echo "Complete Payment System Analysis\n";
echo str_repeat("=", 80) . "\n\n";

// 1. Check payments table structure
echo "1. Payments Table Structure:\n";
echo str_repeat("-", 80) . "\n";
$result = $conn->query("DESCRIBE payments");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "  " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "  Table does not exist\n";
}

// 2. Check for subscription-related payments
echo "\n2. Checking User Subscriptions:\n";
echo str_repeat("-", 80) . "\n";
$result = $conn->query("SELECT role, COUNT(*) as count FROM users WHERE role IN ('lite', 'pro', 'elite') GROUP BY role");
if ($result) {
    echo "User Distribution by Plan:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  " . ucfirst($row['role']) . ": " . $row['count'] . " users\n";
    }
}

// 3. Check all payment-related tables
echo "\n3. All Payment-Related Tables:\n";
echo str_repeat("-", 80) . "\n";
$result = $conn->query("SHOW TABLES");
$paymentTables = [];
while ($row = $result->fetch_array()) {
    $tableName = $row[0];
    if (stripos($tableName, 'payment') !== false || 
        stripos($tableName, 'order') !== false || 
        stripos($tableName, 'transaction') !== false ||
        stripos($tableName, 'subscription') !== false) {
        $paymentTables[] = $tableName;
        
        // Count records in each table
        $countResult = $conn->query("SELECT COUNT(*) as count FROM `$tableName`");
        $count = $countResult->fetch_assoc()['count'];
        echo "  ✓ $tableName: $count records\n";
    }
}

// 4. Check shop orders (if they exist)
echo "\n4. Shop Orders:\n";
echo str_repeat("-", 80) . "\n";
$result = $conn->query("SHOW TABLES LIKE 'shop_orders'");
if ($result && $result->num_rows > 0) {
    $orderResult = $conn->query("SELECT COUNT(*) as total, SUM(total_amount) as revenue FROM shop_orders");
    $orderData = $orderResult->fetch_assoc();
    echo "  Total Orders: " . $orderData['total'] . "\n";
    echo "  Total Revenue: ₹" . number_format($orderData['revenue'] ?? 0, 2) . "\n";
    
    // Recent orders
    $recent = $conn->query("SELECT * FROM shop_orders ORDER BY order_date DESC LIMIT 5");
    if ($recent && $recent->num_rows > 0) {
        echo "\n  Recent Orders:\n";
        while ($order = $recent->fetch_assoc()) {
            echo "    Order #" . $order['order_id'] . " - ₹" . number_format($order['total_amount'], 2) . " (" . $order['order_status'] . ")\n";
        }
    }
} else {
    echo "  No shop_orders table found\n";
}

// 5. Check payment_handler.php for clues
echo "\n5. Checking Payment Handler Configuration:\n";
echo str_repeat("-", 80) . "\n";
if (file_exists('payment_handler.php')) {
    echo "  ✓ payment_handler.php exists\n";
    $content = file_get_contents('payment_handler.php');
    if (stripos($content, 'razorpay') !== false) {
        echo "  ✓ Razorpay integration found\n";
    }
    if (preg_match('/INSERT INTO\s+(\w+)/i', $content, $matches)) {
        echo "  ✓ Inserts data into table: " . $matches[1] . "\n";
    }
} else {
    echo "  ✗ payment_handler.php not found\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "Analysis Complete!\n";

$conn->close();
?>
