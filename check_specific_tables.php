<?php
require 'db_connect.php';

function describe($conn, $table) {
    echo "Structure of $table:\n";
    $res = $conn->query("DESCRIBE $table");
    while($row = $res->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    echo "\n";
}

describe($conn, 'client_profiles');
describe($conn, 'trainer_workouts');
?>
