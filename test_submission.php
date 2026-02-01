<?php
// Test the expert enquiry submission handler
echo "Testing Expert Enquiry Submission Handler\n";
echo str_repeat("=", 80) . "\n\n";

// Simulate POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['name'] = 'Test User';
$_POST['phone'] = '9876543210';
$_POST['email'] = 'testuser@example.com';
$_POST['reason'] = 'Testing the expert enquiry system to ensure data is stored properly.';

// Capture output
ob_start();
include 'submit_expert_enquiry.php';
$response = ob_get_clean();

echo "Response from submit_expert_enquiry.php:\n";
echo str_repeat("-", 80) . "\n";
echo $response . "\n";
echo str_repeat("-", 80) . "\n\n";

// Decode JSON response
$data = json_decode($response, true);

if ($data && isset($data['success'])) {
    if ($data['success']) {
        echo "✓ SUCCESS: Form submission handler is working correctly!\n";
        echo "✓ Message: " . $data['message'] . "\n\n";
        
        // Verify the data was actually inserted
        require 'db_connect.php';
        $result = $conn->query("SELECT * FROM expert_enquiries WHERE email = 'testuser@example.com' ORDER BY created_at DESC LIMIT 1");
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo "✓ VERIFIED: Data was successfully inserted into database!\n\n";
            echo "Inserted Record:\n";
            echo "  - ID: " . $row['enquiry_id'] . "\n";
            echo "  - Name: " . $row['name'] . "\n";
            echo "  - Phone: " . $row['phone'] . "\n";
            echo "  - Email: " . $row['email'] . "\n";
            echo "  - Reason: " . substr($row['reason'], 0, 50) . "...\n";
            echo "  - Status: " . $row['status'] . "\n";
            echo "  - Created: " . $row['created_at'] . "\n\n";
            
            // Clean up test data
            $conn->query("DELETE FROM expert_enquiries WHERE email = 'testuser@example.com'");
            echo "✓ Test data cleaned up.\n";
        } else {
            echo "✗ WARNING: Data was not found in database!\n";
        }
        $conn->close();
    } else {
        echo "✗ FAILED: " . $data['message'] . "\n";
    }
} else {
    echo "✗ ERROR: Invalid response format\n";
    echo "Raw response: " . $response . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "Test Complete!\n";
?>
