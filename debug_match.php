<?php
require_once 'config.php';
require_once 'db_connect.php';

echo "<h1>Debug Trainer Match</h1>";

// 1. Find Trainer Agnus
$sql = "SELECT user_id, first_name, last_name, email FROM users WHERE first_name LIKE '%Agnus%'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($trainer = $result->fetch_assoc()) {
        $trainerId = $trainer['user_id'];
        echo "<h3>Trainer Found: " . $trainer['first_name'] . " " . $trainer['last_name'] . " (ID: $trainerId)</h3>";
        
        // 2. Check for assigned clients (Invite Sent)
        echo "<h4>Clients with status 'trainer_invite' Assigned to ID $trainerId:</h4>";
        $clientSql = "SELECT user_id, first_name, assignment_status, assigned_trainer_id FROM users WHERE assigned_trainer_id = $trainerId";
        $cRes = $conn->query($clientSql);
        
        if ($cRes->num_rows > 0) {
            echo "<table border='1'><tr><th>Client ID</th><th>Name</th><th>Status</th><th>Assigned Trainer ID</th></tr>";
            while($client = $cRes->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $client['user_id'] . "</td>";
                echo "<td>" . $client['first_name'] . "</td>";
                echo "<td>" . $client['assignment_status'] . "</td>";
                echo "<td>" . $client['assigned_trainer_id'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No clients assigned to this trainer.</p>";
        }
        
        // 3. Check for any 'admin_suggested' matches in trainer_applications
        echo "<h4>Trainer Applications entries:</h4>";
        $appSql = "SELECT * FROM trainer_applications WHERE trainer_id = $trainerId";
        $aRes = $conn->query($appSql);
        
        if ($aRes && $aRes->num_rows > 0) {
            echo "<table border='1'><tr><th>App ID</th><th>Client ID</th><th>Status</th></tr>";
            while($app = $aRes->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $app['application_id'] . "</td>";
                echo "<td>" . $app['client_id'] . "</td>";
                echo "<td>" . $app['status'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // SIMULATE DASHBOARD QUERY
            echo "<h4>Simulating Dashboard Query:</h4>";
            $dashSql = "SELECT u.user_id, u.first_name, u.last_name, cp.fitness_goals 
                       FROM users u 
                       JOIN trainer_applications ta ON u.user_id = ta.client_id
                       LEFT JOIN client_profiles cp ON u.user_id = cp.user_id 
                       WHERE ta.trainer_id = $trainerId AND ta.status = 'admin_suggested'";
            
            echo "<pre>$dashSql</pre>";
            
            $dRes = $conn->query($dashSql);
            if (!$dRes) {
                echo "<p style='color:red'>SQL Error: " . $conn->error . "</p>";
            } elseif ($dRes->num_rows > 0) {
                echo "<p style='color:green'>Query Success! Found " . $dRes->num_rows . " rows.</p>";
                while($dRow = $dRes->fetch_assoc()) {
                    print_r($dRow);
                }
            } else {
                echo "<p style='color:red'>Query returned 0 rows. Possible reasons:</p>";
                echo "<ul>";
                
                // Check User 48
                $u48 = $conn->query("SELECT * FROM users WHERE user_id = 48");
                if ($u48->num_rows == 0) echo "<li>User 48 does not exist in 'users' table.</li>";
                else echo "<li>User 48 exists.</li>";
                
                // Check Profile
                $p48 = $conn->query("SELECT * FROM client_profiles WHERE user_id = 48");
                if ($p48->num_rows == 0) echo "<li>Client profile for 48 not found (Left join should handle this).</li>";
                else echo "<li>Client profile exists.</li>";
                
                echo "</ul>";
            }
            
        } else {
             echo "<p>No application entries found.</p>";
        }
    }
} else {
    echo "Trainer Agnus not found.";
}
?>
