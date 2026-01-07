-- ---------------------------------------------------------
-- ELECTRONICS SHOWROOM DB - HIGH PERFORMANCE EDITION
-- Optimized for AI Chatbots, Search, and Filtering
-- ---------------------------------------------------------

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

DROP DATABASE IF EXISTS `electronics_showroom`;
CREATE DATABASE `electronics_showroom` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `electronics_showroom`;

-- =========================================================
-- 1. OPTIMIZED TABLE STRUCTURES
-- =========================================================

-- A. Locations (Normalized for validation speed)
CREATE TABLE `states` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(10) UNIQUE
) ENGINE=InnoDB;

CREATE TABLE `districts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `state_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  FOREIGN KEY (`state_id`) REFERENCES `states`(`id`),
  INDEX `idx_state_dist` (`state_id`) -- Optimization for State->District lookup
) ENGINE=InnoDB;

CREATE TABLE `cities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `district_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  FOREIGN KEY (`district_id`) REFERENCES `districts`(`id`),
  INDEX `idx_dist_city` (`district_id`)
) ENGINE=InnoDB;

CREATE TABLE `pincodes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `city_id` INT NOT NULL,
  `pincode` VARCHAR(6) NOT NULL UNIQUE,
  FOREIGN KEY (`city_id`) REFERENCES `cities`(`id`),
  INDEX `idx_pincode` (`pincode`) -- Fast address validation
) ENGINE=InnoDB;

-- B. Users (Optimized for Auth & Lookup)
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50),
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `phone` VARCHAR(15) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  -- Indexes for fast Login/Identity Resolution
  INDEX `idx_email` (`email`),
  INDEX `idx_phone` (`phone`)
) ENGINE=InnoDB;

CREATE TABLE `user_addresses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `address_line1` VARCHAR(255) NOT NULL,
  `city_id` INT NOT NULL,
  `pincode_id` INT NOT NULL,
  `is_default` BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB;

-- C. Inventory (Heavily Indexed for Search)
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name_en` VARCHAR(100) NOT NULL,
  `name_hi` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) UNIQUE
) ENGINE=InnoDB;

CREATE TABLE `attributes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name_en` VARCHAR(50) NOT NULL,
  `name_hi` VARCHAR(50),
  `data_type` ENUM('text', 'number', 'select') DEFAULT 'text'
) ENGINE=InnoDB;

CREATE TABLE `category_attributes` (
  `category_id` INT NOT NULL,
  `attribute_id` INT NOT NULL,
  PRIMARY KEY (`category_id`, `attribute_id`),
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`),
  FOREIGN KEY (`attribute_id`) REFERENCES `attributes`(`id`)
) ENGINE=InnoDB;

CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `sku` VARCHAR(50) UNIQUE NOT NULL,
  `brand` VARCHAR(50),
  `name_en` VARCHAR(255) NOT NULL,
  `name_hi` VARCHAR(255),
  `description_en` TEXT,
  `description_hi` TEXT,
  `price` DECIMAL(10,2) NOT NULL,
  `stock_quantity` INT DEFAULT 0,
  `image_url` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`),
  
  -- ** AI SEARCH OPTIMIZATIONS **
  -- 1. Full Text for Keyword Search (e.g. "Gaming Laptop")
  FULLTEXT KEY `ft_search_en` (`name_en`, `description_en`, `brand`),
  -- 2. Ngram Parser for Hindi Search (e.g. "सस्ता फोन")
  FULLTEXT KEY `ft_search_hi` (`name_hi`, `description_hi`) WITH PARSER ngram,
  -- 3. Composite Index for Filtering (e.g. "Smartphones under 20k")
  INDEX `idx_cat_price` (`category_id`, `price`),
  -- 4. Brand Filtering
  INDEX `idx_brand` (`brand`)
) ENGINE=InnoDB;

CREATE TABLE `product_attribute_values` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `attribute_id` INT NOT NULL,
  `value_en` VARCHAR(100),
  `value_hi` VARCHAR(100),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`),
  FOREIGN KEY (`attribute_id`) REFERENCES `attributes`(`id`),
  -- ** FILTER OPTIMIZATION **
  -- Fast lookup for "Show me 8GB RAM phones"
  INDEX `idx_attr_val` (`attribute_id`, `value_en`) 
) ENGINE=InnoDB;

-- D. Orders (Optimized for History Retrieval)
CREATE TABLE `carts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT,
  `session_id` VARCHAR(100),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_cart_user` (`user_id`)
) ENGINE=InnoDB;

