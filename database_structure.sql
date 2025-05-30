-- Create database
CREATE DATABASE IF NOT EXISTS mini_erp DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE mini_erp;

-- Table: products
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image_path VARCHAR(255), -- Caminho da imagem do produto
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: stock
CREATE TABLE stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    variation VARCHAR(255),
    quantity INT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Table: orders
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    total DECIMAL(10,2),
    shipping DECIMAL(10,2),
    status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    zip_code VARCHAR(9),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: coupons
CREATE TABLE coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount DECIMAL(10,2),
    min_value DECIMAL(10,2),
    valid_until DATE
);