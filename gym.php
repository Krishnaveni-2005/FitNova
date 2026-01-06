<?php
session_start();
// gym.php - Gym Equipment & Trainer Availability
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Status - FitNova</title>
    <meta name="description" content="Check real-time equipment availability and trainer status at FitNova physical gyms. Plan your workout efficiently with our live tracking system.">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0F2C59;
            --accent-color: #4FACFE;
            --success-color: #00DBDE;
            --warning-color: #FAD02E;
            --danger-color: #FF4E50;
            --text-dark: #1A1A1A;
            --text-light: #555;
            --bg-light: #F8F9FA;
            --white: #FFFFFF;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --font-main: 'Outfit', sans-serif;
            --glass: rgba(255, 255, 255, 0.8);
            --shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: var(--font-main); }
        body { background-color: var(--bg-light); color: var(--text-dark); overflow-x: hidden; line-height: 1.6; }
        a { text-decoration: none; color: inherit; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

        /* Navbar */
        .navbar { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); padding: 20px 0; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .nav-container { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 28px; font-weight: 900; color: var(--primary-color); letter-spacing: -0.5px; }
        .nav-links { display: flex; align-items: center; gap: 40px; }
        .nav-link { font-weight: 500; font-size: 0.95rem; color: var(--text-dark); transition: var(--transition); position: relative; }
        .nav-link::after { content: ''; position: absolute; bottom: -5px; left: 0; width: 0; height: 2px; background: var(--accent-color); transition: var(--transition); }
        .nav-link:hover::after { width: 100%; }
        .nav-link.active::after { width: 100%; }
        .btn-signup { background: var(--primary-color); color: white; padding: 10px 25px; border-radius: 50px; font-weight: 600; font-size: 0.9rem; transition: var(--transition); }
        .btn-signup:hover { background: #0a1f40; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(15, 44, 89, 0.3); }

        /* Hero Section */
        .gym-hero {
            background: linear-gradient(rgba(15, 44, 89, 0.8), rgba(15, 44, 89, 0.8)), url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&q=80&w=2000');
            background-size: cover;
            background-position: center;
            padding: 100px 0;
            color: white;
            text-align: center;
            margin-bottom: 60px;
        }
        .gym-hero h1 { font-size: 3.5rem; font-weight: 900; margin-bottom: 15px; }
        .gym-hero p { font-size: 1.2rem; opacity: 0.9; max-width: 700px; margin: 0 auto; }

        /* Section Styling */
        .section-header { margin-bottom: 40px; display: flex; align-items: center; justify-content: space-between; }
        .section-header h2 { font-size: 2rem; font-weight: 800; color: var(--primary-color); }
        .status-legend { display: flex; gap: 15px; }
        .legend-item { display: flex; align-items: center; gap: 8px; font-size: 0.85rem; font-weight: 600; }
        .dot { width: 10px; height: 10px; border-radius: 50%; }
        .dot.available { background: var(--success-color); box-shadow: 0 0 10px var(--success-color); }
        .dot.busy { background: var(--warning-color); box-shadow: 0 0 10px var(--warning-color); }
        .dot.unavailable { background: var(--danger-color); box-shadow: 0 0 10px var(--danger-color); }

        /* Grid Layouts */
        .gym-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px; margin-bottom: 80px; }

        /* Equipment Card */
        .equip-card {
            background: var(--white);
            border-radius: 24px;
            padding: 25px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
        }
        .equip-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .equip-icon {
            width: 60px;
            height: 60px;
            background: rgba(79, 172, 254, 0.1);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            color: var(--accent-color);
            font-size: 1.5rem;
        }
        .equip-card h3 { font-size: 1.25rem; font-weight: 700; margin-bottom: 10px; }
        .equip-info { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 0.9rem; color: var(--text-light); }
        .progress-container { height: 8px; background: #eee; border-radius: 10px; overflow: hidden; margin-bottom: 10px; }
        .progress-bar { height: 100%; border-radius: 10px; transition: width 1s ease-in-out; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .status-badge.available { background: rgba(0, 219, 222, 0.1); color: var(--success-color); }
        .status-badge.busy { background: rgba(250, 208, 46, 0.1); color: #b7950b; }
        .status-badge.unavailable { background: rgba(255, 78, 80, 0.1); color: var(--danger-color); }

        /* Trainer Card */
        .trainer-card {
            background: var(--white);
            border-radius: 24px;
            padding: 0;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            text-align: center;
        }
        .trainer-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .trainer-img-wrapper { position: relative; height: 200px; }
        .trainer-img { width: 100%; height: 100%; object-fit: cover; }
        .trainer-status-indicator {
            position: absolute;
            bottom: 15px;
            right: 15px;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            border: 3px solid white;
        }
        .trainer-content { padding: 25px; }
        .trainer-content h3 { font-size: 1.3rem; margin-bottom: 5px; }
        .trainer-specialty { color: var(--accent-color); font-weight: 600; font-size: 0.9rem; margin-bottom: 15px; display: block; }
        .trainer-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; border-top: 1px solid #eee; padding-top: 15px; }
        .stat-item span { display: block; font-size: 0.75rem; color: var(--text-light); }
        .stat-item strong { font-size: 1rem; color: var(--primary-color); }

        /* Live Animation */
        .live-pulse {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: var(--danger-color);
            border-radius: 50%;
            margin-right: 8px;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 78, 80, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(255, 78, 80, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 78, 80, 0); }
        }

        /* Footer */
        .footer { background: #111; color: white; padding: 60px 0 30px; margin-top: 100px; }
        .footer-bottom { text-align: center; color: #666; font-size: 0.9rem; padding-top: 30px; border-top: 1px solid #333; }

        @media (max-width: 768px) {
            .gym-hero h1 { font-size: 2.5rem; }
            .section-header { flex-direction: column; align-items: flex-start; gap: 15px; }
            .nav-links { display: none; }
        }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <!-- Hero -->
    <header class="gym-hero">
        <div class="container">
            <h1>Physical Gym Status</h1>
            <p>Check real-time equipment availability and trainer schedules before you head out. Stay efficient with your workout time.</p>
        </div>
    </header>

    <main class="container">
        
        <!-- Equipment Status -->
        <section>
            <div class="section-header">
                <h2><span class="live-pulse"></span>Equipment Availability</h2>
                <div class="status-legend">
                    <div class="legend-item"><div class="dot available"></div> Available</div>
                    <div class="legend-item"><div class="dot busy"></div> In Use</div>
                    <div class="legend-item"><div class="dot unavailable"></div> Maintenance</div>
                </div>
            </div>

            <div class="gym-grid">
                <!-- Card 1: Treadmills -->
                <div class="equip-card">
                    <div class="equip-icon"><i class="fas fa-running"></i></div>
                    <h3>Treadmills</h3>
                    <div class="equip-info">
                        <span>Total Units: 12</span>
                        <strong>8 Available</strong>
                    </div>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 66%; background: var(--success-color);"></div>
                    </div>
                    <span class="status-badge available">High Availability</span>
                </div>

                <!-- Card 2: Dumbbells -->
                <div class="equip-card">
                    <div class="equip-icon"><i class="fas fa-dumbbell"></i></div>
                    <h3>Free Weights</h3>
                    <div class="equip-info">
                        <span>Sets: 20</span>
                        <strong>4 Available</strong>
                    </div>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 20%; background: var(--warning-color);"></div>
                    </div>
                    <span class="status-badge busy">Busy Session</span>
                </div>

                <!-- Card 3: Bench Press -->
                <div class="equip-card">
                    <div class="equip-icon"><i class="fas fa-weight-hanging"></i></div>
                    <h3>Bench Press</h3>
                    <div class="equip-info">
                        <span>Stations: 5</span>
                        <strong>0 Available</strong>
                    </div>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 0%; background: var(--danger-color);"></div>
                    </div>
                    <span class="status-badge unavailable">Full Capacity</span>
                </div>

                <!-- Card 4: Squat Racks -->
                <div class="equip-card">
                    <div class="equip-icon"><i class="fas fa-child"></i></div>
                    <h3>Squat Racks</h3>
                    <div class="equip-info">
                        <span>Racks: 4</span>
                        <strong>2 Available</strong>
                    </div>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 50%; background: var(--warning-color);"></div>
                    </div>
                    <span class="status-badge busy">Moderate</span>
                </div>

                <!-- Card 5: Rowing Machines -->
                <div class="equip-card">
                    <div class="equip-icon"><i class="fas fa-water"></i></div>
                    <h3>Rowing Machines</h3>
                    <div class="equip-info">
                        <span>Units: 6</span>
                        <strong>6 Available</strong>
                    </div>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 100%; background: var(--success-color);"></div>
                    </div>
                    <span class="status-badge available">Open</span>
                </div>

                <!-- Card 6: Lat Pulldown -->
                <div class="equip-card">
                    <div class="equip-icon"><i class="fas fa-level-down-alt"></i></div>
                    <h3>Lat Pulldown</h3>
                    <div class="equip-info">
                        <span>Stations: 3</span>
                        <strong>1 Available</strong>
                    </div>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 33%; background: var(--warning-color);"></div>
                    </div>
                    <span class="status-badge busy">Limited</span>
                </div>
            </div>
        </section>

        <!-- Trainer Status -->
        <section>
            <div class="section-header">
                <h2>Our Trainers On-Site</h2>
                <p style="color: var(--text-light);">Current shift availability</p>
            </div>

            <div class="gym-grid">
                <!-- Trainer 1 -->
                <div class="trainer-card">
                    <div class="trainer-img-wrapper">
                        <img src="https://images.unsplash.com/photo-1594381898411-846e7d193883?auto=format&fit=crop&q=80&w=400" alt="Trainer" class="trainer-img">
                        <div class="trainer-status-indicator" style="background: var(--success-color);"></div>
                    </div>
                    <div class="trainer-content">
                        <h3>John Wickern</h3>
                        <span class="trainer-specialty">Strength & Conditioning</span>
                        <div class="trainer-stats">
                            <div class="stat-item"><span>Status</span><strong>Available</strong></div>
                            <div class="stat-item"><span>Exp.</span><strong>8 Years</strong></div>
                        </div>
                    </div>
                </div>

                <!-- Trainer 2 -->
                <div class="trainer-card">
                    <div class="trainer-img-wrapper">
                        <img src="https://images.unsplash.com/photo-1548690312-e3b507d17a12?auto=format&fit=crop&q=80&w=400" alt="Trainer" class="trainer-img">
                        <div class="trainer-status-indicator" style="background: var(--warning-color);"></div>
                    </div>
                    <div class="trainer-content">
                        <h3>Elena Rodriguez</h3>
                        <span class="trainer-specialty">Yoga & Flexibility</span>
                        <div class="trainer-stats">
                            <div class="stat-item"><span>Status</span><strong>In Session</strong></div>
                            <div class="stat-item"><span>Exp.</span><strong>5 Years</strong></div>
                        </div>
                    </div>
                </div>

                <!-- Trainer 3 -->
                <div class="trainer-card">
                    <div class="trainer-img-wrapper">
                        <img src="https://images.unsplash.com/photo-1567013127542-490d757e51fc?auto=format&fit=crop&q=80&w=400" alt="Trainer" class="trainer-img">
                        <div class="trainer-status-indicator" style="background: var(--success-color);"></div>
                    </div>
                    <div class="trainer-content">
                        <h3>Marcus Chen</h3>
                        <span class="trainer-specialty">Bodybuilding</span>
                        <div class="trainer-stats">
                            <div class="stat-item"><span>Status</span><strong>Available</strong></div>
                            <div class="stat-item"><span>Exp.</span><strong>12 Years</strong></div>
                        </div>
                    </div>
                </div>

                <!-- Trainer 4 -->
                <div class="trainer-card">
                    <div class="trainer-img-wrapper">
                        <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?auto=format&fit=crop&q=80&w=400" alt="Trainer" class="trainer-img">
                        <div class="trainer-status-indicator" style="background: var(--danger-color);"></div>
                    </div>
                    <div class="trainer-content">
                        <h3>Sarah Smith</h3>
                        <span class="trainer-specialty">HIIT & Cardio</span>
                        <div class="trainer-stats">
                            <div class="stat-item"><span>Status</span><strong>Offline</strong></div>
                            <div class="stat-item"><span>Exp.</span><strong>6 Years</strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <?php include 'footer.php'; ?>

</body>
</html>
