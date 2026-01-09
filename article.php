<?php
session_start();

$articles = [
    1 => [
        'title' => 'Why Rest Days Are Crucial for Muscle Growth',
        'category' => 'Training',
        'image' => 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&q=80&w=1200',
        'content' => '
            <p>In our quest for strength and hypertrophy, we often focus intensely on the workout itself—lifting heavier, running faster, and pushing harder. However, the magic of muscle growth doesn\'t actually happen in the gym. It happens when you\'re resting.</p>
            
            <h3>The Science of Hypertrophy</h3>
            <p>When you lift weights, you are essentially causing microscopic tears in your muscle fibers. This is a normal and necessary part of the process. Your body perceives this damage and initiates a repair process, fusing muscle fibers together to form new protein strands (myofibrils). These repaired myofibrils increase in thickness and number to create muscle hypertrophy (growth).</p>
            
            <h3>Why Skipping Rest Stalls Progress</h3>
            <p>If you don\'t give your body enough time to repair this damage, you enter a state of "overtraining." Instead of growing, your muscles remain in a catabolic (breakdown) state. This leads to:</p>
            <ul>
                <li>Decreased performance and strength</li>
                <li>Chronic fatigue and irritability</li>
                <li>Increased risk of injury</li>
                <li>Hormonal imbalances (elevated cortisol)</li>
            </ul>
            
            <h3>How to Rest Properly</h3>
            <p>Rest days don\'t necessarily mean sitting on the couch all day. Active recovery is often better than passive rest. Activities like light walking, stretching, or yoga can increase blood flow to muscles without placing them under heavy strain, accelerating the repair process.</p>
            
            <p><strong>Conclusion:</strong> Respect the rest. It is just as important as the reps.</p>
        ',
        'date' => 'Oct 12, 2025'
    ],
    2 => [
        'title' => 'The Truth About Intermittent Fasting',
        'category' => 'Nutrition',
        'image' => 'https://images.unsplash.com/photo-1498837167922-ddd27525d352?auto=format&fit=crop&q=80&w=1200',
        'content' => '
            <p>Intermittent Fasting (IF) has become one of the most popular health trends in recent years, but is it magic? Or just another way to manage calorie intake?</p>
            
            <h3>What is Intermittent Fasting?</h3>
            <p>IF is not a diet in the conventional sense but rather an eating pattern. It doesn’t specify *what* you should eat, but rather *when* you should eat. The most common method is the 16/8 protocol, which involves fasting for 16 hours and restricting your daily eating period to 8 hours.</p>
            
            <h3>The Benefits</h3>
            <p>Research suggests several powerful benefits:</p>
            <ul>
                <li><strong>Weight Loss:</strong> By shrinking the eating window, many people naturally consume fewer calories.</li>
                <li><strong>Insulin Sensitivity:</strong> Fasting can lower insulin levels, making stored body fat more accessible for burning.</li>
                <li><strong>Cellular Repair:</strong> Fasting triggers autophagy, a process where cells remove old and dysfunctional proteins.</li>
            </ul>
            
            <h3>Is it for everyone?</h3>
            <p>Not necessarily. While safe for most healthy adults, it may not be suitable for those with a history of eating disorders, pregnant women, or individuals with certain medical conditions. The best diet is always the one you can stick to consistently.</p>
        ',
        'date' => 'Nov 05, 2025'
    ],
    3 => [
        'title' => 'Incorporating Mindfulness Into Your Routine',
        'category' => 'Wellness',
        'image' => 'https://images.unsplash.com/photo-1545205597-3d9d02c29597?auto=format&fit=crop&q=80&w=1200',
        'content' => '
            <p>In a world that never slows down, our minds are constantly racing. Physical fitness is crucial, but mental fitness is the foundation upon which true health is built.</p>
            
            <h3>What is Mindfulness?</h3>
            <p>Mindfulness is simply the practice of being fully present and engaged in the moment, aware of your thoughts and feelings without distraction or judgment. It’s about tuning in, not tuning out.</p>
            
            <h3>Simple Ways to Start</h3>
            <p>You don\'t need to meditate for an hour to be mindful. Try these micro-habits:</p>
            <ol>
                <li><strong>Mindful Breathing:</strong> Take one minute to focus solely on your breath. Inhale for 4 seconds, hold for 4, exhale for 4.</li>
                <li><strong>Mindful Eating:</strong> Put your phone away during meals. Notice the texture and taste of your food.</li>
                <li><strong>Body Scan:</strong> Spend 2 minutes scanning your body from head to toe, noticing any areas of tension and consciously releasing them.</li>
            </ol>
            
            <p>Regular mindfulness practice has been shown to reduce stress, lower blood pressure, and improve sleep quality.</p>
        ',
        'date' => 'Dec 20, 2025'
    ],
    4 => [
        'title' => 'HIIT vs LISS: Which is Better for Fat Loss?',
        'category' => 'Cardio',
        'image' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?auto=format&fit=crop&q=80&w=1200',
        'content' => '
            <p>The debate between High-Intensity Interval Training (HIIT) and Low-Intensity Steady State (LISS) cardio is ongoing. Both have merits, but which one burns more fat?</p>
            
            <h3>Understanding HIIT</h3>
            <p>HIIT involves short bursts of intense effort followed by recovery periods. It raises your metabolic rate for hours after the workout, a phenomenon known as EPOC (Excess Post-exercise Oxygen Consumption).</p>
            
            <h3>Understanding LISS</h3>
            <p>LISS involves maintaining a consistent, moderate effort (like a brisk walk or jog) for a longer duration. It is less taxing on the CNS (Central Nervous System) and burns a higher percentage of calories from fat during the session itself.</p>
            
            <h3> The Verdict</h3>
            <p>There is no "better" exercise. HIIT is time-efficient but taxing. LISS is easier to recover from but takes longer. A balanced program usually incorporates both: intense days to push limits, and easy days to foster recovery while remaining active.</p>
        ',
        'date' => 'Jan 02, 2026'
    ],
    5 => [
        'title' => '5-Minute Desk Mobility Routine',
        'category' => 'Mobility',
        'image' => 'https://images.unsplash.com/photo-1512438248247-f0f2a5a8b7f0?auto=format&fit=crop&q=80&w=1200',
        'content' => '
            <p>Sitting is often called "the new smoking." A sedentary lifestyle tightens our hip flexors, rounds our shoulders, and weakens our core. Combat the desk hunch with this 5-minute routine.</p>
            
            <h3>The Routine</h3>
            <ul>
                <li><strong>Neck Rotations:</strong> Slowly roll your head in circles. (1 minute)</li>
                <li><strong>Seated Thoracic Twist:</strong> Twist your torso to the left and right, holding the back of your chair. (1 minute)</li>
                <li><strong>Hip Flexor Stretch:</strong> Stand up, lunge forward with one leg, and push your hips forward. (1 minute per side)</li>
                <li><strong>Overhead Reach:</strong> Interlace fingers and push palms to the sky. (1 minute)</li>
            </ul>
            
            <p>Do this every few hours to maintain posture and energy levels.</p>
        ',
        'date' => 'Jan 05, 2026'
    ],
    6 => [
        'title' => 'The Science of Sleep & Performance',
        'category' => 'Recovery',
        'image' => 'https://images.unsplash.com/photo-1511988617509-a57c8a288659?auto=format&fit=crop&q=80&w=1200',
        'content' => '
            <p>You can have the best training program in the world, but if you aren\'t sleeping, you aren\'t growing.</p>
            
            <h3>Hormonal Harmony</h3>
            <p>Sleep is when your body releases the majority of its Growth Hormone (GH) and Testosterone, both critical for tissue repair. Simultaneously, sleep deprivation increases cortisol, a stress hormone that can lead to muscle breakdown and fat gain.</p>
            
            <h3>Cognitive Performance</h3>
            <p>Lack of sleep impairs reaction time, decision making, and motivation. For athletes, this means poorer technique and lower intensity during workouts.</p>
            
            <p><strong>Tip:</strong> Aim for 7-9 hours of quality sleep. Keep your room cool, dark, and quiet.</p>
        ',
        'date' => 'Jan 07, 2026'
    ],
    7 => [
        'title' => 'Hydration Myths Debunked',
        'category' => 'Nutrition',
        'image' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&q=80&w=1200',
        'content' => '
            <p>Water is life. But how much do we really need? The "8 glasses a day" rule is a good starting point, but it lacks nuance.</p>
            
            <h3>Myth: Everyone needs the same amount.</h3>
            <p><strong>Fact:</strong> Needs vary wildly based on activity level, climate, and body size. An athlete in summer needs far more than an office worker in winter.</p>
            
            <h3>Myth: Thirst is a bad indicator.</h3>
            <p><strong>Fact:</strong> For most people, thirst is a reliable signal. However, during intense exercise, you may need to drink proactively to prevent performance decline.</p>
            
            <p>Check your urine color. Pale yellow is ideal. Clear means you might be overhydrated; dark mean drink up!</p>
        ',
        'date' => 'Jan 08, 2026'
    ]
];

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$article = $articles[$id] ?? null;

