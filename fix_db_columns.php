<?php
// fix_db_columns.php
// Checks and repairs the users table schema.

require 'db_connect.php';

echo "<h2>Database Repair Tool</h2>";

$columnsToAdd = [
    'role' => "ENUM('free', 'pro', 'trainer', 'admin') DEFAULT 'free'",
    'auth_provider' => "ENUM('local', 'google', 'facebook') DEFAULT 'local'",
    'oauth_provider_id' => "VARCHAR(255)",
    'is_email_verified' => "BOOLEAN DEFAULT FALSE",
    'account_status' => "ENUM('active', 'inactive', 'suspended', 'pending') DEFAULT 'active'",
    'trainer_specialization' => "VARCHAR(100)",
    'trainer_experience' => "INT",
    'trainer_certification' => "VARCHAR(255)",
    'phone' => "VARCHAR(20)",
    'password_hash' => "VARCHAR(255) NULL" // Nullable for social login users
];

// Check current columns
$result = $conn->query("SHOW COLUMNS FROM users");
$existingCols = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $existingCols[] = $row['Field'];
    }
} else {
    die("Error reading users table: " . $conn->error);
}

foreach ($columnsToAdd as $col => $def) {
    if (!in_array($col, $existingCols)) {
        $sql = "ALTER TABLE users ADD COLUMN $col $def";
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color:green'>✓ Added column: <strong>$col</strong></p>";
        } else {
            echo "<p style='color:red'>✗ Error adding $col: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:gray'>• Column <strong>$col</strong> already exists.</p>";
    }
}

// Fix password_hash being nullable if it isn't
$res = $conn->query("SHOW COLUMNS FROM users LIKE 'password_hash'");
$row = $res->fetch_assoc();
if ($row && $row['Null'] === 'NO') {
    $conn->query("ALTER TABLE users MODIFY password_hash VARCHAR(255) NULL");
    echo "<p style='color:green'>✓ Updated password_hash to allow NULL (for Google Login).</p>";
}

echo "<hr><p><strong>Repair Complete.</strong> You can now <a href='signup.php'>Return to Signup</a>.</p>";
?>
