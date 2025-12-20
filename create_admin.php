<?php
/**
 * Complete Database Setup Script
 * Run this file ONCE to create all necessary tables
 * Access: http://localhost/Wosa/create_admin.php
 */

$db = mysqli_connect("localhost", "root", "", "testing", 3307);

if (!$db) {
    die("Database connection failed: " . mysqli_connect_error());
}

echo "<style>
    body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
    h2 { color: #2c3e50; }
    h3 { color: #34495e; margin-top: 20px; }
    .success { color: green; }
    .error { color: red; }
    .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0; }
    code { background: #ecf0f1; padding: 2px 6px; border-radius: 3px; }
    hr { margin: 30px 0; border: none; border-top: 2px solid #bdc3c7; }
    .btn { display: inline-block; padding: 12px 24px; background: #3b82f6; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
    .btn:hover { background: #1e3a8a; }
</style>";

echo "<h2>🚀 Complete Database Setup</h2>";
echo "<hr>";

// ==================== STEP 1: Create admin_users table ====================
echo "<h3>Step 1: Creating admin_users table...</h3>";

$create_admin_table = "CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($db, $create_admin_table)) {
    echo "<p class='success'>✅ Table 'admin_users' created successfully.</p>";
} else {
    echo "<p class='error'>❌ Error creating admin_users table: " . mysqli_error($db) . "</p>";
}

// ==================== STEP 2: Create order_manager table ====================
echo "<h3>Step 2: Creating order_manager table...</h3>";

$create_order_manager = "CREATE TABLE IF NOT EXISTS `order_manager` (
  `Order_Id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) DEFAULT NULL,
  `Full_Name` VARCHAR(100) NOT NULL,
  `Phone_No` VARCHAR(20) NOT NULL,
  `Address` VARCHAR(255) NOT NULL,
  `Pay_Mode` VARCHAR(50) NOT NULL,
  `Paid_Amount` INT(11) DEFAULT 0,
  `Order_Date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Order_Id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($db, $create_order_manager)) {
    echo "<p class='success'>✅ Table 'order_manager' created successfully.</p>";
} else {
    echo "<p class='error'>❌ Error creating order_manager table: " . mysqli_error($db) . "</p>";
}

// ==================== STEP 3: Create user_orders table ====================
echo "<h3>Step 3: Creating user_orders table...</h3>";

$create_user_orders = "CREATE TABLE IF NOT EXISTS `user_orders` (
  `Id` INT(11) NOT NULL AUTO_INCREMENT,
  `Order_Id` INT(11) NOT NULL,
  `Item_Name` VARCHAR(100) NOT NULL,
  `Price` INT(11) NOT NULL,
  `Quantity` INT(11) NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `Order_Id` (`Order_Id`),
  CONSTRAINT `user_orders_ibfk_1` FOREIGN KEY (`Order_Id`) REFERENCES `order_manager` (`Order_Id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($db, $create_user_orders)) {
    echo "<p class='success'>✅ Table 'user_orders' created successfully.</p>";
} else {
    echo "<p class='error'>❌ Error creating user_orders table: " . mysqli_error($db) . "</p>";
}

// ==================== STEP 4: Check/Create user_manager table ====================
echo "<h3>Step 4: Verifying user_manager table...</h3>";

$check_user_manager = "SHOW TABLES LIKE 'user_manager'";
$result = mysqli_query($db, $check_user_manager);

if (mysqli_num_rows($result) > 0) {
    echo "<p class='success'>✅ Table 'user_manager' already exists.</p>";
} else {
    echo "<p class='info'>⚠️ Table 'user_manager' not found. Creating it now...</p>";
    
    $create_user_manager = "CREATE TABLE `user_manager` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `username` VARCHAR(100) NOT NULL,
      `email` VARCHAR(100) NOT NULL,
      `password` VARCHAR(255) NOT NULL,
      `phone_number` VARCHAR(20) NOT NULL,
      `address` TEXT NOT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_username` (`username`),
      UNIQUE KEY `unique_email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (mysqli_query($db, $create_user_manager)) {
        echo "<p class='success'>✅ Table 'user_manager' created successfully.</p>";
    } else {
        echo "<p class='error'>❌ Error creating user_manager: " . mysqli_error($db) . "</p>";
    }
}

echo "<br><hr>";

// ==================== STEP 5: Create Admin User ====================
echo "<h3>Step 5: Creating admin user...</h3>";

// Check if admin already exists
$check_admin = "SELECT * FROM admin_users WHERE username = 'admin'";
$result = mysqli_query($db, $check_admin);

if (mysqli_num_rows($result) > 0) {
    echo "<p class='info'>⚠️ Admin user already exists. Deleting old admin...</p>";
    mysqli_query($db, "DELETE FROM admin_users WHERE username = 'admin'");
    echo "<p class='success'>✅ Old admin deleted.</p>";
}

// Create new admin user
$username = 'admin';
$email = 'admin@wosa.com';
$password = 'admin123';

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

echo "<div class='info'>";
echo "<strong>Admin Credentials:</strong><br>";
echo "Username: <code>$username</code><br>";
echo "Email: <code>$email</code><br>";
echo "Password: <code>$password</code><br>";
echo "Hashed: <code style='font-size:0.8em;'>" . substr($hashed_password, 0, 50) . "...</code>";
echo "</div>";

// Insert admin user
$insert_admin = "INSERT INTO admin_users (username, email, password) 
                 VALUES ('$username', '$email', '$hashed_password')";

if (mysqli_query($db, $insert_admin)) {
    echo "<p class='success'>✅ Admin user created successfully!</p>";
} else {
    echo "<p class='error'>❌ Error creating admin: " . mysqli_error($db) . "</p>";
}

// ==================== STEP 6: Verify Admin ====================
echo "<h3>Step 6: Verifying admin user...</h3>";

$verify_query = "SELECT * FROM admin_users WHERE username = 'admin'";
$verify_result = mysqli_query($db, $verify_query);

if (mysqli_num_rows($verify_result) > 0) {
    $admin = mysqli_fetch_assoc($verify_result);
    echo "<p class='success'>✅ Admin user verified in database!</p>";
    echo "<div class='info'>";
    echo "ID: " . $admin['id'] . "<br>";
    echo "Username: " . $admin['username'] . "<br>";
    echo "Email: " . $admin['email'] . "<br>";
    echo "Created: " . $admin['created_at'];
    echo "</div>";
    
    // Test password verification
    echo "<h4>Testing password verification:</h4>";
    if (password_verify('admin123', $admin['password'])) {
        echo "<p class='success'>✅ Password verification works correctly!</p>";
    } else {
        echo "<p class='error'>❌ Password verification failed!</p>";
    }
} else {
    echo "<p class='error'>❌ Admin user not found in database!</p>";
}

// ==================== STEP 7: Show all tables ====================
echo "<br><hr>";
echo "<h3>Step 7: Database Tables Summary</h3>";

$show_tables = "SHOW TABLES";
$tables_result = mysqli_query($db, $show_tables);

echo "<div class='info'>";
echo "<strong>Tables in 'testing' database:</strong><ul>";
while ($table = mysqli_fetch_array($tables_result)) {
    echo "<li>" . $table[0] . "</li>";
}
echo "</ul></div>";

mysqli_close($db);

// ==================== Final Summary ====================
echo "<hr>";
echo "<h2>✅ Setup Complete!</h2>";

echo "<div class='info'>";
echo "<h3>📋 Admin Login Credentials:</h3>";
echo "<ul>";
echo "<li><strong>Username:</strong> admin</li>";
echo "<li><strong>Password:</strong> admin123</li>";
echo "<li><strong>Login URL:</strong> <a href='admin_login.php'>admin_login.php</a></li>";
echo "</ul>";

echo "<h3>📦 Tables Created:</h3>";
echo "<ul>";
echo "<li>✅ admin_users</li>";
echo "<li>✅ order_manager</li>";
echo "<li>✅ user_orders</li>";
echo "<li>✅ user_manager (if not existed)</li>";
echo "</ul>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='admin_login.php' class='btn'>🔐 Go to Admin Login</a>";
echo "<a href='login.php' class='btn'>👤 Go to User Login</a>";
echo "<a href='index.html' class='btn'>🏠 Go to Homepage</a>";
echo "</div>";

echo "<br><br>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; text-align: center;'>";
echo "<strong>⚠️ IMPORTANT SECURITY NOTE:</strong><br>";
echo "Please delete this file (create_admin.php) after setup for security!";
echo "</div>";
?>