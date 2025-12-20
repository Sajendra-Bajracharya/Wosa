<?php
session_start();

// initializing variables
$username = "";
$email    = "";
$phone    = "";
$address  = "";
$errors   = array(); 

// ✅ FIX: add port 3307
$db = mysqli_connect("localhost", "root", "", "testing", 3307);

if (!$db) {
    die("Database connection failed: " . mysqli_connect_error());
}

// REGISTER USER
if (isset($_POST['reg_user'])) {

    $username   = mysqli_real_escape_string($db, $_POST['username']);
    $email      = mysqli_real_escape_string($db, $_POST['email']);
    $password_1 = mysqli_real_escape_string($db, $_POST['password_1']);
    $password_2 = mysqli_real_escape_string($db, $_POST['password_2']);
    $number     = mysqli_real_escape_string($db, $_POST['number']);
    $address    = mysqli_real_escape_string($db, $_POST['address']);

    if (empty($username)) array_push($errors, "Username is required");
    if (empty($email)) array_push($errors, "Email is required");
    if (empty($password_1)) array_push($errors, "Password is required");
    if ($password_1 != $password_2) {
        array_push($errors, "Passwords do not match");
    }

    $user_check_query = "SELECT * FROM login WHERE username='$username' OR email='$email' LIMIT 1";
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
        $password = md5($password_1); // (keep as-is for now)

        $query = "INSERT INTO login (username, email, password, number, address)
                  VALUES ('$username', '$email', '$password', '$number', '$address')";
        mysqli_query($db, $query);

        $_SESSION['username'] = $username;
        header("Location: index.html");
        exit;
    }
}

// LOGIN USER
if (isset($_POST['login_user'])) {

    $username = mysqli_real_escape_string($db, $_POST['username']);
    $password = mysqli_real_escape_string($db, $_POST['password']);

    if (empty($username)) array_push($errors, "Username is required");
    if (empty($password)) array_push($errors, "Password is required");

    if (count($errors) == 0) {
        $password = md5($password);

        $query = "SELECT * FROM login WHERE username='$username' AND password='$password'";
        $results = mysqli_query($db, $query);

        if (mysqli_num_rows($results) == 1) {
            $_SESSION['username'] = $username;
            header("Location: profile.php");
            exit;
        } else {
            array_push($errors, "Wrong username/password");
        }
    }
}
?>
