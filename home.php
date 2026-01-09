<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitNova - Health & Wellness Ecosystem</title>
    <meta name="description" content="FitNova is a comprehensive health and wellness ecosystem offering expert guidance, curated fitness equipment, and personalized training plans.">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0F2C59; /* Dark Blue */
            --accent-color: #4FACFE; /* Light Blue */
            --text-dark: #1A1A1A;
            --text-light: #555;
            --bg-light: #F8F9FA;
            --white: #FFFFFF;
            --transition: all 0.3s ease;
            --font-main: 'Outfit', sans-serif;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: var(--font-main); }
        body { background-color: var(--bg-light); color: var(--text-dark); overflow-x: hidden; }
        a { text-decoration: none; color: inherit; }
        ul { list-style: none; }
        
        /* Layout */
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        
        /* Navbar */
        .navbar { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); padding: 20px 0; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .nav-container { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 28px; font-weight: 900; color: var(--primary-color); letter-spacing: -0.5px; }
        .nav-links { display: flex; align-items: center; gap: 40px; }
        .nav-link { font-weight: 500; font-size: 0.95rem; color: var(--text-dark); transition: var(--transition); }
        .nav-link:hover { color: var(--primary-color); }
        .btn-signup { background: var(--primary-color); color: white; padding: 10px 25px; border-radius: 50px; font-weight: 600; font-size: 0.9rem; transition: var(--transition); }
        .btn-signup:hover { background: #0a1f40; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(15, 44, 89, 0.3); }

        /* Hero */
        .hero { position: relative; padding: 120px 0 220px; text-align: center; background: linear-gradient(135deg, #eef2f3 0%, #dfe9f3 100%); overflow: hidden; }
        /* Placeholder background image matching usage context */
        .hero::before {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&q=80&w=2000') center/cover no-repeat;
            opacity: 0.15; pointer-events: none;
        }
        .hero-tag { display: inline-block; font-size: 0.8rem; font-weight: 700; letter-spacing: 2px; color: #5B7C99; margin-bottom: 20px; text-transform: uppercase; background: rgba(255,255,255,0.7); padding: 5px 15px; border-radius: 20px; backdrop-filter: blur(5px); }
        .hero-title { font-size: 56px; font-weight: 800; line-height: 1.2; margin-bottom: 20px; color: var(--text-dark); max-width: 900px; margin-left: auto; margin-right: auto; position: relative; }
        .hero-subtitle { font-size: 1.1rem; color: var(--text-light); margin-bottom: 0px; font-weight: 400; position: relative; }

        /* Hero Cards (Floating) */
        .hero-cards-section { margin-top: -160px; padding-bottom: 80px; position: relative; z-index: 10; }
        .hero-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; }
        
        .h-card { 
            background: white; 
            border-radius: 20px; 
            overflow: hidden; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.08); 
            transition: var(--transition); 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            text-align: center; 
            padding-bottom: 30px; 
            height: 480px; /* Keep height for consistency */
        }
        
        .h-card:hover { transform: translateY(-10px); box-shadow: 0 30px 60px rgba(0,0,0,0.12); }
        .h-card img { width: 100%; height: 240px; object-fit: cover; margin-bottom: 25px; }
        .h-card h3 { font-size: 1.4rem; font-weight: 700; margin-bottom: 10px; color: var(--text-dark); }
        .h-card p { font-size: 0.95rem; color: var(--text-light); margin-bottom: 25px; padding: 0 25px; line-height: 1.6; }
        
        .btn-view { display: inline-block; background: var(--text-dark); color: white; padding: 10px 30px; border-radius: 50px; font-size: 0.9rem; font-weight: 600; transition: var(--transition); margin-top: auto; }
        .btn-view:hover { background: var(--primary-color); transform: translateY(-2px); }

        /* Features */
        .why-us { padding: 60px 0 100px; background: white; }
        .section-title { text-align: center; font-size: 36px; font-weight: 800; margin-bottom: 60px; color: var(--text-dark); }
        .features-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 40px; }
        .f-card { display: flex; flex-direction: column; align-items: flex-start; padding: 40px; background: white; border-radius: 20px; border: 1px solid #eee; transition: var(--transition); box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
        .f-card:hover { box-shadow: 0 20px 50px rgba(0,0,0,0.08); transform: translateY(-5px); border-color: transparent; }
        .f-card i { font-size: 2.5rem; margin-bottom: 25px; }
        .f-card h4 { font-size: 1.5rem; font-weight: 700; margin-bottom: 15px; color: var(--text-dark); }
        .f-card p { color: var(--text-light); font-size: 1rem; line-height: 1.6; }
        .blue-icon { color: #4FACFE; }
        .green-icon { color: #2ecc71; }
        .yellow-icon { color: #f1c40f; }
        .red-icon { color: #e74c3c; }

        /* Testimonials */
        .testimonials { padding: 100px 0; background-color: #F8F9FA; }
        .t-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; }
        .t-card { background: white; padding: 40px 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); transition: var(--transition); border: 1px solid #f0f0f0; }
        .t-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.06); }
        .stars { color: #FFD700; margin-bottom: 20px; font-size: 0.9rem; letter-spacing: 2px; }
        .t-text { font-style: italic; margin-bottom: 30px; color: var(--text-dark); font-size: 1rem; line-height: 1.6; }
        .t-author { display: flex; align-items: center; gap: 15px; border-top: 1px solid #eee; padding-top: 20px; width: 100%; }
        .t-author img { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; }
        .t-author h5 { font-size: 1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 2px; }
        .t-author p { font-size: 0.85rem; color: var(--text-light); font-weight: 500; }

        /* Footer */
        .footer { background: #111; color: white; padding: 100px 0 30px; }
        .footer-top { display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 60px; padding-bottom: 60px; border-bottom: 1px solid #333; }
        .f-logo { font-size: 32px; font-weight: 900; margin-bottom: 25px; color: white; }
        .f-desc { color: #999; font-size: 1rem; margin-bottom: 30px; line-height: 1.6; max-width: 350px; }
        .f-socials { display: flex; gap: 15px; }
        .f-socials a { width: 40px; height: 40px; background: #222; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: var(--transition); color: white; }
        .f-socials a:hover { background: var(--accent-color); transform: translateY(-3px); }
        .footer-top h4 { margin-bottom: 30px; font-size: 1.2rem; font-weight: 700; color: white; }
        .f-links li { margin-bottom: 18px; }
        .f-links a { color: #999; font-size: 0.95rem; transition: var(--transition); }
        .f-links a:hover { color: white; padding-left: 5px; }
        .footer-bottom { padding-top: 40px; text-align: center; color: #666; font-size: 0.9rem; }

        @media (max-width: 992px) {
            .hero-title { font-size: 36px; }
            .hero-cards { grid-template-columns: 1fr; max-width: 500px; margin: 0 auto; }
            .features-grid { grid-template-columns: 1fr; }
            .t-grid { grid-template-columns: 1fr; }
            .footer-top { grid-template-columns: 1fr; text-align: center; }
            .f-desc { margin: 0 auto 30px; }
            .f-socials { justify-content: center; }
            .h-card img { height: 250px; }
        }
    </style>
</head>
<body>

    <?php $isHomePage = true; include 'header.php'; ?>

    <!-- Hero -->
    <header class="hero">
        <div class="container">
            <span class="hero-tag">HEALTH & WELLNESS ECOSYSTEM</span>
            <h1 class="hero-title">Your perfect coach is just a few steps away</h1>
            <p class="hero-subtitle">Find the ideal coach for your goals & interests</p>
        </div>
    </header>

    <!-- Cards Section -->
    <section class="container hero-cards-section">
        <div class="hero-cards">
            <!-- Card 1 -->
            <div class="h-card">
                <img src="https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&q=80&w=800" alt="Fitness & Nutrition">
                <h3>Fitness & Nutrition</h3>
                <p>Unlock your dream body with the right diet & workouts.</p>
                <a href="fitness_nutrition.php" class="btn-view">View</a>
            </div>
            <!-- Card 2 -->
            <div class="h-card">
                <img src="https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?auto=format&fit=crop&q=80&w=800" alt="Online Personal Training">
                <h3>Online Personal Training</h3>
                <p>1-on-1 online sessions for Yoga, Zumba & more!</p>
                <a href="trainers.php" class="btn-view">View</a>
            </div>
            <!-- Card 3 -->
            <div class="h-card">
                <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&q=80&w=800" alt="Learn">
                <h3>Learn</h3>
                <p>Discover fitness articles, insights, and wellness tips.</p>
                <a href="learn.php" class="btn-view">View</a>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="why-us">
        <div class="container">
            <h2 class="section-title">Why Choose FitNova?</h2>
            <div class="features-grid">
                <div class="f-card">
                    <i class="fas fa-users blue-icon"></i>
                    <h4>Expert Guidance</h4>
                    <p>Connect with certified trainers, nutritionists, and physiotherapists who provide personalized guidance based on your fitness goals and needs.</p>
                </div>
                <div class="f-card">
                    <i class="fas fa-store green-icon"></i>
                    <h4>Curated Fitshop</h4>
                    <p>Access our exclusive marketplace with premium fitness equipment, supplements, and wellness products, all carefully selected by our experts.</p>
                </div>
                <div class="f-card">
                    <i class="fas fa-graduation-cap yellow-icon"></i>
                    <h4>Learn & Grow</h4>
                    <p>Expand your fitness knowledge with our educational resources, articles, and video tutorials covering various aspects of health and wellness.</p>
                </div>
                <div class="f-card">
                    <i class="fas fa-mobile-alt red-icon"></i>
                    <h4>All-in-One Platform</h4>
                    <p>Everything you need for your fitness journey in one place - from workout tracking to nutrition planning and community support.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials">
        <div class="container">
            <h2 class="section-title">Real Stories, Real Results</h2>
            <div class="t-grid">
                <div class="t-card">
                    <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                    <p class="t-text">"With my hectic routine, I thought fitness was impossible. But this program fit perfectly into my lifestyle. I've lost 20 lbs, gained confidence!"</p>
                    <div class="t-author">
                        <img src="sara.jpg" onerror="this.src='https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&q=80&w=150'" alt="Sarah">
                        <div>
                            <h5>Sarah Johnson</h5>
                            <p>Transformation: Weight Loss</p>
                        </div>
                    </div>
                </div>
                <div class="t-card">
                    <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                    <p class="t-text">"My online trainer fixed my squat form over video calls! It's like having a coach in my living room. Pure gold."</p>
                    <div class="t-author">
                        <img src="assets/david.jpg" onerror="this.src='https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=150'" alt="David">
                        <div>
                            <h5>David Kim</h5>
                            <p>Transformation: Strength</p>
                        </div>
                    </div>
                </div>
                <div class="t-card">
                    <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                    <p class="t-text">"The certification courses are incredibly detailed. I went from fitness enthusiast to certified nutritionist in 6 months."</p>
                    <div class="t-author">
                        <img src="emily.jpg" onerror="this.src='https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&q=80&w=150'" alt="Emily">
                        <div>
                            <h5>Emily Chen</h5>
                            <p>Transformation: Career</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>


</body>
</html>
