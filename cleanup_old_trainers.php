<?php
require 'db_connect.php';

$approved_last_names = [
    'Joseph', 'Sam', 'John', 'Jojo', 'James', 'Benny', 'R', 'Thomas', 'Reji', 'Shibu', 'Sabu'
];
// Full names for more precision if needed
// Joshua Joseph, Abner Sam, David John, Albin Jojo, Melbin James, 
// Nevin Benny, Rajesh R, Alen Thomas, Elis Reji, Reibin Chacko Thomas, 
// Sharon Shibu, Agnus Sabu.

// We will delete any trainer whose Last Name is NOT in our approved list.
// Note: 'Thomas' appears twice (Alen Thomas, Reibin Chacko Thomas), which is fine.

$last_names_string = "'" . implode("','", $approved_last_names) . "'";

$sql = "DELETE FROM users WHERE role = 'trainer' AND last_name NOT IN ($last_names_string)";

if ($conn->query($sql) === TRUE) {
    echo "Cleaned up " . $conn->affected_rows . " old trainer records.";
} else {
    echo "Error cleaning up: " . $conn->error;
}

$conn->close();
?>
