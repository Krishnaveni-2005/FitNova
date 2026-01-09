<?php
require 'db_connect.php';

echo "<h2>Checking Users Table Structure</h2>";

// Get table structure
$result = $conn->query("DESCRIBE users");

echo "<h3>Current Columns in 'users' table:</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr style='background: #0F2C59; color: white;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";

$columns = [];
while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td><strong>" . $row['Field'] . "</strong></td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
    echo "</tr>";
    $columns[] = $row['Field'];
}
echo "</table>";

// Check if required columns exist
$requiredColumns = ['certifications', 'experience_years', 'bio', 'specialization', 'profile_picture'];
$missingColumns = [];

echo "<h3>Checking Required Columns:</h3>";
echo "<ul>";
foreach($requiredColumns as $col) {
    if(in_array($col, $columns)) {
        echo "<li style='color: green;'>✅ <strong>$col</strong> - EXISTS</li>";
    } else {
        echo "<li style='color: red;'>❌ <strong>$col</strong> - MISSING</li>";
        $missingColumns[] = $col;
    }
}
echo "</ul>";

if(!empty($missingColumns)) {
    echo "<h3 style='color: red;'>⚠️ Missing Columns Detected!</h3>";
    echo "<p>The following columns need to be added to the users table:</p>";
    echo "<ul>";
    foreach($missingColumns as $col) {
        echo "<li><code>$col</code></li>";
    }
    echo "</ul>";
    
    echo "<p><a href='fix_users_table.php' style='background: #dc3545; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;'>Click Here to Fix Table Structure</a></p>";
} else {
    echo "<h3 style='color: green;'>✅ All Required Columns Exist!</h3>";
    echo "<p><a href='add_all_trainers.php' style='background: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;'>Proceed to Add Trainers</a></p>";
}

$conn->close();
?>
