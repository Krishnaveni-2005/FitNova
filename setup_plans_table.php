<?php
require 'db_connect.php';

// Create plans table
$sql = "CREATE TABLE IF NOT EXISTS subscription_plans (
    plan_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    price_monthly DECIMAL(10,2) NOT NULL,
    price_yearly DECIMAL(10,2) NOT NULL,
    description VARCHAR(255),
    features TEXT, -- JSON array of features
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table subscription_plans created successfully.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Check if empty, then seed
$check = $conn->query("SELECT COUNT(*) as count FROM subscription_plans");
$row = $check->fetch_assoc();

if ($row['count'] == 0) {
    $plans = [
        [
            'name' => 'Basic',
            'price_monthly' => 0.00,
            'price_yearly' => 0.00,
            'description' => 'Forever free',
            'features' => json_encode([
                "Basic Workout Library",
                "1 Active Program",
                "Community Access"
            ])
        ],
        [
            'name' => 'Pro',
            'price_monthly' => 2499.00,
            'price_yearly' => 7999.00,
            'description' => 'Best Value',
            'features' => json_encode([
                "Unlimited Workouts",
                "Custom Training Plans",
                "Advanced Progress Tracking",
                "Nutrition Guide",
                "Priority Support"
            ])
        ],
        [
            'name' => 'Elite',
            'price_monthly' => 4999.00,
            'price_yearly' => 8999.00, // Based on the JS logic in subscription_plans.php
            'description' => 'Ultimate Experience',
            'features' => json_encode([
                "Everything in Pro",
                "Dedicated Personal Coach",
                "Weekly Video Check-ins",
                "Personalized Meal Plans",
                "Live 1-on-1 Classes",
                "Exclusive FitShop Discounts"
            ])
        ]
    ];

    $stmt = $conn->prepare("INSERT INTO subscription_plans (name, price_monthly, price_yearly, description, features) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($plans as $p) {
        $stmt->bind_param("sddss", $p['name'], $p['price_monthly'], $p['price_yearly'], $p['description'], $p['features']);
        $stmt->execute();
    }
    echo "Seeded default plans.<br>";
    $stmt->close();
} else {
    echo "Plans table already has data.<br>";
}

$conn->close();
?>
