<?php
require 'db_connect.php';

// 1. Update Plans Table
// Target State: 
// Plan 2 (was Pro 2499) -> Become Lite (2499)
// Plan 3 (was Elite 4999) -> Become Pro (4999)

$conn->query("UPDATE subscription_plans SET name='Lite', description='Starter Premium' WHERE price_monthly = 2499.00");
$conn->query("UPDATE subscription_plans SET name='Pro', description='Ultimate Experience' WHERE price_monthly = 4999.00");

// 2. Update Users Roles
// Old Pro -> Lite
// Old Elite -> Pro

// Use temporary placeholder to avoid collision
$conn->query("UPDATE users SET role='lite_temp' WHERE role='pro'");
$conn->query("UPDATE users SET role='pro' WHERE role='elite'");
$conn->query("UPDATE users SET role='lite' WHERE role='lite_temp'");

echo "Migration Completed: Plans renamed and User roles shifted (Pro->Lite, Elite->Pro).";
?>
