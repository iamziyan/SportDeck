-- --------------------------------------------------------
-- SportDeck Database Schema
-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS sportdeck_db;
USE sportdeck_db;

-- 1. Users Table (Authentication & Roles)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    age INT DEFAULT NULL,
    contact VARCHAR(20) DEFAULT NULL,
    role ENUM('player', 'admin') DEFAULT 'player',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tournaments Table
CREATE TABLE IF NOT EXISTS tournaments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    sport_type VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('upcoming', 'ongoing', 'completed') DEFAULT 'upcoming',
    max_players INT DEFAULT NULL  -- Optional player registration limit
);

-- 3. Teams Table
CREATE TABLE IF NOT EXISTS teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    coach_name VARCHAR(100) DEFAULT NULL,
    tournament_id INT NOT NULL,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE
);

-- 4. Players Table (Specific tournament players / Roster)
CREATE TABLE IF NOT EXISTS players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    age INT DEFAULT NULL,
    contact VARCHAR(20) DEFAULT NULL,
    team_id INT NOT NULL,
    user_id INT DEFAULT NULL, -- Optional link back to users table
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 5. Matches Table
CREATE TABLE IF NOT EXISTS matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT NOT NULL,
    team1_id INT NOT NULL,
    team2_id INT NOT NULL,
    match_date DATETIME NOT NULL,
    venue VARCHAR(100) DEFAULT NULL,
    status ENUM('upcoming', 'completed') DEFAULT 'upcoming',
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
    FOREIGN KEY (team1_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (team2_id) REFERENCES teams(id) ON DELETE CASCADE
);

-- 6. Results Table
CREATE TABLE IF NOT EXISTS results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT NOT NULL UNIQUE,
    winner_team_id INT DEFAULT NULL, -- NULL if draw
    score VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (winner_team_id) REFERENCES teams(id) ON DELETE SET NULL
);

-- 7. Tournament Registrations Table (Player self-registration)
CREATE TABLE IF NOT EXISTS tournament_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tournament_id INT NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('registered','cancelled') DEFAULT 'registered',
    UNIQUE KEY unique_reg (user_id, tournament_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE
);

-- --------------------------------------------------------
-- Dummy Data
-- --------------------------------------------------------

-- Insert Admin User (Password: 123456 -> hashed)
-- password_hash('123456', PASSWORD_DEFAULT) generated via PHP
INSERT INTO users (name, email, password_hash, role) VALUES 
('Super Admin', 'admin@sportdeck.com', '$2y$10$wU0M/f4h/k3R6UvR8xW6yOi2iJcEqi6sR7yQkVm22lA2yIe5hW6/C', 'admin');

-- Insert Sample Players
INSERT INTO users (name, email, password_hash, role) VALUES 
('John Doe', 'john@example.com', '$2y$10$wU0M/f4h/k3R6UvR8xW6yOi2iJcEqi6sR7yQkVm22lA2yIe5hW6/C', 'player'),
('Jane Smith', 'jane@example.com', '$2y$10$wU0M/f4h/k3R6UvR8xW6yOi2iJcEqi6sR7yQkVm22lA2yIe5hW6/C', 'player');

-- Insert Sample Tournament
INSERT INTO tournaments (name, sport_type, start_date, end_date, status) VALUES 
('Spring Basketball Cup 2026', 'Basketball', '2026-04-01', '2026-04-30', 'upcoming'),
('Winter Soccer League', 'Soccer', '2025-11-01', '2025-12-15', 'completed');

-- Insert Sample Teams
INSERT INTO teams (name, coach_name, tournament_id) VALUES 
('Lakers B', 'Coach Carter', 1),
('Bulls Z', 'Coach Jackson', 1),
('City Strikers', 'Coach Pep', 2),
('United Stars', 'Coach Fergie', 2);

-- Insert Sample Matches
INSERT INTO matches (tournament_id, team1_id, team2_id, match_date, venue, status) VALUES 
(1, 1, 2, '2026-04-05 18:00:00', 'Main Court', 'upcoming'),
(2, 3, 4, '2025-12-01 19:30:00', 'City Stadium', 'completed');

-- Insert Sample Result
INSERT INTO results (match_id, winner_team_id, score) VALUES 
(2, 3, '3-1');
