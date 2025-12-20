<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$secretKey = "68cc93e50fb540eb89ba1dfc27531ff0";
$KHALTI_GATEWAY_URL = "https://a.khalti.com";

// DB connection (port 3307)
$con = mysqli_connect("localhost", "root", "", "testing", 3307);
if (mysqli_connect_error()) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get pidx from URL
$pidx = $_GET['pidx'] ?? null;

if (!$pidx) {
    echo "<h2 style='color: red;'>Invalid payment callback - No transaction ID received</h2>";
    echo "<p><a href='mycart.php'>Return to Cart</a></p>";
    exit;
}

// Verify payment status with Khalti
$payload = json_encode(["pidx" => $pidx]);

$ch = curl_init($KHALTI_GATEWAY_URL . "/api/v2/epayment/lookup/");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
        "Authorization: Key $secretKey",
        "Content-Type: application/json"
    ],
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

$data = json_decode($response, true);

// Log for debugging (remove in production)
error_log("Khalti Verification - HTTP Code: $httpCode");
error_log("Khalti Verification Response: " . $response);

// Check payment status
if (!isset($data['status'])) {
    echo "<h2 style='color: red;'>Payment verification failed</h2>";
    echo "<h3>Response from Khalti:</h3>";
    echo "<pre style='background: #f4f4f4; padding: 15px;'>";
    print_r($data);
    echo "</pre>";
    echo "<p><a href='mycart.php'>Return to Cart</a></p>";
    exit;
}

$paymentStatus = $data['status'];

// Check if payment was completed
if ($paymentStatus !== 'Completed') {
    echo "<h2 style='color: orange;'>Payment Status: $paymentStatus</h2>";
    echo "<p>The payment was not completed. Please try again.</p>";
    echo "<p><a href='mycart.php'>Return to Cart</a></p>";
    exit;
}

// Session check
if (!isset($_SESSION['pending_order'])) {
    echo "<h2 style='color: red;'>Session expired</h2>";
    echo "<p>Your session has expired. Please place your order again.</p>";
    echo "<p><a href='index.php'>Go to Home</a></p>";
    exit;
}

$pendingOrder = $_SESSION['pending_order'];
$cartItems = $pendingOrder['cart_items'] ?? [];

if (empty($cartItems)) {
    echo "<h2 style='color: red;'>Cart is empty</h2>";
    echo "<p><a href='index.php'>Go to Home</a></p>";
    exit;
}

// Begin transaction
mysqli_begin_transaction($con);

