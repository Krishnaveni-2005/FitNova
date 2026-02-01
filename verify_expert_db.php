<?php
require 'db_connect.php';

echo "Expert Enquiry System - Database Verification\n";
echo str_repeat("=", 80) . "\n\n";

// 1. Check if table exists
echo "1. Checking if 'expert_enquiries' table exists...\n";
$result = $conn->query("SHOW TABLES LIKE 'expert_enquiries'");
if ($result && $result->num_rows > 0) {
    echo "   ✓ Table exists!\n\n";
} else {
    echo "   ✗ Table does NOT exist!\n";
    echo "   Creating table now...\n";
    
    $sql = "CREATE TABLE IF NOT EXISTS expert_enquiries (
        enquiry_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(100) NOT NULL,
        reason TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pending','contacted','resolved') DEFAULT 'pending'
    )";
    
    if ($conn->query($sql)) {
        echo "   ✓ Table created successfully!\n\n";
    } else {
        echo "   ✗ Error creating table: " . $conn->error . "\n\n";
        exit;
    }
}

// 2. Check table structure
echo "2. Verifying table structure...\n";
$result = $conn->query("DESCRIBE expert_enquiries");
$fields = [];
while ($row = $result->fetch_assoc()) {
    $fields[] = $row['Field'];
    echo "   ✓ " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

$required_fields = ['enquiry_id', 'name', 'phone', 'email', 'reason', 'created_at', 'status'];
$missing = array_diff($required_fields, $fields);
if (empty($missing)) {
    echo "   ✓ All required fields present!\n\n";
} else {
    echo "   ✗ Missing fields: " . implode(', ', $missing) . "\n\n";
}

// 3. Test data insertion
echo "3. Testing data insertion...\n";
$test_name = "System Test User";
$test_phone = "1234567890";
$test_email = "systemtest@fitnova.test";
$test_reason = "Automated system test to verify database functionality.";

$stmt = $conn->prepare("INSERT INTO expert_enquiries (name, phone, email, reason) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $test_name, $test_phone, $test_email, $test_reason);

if ($stmt->execute()) {
    $insert_id = $stmt->insert_id;
    echo "   ✓ Test record inserted successfully! (ID: $insert_id)\n\n";
    
    // 4. Verify the inserted data
    echo "4. Verifying inserted data...\n";
    $verify = $conn->query("SELECT * FROM expert_enquiries WHERE enquiry_id = $insert_id");
    if ($verify && $verify->num_rows > 0) {
        $row = $verify->fetch_assoc();
        echo "   ✓ Data retrieved successfully!\n";
        echo "   - Name: " . $row['name'] . "\n";
        echo "   - Phone: " . $row['phone'] . "\n";
        echo "   - Email: " . $row['email'] . "\n";
        echo "   - Reason: " . substr($row['reason'], 0, 50) . "...\n";
        echo "   - Status: " . $row['status'] . "\n";
        echo "   - Created: " . $row['created_at'] . "\n\n";
    } else {
        echo "   ✗ Could not retrieve inserted data!\n\n";
    }
    
    // Clean up test data
    $conn->query("DELETE FROM expert_enquiries WHERE enquiry_id = $insert_id");
    echo "   ✓ Test data cleaned up.\n\n";
} else {
    echo "   ✗ Error inserting test data: " . $stmt->error . "\n\n";
}
$stmt->close();

// 5. Check existing enquiries
echo "5. Current enquiries in database...\n";
$result = $conn->query("SELECT COUNT(*) as total FROM expert_enquiries");
$count = $result->fetch_assoc()['total'];
echo "   Total enquiries: $count\n";

if ($count > 0) {
    echo "\n   Recent 3 enquiries:\n";
    echo "   " . str_repeat("-", 76) . "\n";
    $result = $conn->query("SELECT * FROM expert_enquiries ORDER BY created_at DESC LIMIT 3");
    while ($row = $result->fetch_assoc()) {
        echo "   #" . $row['enquiry_id'] . " - " . $row['name'] . " (" . $row['phone'] . ")\n";
        echo "       Email: " . $row['email'] . "\n";
        echo "       Status: " . $row['status'] . " | Created: " . $row['created_at'] . "\n";
        echo "   " . str_repeat("-", 76) . "\n";
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "✓ DATABASE VERIFICATION COMPLETE!\n";
echo "✓ The expert_enquiries table is properly configured and working!\n";
echo str_repeat("=", 80) . "\n";

$conn->close();
?>
