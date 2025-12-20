<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* 🔐 LOGIN CHECK */
if (!isset($_SESSION['user_id'])) {
    echo "<script>
        alert('Please login to proceed with checkout');
        window.location.href='login.php';
    </script>";
    exit();
}

/* DATABASE CONNECTION */
$con = mysqli_connect("localhost", "root", "", "testing", 3307);

if (!$con) {
    echo "<script>
        alert('Cannot connect to database');
        window.location.href='mycart.php';
    </script>";
    exit();
}

/* PURCHASE LOGIC */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['purchase'])) {

    $user_id   = $_SESSION['user_id'];
    $full_name = mysqli_real_escape_string($con, $_POST['full_name']);
    $phone     = mysqli_real_escape_string($con, $_POST['phone_no']);
    $address   = mysqli_real_escape_string($con, $_POST['address']);
    $pay_mode  = mysqli_real_escape_string($con, $_POST['pay_mode']);

    /* INSERT INTO order_manager - UPDATED WITH user_id */
    $query1 = "INSERT INTO order_manager 
               (user_id, Full_Name, Phone_No, Address, Pay_Mode)
               VALUES (?,?,?,?,?)";

    $stmt1 = mysqli_prepare($con, $query1);
    mysqli_stmt_bind_param($stmt1, "issss",
        $user_id,
        $full_name,
        $phone,
        $address,
        $pay_mode
    );

    if (mysqli_stmt_execute($stmt1)) {

        $Order_Id = mysqli_insert_id($con);

        /* INSERT ITEMS INTO user_orders */
        $query2 = "INSERT INTO user_orders 
                   (Order_Id, Item_Name, Price, Quantity)
                   VALUES (?,?,?,?)";

        $stmt2 = mysqli_prepare($con, $query2);
        mysqli_stmt_bind_param($stmt2, "isii",
            $Order_Id,
            $Item_Name,
            $Price,
            $Quantity
        );

        foreach ($_SESSION['cart'] as $item) {
            $Item_Name = $item['Item_Name'];
            $Price     = $item['Price'];
            $Quantity  = $item['Quantity'];
            mysqli_stmt_execute($stmt2);
        }

        unset($_SESSION['cart']);

        echo "<script>
            alert('Order placed successfully!');
            window.location.href='my_orders.php';
        </script>";
    } else {
        echo "<script>
            alert('Order failed: " . mysqli_error($con) . "');
            window.location.href='mycart.php';
        </script>";
    }
}
?>