try {
    // Insert order into order_manager
    $stmt1 = mysqli_prepare(
        $con,
        "INSERT INTO order_manager (Full_Name, Phone_No, Address, Pay_Mode, Paid_Amount, order_status) VALUES (?,?,?,?,?,?)"
    );

    $payMode = "Khalti";
    $paidAmount = (int)($data['total_amount'] ?? 0); // amount in paisa
    $orderStatus = "pending";

    mysqli_stmt_bind_param(
        $stmt1,
        "ssssss",
        $pendingOrder['full_name'],
        $pendingOrder['phone_no'],
        $pendingOrder['address'],
        $payMode,
        $paidAmount,
        $orderStatus
    );
    
    if (!mysqli_stmt_execute($stmt1)) {
        throw new Exception("Failed to insert order: " . mysqli_error($con));
    }

    $Order_Id = mysqli_insert_id($con);
    mysqli_stmt_close($stmt1);

    // Insert order items
    $stmt2 = mysqli_prepare(
        $con,
        "INSERT INTO user_orders (Order_Id, Item_Name, Price, Quantity) VALUES (?,?,?,?)"
    );

    mysqli_stmt_bind_param($stmt2, "isii", $Order_Id, $Item_Name, $Price, $Quantity);

    foreach ($cartItems as $item) {
        $Item_Name = $item['Item_Name'];
        $Price = $item['Price'];
        $Quantity = $item['Quantity'];
        
        if (!mysqli_stmt_execute($stmt2)) {
            throw new Exception("Failed to insert order item: " . mysqli_error($con));
        }
    }

    mysqli_stmt_close($stmt2);

    // Commit transaction
    mysqli_commit($con);

    // Clear session
    unset($_SESSION['cart'], $_SESSION['pending_order']);

    $paidAmountRs = $paidAmount / 100; // convert paisa to rupees

    // Success page
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Payment Successful - Wosa</title>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet"/>
        <script src="https://kit.fontawesome.com/144a91ca19.js" crossorigin="anonymous"></script>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Montserrat', sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            
            .success-container {
                background: white;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                padding: 50px;
                max-width: 600px;
                text-align: center;
                animation: slideUp 0.5s ease-out;
            }
            
            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .success-icon {
                width: 100px;
                height: 100px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 30px;
                animation: scaleIn 0.5s ease-out 0.2s both;
            }
            
            @keyframes scaleIn {
                from {
                    transform: scale(0);
                }
                to {
                    transform: scale(1);
                }
            }
            
            .success-icon i {
                font-size: 50px;
                color: white;
            }
            
            h1 {
                color: #2c3e50;
                margin-bottom: 20px;
                font-size: 2.5rem;
            }
            
            .order-details {
                background: #f8f9fa;
                border-radius: 10px;
                padding: 25px;
                margin: 30px 0;
                text-align: left;
            }
            
            .detail-row {
                display: flex;
                justify-content: space-between;
                padding: 12px 0;
                border-bottom: 1px solid #dee2e6;
            }
            
            .detail-row:last-child {
                border-bottom: none;
            }
            
            .detail-label {
                font-weight: 600;
                color: #495057;
            }
            
            .detail-value {
                color: #212529;
            }
            
            .amount {
                font-size: 1.5rem;
                color: #28a745;
                font-weight: bold;
            }
            
            .buttons {
                display: flex;
                gap: 15px;
                justify-content: center;
                margin-top: 30px;
            }
            
            .btn {
                padding: 15px 30px;
                border: none;
                border-radius: 10px;
                font-size: 1rem;
                font-weight: 600;
                cursor: pointer;
                text-decoration: none;
                display: inline-block;
                transition: all 0.3s;
            }
            
            .btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }
            
            .btn-secondary {
                background: #6c757d;
                color: white;
            }
            
            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            }
        </style>
    </head>
    <body>
        <div class="success-container">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h1>Payment Successful!</h1>
            <p style="color: #6c757d; font-size: 1.1rem;">Thank you for your purchase</p>
            
            <div class="order-details">
                <div class="detail-row">
                    <span class="detail-label">Order ID:</span>
                    <span class="detail-value"><strong>#<?php echo $Order_Id; ?></strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Customer Name:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($pendingOrder['full_name']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method:</span>
                    <span class="detail-value">Khalti</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Amount Paid:</span>
                    <span class="detail-value amount">Rs. <?php echo number_format($paidAmountRs, 2); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Transaction ID:</span>
                    <span class="detail-value" style="font-size: 0.9rem;"><?php echo htmlspecialchars($pidx); ?></span>
                </div>
            </div>
            
            <p style="color: #6c757d; margin-top: 20px;">
                <i class="fas fa-info-circle"></i> 
                A confirmation email has been sent to your email address.
            </p>
            
            <div class="buttons">
                <a href="/Wosa/index.html" class="btn btn-primary">
                    <i class="fas fa-home"></i> Continue Shopping
                </a>
                <a href="/Wosa/my_orders.php" class="btn btn-secondary">
                    <i class="fas fa-list"></i> View Orders
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;

} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($con);
    
    echo "<h2 style='color: red;'>Order Processing Failed</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Your payment was successful, but we couldn't process your order. Please contact support with your transaction ID: <strong>$pidx</strong></p>";
    echo "<p><a href='index.php'>Go to Home</a></p>";
    exit;
}
?>