CREATE TABLE `cart_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `cart_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT DEFAULT 1,
  FOREIGN KEY (`cart_id`) REFERENCES `carts`(`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB;

CREATE TABLE `order_statuses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `status_name` VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `order_status_id` INT NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `shipping_address_id` INT,
  `order_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
  FOREIGN KEY (`order_status_id`) REFERENCES `order_statuses`(`id`),
  -- ** HISTORY OPTIMIZATION **
  -- Fast retrieval of "My recent orders"
  INDEX `idx_user_date` (`user_id`, `order_date`)
) ENGINE=InnoDB;

CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `price_at_purchase` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB;

CREATE TABLE `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `payment_method` VARCHAR(50),
  `transaction_id` VARCHAR(100),
  `status` VARCHAR(20),
  `amount` DECIMAL(10,2),
  `payment_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`),
  INDEX `idx_txn_id` (`transaction_id`) -- Fast payment tracking
) ENGINE=InnoDB;

-- =========================================================
-- 2. SETUP DATA GENERATION ENGINE
-- =========================================================
DROP TABLE IF EXISTS `helper_seq`;
CREATE TABLE `helper_seq` (n INT);
INSERT INTO `helper_seq` VALUES (0),(1),(2),(3),(4),(5),(6),(7),(8),(9);

-- =========================================================
-- 3. STATIC DATA INJECTION
-- =========================================================

-- Locations
INSERT INTO `states` (`name`, `code`) VALUES ('Maharashtra', 'MH'), ('Delhi', 'DL'), ('Karnataka', 'KA'), ('Tamil Nadu', 'TN'), ('Telangana', 'TG');
INSERT INTO `districts` (`state_id`, `name`) VALUES (1, 'Mumbai'), (1, 'Pune'), (2, 'New Delhi'), (3, 'Bengaluru'), (4, 'Chennai'), (5, 'Hyderabad');
INSERT INTO `cities` (`district_id`, `name`) VALUES (1, 'Mumbai'), (2, 'Pune'), (3, 'Delhi'), (4, 'Bengaluru'), (5, 'Chennai'), (6, 'Hyderabad');
INSERT INTO `pincodes` (`city_id`, `pincode`) VALUES (1, '400001'), (2, '411001'), (3, '110001'), (4, '560001'), (5, '600001'), (6, '500001');

-- Categories
INSERT INTO `categories` (`name_en`, `name_hi`, `slug`) VALUES
('Smartphones', 'स्मार्टफोन', 'smartphones'),
('Laptops', 'लैपटॉप', 'laptops'),
('Smart TV', 'स्मार्ट टीवी', 'smart-tv'),
('Headphones', 'हेडफोन', 'headphones'),
('Smartwatches', 'स्मार्टवॉच', 'smartwatches');

-- Attributes Master
INSERT INTO `attributes` (`name_en`, `name_hi`, `data_type`) VALUES 
('Color', 'रंग', 'text'), ('Storage', 'स्टोरेज', 'text'), ('RAM', 'रैम', 'text'), ('Screen Size', 'स्क्रीन', 'text');

-- Link Categories to Attributes
INSERT INTO `category_attributes` (`category_id`, `attribute_id`) VALUES
(1,1),(1,2),(1,3), -- Phones: Color, Storage, RAM
(2,2),(2,3),(2,4), -- Laptops: Storage, RAM, Screen
(3,4);             -- TV: Screen

INSERT INTO `order_statuses` (`status_name`) VALUES ('Pending'), ('Confirmed'), ('Shipped'), ('Delivered'), ('Cancelled');

-- =========================================================
-- 4. REALISTIC MASS DATA GENERATION
-- =========================================================

-- A. Users (200 Entries)
INSERT INTO `users` (`first_name`, `last_name`, `email`, `phone`, `password_hash`)
SELECT 
  ELT(FLOOR(1 + (RAND() * 10)), 'Rahul', 'Aditya', 'Rohan', 'Karthik', 'Amit', 'Priya', 'Sneha', 'Ananya', 'Divya', 'Kavya'),
  ELT(FLOOR(1 + (RAND() * 10)), 'Sharma', 'Patel', 'Iyer', 'Singh', 'Gupta', 'Reddy', 'Kumar', 'Verma', 'Mehta', 'Jain'),
  CONCAT('user.', (d1.n*100 + d2.n*10 + d3.n), '.', FLOOR(RAND()*999), '@gmail.com'),
  CONCAT('9', FLOOR(800000000 + RAND() * 100000000)),
  '$2y$10$X7.G1...' -- Dummy Hash
FROM `helper_seq` d1, `helper_seq` d2, `helper_seq` d3
WHERE (d1.n*100 + d2.n*10 + d3.n) < 200;

INSERT INTO `user_addresses` (`user_id`, `address_line1`, `city_id`, `pincode_id`, `is_default`)
SELECT id, CONCAT('Flat ', FLOOR(1+RAND()*100), ', Tower ', ELT(FLOOR(1+RAND()*3), 'A','B','C')), FLOOR(1+RAND()*6), FLOOR(1+RAND()*6), 1 FROM `users`;

