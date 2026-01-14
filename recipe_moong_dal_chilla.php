<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moong Dal Chilla Recipe - FitNova</title>
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
        .recipe-header { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.08); margin-bottom: 30px; }
        .recipe-image { 
            width: 100%; 
            height: 450px; 
            background-image: url('https://images.unsplash.com/photo-1630383249896-424e482df921?auto=format&fit=crop&q=80&w=1200'); 
            background-size: cover; 
            background-position: center;
            position: relative;
            display: flex;
            align-items: flex-end;
        }
        .recipe-image::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60%;
            background: linear-gradient(to top, rgba(15, 44, 89, 0.95), transparent);
        }
        .recipe-title-overlay {
            position: relative;
            z-index: 1;
            color: white;
            padding: 40px;
            width: 100%;
        }
        .recipe-title-overlay h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 3rem;
            margin: 0 0 10px 0;
            font-weight: 900;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .recipe-title-overlay p {
            font-size: 1.2rem;
            margin: 0;
            opacity: 0.95;
            font-weight: 500;
        }
        .recipe-meta { display: flex; gap: 30px; padding: 25px; background: #f8f9fa; }
        .meta-item { display: flex; align-items: center; gap: 8px; color: var(--text-light); }
        .meta-item i { color: var(--accent-color); font-size: 1.2rem; }
        .recipe-content { background: white; border-radius: 16px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        h2 { font-family: 'Outfit', sans-serif; color: var(--primary-color); margin-bottom: 20px; }
        .ingredients-list { list-style: none; padding: 0; }
        .ingredients-list li { padding: 12px 0; border-bottom: 1px solid #eee; display: flex; gap: 12px; align-items: center; }
        .ingredients-list li i { color: var(--accent-color); }
        .instructions { line-height: 1.8; color: var(--text-color); }
        .instructions ol { padding-left: 20px; }
        .instructions li { margin-bottom: 15px; }
        .nutrition-info { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 12px; margin-top: 30px; }
        .nutrition-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 20px; margin-top: 15px; }
        .nutrition-item { text-align: center; }
        .nutrition-item .value { font-size: 1.5rem; font-weight: 700; }
        .nutrition-item .label { font-size: 0.85rem; opacity: 0.9; }
        .back-btn { display: inline-block; margin-bottom: 20px; padding: 12px 24px; background: var(--primary-color); color: white; text-decoration: none; border-radius: 8px; transition: 0.3s; }
        .back-btn:hover { background: #1a3d7a; transform: translateY(-2px); }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <div class="recipe-header">
            <div class="recipe-image">
                <div class="recipe-title-overlay">
                    <h1>Moong Dal Chilla</h1>
                    <p>High-Protein Savory Pancake</p>
                </div>
            </div>
            <div class="recipe-meta">
                <div class="meta-item"><i class="far fa-clock"></i> <strong>25 mins</strong></div>
                <div class="meta-item"><i class="fas fa-fire"></i> <strong>180 kcal</strong></div>
                <div class="meta-item"><i class="fas fa-utensils"></i> <strong>2 servings</strong></div>
                <div class="meta-item"><i class="fas fa-signal"></i> <strong>Easy</strong></div>
            </div>
        </div>

        <div class="recipe-content">
            <h2>About This Recipe</h2>
            <p>Moong Dal Chilla is a nutritious, high-protein savory pancake made from green gram (moong dal). It's loaded with vegetables and makes for a perfect post-workout meal. Rich in protein and fiber, this dish supports muscle recovery and keeps you energized throughout the day.</p>

            <h2>Ingredients</h2>
            <ul class="ingredients-list">
                <li><i class="fas fa-check-circle"></i> 1 cup split green moong dal (soaked 4-6 hours)</li>
                <li><i class="fas fa-check-circle"></i> 1 small onion, finely chopped</li>
                <li><i class="fas fa-check-circle"></i> 1 tomato, finely chopped</li>
                <li><i class="fas fa-check-circle"></i> 1-2 green chilies, chopped</li>
                <li><i class="fas fa-check-circle"></i> 1/4 cup grated carrot</li>
                <li><i class="fas fa-check-circle"></i> 2 tbsp fresh coriander, chopped</li>
                <li><i class="fas fa-check-circle"></i> 1/2 tsp cumin seeds</li>
                <li><i class="fas fa-check-circle"></i> 1/4 tsp turmeric powder</li>
                <li><i class="fas fa-check-circle"></i> Salt to taste</li>
                <li><i class="fas fa-check-circle"></i> 2-3 tbsp water (for batter consistency)</li>
                <li><i class="fas fa-check-circle"></i> 1 tsp oil (for cooking each chilla)</li>
            </ul>

            <h2>Instructions</h2>
            <div class="instructions">
                <ol>
                    <li><strong>Prepare the Batter:</strong> Drain the soaked moong dal and blend it into a smooth paste with 2-3 tbsp water. The batter should be slightly thick but pourable.</li>
                    <li><strong>Mix Ingredients:</strong> Transfer the batter to a bowl. Add chopped onions, tomatoes, green chilies, grated carrot, coriander, cumin seeds, turmeric powder, and salt. Mix well.</li>
                    <li><strong>Heat the Pan:</strong> Heat a non-stick pan or tawa on medium heat. Lightly grease with oil.</li>
                    <li><strong>Pour and Spread:</strong> Pour a ladleful of batter onto the pan and spread it in a circular motion to make a thin pancake.</li>
                    <li><strong>Cook:</strong> Drizzle a few drops of oil around the edges. Cook for 2-3 minutes until the bottom turns golden brown.</li>
                    <li><strong>Flip and Finish:</strong> Flip carefully and cook the other side for 2 minutes until golden and crispy.</li>
                    <li><strong>Serve:</strong> Serve hot with mint chutney, yogurt, or tomato ketchup.</li>
                </ol>
            </div>

            <div class="nutrition-info">
                <h2 style="color: white; margin-bottom: 10px;">Nutritional Information (Per Serving)</h2>
                <div class="nutrition-grid">
                    <div class="nutrition-item">
                        <div class="value">180</div>
                        <div class="label">Calories</div>
                    </div>
                    <div class="nutrition-item">
                        <div class="value">12g</div>
                        <div class="label">Protein</div>
                    </div>
                    <div class="nutrition-item">
                        <div class="value">28g</div>
                        <div class="label">Carbs</div>
                    </div>
                    <div class="nutrition-item">
                        <div class="value">3g</div>
                        <div class="label">Fat</div>
                    </div>
                    <div class="nutrition-item">
                        <div class="value">6g</div>
                        <div class="label">Fiber</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
