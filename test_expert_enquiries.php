<?php
require 'db_connect.php';

// Check if table exists and show sample data
echo "Expert Enquiries Database Test\n";
echo str_repeat("=", 80) . "\n\n";

// Check table structure
$result = $conn->query("DESCRIBE expert_enquiries");
if ($result) {
    echo "✓ Table 'expert_enquiries' exists!\n\n";
    echo "Table Structure:\n";
    echo str_repeat("-", 80) . "\n";
    while($row = $result->fetch_assoc()) {
        echo sprintf("%-20s %-30s\n", $row['Field'], $row['Type']);
    }
    echo "\n";
}

// Check for any existing enquiries
$result = $conn->query("SELECT COUNT(*) as total FROM expert_enquiries");
$count = $result->fetch_assoc()['total'];
echo "Total Enquiries: " . $count . "\n\n";

if ($count > 0) {
    echo "Recent Enquiries:\n";
    echo str_repeat("-", 80) . "\n";
    $result = $conn->query("SELECT * FROM expert_enquiries ORDER BY created_at DESC LIMIT 5");
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row['enquiry_id'] . "\n";
        echo "Name: " . $row['name'] . "\n";
        echo "Phone: " . $row['phone'] . "\n";
        echo "Email: " . $row['email'] . "\n";
        echo "Reason: " . substr($row['reason'], 0, 50) . "...\n";
        echo "Status: " . $row['status'] . "\n";
        echo "Created: " . $row['created_at'] . "\n";
        echo str_repeat("-", 80) . "\n";
    }
}

$conn->close();
?>
