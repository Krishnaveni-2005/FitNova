<?php session_start(); include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitness & Nutrition - FitNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0F2C59;
            --accent-color: #4FACFE;
            --text-dark: #1A1A1A;
            --bg-light: #F8F9FA;
        }
        body { background-color: var(--bg-light); font-family: 'Outfit', sans-serif; }
        .hero {
            background: linear-gradient(135deg, #0F2C59 0%, #00d2ff 100%);
            color: white; padding: 80px 0; text-align: center; margin-bottom: 40px;
        }
        .hero h1 { font-size: 3rem; margin-bottom: 10px; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        
        /* Tabs */
        .hub-tabs { display: flex; justify-content: center; gap: 20px; margin-bottom: 50px; }
        .hub-tab { 
            padding: 15px 40px; border-radius: 50px; border: none; font-size: 1.1rem; font-weight: 700; cursor: pointer;
            background: white; color: var(--text-dark); box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: 0.3s;
        }
        .hub-tab.active { background: var(--primary-color); color: white; transform: scale(1.05); }

        /* Content Sections */
        .content-section { display: none; animation: fadeIn 0.5s; }
        .content-section.active { display: block; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .section-title { font-size: 2rem; color: var(--primary-color); margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 10px; }

        /* Grids */
        .data-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; margin-bottom: 60px; }
        .data-card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.05); transition: 0.3s; }
        .data-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        .data-img { height: 200px; background-size: cover; background-position: center; }
        .data-info { padding: 25px; }
        .data-info h3 { font-size: 1.3rem; margin-bottom: 10px; color: var(--primary-color); }
        .data-info p { color: #666; font-size: 0.95rem; line-height: 1.5; margin-bottom: 15px; }
        .btn-link { color: var(--accent-color); font-weight: 600; text-decoration: none; }
    </style>
</head>
<body>
    <div class="hero">
        <div class="container">
            <h1>Fitness & Nutrition Hub</h1>
            <p>Your complete guide to workouts and healthy eating</p>
        </div>
    </div>

    <div class="container">
        <div class="hub-tabs">
            <button class="hub-tab active" onclick="switchSection('nutrition')">Nutrition & Foods</button>
            <button class="hub-tab" onclick="switchSection('workouts')">Workout Details</button>
        </div>

        <!-- Nutrition Section -->
        <div id="nutrition" class="content-section active">
            <h2 class="section-title">Healthy Nutrition Foods</h2>
            <div class="data-grid">
                <!-- Recipe/Food 1 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Superfood Salad Bowl</h3>
                        <p>Packed with kale, quinoa, avocado, and lean protein sources. Ideal for recovery.</p>
                        <a href="healthy_recipes.php" class="btn-link">View Full Recipe <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Recipe/Food 2 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1598103442097-8b74394b95c6?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>High-Protein grilled Chicken</h3>
                        <p>Grilled chicken breast with steamed broccoli and sweet potatoes.</p>
                        <a href="healthy_recipes.php" class="btn-link">View Full Recipe <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Recipe/Food 3 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1610348725531-843dff563e2c?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Green Detox Smoothie</h3>
                        <p>Spinach, apple, kiwi, and ginger blend for a refreshing energy boost.</p>
                        <a href="healthy_recipes.php" class="btn-link">View Full Recipe <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Recipe/Food 4 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Quinoa & Veggie Power Bowl</h3>
                        <p>A nutrient-dense bowl with quinoa, chickpeas, roasted veggies, and tahini dressing.</p>
                        <a href="healthy_recipes.php" class="btn-link">View Full Recipe <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Recipe/Food 5 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1467003909585-2f8a72700288?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Grilled Salmon with Asparagus</h3>
                        <p>Rich in Omega-3s, this simple grilled salmon dish is perfect for a healthy dinner.</p>
                        <a href="healthy_recipes.php" class="btn-link">View Full Recipe <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Recipe/Food 6 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Berry & Yogurt Parfait</h3>
                        <p>Layers of greek yogurt, granola, and fresh mixed berries for a protein-packed sweet treat.</p>
                        <a href="healthy_recipes.php" class="btn-link">View Full Recipe <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Recipe/Food 7 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1541519227354-08fa5d50c44d?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Avocado Toast with Egg</h3>
                        <p>Whole grain toast topped with ripe avocado and a poached egg for healthy fats.</p>
                        <a href="healthy_recipes.php" class="btn-link">View Full Recipe <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Recipe/Food 8 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1623428187969-5da2dcea5ebf?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Lean Turkey Wrap</h3>
                        <p>Turkey slices, lettuce, and hummus in a whole wheat wrap for a quick lunch.</p>
                        <a href="healthy_recipes.php" class="btn-link">View Full Recipe <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Recipe/Food 9 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1587080266227-677cc2a4e76e?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Oatmeal with Almonds</h3>
                        <p>Warm oatmeal topped with almond slices and banana for slow-release energy.</p>
                        <a href="healthy_recipes.php" class="btn-link">View Full Recipe <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                </div>
            </div>
            
            <div style="text-align: center; margin-bottom: 40px;">
                <a href="healthy_recipes.php" class="hub-tab" style="display:inline-block; background: var(--primary-color); color:white;">See All Recipes</a>
            </div>
</div>

        <!-- Workouts Section -->
        <div id="workouts" class="content-section">
            <h2 class="section-title">Workout Plans & Details</h2>
            <div class="data-grid">
                <!-- Workout 1 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Full Body HIIT</h3>
                        <p>High intensity interval training to burn fat and build endurance in 20 mins.</p>
                        <a href="free_workouts.php" class="btn-link">Start Workout <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Workout 2 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Strength Training 101</h3>
                        <p>Fundamental compound movements for building maximize muscle strength.</p>
                        <a href="free_workouts.php" class="btn-link">Start Workout <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Workout 3 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1549576490-b0b4831ef60a?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Yoga for Flexibility</h3>
                        <p>Relaxing flow to improve mobility, reduce stress, and prevent injury.</p>
                        <a href="free_workouts.php" class="btn-link">Start Workout <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin-bottom: 40px;">
                <a href="free_workouts.php" class="hub-tab" style="display:inline-block; background: var(--primary-color); color:white;">See All Workouts</a>
            </div>
        </div>
    </div>

    <script>
        function switchSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active'));
            document.querySelectorAll('.hub-tab').forEach(tab => tab.classList.remove('active'));

            // Show target
            document.getElementById(sectionId).classList.add('active');
            
            // Highlight button
            // Simple logic assuming order: 0 is nutrition, 1 is workouts
            if(sectionId === 'nutrition') document.getElementsByClassName('hub-tab')[0].classList.add('active');
            else document.getElementsByClassName('hub-tab')[1].classList.add('active');
        }
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>

