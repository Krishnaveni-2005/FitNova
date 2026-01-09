<?php
// fix_specific_images_custom.php
// Updates database to link specific products to their new custom images or high-quality fallbacks.

require 'db_connect.php';

echo "<h3>Linking Custom Images...</h3>";

// 1. Custom Generated Images (Local PNGs)
$custom_updates = [
    'Bodybuilding Stringer Vest' => 'stringer_vest.png',
    'Performance Hoodie' => 'performance_hoodie.png',
    'Gym Joggers' => 'gym_joggers.png',
    'Compression Leggings' => 'compression_leggings.png',
    'Graphic Gym Tee' => 'graphic_gym_tee.png',
    'Mesh Training Shorts' => 'mesh_shorts.png',
    'Pro Compress Shorts' => 'mesh_shorts.png' // Re-use mesh shorts image
];

foreach ($custom_updates as $name => $img) {
    // LIKE query to match partial names if needed, but exact is safer for these
    $stmt = $conn->prepare("UPDATE products SET image_url = ? WHERE name = ?");
    $stmt->bind_param("ss", $img, $name);
    $stmt->execute();
    if($stmt->affected_rows > 0) echo "✓ Linked $name to <em>$img</em><br>";
}

// 2. Fallbacks for items we couldn't generate (Unsplash)
$fallbacks = [
    'High-Waist Sculpt Leggings' => 'https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?auto=format&fit=crop&q=80&w=600',
    'PowerLifting Singlet' => 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&q=80&w=600', // Man lifting
    'Core Tank Top' => 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?auto=format&fit=crop&q=80&w=600', // White tank/tee
    'Thermal Base Layer' => 'https://images.unsplash.com/photo-1516666117442-31c18e3fa78d?auto=format&fit=crop&q=80&w=600', // Workout top
    'Stealth Gym Hoodie' => 'performance_hoodie.png' // Reuse the hoodie image
];

foreach ($fallbacks as $name => $img) {
    $stmt = $conn->prepare("UPDATE products SET image_url = ? WHERE name = ?");
    $stmt->bind_param("ss", $img, $name);
    $stmt->execute();
    if($stmt->affected_rows > 0) echo "✓ Linked $name to fallback image.<br>";
}

// 3. Catch-all: If any product still has no image (NULL or empty string), give it a generic one
$generic = 'https://images.unsplash.com/photo-1571902943202-507ec2618e8f?auto=format&fit=crop&q=80&w=600'; // Gym generic
$conn->query("UPDATE products SET image_url = '$generic' WHERE image_url IS NULL OR image_url = ''");

echo "<hr><p>All specific images updated!</p>";
echo "<a href='fitshop.php' style='padding:10px 20px; background:#0F2C59; color:white; text-decoration:none; border-radius:5px;'>Return to FitShop</a>";
?>