if (!$article) {
    header("Location: learn.php");
    exit();
}

include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($article['title']); ?> - FitNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-color: #0F2C59; --accent-color: #4FACFE; }
        body { background: #F8F9FA; font-family: 'Outfit', sans-serif; color: #333; }
        
        .article-hero {
            height: 400px;
            background: url('<?php echo $article['image']; ?>') center/cover;
            position: relative;
        }
        
        .article-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.8) 100%);
        }
        
        .hero-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 60px 0;
            color: white;
            text-align: center;
            z-index: 2;
        }
        
        .a-tag {
            background: var(--accent-color);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .hero-content h1 {
            font-size: 3rem;
            margin: 20px 0 10px;
            line-height: 1.2;
            padding: 0 20px;
        }
        
        .a-date { opacity: 0.8; font-size: 1rem; }
        
        .content-container {
            max-width: 1200px;
            margin: -60px auto 60px;
            background: white;
            border-radius: 20px;
            padding: 60px;
            position: relative;
            z-index: 3;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            font-size: 1.15rem;
            line-height: 1.8;
            color: #4a4a4a;
        }
        
        .content-container h3 {
            color: var(--primary-color);
            margin: 40px 0 20px;
            font-size: 1.8rem;
        }
        
        .content-container p { margin-bottom: 25px; }
        .content-container ul, .content-container ol { margin-bottom: 25px; padding-left: 20px; }
        .content-container li { margin-bottom: 10px; }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: white;
            font-weight: 500;
            margin-bottom: 20px;
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 30px;
            backdrop-filter: blur(5px);
            transition: 0.3s;
        }
        
        .back-link:hover { background: rgba(255,255,255,0.3); }

        @media(max-width: 768px) {
            .hero-content h1 { font-size: 2rem; }
            .content-container { padding: 30px; margin-top: -40px; }
        }

        .share-menu {
            display: none;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .share-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            text-decoration: none;
            transition: transform 0.2s;
            border: none;
            cursor: pointer;
        }

        .share-btn:hover { transform: scale(1.1); }
        .share-wa { background: #25D366; }
        .share-ig { background: #C13584; }
    </style>
</head>
<body>
    <div class="article-hero">
        <div class="container hero-content">
            <span class="a-tag"><?php echo htmlspecialchars($article['category']); ?></span>
            <h1><?php echo htmlspecialchars($article['title']); ?></h1>
            <span class="a-date"><i class="far fa-calendar-alt"></i> <?php echo $article['date']; ?></span>
        </div>
    </div>
    
    <div class="content-container">
        <?php echo $article['content']; ?>
        
        <div style="margin-top: 50px; padding-top: 30px; border-top: 1px solid #eee; text-align: center;">
            <p>Did you find this helpful?</p>
            <button onclick="toggleShare()" class="back-link" style="background: var(--primary-color); color: white; border: none; cursor: pointer; font-family: inherit; font-size: 1rem;">Share this Article</button>
            
            <div id="shareMenu" class="share-menu" style="display: none;">
                <a href="#" onclick="shareWhatsApp(); return false;" class="share-btn share-wa" title="Share on WhatsApp">
                    <i class="fab fa-whatsapp"></i>
                </a>
                <a href="#" onclick="shareInstagram(); return false;" class="share-btn share-ig" title="Share on Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
            </div>
        </div>
    </div>
    
    <script>
        function toggleShare() {
            var menu = document.getElementById('shareMenu');
            menu.style.display = (menu.style.display === 'flex') ? 'none' : 'flex';
        }

        function shareWhatsApp() {
            var url = encodeURIComponent(window.location.href);
            var text = encodeURIComponent("Check out this article on FitNova: <?php echo addslashes($article['title']); ?>\n");
            window.open('https://api.whatsapp.com/send?text=' + text + url, '_blank');
        }

        function shareInstagram() {
            // Instagram doesn't allow direct web sharing via URL.
            // Copy link and open Instagram
            var url = window.location.href;
            navigator.clipboard.writeText(url).then(function() {
                alert('Link copied to clipboard! Opening Instagram...');
                window.open('https://www.instagram.com/', '_blank');
            }, function() {
                alert('Could not copy link. Please manually copy the URL.');
                window.open('https://www.instagram.com/', '_blank');
            });
        }
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>
