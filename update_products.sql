-- Create Products Table
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category ENUM('men', 'women', 'equipment', 'supplements') NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    rating DECIMAL(3, 1) DEFAULT 4.5,
    review_count INT DEFAULT 0,
    is_new BOOLEAN DEFAULT FALSE,
    is_sale BOOLEAN DEFAULT FALSE,
    is_bestseller BOOLEAN DEFAULT FALSE,
    has_sizes BOOLEAN DEFAULT FALSE, -- To trigger size dropdown
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed Data (Sample)
INSERT INTO products (name, category, price, image_url, rating, review_count, has_sizes, is_bestseller) VALUES
-- Men's Wear
('Pro-Fit Compression Tee', 'men', 1499.00, 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?auto=format&fit=crop&q=80&w=600', 4.9, 420, TRUE, TRUE),
('Bodybuilding Stringer Vest', 'men', 899.00, 'https://images.unsplash.com/photo-1590400902521-4d9432bb9a69?auto=format&fit=crop&q=80&w=600', 4.7, 150, TRUE, FALSE),
('2-in-1 Training Shorts', 'men', 1299.00, 'https://images.unsplash.com/photo-1618354691438-25bc04584c23?auto=format&fit=crop&q=80&w=600', 4.8, 300, TRUE, FALSE),
('Performance Hoodie', 'men', 2499.00, 'https://images.unsplash.com/photo-1556906781-9a412961d28c?auto=format&fit=crop&q=80&w=600', 4.6, 120, TRUE, FALSE),
('Gym Joggers', 'men', 1899.00, 'https://images.unsplash.com/photo-1552160753-117159d79631?auto=format&fit=crop&q=80&w=600', 4.5, 90, TRUE, FALSE),

-- Women's Wear
('High-Waist Sculpt Leggings', 'women', 2499.00, 'https://images.unsplash.com/photo-1574914629385-46448b767bb1?auto=format&fit=crop&q=80&w=600', 4.9, 512, TRUE, TRUE),
('High-Impact Sports Bra', 'women', 1699.00, 'https://images.unsplash.com/photo-1620799140408-ed5341cd2431?auto=format&fit=crop&q=80&w=600', 4.8, 230, TRUE, FALSE),
('Breathable Racerback Tank', 'women', 999.00, 'https://images.unsplash.com/photo-1518310383802-640c2de311b2?auto=format&fit=crop&q=80&w=600', 4.6, 180, TRUE, FALSE),
('Seamless Yoga Set', 'women', 3499.00, 'https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?auto=format&fit=crop&q=80&w=600', 4.9, 310, TRUE, TRUE),
('Cropped Hoodie', 'women', 1999.00, 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&q=80&w=600', 4.7, 140, TRUE, FALSE),

-- Supplements
('100% Gold Whey Isolate (2.5kg)', 'supplements', 6499.00, 'https://images.unsplash.com/photo-1593095948071-474c5cc2989d?auto=format&fit=crop&q=80&w=600', 5.0, 1204, FALSE, TRUE),
('Micronized Creatine Monohydrate', 'supplements', 1499.00, 'https://plus.unsplash.com/premium_photo-1675806655184-7a353683f218?auto=format&fit=crop&q=80&w=600', 4.8, 890, FALSE, FALSE),
('Explode Pre-Workout', 'supplements', 2199.00, 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?auto=format&fit=crop&q=80&w=600', 4.7, 340, FALSE, FALSE),
('BCAA Powder', 'supplements', 1899.00, 'https://images.unsplash.com/photo-1579722822173-ffae64857388?auto=format&fit=crop&q=80&w=600', 4.6, 210, FALSE, FALSE),
('Multivitamin Pack', 'supplements', 999.00, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&q=80&w=600', 4.8, 560, FALSE, FALSE),

-- Equipment
('Rubber Hex Dumbbells (Pair)', 'equipment', 3999.00, 'https://images.unsplash.com/photo-1638536532686-d610adfc8e5c?auto=format&fit=crop&q=80&w=600', 4.9, 450, FALSE, TRUE),
('Pro Power Resistance Bands', 'equipment', 1299.00, 'https://images.unsplash.com/photo-1598289431512-b97b0917affc?auto=format&fit=crop&q=80&w=600', 4.8, 670, FALSE, FALSE),
('Cast Iron Kettlebell (16kg)', 'equipment', 2899.00, 'https://images.unsplash.com/photo-1517963652038-16cb0358826d?auto=format&fit=crop&q=80&w=600', 4.9, 110, FALSE, FALSE),
('Yoga Mat Professional', 'equipment', 1499.00, 'https://images.unsplash.com/photo-1599901860904-17e6ed7083a0?auto=format&fit=crop&q=80&w=600', 4.7, 340, FALSE, FALSE),
('Adjustable Bench', 'equipment', 8999.00, 'https://images.unsplash.com/photo-1598289431512-b97b0917affc?auto=format&fit=crop&q=80&w=600', 4.8, 85, FALSE, FALSE);
