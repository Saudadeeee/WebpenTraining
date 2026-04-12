-- Task 4 Case 1: Enable xp_cmdshell for RCE demonstration

-- Switch to master database
USE master;

-- Enable advanced options
sp_configure 'show advanced options', 1;
RECONFIGURE;

-- Enable xp_cmdshell
sp_configure 'xp_cmdshell', 1;
RECONFIGURE;

-- Create a test database
CREATE DATABASE rce_test;

-- Create test table
USE rce_test;
CREATE TABLE users (
    id INT PRIMARY KEY IDENTITY(1,1),
    username VARCHAR(100),
    password VARCHAR(255),
    email VARCHAR(100)
);

-- Insert sample data
INSERT INTO users (username, password, email) VALUES
('admin', 'admin123', 'admin@test.com'),
('user1', 'password1', 'user1@test.com'),
('sa', 'SuperAdmin@123', 'sa@test.com');

-- Re-enable master
USE master;

-- Information about xp_cmdshell
PRINT 'xp_cmdshell is now ENABLED for RCE testing';
PRINT 'WARNING: This is a SECURITY RISK and should not be enabled in production!';