-- B. Products (5000 Entries)
-- Optimized Logic: Generates real brands/names based on Category ID
INSERT INTO `products` (`category_id`, `sku`, `brand`, `name_en`, `name_hi`, `description_en`, `description_hi`, `price`, `stock_quantity`)
SELECT 
  FLOOR(1 + (RAND() * 5)) as cat_id,
  CONCAT('SKU-', (d1.n*1000 + d2.n*100 + d3.n*10 + d4.n)),
  
  -- Brand Logic
  CASE 
     WHEN FLOOR(1 + (RAND() * 5)) = 1 THEN ELT(FLOOR(1+RAND()*3), 'Apple', 'Samsung', 'Xiaomi') 
     WHEN FLOOR(1 + (RAND() * 5)) = 2 THEN ELT(FLOOR(1+RAND()*3), 'Dell', 'HP', 'Lenovo')
     WHEN FLOOR(1 + (RAND() * 5)) = 3 THEN ELT(FLOOR(1+RAND()*3), 'Sony', 'LG', 'Samsung')
     ELSE 'Boat'
  END,

  -- Name Logic (English)
  CONCAT(
    CASE 
       WHEN FLOOR(1 + (RAND() * 5)) = 1 THEN ELT(FLOOR(1+RAND()*3), 'iPhone 15', 'Galaxy S24', 'Note 13 Pro')
       WHEN FLOOR(1 + (RAND() * 5)) = 2 THEN ELT(FLOOR(1+RAND()*3), 'XPS 13', 'Pavilion', 'ThinkPad')
       ELSE 'Smart Device'
    END, ' - ', (d1.n*1000 + d2.n*100 + d3.n*10 + d4.n)
  ),

  -- Name Logic (Hindi)
  CONCAT(
    CASE 
       WHEN FLOOR(1 + (RAND() * 5)) = 1 THEN ELT(FLOOR(1+RAND()*3), 'आईफोन 15', 'गैलेक्सी S24', 'नोट 13 प्रो')
       WHEN FLOOR(1 + (RAND() * 5)) = 2 THEN ELT(FLOOR(1+RAND()*3), 'एक्सपीएस 13', 'पविलियन', 'थिंकपैड')
       ELSE 'स्मार्ट डिवाइस'
    END, ' - ', (d1.n*1000 + d2.n*100 + d3.n*10 + d4.n)
  ),
  'Flagship product with 1 year manufacturer warranty. Best in class performance.',
  '1 साल की निर्माता वारंटी के साथ प्रमुख उत्पाद। बेहतरीन प्रदर्शन।',
  ROUND(2000 + (RAND() * 95000), 2),
  FLOOR(RAND() * 100)
FROM `helper_seq` d1, `helper_seq` d2, `helper_seq` d3, `helper_seq` d4
WHERE (d1.n*1000 + d2.n*100 + d3.n*10 + d4.n) < 5000;

-- C. Assign Attributes (Smart Mapping)
-- Only assign RAM (Attr ID 3) to Phones & Laptops
INSERT INTO `product_attribute_values` (`product_id`, `attribute_id`, `value_en`, `value_hi`)
SELECT id, 3, ELT(FLOOR(1+RAND()*3), '8GB', '16GB', '32GB'), ELT(FLOOR(1+RAND()*3), '8जीबी', '16जीबी', '32जीबी')
FROM `products` WHERE category_id IN (1, 2) LIMIT 2000;

-- D. Orders (500 Entries)
INSERT INTO `orders` (`user_id`, `order_status_id`, `total_amount`, `shipping_address_id`, `order_date`)
SELECT 
  (SELECT id FROM users ORDER BY RAND() LIMIT 1),
  ELT(FLOOR(1+RAND()*4), 1, 2, 3, 4),
  0,
  1,
  DATE_SUB(NOW(), INTERVAL FLOOR(RAND()*365) DAY)
FROM `helper_seq` d1, `helper_seq` d2, `helper_seq` d3
WHERE (d1.n*100 + d2.n*10 + d3.n) < 500;

-- Link correct address to order
UPDATE `orders` o JOIN `user_addresses` ua ON o.user_id = ua.user_id SET o.shipping_address_id = ua.id;

-- Add Order Items
INSERT INTO `order_items` (`order_id`, `product_id`, `quantity`, `price_at_purchase`)
SELECT o.id, (SELECT id FROM products ORDER BY RAND() LIMIT 1), 1, 0 FROM `orders` o;

-- Calculate Totals
UPDATE `order_items` oi JOIN `products` p ON oi.product_id = p.id SET oi.price_at_purchase = p.price;
UPDATE `orders` o JOIN `order_items` oi ON o.id = oi.order_id SET o.total_amount = oi.price_at_purchase;

-- Payments
INSERT INTO `payments` (`order_id`, `payment_method`, `transaction_id`, `status`, `amount`, `payment_date`)
SELECT id, 'UPI', CONCAT('TXN', FLOOR(RAND()*999999)), 'Completed', total_amount, order_date FROM `orders`;

-- Cleanup
DROP TABLE `helper_seq`;
SET FOREIGN_KEY_CHECKS = 1;