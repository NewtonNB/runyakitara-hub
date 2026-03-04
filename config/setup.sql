-- Runyakitara Hub Database Setup
CREATE DATABASE IF NOT EXISTS runyakitara_hub;
USE runyakitara_hub;

-- Users table for admin authentication
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'editor') DEFAULT 'editor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Lessons table
CREATE TABLE IF NOT EXISTS lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    content TEXT NOT NULL,
    level ENUM('beginner', 'intermediate', 'advanced') NOT NULL,
    lesson_order INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Dictionary entries
CREATE TABLE IF NOT EXISTS dictionary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    word_runyakitara VARCHAR(100) NOT NULL,
    word_english VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    pronunciation VARCHAR(100),
    example_sentence TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Proverbs table
CREATE TABLE IF NOT EXISTS proverbs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proverb TEXT NOT NULL,
    translation TEXT NOT NULL,
    meaning TEXT NOT NULL,
    usage TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Articles/News table
CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    excerpt TEXT,
    author VARCHAR(100),
    published_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Translations table
CREATE TABLE IF NOT EXISTS translations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    type ENUM('song', 'story', 'poem', 'speech', 'document') NOT NULL,
    original_text TEXT NOT NULL,
    translated_text TEXT NOT NULL,
    cultural_context TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Grammar topics table
CREATE TABLE IF NOT EXISTS grammar_topics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    examples TEXT,
    difficulty VARCHAR(20) DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Media resources table
CREATE TABLE IF NOT EXISTS media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    type ENUM('audio', 'video') NOT NULL,
    category VARCHAR(50),
    file_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Contact messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample admin user (password: admin123)
INSERT INTO users (username, password, email, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@runyakitara.com', 'admin');

-- Insert sample lessons
INSERT INTO lessons (title, description, content, level, lesson_order) VALUES
('Introduction to Runyakitara', 'Learn about the language family and its speakers', 'Runyakitara is a collective term for closely related Bantu languages...', 'beginner', 1),
('Alphabet & Pronunciation', 'Master the sounds and letters', 'The Runyakitara alphabet consists of...', 'beginner', 2),
('Basic Greetings', 'Essential phrases for daily interaction', 'Oraire ota? - How are you?', 'beginner', 3);

-- Insert sample dictionary entries
INSERT INTO dictionary (word_runyakitara, word_english, category, pronunciation) VALUES
('Omuntu', 'Person', 'People', 'oh-moon-too'),
('Ente', 'Cow', 'Animals', 'en-teh'),
('Omugyenyi', 'Guest', 'People', 'oh-moo-gyen-yee'),
('Oraire', 'Hello/Greetings', 'Greetings', 'oh-rye-reh');

-- Insert sample proverbs
INSERT INTO proverbs (proverb_text, translation, meaning) VALUES
('Omugurusi tarikuza omu mwanya', 'An old person does not grow in one place', 'Experience comes from traveling and learning from different places'),
('Akahurira kazoora', 'What is delayed will eventually come', 'Patience is rewarded; good things come to those who wait');

-- Insert sample articles
INSERT INTO articles (title, content, excerpt, author, published_date) VALUES
('The Importance of Preserving Runyakitara Languages', 'Full article content here...', 'Exploring why language preservation matters...', 'Admin', CURDATE()),
('Famous Banyakitara Personalities', 'Full article content here...', 'Celebrating influential figures...', 'Admin', CURDATE());
