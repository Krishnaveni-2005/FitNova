<?php
// fix_men_images.php
// Specifically fixes the missing images in the Men's category caused by the bulk add script.

require 'db_connect.php';

echo "<h3>Fixing Men's Category Images...</h3>";

// Image pool for Men
$menImages = [
    'https://images.unsplash.com/photo-1581655353564-df123a1eb820?auto=format&fit=crop&q=80&w=600', // White tee
    'https://images.unsplash.com/photo-1618354691438-25bc04584c23?auto=format&fit=crop&q=80&w=600', // Black tee
    'https://images.unsplash.com/photo-1576566588028-4147f3842f27?auto=format&fit=crop&q=80&w=600', // Running shirt
    'https://images.unsplash.com/photo-1552160753-117159d79631?auto=format&fit=crop&q=80&w=600', // Joggers
    'https://images.unsplash.com/photo-1516666117442-31c18e3fa78d?auto=format&fit=crop&q=80&w=600', // Man working out
    'https://images.unsplash.com/photo-1506629082955-511b1aa002c9?auto=format&fit=crop&q=80&w=600', // Male model
    'https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?auto=format&fit=crop&q=80&w=600', // Shirtless/active
    'https://images.unsplash.com/photo-1483721310020-03333e577078?auto=format&fit=crop&q=80&w=600', // Training
    'https://images.unsplash.com/photo-1560272564-c83b66b1ad12?auto=format&fit=crop&q=80&w=600',  // Lifestyle
    'https://images.unsplash.com/photo-1599058945522-28d584b6f0ff?auto=format&fit=crop&q=80&w=600'  // Gym pose
];

// 1. Fix Men's products that are incorrectly using the stringer vest image
// We exclude the actual 'Bodybuilding Stringer Vest' from this update
$sql = "SELECT product_id, name FROM products WHERE category = 'men' AND name != 'Bodybuilding Stringer Vest'";
$result = $conn->query($sql);

$count = 0;
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $randImg = $menImages[array_rand($menImages)];
        $pid = $row['product_id'];
        
        $update = $conn->query("UPDATE products SET image_url = '$randImg' WHERE product_id = $pid");
        if ($update) $count++;
    }
}

echo "✓ Fixed $count products in Men's category.<br>";

// 2. Double check ANY product with empty image
$sqlEmpty = "SELECT product_id FROM products WHERE image_url IS NULL OR image_url = ''";
$resEmpty = $conn->query($sqlEmpty);
if ($resEmpty->num_rows > 0) {
    echo "Found " . $resEmpty->num_rows . " other empty images. Patching...<br>";
    $fallback = 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&q=80&w=600'; // Dumbbells generic
    while($row = $resEmpty->fetch_assoc()) {
        $pid = $row['product_id'];
        $conn->query("UPDATE products SET image_url = '$fallback' WHERE product_id = $pid");
    }
    echo "✓ Patched empty images.<br>";
}

echo "<hr><p>All missing images repaired!</p>";
echo "<a href='fitshop.php' style='padding:10px 20px; background:#0F2C59; color:white; text-decoration:none; border-radius:5px;'>Return to FitShop</a>";

$conn->close();
?>
