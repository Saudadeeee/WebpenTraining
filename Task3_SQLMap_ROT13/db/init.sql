-- Task 3: ROT13 SQL Injection Database Setup

USE rot13_db;

-- Create products table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2),
    stock INT,
    secret VARCHAR(255)
);

-- Insert sample data
INSERT INTO products (id, name, description, price, stock, secret) VALUES
(1, 'Gaming Laptop', 'High-performance laptop for gaming', 1299.99, 15, 'FLAG{rot13_sqli_bypass_successful}'),
(2, 'Wireless Mouse', 'Ergonomic wireless mouse', 29.99, 150, 'secret_mouse_2024'),
(3, 'Mechanical Keyboard', 'RGB mechanical keyboard', 89.99, 50, 'admin_password_123'),
(4, 'USB-C Hub', 'Multi-port USB-C hub', 49.99, 75, 'hub_secret_key'),
(5, 'Monitor 4K', '27 inch 4K UHD monitor', 399.99, 20, 'monitor_license_2024');

-- Create admin_users table to demonstrate data extraction
CREATE TABLE admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100),
    email VARCHAR(100),
    password_hash VARCHAR(255),
    api_key VARCHAR(255)
);

INSERT INTO admin_users (id, username, email, password_hash, api_key) VALUES
(1, 'admin', 'admin@company.com', 'hashed_password_abc123', 'dummy_test_51234567890'),
(2, 'superuser', 'super@company.com', 'hashed_password_xyz789', 'dummy_live_98765432100');
-- Create activity logs
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100),
    query TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45)
);

-- Table with sensitive data for demonstration
CREATE TABLE sensitive_data (
    id INT PRIMARY KEY AUTO_INCREMENT,
    api_key VARCHAR(255),
    database_password VARCHAR(255),
    private_key TEXT,
    access_token VARCHAR(500)
);

INSERT INTO sensitive_data VALUES
(1, 'dummy_live_dummyKey1234567890abcdef', 'OriginalDBPassword123!', 'PRIVATE_KEY_CONTENT_HERE', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9');

-- Indexes for performance
CREATE INDEX idx_name ON products(name);
CREATE INDEX idx_username ON admin_users(username);
