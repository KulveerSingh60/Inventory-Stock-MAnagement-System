-- ============================================================
-- StockMaster Pro — Inventory & Stock Management System
-- MySQL Database Schema
-- Import this file (e.g. `mysql -u root -p < db/schema.sql`)
-- then update credentials in db_config.php if needed.
-- ============================================================

CREATE DATABASE IF NOT EXISTS inventory_system
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE inventory_system;

-- ------------------------------------------------------------
-- Table: users
-- Passwords are stored as bcrypt hashes (see login.php).
-- role: 'admin' (full access) or 'staff' (no delete permissions).
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','staff') NOT NULL DEFAULT 'staff',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: categories
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL UNIQUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: products
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(150) NOT NULL,
  category VARCHAR(100) NOT NULL DEFAULT 'Uncategorized',
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  qty INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_name (name),
  KEY idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: sales
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS sales (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id INT UNSIGNED NOT NULL,
  qty INT NOT NULL,
  total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  sale_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_product (product_id),
  KEY idx_sale_date (sale_date),
  CONSTRAINT fk_sales_product FOREIGN KEY (product_id)
    REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: purchases
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS purchases (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id INT UNSIGNED NOT NULL,
  qty INT NOT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  purchase_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_product (product_id),
  KEY idx_purchase_date (purchase_date),
  CONSTRAINT fk_purchases_product FOREIGN KEY (product_id)
    REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Seed data
-- ------------------------------------------------------------
-- Default users (change passwords after first login):
--   admin:  admin / admin123   (full access)
--   staff:  staff / staff123   (no delete permissions)
-- Regenerate a new hash with: php -r "echo password_hash('yourpass', PASSWORD_DEFAULT);"
INSERT INTO users (username, password, role) VALUES
  ('admin', '$2y$10$ql/zNbius8DbosPz5Yd59O.2r/ZEjEusLvn300sLQe2tDvHHWt6Vu', 'admin'),
  ('staff', '$2y$10$TLbI8Akq/6Jv9w.12.t2qeMD3JkGiN04v0t2CeEgVFznFeaIdqySK', 'staff');

INSERT INTO categories (name) VALUES
  ('Electronics'),
  ('Accessories'),
  ('Stationery');

INSERT INTO products (name, category, price, qty) VALUES
  ('Wireless Mouse', 'Electronics', 15.99, 25),
  ('Mechanical Keyboard', 'Electronics', 59.99, 8),
  ('USB-C Cable (1m)', 'Accessories', 4.99, 120),
  ('A4 Printer Paper (500)', 'Stationery', 6.50, 40);
