<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$db = mysqli_connect("localhost", "root", "", "testing", 3307);
$user_id = $_SESSION['user_id'];

// Get user's orders - Fixed query to properly match user_id
$orders_query = "SELECT om.*, 
                 GROUP_CONCAT(CONCAT(uo.Item_Name, ' (x', uo.Quantity, ')') SEPARATOR ', ') as items,
                 SUM(uo.Price * uo.Quantity) as total_amount
                 FROM order_manager om
                 LEFT JOIN user_orders uo ON om.Order_Id = uo.Order_Id
                 WHERE om.user_id = $user_id
                 GROUP BY om.Order_Id
                 ORDER BY om.Order_Date DESC";
$orders_result = mysqli_query($db, $orders_query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Orders - Wosa</title>
    <link rel="stylesheet" href="css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <script src="https://kit.fontawesome.com/144a91ca19.js" crossorigin="anonymous"></script>
    <style>
        .orders-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .order-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
            margin-bottom: 15px;
        }
        
        .order-id {
            font-size: 1.3rem;
            font-weight: bold;
            color: #3b82f6;
        }
        
        .order-date {
            color: #7f8c8d;
        }
        
        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }
        
        .detail-item {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 5px;
        }
        
        .detail-label {
            font-size: 0.85rem;
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .order-items {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
        
        .order-items h4 {
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .payment-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .payment-cod {
            background: #fef5e7;
            color: #f39c12;
        }
        
        .payment-khalti {
            background: #dbeafe;
            color: #3b82f6;
        }
        
        .no-orders {
            text-align: center;
            padding: 60px 20px;
        }
        
        .no-orders i {
            font-size: 5rem;
            color: #bdc3c7;
            margin-bottom: 20px;
        }
        
        .no-orders h2 {
            color: #7f8c8d;
            margin-bottom: 15px;
        }
        
        .btn-shop {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            margin-top: 20px;
            transition: transform 0.3s;
        }
        
        .btn-shop:hover {
            transform: scale(1.05);
        }
        
        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background: #f8f9fa;
            color: #2c3e50;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .back-btn:hover {
            background: #e9ecef;
        }
    </style>
</head>
<body>
    <header class="nav-section">
        <div class="logo-container">
            <img class="logo" src="images/logo.png" alt="Logo" />
        </div>
        <div class="main-nav-links">
            <ul class="main-nav-list">
                <li><a class="main-nav-link" href="index.html">Home</a></li>
                <li><a class="main-nav-link" href="aboutus.html">About</a></li>
                <li><a class="main-nav-link" href="contact.html">Contact</a></li>
            </ul>
        </div>
        <div class="nav-icon">
            <a href="login.php">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 icons">
                    <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd"/>
                </svg>
            </a>
            <a href="mycart.php">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 icons">
                    <path fill-rule="evenodd" d="M7.5 6v.75H5.513c-.96 0-1.764.724-1.865 1.679l-1.263 12A1.875 1.875 0 004.25 22.5h15.5a1.875 1.875 0 001.865-2.071l-1.263-12a1.875 1.875 0 00-1.865-1.679H16.5V6a4.5 4.5 0 10-9 0zM12 3a3 3 0 00-3 3v.75h6V6a3 3 0 00-3-3zm-3 8.25a3 3 0 106 0v-.75a.75.75 0 011.5 0v.75a4.5 4.5 0 11-9 0v-.75a.75.75 0 011.5 0v.75z" clip-rule="evenodd"/>
                </svg>
            </a>
        </div>
    </header>

    <div class="orders-container">
        <a href="login.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
        
        <div class="page-header">
            <h1><i class="fas fa-box"></i> My Orders</h1>
            <p>Track and view your order history</p>
        </div>

        <?php if ($orders_result && mysqli_num_rows($orders_result) > 0): ?>
            <?php while($order = mysqli_fetch_assoc($orders_result)): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-id">Order #<?php echo $order['Order_Id']; ?></div>
                            <div class="order-date">
                                <i class="fas fa-calendar"></i> 
                                <?php echo date('F d, Y - h:i A', strtotime($order['Order_Date'])); ?>
                            </div>
                        </div>
                        <div>
                            <span class="payment-badge <?php echo strtolower($order['Pay_Mode']) == 'cod' ? 'payment-cod' : 'payment-khalti'; ?>">
                                <i class="fas fa-money-bill-wave"></i> <?php echo $order['Pay_Mode']; ?>
                            </span>
                        </div>
                    </div>

                    <div class="order-details">
                        <div class="detail-item">
                            <div class="detail-label">Customer Name</div>
                            <div class="detail-value"><?php echo htmlspecialchars($order['Full_Name']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Phone Number</div>
                            <div class="detail-value"><?php echo htmlspecialchars($order['Phone_No']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Delivery Address</div>
                            <div class="detail-value"><?php echo htmlspecialchars($order['Address']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Total Amount</div>
                            <div class="detail-value" style="color: #27ae60; font-size: 1.2rem;">
                                Rs. <?php echo number_format($order['total_amount']); ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($order['items']): ?>
                    <div class="order-items">
                        <h4><i class="fas fa-shopping-bag"></i> Items Ordered</h4>
                        <p><?php echo htmlspecialchars($order['items']); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-orders">
                <i class="fas fa-box-open"></i>
                <h2>No Orders Yet</h2>
                <p>You haven't placed any orders yet. Start shopping now!</p>
                <a href="index.html" class="btn-shop">
                    <i class="fas fa-shopping-bag"></i> Start Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <div class="col">
            <h4>Contact</h4>
            <p><strong>Address: </strong> Dubarmarg, Kathmandu</p>
            <p><strong>Phone:</strong> +977 98XXXXXXXX</p>
            <p><strong>Hours:</strong> 10:00 - 19:00, Sun - Fri</p>
        </div>
        <div class="col links">
            <h4>About</h4>
            <a href="aboutus.html">About Us</a>
            <a href="contact.html">Contact Us</a>
        </div>
        <div class="col links">
            <h4>My Accounts</h4>
            <a href="login.php">Sign In</a>
            <a href="mycart.php">View Cart</a>
        </div>
        <div class="col payment-partners">
            <h4>Payment Partners</h4>
            <p>Secured Payment Gateways</p>
            <div>
                <img src="images/esewa.png" alt="eSewa" />
                <img src="images/khalti.png" alt="Khalti" />
            </div>
        </div>
    </footer>
</body>
</html>