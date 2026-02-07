-- AI Mock Interview System Database Schema
-- Create database
CREATE DATABASE IF NOT EXISTS ai_mock_interview;
USE ai_mock_interview;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create interview results table
CREATE TABLE IF NOT EXISTS interview_results (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    question VARCHAR(500) NOT NULL,
    answer_text LONGTEXT NOT NULL,
    accuracy DECIMAL(5, 2) NOT NULL,
    clarity DECIMAL(5, 2) NOT NULL,
    completeness DECIMAL(5, 2) NOT NULL,
    confidence DECIMAL(5, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert sample user for testing
INSERT INTO users (username) VALUES ('guest') ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id);

-- Create index for faster queries
CREATE INDEX idx_user_created ON interview_results(user_id, created_at);
