<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Learn - Fitness Articles & Tips - FitNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-color: #0F2C59; --accent-color: #4FACFE; }
        body { background: #F8F9FA; font-family: 'Outfit', sans-serif; }
        .hero { padding: 80px 0; background: linear-gradient(135deg, #2b32b2 0%, #1488cc 100%); color: white; text-align: center; }
        .container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
        
        .articles-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 15px; }
        .article-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: 0.3s; display: flex; flex-direction: column; }
        .article-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .a-img { width: 100%; height: 150px; object-fit: cover; }
        .a-content { padding: 15px; display: flex; flex-direction: column; flex: 1; }
        .a-tag { display: inline-block; padding: 3px 8px; background: rgba(79, 172, 254, 0.1); color: var(--accent-color); border-radius: 4px; font-size: 0.7rem; font-weight: 700; margin-bottom: 8px; align-self: flex-start; }
        .a-title { font-size: 1.05rem; font-weight: 700; color: #333; margin-bottom: 6px; line-height: 1.3; }
        .a-desc { font-size: 0.85rem; color: #666; margin-bottom: 12px; line-height: 1.5; flex-grow: 1; }
        .read-more { color: var(--primary-color); font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 5px; font-size: 0.8rem; margin-top: auto; }
        
        @media(max-width: 768px) { .articles-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="hero">
        <div class="container" style="padding:0;">
            <h1>Knowledge Hub</h1>
            <p>Expert insights, wellness tips, and the latest in fitness science.</p>
        </div>
    </div>

    <div class="container">
        <div class="articles-grid">
            <!-- Article 1 -->
            <div class="article-card">
                <img src="https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&q=80&w=600" class="a-img">
                <div class="a-content">
                    <span class="a-tag">Training</span>
                    <h3 class="a-title">Why Rest Days Are Crucial for Muscle Growth</h3>
                    <p class="a-desc">Overtraining can stall your progress. Learn why taking time off helps you come back stronger.</p>
                    <a href="article.php?id=1" class="read-more">Read Article <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

            <!-- Article 2 -->
            <div class="article-card">
                <img src="https://images.unsplash.com/photo-1498837167922-ddd27525d352?auto=format&fit=crop&q=80&w=600" class="a-img">
                <div class="a-content">
                    <span class="a-tag">Nutrition</span>
                    <h3 class="a-title">The Truth About Intermittent Fasting</h3>
                    <p class="a-desc">Is it just a trend or a sustainable lifestyle? We break down the science and benefits.</p>
                    <a href="article.php?id=2" class="read-more">Read Article <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

            <!-- Article 3 -->
            <div class="article-card">
                <img src="https://images.unsplash.com/photo-1545205597-3d9d02c29597?auto=format&fit=crop&q=80&w=600" class="a-img">
                <div class="a-content">
                    <span class="a-tag">Wellness</span>
                    <h3 class="a-title">Incorporating Mindfulness Into Your Routine</h3>
                    <p class="a-desc">Mental fitness is just as important as physical. Simple tips to stay grounded.</p>
                    <a href="article.php?id=3" class="read-more">Read Article <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

             <!-- Article 4 -->
             <div class="article-card">
                <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?auto=format&fit=crop&q=80&w=600" class="a-img">
                <div class="a-content">
                    <span class="a-tag">Cardio</span>
                    <h3 class="a-title">HIIT vs LISS: Which is Better for Fat Loss?</h3>
                    <p class="a-desc">Comparing High Intensity Interval Training with Low Intensity Steady State cardio.</p>
                    <a href="article.php?id=4" class="read-more">Read Article <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

            <!-- Article 5 -->
            <div class="article-card">
                <img src="https://images.unsplash.com/photo-1512438248247-f0f2a5a8b7f0?auto=format&fit=crop&q=80&w=600" class="a-img">
                <div class="a-content">
                    <span class="a-tag">Mobility</span>
                    <h3 class="a-title">5-Minute Desk Mobility Routine</h3>
                    <p class="a-desc">Combat sitting all day with these simple, effective stretches you can do anywhere.</p>
                    <a href="article.php?id=5" class="read-more">Read Article <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

            <!-- Article 6 -->
            <div class="article-card">
                <img src="https://images.unsplash.com/photo-1511988617509-a57c8a288659?auto=format&fit=crop&q=80&w=600" class="a-img">
                <div class="a-content">
                    <span class="a-tag">Recovery</span>
                    <h3 class="a-title">The Science of Sleep & Performance</h3>
                    <p class="a-desc">Why getting 8 hours isn't just a luxury—it's your most powerful performance enhancer.</p>
                    <a href="article.php?id=6" class="read-more">Read Article <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

            <!-- Article 7 -->
            <div class="article-card">
                <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&q=80&w=600" class="a-img">
                <div class="a-content">
                    <span class="a-tag">Nutrition</span>
                    <h3 class="a-title">Hydration Myths Debunked</h3>
                    <p class="a-desc">Do you really need 8 glasses a day? We separate fact from fiction regarding water intake.</p>
                    <a href="article.php?id=7" class="read-more">Read Article <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
