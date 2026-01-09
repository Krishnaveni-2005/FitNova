<?php session_start(); include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vegetable Khichdi - FitNova</title>
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
            background-image: url('https://images.unsplash.com/photo-1596797038530-2c107229654b?auto=format&fit=crop&q=80&w=1200'); 
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
    </style>
</head>
<body>
    <div class="container">
        <div class="recipe-header">
            <div class="recipe-image">
                <div class="recipe-title-overlay">
                    <h1>Vegetable Khichdi</h1>
                    <p>One-Pot Complete Protein Comfort Food</p>
                </div>
            </div>
            <div class="recipe-meta">
                <div class="meta-item"><i class="far fa-clock"></i> <strong>30 mins</strong></div>
                <div class="meta-item"><i class="fas fa-fire"></i> <strong>280 kcal</strong></div>
                <div class="meta-item"><i class="fas fa-utensils"></i> <strong>3 servings</strong></div>
                <div class="meta-item"><i class="fas fa-signal"></i> <strong>Easy</strong></div>
            </div>
        </div>

        <div class="recipe-content">
            <h2>About This Recipe</h2>
            <p>Vegetable Khichdi is a wholesome one-pot meal combining rice, lentils, and vegetables. The rice-lentil combination provides complete protein, while vegetables add essential vitamins and minerals. This easy-to-digest comfort food is perfect for recovery days or when you need a nutritious, gentle meal.</p>

            <h2>Ingredients</h2>
            <ul class="ingredients-list">
                <li><i class="fas fa-check-circle"></i> 1/2 cup rice (white or brown)</li>
                <li><i class="fas fa-check-circle"></i> 1/2 cup yellow moong dal</li>
                <li><i class="fas fa-check-circle"></i> 1 cup mixed vegetables (carrots, peas, beans, cauliflower)</li>
                <li><i class="fas fa-check-circle"></i> 1 onion, chopped</li>
                <li><i class="fas fa-check-circle"></i> 1 tomato, chopped</li>
                <li><i class="fas fa-check-circle"></i> 1 tsp ginger, grated</li>
                <li><i class="fas fa-check-circle"></i> 1 tsp cumin seeds</li>
                <li><i class="fas fa-check-circle"></i> 1/2 tsp turmeric powder</li>
                <li><i class="fas fa-check-circle"></i> 1/2 tsp garam masala</li>
                <li><i class="fas fa-check-circle"></i> Few curry leaves</li>
                <li><i class="fas fa-check-circle"></i> 3.5 cups water</li>
                <li><i class="fas fa-check-circle"></i> 1 tbsp ghee or oil</li>
                <li><i class="fas fa-check-circle"></i> Salt to taste</li>
                <li><i class="fas fa-check-circle"></i> Fresh coriander for garnish</li>
            </ul>

            <h2>Instructions</h2>
            <div class="instructions">
                <ol>
                    <li><strong>Wash Rice & Dal:</strong> Rinse rice and moong dal together until water runs clear.</li>
                    <li><strong>Prepare Tempering:</strong> Heat ghee in a pressure cooker or pot. Add cumin seeds and let them crackle.</li>
                    <li><strong>Sauté Aromatics:</strong> Add curry leaves, grated ginger, and chopped onions. Sauté until onions turn golden.</li>
                    <li><strong>Add Vegetables:</strong> Add tomatoes and mixed vegetables. Cook for 2-3 minutes.</li>
                    <li><strong>Add Rice & Dal:</strong> Add washed rice and dal. Mix well with vegetables.</li>
                    <li><strong>Season & Cook:</strong> Add turmeric, garam masala, salt, and water. In a pressure cooker, cook for 3-4 whistles. If using a pot, cover and simmer for 20-25 minutes until rice and dal are soft.</li>
                    <li><strong>Mash Lightly:</strong> Once cooked, mash the khichdi lightly with a spoon for a creamy consistency.</li>
                    <li><strong>Serve:</strong> Garnish with fresh coriander. Serve hot with yogurt, pickle, or papad.</li>
                </ol>
            </div>

            <div class="nutrition-info">
                <h2 style="color: white; margin-bottom: 10px;">Nutritional Information (Per Serving)</h2>
                <div class="nutrition-grid">
                    <div class="nutrition-item">
                        <div class="value">280</div>
                        <div class="label">Calories</div>
                    </div>
                    <div class="nutrition-item">
                        <div class="value">12g</div>
                        <div class="label">Protein</div>
                    </div>
                    <div class="nutrition-item">
                        <div class="value">48g</div>
                        <div class="label">Carbs</div>
                    </div>
                    <div class="nutrition-item">
                        <div class="value">5g</div>
                        <div class="label">Fat</div>
                    </div>
                    <div class="nutrition-item">
                        <div class="value">7g</div>
                        <div class="label">Fiber</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
