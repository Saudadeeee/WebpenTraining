

USE rce_file_db;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100),
    email VARCHAR(100),
    password VARCHAR(255),
    is_admin BOOLEAN
);

INSERT INTO users (username, email, password, is_admin) VALUES
('admin', 'admin@company.com', 'admin123', TRUE),
('user1', 'user1@company.com', 'password123', FALSE),
('user2', 'user2@company.com', 'password456', FALSE);

CREATE TABLE uploaded_files (
    id INT PRIMARY KEY AUTO_INCREMENT,
    file_name VARCHAR(255),
    file_path VARCHAR(255),
    file_size INT,
    file_type VARCHAR(50),
    upload_date DATETIME,
    content LONGTEXT
);

-- Insert sample files
INSERT INTO uploaded_files (file_name, file_path, file_size, file_type, upload_date, content) VALUES
('document.pdf', '/uploads/document.pdf', 2048, 'application/pdf', NOW(), 'Sample PDF content'),
('image.jpg', '/uploads/image.jpg', 51200, 'image/jpeg', NOW(), 'JPEG binary data'),
('config.php', '/uploads/config.php', 512, 'text/php', NOW(), '<?php // config file ?>');

-- Create products table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    description TEXT,
    price DECIMAL(10,2),
    stock INT,
    admin_secret VARCHAR(255)
);

INSERT INTO products VALUES
(1, 'Product 1', 'Description', 99.99, 100, 'FLAG{mysql_into_outfile_rce}'),
(2, 'Product 2', 'Description', 49.99, 50, 'secret_value_123');

-- Create admin table
CREATE TABLE admin_panel (
    id INT PRIMARY KEY AUTO_INCREMENT,
    panel_name VARCHAR(100),
    access_key VARCHAR(255),
    hidden_data TEXT
);

INSERT INTO admin_panel VALUES
(1, 'Main Admin', 'sk_live_secret_key_123456', 'Sensitive admin information');

-- Create indexes
CREATE INDEX idx_username ON users(username);
CREATE INDEX idx_email ON users(email);
CREATE INDEX idx_file_name ON uploaded_files(file_name);

-- Permissions (allow FILE operations)
-- Note: This is done via docker-compose with --secure-file-priv=""
