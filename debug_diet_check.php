<?php
require 'db_connect.php';

// Check for Krishnaveni (ID 3)
$userId = 3;

echo "<h2>Checking Diet Plan for User ID: $userId</h2>";

$sql = "SELECT * FROM trainer_diet_plans WHERE user_id = $userId";
$res = $conn->query($sql);

if ($res->num_rows > 0) {
    while($row = $res->fetch_assoc()) {
        echo "<hr>";
        echo "Diet ID: " . $row['diet_id'] . "<br>";
        echo "Plan Name: " . $row['plan_name'] . "<br>";
        echo "User ID: " . $row['user_id'] . "<br>";
        echo "Meal Details (Raw): " . htmlspecialchars($row['meal_details']) . "<br>";
        
        $decoded = json_decode($row['meal_details'], true);
        if ($decoded) {
            echo "<b>JSON Decoded:</b><pre>";
            print_r($decoded);
            echo "</pre>";
        } else {
            echo "<b>Not JSON or Invalid</b><br>";
        }
    }
} else {
    echo "No diet plan found for User ID $userId.<br>";
    
    // Check if any plan exists by name?
    echo "Checking by Client Name 'KRISHNAVENI R NAIR'...<br>";
    $s2 = "SELECT * FROM trainer_diet_plans WHERE client_name LIKE '%KRISHNAVENI%'";
    $r2 = $conn->query($s2);
    while($row = $r2->fetch_assoc()) {
        echo "Found by Name (ID: " . $row['diet_id'] . ") - UserID: " . $row['user_id'] . "<br>";
    }
}
?>
