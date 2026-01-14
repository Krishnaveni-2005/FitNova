<?php
require 'db_connect.php';

// Delete the "demo" trainers we just added
// We can identify them by email domains @fitnova.com if the user's trainers were different?
// But the user said "remove the trainers listed in the current admin page".
// These ARE the @fitnova.com trainers.

$sql = "DELETE FROM users WHERE email LIKE '%@fitnova.com' AND role = 'trainer'";
if ($conn->query($sql) === TRUE) {
    echo "Removed demo trainers.";
} else {
    echo "Error: " . $conn->error;
}
?>
