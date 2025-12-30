-- Database Schema for FitNova

CREATE DATABASE IF NOT EXISTS fitnova_db;
USE fitnova_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password_hash VARCHAR(255), -- Nullable for users who sign up via Social Login only
    
    -- Social Login Fields
    auth_provider ENUM('local', 'google', 'facebook') DEFAULT 'local',
    oauth_provider_id VARCHAR(255), -- Stores the Google 'sub' ID or Facebook ID
    
    -- Verification & Status
    is_email_verified BOOLEAN DEFAULT FALSE,
    account_status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Terms Acceptance Log (Optional but good for compliance)
CREATE TABLE IF NOT EXISTS terms_acceptance (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    accepted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Indexes for performance
CREATE INDEX idx_email ON users(email);
CREATE INDEX idx_oauth ON users(oauth_provider_id);
