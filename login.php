<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('server.php');

// Fetch user details
$user_details = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM user_manager WHERE id = '$user_id'";
    $result = mysqli_query($db, $query);
    if ($result) {
        $user_details = mysqli_fetch_assoc($result);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Login - Wosa</title>
  <link rel="stylesheet" href="css/login.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <script src="https://kit.fontawesome.com/144a91ca19.js" crossorigin="anonymous"></script>
  <style>
    .welcome-section {
      max-width: 1000px;
      margin: 40px auto;
      padding: 40px 20px;
      text-align: center;
    }
    
    .welcome-header {
      background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
      color: white;
      padding: 40px 30px;
      border-radius: 20px;
      margin-bottom: 30px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .welcome-header h1 {
      font-size: 2.5rem;
      margin-bottom: 10px;
    }
    
    .welcome-header p {
      font-size: 1.2rem;
      opacity: 0.9;
    }
    
    .dashboard-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin: 30px 0;
    }
    
    .dashboard-card {
      background: white;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      transition: transform 0.3s, box-shadow 0.3s;
      text-decoration: none;
      color: #333;
    }
    
    .dashboard-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    
    .dashboard-card i {
      font-size: 3rem;
      margin-bottom: 15px;
      color: #3b82f6;
    }
    
    .dashboard-card h3 {
      font-size: 1.3rem;
      margin-bottom: 10px;
      color: #333;
    }
    
    .dashboard-card p {
      color: #666;
      font-size: 0.95rem;
    }
    
    .user-info-section {
      background: #f8f9fa;
      padding: 30px;
      border-radius: 15px;
      margin: 30px 0;
      text-align: left;
    }
    
    .user-info-section h2 {
      margin-bottom: 20px;
      color: #333;
    }
    
    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 15px;
    }
    
    .info-item {
      background: white;
      padding: 15px;
      border-radius: 10px;
      border-left: 4px solid #3b82f6;
    }
    
    .info-item label {
      font-weight: 600;
      color: #3b82f6;
      display: block;
      margin-bottom: 5px;
      font-size: 0.9rem;
    }
    
    .info-item span {
      color: #333;
      font-size: 1.1rem;
    }
    
    .action-buttons {
      display: flex;
      gap: 15px;
      justify-content: center;
      flex-wrap: wrap;
      margin-top: 30px;
    }
    
    .btn-action {
      padding: 12px 30px;
      border: none;
      border-radius: 25px;
      font-size: 1rem;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      transition: all 0.3s;
      font-weight: 600;
    }
    
    .btn-primary-action {
      background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
      color: white;
    }
    
    .btn-primary-action:hover {
      transform: scale(1.05);
      box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
    }
    
    .btn-secondary-action {
      background: #fff;
      color: #3b82f6;
      border: 2px solid #3b82f6;
    }
    
    .btn-secondary-action:hover {
      background: #3b82f6;
      color: white;
    }
    
    .btn-danger-action {
      background: #e74c3c;
      color: white;
    }
    
    .btn-danger-action:hover {
      background: #c0392b;
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

  <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
    <!-- ✅ LOGGED IN - DASHBOARD -->
    <div class="welcome-section">
      <div class="welcome-header">
        <h1>Welcome Back, <?php echo htmlspecialchars($_SESSION['username']); ?>! 👋</h1>
        <p>Ready to continue your shopping journey?</p>
      </div>

      <!-- Dashboard Cards -->
      <div class="dashboard-grid">
        <a href="index.html" class="dashboard-card">
          <i class="fas fa-shopping-bag"></i>
          <h3>Continue Shopping</h3>
          <p>Browse our latest collections</p>
        </a>

        <a href="mycart.php" class="dashboard-card">
          <i class="fas fa-shopping-cart"></i>
          <h3>My Cart</h3>
          <p>View items in your cart</p>
        </a>

        <a href="my_orders.php" class="dashboard-card">
          <i class="fas fa-box"></i>
          <h3>My Orders</h3>
          <p>Track your orders</p>
        </a>
      </div>

      <!-- User Information -->
      <?php if ($user_details): ?>
      <div class="user-info-section">
        <h2>Your Account Details</h2>
        <div class="info-grid">
          <div class="info-item">
            <label>Username</label>
            <span><?php echo htmlspecialchars($user_details['username']); ?></span>
          </div>
          <div class="info-item">
            <label>Email</label>
            <span><?php echo htmlspecialchars($user_details['email']); ?></span>
          </div>
          <div class="info-item">
            <label>Phone Number</label>
            <span><?php echo htmlspecialchars($user_details['phone_number']); ?></span>
          </div>
          <div class="info-item">
            <label>Address</label>
            <span><?php echo htmlspecialchars($user_details['address']); ?></span>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Action Buttons -->
      <div class="action-buttons">
        <a href="index.html" class="btn-action btn-primary-action">
          <i class="fas fa-store"></i> Shop Now
        </a>
        <a href="logout.php" class="btn-action btn-danger-action">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </div>

  <?php else: ?>
    <!-- 🔐 LOGIN FORM -->
    <div class="img">
      <h2>#LOGIN</h2>
    </div>
    
    <div class="container">
      <form method="post" action="login.php">
        <?php include('errors.php'); ?>

        <div class="user">
          <input type="text" name="username" class="form-control" id="name" required>
          <label for="name" class="form-label">Username</label>
        </div>

        <div class="user">
          <input type="password" name="password" class="form-control" id="password" required>
          <label for="password" class="form-label">Password</label>
        </div>

        <input type="submit" value="Login" name="login_user"/>

        <div class="pa">
          <p>Not yet a member? <a href="register.php">Sign up</a></p>
          <p><a href="admin_login.php" style="color: #3b82f6;">Admin Login</a></p>
        </div>
      </form>
    </div>
  <?php endif; ?>

  <!-- Footer -->
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