<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Khalti credentials
$publicKey = "3b0b476539d44bd3ab515bd4709049ba";
$secretKey = "68cc93e50fb540eb89ba1dfc27531ff0";
$KHALTI_GATEWAY_URL = "https://a.khalti.com";

// DB connection (port 3307)
$con = mysqli_connect("localhost", "root", "", "testing", 3307);
if (mysqli_connect_error()) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Cart check
if (!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
    echo "<script>alert('Your cart is empty'); window.location.href='mycart.php';</script>";
    exit;
}

// Customer data
$full_name = mysqli_real_escape_string($con, $_POST['full_name'] ?? '');
$phone_no  = mysqli_real_escape_string($con, $_POST['phone_no'] ?? '');
$address   = mysqli_real_escape_string($con, $_POST['address'] ?? '');

if (!$full_name || !$phone_no || !$address) {
    echo "<script>alert('Fill all fields'); window.location.href='mycart.php';</script>";
    exit;
}

// Calculate total
$totalAmount = 0;
$orderItems = [];
foreach ($_SESSION['cart'] as $item) {
    $totalAmount += $item['Price'] * $item['Quantity'];
    $orderItems[] = $item['Item_Name'];
}

// Convert to paisa (Khalti works in paisa, 1 Rs = 100 paisa)
$amountInPaisa = (int)($totalAmount * 100);

// IMPORTANT: For Khalti sandbox testing, you need to use specific test amounts
// Khalti sandbox only accepts these amounts: 1000, 5000, 10000, 50000 paisa
// That's Rs. 10, Rs. 50, Rs. 100, or Rs. 500
// For production, remove this limitation

// For testing, we'll round to nearest test amount
$testAmounts = [1000, 5000, 10000, 50000]; // in paisa
$closestAmount = 1000; // default to Rs. 10

foreach ($testAmounts as $amt) {
    if (abs($amountInPaisa - $amt) < abs($amountInPaisa - $closestAmount)) {
        $closestAmount = $amt;
    }
}

// For testing, use the test amount
// Comment this line in production
$amountInPaisa = $closestAmount;

// Order info
$purchaseOrderId = "WOSA-" . time() . "-" . rand(1000, 9999);
$purchaseOrderName = "Wosa Fashion Order";

// Save pending order in session
$_SESSION['pending_order'] = [
    'purchase_order_id' => $purchaseOrderId,
    'full_name' => $full_name,
    'phone_no' => $phone_no,
    'address' => $address,
    'total_amount' => $totalAmount,
    'cart_items' => $_SESSION['cart']
];

// Your website URLs - UPDATE THESE TO YOUR ACTUAL URLs
// For localhost development with ngrok or local testing
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$projectPath = dirname($_SERVER['PHP_SELF']);
$returnUrl  = $baseUrl . $projectPath . "/verify.php";
$websiteUrl = $baseUrl . $projectPath;

// Khalti initiate payment payload
$data = [
    "return_url" => $returnUrl,
    "website_url" => $websiteUrl,
    "amount" => $amountInPaisa,
    "purchase_order_id" => $purchaseOrderId,
    "purchase_order_name" => $purchaseOrderName,
    "customer_info" => [
        "name" => $full_name,
        "email" => "customer@example.com", // Optional
        "phone" => $phone_no
    ]
];

// Initialize cURL
$ch = curl_init($KHALTI_GATEWAY_URL . "/api/v2/epayment/initiate/");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        "Authorization: Key $secretKey",
        "Content-Type: application/json"
    ],
    CURLOPT_SSL_VERIFYPEER => true, // Set to true for production
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Debug information (remove in production)
if ($httpCode !== 200) {
    error_log("Khalti API Error - HTTP Code: $httpCode");
    error_log("Khalti API Response: " . $response);
    error_log("cURL Error: " . $curlError);
}

$responseData = json_decode($response, true);

// Check if payment URL is received
if (isset($responseData['payment_url']) && !empty($responseData['payment_url'])) {
    // Redirect to Khalti payment page
    header("Location: " . $responseData['payment_url']);
    exit;
} else {
    // Payment initiation failed
    $errorMessage = "Payment initiation failed. ";
    
    if (isset($responseData['error_key'])) {
        $errorMessage .= "Error: " . $responseData['error_key'];
    }
    
    if (isset($responseData['detail'])) {
        $errorMessage .= " - " . $responseData['detail'];
    }
    
    echo "<h2 style='color: red;'>$errorMessage</h2>";
    echo "<h3>Response from Khalti:</h3>";
    echo "<pre style='background: #f4f4f4; padding: 15px; border-radius: 5px;'>";
    print_r($responseData);
    echo "</pre>";
    echo "<h3>Debug Information:</h3>";
    echo "<pre style='background: #f4f4f4; padding: 15px; border-radius: 5px;'>";
    echo "HTTP Code: $httpCode\n";
    echo "Amount (paisa): $amountInPaisa\n";
    echo "Amount (Rs): " . ($amountInPaisa / 100) . "\n";
    echo "Purchase Order ID: $purchaseOrderId\n";
    echo "Return URL: $returnUrl\n";
    echo "Website URL: $websiteUrl\n";
    echo "</pre>";
    echo "<p><a href='mycart.php' style='display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px;'>Back to Cart</a></p>";
    exit;
}?>