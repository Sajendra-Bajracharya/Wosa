<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('server.php');
?>
<!DOCTYPE html>
<html>
<head>
  <title>Login - Registration System</title>
  <link rel="stylesheet" href="css/login.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <script src="https://kit.fontawesome.com/144a91ca19.js" crossorigin="anonymous"></script>
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
    <!-- ✅ LOGIN SUCCESS -->
    <div class="img">
      <h2>#WELCOME</h2>
    </div>
    
    <div class="container" style="text-align:center; padding: 40px 20px;">
      <h2>Login Successful 🎉</h2>
      <p style="font-size: 18px; margin: 20px 0;">Welcome back, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</p>
      
      <div style="margin-top: 30px;">
        <a href="index.html" style="text-decoration: none;">
          <input type="button" value="Continue Shopping" style="margin: 10px; padding: 12px 30px; cursor: pointer;"/>
        </a>
        <a href="profile.php" style="text-decoration: none;">
          <input type="button" value="My Profile" style="margin: 10px; padding: 12px 30px; cursor: pointer;"/>
        </a>
        <br>
        <a href="logout.php" style="text-decoration: none;">
          <input type="button" value="Logout" style="margin: 10px; padding: 12px 30px; cursor: pointer; background: #e74c3c;"/>
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