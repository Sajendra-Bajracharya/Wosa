<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit();
}

$db = mysqli_connect("localhost", "root", "", "testing", 3307);
if (mysqli_connect_error()) {
    die("Database connection failed: " . mysqli_connect_error());
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['username'];

// Get user's full name from user_manager table
$user_query = "SELECT username, phone_number FROM user_manager WHERE id = ?";
$stmt = mysqli_prepare($db, $user_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($user_result);
$user_full_name = $user_data['username'] ?? '';
$user_phone = $user_data['phone_number'] ?? '';

// Get all orders for this user (matching by name OR phone number for better matching)
// This way orders will show up even if entered with slight variations
$orders_query = "SELECT * FROM order_manager 
                 WHERE Full_Name LIKE ? OR Phone_No = ?
                 ORDER BY Order_Date DESC";
$stmt = mysqli_prepare($db, $orders_query);
$name_pattern = "%{$user_full_name}%";
mysqli_stmt_bind_param($stmt, "ss", $name_pattern, $user_phone);
mysqli_stmt_execute($stmt);
$orders_result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Wosa</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <script src="https://kit.fontawesome.com/144a91ca19.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/style.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: #f5f6fa;
            min-height: 100vh;
        }

        .orders-container {
            max-width: 1200px;
            margin: 100px auto 50px;
            padding: 20px;
        }

        .page-header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .page-header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #7f8c8d;
        }

        .orders-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .order-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s;
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .order-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .order-id {
            font-size: 1.3rem;
            font-weight: 600;
        }

        .order-date {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .order-status {
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: capitalize;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background: #cfe2ff;
            color: #084298;
        }

        .status-shipped {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-delivered {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .order-body {
            padding: 30px;
        }

        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 2px solid #ecf0f1;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .info-label {
            color: #7f8c8d;
            font-size: 0.85rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            color: #2c3e50;
            font-size: 1rem;
            font-weight: 600;
        }

        .order-items {
            margin-top: 20px;
        }

        .order-items h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .item-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .item-row:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .item-quantity {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .item-price {
            font-weight: 700;
            color: #667eea;
            font-size: 1.1rem;
        }

        .order-total {
            margin-top: 20px;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }

        .total-label {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .total-amount {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .empty-state {
            background: white;
            border-radius: 15px;
            padding: 60px 30px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .empty-state i {
            font-size: 5rem;
            color: #bdc3c7;
            margin-bottom: 20px;
        }

        .empty-state h2 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .empty-state p {
            color: #7f8c8d;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }

        .btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #95a5a6;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
            box-shadow: 0 10px 25px rgba(149, 165, 166, 0.4);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .back-link:hover {
            transform: translateX(-5px);
            color: #764ba2;
        }

        .track-order-btn {
            background: transparent;
            border: 2px solid white;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .track-order-btn:hover {
            background: white;
            color: #667eea;
        }

        @media (max-width: 768px) {
            .orders-container {
                margin: 80px auto 30px;
                padding: 15px;
            }

            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .order-info {
                grid-template-columns: 1fr;
            }

            .item-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .order-total {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <?php include("header.php"); ?>

    <div class="orders-container">
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>

        <div class="page-header">
            <h1><i class="fas fa-shopping-bag"></i> My Orders</h1>
            <p>Welcome back, <strong><?php echo htmlspecialchars($user_name); ?></strong>! Here are all your orders.</p>
        </div>

        <?php if (mysqli_num_rows($orders_result) > 0): ?>
            <div class="orders-list">
                <?php while($order = mysqli_fetch_assoc($orders_result)): ?>
                    <?php
                    // Get order items
                    $order_id = $order['Order_Id'];
                    $items_query = "SELECT * FROM user_orders WHERE Order_Id = ?";
                    $stmt_items = mysqli_prepare($db, $items_query);
                    mysqli_stmt_bind_param($stmt_items, "i", $order_id);
                    mysqli_stmt_execute($stmt_items);
                    $items_result = mysqli_stmt_get_result($stmt_items);
                    
                    // Determine status class
                    $status = $order['order_status'] ?? 'pending';
                    $status_class = 'status-' . $status;
                    ?>
                    
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <div class="order-id">Order #<?php echo $order['Order_Id']; ?></div>
                                <div class="order-date">
                                    <i class="far fa-calendar"></i>
                                    <?php echo date('F d, Y • g:i A', strtotime($order['Order_Date'])); ?>
                                </div>
                            </div>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <span class="order-status <?php echo $status_class; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </div>
                        </div>

                        <div class="order-body">
                            <div class="order-info">
                                <div class="info-item">
                                    <span class="info-label">
                                        <i class="fas fa-user"></i> Customer Name
                                    </span>
                                    <span class="info-value"><?php echo htmlspecialchars($order['Full_Name']); ?></span>
                                </div>

                                <div class="info-item">
                                    <span class="info-label">
                                        <i class="fas fa-phone"></i> Phone Number
                                    </span>
                                    <span class="info-value"><?php echo htmlspecialchars($order['Phone_No']); ?></span>
                                </div>

                                <div class="info-item">
                                    <span class="info-label">
                                        <i class="fas fa-map-marker-alt"></i> Delivery Address
                                    </span>
                                    <span class="info-value"><?php echo htmlspecialchars($order['Address']); ?></span>
                                </div>

                                <div class="info-item">
                                    <span class="info-label">
                                        <i class="fas fa-credit-card"></i> Payment Method
                                    </span>
                                    <span class="info-value"><?php echo htmlspecialchars($order['Pay_Mode']); ?></span>
                                </div>
                            </div>

                            <div class="order-items">
                                <h3><i class="fas fa-box"></i> Order Items</h3>
                                <div class="item-list">
                                    <?php 
                                    $total = 0;
                                    while($item = mysqli_fetch_assoc($items_result)): 
                                        $item_total = $item['Price'] * $item['Quantity'];
                                        $total += $item_total;
                                    ?>
                                        <div class="item-row">
                                            <div class="item-details">
                                                <div class="item-name"><?php echo htmlspecialchars($item['Item_Name']); ?></div>
                                                <div class="item-quantity">
                                                    Quantity: <?php echo $item['Quantity']; ?> × Rs. <?php echo number_format($item['Price'], 2); ?>
                                                </div>
                                            </div>
                                            <div class="item-price">
                                                Rs. <?php echo number_format($item_total, 2); ?>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>

                            <div class="order-total">
                                <span class="total-label">
                                    <i class="fas fa-receipt"></i> Total Amount Paid:
                                </span>
                                <span class="total-amount">Rs. <?php echo number_format($order['Paid_Amount'] / 100, 2); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-shopping-basket"></i>
                <h2>No Orders Yet</h2>
                <p>You haven't placed any orders yet. Start shopping to see your orders here!</p>
                <a href="index.php" class="btn">
                    <i class="fas fa-shopping-cart"></i> Start Shopping
                </a>
                <br><br>
                <p style="font-size: 0.9rem; color: #95a5a6;">
                    Orders are matched by your account name: <strong><?php echo htmlspecialchars($user_full_name); ?></strong>
                </p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Add smooth scroll behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>
</html>