<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - FitNova</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #0F2C59;
            --secondary-color: #3498DB;
            --accent-color: #E63946;
            --text-dark: #1A1A1A;
            --text-gray: #555;
            --bg-light: #f4f7f6;
            --border-color: #E2E8F0;
            --transition: all 0.3s ease;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
        }

        h1, h2, h3, h4 {
            font-family: 'Outfit', sans-serif;
            color: var(--primary-color);
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1a3a6e 100%);
            padding: 40px 20px 60px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0) 70%);
            animation: pulse 15s infinite linear;
        }
        
        @keyframes pulse {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
            color: white;
            text-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .hero-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            font-weight: 300;
        }

        /* Layout */
        .layout-container {
            max-width: 95%; /* Widened from 1200px to spread content and push sidebar left */
            margin: -60px auto 80px;
            position: relative;
            z-index: 10;
            display: grid;
            grid-template-columns: 240px 1fr;
            gap: 40px;
            padding: 0 20px;
        }

        /* Sidebar Navigation */
        .sidebar {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
            position: sticky;
            top: 100px;
            height: fit-content;
            border: 1px solid rgba(255,255,255,0.5);
        }
        
        .sidebar-title {
            font-size: 1.1rem;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-color);
        }

        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-nav li {
            margin-bottom: 10px;
        }

        .sidebar-nav a {
            text-decoration: none;
            color: var(--text-gray);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .sidebar-nav a:hover, .sidebar-nav a.active {
            background: rgba(52, 152, 219, 0.1);
            color: var(--secondary-color);
            transform: translateX(5px);
        }

        /* Content Area */
        .content-area {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .policy-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            border: 1px solid rgba(0,0,0,0.03);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        
        .policy-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--secondary-color);
            opacity: 0;
            transition: var(--transition);
        }

        .policy-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.08);
        }
        
        .policy-card:hover::before {
            opacity: 1;
        }

        .policy-card h2 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .icon-wrapper {
            width: 50px;
            height: 50px;
            background: rgba(52, 152, 219, 0.1);
            color: var(--secondary-color);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .policy-card p {
            color: var(--text-gray);
            font-size: 1.05rem;
            line-height: 1.8;
            margin-bottom: 15px;
        }

        .data-list {
            list-style: none;
            padding: 0;
            margin-top: 20px;
        }

        .data-list li {
            position: relative;
            padding-left: 30px;
            margin-bottom: 15px;
            color: var(--text-gray);
            font-size: 1.05rem;
        }

        .data-list li::before {
            content: '\f00c';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            left: 0;
            top: 2px;
            color: var(--secondary-color);
        }

        .contact-box {
            background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
            padding: 30px;
            border-radius: 15px;
            margin-top: 30px;
            border: 1px dashed #ccc;
            text-align: center;
        }

        .contact-box p {
            margin: 0;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .contact-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 25px;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: var(--transition);
        }
        
        .contact-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .last-updated {
            text-align: center;
            color: #888;
            font-size: 1rem;
            margin-top: 20px;
            font-weight: 500;
        }

        @media (max-width: 992px) {
            .layout-container {
                grid-template-columns: 1fr;
            }
            .sidebar {
                display: none;
            }
        }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <section class="hero-section">
        <h1 class="hero-title">Privacy Policy</h1>
        <p class="hero-subtitle">Your trust is our priority. Learn how we collect, protect, and use your data to power your fitness journey.</p>
    </section>

    <div class="layout-container">
        
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <h3 class="sidebar-title">Contents</h3>
            <ul class="sidebar-nav">
                <li><a href="#intro" class="active"><i class="fas fa-arrow-right"></i> Introduction</a></li>
                <li><a href="#collection"><i class="fas fa-arrow-right"></i> Data Collection</a></li>
                <li><a href="#usage"><i class="fas fa-arrow-right"></i> Information Usage</a></li>
                <li><a href="#sharing"><i class="fas fa-arrow-right"></i> Data Sharing</a></li>
                <li><a href="#security"><i class="fas fa-arrow-right"></i> Security Measures</a></li>
                <li><a href="#rights"><i class="fas fa-arrow-right"></i> Your Rights</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="content-area">
            
            <div id="intro" class="policy-card">
                <h2>
                    <div class="icon-wrapper"><i class="fas fa-shield-alt"></i></div>
                    1. Introduction
                </h2>
                <p>Welcome to FitNova. We are committed to protecting your personal information and your right to privacy. If you have any questions or concerns about this privacy notice, or our practices with regards to your personal information, please contact us.</p>
                <p>This Privacy Policy applies to all information collected through our website, mobile application, and related services. By using FitNova, you consent to the data practices described in this statement.</p>
            </div>

            <div id="collection" class="policy-card">
                <h2>
                    <div class="icon-wrapper"><i class="fas fa-database"></i></div>
                    2. Information We Collect
                </h2>
                <p>We collect personal information that you voluntarily provide to us when you register on the website, express an interest in obtaining information about us or our products and services.</p>
                <p>The personal information that we collect depends on the context of your interactions with us, and may include:</p>
                <ul class="data-list">
                    <li><strong>Personal Data:</strong> Name, email address, phone number, date of birth, and profile pictures.</li>
                    <li><strong>Health Metrics:</strong> Workout history, dietary preferences, weight logging, and overall fitness goals to provide personalization.</li>
                    <li><strong>Payment Information:</strong> We may collect data necessary to process your payment if you make purchases. All payment data is stored directly by our secure payment processors.</li>
                </ul>
            </div>

            <div id="usage" class="policy-card">
                <h2>
                    <div class="icon-wrapper"><i class="fas fa-chart-network"></i></div>
                    3. How We Use Your Information
                </h2>
                <p>We use personal information collected via our platform for a variety of business purposes described below. We process your personal information for these purposes in reliance on our legitimate business interests.</p>
                <ul class="data-list">
                    <li><strong>To facilitate account creation:</strong> Managing your onboarding and authentication.</li>
                    <li><strong>Personalized Experience:</strong> Your fitness data is used exclusively to generate customized workout plans and macros analysis.</li>
                    <li><strong>Order Fulfillment:</strong> Processing transactions via FitShop securely and efficiently.</li>
                </ul>
            </div>

            <div id="sharing" class="policy-card">
                <h2>
                    <div class="icon-wrapper"><i class="fas fa-share-nodes"></i></div>
                    4. Sharing Your Information
                </h2>
                <p>We only share information with your consent, to comply with laws, to provide you with services, to protect your rights, or to fulfill business obligations.</p>
                <ul class="data-list">
                    <li><strong>Personal Trainers:</strong> If you are a Pro or Elite user, relevant workout and diet metrics are shared securely with your assigned trainer.</li>
                    <li><strong>Third-Party Service Providers:</strong> We may share your data with vendors who perform services for us (e.g., Razorpay for payments).</li>
                </ul>
            </div>

            <div id="security" class="policy-card">
                <h2>
                    <div class="icon-wrapper"><i class="fas fa-lock"></i></div>
                    5. Data Security
                </h2>
                <p>We have implemented appropriate technical and organizational security measures designed to protect the security of any personal information we process. Our servers are secured and encrypted. However, please remember that we cannot guarantee that the internet itself is 100% secure.</p>
            </div>

            <div id="rights" class="policy-card">
                <h2>
                    <div class="icon-wrapper"><i class="fas fa-user-check"></i></div>
                    6. Your Privacy Rights
                </h2>
                <p>Depending on your location, you may have certain rights regarding your personal information, such as the right to request access, obtain a copy of your personal information, or request erasure.</p>
                
                <div class="contact-box">
                    <p>Want to review, update, or delete the data we collect from you?</p>
                    <a href="#" class="contact-btn" onclick="if(typeof handleTalkToExperts === 'function') handleTalkToExperts(event); else { event.preventDefault(); alert('Please open Talk with Experts from the navigation menu.'); }">Contact Our Support Team</a>
                </div>
            </div>

            <div class="last-updated">
                Last Updated: <?php echo date('F d, Y'); ?>
            </div>

        </main>
    </div>

    <script>
        // Smooth scrolling and active state for sidebar
        document.querySelectorAll('.sidebar-nav a').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelectorAll('.sidebar-nav a').forEach(a => a.classList.remove('active'));
                this.classList.add('active');
                
                const targetId = this.getAttribute('href').substring(1);
                const targetEl = document.getElementById(targetId);
                
                if(targetEl) {
                    window.scrollTo({
                        top: targetEl.offsetTop - 120,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Update active nav link on scroll
        window.addEventListener('scroll', () => {
            let current = '';
            document.querySelectorAll('.policy-card').forEach(section => {
                const sectionTop = section.offsetTop;
                if (pageYOffset >= sectionTop - 150) {
                    current = section.getAttribute('id');
                }
            });

            document.querySelectorAll('.sidebar-nav a').forEach(a => {
                a.classList.remove('active');
                if (current && a.getAttribute('href').substring(1) === current) {
                    a.classList.add('active');
                }
            });
        });
    </script>

    <?php include 'footer.php'; ?>

</body>
</html>
