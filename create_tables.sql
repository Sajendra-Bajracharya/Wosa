-- SQL script to create tables for the Wosa e-commerce checkout system
-- Run this script in your MySQL database 'testing'

-- Create order_manager table to store order information
CREATE TABLE IF NOT EXISTS `order_manager` (
  `Order_Id` INT(11) NOT NULL AUTO_INCREMENT,
  `Full_Name` VARCHAR(100) NOT NULL,
  `Phone_No` VARCHAR(20) NOT NULL,
  `Address` VARCHAR(255) NOT NULL,
  `Pay_Mode` VARCHAR(50) NOT NULL,
  `Order_Date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Order_Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create user_orders table to store individual items in each order
CREATE TABLE IF NOT EXISTS `user_orders` (
  `Id` INT(11) NOT NULL AUTO_INCREMENT,
  `Order_Id` INT(11) NOT NULL,
  `Item_Name` VARCHAR(100) NOT NULL,
  `Price` INT(11) NOT NULL,
  `Quantity` INT(11) NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `Order_Id` (`Order_Id`),
  CONSTRAINT `user_orders_ibfk_1` FOREIGN KEY (`Order_Id`) REFERENCES `order_manager` (`Order_Id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS user_manager;

-- Create user_manager table with all signup fields
CREATE TABLE user_manager (
  id INT(11) NOT NULL AUTO_INCREMENT,
  username VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  phone_number VARCHAR(20) NOT NULL,
  address TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY unique_username (username),
  UNIQUE KEY unique_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    category ENUM('men', 'women', 'accessories') NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT,
    image_path VARCHAR(255),
    stock_quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample men's products
INSERT INTO products (product_name, category, price, description, image_path, stock_quantity) VALUES
('Bomber jacket', 'men', 2000.00, 'Stylish bomber jacket for men', 'img/4.png', 50),
('Cotton jacket', 'men', 3000.00, 'Premium cotton jacket', 'img/5.png', 40),
('Sweat Shirt', 'men', 1000.00, 'Comfortable sweat shirt', 'img/3.png', 60),
('Denim jacket', 'men', 2500.00, 'Classic denim jacket', 'img/6.png', 45),
('Camo trousers', 'men', 1500.00, 'Camouflage trousers', 'img/7.png', 55),
('Slim fit chinos', 'men', 1200.00, 'Slim fit chino pants', 'img/8.png', 70),
('Track trouser', 'men', 1500.00, 'Athletic track trousers', 'img/9.png', 65),
('Alex Fit Trousers', 'men', 1800.00, 'Premium fit trousers', 'img/10.png', 50),
('Sport jacket', 'men', 2500.00, 'Sports style jacket', 'img/15.png', 40),
('Barrowland jacket', 'men', 3000.00, 'Designer Barrowland jacket', 'img/16.png', 35),
('Padded jacket', 'men', 3500.00, 'Warm padded jacket', 'img/17.png', 30),
('Chest patch jacket', 'men', 4000.00, 'Jacket with chest patch', 'img/18.png', 25),
('Straight-leg jeans', 'men', 1900.00, 'Classic straight leg jeans', 'img/11.png', 60),
('Straight-leg trousers', 'men', 1500.00, 'Straight leg trousers', 'img/12.png', 55),
('Distressed-effect jeans', 'men', 1600.00, 'Trendy distressed jeans', 'img/13.png', 50);

-- Insert sample women's products
INSERT INTO products (product_name, category, price, description, image_path, stock_quantity) VALUES
('Cherry Hoodie', 'women', 2000.00, 'Stylish cherry hoodie', 'gimg/1.png', 45),
('Cherry sweatshirt', 'women', 1000.00, 'Comfortable cherry sweatshirt', 'gimg/2.png', 55),
('GG knit dress', 'women', 1400.00, 'Elegant GG knit dress', 'gimg/3.png', 40),
('Cardigan', 'women', 2300.00, 'Cozy cardigan', 'gimg/4.png', 50),
('Puffer jacket', 'women', 3500.00, 'Warm puffer jacket', 'gimg/5.png', 35),
('Zipped track top', 'women', 1500.00, 'Athletic track top', 'gimg/6.png', 60),
('Cashmere wool jumper', 'women', 2000.00, 'Luxury cashmere jumper', 'gimg/7.png', 30),
('V neck cardigan', 'women', 1800.00, 'Elegant V neck cardigan', 'gimg/8.png', 45),
('High waisted skirt', 'women', 1000.00, 'Trendy high waisted skirt', 'gimg/9.png', 65),
('Front button skirt', 'women', 1500.00, 'Classic button skirt', 'gimg/10.png', 50),
('Denim skirt', 'women', 1500.00, 'Casual denim skirt', 'gimg/11.png', 55),
('Track pants', 'women', 2000.00, 'Comfortable track pants', 'gimg/12.png', 60),
('Ribcage jeans', 'women', 1700.00, 'High rise ribcage jeans', 'gimg/13.png', 50),
('Straight leg track pants', 'women', 2000.00, 'Athletic straight leg pants', 'gimg/14.png', 45),
('Yellow track pants', 'women', 1800.00, 'Vibrant yellow track pants', 'gimg/15.png', 40),
('Wide-leg denim jeans', 'women', 2500.00, 'Trendy wide leg jeans', 'gimg/16.png', 35);


ALTER TABLE order_manager 
   ADD COLUMN order_status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') 
   DEFAULT 'pending' AFTER Paid_Amount;