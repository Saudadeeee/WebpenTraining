-- Task 4 Case 1: RCE via SQL Injection Lab (Linux-compatible)
-- Note: xp_cmdshell is Windows-only, so we use direct RCE via web app

USE master;

-- Create test database
CREATE DATABASE rce_test;

USE rce_test;

-- Create products table
CREATE TABLE products (
    id INT PRIMARY KEY IDENTITY(1,1),
    product_name VARCHAR(100),
    price DECIMAL(10, 2),
    stock INT
);

CREATE TABLE app_config (
    config_key VARCHAR(100) PRIMARY KEY,
    config_value VARCHAR(255) NOT NULL
);

-- Insert sample data
INSERT INTO products (product_name, price, stock) VALUES
('Laptop', 999.99, 5),
('Mouse', 25.50, 20),
('Keyboard', 75.00, 15);

INSERT INTO app_config (config_key, config_value) VALUES
('diag_target', 'whoami');

PRINT 'Database initialized for RCE Lab';
PRINT 'SQL Injection endpoint: POST to /index.php with action=search&product_id=PAYLOAD';
PRINT 'Diagnostics endpoint: POST to /index.php with action=diag';
