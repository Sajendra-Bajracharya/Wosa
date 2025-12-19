<?php
/**
 * Database Setup Script for Wosa E-commerce
 * Run this file once to create the necessary tables
 * Access it via browser: http://localhost/Wosa/setup_database.php
 */

// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "testing";

// Connect to MySQL server
$conn = mysqli_connect($host, $username, $password);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database if it doesn't exist
$sql_create_db = "CREATE DATABASE IF NOT EXISTS `$database`";
if (mysqli_query($conn, $sql_create_db)) {
    echo "Database '$database' checked/created successfully.<br>";
} else {
    die("Error creating database: " . mysqli_error($conn));
}

// Select the database
mysqli_select_db($conn, $database);

// Create order_manager table
$sql_order_manager = "CREATE TABLE IF NOT EXISTS `order_manager` (
  `Order_Id` INT(11) NOT NULL AUTO_INCREMENT,
  `Full_Name` VARCHAR(100) NOT NULL,
  `Phone_No` VARCHAR(20) NOT NULL,
  `Address` VARCHAR(255) NOT NULL,
  `Pay_Mode` VARCHAR(50) NOT NULL,
  `Order_Date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Order_Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($conn, $sql_order_manager)) {
    echo "Table 'order_manager' created successfully.<br>";
} else {
    echo "Error creating table order_manager: " . mysqli_error($conn) . "<br>";
}

// Create user_orders table
$sql_user_orders = "CREATE TABLE IF NOT EXISTS `user_orders` (
  `Id` INT(11) NOT NULL AUTO_INCREMENT,
  `Order_Id` INT(11) NOT NULL,
  `Item_Name` VARCHAR(100) NOT NULL,
  `Price` INT(11) NOT NULL,
  `Quantity` INT(11) NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `Order_Id` (`Order_Id`),
  CONSTRAINT `user_orders_ibfk_1` FOREIGN KEY (`Order_Id`) REFERENCES `order_manager` (`Order_Id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($conn, $sql_user_orders)) {
    echo "Table 'user_orders' created successfully.<br>";
} else {
    echo "Error creating table user_orders: " . mysqli_error($conn) . "<br>";
}

mysqli_close($conn);

echo "<br><strong>Database setup completed!</strong><br>";
echo "<a href='index.html'>Go to Home Page</a>";

?>

