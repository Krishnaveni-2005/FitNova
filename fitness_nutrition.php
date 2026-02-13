<?php session_start(); ?>
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
            background: linear-gradient(135deg, #0F2C59 0%, #1565C0 100%);
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
        .data-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 15px; margin-bottom: 60px; }
        .data-card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.05); transition: 0.3s; }
        .data-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        .data-img { height: 160px; background-size: cover; background-position: center; }
        .data-info { padding: 20px; }
        .data-info h3 { font-size: 1.1rem; margin-bottom: 10px; color: var(--primary-color); }
        .data-info p { color: #666; font-size: 0.9rem; line-height: 1.4; margin-bottom: 15px; }
        .btn-link { color: var(--accent-color); font-weight: 600; text-decoration: none; font-size: 0.9rem; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
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
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1630383249896-424e482df921?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Moong Dal Chilla</h3>
                        <p>High-protein savory pancake made with green gram, loaded with veggies. Perfect post-workout meal.</p>
                        <a href="recipe_moong_dal_chilla.php" class="btn-link">View Full Recipe <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Recipe/Food 2 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1599487488170-d11ec9c172f0?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Tandoori Chicken Protein Bowl</h3>
                        <p>Lean tandoori chicken with mint chutney, cucumber raita and brown rice. High in protein.</p>
                        <a href="recipe_tandoori_chicken.php" class="btn-link">View Full Recipe <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Recipe/Food 3 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1631452180519-c014fe946bc7?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Palak Paneer Power Bowl</h3>
                        <p>Iron-rich spinach curry with protein-packed cottage cheese. Nutrient-dense and delicious.</p>
                        <a href="recipe_palak_paneer.php" class="btn-link">View Full Recipe <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Recipe/Food 4 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1588166524941-3bf61a9c41db?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Masala Oats Bowl</h3>
                        <p>Savory Indian-spiced oats with vegetables. Low-calorie, high-fiber breakfast for energy.</p>
                        <a href="recipe_masala_oats.php" class="btn-link">View Full Recipe <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Recipe/Food 5 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1546833999-b9f581a1996d?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Dal Tadka with Brown Rice</h3>
                        <p>Protein-rich lentils tempered with spices, served with fiber-packed brown rice.</p>
                        <a href="recipe_dal_tadka.php" class="btn-link">View Full Recipe <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Recipe/Food 6 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1623428187969-5da2dcea5ebf?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Mixed Sprouts Salad</h3>
                        <p>Crunchy protein-packed sprouts with tomato, onion, lemon and chaat masala. Fresh and filling.</p>
                        <a href="recipe_sprouts_salad.php" class="btn-link">View Full Recipe <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Recipe/Food 7 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1668236543090-82eba5ee5976?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Ragi Dosa with Coconut Chutney</h3>
                        <p>Finger millet crepe rich in calcium and iron. Gluten-free and perfect for weight management.</p>
                        <a href="recipe_ragi_dosa.php" class="btn-link">View Full Recipe <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Recipe/Food 8 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1567188040759-fb8a883dc6d8?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Paneer Tikka Protein Platter</h3>
                        <p>Grilled cottage cheese marinated in yogurt and spices. High protein, low carb snack.</p>
                        <a href="recipe_paneer_tikka.php" class="btn-link">View Full Recipe <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Recipe/Food 9 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1606491956689-2ea866880c84?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Besan Chilla (Gram Flour Pancake)</h3>
                        <p>Savory chickpea flour pancake loaded with onions and tomatoes. High in protein and fiber.</p>
                        <a href="recipe_besan_chilla.php" class="btn-link">View Full Recipe <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
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
                        <a href="workout_full_body_hiit.php" class="btn-link">Start Workout <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Workout 2 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Strength Training 101</h3>
                        <p>Fundamental compound movements for building maximize muscle strength.</p>
                        <a href="workout_strength_training.php" class="btn-link">Start Workout <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Workout 3 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1549576490-b0b4831ef60a?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Yoga for Flexibility</h3>
                        <p>Relaxing flow to improve mobility, reduce stress, and prevent injury.</p>
                        <a href="workout_yoga_flexibility.php" class="btn-link">Start Workout <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Workout 4 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Core Strength Training</h3>
                        <p>Target your abs and core muscles with effective exercises for stability and power.</p>
                        <a href="workout_core_strength.php" class="btn-link">Start Workout <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Workout 5 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1476480862126-209bfaa8edc8?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Cardio Blast</h3>
                        <p>High-energy cardio session to boost stamina and burn maximum calories in 30 mins.</p>
                        <a href="workout_cardio_blast.php" class="btn-link">Start Workout <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Workout 6 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1574680096145-d05b474e2155?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Upper Body Power</h3>
                        <p>Build strength in chest, back, shoulders, and arms with targeted resistance training.</p>
                        <a href="workout_upper_body.php" class="btn-link">Start Workout <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Workout 7 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Lower Body Sculpt</h3>
                        <p>Tone and strengthen legs, glutes, and hamstrings for powerful lower body development.</p>
                        <a href="workout_lower_body.php" class="btn-link">Start Workout <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Workout 8 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Functional Fitness</h3>
                        <p>Real-world movements to improve daily performance, balance, and overall athleticism.</p>
                        <a href="workout_functional.php" class="btn-link">Start Workout <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <!-- Workout 9 -->
                <div class="data-card">
                    <div class="data-img" style="background-image: url('https://images.unsplash.com/photo-1599901860904-17e6ed7083a0?auto=format&fit=crop&q=80&w=600');"></div>
                    <div class="data-info">
                        <h3>Pilates Core Flow</h3>
                        <p>Low-impact exercises focusing on core control, posture, and muscle tone.</p>
                        <a href="workout_pilates.php" class="btn-link">Start Workout <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
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

        // Check for view parameter on load
        document.addEventListener('DOMContentLoaded', () => {
            const params = new URLSearchParams(window.location.search);
            const view = params.get('view');
            if (view === 'workouts') {
                switchSection('workouts');
            } else if (view === 'nutrition') {
                switchSection('nutrition');
            }
        });
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>

