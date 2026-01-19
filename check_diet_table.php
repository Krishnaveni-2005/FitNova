<?php
require 'db_connect.php';

// Check columns
$cols = $conn->query("SHOW COLUMNS FROM trainer_diet_plans");
$hasUserId = false;
if($cols) {
    while($row = $cols->fetch_assoc()) {
        echo $row['Field'] . " ";
        if($row['Field'] == 'user_id') $hasUserId = true;
    }
} else {
    echo "Table does not exist. Creating default...";
}

if (!$hasUserId) {
    echo "<br>Adding user_id column...";
    $conn->query("ALTER TABLE trainer_diet_plans ADD COLUMN user_id INT AFTER trainer_id");
}
?>
