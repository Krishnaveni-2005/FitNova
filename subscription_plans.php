<?php
session_start();
$currentRole = $_SESSION['user_role'] ?? 'guest';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Plans - FitNova</title>
    <!-- Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0F2C59;
            --accent-color: #E63946;
            --bg-color: #F8F9FA;
            --text-color: #333333;
            --text-light: #6C757D;
            --white: #FFFFFF;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 60px 20px;
            text-align: center;
        }

        h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .subtitle {
            font-size: 1.1rem;
            color: var(--text-light);
            margin-bottom: 50px;
        }

        /* Toggle */
        .toggle-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 60px;
        }

        .toggle-label {
            font-weight: 600;
            font-size: 1rem;
            color: var(--text-color);
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: var(--primary-color);
        }

        input:checked+.slider:before {
            transform: translateX(26px);
        }

        /* Pricing Cards */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .plan-card {
            background: var(--white);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            border: 2px solid transparent;
        }

        .plan-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .plan-card.popular {
            border-color: var(--primary-color);
        }

        .popular-tag {
            background: var(--primary-color);
            color: white;
            padding: 5px 30px;
            font-size: 0.8rem;
            font-weight: 700;
            position: absolute;
            top: 20px;
            right: -30px;
            transform: rotate(45deg);
            text-transform: uppercase;
        }

        .plan-name {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .price {
            font-size: 3rem;
            font-weight: 800;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .price span {
            font-size: 1rem;
            color: var(--text-light);
            font-weight: 400;
        }

        .billing-text {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 30px;
            height: 20px;
        }

        .features {
            list-style: none;
            margin-bottom: 40px;
            text-align: left;
        }

        .features li {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-color);
        }

        .features li i {
            color: #2ECC71;
        }

        .features li.unavailable {
            color: #ccc;
            text-decoration: line-through;
        }

        .features li.unavailable i {
            color: #ccc;
        }

        .btn-plan {
            display: inline-block;
            width: 100%;
            padding: 15px;
            background: var(--primary-color);
            color: white;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
        }

        .btn-plan:hover {
            background: #0a1f3f;
            transform: scale(1.05);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
        }

        .btn-disabled {
            background: #E9ECEF;
            color: #ADB5BD;
            cursor: not-allowed;
            pointer-events: none;
        }

        .back-link {
            display: inline-block;
            margin-top: 40px;
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
        }

        .back-link:hover {
            color: var(--primary-color);
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Unlock Your Full Potential</h1>
        <p class="subtitle">Choose the plan that fits your fitness journey.</p>

        <div class="toggle-container">
            <span class="toggle-label">Monthly</span>
            <label class="switch">
                <input type="checkbox" id="billingToggle">
                <span class="slider"></span>
            </label>
            <span class="toggle-label">Yearly <span
                    style="color: var(--accent-color); font-size: 0.8rem; margin-left: 5px;">(Save 20%)</span></span>
        </div>

        <div class="pricing-grid">
            <!-- Free Plan -->
            <div class="plan-card">
                <div class="plan-name">Basic</div>
                <div class="price">Free</div>
                <p class="billing-text">Forever free</p>
                <ul class="features">
                    <li><i class="fas fa-check"></i> Basic Workout Library</li>
                    <li><i class="fas fa-check"></i> 1 Active Program</li>
                    <li><i class="fas fa-check"></i> Community Access</li>
                    <li class="unavailable"><i class="fas fa-times"></i> Personal Coach</li>
                    <li class="unavailable"><i class="fas fa-times"></i> Custom Meal Plans</li>
                    <li class="unavailable"><i class="fas fa-times"></i> Live Classes</li>
                </ul>
                <?php if ($currentRole === 'free'): ?>
                    <a href="#" class="btn-plan btn-outline btn-disabled">Current Plan</a>
                <?php else: ?>
                    <a href="freeuser_dashboard.php" class="btn-plan btn-outline">Switch to Basic</a>
                <?php endif; ?>
            </div>

            <!-- Pro Plan -->
            <div class="plan-card popular">
                <div class="popular-tag">Best Value</div>
                <div class="plan-name">Pro Member</div>
                <div class="price" id="proPrice">₹2,499</div>
                <p class="billing-text" id="proBilling">per month</p>
                <ul class="features">
                    <li><i class="fas fa-check"></i> Unlimited Workouts</li>
                    <li><i class="fas fa-check"></i> Custom Training Plans</li>
                    <li><i class="fas fa-check"></i> Advanced Progress Tracking</li>
                    <li><i class="fas fa-check"></i> Nutrition Guide</li>
                    <li><i class="fas fa-check"></i> Priority Support</li>
                    <li class="unavailable"><i class="fas fa-times"></i> 1-on-1 Coaching</li>
                </ul>
                <?php if ($currentRole === 'pro'): ?>
                    <a href="#" class="btn-plan btn-disabled">Current Plan</a>
                <?php else: ?>
                    <a href="#" class="btn-plan" id="btnPro">Get Started</a>
                <?php endif; ?>
            </div>

            <!-- Elite Plan -->
            <div class="plan-card">
                <div class="plan-name">Elite</div>
                <div class="price" id="elitePrice">₹4,999</div>
                <p class="billing-text" id="eliteBilling">per month</p>
                <ul class="features">
                    <li><i class="fas fa-check"></i> Everything in Pro</li>
                    <li><i class="fas fa-check"></i> Dedicated Personal Coach</li>
                    <li><i class="fas fa-check"></i> Weekly Video Check-ins</li>
                    <li><i class="fas fa-check"></i> Personalized Meal Plans</li>
                    <li><i class="fas fa-check"></i> Live 1-on-1 Classes</li>
                    <li><i class="fas fa-check"></i> Exclusive FitShop Discounts</li>
                </ul>
                <?php if ($currentRole === 'elite'): ?>
                    <a href="#" class="btn-plan btn-disabled">Current Plan</a>
                <?php else: ?>
                    <a href="#" class="btn-plan" id="btnElite">Go Elite</a>
                <?php endif; ?>
            </div>
        </div>

        <?php
        $backLink = 'home.php';
        if ($currentRole === 'free') $backLink = 'freeuser_dashboard.php';
        elseif ($currentRole === 'pro') $backLink = 'prouser_dashboard.php';
        elseif ($currentRole === 'elite') $backLink = 'eliteuser_dashboard.php';
        ?>
        <a href="<?php echo $backLink; ?>" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <script>
        const toggle = document.getElementById('billingToggle');
        const proPrice = document.getElementById('proPrice');
        const elitePrice = document.getElementById('elitePrice');
        const proBilling = document.getElementById('proBilling');
        const eliteBilling = document.getElementById('eliteBilling');
        const btnPro = document.getElementById('btnPro');
        const btnElite = document.getElementById('btnElite');

        let isYearly = false;

        toggle.addEventListener('change', function () {
            isYearly = this.checked;
            if (isYearly) {
                // Yearly (approx 20% off)
                if (proPrice) proPrice.innerText = '₹7,999';
                if (elitePrice) elitePrice.innerText = '₹8,999';
                if (proBilling) proBilling.innerText = 'per year (save 20%)';
                if (eliteBilling) eliteBilling.innerText = 'per year (save 20%)';
            } else {
                // Monthly
                if (proPrice) proPrice.innerText = '₹2,499';
                if (elitePrice) elitePrice.innerText = '₹4,999';
                if (proBilling) proBilling.innerText = 'per month';
                if (eliteBilling) eliteBilling.innerText = 'per month';
            }
        });

        if (btnPro) {
            btnPro.addEventListener('click', (e) => {
                e.preventDefault();
                const billing = isYearly ? 'yearly' : 'monthly';
                window.location.href = `payment.php?plan=pro&billing=${billing}`;
            });
        }

        if (btnElite) {
            btnElite.addEventListener('click', (e) => {
                e.preventDefault();
                const billing = isYearly ? 'yearly' : 'monthly';
                window.location.href = `payment.php?plan=elite&billing=${billing}`;
            });
        }
    </script>
</body>

</html>
