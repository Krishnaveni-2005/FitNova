const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const db = require('./db');
const path = require('path');

const app = express();
const PORT = 3000;

// Middleware
app.use(cors());
app.use(bodyParser.json());
app.use(express.static(__dirname)); // Serve static HTML files from root

// Route: Handle Normal Signup
app.post('/api/signup', async (req, res) => {
    try {
        const { firstName, lastName, email, phone, password } = req.body;

        // Check if user exists
        const [rows] = await db.query('SELECT * FROM users WHERE email = ?', [email]);
        if (rows.length > 0) {
            return res.status(409).json({ error: 'Email already exists' });
        }

        // Insert new user
        // Note: In production, ALWAYS hash passwords using bcrypt!
        // const hashedPassword = await bcrypt.hash(password, 10); 
        const sql = `INSERT INTO users (first_name, last_name, email, phone, password_hash, auth_provider) VALUES (?, ?, ?, ?, ?, 'local')`;
        await db.execute(sql, [firstName, lastName, email, phone, password]);

        res.status(201).json({ message: 'User created successfully' });

    } catch (error) {
        console.error(error);
        res.status(500).json({ error: 'Database error' });
    }
});

// Route: Handle Google Signup/Login
app.post('/api/google-auth', async (req, res) => {
    try {
        const { email, firstName, lastName, googleId, picture, emailVerified } = req.body;

        // Basic verification
        if (!emailVerified) {
            return res.status(403).json({ error: 'Email not verified by Google' });
        }

        // Check availability
        const [rows] = await db.query('SELECT * FROM users WHERE email = ?', [email]);

        if (rows.length > 0) {
            // User exists, log them in (or update google ID if missing)
            const user = rows[0];
            if (user.auth_provider === 'local' && !user.oauth_provider_id) {
                // Link accounts (Optional logic)
                await db.execute('UPDATE users SET auth_provider = ?, oauth_provider_id = ? WHERE user_id = ?', ['google', googleId, user.user_id]);
            }
            return res.json({ message: 'Login successful', user: user });
        } else {
            // Create new Google user
            const sql = `INSERT INTO users (first_name, last_name, email, account_status, is_email_verified, auth_provider, oauth_provider_id) VALUES (?, ?, ?, 'active', true, 'google', ?)`;
            await db.execute(sql, [firstName, lastName, email, googleId]);

            res.status(201).json({ message: 'Google account created' });
        }

    } catch (error) {
        console.error(error);
        res.status(500).json({ error: 'Database error' });
    }
});

app.listen(PORT, () => {
    console.log(`Server running on http://localhost:${PORT}`);
});
