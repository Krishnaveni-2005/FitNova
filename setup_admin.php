<?php
/**
 * Admin Setup Script
 * This script sets up Krishnaraj R Nair as the admin user
 * Run this file once to create/update the admin account
 */

require "db_connect.php";

// Admin user details
$adminEmail = "krishnavenirnair2005@gmail.com";
$adminFirstName = "Krishnaraj";
$adminLastName = "R Nair";
$adminPassword = "Admin@2005"; // You can change this password
$adminRole = "admin";

// Check if user already exists
$checkSql = "SELECT * FROM users WHERE email = '$adminEmail'";
$result = $conn->query($checkSql);

if ($result->num_rows > 0) {
    // User exists, update to admin role
    $updateSql = "UPDATE users SET role = '$adminRole' WHERE email = '$adminEmail'";
    
    if ($conn->query($updateSql) === TRUE) {
        echo "<h2 style='color: green;'>✓ Admin user updated successfully!</h2>";
        echo "<p>Email: <strong>$adminEmail</strong></p>";
        echo "<p>Role: <strong>Admin</strong></p>";
        echo "<p>The user has been set as an administrator.</p>";
    } else {
        echo "<h2 style='color: red;'>✗ Error updating user:</h2>";
        echo "<p>" . $conn->error . "</p>";
    }
} else {
    // User doesn't exist, create new admin user
    $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
    
    $insertSql = "INSERT INTO users (first_name, last_name, email, password_hash, role, auth_provider, is_email_verified) 
                  VALUES ('$adminFirstName', '$adminLastName', '$adminEmail', '$hashedPassword', '$adminRole', 'local', 1)";
    
    if ($conn->query($insertSql) === TRUE) {
        echo "<h2 style='color: green;'>✓ Admin user created successfully!</h2>";
        echo "<p>Email: <strong>$adminEmail</strong></p>";
        echo "<p>Password: <strong>$adminPassword</strong></p>";
        echo "<p>Role: <strong>Admin</strong></p>";
        echo "<p style='color: orange;'>⚠️ Please change the password after first login!</p>";
    } else {
        echo "<h2 style='color: red;'>✗ Error creating admin user:</h2>";
        echo "<p>" . $conn->error . "</p>";
    }
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Login with email: <strong>$adminEmail</strong></li>";
echo "<li>You will be redirected to the Admin Dashboard</li>";
echo "<li>Only this user will have access to admin features</li>";
echo "</ol>";
echo "<br>";
echo "<a href='login.php' style='padding: 10px 20px; background: #0F2C59; color: white; text-decoration: none; border-radius: 5px;'>Go to Login Page</a>";

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup - FitNova</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: #f5f5f5;
        }
        h2, h3 {
            color: #0F2C59;
        }
        p {
            line-height: 1.6;
        }
        hr {
            margin: 30px 0;
            border: none;
            border-top: 2px solid #ddd;
        }
    </style>
</head>
<body>
</body>
</html>
