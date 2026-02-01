<?php
require 'db_connect.php';

echo "Payment Records Analysis\n";
echo str_repeat("=", 80) . "\n\n";

// Check if payments table exists
$tables = $conn->query("SHOW TABLES LIKE 'payments'");
if ($tables && $tables->num_rows > 0) {
    echo "✓ 'payments' table exists\n\n";
    
    // Get total payments
    $result = $conn->query("SELECT COUNT(*) as total FROM payments");
    $total = $result->fetch_assoc()['total'];
    echo "Total Payment Records: $total\n\n";
    
    if ($total > 0) {
        // Get payment statistics
        echo "Payment Statistics:\n";
        echo str_repeat("-", 80) . "\n";
        
        // Total revenue
        $result = $conn->query("SELECT SUM(amount) as total_revenue FROM payments WHERE status = 'success'");
        $revenue = $result->fetch_assoc()['total_revenue'] ?? 0;
        echo "Total Revenue (Successful Payments): ₹" . number_format($revenue, 2) . "\n";
        
        // Payment status breakdown
        $result = $conn->query("SELECT status, COUNT(*) as count, SUM(amount) as total FROM payments GROUP BY status");
        echo "\nPayment Status Breakdown:\n";
        while ($row = $result->fetch_assoc()) {
            echo "  - " . ucfirst($row['status']) . ": " . $row['count'] . " payments (₹" . number_format($row['total'], 2) . ")\n";
        }
        
        // Recent payments
        echo "\n" . str_repeat("-", 80) . "\n";
        echo "Recent Payments:\n";
        echo str_repeat("-", 80) . "\n";
        
        $result = $conn->query("SELECT p.*, u.first_name, u.last_name, u.email 
                                FROM payments p 
                                LEFT JOIN users u ON p.user_id = u.user_id 
                                ORDER BY p.created_at DESC 
                                LIMIT 10");
        
        while ($row = $result->fetch_assoc()) {
            echo "Payment ID: " . $row['payment_id'] . "\n";
            echo "  User: " . ($row['first_name'] ?? 'N/A') . " " . ($row['last_name'] ?? '') . " (" . ($row['email'] ?? 'N/A') . ")\n";
            echo "  Amount: ₹" . number_format($row['amount'], 2) . "\n";
            echo "  Plan: " . ($row['plan_type'] ?? 'N/A') . "\n";
            echo "  Status: " . ucfirst($row['status']) . "\n";
            echo "  Razorpay ID: " . ($row['razorpay_payment_id'] ?? 'N/A') . "\n";
            echo "  Date: " . $row['created_at'] . "\n";
            echo str_repeat("-", 80) . "\n";
        }
    }
} else {
    echo "✗ 'payments' table does NOT exist\n";
    echo "Checking for alternative payment tables...\n\n";
    
    // Check all tables
    $result = $conn->query("SHOW TABLES");
    echo "Available tables:\n";
    while ($row = $result->fetch_array()) {
        if (stripos($row[0], 'payment') !== false || stripos($row[0], 'order') !== false || stripos($row[0], 'transaction') !== false) {
            echo "  - " . $row[0] . " (potential payment table)\n";
        }
    }
}

$conn->close();
?>
