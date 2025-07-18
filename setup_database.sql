-- Create the database
CREATE DATABASE IF NOT EXISTS my_project;

-- Use the database
USE my_project;

-- Create the customers table
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Optional: Insert some sample data for testing
INSERT INTO customers (first_name, last_name, phone) VALUES
('John', 'Doe', '123-456-7890'),
('Jane', 'Smith', '098-765-4321'),
('Bob', 'Johnson', '555-123-4567'); 