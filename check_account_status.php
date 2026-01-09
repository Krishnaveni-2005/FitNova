<?php
require 'db_connect.php';

echo "<h2>Checking Users Table Structure</h2>";

// Get table structure
$result = $conn->query("DESCRIBE users");

echo "<h3>Columns in 'users' table:</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr style='background: #0F2C59; color: white;'><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";

$hasAccountStatus = false;
while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td><strong>" . $row['Field'] . "</strong></td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
    echo "</tr>";
    
    if($row['Field'] === 'account_status') {
        $hasAccountStatus = true;
    }
}
echo "</table>";

if(!$hasAccountStatus) {
    echo "<h3 style='color: red;'>⚠️ 'account_status' column is MISSING!</h3>";
    echo "<p>Adding it now...</p>";
    
    $alterSql = "ALTER TABLE users ADD COLUMN account_status VARCHAR(20) DEFAULT 'active'";
    if($conn->query($alterSql)) {
        echo "<p style='color: green;'>✅ Successfully added 'account_status' column!</p>";
    } else {
        echo "<p style='color: red;'>❌ Error: " . $conn->error . "</p>";
    }
} else {
    echo "<h3 style='color: green;'>✅ 'account_status' column exists!</h3>";
}

$conn->close();
?>
