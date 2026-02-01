<?php
require 'db_connect.php';

echo "Revenue Calculation Based on User Subscriptions\n";
echo str_repeat("=", 80) . "\n\n";

// Define plan prices (you can adjust these based on your actual pricing)
$planPrices = [
    'lite' => 499,    // Monthly price for Lite
    'pro' => 999,     // Monthly price for Pro  
    'elite' => 1999   // Monthly price for Elite
];

// Get users by plan
$result = $conn->query("SELECT role, first_name, last_name, email, created_at 
                        FROM users 
                        WHERE role IN ('lite', 'pro', 'elite') 
                        ORDER BY role, created_at DESC");

$revenue = [
    'lite' => ['count' => 0, 'total' => 0],
    'pro' => ['count' => 0, 'total' => 0],
    'elite' => ['count' => 0, 'total' => 0]
];

$users = [];

while ($row = $result->fetch_assoc()) {
    $plan = $row['role'];
    $revenue[$plan]['count']++;
    $revenue[$plan]['total'] += $planPrices[$plan];
    $users[] = $row;
}

// Calculate total revenue
$totalRevenue = $revenue['lite']['total'] + $revenue['pro']['total'] + $revenue['elite']['total'];
$totalUsers = $revenue['lite']['count'] + $revenue['pro']['count'] + $revenue['elite']['count'];

echo "Subscription Summary:\n";
echo str_repeat("-", 80) . "\n";
echo sprintf("%-15s %-10s %-15s %-15s\n", "Plan", "Users", "Price/User", "Total Revenue");
echo str_repeat("-", 80) . "\n";

foreach (['lite', 'pro', 'elite'] as $plan) {
    echo sprintf("%-15s %-10d ₹%-14s ₹%-14s\n", 
        ucfirst($plan), 
        $revenue[$plan]['count'],
        number_format($planPrices[$plan], 2),
        number_format($revenue[$plan]['total'], 2)
    );
}

echo str_repeat("-", 80) . "\n";
echo sprintf("%-15s %-10d %-15s ₹%-14s\n", 
    "TOTAL", 
    $totalUsers,
    "",
    number_format($totalRevenue, 2)
);
echo str_repeat("=", 80) . "\n\n";

// Show individual subscribers
if (count($users) > 0) {
    echo "Paid Subscribers:\n";
    echo str_repeat("-", 80) . "\n";
    
    foreach ($users as $user) {
        $plan = ucfirst($user['role']);
        $price = $planPrices[$user['role']];
        echo "• " . $user['first_name'] . " " . $user['last_name'] . "\n";
        echo "  Email: " . $user['email'] . "\n";
        echo "  Plan: $plan (₹" . number_format($price, 2) . ")\n";
        echo "  Subscribed: " . date('M d, Y', strtotime($user['created_at'])) . "\n";
        echo str_repeat("-", 80) . "\n";
    }
}

echo "\n💰 ESTIMATED MONTHLY REVENUE: ₹" . number_format($totalRevenue, 2) . "\n";
echo "📊 TOTAL PAID SUBSCRIBERS: $totalUsers\n";

$conn->close();
?>
