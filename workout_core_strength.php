<?php session_start(); include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Core Strength Training - FitNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0F2C59;
            --accent-color: #3498DB;
            --bg-color: #F8F9FA;
            --text-color: #333333;
            --text-light: #6C757D;
        }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-color); color: var(--text-color); margin: 0; }
        .container { max-width: 100%; margin: 40px auto; padding: 0 40px; }
        .workout-header { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.08); margin-bottom: 30px; }
        .workout-image { 
            width: 100%; 
            height: 450px; 
            background-image: url('https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?auto=format&fit=crop&q=80&w=1200'); 
            background-size: cover; 
            background-position: center;
            position: relative;
            display: flex;
            align-items: flex-end;
        }
        .workout-image::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60%;
            background: linear-gradient(to top, rgba(15, 44, 89, 0.95), transparent);
        }
        .workout-title-overlay {
            position: relative;
            z-index: 1;
            color: white;
            padding: 40px;
            width: 100%;
        }
        .workout-title-overlay h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 3rem;
            margin: 0 0 10px 0;
            font-weight: 900;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .workout-title-overlay p {
            font-size: 1.2rem;
            margin: 0;
            opacity: 0.95;
            font-weight: 500;
        }
        .workout-meta { display: flex; gap: 30px; padding: 25px; background: #f8f9fa; flex-wrap: wrap; }
        .meta-item { display: flex; align-items: center; gap: 8px; color: var(--text-light); }
        .meta-item i { color: var(--accent-color); font-size: 1.2rem; }
        .workout-content { background: white; border-radius: 16px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        h2 { font-family: 'Outfit', sans-serif; color: var(--primary-color); margin-bottom: 20px; margin-top: 30px; }
        .exercise-list { list-style: none; padding: 0; }
        .exercise-list li { padding: 15px; margin-bottom: 10px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid var(--accent-color); }
        .exercise-list strong { color: var(--primary-color); display: block; margin-bottom: 5px; }
        .btn-start { display: inline-block; margin-top: 30px; padding: 15px 40px; background: linear-gradient(135deg, var(--accent-color), #2980b9); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: 0.3s; }
        .btn-start:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(52, 152, 219, 0.3); }
    </style>
</head>
<body>
    <div class="container">
        <div class="workout-header">
            <div class="workout-image">
                <div class="workout-title-overlay">
                    <h1>Core Strength Training</h1>
                    <p>Build a Powerful Core Foundation</p>
                </div>
            </div>
            <div class="workout-meta">
                <div class="meta-item"><i class="far fa-clock"></i> <strong>25 minutes</strong></div>
                <div class="meta-item"><i class="fas fa-fire"></i> <strong>200 kcal burned</strong></div>
                <div class="meta-item"><i class="fas fa-dumbbell"></i> <strong>No Equipment</strong></div>
                <div class="meta-item"><i class="fas fa-signal"></i> <strong>Intermediate</strong></div>
            </div>
        </div>

        <div class="workout-content">
            <h2>About This Workout</h2>
            <p>This core-focused workout targets your abs, obliques, and lower back to build stability, power, and definition. Perfect for improving athletic performance, posture, and overall functional strength.</p>

            <h2>Exercises</h2>
            <ul class="exercise-list">
                <li>
                    <strong>1. Plank Hold</strong>
                    3 sets x 45-60 seconds<br>
                    Keep your body in a straight line from head to heels. Engage your core and avoid sagging hips.
                </li>
                <li>
                    <strong>2. Russian Twists</strong>
                    3 sets x 20 reps (each side)<br>
                    Sit with knees bent, lean back slightly. Rotate torso side to side, touching the ground beside you.
                </li>
                <li>
                    <strong>3. Bicycle Crunches</strong>
                    3 sets x 15 reps (each side)<br>
                    Alternate bringing opposite elbow to knee in a cycling motion. Focus on controlled movements.
                </li>
                <li>
                    <strong>4. Mountain Climbers</strong>
                    3 sets x 20 reps (each leg)<br>
                    From plank position, drive knees toward chest alternately at a quick pace.
                </li>
                <li>
                    <strong>5. Dead Bug</strong>
                    3 sets x 12 reps (each side)<br>
                    Lie on back, extend opposite arm and leg while keeping lower back pressed to the floor.
                </li>
                <li>
                    <strong>6. Leg Raises</strong>
                    3 sets x 12 reps<br>
                    Lie flat, raise straight legs up to 90 degrees, then lower slowly without touching the floor.
                </li>
            </ul>

            <h2>Workout Tips</h2>
            <p>• Rest 30-45 seconds between sets<br>
            • Focus on form over speed<br>
            • Keep your core engaged throughout all exercises<br>
            • Breathe steadily - don't hold your breath<br>
            • Warm up with light cardio for 5 minutes before starting</p>

            <a href="fitness_nutrition.php" class="btn-start"><i class="fas fa-play-circle"></i> Back to Workouts</a>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
