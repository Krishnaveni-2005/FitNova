<!DOCTYPE html>
<html>
<head>
    <title>Database Users - FitNova</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        h2 { color: #0F2C59; border-bottom: 3px solid #4FACFE; padding-bottom: 10px; }
        h3 { color: #333; margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; background: white; margin: 20px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        th { background: #0F2C59; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f9f9f9; }
        .summary { background: white; padding: 20px; border-radius: 10px; margin: 20px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .summary li { margin: 10px 0; font-size: 16px; }
        .count { color: #4FACFE; font-weight: bold; font-size: 20px; }
    </style>
</head>
<body>
    <h2>📊 FitNova Database - All Users</h2>
    
<?php
require 'db_connect.php';

// Get all trainers
echo "<h3>👨‍🏫 TRAINERS</h3>";
$trainersQuery = "SELECT * FROM users WHERE role = 'trainer' ORDER BY id";
$trainersResult = $conn->query($trainersQuery);

if ($trainersResult && $trainersResult->num_rows > 0) {
    echo "<p>Total: <span class='count'>" . $trainersResult->num_rows . "</span></p>";
    echo "<table>";
    echo "<tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Specialization</th>
            <th>Status</th>
            <th>Joined</th>
          </tr>";
    
    while($row = $trainersResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td><strong>#" . $row['id'] . "</strong></td>";
        echo "<td><strong>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['specialization'] ?? 'Not set') . "</td>";
        echo "<td>" . htmlspecialchars($row['account_status'] ?? 'active') . "</td>";
        echo "<td>" . date('M d, Y', strtotime($row['created_at'] ?? 'now')) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ No trainers found in database.</p>";
}

// Get all clients
echo "<h3>👥 CLIENTS</h3>";
$clientsQuery = "SELECT * FROM users WHERE role IN ('client', 'paid_client', 'free_client') ORDER BY id";
$clientsResult = $conn->query($clientsQuery);

if ($clientsResult && $clientsResult->num_rows > 0) {
    echo "<p>Total: <span class='count'>" . $clientsResult->num_rows . "</span></p>";
    echo "<table>";
    echo "<tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Type</th>
            <th>Status</th>
            <th>Joined</th>
          </tr>";
    
    while($row = $clientsResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td><strong>#" . $row['id'] . "</strong></td>";
        echo "<td><strong>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . ucwords(str_replace('_', ' ', $row['role'])) . "</td>";
        echo "<td>" . htmlspecialchars($row['account_status'] ?? 'active') . "</td>";
        echo "<td>" . date('M d, Y', strtotime($row['created_at'] ?? 'now')) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ No clients found in database.</p>";
}

// Summary
echo "<div class='summary'>";
echo "<h3>📈 SUMMARY</h3>";

$totalQuery = "SELECT COUNT(*) as total FROM users";
$totalResult = $conn->query($totalQuery);
$total = $totalResult->fetch_assoc()['total'];

$trainerCountQuery = "SELECT COUNT(*) as total FROM users WHERE role = 'trainer'";
$trainerCountResult = $conn->query($trainerCountQuery);
$trainerCount = $trainerCountResult->fetch_assoc()['total'];

$clientCountQuery = "SELECT COUNT(*) as total FROM users WHERE role IN ('client', 'paid_client', 'free_client')";
$clientCountResult = $conn->query($clientCountQuery);
$clientCount = $clientCountResult->fetch_assoc()['total'];

echo "<ul>";
echo "<li>📊 <strong>Total Users:</strong> <span class='count'>$total</span></li>";
echo "<li>👨‍🏫 <strong>Total Trainers:</strong> <span class='count'>$trainerCount</span></li>";
echo "<li>👥 <strong>Total Clients:</strong> <span class='count'>$clientCount</span></li>";
echo "</ul>";
echo "</div>";

$conn->close();
?>

<p style="margin-top: 30px;"><a href="home.php" style="color: #0F2C59;">← Back to Home</a></p>

</body>
</html>
