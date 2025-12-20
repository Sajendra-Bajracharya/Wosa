<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset admin session variables
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_logged_in']);

// Destroy session
session_destroy();

// Redirect to admin login
header("Location: admin_login.php");
exit;
?>