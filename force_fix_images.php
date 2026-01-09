<?php
// force_fix_images.php
// Uses robust pattern matching to force update images for products that were missed.

require 'db_connect.php';

echo "<h3>Force Fixing Product Images...</h3>";

// Map of partial name matches -> New Image URL
$fixes = [
    // Men
    'Titan' => 'https://images.unsplash.com/photo-1617363020293-62381e4618a0?auto=format&fit=crop&q=80&w=600', // Titan Cargo Joggers
    'Thermal Base' => 'https://images.unsplash.com/photo-1516666117442-31c18e3fa78d?auto=format&fit=crop&q=80&w=600',
    
    // Women
    'Support Tank' => 'https://images.unsplash.com/photo-1605296867304-46d5465a13f1?auto=format&fit=crop&q=80&w=600',
    'Impact Sports' => 'https://images.unsplash.com/photo-1620799140408-ed5341cd2431?auto=format&fit=crop&q=80&w=600',
    'Long Sleeve Crop' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&q=80&w=600',
    'Booty Lift' => 'https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?auto=format&fit=crop&q=80&w=600',
    'Breathable Racerback' => 'https://images.unsplash.com/photo-1518310383802-640c2de311b2?auto=format&fit=crop&q=80&w=600',
    'Mesh Panel' => 'https://images.unsplash.com/photo-1574914629385-46448b767bb1?auto=format&fit=crop&q=80&w=600',
    'Seamless Crop' => 'https://images.unsplash.com/photo-1620799139507-2a76f79a2f4d?auto=format&fit=crop&q=80&w=600',
    
    // Supplements
    'Gold Whey' => 'https://images.unsplash.com/photo-1593095948071-474c5cc2989d?auto=format&fit=crop&q=80&w=600',
    'Vegan Pea' => 'https://images.unsplash.com/photo-1579722820308-d74e571900a9?auto=format&fit=crop&q=80&w=600',
    'Creatine Monohydrate' => 'https://plus.unsplash.com/premium_photo-1675806655184-7a353683f218?auto=format&fit=crop&q=80&w=600',
    'Pre-Workout' => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?auto=format&fit=crop&q=80&w=600'
];

$count = 0;

foreach ($fixes as $key => $url) {
    // Check if it exists before trying to update (for reporting)
    $cleanKey = "%" . $key . "%";
    $stmt = $conn->prepare("UPDATE products SET image_url = ? WHERE name LIKE ?");
    $stmt->bind_param("ss", $url, $cleanKey);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo "<p style='color:green'>✓ Fixed products matching: <strong>'$key'</strong></p>";
        $count += $stmt->affected_rows;
    } else {
        // Double check if it's already updated, or truly missing
        // This is just debug output
        echo "<p style='color:#666'>• No new update for target: '$key' (may already have image or name mismatch)</p>";
    }
}

echo "<h3>Summary</h3>";
echo "<p>Total products updated: $count</p>";
echo "<hr><a href='fitshop.php' style='padding:10px 20px; background:#0F2C59; color:white; text-decoration:none; border-radius:5px;'>Return to FitShop</a>";

$conn->close();
?>
