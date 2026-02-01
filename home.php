<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitNova - Health & Wellness Ecosystem</title>
    <meta name="description" content="FitNova is a comprehensive health and wellness ecosystem offering expert guidance, curated fitness equipment, and personalized training plans.">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0F2C59; /* Dark Blue */
            --accent-color: #4FACFE; /* Light Blue */
            --text-dark: #1A1A1A;
            --text-light: #555;
            --bg-light: #F8F9FA;
            --white: #FFFFFF;
            --transition: all 0.3s ease;
            --font-main: 'Outfit', sans-serif;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: var(--font-main); }
        body { background-color: var(--bg-light); color: var(--text-dark); overflow-x: hidden; }
        a { text-decoration: none; color: inherit; }
        ul { list-style: none; }
        
        /* Layout */
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        
        /* Navbar */
        .navbar { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); padding: 15px 0; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .nav-container { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 28px; font-weight: 900; color: var(--primary-color); letter-spacing: -0.5px; }
        .nav-links { display: flex; align-items: center; gap: 40px; }
        .nav-link { font-weight: 500; font-size: 0.95rem; color: var(--text-dark); transition: var(--transition); }
        .nav-link:hover { color: var(--primary-color); }
        .btn-signup { background: var(--primary-color); color: white; padding: 10px 25px; border-radius: 50px; font-weight: 600; font-size: 0.9rem; transition: var(--transition); }
        .btn-signup:hover { background: #0a1f40; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(15, 44, 89, 0.3); }

        /* Hero */
        .hero { position: relative; padding: 80px 0 180px; text-align: center; background: linear-gradient(135deg, #c5d3e0 0%, #a8b8cc 100%); overflow: hidden; }
        /* Placeholder background image matching usage context */
        .hero::before {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&q=80&w=2000') center/cover no-repeat;
            opacity: 0.15; pointer-events: none;
        }
        .hero-tag { display: inline-block; font-size: 0.75rem; font-weight: 700; letter-spacing: 2px; color: #5B7C99; margin-bottom: 15px; text-transform: uppercase; background: rgba(255,255,255,0.7); padding: 5px 15px; border-radius: 20px; backdrop-filter: blur(5px); }
        .hero-title { font-size: 42px; font-weight: 800; line-height: 1.2; margin-bottom: 15px; color: var(--text-dark); max-width: 900px; margin-left: auto; margin-right: auto; position: relative; }
        .hero-subtitle { font-size: 1rem; color: var(--text-light); margin-bottom: 0px; font-weight: 400; position: relative; }

        /* Hero Cards (Floating) */
        .hero-cards-section { margin-top: -120px; padding-bottom: 50px; position: relative; z-index: 10; }
        .hero-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        
        .h-card { 
            background: white; 
            border-radius: 20px; 
            overflow: hidden; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.08); 
            transition: var(--transition); 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            text-align: center; 
            padding-bottom: 25px; 
            height: 380px; 
        }
        
        .h-card:hover { transform: translateY(-10px); box-shadow: 0 30px 60px rgba(0,0,0,0.12); }
        .h-card img { width: 100%; height: 180px; object-fit: cover; margin-bottom: 20px; }
        .h-card h3 { font-size: 1.2rem; font-weight: 700; margin-bottom: 8px; color: var(--text-dark); }
        .h-card p { font-size: 0.9rem; color: var(--text-light); margin-bottom: 20px; padding: 0 20px; line-height: 1.5; }
        
        .btn-view { display: inline-block; background: var(--text-dark); color: white; padding: 8px 25px; border-radius: 50px; font-size: 0.85rem; font-weight: 600; transition: var(--transition); margin-top: auto; }
        .btn-view:hover { background: var(--primary-color); transform: translateY(-2px); }

        /* Features */
        .why-us { padding: 40px 0 50px; background: white; }
        .section-title { text-align: center; font-size: 28px; font-weight: 800; margin-bottom: 40px; color: var(--text-dark); }
        .features-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 30px; }
        .f-card { display: flex; flex-direction: column; align-items: flex-start; padding: 25px; background: white; border-radius: 20px; border: 1px solid #eee; transition: var(--transition); box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
        .f-card:hover { box-shadow: 0 20px 50px rgba(0,0,0,0.08); transform: translateY(-5px); border-color: transparent; }
        .f-card i { font-size: 2rem; margin-bottom: 15px; }
        .f-card h4 { font-size: 1.2rem; font-weight: 700; margin-bottom: 10px; color: var(--text-dark); }
        .f-card p { color: var(--text-light); font-size: 0.95rem; line-height: 1.5; }
        .blue-icon { color: #4FACFE; }
        .green-icon { color: #2ecc71; }
        .yellow-icon { color: #f1c40f; }
        .red-icon { color: #e74c3c; }

        /* Testimonials */
        .testimonials { padding: 50px 0; background-color: #F8F9FA; }
        .t-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .t-card { background: white; padding: 30px 25px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); transition: var(--transition); border: 1px solid #f0f0f0; }
        .t-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.06); }
        .stars { color: #FFD700; margin-bottom: 15px; font-size: 0.8rem; letter-spacing: 2px; }
        .t-text { font-style: italic; margin-bottom: 25px; color: var(--text-dark); font-size: 0.9rem; line-height: 1.5; }
        .t-author { display: flex; align-items: center; gap: 15px; border-top: 1px solid #eee; padding-top: 20px; width: 100%; }
        .t-author img { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; }
        .t-author h5 { font-size: 0.95rem; font-weight: 700; color: var(--text-dark); margin-bottom: 2px; }
        .t-author p { font-size: 0.8rem; color: var(--text-light); font-weight: 500; }

        /* Footer */
        .footer { background: #111; color: white; padding: 60px 0 20px; }
        .footer-top { display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 40px; padding-bottom: 40px; border-bottom: 1px solid #333; }
        .f-logo { font-size: 28px; font-weight: 900; margin-bottom: 20px; color: white; }
        .f-desc { color: #999; font-size: 0.9rem; margin-bottom: 25px; line-height: 1.5; max-width: 350px; }
        .f-socials { display: flex; gap: 10px; }
        .f-socials a { width: 35px; height: 35px; background: #222; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: var(--transition); color: white; }
        .f-socials a:hover { background: var(--accent-color); transform: translateY(-3px); }
        .footer-top h4 { margin-bottom: 20px; font-size: 1.1rem; font-weight: 700; color: white; }
        .f-links li { margin-bottom: 15px; }
        .f-links a { color: #999; font-size: 0.9rem; transition: var(--transition); }
        .f-links a:hover { color: white; padding-left: 5px; }
        .footer-bottom { padding-top: 25px; text-align: center; color: #666; font-size: 0.85rem; }

        @media (max-width: 992px) {
            .hero-title { font-size: 32px; }
            .hero-cards { grid-template-columns: 1fr; max-width: 500px; margin: 0 auto; }
            .features-grid { grid-template-columns: 1fr; }
            .t-grid { grid-template-columns: 1fr; }
            .footer-top { grid-template-columns: 1fr; text-align: center; }
            .f-desc { margin: 0 auto 30px; }
            .f-socials { justify-content: center; }
            .h-card img { height: 200px; }
        }


    </style>
</head>
<body>

    <?php $isHomePage = true; include 'header.php'; ?>

    <!-- Hero -->
    <header class="hero">
        <div class="container">
            <span class="hero-tag">HEALTH & WELLNESS ECOSYSTEM</span>
            <h1 class="hero-title">Your perfect coach is just a few steps away</h1>
            <p class="hero-subtitle">Find the ideal coach for your goals & interests</p>
        </div>
    </header>

    <!-- Cards Section -->
    <section class="container hero-cards-section">
        <div class="hero-cards">
            <!-- Card 1 -->
            <div class="h-card">
                <img src="https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&q=80&w=800" alt="Fitness & Nutrition">
                <h3>Fitness & Nutrition</h3>
                <p>Unlock your dream body with the right diet & workouts.</p>
                <a href="fitness_nutrition.php" class="btn-view">View</a>
            </div>
            <!-- Card 2 -->
            <div class="h-card">
                <img src="https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?auto=format&fit=crop&q=80&w=800" alt="Online Personal Training">
                <h3>Online Personal Training</h3>
                <p>1-on-1 online sessions for Yoga, Zumba & more!</p>
                <a href="trainers.php" class="btn-view">View</a>
            </div>
            <!-- Card 3 -->
            <div class="h-card">
                <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&q=80&w=800" alt="Learn">
                <h3>Learn</h3>
                <p>Discover fitness articles, insights, and wellness tips.</p>
                <a href="learn.php" class="btn-view">View</a>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="why-us">
        <div class="container">
            <h2 class="section-title">
                Why Choose FitNova?
            </h2>
            <div>
                <div class="features-grid">
                    <div class="f-card">
                        <i class="fas fa-users blue-icon"></i>
                        <h4>Expert Guidance</h4>
                        <p>Connect with certified trainers, nutritionists, and physiotherapists who provide personalized guidance based on your fitness goals and needs.</p>
                    </div>
                    <div class="f-card">
                        <i class="fas fa-store green-icon"></i>
                        <h4>Curated Fitshop</h4>
                        <p>Access our exclusive marketplace with premium fitness equipment, supplements, and wellness products, all carefully selected by our experts.</p>
                    </div>
                    <div class="f-card">
                        <i class="fas fa-graduation-cap yellow-icon"></i>
                        <h4>Learn & Grow</h4>
                        <p>Expand your fitness knowledge with our educational resources, articles, and video tutorials covering various aspects of health and wellness.</p>
                    </div>
                    <div class="f-card">
                        <i class="fas fa-mobile-alt red-icon"></i>
                        <h4>All-in-One Platform</h4>
                        <p>Everything you need for your fitness journey in one place - from workout tracking to nutrition planning and community support.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials">
        <div class="container">
            <h2 class="section-title">
                Real Stories, Real Results
            </h2>
            <div>
                <div class="t-grid">
                    <div class="t-card">
                        <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <p class="t-text">"With my hectic routine, I thought fitness was impossible. But this program fit perfectly into my lifestyle. I've lost 20 lbs, gained confidence!"</p>
                        <div class="t-author">
                            <img src="sara.jpg" onerror="this.src='https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&q=80&w=150'" alt="Sarah">
                            <div>
                                <h5>Sarah Johnson</h5>
                                <p>Transformation: Weight Loss</p>
                            </div>
                        </div>
                    </div>
                    <div class="t-card">
                        <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <p class="t-text">"My online trainer fixed my squat form over video calls! It's like having a coach in my living room. Pure gold."</p>
                        <div class="t-author">
                            <img src="assets/david.jpg" onerror="this.src='https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=150'" alt="David">
                            <div>
                                <h5>David Kim</h5>
                                <p>Transformation: Strength</p>
                            </div>
                        </div>
                    </div>
                    <div class="t-card">
                        <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <p class="t-text">"The certification courses are incredibly detailed. I went from fitness enthusiast to certified nutritionist in 6 months."</p>
                        <div class="t-author">
                            <img src="emily.jpg" onerror="this.src='https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&q=80&w=150'" alt="Emily">
                            <div>
                                <h5>Emily Chen</h5>
                                <p>Transformation: Career</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>


    <!-- Chatbot Widget -->
    <div id="chatbot-container">
        <div id="chat-window" class="chat-window">
            <div class="chat-header">
                <div class="chat-title">FitNova Assistant</div>
                <button id="close-chat" class="close-chat">&times;</button>
            </div>
            <div id="chat-messages" class="chat-messages">
                <div class="message bot-message">
                    Hello! 👋 I'm your FitNova assistant. How can I help you today?
                </div>
                 <div class="message bot-message">
                    You can ask me about:
                    <br>• Gym Memberships
                    <br>• Finding a Trainer
                    <br>• Diet Plans
                    <br>• Products
                </div>
            </div>
            <div class="chat-input-area">
                <input type="text" id="chat-input" placeholder="Type a message..." onkeypress="handleChatInput(event)">
                <button id="send-btn" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
        <button id="chatbot-toggle" class="chatbot-toggle" onclick="toggleChat()">
            <i class="fas fa-comment-dots"></i>
        </button>
    </div>

    <style>
        /* Chatbot Styles */
        #chatbot-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 9999;
            font-family: 'Outfit', sans-serif;
        }

        .chatbot-toggle {
            width: 60px;
            height: 60px;
            background: var(--primary-color);
            border-radius: 50%;
            color: white;
            border: none;
            cursor: pointer;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            transition: all 0.3s ease;
        }

        .chatbot-toggle:hover {
            transform: scale(1.1);
            background: #0a1f40;
        }

        .chat-window {
            position: absolute;
            bottom: 80px;
            right: 0;
            width: 350px;
            height: 500px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            display: none;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid #eee;
            animation: slideUp 0.3s ease;
        }

        .chat-window.active {
            display: flex;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .chat-header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-title {
            font-weight: 700;
            font-size: 1.1rem;
        }

        .close-chat {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            opacity: 0.8;
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .message {
            padding: 12px 16px;
            border-radius: 12px;
            max-width: 80%;
            font-size: 0.95rem;
            line-height: 1.5;
            position: relative;
        }

        .bot-message {
            background: white;
            color: var(--text-dark);
            align-self: flex-start;
            border-bottom-left-radius: 2px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .user-message {
            background: var(--primary-color);
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 2px;
        }

        .chat-input-area {
            padding: 15px;
            background: white;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
        }

        #chat-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 25px;
            outline: none;
            transition: border-color 0.3s;
        }

        #chat-input:focus {
            border-color: var(--primary-color);
        }

        #send-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s;
        }

        #send-btn:hover {
            transform: scale(1.1);
        }
    </style>

    <script>



        function toggleChat() {
            const chatWindow = document.getElementById('chat-window');
            chatWindow.classList.toggle('active');
            if (chatWindow.classList.contains('active')) {
                document.getElementById('chat-input').focus();
            }
        } 
        
        document.getElementById('close-chat').addEventListener('click', toggleChat);

        function handleChatInput(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        }

        function sendMessage() {
            const input = document.getElementById('chat-input');
            const message = input.value.trim();
            if (message) {
                addMessage(message, 'user');
                input.value = '';
                
                // Simulate bot typing and response
                const delay = Math.max(500, Math.min(1500, message.length * 20)); // varying delay based on complexity
                setTimeout(() => {
                    const response = getBotResponse(message.toLowerCase());
                    addMessage(response, 'bot');
                }, delay);
            }
        }

        function addMessage(text, sender) {
            const messagesDiv = document.getElementById('chat-messages');
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('message', `${sender}-message`);
            messageDiv.innerHTML = text;
            messagesDiv.appendChild(messageDiv);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }

        // Advanced Chatbot Logic
        class SmartBot {
            constructor() {
                this.context = {};
                this.memory = {
                    name: null,
                };
                
                // Knowledge Base
                this.knowledge = [
                    // GREETINGS
                    { patterns: [/\b(hi|hello|hey|yo|sup|greetings|good morning|good evening)\b/i], response: ["Hello! 👋 How can I help you today?", "Hi there! Ready to get fit?", "Hey! What's on your mind?"] },
                    { patterns: [/\b(bye|goodbye|cya|see you|later)\b/i], response: ["Goodbye! Stay active! 💪", "See you later! Keep crushing your goals.", "Bye! Hope to see you back soon!"] },
                    { patterns: [/\b(thanks|thank you|thx|appreciate it)\b/i], response: ["You're welcome!", "Anytime!", "Glad I could help!"] },
                    { patterns: [/\b(how are you|how are things|how's it going)\b/i], response: ["I'm just a bot, but I'm feeling 100% optimized! How are you?", "Doing great! Ready to help you crush your fitness goals."] },
                    
                    // IDENTITY
                    { patterns: [/\b(who are you|what are you|bot name)\b/i], response: ["I'm the FitNova Assistant. I'm here to help you navigate our gym, plans, and products."] },
                    
                    // KEY TOPICS
                    // 1. PRODUCTS / SHOP
                    { patterns: [/\b(product|products|merch|gear|store|shop|buy|supplement|protein|whey|creatine|shirt|pants|wear)\b/i], response: ["We have a great selection of premium fitness gear and supplements! Visit our <a href='fitshop.php' style='color:#4FACFE; font-weight:bold;'>FitShop</a> to browse our catalog."] },
                    
                    // 2. DIET / NUTRITION
                    { patterns: [/\b(diet|food|nutrition|recipe|eat|meal|calories|macro|keto|vegan|vegetarian)\b/i], response: ["Nutrition is key! 🥗 Check out our <a href='healthy_recipes.php' style='color:#4FACFE; font-weight:bold;'>Healthy Recipes</a> or ask a trainer for a custom meal plan."] },
                    
                    // 3. TRAINERS
                    { patterns: [/\b(trainer|coach|pt|personal training|guide|mentor|instructor)\b/i], response: ["Our certified trainers are the best in the business. Find your perfect match on the <a href='trainers.php' style='color:#4FACFE; font-weight:bold;'>Trainers Page</a>."] },
                    
                    // 4. MEMBERSHIP / PLANS
                    { patterns: [/\b(price|cost|plan|membership|fee|subscription|join|sign up|register)\b/i], response: ["We offer flexible plans for every budget. You can start small or go Pro! See our plans <a href='subscription_plans.php' style='color:#4FACFE; font-weight:bold;'>here</a>."] },
                    
                    // 5. WORKOUTS
                    { patterns: [/\b(workout|exercise|routine|training|lift|cardio|strength|muscle|run)\b/i], response: ["Need workout ideas? Our <a href='fitness_nutrition.php' style='color:#4FACFE; font-weight:bold;'>Fitness Section</a> has plenty of free guides, or you can get a custom plan from a trainer."] },
                    
                    // 6. GYM INFO
                    { patterns: [/\b(gym|facility|equipment|machine|hours|location|where|address|open|close)\b/i], response: ["FitNova gyms are open 24/7! We have multiple locations with top-tier equipment. Check the 'Offline Gym' section in your dashboard for access."] },

                    // 7. SUPPORT
                    { patterns: [/\b(contact|email|phone|support|help|issue|problem|bug|error)\b/i], response: ["If you're facing issues, you can reach our support team at <a href='mailto:support@fitnova.com' style='color:#4FACFE;'>support@fitnova.com</a>."] },

                    // Conversational Interactions
                    { patterns: [/\b(my name is|i am|call me) (.+)/i], action: (m) => this.setName(m) },
                    { patterns: [/\b(i want|i need|i'd like|can i get) (.+)/i], action: (m) => this.reflectRequest(m) },
                    { patterns: [/\b(can you|do you) (.+)/i], response: ["I can certainly try to help with that! If it's specific to your account, you might need to log in first.", "Check the menu links—I bet what you're looking for is there!"] },
                    { patterns: [/\b(why) (.+)/i], response: ["That's a good question. Often it aligns with your personal fitness journey.", "Why? Because we believe in your potential!"] },
                    
                    // Specific Questions Catch-all
                    { patterns: [/\?\s*$/], response: ["That's a great question. You can often find the answer in our 'Learn' section, or by asking a trainer directly.", "I'm not sure about the specifics, but our support team surely knows!"] },
                ];
                
                this.defaults = [
                    "That's interesting! Tell me more.",
                    "I see. How does that fit into your fitness goals?",
                    "Could you elaborate on that?",
                    "I'm listening. Go on.",
                    "That sounds like something our trainers could help with! <a href='trainers.php' style='color:#4FACFE'>Find a Trainer</a>",
                    "Can you tell me more about what you're looking for?",
                    "We have resources for that! Have you checked the menu?"
                ];
            }

            setName(match) {
                const name = match[2];
                this.memory.name = name;
                return `Nice to meet you, ${name}! How can I help you reach your goals today?`;
            }

            reflectRequest(match) {
                const desire = match[2];
                // simple heuristic to detect if the captured group is valid text
                if (desire.length < 2) return "Could you be more specific?";
                return `It sounds like you're interested in ${desire}. We can definitely help with that! <br>Check our <a href='home.php' style='color:#4FACFE'>Homepage</a> for quick links.`;
            }

            getResponse(input) {
                    const text = input.toLowerCase();
                    let bestMatch = null;

                    // Check patterns
                    for (let k of this.knowledge) {
                        for (let p of k.patterns) {
                            const match = text.match(p);
                            if (match) {
                                // If it's an action, execute it immediately
                                if (k.action) return k.action(match);
                                
                                // Otherwise, return a random response from the list
                                return k.response[Math.floor(Math.random() * k.response.length)];
                            }
                        }
                    }

                    // Personalization if name is known
                    if (this.memory.name && Math.random() > 0.7) {
                        return `${this.memory.name}, I'm not 100% sure about that, but our trainers would know!`;
                    }

                    return this.defaults[Math.floor(Math.random() * this.defaults.length)];
            }
        }

        const bot = new SmartBot();

        function getBotResponse(input) {
            return bot.getResponse(input);
        }
    </script>
</body>
</html>
