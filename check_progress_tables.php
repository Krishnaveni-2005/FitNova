<?php
require 'db_connect.php';

$tables = [];
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
}

echo "Tables: " . implode(", ", $tables) . "\n\n";

foreach ($tables as $table) {
    if (strpos($table, 'progress') !== false || strpos($table, 'goal') !== false || strpos($table, 'tracking') !== false || strpos($table, 'log') !== false) {
        echo "Structure for '$table':\n";
        $res = $conn->query("DESCRIBE $table");
        while ($r = $res->fetch_assoc()) {
            echo $r['Field'] . " - " . $r['Type'] . "\n";
        }
        echo "\n";
    }
}
?>
