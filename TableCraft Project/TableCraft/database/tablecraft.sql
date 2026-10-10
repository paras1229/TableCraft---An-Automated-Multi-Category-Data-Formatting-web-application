-- =====================================================================
-- TableCraft - Automated Multi Category Data Formatting Web Application
-- Database: tablecraft
-- Import this file in phpMyAdmin (WampServer) before running the site.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS tablecraft CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tablecraft;

-- ---------------------------------------------------------------------
-- 1. USERS  (Login / Register)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100)  NOT NULL,
    email         VARCHAR(150)  NOT NULL UNIQUE,
    password      VARCHAR(255)  NOT NULL,           -- stored with password_hash()
    avatar_color  VARCHAR(7)    DEFAULT '#1F50FF',  -- used for the initials avatar
    created_at    DATETIME      DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. DATA_TABLES  (every table a user creates, in every category)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS data_tables (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    category      VARCHAR(50)  NOT NULL,   -- student_record, shop_order, employee_info,
                                            -- library_books, product_stock, commercial, custom
    title         VARCHAR(150) NOT NULL,
    theme_mode    ENUM('bright','dark') DEFAULT 'bright',
    accent_color  VARCHAR(7)   DEFAULT '#1F50FF',
    font_family   VARCHAR(50)  DEFAULT 'Figtree',
    is_commercial TINYINT(1)   DEFAULT 0,   -- enables qty*price / tax / total auto-calculation
    created_at    DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. TABLE_COLUMNS  (dynamic column definitions per table)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS table_columns (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    table_id      INT NOT NULL,
    column_name   VARCHAR(100) NOT NULL,
    column_type   ENUM('text','number','date','currency') DEFAULT 'text',
    column_order  INT DEFAULT 0,
    is_formula    TINYINT(1) DEFAULT 0,     -- e.g. Total = Qty * Price for commercial tables
    FOREIGN KEY (table_id) REFERENCES data_tables(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. TABLE_ROWS  (one row per record)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS table_rows (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    table_id      INT NOT NULL,
    row_order     INT DEFAULT 0,
    FOREIGN KEY (table_id) REFERENCES data_tables(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. TABLE_CELLS  (the actual typed data — column x row intersection)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS table_cells (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    row_id        INT NOT NULL,
    column_id     INT NOT NULL,
    cell_value    TEXT,
    FOREIGN KEY (row_id) REFERENCES table_rows(id) ON DELETE CASCADE,
    FOREIGN KEY (column_id) REFERENCES table_columns(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Demo account so the college project can be shown immediately
-- password is: Demo@123
-- ---------------------------------------------------------------------
INSERT INTO users (full_name, email, password, avatar_color) VALUES
('Demo User', 'demo@tablecraft.com', '$2b$10$GwNfOiVWVPjpaHTynDJe9uEcHpP/1kHc/PI1FFDRuQj4wJmD5G2b6', '#1F50FF');
-- Login with email: demo@tablecraft.com   password: Demo@123
-- (You can also just click "Register" and create your own account.)
