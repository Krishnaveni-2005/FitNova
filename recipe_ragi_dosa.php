<?php session_start(); include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ragi Dosa with Coconut Chutney - FitNova</title>
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
            background-image: url('https://images.unsplash.com/photo-1668236543090-82eba5ee5976?auto=format&fit=crop&q=80&w=1200'); 
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
    <div class="container">
        <div class="recipe-header">
            <div class="recipe-image">
                <div class="recipe-title-overlay">
                    <h1>Ragi Dosa with Coconut Chutney</h1>
                    <p>Gluten-Free Calcium-Rich Crepe</p>
                </div>
            </div>
            <div class="recipe-meta">
                <div class="meta-item"><i class="far fa-clock"></i> <strong>30 mins</strong></div>
                <div class="meta-item"><i class="fas fa-fire"></i> <strong>200 kcal</strong></div>
                <div class="meta-item"><i class="fas fa-utensils"></i> <strong>3 servings</strong></div>
                <div class="meta-item"><i class="fas fa-signal"></i> <strong>Medium</strong></div>
            </div>
        </div>

        <div class="recipe-content">
            <h2>About This Recipe</h2>
            <p>Ragi Dosa is a nutritious South Indian crepe made from finger millet flour. Rich in calcium and iron, it's gluten-free and perfect for weight management. Paired with coconut chutney, this makes for a healthy and delicious breakfast or dinner option.</p>

            <h2>Ingredients</h2>
            <h3>For Ragi Dosa:</h3>
            <ul class="ingredients-list">
                <li><i class="fas fa-check-circle"></i> 1 cup ragi (finger millet) flour</li>
                <li><i class="fas fa-check-circle"></i> 1/2 cup rice flour</li>
                <li><i class="fas fa-check-circle"></i> 1/4 cup urad dal flour (optional, for crispiness)</li>
                <li><i class="fas fa-check-circle"></i> 1 onion, finely chopped</li>
                <li><i class="fas fa-check-circle"></i> 2-3 green chilies, chopped</li>
                <li><i class="fas fa-check-circle"></i> Few curry leaves</li>
                <li><i class="fas fa-check-circle"></i> 1/2 tsp cumin seeds</li>
                <li><i class="fas fa-check-circle"></i> Fresh coriander, chopped</li>
                <li><i class="fas fa-check-circle"></i> Salt to taste</li>
                <li><i class="fas fa-check-circle"></i> 2 cups water (adjust for batter consistency)</li>
                <li><i class="fas fa-check-circle"></i> Oil for cooking</li>
            </ul>
            <h3>For Coconut Chutney:</h3>
            <ul class="ingredients-list">
                <li><i class="fas fa-check-circle"></i> 1 cup fresh grated coconut</li>
                <li><i class="fas fa-check-circle"></i> 2-3 green chilies</li>
                <li><i class="fas fa-check-circle"></i> 1 small piece ginger</li>
                <li><i class="fas fa-check-circle"></i> 1 tbsp roasted chana dal</li>
                <li><i class="fas fa-check-circle"></i> Few curry leaves</li>
                <li><i class="fas fa-check-circle"></i> Salt to taste</li>
                <li><i class="fas fa-check-circle"></i> Tempering: mustard seeds, curry leaves, oil</li>
            </ul>

            <h2>Instructions</h2>
            <div class="instructions">
                <ol>
                    <li><strong>Prepare Dosa Batter:</strong> Mix ragi flour, rice flour, and urad dal flour in a bowl. Add water gradually to make a smooth, pourable batter (consistency of buttermilk).</li>
                    <li><strong>Add Vegetables:</strong> Add chopped onions, green chilies, curry leaves, cumin seeds, coriander, and salt. Mix well. Let it rest for 10 minutes.</li>
                    <li><strong>Make Coconut Chutney:</strong> Blend coconut, green chilies, ginger, roasted chana dal, and salt with little water until smooth. Temper with mustard seeds and curry leaves.</li>
                    <li><strong>Heat Pan:</strong> Heat a non-stick pan or tawa on medium heat. Lightly grease with oil.</li>
                    <li><strong>Pour Batter:</strong> Pour a ladleful of batter onto the pan. Spread gently in circular motions to form a thin dosa.</li>
                    <li><strong>Cook:</strong> Drizzle oil around the edges. Cook for 2-3 minutes until the bottom turns golden and crispy.</li>
                    <li><strong>Flip and Finish:</strong> Flip and cook the other side for 1-2 minutes.</li>
                    <li><strong>Serve:</strong> Serve hot ragi dosas with coconut chutney and sambar.</li>
                </ol>
            </div>

            <div class="nutrition-info">
                <h2 style="color: white; margin-bottom: 10px;">Nutritional Information (Per Serving - 2 Dosas)</h2>
                <div class="nutrition-grid">
                    <div class="nutrition-item">
                        <div class="value">200</div>
                        <div class="label">Calories</div>
                    </div>
                    <div class="nutrition-item">
                        <div class="value">6g</div>
                        <div class="label">Protein</div>
                    </div>
                    <div class="nutrition-item">
                        <div class="value">35g</div>
                        <div class="label">Carbs</div>
                    </div>
                    <div class="nutrition-item">
                        <div class="value">4g</div>
                        <div class="label">Fat</div>
                    </div>
                    <div class="nutrition-item">
                        <div class="value">150mg</div>
                        <div class="label">Calcium</div>
                    </div>
                    <div class="nutrition-item">
                        <div class="value">3mg</div>
                        <div class="label">Iron</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
