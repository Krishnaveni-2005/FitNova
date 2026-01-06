<?php session_start(); include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Free Workouts - FitNova</title>
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
            --border-radius: 16px;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
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

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #0F2C59 0%, #164282 100%);
            color: white;
            padding: 80px 20px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-radius: 0 0 30px 30px;
            margin-bottom: 40px;
            box-shadow: 0 4px 20px rgba(15, 44, 89, 0.3);
        }

        .hero::before {
            content: ''; position: absolute; width: 300px; height: 300px; border-radius: 50%; background: rgba(255, 255, 255, 0.05); top: -100px; left: -50px;
        }

        .hero-title {
            font-family: 'Outfit', sans-serif;
            font-size: 2.5rem;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .hero-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto 30px;
            position: relative;
            z-index: 1;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 40px;
        }

        /* Tabs */
        .tabs-container {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
        }

        .tabs {
            display: inline-flex;
            background: white;
            padding: 5px;
            border-radius: 50px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .tab-btn {
            padding: 12px 30px;
            border: none;
            background: none;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-light);
            cursor: pointer;
            border-radius: 50px;
            transition: var(--transition);
        }

        .tab-btn.active {
            background: var(--accent-color);
            color: white;
            box-shadow: 0 4px 10px rgba(230, 57, 70, 0.3);
        }

        /* Video Grid */
        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .video-grid.active {
            display: grid;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .video-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            cursor: pointer;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .video-card:hover { transform: translateY(-8px); box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12); }

        .video-thumbnail {
            width: 100%;
            height: 200px;
            background-color: #EEE;
            position: relative;
            background-size: cover;
            background-position: center;
            overflow: hidden;
        }

        .play-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.9);
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center; justify-content: center; color: white; font-size: 1.5rem; transition: var(--transition); border: 2px solid rgba(255, 255, 255, 0.8); z-index: 2; backdrop-filter: blur(4px);
        }

        .video-card:hover .play-icon { background: var(--accent-color); border-color: var(--accent-color); transform: translate(-50%, -50%) scale(1.1); }
        .duration-badge { position: absolute; bottom: 15px; right: 15px; background: rgba(0, 0, 0, 0.7); color: white; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; z-index: 2; }

        .video-info { padding: 25px; }
        .video-title { font-family: 'Outfit', sans-serif; font-size: 1.25rem; margin-bottom: 8px; color: var(--primary-color); }
        .video-desc { font-size: 0.9rem; color: var(--text-light); margin-bottom: 20px; line-height: 1.5; }
        .video-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 15px; border-top: 1px solid #f0f0f0; }
        .trainer-info { display: flex; align-items: center; gap: 10px; }
        .trainer-avatar { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; }
        .level-tag { font-size: 0.8rem; font-weight: 700; color: var(--accent-color); }

        /* Modal */
        .modal {
            display: none;
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.9); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(10px);
        }

        .modal-content {
            width: 90%; max-width: 900px; aspect-ratio: 16/9; background: black; border-radius: 12px; overflow: hidden; position: relative;
        }

        .close-modal { position: absolute; top: 20px; right: 20px; color: white; font-size: 2rem; cursor: pointer; z-index: 2001; }

        @media (max-width: 768px) {
            .video-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>

    <div class="hero">
        <h1 class="hero-title">Free Workouts</h1>
        <p class="hero-subtitle">Curated sessions to get you moving, sweating, and smiling. No equipment needed.</p>
    </div>

    <div class="container">
        <div class="tabs-container">
            <div class="tabs">
                <button class="tab-btn active" onclick="openTab('basic')">Basic</button>
                <button class="tab-btn" onclick="openTab('intermediate')">Intermediate</button>
                <button class="tab-btn" onclick="openTab('advanced')">Advanced</button>
            </div>
        </div>

        <!-- Basic Videos -->
        <div id="basic" class="video-grid active">
            <div class="video-card" onclick="playVideo('Video 1')">
                <div class="video-thumbnail"
                    style="background-image: url('https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80')">
                    <div class="play-icon"><i class="fas fa-play"></i></div>
                    <div class="duration-badge"><i class="far fa-clock"></i> 15:00</div>
                </div>
                <div class="video-info">
                    <h3 class="video-title">15-Min Morning Warmup</h3>
                    <p class="video-desc">Start your day with energy. Gentle stretches and light cardio to wake up your body.</p>
                    <div class="video-footer">
                        <div class="trainer-info">
                            <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?ixlib=rb-4.0.3&auto=format&fit=facearea&facepad=2&w=100&q=80"
                                class="trainer-avatar" alt="Trainer">
                            <span class="trainer-name">Sarah Fit</span>
                        </div>
                        <span class="level-tag">Beginner</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- More videos would go here... -->
        <div id="intermediate" class="video-grid">
           <!-- Intermediate content -->
        </div>

        <div id="advanced" class="video-grid">
           <!-- Advanced content -->
        </div>
    </div>

    <!-- Video Player Modal -->
    <div class="modal" id="videoModal">
        <span class="close-modal" onclick="closeModal()">&times;</span>
        <div class="modal-content">
            <iframe width="100%" height="100%" src="https://www.youtube.com/embed/dQw4w9WgXcQ"
                title="YouTube video player" frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen></iframe>
        </div>
    </div>

    <script>
        function openTab(tabName) {
            document.querySelectorAll('.video-grid').forEach(Grid => Grid.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
            event.currentTarget.classList.add('active');
        }

        function playVideo(title) {
            document.getElementById('videoModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('videoModal').style.display = 'none';
        }

        window.onclick = function (event) {
            const modal = document.getElementById('videoModal');
            if (event.target == modal) closeModal();
        }
    </script>
    <?php include 'footer.php'; ?>
</body>

</html>
