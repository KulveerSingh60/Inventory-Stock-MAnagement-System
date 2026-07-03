STOCKSMASTER PRO - DATABASE SETUP INSTRUCTIONS
===============================================

http://localhost/phpmyadmin5.2.3/index.php?route=/database/structure&db=inventory_system

1. Open WampServer and ensure all services are running (icon should be green).
2. Go to PHPMyAdmin (usually http://localhost/phpmyadmin).
3. Create a new database named: inventory_system
4. Run the following SQL queries to create the necessary tables:

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    price DECIMAL(10, 2) DEFAULT 0.00,
    qty INT DEFAULT 0
);

CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    qty INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    qty INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    purchase_date DATE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- Initial Categories
INSERT INTO categories (name) VALUES 
('Tech Accessories'), 
('Office Supplies'), 
('Home Appliances'), 
('Tools & Hardware'), 
('Health & Wellness');

-- Initial Products
INSERT INTO products (name, category, price, qty) VALUES 
('Logitech MX Master 3S', 'Tech Accessories', 99.00, 10),
('Dell UltraSharp 27" Monitor', 'Tech Accessories', 450.00, 5),
('Anker 737 Power Bank', 'Tech Accessories', 120.00, 15),
('Keychron K2 Mechanical Keyboard', 'Tech Accessories', 85.00, 12),
('USB-C Hub (7-in-1)', 'Tech Accessories', 35.00, 20),
('A4 Premium Printer Paper', 'Office Supplies', 15.00, 100),
('Pilot G2 Gel Pens (Black)', 'Office Supplies', 12.00, 50),
('Heavy Duty Stapler', 'Office Supplies', 25.00, 15),
('Moleskine Classic Notebook', 'Office Supplies', 20.00, 30),
('Desktop File Organizer', 'Office Supplies', 30.00, 10),
('Ninja Air Fryer', 'Home Appliances', 130.00, 8),
('Breville Espresso Machine', 'Home Appliances', 600.00, 3),
('Dyson V15 Vacuum', 'Home Appliances', 700.00, 2),
('Instant Pot Multi-Cooker', 'Home Appliances', 100.00, 12),
('Philips Hue Smart Bulb', 'Home Appliances', 45.00, 25),
('DeWalt Cordless Drill', 'Tools & Hardware', 150.00, 6),
('24-Piece Screwdriver Set', 'Tools & Hardware', 20.00, 15),
('Stanley 25ft Tape Measure', 'Tools & Hardware', 12.00, 30),
('LED Work Light', 'Tools & Hardware', 40.00, 10),
('Adjustable Wrench', 'Tools & Hardware', 18.00, 20),
('Digital Thermometer', 'Health & Wellness', 25.00, 50),
('Hand Sanitizer (500ml)', 'Health & Wellness', 8.00, 100),
('N95 Face Masks (20pk)', 'Health & Wellness', 35.00, 200),
('First Aid Kit', 'Health & Wellness', 40.00, 15),
('Yoga Mat', 'Health & Wellness', 30.00, 20);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password) VALUES ('admin', 'admin123');