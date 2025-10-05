-- database.sql
CREATE DATABASE IF NOT EXISTS productivity_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE productivity_db;

-- users table
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- categories table
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  color VARCHAR(7) DEFAULT '#6c5ce7',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- tasks table
CREATE TABLE IF NOT EXISTS tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  category_id INT DEFAULT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  due_date DATE DEFAULT NULL,
  priority ENUM('low','medium','high') DEFAULT 'medium',
  is_done TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  completed_at DATETIME DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- task_history: for analytics (tracks completions)
CREATE TABLE IF NOT EXISTS task_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  task_id INT NOT NULL,
  user_id INT NOT NULL,
  action ENUM('created','completed','updated','deleted') NOT NULL,
  action_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Sample user (password: pass123) - change or delete in real usage
INSERT INTO users (name, email, password) VALUES
('Demo User','demo@example.com', '$2y$10$e0NRqgG2lZx1kzv2kd2nHu6HduP1qvH3qH9I4v2Gw2uTQrWQYkFqK'); 
-- hash for 'pass123' using password_hash('pass123', PASSWORD_DEFAULT)

-- sample categories for demo user (user_id = 1)
INSERT INTO categories (user_id, name, color) VALUES
(1, 'Study', '#ff7675'),
(1, 'Work', '#74b9ff'),
(1, 'Personal', '#55efc4');

-- sample tasks
INSERT INTO tasks (user_id, category_id, title, description, due_date, priority)
VALUES
(1, 1, 'Read chapter 4', 'Read and summarize chapter 4 of networking.', DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY), 'high'),
(1, 2, 'Weekly report', 'Prepare weekly report for team', DATE_ADD(CURRENT_DATE(), INTERVAL 3 DAY), 'medium'),
(1, NULL, 'Buy groceries', 'Milk, eggs, bread', NULL, 'low');
