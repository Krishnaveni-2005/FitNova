<?php
session_start();
require 'db_connect.php';

// Check admin permission
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("Unauthorized Access");
}

// Set headers to download file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=fitnova_users_data.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Output column headings
fputcsv($output, array('User ID', 'First Name', 'Last Name', 'Email', 'Role', 'Account Status', 'Phone', 'Created At'));

// Fetch users
$query = "SELECT user_id, first_name, last_name, email, role, account_status, phone, created_at FROM users ORDER BY created_at DESC";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
}

fclose($output);
exit();
?>
