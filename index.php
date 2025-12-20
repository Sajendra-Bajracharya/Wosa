<?php
session_start();
// Calculate cart count
$cartCount = 0;
if (isset($_SESSION['cart'])) {
    $cartCount = count($_SESSION['cart']);
}
?>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" type="text/css" href="slick/slick.css" />
    <link rel="stylesheet" type="text/css" href="slick/slick-theme.css" />
    <link
      href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <script
      src="https://kit.fontawesome.com/144a91ca19.js"
      crossorigin="anonymous"
    ></script>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/cart-styles.css" />

    <title>Wo:sa</title>
  </head>
  <body>
    <header class="nav-section">
      <div class="logo-container">
        <a href="index.php"
          ><img class="logo" src="images/logo.png" alt=""
        /></a>
      </div>
      <div class="main-nav-links">
        <ul class="main-nav-list">
          <li><a class="main-nav-link" href="index.php">Home</a></li>
          <li><a class="main-nav-link" href="aboutus.html">About</a></li>
          <li>
            <a class="main-nav-link" href="contact.html">Contact</a>
          </li>
        </ul>
      </div>

      <div class="nav-icon">
        <a href="login.php">
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
        <a href="mycart.php" class="cart-icon-wrapper">
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
          <?php if($cartCount > 0): ?>
          <span class="cart-badge"><?php echo $cartCount; ?></span>
          <?php endif; ?>
        </a>
      </div>
    </header>
    
    <!-- Toast Notification Container -->
    <div id="toast-container"></div>
    
    <!-- ... rest of your existing index.html content ... -->
