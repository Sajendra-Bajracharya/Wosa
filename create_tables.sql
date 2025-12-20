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