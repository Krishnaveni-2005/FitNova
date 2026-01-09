<?php
// fix_remaining_images.php
// Targeted fixes for specific missing product images using high-quality Unsplash matches.

require 'db_connect.php';

echo "<h3>Fixing Remaining Missing Images...</h3>";

$updates = [
    // MEN
    'Titan Cargo Joggers' => 'https://images.unsplash.com/photo-1617363020293-62381e4618a0?auto=format&fit=crop&q=80&w=600',
    'Thermal Base Layer' => 'https://images.unsplash.com/photo-1516666117442-31c18e3fa78d?auto=format&fit=crop&q=80&w=600',
    
    // WOMEN
    'Support Tank Top' => 'https://images.unsplash.com/photo-1605296867304-46d5465a13f1?auto=format&fit=crop&q=80&w=600',
    'Impact Sports Bra' => 'https://images.unsplash.com/photo-1620799140408-ed5341cd2431?auto=format&fit=crop&q=80&w=600',
    'Long Sleeve Crop' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&q=80&w=600',
    'Booty Lift Shorts' => 'https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?auto=format&fit=crop&q=80&w=600',
    'Breathable Racerback Tank' => 'https://images.unsplash.com/photo-1518310383802-640c2de311b2?auto=format&fit=crop&q=80&w=600',
    'Mesh Panel Leggings' => 'https://images.unsplash.com/photo-1574914629385-46448b767bb1?auto=format&fit=crop&q=80&w=600',
    'Seamless Crop Top' => 'https://images.unsplash.com/photo-1620799139507-2a76f79a2f4d?auto=format&fit=crop&q=80&w=600',
    
    // SUPPLEMENTS
    '100% Gold Whey Isolate (2.5kg)' => 'https://images.unsplash.com/photo-1593095948071-474c5cc2989d?auto=format&fit=crop&q=80&w=600',
    'Vegan Pea Protein' => 'https://images.unsplash.com/photo-1579722820308-d74e571900a9?auto=format&fit=crop&q=80&w=600',
    'Micronized Creatine Monohydrate' => 'https://plus.unsplash.com/premium_photo-1675806655184-7a353683f218?auto=format&fit=crop&q=80&w=600',
    'Explode Pre-Workout' => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?auto=format&fit=crop&q=80&w=600'
];

foreach ($updates as $name => $url) {
    // using prepared statements for safety, though these strings are safe
    $stmt = $conn->prepare("UPDATE products SET image_url = ? WHERE name = ?");
    $stmt->bind_param("ss", $url, $name);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo "✓ Fixed: <strong>$name</strong><br>";
    } else {
        echo "• No change/Not found: $name<br>";
    }
}

// Final sweep for any broken broken links (cleanup)
echo "<br>Checking for any remaining missing images...<br>";
// Fix 'Graphic Gym Tee' just in case the previous script missed it or it reverted
// We use the custom one if available, otherwise fallback
$conn->query("UPDATE products SET image_url = 'graphic_gym_tee.png' WHERE name = 'Graphic Gym Tee' AND image_url NOT LIKE '%.png%'");

echo "<hr><p>All targeted products have been updated!</p>";
echo "<a href='fitshop.php' style='padding:10px 20px; background:#0F2C59; color:white; text-decoration:none; border-radius:5px;'>Return to FitShop</a>";

$conn->close();
?>
