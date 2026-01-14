<?php
require 'db_connect.php';

// Approve all pending trainer assignments
$sql = "UPDATE users SET assignment_status = 'approved' WHERE assignment_status = 'pending'";

if ($conn->query($sql) === TRUE) {
    if ($conn->affected_rows > 0) {
        echo "<div style='font-family:sans-serif; padding:50px; text-align:center; color:green;'>";
        echo "<h1>Success!</h1>";
        echo "<p>Approved " . $conn->affected_rows . " pending trainer request(s).</p>";
        echo "<p>The 'Trainer Request Pending' banner should now be gone.</p>";
        echo "<a href='prouser_dashboard.php' style='background:#0F2C59; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Go to Dashboard</a>";
        echo "</div>";
    } else {
        echo "<div style='font-family:sans-serif; padding:50px; text-align:center; color:orange;'>";
        echo "<h1>No Pending Requests Found</h1>";
        echo "<p>There were no pending requests to approve.</p>";
        echo "</div>";
    }
} else {
    echo "Error updating record: " . $conn->error;
}

$conn->close();
?>
