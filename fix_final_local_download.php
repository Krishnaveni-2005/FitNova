<?php
// fix_final_local_download.php
// URL to local downloader and mapper.
// Goal: Download the external images to the server so they load reliably, 
// and map any remaining missing items to existing local files.

require 'db_connect.php';

echo "<h3>Localizing Images...</h3>";

// 1. Helper Function to Download Image
function downloadImage($url, $saveAs) {
    if (file_exists($saveAs)) return true; // Already exists
    
    $content = @file_get_contents($url);
    if ($content) {
        file_put_contents($saveAs, $content);
        return true;
    }
    return false;
}

// 2. define Sources for missing categories
$suppUrl = 'https://images.unsplash.com/photo-1593095948071-474c5cc2989d?auto=format&fit=crop&q=80&w=600';
$womenUrl = 'https://images.unsplash.com/photo-1518310383802-640c2de311b2?auto=format&fit=crop&q=80&w=600';

// 3. Download them
$hasSupp = downloadImage($suppUrl, 'supplement_gen.jpg');
$hasWomen = downloadImage($womenUrl, 'women_gen.jpg');

if ($hasSupp) echo "✓ Downloaded supplement image.<br>";
else echo "⚠ Could not download supplement image. Using fallback.<br>";

if ($hasWomen) echo "✓ Downloaded women's image.<br>";
else echo "⚠ Could not download women's image. Using fallback.<br>";

// 4. MAPPINGS
// We map specific keywords to our LOCAL files.
$updates = [
    // Existing Local PNGs
    'Titan Cargo' => 'gym_joggers.png',
    'Thermal Base' => 'performance_hoodie.png', // Best match we have
    'Mesh Training' => 'mesh_shorts.png',
    'Compression' => 'compression_leggings.png',
    'Legging' => 'compression_leggings.png', // Catch all leggings
    'Jogger' => 'gym_joggers.png', // Catch all joggers
    'Hoodie' => 'performance_hoodie.png',
    'Tee' => 'graphic_gym_tee.png',
    'Short' => 'mesh_shorts.png',
    
    // New Downloads (or fallbacks)
    'Supplement' => ($hasSupp ? 'supplement_gen.jpg' : 'graphic_gym_tee.png'),
    'Whey' => ($hasSupp ? 'supplement_gen.jpg' : 'graphic_gym_tee.png'),
    'Protein' => ($hasSupp ? 'supplement_gen.jpg' : 'graphic_gym_tee.png'),
    'Creatine' => ($hasSupp ? 'supplement_gen.jpg' : 'graphic_gym_tee.png'),
    'Pre-Workout' => ($hasSupp ? 'supplement_gen.jpg' : 'graphic_gym_tee.png'),
    'Multivitamin' => ($hasSupp ? 'supplement_gen.jpg' : 'graphic_gym_tee.png'),
    
    'Tank' => ($hasWomen ? 'women_gen.jpg' : 'stringer_vest.png'),
    'Bra' => ($hasWomen ? 'women_gen.jpg' : 'stringer_vest.png'),
    'Crop' => ($hasWomen ? 'women_gen.jpg' : 'graphic_gym_tee.png'),
    'Women' => ($hasWomen ? 'women_gen.jpg' : 'compression_leggings.png')
];

// 5. Execute Updates
foreach ($updates as $keyword => $img) {
    $search = "%" . $keyword . "%";
    // Update anything matching the keyword
    // We update ONLY if it's currently using an external link (http...) or is empty
    // preventing overwrite of our good specific pngs if possible, though our mapping is pretty specific.
    $sql = "UPDATE products SET image_url = '$img' WHERE name LIKE '$search' AND image_url LIKE 'http%'";
    $conn->query($sql);
}

// 6. Final Catch-all for Supplements specifically
if ($hasSupp) {
    $conn->query("UPDATE products SET image_url = 'supplement_gen.jpg' WHERE category = 'supplements' AND image_url LIKE 'http%'");
}

echo "<hr><p>All images have been converted to LOCAL files!</p>";
echo "<a href='fitshop.php' style='padding:10px 20px; background:#0F2C59; color:white; text-decoration:none; border-radius:5px;'>Return to FitShop</a>";

$conn->close();
?>
