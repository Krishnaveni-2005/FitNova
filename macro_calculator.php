<?php
session_start();
require "db_connect.php";

$userId = $_SESSION['user_id'] ?? 0;
// Fetch user data if logged in to pre-fill
$userProfile = [];
if ($userId) {
    $stmt = $conn->prepare("SELECT * FROM client_profiles WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $userProfile = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$calculated = false;
$results = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $age = intval($_POST['age']);
    $gender = $_POST['gender'];
    $height = floatval($_POST['height']);
    $weight = floatval($_POST['weight']);
    $activity = $_POST['activity'];
    $goal = $_POST['goal'];

    // Mifflin-St Jeor Equation
    if ($gender === 'male') {
        $bmr = (10 * $weight) + (6.25 * $height) - (5 * $age) + 5;
    } else {
        $bmr = (10 * $weight) + (6.25 * $height) - (5 * $age) - 161;
    }

    // Activity Multiplier
    $multipliers = [
        'sedentary' => 1.2,
        'light' => 1.375,
        'moderate' => 1.55,
        'active' => 1.725,
        'extra' => 1.9
    ];
    $tdee = $bmr * ($multipliers[$activity] ?? 1.2);

    // Goal Adjustment
    $calories = $tdee;
    if ($goal === 'lose') $calories -= 500;
    if ($goal === 'gain') $calories += 500;

    // Macros Split (Standard Balanced)
    $proteinGrams = round(($calories * 0.30) / 4);
    $fatGrams = round(($calories * 0.25) / 9);
    $carbGrams = round(($calories * 0.45) / 4);

    $results = [
        'bmr' => round($bmr),
        'tdee' => round($tdee),
        'daily_calories' => round($calories),
        'protein' => $proteinGrams,
        'fats' => $fatGrams,
        'carbs' => $carbGrams,
        'calculated_at' => date('Y-m-d H:i:s'),
        'type' => 'auto'
    ];
    $calculated = true;

    // Save to Database if logged in
    if ($userId) {
        // Ensure the client_profiles record exists
        $checkProfile = $conn->prepare("SELECT user_id FROM client_profiles WHERE user_id = ?");
        $checkProfile->bind_param("i", $userId);
        $checkProfile->execute();
        $profileExists = $checkProfile->get_result()->fetch_assoc();
        $checkProfile->close();
        
        if (!$profileExists) {
            // Create the profile record first
            $createProfile = $conn->prepare("INSERT INTO client_profiles (user_id) VALUES (?)");
            $createProfile->bind_param("i", $userId);
            $createProfile->execute();
            $createProfile->close();
        }
        
        // Now save the macros
        $jsonMacros = json_encode($results);
        $updSql = "UPDATE client_profiles SET custom_macros_json = ? WHERE user_id = ?";
        $stmt = $conn->prepare($updSql);
        $stmt->bind_param("si", $jsonMacros, $userId);
        $stmt->execute();
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Macro Calculator - FitNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0F2C59;
            --secondary-color: #DAC0A3;
            --accent-color: #E63946;
            --bg-color: #F8F9FA;
            --text-color: #333;
            --success-color: #28a745;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            padding: 30px;
        }

        .page-header {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        h1 { font-family: 'Outfit', sans-serif; color: var(--primary-color); margin: 0; }
        .back-link { text-decoration: none; color: #666; font-weight: 500; display: flex; align-items: center; gap: 8px; }
        .back-link:hover { color: var(--primary-color); }

        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #555; }
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
        }
        
        .btn-calc {
            width: 100%;
            background: var(--primary-color);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-calc:hover { background: #0a1f3f; }

        .result-box {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        .result-value {
            font-family: 'Outfit', sans-serif;
            font-size: 36px;
            font-weight: 700;
            color: var(--primary-color);
        }
        .result-label { color: #777; font-size: 14px; }

        .macro-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        .macro-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border-top: 4px solid #ccc;
        }
        .macro-card.protein { border-color: var(--primary-color); }
        .macro-card.carbs { border-color: var(--accent-color); }
        .macro-card.fats { border-color: var(--secondary-color); }

        .macro-val { font-weight: 700; font-size: 20px; display: block; margin-top: 5px; }

        @media (max-width: 768px) {
            .container { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <div>
                <h1>Macro Calculator</h1>
                <p style="color: #666;">Estimate your daily nutritional needs.</p>
            </div>
            <a href="prouser_dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>

        <!-- Input Form -->
        <div class="card">
            <h3 style="margin-top: 0; color: var(--primary-color); margin-bottom: 20px;">Your Details</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" required>
                        <option value="male" <?php echo (($userProfile['gender'] ?? '') == 'male') ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo (($userProfile['gender'] ?? '') == 'female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Age</label>
                        <input type="number" name="age" value="<?php echo isset($_POST['age']) ? $_POST['age'] : 25; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Height (cm)</label>
                        <input type="number" name="height" value="<?php echo $userProfile['height_cm'] ?? (isset($_POST['height']) ? $_POST['height'] : 175); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Current Weight (kg)</label>
                    <input type="number" name="weight" value="<?php echo $userProfile['weight_kg'] ?? (isset($_POST['weight']) ? $_POST['weight'] : 70); ?>" required>
                </div>
                <div class="form-group">
                    <label>Activity Level</label>
                    <select name="activity">
                        <option value="sedentary">Sedentary (Office job)</option>
                        <option value="light">Lightly Active (1-2 days/week)</option>
                        <option value="moderate" selected>Moderately Active (3-5 days/week)</option>
                        <option value="active">Very Active (6-7 days/week)</option>
                        <option value="extra">Extra Active (Physical job + training)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Goal</label>
                    <select name="goal">
                        <option value="lose">Lose Weight (-500 cal)</option>
                        <option value="maintain" selected>Maintain Weight</option>
                        <option value="gain">Gain Muscle (+500 cal)</option>
                    </select>
                </div>
                <button type="submit" class="btn-calc">Calculate Macros</button>
            </form>
        </div>

        <!-- Results Display -->
        <div class="card" style="display: flex; flex-direction: column; justify-content: center;">
            <?php if ($calculated): ?>
                <div style="text-align: center; margin-bottom: 20px;">
                    <i class="fas fa-chart-pie" style="font-size: 40px; color: var(--secondary-color);"></i>
                    <h2 style="color: var(--primary-color); margin: 10px 0;">Your Daily Targets</h2>
                </div>

                <div class="result-box">
                    <div class="result-value"><?php echo number_format($results['daily_calories']); ?></div>
                    <div class="result-label">Calories / day</div>
                </div>

                <div class="macro-grid">
                    <div class="macro-card protein">
                        <div style="font-size: 13px; color: #555;">Protein</div>
                        <span class="macro-val" style="color: var(--primary-color);"><?php echo $results['protein']; ?>g</span>
                    </div>
                    <div class="macro-card carbs">
                        <div style="font-size: 13px; color: #555;">Carbs</div>
                        <span class="macro-val" style="color: var(--accent-color);"><?php echo $results['carbs']; ?>g</span>
                    </div>
                    <div class="macro-card fats">
                        <div style="font-size: 13px; color: #555;">Fats</div>
                        <span class="macro-val" style="color: var(--secondary-color);"><?php echo $results['fats']; ?>g</span>
                    </div>
                </div>

                <div style="margin-top: 30px; background: #eef2ff; padding: 15px; border-radius: 8px;">
                    <h4 style="margin-top: 0; color: var(--primary-color);"><i class="fas fa-info-circle"></i> stats</h4>
                    <ul style="margin: 0; padding-left: 20px; font-size: 14px; color: #555;">
                        <li><strong>BMR:</strong> <?php echo $results['bmr']; ?> kcal (Basal Metabolic Rate)</li>
                        <li><strong>TDEE:</strong> <?php echo $results['tdee']; ?> kcal (Maintenance)</li>
                    </ul>
                </div>

            <?php else: ?>
                <div style="text-align: center; color: #999; padding: 40px;">
                    <i class="fas fa-calculator" style="font-size: 50px; margin-bottom: 20px; color: #eee;"></i>
                    <h3>Ready to Calculate</h3>
                    <p>Enter your details on the left to get your personalized macro split.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
