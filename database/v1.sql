-- Database: carlyn_cake_shop

CREATE DATABASE IF NOT EXISTS carlyn_cake_shop;
USE carlyn_cake_shop;

-- Users Table
CREATE TABLE IF NOT EXISTS Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE IF NOT EXISTS Products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('fondant', 'semifondant', 'icing') NOT NULL,
    base_price DECIMAL(10, 2) NOT NULL,
    max_tiers INT NOT NULL
);

-- Customizations Table
CREATE TABLE IF NOT EXISTS Customizations (
    customization_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    tiers INT CHECK (tiers BETWEEN 1 AND 5),
    size_in_inches INT NOT NULL,
    flavor ENUM('Chocolate', 'Vanilla', 'Chiffon', 'Red Velvet') NOT NULL,
    message VARCHAR(255) CHECK (CHAR_LENGTH(message) <= 7),
    specific_instructions TEXT,
    reference_images TEXT,
    add_ons TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (product_id) REFERENCES Products(product_id)
);

-- Orders Table
CREATE TABLE IF NOT EXISTS Orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_price DECIMAL(10, 2) NOT NULL,
    delivery_fee DECIMAL(10, 2),
    order_status ENUM('Pending', 'In Progress', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    order_date DATETIME,
    pickup_or_delivery ENUM('Pickup', 'Delivery') NOT NULL,
    contact_number VARCHAR(15),
    delivery_address TEXT,
    delivery_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);

-- Ingredients Table
CREATE TABLE IF NOT EXISTS Ingredients (
    ingredient_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    ingredient_name VARCHAR(100) NOT NULL,
    quantity VARCHAR(50) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES Products(product_id)
);

-- Admin Table
CREATE TABLE IF NOT EXISTS Admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Communication Table
CREATE TABLE IF NOT EXISTS Communication (
    comm_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    admin_id INT,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (admin_id) REFERENCES Admins(admin_id)
);

-- Sales Table
CREATE TABLE IF NOT EXISTS Sales (
    sale_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    sale_amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('Cash', 'GCash') NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES Orders(order_id)
);

-- Order Calendar Table
CREATE TABLE IF NOT EXISTS Order_Calendar (
    calendar_id INT AUTO_INCREMENT PRIMARY KEY,
    order_date DATE NOT NULL UNIQUE,
    order_limit INT NOT NULL,
    orders_count INT DEFAULT 0
);

-- Insert sample admin account
-- Note: Replace 'your_hashed_password' with an actual hashed password
INSERT INTO Admins (email, password) VALUES
('admin@example.com', 'your_hashed_password');  -- Use password_hash() in PHP to generate the hashed password.
