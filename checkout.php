<?php 
session_start();

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

// Convert to paisa
$amountInPaisa = (int)($totalAmount * 100);

// --- SANDBOX FIX: Khalti only allows specific test amounts ---
$sandboxAmounts = [1000, 5000, 10000, 50000]; // Rs. 10, 50, 100, 500
$closestAmount = 1000; // default
foreach ($sandboxAmounts as $amt) {
    if ($amountInPaisa <= $amt) {
        $closestAmount = $amt;
        break;
    }
}
$amountInPaisa = $closestAmount;
// --- END SANDBOX FIX ---

// Order info
$purchaseOrderId = "WOSA-" . uniqid();
$purchaseOrderName = "Wosa Order";

// Save pending order
$_SESSION['pending_order'] = [
    'purchase_order_id' => $purchaseOrderId,
    'full_name' => $full_name,
    'phone_no' => $phone_no,
    'address' => $address,
    'total_amount' => $totalAmount
];

// UPDATE: ngrok public URL
$ngrokUrl = "https://nationally-isolatable-dorthey.ngrok-free.dev/Wosa";
$returnUrl  = $ngrokUrl . "/verify.php";
$websiteUrl = $ngrokUrl;

// Khalti initiate payload
$data = [
    "return_url" => $returnUrl,
    "website_url" => $websiteUrl,
    "amount" => $amountInPaisa,
    "purchase_order_id" => $purchaseOrderId,
    "purchase_order_name" => $purchaseOrderName
];

$ch = curl_init($KHALTI_GATEWAY_URL . "/api/v2/epayment/initiate/");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        "Authorization: Key $secretKey",
        "Content-Type: application/json"
    ],
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
curl_close($ch);

$responseData = json_decode($response, true);

// Redirect
if (isset($responseData['payment_url'])) {
    header("Location: " . $responseData['payment_url']);
    exit;
}

echo "<pre>Payment initiation failed\n";
print_r($responseData);
echo "</pre>";
exit;
?>
