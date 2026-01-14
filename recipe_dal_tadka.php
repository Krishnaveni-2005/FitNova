<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dal Tadka with Brown Rice - FitNova</title>
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
            background-image: url('https://images.unsplash.com/photo-1546833999-b9f581a1996d?auto=format&fit=crop&q=80&w=1200'); 
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
        h3 { color: var(--accent-color); font-size: 1.1rem; margin-top: 20px; }
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
    <?php include 'header.php'; ?>
    <div class="container">
        <div class="recipe-header">
            <div class="recipe-image">
                <div class="recipe-title-overlay">
                    <h1>Dal Tadka with Brown Rice</h1>
                    <p>Protein-Rich Comfort Food</p>
                </div>
            </div>
            <div class="recipe-meta">
                <div class="meta-item"><i class="far fa-clock"></i> <strong>35 mins</strong></div>
                <div class="meta-item"><i class="fas fa-fire"></i> <strong>380 kcal</strong></div>
                <div class="meta-item"><i class="fas fa-utensils"></i> <strong>3 servings</strong></div>
                <div class="meta-item"><i class="fas fa-signal"></i> <strong>Easy</strong></div>
            </div>
        </div>

        <div class="recipe-content">
            <h2>About This Recipe</h2>
            <p>Dal Tadka is a classic Indian comfort food featuring protein-rich yellow lentils tempered with aromatic spices. Served with fiber-packed brown rice, this combination provides a complete protein meal that's perfect for muscle recovery and sustained energy.</p>

            <h2>Ingredients</h2>
            <h3>For Dal:</h3>
            <ul class="ingredients-list">
                <li><i class="fas fa-check-circle"></i> 1 cup yellow lentils (toor dal), washed</li>
                <li><i class="fas fa-check-circle"></i> 3 cups water</li>
                <li><i class="fas fa-check-circle"></i> 1/2 tsp turmeric powder</li>
                <li><i class="fas fa-check-circle"></i> Salt to taste</li>
            </ul>
            <h3>For Tadka (Tempering):</h3>
            <ul class="ingredients-list">
                <li><i class="fas fa-check-circle"></i> 2 tbsp ghee or oil</li>
                <li><i class="fas fa-check-circle"></i> 1 tsp cumin seeds</li>
                <li><i class="fas fa-check-circle"></i> 4-5 garlic cloves, chopped</li>
                <li><i class="fas fa-check-circle"></i> 2 dry red chilies</li>
                <li><i class="fas fa-check-circle"></i> 1 onion, sliced</li>
                <li><i class="fas fa-check-circle"></i> 2 tomatoes, chopped</li>
                <li><i class="fas fa-check-circle"></i> 1 tsp red chili powder</li>
                <li><i class="fas fa-check-circle"></i> 1 tsp coriander powder</li>
                <li><i class="fas fa-check-circle"></i> Fresh coriander for garnish</li>
            </ul>
            <h3>For Serving:</h3>
            <ul class="ingredients-list">
                <li><i class="fas fa-check-circle"></i> 1.5 cups cooked brown rice</li>
            </ul>

            <h2>Instructions</h2>
            <div class="instructions">
                <ol>
                    <li><strong>Cook Dal:</strong> Pressure cook lentils with water, turmeric, and salt for 3-4 whistles until soft and mushy. Mash lightly with a spoon.</li>
                    <li><strong>Prepare Tadka:</strong> Heat ghee/oil in a pan. Add cumin seeds and let them crackle.</li>
                    <li><strong>Add Aromatics:</strong> Add dry red chilies, chopped garlic, and sliced onions. Sauté until onions turn golden brown.</li>
                    <li><strong>Add Tomatoes:</strong> Add chopped tomatoes and cook until they turn soft and mushy.</li>
                    <li><strong>Season:</strong> Add red chili powder and coriander powder. Mix well and cook for 1-2 minutes.</li>
                    <li><strong>Combine:</strong> Pour the cooked dal into the tadka. Mix well and simmer for 5 minutes. Adjust consistency with water if needed.</li>
                    <li><strong>Garnish:</strong> Add fresh coriander leaves on top.</li>
                    <li><strong>Serve:</strong> Serve hot dal with brown rice, accompanied by a salad or pickle.</li>
                </ol>
            </div>

            <div class="nutrition-info">
                <h2 style="color: white; margin-bottom: 10px;">Nutritional Information (Per Serving with Rice)</h2>
                <div class="nutrition-grid">
                    <div class="nutrition-item">
                        <div class="value">380</div>
                        <div class="label">Calories</div>
                    </div>
                    <div class="nutrition-item">
                        <div class="value">15g</div>
                        <div class="label">Protein</div>
                    </div>
                    <div class="nutrition-item">
                        <div class="value">58g</div>
                        <div class="label">Carbs</div>
                    </div>
                    <div class="nutrition-item">
                        <div class="value">8g</div>
                        <div class="label">Fat</div>
                    </div>
                    <div class="nutrition-item">
                        <div class="value">10g</div>
                        <div class="label">Fiber</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
