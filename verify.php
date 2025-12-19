<?php
session_start();

// Khalti Sandbox API Credentials
$secretKey = "651dfb44a54b463891292995be953d92";
$KHALTI_GATEWAY_URL = "https://a.khalti.com";

// Database connection
$con = mysqli_connect("localhost", "root", "", "testing");

if(mysqli_connect_error()){
    echo "<script>
        alert('Database connection error');
        window.location.href='mycart.php';
    </script>";
    exit;
}

// Get payment info from Khalti redirect
$token = $_GET['token'] ?? null;
$amount = $_GET['amount'] ?? null;
$purchase_order_id = $_GET['purchase_order_id'] ?? null;

if(!$token || !$amount){
    echo "<script>
        alert('Invalid payment response.');
        window.location.href='mycart.php';
    </script>";
    exit;
}

// Check if pending order exists in session
if(!isset($_SESSION['pending_order']) || !isset($_SESSION['cart'])){
    echo "<script>
        alert('Session expired. Please try again.');
        window.location.href='mycart.php';
    </script>";
    exit;
}

// Verify payment with Khalti Sandbox server
$data = [
    "token" => $token,
    "amount" => (int)$amount
];

$ch = curl_init($KHALTI_GATEWAY_URL . "/api/v2/epayment/verify/");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Key $secretKey"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$verifyData = json_decode($response, true);

if(isset($verifyData['status_code']) && $verifyData['status_code'] == 200){
    // Payment verified successfully - Save order to database
    $pendingOrder = $_SESSION['pending_order'];
    
    // Insert order into order_manager table
    $query1 = "INSERT INTO `order_manager`(`Full_Name`, `Phone_No`, `Address`, `Pay_Mode`) VALUES (?,?,?,?)";
    $stmt1 = mysqli_prepare($con, $query1);
    
    if($stmt1){
        $payMode = 'Khalti';
        mysqli_stmt_bind_param($stmt1, "ssss", 
            $pendingOrder['full_name'], 
            $pendingOrder['phone_no'], 
            $pendingOrder['address'], 
            $payMode
        );
        
        if(mysqli_stmt_execute($stmt1)){
            $Order_Id = mysqli_insert_id($con);
            mysqli_stmt_close($stmt1);
            
            // Insert order items into user_orders table
            $query2 = "INSERT INTO `user_orders`(`Order_Id`, `Item_Name`, `Price`, `Quantity`) VALUES (?,?,?,?)";
            $stmt2 = mysqli_prepare($con, $query2);
            
            if($stmt2){
                mysqli_stmt_bind_param($stmt2, "isii", $Order_Id, $Item_Name, $Price, $Quantity);
                
                foreach($_SESSION['cart'] as $key => $values){
                    $Item_Name = $values['Item_Name'];
                    $Price = $values['Price'];
                    $Quantity = $values['Quantity'];
                    mysqli_stmt_execute($stmt2);
                }
                
                mysqli_stmt_close($stmt2);
                
                // Clear cart and session
                unset($_SESSION['cart']);
                unset($_SESSION['pending_order']);
                
                mysqli_close($con);
                
                // Success message
                echo "<script>
                    alert('Payment successful! Your order has been placed. Order ID: $Order_Id');
                    window.location.href='index.html';
                </script>";
                exit;
            } else {
                mysqli_close($con);
                echo "<script>
                    alert('Error saving order items. Please contact support.');
                    window.location.href='mycart.php';
                </script>";
                exit;
            }
        } else {
            mysqli_stmt_close($stmt1);
            mysqli_close($con);
            echo "<script>
                alert('Error saving order. Please contact support.');
                window.location.href='mycart.php';
            </script>";
            exit;
        }
    } else {
        mysqli_close($con);
        echo "<script>
            alert('Database error. Please contact support.');
            window.location.href='mycart.php';
        </script>";
        exit;
    }
} else {
    // Payment verification failed
    $errorMsg = isset($verifyData['detail']) ? $verifyData['detail'] : 'Payment verification failed';
    echo "<script>
        alert('Payment verification failed: $errorMsg');
        window.location.href='mycart.php';
    </script>";
    exit;
}
?>