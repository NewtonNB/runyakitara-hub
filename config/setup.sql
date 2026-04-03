-- Runyakitara Hub — MySQL Setup
-- Run this once to create the database and all tables.

CREATE DATABASE IF NOT EXISTS runyakitara_hub
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE runyakitara_hub;

-- ── Users ──────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(80)  UNIQUE NOT NULL,
    password   VARCHAR(255) NOT NULL,
    email      VARCHAR(150) UNIQUE NOT NULL,
    role       VARCHAR(30)  DEFAULT 'editor',
    created_at DATETIME     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Lessons ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS lessons (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(200) NOT NULL,
    description  TEXT,
    content      LONGTEXT     NOT NULL,
    level        VARCHAR(20)  NOT NULL,
    lesson_order INT          NOT NULL,
    deleted_at   DATETIME     DEFAULT NULL,
    created_at   DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Dictionary ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS dictionary (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    word_runyakitara VARCHAR(150) NOT NULL,
    word_english     VARCHAR(150) NOT NULL,
    category         VARCHAR(80),
    pronunciation    VARCHAR(150),
    example_sentence TEXT,
    deleted_at       DATETIME DEFAULT NULL,
    created_at       DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Proverbs ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS proverbs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    proverb    TEXT NOT NULL,
    translation TEXT NOT NULL,
    meaning    TEXT NOT NULL,
    usage      TEXT,
    deleted_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Articles ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS articles (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    title          VARCHAR(200) NOT NULL,
    content        LONGTEXT     NOT NULL,
    excerpt        TEXT,
    author         VARCHAR(100),
    category       VARCHAR(80),
    published_date DATE,
    deleted_at     DATETIME DEFAULT NULL,
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Translations ───────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS translations (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(200) NOT NULL,
    type            VARCHAR(30)  NOT NULL,
    original_text   LONGTEXT     NOT NULL,
    translated_text LONGTEXT     NOT NULL,
    cultural_context TEXT,
    deleted_at      DATETIME DEFAULT NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Grammar Topics ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS grammar_topics (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    title      VARCHAR(200) NOT NULL,
    content    LONGTEXT     NOT NULL,
    examples   TEXT,
    difficulty VARCHAR(20)  DEFAULT 'medium',
    deleted_at DATETIME     DEFAULT NULL,
    created_at DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Media ──────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS media (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    description TEXT,
    type        VARCHAR(20)  NOT NULL,
    category    VARCHAR(80),
    file_path   VARCHAR(500) NOT NULL,
    deleted_at  DATETIME DEFAULT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Contact Messages ───────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS contact_messages (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    subject    VARCHAR(200) NOT NULL,
    message    TEXT         NOT NULL,
    status     VARCHAR(20)  DEFAULT 'new',
    created_at DATETIME     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Likes ──────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS likes (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    content_type VARCHAR(30)  NOT NULL,
    content_id   INT          NOT NULL,
    ip_address   VARCHAR(45)  NOT NULL,
    created_at   DATETIME     DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_like (content_type, content_id, ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Comments ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS comments (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    content_type VARCHAR(30)  NOT NULL,
    content_id   INT          NOT NULL,
    name         VARCHAR(80)  NOT NULL,
    comment      TEXT         NOT NULL,
    status       VARCHAR(20)  DEFAULT 'approved',
    created_at   DATETIME     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── RBAC: Roles & Permissions ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS roles (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS permissions (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS role_permissions (
    role_id       INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id)       REFERENCES roles(id)       ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_roles (
    user_id    INT NOT NULL,
    role_id    INT NOT NULL,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Default Admin User (password: admin123) ────────────────────────────────
INSERT IGNORE INTO users (username, password, email, role)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@runyakitara.com', 'admin');
