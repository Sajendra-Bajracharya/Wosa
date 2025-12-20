<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// initializing variables
$username = "";
$email    = "";
$phone    = "";
$address  = "";
$errors = array(); 

// connect to the database (PORT: 3307)
$db = mysqli_connect("localhost", "root", "", "testing", 3307);

if (!$db) {
    die("Database connection failed: " . mysqli_connect_error());
}

/* ===================== REGISTER USER ===================== */
if (isset($_POST['reg_user'])) {

  $username = mysqli_real_escape_string($db, $_POST['username']);
  $email    = mysqli_real_escape_string($db, $_POST['email']);
  $password_1 = mysqli_real_escape_string($db, $_POST['password_1']);
  $password_2 = mysqli_real_escape_string($db, $_POST['password_2']);
  $phone_number = mysqli_real_escape_string($db, $_POST['number']); // Changed variable name
  $address  = mysqli_real_escape_string($db, $_POST['address']);

  // Validation
  if (empty($username)) array_push($errors, "Username is required");
  if (empty($email)) array_push($errors, "Email is required");
  if (empty($password_1)) array_push($errors, "Password is required");
  if ($password_1 != $password_2) array_push($errors, "Passwords do not match");
  if (empty($phone_number)) array_push($errors, "Phone number is required");
  if (empty($address)) array_push($errors, "Address is required");

  // Check if user already exists
  $user_check_query = "SELECT * FROM user_manager WHERE username='$username' OR email='$email' LIMIT 1";
  $result = mysqli_query($db, $user_check_query);
  $user = mysqli_fetch_assoc($result);

  if ($user) {
    if ($user['username'] === $username) {
      array_push($errors, "Username already exists");
    }
    if ($user['email'] === $email) {
      array_push($errors, "Email already exists");
    }
  }

  if (count($errors) == 0) {
    // ✅ IMPROVED: Use password_hash instead of md5 (more secure)
    $password = password_hash($password_1, PASSWORD_DEFAULT);

    // ✅ FIXED: Changed 'number' to 'phone_number'
    $query = "INSERT INTO user_manager (username, email, password, phone_number, address)
              VALUES ('$username', '$email', '$password', '$phone_number', '$address')";
    
    if (mysqli_query($db, $query)) {
      $_SESSION['user_id'] = mysqli_insert_id($db);
      $_SESSION['username'] = $username;
      $_SESSION['logged_in'] = true;

      header('location: login.php');
      exit();
    } else {
      array_push($errors, "Registration failed: " . mysqli_error($db));
    }
  }
}

/* ===================== LOGIN USER ===================== */
if (isset($_POST['login_user'])) {

  $username = mysqli_real_escape_string($db, $_POST['username']);
  $password = mysqli_real_escape_string($db, $_POST['password']);

  if (empty($username)) array_push($errors, "Username is required");
  if (empty($password)) array_push($errors, "Password is required");

  if (count($errors) == 0) {
    // ✅ Get user data first
    $query = "SELECT * FROM user_manager WHERE username='$username' LIMIT 1";
    $results = mysqli_query($db, $query);

    if (mysqli_num_rows($results) == 1) {
      $row = mysqli_fetch_assoc($results);

      // ✅ IMPROVED: Use password_verify for hashed passwords
      if (password_verify($password, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['logged_in'] = true;

        header('location: login.php');
        exit();
      } else {
        array_push($errors, "Wrong username/password combination");
      }
    } else {
      array_push($errors, "Wrong username/password combination");
    }
  }
}
?> 