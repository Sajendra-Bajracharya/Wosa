<?php
session_start();

$secretKey = "651dfb44a54b463891292995be953d92";
$KHALTI_GATEWAY_URL = "https://a.khalti.com";

// DB connection
$con = mysqli_connect("localhost", "root", "", "testing");
if (mysqli_connect_error()) {
    die("Database connection failed");
}

// Get pidx
$pidx = $_GET['pidx'] ?? null;
if (!$pidx) {
    echo "<script>alert('Invalid payment callback'); window.location.href='mycart.php';</script>";
    exit;
}

// Lookup payment
$payload = json_encode(["pidx" => $pidx]);

$ch = curl_init($KHALTI_GATEWAY_URL . "/api/v2/epayment/lookup/");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
        "Authorization: Key $secretKey",
        "Content-Type: application/json"
    ]
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

// Check payment status
if (!isset($data['status']) || $data['status'] !== 'Completed') {
    echo "<script>alert('Payment not completed'); window.location.href='mycart.php';</script>";
    exit;
}

// Session check
if (!isset($_SESSION['pending_order']) || !isset($_SESSION['cart'])) {
    echo "<script>alert('Session expired'); window.location.href='mycart.php';</script>";
    exit;
}

$pendingOrder = $_SESSION['pending_order'];

// Insert order
$stmt1 = mysqli_prepare(
    $con,
    "INSERT INTO order_manager (Full_Name, Phone_No, Address, Pay_Mode, Paid_Amount) VALUES (?,?,?,?,?)"
);

$payMode = "Khalti";
$paidAmount = $data['amount'] ?? 0; // amount in paisa

mysqli_stmt_bind_param(
    $stmt1,
    "sssss",
    $pendingOrder['full_name'],
    $pendingOrder['phone_no'],
    $pendingOrder['address'],
    $payMode,
    $paidAmount
);
mysqli_stmt_execute($stmt1);

$Order_Id = mysqli_insert_id($con);
mysqli_stmt_close($stmt1);

// Insert items
$stmt2 = mysqli_prepare(
    $con,
    "INSERT INTO user_orders (Order_Id, Item_Name, Price, Quantity) VALUES (?,?,?,?)"
);

mysqli_stmt_bind_param($stmt2, "isii", $Order_Id, $Item_Name, $Price, $Quantity);

foreach ($_SESSION['cart'] as $item) {
    $Item_Name = $item['Item_Name'];
    $Price = $item['Price'];
    $Quantity = $item['Quantity'];
    mysqli_stmt_execute($stmt2);
}

mysqli_stmt_close($stmt2);

// Clear session
unset($_SESSION['cart'], $_SESSION['pending_order']);

$paidAmountRs = $paidAmount / 100; // convert paisa to rupees

echo "<script>
    alert('Payment successful! Order ID: $Order_Id. Amount paid: Rs. $paidAmountRs');
    window.location.href='index.html';
</script>";
exit;
?>
