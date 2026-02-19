<?php
require 'db_connect.php';

$res = $conn->query("SHOW COLUMNS FROM users");
if ($res) {
    echo "Columns in users table:\n";
    while ($row = $res->fetch_assoc()) {
        echo $row['Field'] . "\n";
    }
} else {
    echo "Error: " . $conn->error;
}
echo "\n----------------\n";
$res2 = $conn->query("SHOW COLUMNS FROM client_profiles");
if ($res2) {
    echo "Columns in client_profiles table:\n";
    while ($row = $res2->fetch_assoc()) {
        echo $row['Field'] . "\n";
    }
} else {
    echo "Error: " . $conn->error;
}
?>
