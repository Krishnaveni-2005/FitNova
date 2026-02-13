<?php
session_start();
require 'db_connect.php';

$currentRole = $_SESSION['user_role'] ?? 'guest';
$userId = $_SESSION['user_id'] ?? 0;

// Check current subscription status
$canSwitch = true;
$subscriptionEnd = null;
$currentPlan = null;
$daysUntilSwitch = 0;

if ($userId > 0 && $currentRole !== 'free') {
    $subSql = "SELECT current_plan, subscription_end, can_switch_after FROM user_subscriptions WHERE user_id = ?";
    $subStmt = $conn->prepare($subSql);
    $subStmt->bind_param("i", $userId);
    $subStmt->execute();
    $subResult = $subStmt->get_result();
    
    if ($subRow = $subResult->fetch_assoc()) {
        $currentPlan = $subRow['current_plan'];
        $subscriptionEnd = $subRow['subscription_end'];
        $canSwitchAfter = $subRow['can_switch_after'];
        
        // Check if current date is before the can_switch_after date
        $today = date('Y-m-d');
        if ($today < $canSwitchAfter) {
            $canSwitch = false;
            $daysUntilSwitch = ceil((strtotime($canSwitchAfter) - strtotime($today)) / (60 * 60 * 24));
        }
    }
    $subStmt->close();
}
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
            --bg-color: #f1f5f9;
            --text-color: #1e293b;
            --text-light: #64748b;
            --white: #FFFFFF;
            --success: #10b981;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-hover: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
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
            background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
            background-size: 20px 20px;
            min-height: 100vh;
            display: flex;
            align-items: center; /* Vertical Center */
            justify-content: center; /* Horizontal Center */
            overflow-y: hidden; /* Try to prevent scroll if possible */
        }

        .container {
            width: 100%;
            max-width: 1200px;
            padding: 0 20px;
            text-align: center;
        }

        .header-content {
            margin-bottom: 30px;
        }

        h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2.2rem; /* Nice and big */
            color: var(--primary-color);
            margin-bottom: 8px;
            font-weight: 800;
        }

        .subtitle {
            font-size: 1rem;
            color: var(--text-light);
        }

        /* Toggle */
        .toggle-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-top: 15px;
        }

        .toggle-label {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--text-color);
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 28px;
        }

        .switch input { opacity: 0; width: 0; height: 0; }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #cbd5e1;
            transition: .3s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        input:checked+.slider { background-color: var(--primary-color); }
        input:checked+.slider:before { transform: translateX(22px); }

        /* Pricing Cards Grid */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            align-items: center; /* Align cards beautifully */
            margin-top: 20px;
        }

        .plan-card {
            background: var(--white);
            border-radius: 24px;
            padding: 25px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            height: 520px; /* Fixed height for uniformity */
        }

        .plan-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
        }

        /* Scale up the popular card slightly */
        .plan-card.popular {
            border: 2px solid var(--primary-color);
            transform: scale(1.03);
            z-index: 10;
            height: 540px; /* Slightly taller */
        }
        
        .plan-card.popular:hover {
            transform: scale(1.03) translateY(-8px);
        }

        .popular-tag {
            background: var(--primary-color);
            color: white;
            padding: 5px 40px;
            font-size: 0.75rem;
            font-weight: 700;
            position: absolute;
            top: 20px;
            right: -35px;
            transform: rotate(45deg);
            text-transform: uppercase;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .plan-name {
            font-family: 'Outfit', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .price {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-color);
            line-height: 1;
            margin-bottom: 5px;
        }

        .billing-text {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-bottom: 25px;
            font-weight: 500;
        }

        .features {
            list-style: none;
            margin-bottom: 20px;
            text-align: left;
            flex-grow: 1;
            padding: 0 10px;
        }

        .features li {
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text-color);
            font-size: 0.95rem;
        }

        .features li i { 
            color: var(--success); 
            font-size: 14px;
            flex-shrink: 0;
        }
        
        .features li.unavailable { 
            color: #94a3b8; 
            text-decoration: none; 
            opacity: 0.7;
        }
        .features li.unavailable i { 
            color: #cbd5e1; 
        }

        .btn-plan {
            display: block;
            width: 100%;
            padding: 14px;
            background: var(--primary-color);
            color: white;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            font-size: 1rem;
            box-shadow: 0 4px 6px -1px rgba(15, 44, 89, 0.2);
        }

        .btn-plan:hover {
            background: #1A3C6B;
            box-shadow: 0 10px 15px -3px rgba(15, 44, 89, 0.3);
            transform: translateY(-1px);
        }

        .btn-outline {
            background: white;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            box-shadow: none;
        }

        .btn-outline:hover {
            background: #f8fafc;
            color: #1A3C6B;
            border-color: #1A3C6B;
        }

        .btn-disabled {
            background: #f1f5f9;
            color: #94a3b8;
            cursor: default;
            box-shadow: none;
            border: 1px solid #e2e8f0;
        }
        .btn-disabled:hover {
            background: #f1f5f9;
            transform: none;
            box-shadow: none;
        }

        /* Warning Banner */
        .warning-banner {
            background: linear-gradient(135deg, #f59e0b 0%, #dc2626 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .warning-banner h3 {
            margin: 0 0 10px 0;
            font-size: 1.3rem;
        }
        
        .warning-banner p {
            margin: 0;
            opacity: 0.95;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.6);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 20px;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            position: relative;
        }
        
        .modal-header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .modal-header h2 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .modal-body {
            margin-bottom: 25px;
        }
        
        .modal-body p {
            line-height: 1.6;
            color: var(--text-color);
            margin-bottom: 15px;
        }
        
        .agreement-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .agreement-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .agreement-checkbox label {
            cursor: pointer;
            font-size: 0.95rem;
        }
        
        .modal-footer {
            display: flex;
            gap: 15px;
        }
        
        .modal-btn {
            flex: 1;
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-cancel {
            background: #e2e8f0;
            color: var(--text-color);
        }
        
        .btn-cancel:hover {
            background: #cbd5e1;
        }
        
        .btn-confirm {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-confirm:hover {
            background: #1A3C6B;
        }
        
        .btn-confirm:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
            opacity: 0.5;
        }

        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Unlock Your Full Potential</h1>
        <p class="subtitle">Choose the plan that fits your fitness journey.</p>

        <?php if (!$canSwitch && $currentRole !== 'free'): ?>
        <div class="warning-banner">
            <h3><i class="fas fa-exclamation-triangle"></i> Plan Switch Restricted</h3>
            <p>You can switch plans in <?php echo $daysUntilSwitch; ?> days (on <?php echo date('F j, Y', strtotime($subscriptionEnd)); ?>)</p>
            <p style="font-size: 0.9rem; margin-top: 10px;">No refunds are available for early plan switching. Please wait until your current subscription period ends.</p>
        </div>
        <?php endif; ?>

        <div class="toggle-container">
            <span class="toggle-label">6 Months</span>
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
                <?php else:
                    if ($currentRole === 'free') $backLink = 'freeuser_dashboard.php';
                    elseif ($currentRole === 'lite') $backLink = 'liteuser_dashboard.php';
                    elseif ($currentRole === 'pro') $backLink = 'prouser_dashboard.php';
                ?>
                    <a href="<?php echo $backLink; ?>" class="btn-plan btn-outline">Switch to Basic</a>
                <?php endif; ?>
            </div>

            <!-- Lite Plan (Formerly Pro Spot, New Lite Details) -->
            <div class="plan-card popular">
                <div class="popular-tag">Best Value</div>
                <div class="plan-name">Lite Member</div>
                <div class="price" id="proPrice">₹4,999</div>
                <p class="billing-text" id="proBilling">per 6 months</p>
                <ul class="features">
                    <li><i class="fas fa-check"></i> Unlimited Workouts</li>
                    <li><i class="fas fa-check"></i> Custom Training Plans</li>
                    <li><i class="fas fa-check"></i> Advanced Progress Tracking</li>
                    <li><i class="fas fa-check"></i> Nutrition Guide</li>
                    <li><i class="fas fa-check"></i> Priority Support</li>
                    <li class="unavailable"><i class="fas fa-times"></i> 1-on-1 Coaching</li>
                </ul>
                <?php if ($currentRole === 'lite'): ?>
                    <a href="#" class="btn-plan btn-disabled">Current Plan</a>
                <?php else: ?>
                    <a href="#" class="btn-plan" id="btnLite">Get Started</a>
                <?php endif; ?>
            </div>

            <!-- Pro Plan (Formerly Elite Spot, New Pro Details) -->
            <!-- Pro Plan (Formerly Elite Spot, New Pro Details) -->
            <div class="plan-card">
                <div class="plan-name">Pro</div>
                <div class="price" id="elitePrice">₹8,999</div>
                <p class="billing-text" id="eliteBilling">per 6 months</p>
                <ul class="features">
                    <li><i class="fas fa-check"></i> Everything in Lite</li>
                    <li><i class="fas fa-check"></i> Dedicated Personal Coach</li>
                    <li><i class="fas fa-check"></i> Weekly Video Check-ins</li>
                    <li><i class="fas fa-check"></i> Personalized Meal Plans</li>
                    <li><i class="fas fa-check"></i> Live 1-on-1 Classes</li>
                    <li><i class="fas fa-check"></i> Exclusive FitShop Discounts</li>
                </ul>
                <?php if ($currentRole === 'pro'): ?>
                    <a href="#" class="btn-plan btn-disabled">Current Plan</a>
                <?php else: ?>
                    <a href="#" class="btn-plan" id="btnProHigh">Go Pro</a>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Plan Switch Agreement Modal -->
    <div id="switchModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>⚠️ Plan Switch Agreement</h2>
            </div>
            <div class="modal-body">
                <p><strong>IMPORTANT:</strong> Before switching your subscription plan, please read and agree to the following terms:</p>
                
                <ul style="line-height: 1.8; padding-left: 20px; color: #64748b;">
                    <li>Your new plan will start immediately upon payment</li>
                    <li><strong>No refunds will be provided</strong> for any unused portion of your current subscription</li>
                    <li>Your subscription period is <strong><?php echo $currentRole === 'lite' || $currentRole === 'pro' ? '6 months' : '1 month'; ?></strong></li>
                    <li>You cannot switch plans again until the subscription period ends</li>
                    <li>All plan features will be activated after successful payment</li>
                </ul>
                
                <div class="agreement-checkbox">
                    <input type="checkbox" id="agreeCheckbox" />
                    <label for="agreeCheckbox">I understand and agree to the no-refund policy and subscription terms</label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="modal-btn btn-cancel" onclick="closeModal()">Cancel</button>
                <button class="modal-btn btn-confirm" id="confirmBtn" disabled onclick="proceedToPayment()">Confirm & Continue</button>
            </div>
        </div>
    </div>

    <script>
        // PHP values
        const canSwitch = <?php echo $canSwitch ? 'true' : 'false'; ?>;
        const currentRole = '<?php echo $currentRole; ?>';
        const daysUntilSwitch = <?php echo $daysUntilSwitch; ?>;
        
        // Capture trainer ID if present
        const urlParams = new URLSearchParams(window.location.search);
        const trainerId = urlParams.get('trainer_id');

        // Select elements
        const billingToggle = document.getElementById('billingToggle');
        const proPrice = document.getElementById('proPrice');
        const elitePrice = document.getElementById('elitePrice');
        const proBilling = document.getElementById('proBilling');
        const eliteBilling = document.getElementById('eliteBilling');
        const btnLite = document.getElementById('btnLite');
        const btnProHigh = document.getElementById('btnProHigh');
        const switchModal = document.getElementById('switchModal');
        const agreeCheckbox = document.getElementById('agreeCheckbox');
        const confirmBtn = document.getElementById('confirmBtn');

        let isYearly = false;
        let pendingPlan = null;
        let pendingBilling = null;

        // Enable/disable confirm button based on checkbox
        if (agreeCheckbox) {
            agreeCheckbox.addEventListener('change', function() {
                confirmBtn.disabled = !this.checked;
            });
        }

        // Modal functions
        function showModal(plan, billing) {
            if (!canSwitch && currentRole !== 'free') {
                alert(`You cannot switch plans yet. Please wait ${daysUntilSwitch} more days until your current subscription period ends.\n\nNo refunds are available for early switching.`);
                return false;
            }
            
            pendingPlan = plan;
            pendingBilling = billing;
            switchModal.style.display = 'block';
            return true;
        }

        function closeModal() {
            switchModal.style.display = 'none';
            agreeCheckbox.checked = false;
            confirmBtn.disabled = true;
            pendingPlan = null;
            pendingBilling = null;
        }

        function proceedToPayment() {
            if (!agreeCheckbox.checked) {
                alert('Please agree to the terms before continuing.');
                return;
            }
            
            let redirectUrl = `payment.php?plan=${pendingPlan}&billing=${pendingBilling}`;
            if (trainerId) redirectUrl += `&trainer_id=${trainerId}`;
            window.location.href = redirectUrl;
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == switchModal) {
                closeModal();
            }
        }

        if (billingToggle) {
            billingToggle.addEventListener('change', function () {
                isYearly = this.checked;
                if (isYearly) {
                    // Yearly (approx 20% off)
                    if (proPrice) proPrice.innerText = '₹7,999';
                    if (elitePrice) elitePrice.innerText = '₹8,999';
                    if (proBilling) proBilling.innerText = 'per year (save 20%)';
                    if (eliteBilling) eliteBilling.innerText = 'per year (save 20%)';
                } else {
                    // 6 months
                    if (proPrice) proPrice.innerText = '₹4,999';
                    if (elitePrice) elitePrice.innerText = '₹8,999';
                    if (proBilling) proBilling.innerText = 'per 6 months';
                    if (eliteBilling) eliteBilling.innerText = 'per 6 months';
                }
            });
        }

        if (btnLite) {
            btnLite.addEventListener('click', (e) => {
                e.preventDefault();
                const billing = isYearly ? 'yearly' : '6months';
                showModal('lite', billing);
            });
        }

        if (btnProHigh) {
            btnProHigh.addEventListener('click', (e) => {
                e.preventDefault();
                const billing = isYearly ? 'yearly' : '6months';
                showModal('pro', billing);
            });
        }
    </script>
</body>

</html>
