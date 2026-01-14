<?php
require 'db_connect.php';

echo "<div style='font-family:sans-serif; padding:20px;'>";
echo "<h2>Resetting Trainer Passwords...</h2>";

// Fetch all trainers
$sql = "SELECT user_id, first_name, last_name, email FROM users WHERE role = 'trainer'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $firstName = trim($row['first_name']);
        
        // Get the first word of the first name
        $nameParts = explode(' ', $firstName);
        $firstWord = $nameParts[0];
        
        // Construct the password: [FirstWord]@2005
        $newPasswordPlain = $firstWord . "@2005";
        
        // Hash it
        $hashedPassword = password_hash($newPasswordPlain, PASSWORD_DEFAULT);
        
        // Update the database
        $updateSql = "UPDATE users SET password_hash = ? WHERE user_id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("si", $hashedPassword, $row['user_id']);
        
        if ($stmt->execute()) {
            echo "<p style='color:green;'><strong>" . htmlspecialchars($row['first_name'] . " " . $row['last_name']) . "</strong><br>";
            echo "Email: " . htmlspecialchars($row['email']) . "<br>";
            echo "New Password: <strong>" . htmlspecialchars($newPasswordPlain) . "</strong></p><hr>";
        } else {
            echo "<p style='color:red;'>Failed to update " . htmlspecialchars($row['first_name']) . ": " . $conn->error . "</p>";
        }
        $stmt->close();
    }
} else {
    echo "<p>No trainers found.</p>";
}

echo "<h3>Done. All trainer passwords have been updated.</h3>";
echo "</div>";

$conn->close();
?>
