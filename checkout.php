<?php
session_start();

// Khalti Sandbox API Credentials
$publicKey = "f4aeb62ff3424f0c9ce6b9e433660fd0";
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

// Check if cart is empty
if(!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0){
    echo "<script>
        alert('Your cart is empty!');
        window.location.href='mycart.php';
    </script>";
    exit;
}

// Get customer information from POST
$full_name = isset($_POST['full_name']) ? mysqli_real_escape_string($con, $_POST['full_name']) : '';
$phone_no = isset($_POST['phone_no']) ? mysqli_real_escape_string($con, $_POST['phone_no']) : '';
$address = isset($_POST['address']) ? mysqli_real_escape_string($con, $_POST['address']) : '';

// Validate customer information
if(empty($full_name) || empty($phone_no) || empty($address)){
    echo "<script>
        alert('Please fill in all required fields!');
        window.location.href='mycart.php';
    </script>";
    exit;
}

// Calculate total amount from cart (in rupees)
$totalAmount = 0;
$orderItems = [];
foreach($_SESSION['cart'] as $item){
    $itemTotal = $item['Price'] * $item['Quantity'];
    $totalAmount += $itemTotal;
    $orderItems[] = $item['Item_Name'] . " (Qty: " . $item['Quantity'] . ")";
}

// Generate unique purchase order ID
$purchaseOrderId = "WOSA-" . uniqid();
$purchaseOrderName = "Wosa Order - " . implode(", ", array_slice($orderItems, 0, 2));
if(count($orderItems) > 2){
    $purchaseOrderName .= " and " . (count($orderItems) - 2) . " more";
}

// Limit purchase order name length (Khalti may have character limits)
if(strlen($purchaseOrderName) > 100){
    $purchaseOrderName = substr($purchaseOrderName, 0, 97) . "...";
}

// Store order information in session for verification
$_SESSION['pending_order'] = [
    'purchase_order_id' => $purchaseOrderId,
    'full_name' => $full_name,
    'phone_no' => $phone_no,
    'address' => $address,
    'total_amount' => $totalAmount,
    'items' => $_SESSION['cart']
];

// Get current website URL for return URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . "://" . $host . dirname($_SERVER['PHP_SELF']);
$returnUrl = $baseUrl . "/verify.php";
$websiteUrl = $baseUrl;

// Convert amount to paisa (Khalti requires amount in paisa)
// Ensure it's an integer
$amountInPaisa = (int)($totalAmount * 100);

// Validate amount (minimum 100 paisa = Rs 1)
if($amountInPaisa < 100){
    echo "<script>
        alert('Minimum order amount is Rs. 1');
        window.location.href='mycart.php';
    </script>";
    exit;
}

// Khalti Sandbox payment initiation API
$data = [
    "public_key" => $publicKey,
    "amount" => $amountInPaisa,
    "purchase_order_id" => $purchaseOrderId,
    "purchase_order_name" => $purchaseOrderName,
    "return_url" => $returnUrl,
    "website_url" => $websiteUrl
];

$apiUrl = $KHALTI_GATEWAY_URL . "/api/v2/epayment/initiate/";

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Key $secretKey"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For localhost testing

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Debug: Log the response (remove in production)
if($response === false){
    echo "<script>
        alert('CURL Error: $curlError');
        window.location.href='mycart.php';
    </script>";
    exit;
}

$responseData = json_decode($response, true);

// Better error handling
if(isset($responseData['payment_url'])){
    header("Location: ".$responseData['payment_url']);
    exit;
} else {
    // Detailed error handling
    $errorMsg = 'Payment initiation failed';
    
    if(isset($responseData['detail'])){
        $errorMsg = $responseData['detail'];
    } elseif(isset($responseData['error_key'])){
        $errorMsg = $responseData['error_key'];
    } elseif(isset($responseData['message'])){
        $errorMsg = $responseData['message'];
    }
    
    // Show detailed error for debugging
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Payment Error</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
            .error-box { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
            h2 { color: #d32f2f; }
            .debug { background: #f5f5f5; padding: 15px; border-radius: 3px; margin-top: 15px; font-family: monospace; font-size: 12px; overflow-x: auto; }
            .btn { background: #1976d2; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; text-decoration: none; display: inline-block; margin-top: 15px; }
            .btn:hover { background: #1565c0; }
        </style>
    </head>
    <body>
        <div class='error-box'>
            <h2>Payment Initiation Error</h2>
            <p><strong>Error:</strong> $errorMsg</p>
            <div class='debug'>
                <strong>HTTP Status Code:</strong> $httpCode<br><br>
                <strong>API URL:</strong> $apiUrl<br><br>
                <strong>Request Data:</strong><br>
                <pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre><br>
                <strong>Response Data:</strong><br>
                <pre>" . print_r($responseData, true) . "</pre>
            </div>
            <a href='mycart.php' class='btn'>Go Back to Cart</a>
        </div>
    </body>
    </html>";
    exit;
}

mysqli_close($con);
?>