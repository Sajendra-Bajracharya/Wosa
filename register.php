<?php include('server.php') ?>
<!DOCTYPE html>
<html>
<head>
  <title>Registration system PHP and MySQL</title>
  <link rel="stylesheet" type="text/css" href="css/signupp.css">
  <link
      href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />
	<script
      src="https://kit.fontawesome.com/144a91ca19.js"
      crossorigin="anonymous"
    ></script>
</head>
<body>

<header class="nav-section">
      <div class="logo-container">
        <img class="logo" src="images/logo.png" alt="" />
      </div>
      <div class="main-nav-links">
        <ul class="main-nav-list">
          <li><a class="main-nav-link" href="index.html">Home</a></li>
          <li><a class="main-nav-link" href="aboutus.html">About</a></li>
          <li>
            <a class="main-nav-link" href="#">Contact</a>
          </li>
        </ul>
      </div>

      <div class="nav-icon">
      <a href="register.php">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="currentColor"
            class="w-6 h-6 icons"
          >
            <path
              fill-rule="evenodd"
              d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z"
              clip-rule="evenodd"
            />
          </svg>
        </a>
        <a href="mycart.php">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="currentColor"
            class="w-6 h-6 icons"
          >
            <path
              fill-rule="evenodd"
              d="M7.5 6v.75H5.513c-.96 0-1.764.724-1.865 1.679l-1.263 12A1.875 1.875 0 004.25 22.5h15.5a1.875 1.875 0 001.865-2.071l-1.263-12a1.875 1.875 0 00-1.865-1.679H16.5V6a4.5 4.5 0 10-9 0zM12 3a3 3 0 00-3 3v.75h6V6a3 3 0 00-3-3zm-3 8.25a3 3 0 106 0v-.75a.75.75 0 011.5 0v.75a4.5 4.5 0 11-9 0v-.75a.75.75 0 011.5 0v.75z"
              clip-rule="evenodd"
            />
          </svg>
        </a>
      </div>
    </header>

	<div class="img">
    <h2>#SIGNUP</h2>
  </div> 
	<div class="container">
  <form method="post" action="register.php">
  	<?php include('errors.php'); ?>

  	<div class="user">
  	  <input type="text" name="username" 
      id="name" class="form-control"  value="<?php echo $username; ?>" required>
  	  <label for="name" class="form-label" >Username</label>
  	</div>

  	<div class="user">
  	  <input type="email" name="email"
      id="mail" class="form-control" value="<?php echo $email; ?>" required>
  	  <label for="mail" class="form-label" id="email">Email</label>
  	</div>

  	<div class="user">
  	  <input type="password" name="password_1" id="pass" class="form-control" required>
  	  <label for="pass" class="form-label" id="password">Password</label>
  	</div>

  	<div class="user">
  	  <input type="password" name="password_2" id="repass" class="form-control" required>
  	  <label for="repass" class="form-label" id="cpassword">Confirm password</label>
  	</div>

  	<div class="user">
  	  <input type="number" name="number" id="num" class="form-control" value="<?php echo $number; ?>" required>
  	  <label for="num" class="form-label" id="num">Phone Number</label>
  	</div>

  	<div class="user">
  	  <input type="text" name="address" id="add" class="form-control" value="<?php echo $address; ?>" required>
  	  <label for="add" class="form-label" id="add">Address</label>
  	</div>

  	<!-- <div class="user">
  	  <button type="submit" class="btn" name="reg_user">Register</button>
  	</div> -->
     <input type="submit" value="Signup" name="reg_user" />

		 <div class="pa"><p>
  		Already a member? <a href="login.php">Log in</a>
  	</p></div>
  	

  </form>
  </div>

	<!-- footer -->
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
        <a href="#">About Us</a>
        <a href="#">Contact Us</a>
      </div>

      <div class="col links">
        <h4>My Accounts</h4>
        <a href="#">Sign In</a>
        <a href="#">View Cart</a>
        <a href="#">Help</a>
      </div>

      <div class="col payment-partners">
        <h4>Payment Partners</h4>
        <p>Secured Payment Gateways</p>
        <div>
          <img src="images/esewa.png" alt="" />
          <img src="images/khalti.png" alt="" />
        </div>
      </div>
    </footer>

</body>
</html>