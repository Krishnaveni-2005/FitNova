<?php
$conn = new mysqli('localhost', 'root', '', 'fitnova_db');
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$result = $conn->query("SHOW TABLES");
$markdown = "## 4.5 TABLE DESIGN\n\n";
$i = 1;

while ($row = $result->fetch_row()) {
    $table = $row[0];
    if (strtolower($table) == 'wishlist' || strtolower($table) == 'wishlists') continue;
    
    $markdown .= "**" . $i++ . ". " . ucfirst($table) . " Table**\n\n";
    $markdown .= "| Field Name | Data Type | Description |\n";
    $markdown .= "|---|---|---|\n";
    
    $desc = $conn->query("DESCRIBE `$table`");
    while ($col = $desc->fetch_assoc()) {
        $field = $col['Field'];
        $type = strtoupper($col['Type']);
        if ($col['Key'] == 'PRI') $type .= " (PK)";
        else if ($col['Key'] == 'UNI') $type .= " (UNIQUE)";
        
        $descText = "";
        
        // Let's try to infer a good description
        $fLower = strtolower($field);
        if ($col['Key'] == 'PRI') $descText = "Unique ID for " . rtrim(strtolower($table), 's');
        elseif (strpos($fLower, 'email') !== false) $descText = "User's email address";
        elseif (strpos($fLower, 'phone') !== false) $descText = "Contact number";
        elseif (strpos($fLower, 'password') !== false) $descText = "Encrypted password";
        elseif (strpos($fLower, 'name') !== false) $descText = "Full name of the " . rtrim(strtolower($table), 's');
        elseif (strpos($fLower, 'date') !== false || strpos($fLower, '_at') !== false) $descText = "Timestamp / Date of action";
        elseif (strpos($fLower, 'status') !== false) $descText = "Current status (e.g. Active, Pending)";
        elseif (strpos($fLower, 'role') !== false || strpos($fLower, 'type') !== false) $descText = ucfirst(str_replace('_', ' ', $field));
        elseif (strpos($fLower, 'id') !== false) {
             if (strpos($fLower, 'trainer_id') !== false) $descText = "Foreign Key linking to Trainer";
             elseif (strpos($fLower, 'user_id') !== false || strpos($fLower, 'client_id') !== false) $descText = "Foreign Key linking to User";
             elseif (strpos($fLower, 'product_id') !== false) $descText = "Foreign Key linking to Product";
             else $descText = "Foreign key reference";
        }
        else {
             $descText = ucfirst(str_replace('_', ' ', $field));
        }

        $markdown .= "| {$field} | {$type} | {$descText} |\n";
    }
    $markdown .= "\n<br>\n\n";
}

file_put_contents('schema_dump.md', $markdown);
echo "Done";
?>
