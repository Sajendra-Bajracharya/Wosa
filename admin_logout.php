<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_logged_in']);

session_destroy();

header("Location: admin_login.php");
exit;
?>