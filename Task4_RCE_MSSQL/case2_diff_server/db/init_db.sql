-- Task 4 Case 2: Initialize MSSQL for remote RCE testing

USE master;

-- Enable advanced options
EXEC sp_configure 'show advanced options', 1;
RECONFIGURE;

-- Enable xp_cmdshell
EXEC sp_configure 'xp_cmdshell', 1;
RECONFIGURE;

-- Create test database
IF NOT EXISTS (SELECT name FROM sys.databases WHERE name = 'TestDB')
BEGIN
    CREATE DATABASE TestDB;
END

-- Switch to TestDB
USE TestDB;

-- Create Users table
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'Users')
BEGIN
    CREATE TABLE Users (
        ID INT PRIMARY KEY IDENTITY(1,1),
        Username NVARCHAR(100),
        Email NVARCHAR(100),
        Password NVARCHAR(255),
        AdminLevel INT
    );
END

-- Insert sample data
INSERT INTO Users (Username, Email, Password, AdminLevel) VALUES
(N'Admin', N'admin@company.com', N'hashedpass123', 99),
(N'User1', N'user1@company.com', N'hashedpass456', 1),
(N'User2', N'user2@company.com', N'hashedpass789', 1);

-- Grant execute permissions on xp_cmdshell to public
GRANT EXEC ON master..xp_cmdshell TO public;

-- Switch back to master
USE master;

PRINT 'MSSQL Server initialized for RCE testing'
PRINT 'xp_cmdshell is ENABLED'
PRINT 'TestDB created with Users table'
