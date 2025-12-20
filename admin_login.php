<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = array();
$db = mysqli_connect("localhost", "root", "", "testing", 3307);

if (!$db) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Admin Login Logic
if (isset($_POST['admin_login'])) {
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $password = mysqli_real_escape_string($db, $_POST['password']);
    
    if (empty($username)) array_push($errors, "Username is required");
    if (empty($password)) array_push($errors, "Password is required");
    
    if (count($errors) == 0) {
        // Check if admin_users table exists
        $table_check = mysqli_query($db, "SHOW TABLES LIKE 'admin_users'");
        
        if (mysqli_num_rows($table_check) == 0) {
            array_push($errors, "Admin table not found. Please run create_admin.php first!");
        } else {
            $query = "SELECT * FROM admin_users WHERE username='$username' LIMIT 1";
            $result = mysqli_query($db, $query);
            
            if (mysqli_num_rows($result) == 1) {
                $admin = mysqli_fetch_assoc($result);
                
                if (password_verify($password, $admin['password'])) {
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_logged_in'] = true;
                    
                    header('location: admin_dashboard.php');
                    exit();
                } else {
                    array_push($errors, "Wrong username/password combination");
                }
            } else {
                array_push($errors, "Admin user not found. Please run create_admin.php to create admin user.");
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - Wosa</title>
    <link rel="stylesheet" href="css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <script src="https://kit.fontawesome.com/144a91ca19.js" crossorigin="anonymous"></script>
    <style>
        .admin-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            padding: 20px 0;
            text-align: center;
            color: white;
        }
        
        .admin-badge-large {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        
        .img h2 {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .setup-notice {
            background: #dbeafe;
            border: 1px solid #3b82f6;
            padding: 15px;
            border-radius: 5px;
            margin: 20px auto;
            max-width: 500px;
            text-align: center;
        }
        
        .setup-notice a {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 20px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        
        .setup-notice a:hover {
            background: #1e3a8a;
        }
    </style>
</head>
<body>
    <header class="nav-section">
      <div class="logo-container">
        <img class="logo" src="images/logo.png" alt="Logo" />
      </div>
      <div class="main-nav-links">
        <ul class="main-nav-list">
          <li><a class="main-nav-link" href="index.html">Home</a></li>
          <li><a class="main-nav-link" href="aboutus.html">About</a></li>
          <li><a class="main-nav-link" href="contact.html">Contact</a></li>
        </ul>
      </div>

      <div class="nav-icon">
        <a href="login.php">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 icons">
            <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd"/>
          </svg>
        </a>
        <a href="mycart.php">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 icons">
            <path fill-rule="evenodd" d="M7.5 6v.75H5.513c-.96 0-1.764.724-1.865 1.679l-1.263 12A1.875 1.875 0 004.25 22.5h15.5a1.875 1.875 0 001.865-2.071l-1.263-12a1.875 1.875 0 00-1.865-1.679H16.5V6a4.5 4.5 0 10-9 0zM12 3a3 3 0 00-3 3v.75h6V6a3 3 0 00-3-3zm-3 8.25a3 3 0 106 0v-.75a.75.75 0 011.5 0v.75a4.5 4.5 0 11-9 0v-.75a.75.75 0 011.5 0v.75z" clip-rule="evenodd"/>
          </svg>
        </a>
      </div>
    </header>

    <div class="img">
        <h2>#ADMIN LOGIN</h2>
    </div>
    
    <?php
    // Check if admin table exists
    $table_exists = mysqli_query($db, "SHOW TABLES LIKE 'admin_users'");
    if (mysqli_num_rows($table_exists) == 0):
    ?>
        <div class="setup-notice">
            <h3>⚠️ Admin Setup Required</h3>
            <p>Admin table not found. Please run the setup script first.</p>
            <a href="create_admin.php">Run Admin Setup</a>
        </div>
    <?php endif; ?>

    <div class="container">
        <form method="post" action="admin_login.php">
            <?php if (count($errors) > 0): ?>
                <div class="error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach ?>
                </div>
            <?php endif ?>

            <div class="user">
                <input type="text" name="username" class="form-control" id="admin-name" required>
                <label for="admin-name" class="form-label">Admin Username</label>
            </div>

            <div class="user">
                <input type="password" name="password" class="form-control" id="admin-password" required>
                <label for="admin-password" class="form-label">Admin Password</label>
            </div>

            <input type="submit" value="Admin Login" name="admin_login"/>

            <div class="pa">
                <p><a href="login.php">← Back to User Login</a></p>
                <p style="font-size: 0.9rem; color: #7f8c8d;">Default: admin / admin123</p>
            </div>
        </form>
    </div>

    <footer class="footer">
      <div class="col">
        <h4>Contact</h4>
        <p><strong>Address: </strong> Dubarmarg, Kathmandu</p>
        <p><strong>Phone:</strong> +977 98XXXXXXXX</p>
        <p><strong>Hours:</strong> 10:00 - 19:00, Sun - Fri</p>

        <div class="follow">
          <h4>Follow Us</h4>
          <div class="footer-icon">
            <i class="fa-brands fa-facebook-f"></i>
            <i class="fab fa-twitter"></i>
            <i class="fab fa-instagram"></i>
            <i class="fa-brands fa-youtube"></i>
          </div>
        </div>
      </div>

      <div class="col links">
        <h4>About</h4>
        <a href="aboutus.html">About Us</a>
        <a href="contact.html">Contact Us</a>
      </div>

      <div class="col links">
        <h4>My Accounts</h4>
        <a href="login.php">Sign In</a>
        <a href="mycart.php">View Cart</a>
        <a href="#">Help</a>
      </div>

      <div class="col payment-partners">
        <h4>Payment Partners</h4>
        <p>Secured Payment Gateways</p>
        <div>
          <img src="images/esewa.png" alt="eSewa" />
          <img src="images/khalti.png" alt="Khalti" />
        </div>
      </div>
    </footer>
</body>
</html>