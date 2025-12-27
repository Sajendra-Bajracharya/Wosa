<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = mysqli_connect("localhost", "root", "", "testing", 3307);
if (mysqli_connect_error()) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Check if user is logged in
$logged_in = isset($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? 'Not logged in';
$username = $_SESSION['username'] ?? 'Not logged in';
$user_phone = 'Not found';

if ($logged_in) {
    // Get user's info from database
    $user_query = "SELECT username, phone_number FROM user_manager WHERE id = ?";
    $stmt = mysqli_prepare($db, $user_query);
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user_data = mysqli_fetch_assoc($result);
    $username = $user_data['username'] ?? 'Not found';
    $user_phone = $user_data['phone_number'] ?? 'Not found';
}

// Get all orders in database
$all_orders_query = "SELECT * FROM order_manager ORDER BY Order_Date DESC LIMIT 10";
$all_orders = mysqli_query($db, $all_orders_query);

// Get orders matching user's name OR phone
$user_orders = [];
if ($logged_in && $username !== 'Not found') {
    $user_orders_query = "SELECT * FROM order_manager 
                          WHERE Full_Name LIKE ? OR Phone_No = ?
                          ORDER BY Order_Date DESC";
    $stmt = mysqli_prepare($db, $user_orders_query);
    $name_pattern = "%{$username}%";
    mysqli_stmt_bind_param($stmt, "ss", $name_pattern, $user_phone);
    mysqli_stmt_execute($stmt);
    $user_orders_result = mysqli_stmt_get_result($stmt);
    while($row = mysqli_fetch_assoc($user_orders_result)) {
        $user_orders[] = $row;
    }
}

// Get total orders count
$count_query = "SELECT COUNT(*) as total FROM order_manager";
$count_result = mysqli_query($db, $count_query);
$total_orders = mysqli_fetch_assoc($count_result)['total'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Debug Helper</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .section h2 {
            color: #34495e;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #3498db;
        }
        .info-label {
            font-weight: bold;
            color: #7f8c8d;
            font-size: 0.85rem;
            margin-bottom: 5px;
        }
        .info-value {
            color: #2c3e50;
            font-size: 1.1rem;
        }
        .success {
            background: #d4edda;
            border-left-color: #28a745;
        }
        .warning {
            background: #fff3cd;
            border-left-color: #ffc107;
        }
        .error {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        table th {
            background: #34495e;
            color: white;
            font-weight: 600;
        }
        table tr:hover {
            background: #f8f9fa;
        }
        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
        }
        .btn:hover {
            background: #2980b9;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .alert-info {
            background: #d1ecf1;
            border-left: 4px solid #0c5460;
            color: #0c5460;
        }
        .alert-warning {
            background: #fff3cd;
            border-left: 4px solid #856404;
            color: #856404;
        }
        .alert-success {
            background: #d4edda;
            border-left: 4px solid #155724;
            color: #155724;
        }
        code {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .highlight {
            background: #d4edda !important;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1> Order Debug Helper</h1>
        <p style="color: #7f8c8d; margin-bottom: 30px;">
            This page helps diagnose why orders aren't showing in My Orders page.<br>
            <strong>Note:</strong> Orders are now matched by <strong>customer name</strong> (with partial matching).
        </p>

        <!-- User Session Info -->
        <div class="section">
            <h2>👤 Current User Session</h2>
            <div class="info-grid">
                <div class="info-item <?php echo $logged_in ? 'success' : 'error'; ?>">
                    <div class="info-label">Login Status</div>
                    <div class="info-value"><?php echo $logged_in ? '✅ Logged In' : '❌ Not Logged In'; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">User ID</div>
                    <div class="info-value"><?php echo $user_id; ?></div>
                </div>
                <div class="info-item <?php echo $username !== 'Not found' && $username !== 'Not logged in' ? 'success' : 'error'; ?>">
                    <div class="info-label">Username (for matching orders)</div>
                    <div class="info-value"><?php echo htmlspecialchars($username); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Phone Number (backup match)</div>
                    <div class="info-value"><?php echo htmlspecialchars($user_phone); ?></div>
                </div>
            </div>
            
            <?php if ($logged_in): ?>
                <div class="alert alert-info">
                    <strong>🎯 Matching Strategy:</strong><br>
                    Orders will show if the order name contains <code><?php echo htmlspecialchars($username); ?></code> 
                    OR if the phone matches <code><?php echo htmlspecialchars($user_phone); ?></code>
                </div>
            <?php endif; ?>
        </div>

        <!-- Orders Database Status -->
        <div class="section">
            <h2>Orders Database Status</h2>
            <div class="info-grid">
                <div class="info-item <?php echo $total_orders > 0 ? 'success' : 'warning'; ?>">
                    <div class="info-label">Total Orders in Database</div>
                    <div class="info-value"><?php echo $total_orders; ?></div>
                </div>
                <div class="info-item <?php echo count($user_orders) > 0 ? 'success' : 'warning'; ?>">
                    <div class="info-label">Orders Matching Your Account</div>
                    <div class="info-value"><?php echo count($user_orders); ?></div>
                </div>
            </div>

            <?php if ($total_orders == 0): ?>
                <div class="alert alert-warning">
                    <strong>No orders found in database!</strong><br>
                    This means no orders have been placed yet, or the orders aren't being saved to the database.
                </div>
            <?php elseif (count($user_orders) == 0 && $logged_in): ?>
                <div class="alert alert-warning">
                    <strong>Orders exist but none match your account!</strong><br>
                    Your username: <code><?php echo htmlspecialchars($username); ?></code><br>
                    Your phone: <code><?php echo htmlspecialchars($user_phone); ?></code><br><br>
                    <strong>This usually means:</strong>
                    <ul style="margin: 10px 0 0 20px;">
                        <li>You entered a different name when placing the order</li>
                        <li>Orders were placed before creating this account</li>
                        <li>The name in your order doesn't contain your username</li>
                    </ul>
                </div>
            <?php elseif (count($user_orders) > 0): ?>
                <div class="alert alert-success">
                    <strong>Perfect! Found <?php echo count($user_orders); ?> order(s) for you.</strong><br>
                    These orders match your account and should appear in My Orders page.
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Orders in Database -->
        <div class="section">
            <h2>Recent Orders (Last 10)</h2>
            <p style="margin-bottom: 15px; color: #7f8c8d;">
                <strong>Green rows</strong> = Orders that match your account
            </p>
            <?php if (mysqli_num_rows($all_orders) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer Name</th>
                            <th>Phone Number</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Match Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        mysqli_data_seek($all_orders, 0);
                        while($order = mysqli_fetch_assoc($all_orders)): 
                            // Check if this order matches the user
                            $name_matches = (stripos($order['Full_Name'], $username) !== false);
                            $phone_matches = ($order['Phone_No'] === $user_phone);
                            $matches_user = ($name_matches || $phone_matches);
                        ?>
                            <tr class="<?php echo $matches_user ? 'highlight' : ''; ?>">
                                <td><strong>#<?php echo $order['Order_Id']; ?></strong></td>
                                <td>
                                    <?php echo htmlspecialchars($order['Full_Name']); ?>
                                    <?php if ($name_matches): ?>
                                        <br><small style="color: #28a745;">✓ Name matches</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($order['Phone_No']); ?>
                                    <?php if ($phone_matches): ?>
                                        <br><small style="color: #28a745;">✓ Phone matches</small>
                                    <?php endif; ?>
                                </td>
                                <td>Rs. <?php echo number_format($order['Paid_Amount'] / 100, 2); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($order['Order_Date'])); ?></td>
                                <td>
                                    <?php if ($matches_user): ?>
                                        <span class="badge badge-success">✓ Your Order</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Other User</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="padding: 20px; background: #f8f9fa; border-radius: 5px; text-align: center; color: #7f8c8d;">
                    No orders found in database.
                </p>
            <?php endif; ?>
        </div>

        <!-- Solutions -->
        <div class="section">
            <h2>🔧 Common Solutions</h2>
            
            <?php if (!$logged_in): ?>
                <div class="alert alert-warning">
                    <strong>Problem: You're not logged in</strong><br>
                    <strong>Solution:</strong> <a href="login.php" class="btn">Login Here</a>
                </div>
            <?php endif; ?>

            <?php if ($username === 'Not found' && $logged_in): ?>
                <div class="alert alert-warning">
                    <strong>Problem: Your user account doesn't have a username</strong><br>
                    <strong>Solution:</strong> This is unusual. Check the user_manager table for your user ID.
                </div>
            <?php endif; ?>

            <?php if ($total_orders > 0 && count($user_orders) == 0 && $logged_in && $username !== 'Not found'): ?>
                <div class="alert alert-info">
                    <strong>Problem: Orders exist but don't match your account</strong><br>
                    <strong>Your account username:</strong> <code><?php echo htmlspecialchars($username); ?></code><br>
                    <strong>Your account phone:</strong> <code><?php echo htmlspecialchars($user_phone); ?></code><br><br>
                    
                    <strong>Possible reasons:</strong>
                    <ul style="margin: 10px 0 0 20px;">
                        <li>You entered a different name during checkout</li>
                        <li>Orders were placed before creating this account</li>
                        <li>The customer name in orders doesn't match your username</li>
                    </ul>
                    
                    <strong>Solutions:</strong>
                    <ol style="margin: 10px 0 0 20px;">
                        <li><strong>Option 1:</strong> Place a new order using your current account name</li>
                        <li><strong>Option 2:</strong> Update existing orders to match your username:
                            <pre style="background: #2c3e50; color: #ecf0f1; padding: 10px; border-radius: 5px; margin-top: 10px;">UPDATE order_manager 
SET Full_Name = '<?php echo htmlspecialchars($username); ?>' 
WHERE Order_Id = YOUR_ORDER_ID;</pre>
                        </li>
                        <li><strong>Option 3:</strong> Make sure you use the same name when checking out as your account username</li>
                    </ol>
                </div>
            <?php endif; ?>

            <?php if ($total_orders == 0): ?>
                <div class="alert alert-info">
                    <strong>No orders in database yet</strong><br>
                    <strong>What to do:</strong>
                    <ol style="margin: 10px 0 0 20px;">
                        <li>Place a test order to verify the system works</li>
                        <li>Make sure you use your account name: <code><?php echo htmlspecialchars($username); ?></code></li>
                        <li>Complete the Khalti payment successfully</li>
                        <li>Check if the order appears in admin panel</li>
                    </ol>
                </div>
            <?php endif; ?>

            <?php if (count($user_orders) > 0): ?>
                <div class="alert alert-success">
                    <strong> Everything looks good!</strong><br>
                    You have <?php echo count($user_orders); ?> order(s) that should appear on your My Orders page.<br>
                    If they're not showing, try clearing your browser cache and refreshing.
                </div>
            <?php endif; ?>
        </div>

        <!-- Action Buttons -->
        <div class="section">
            <h2> Quick Actions</h2>
            <a href="my_orders.php" class="btn">View My Orders Page</a>
            <a href="index.php" class="btn">Go to Home</a>
            <?php if (!$logged_in): ?>
                <a href="login.php" class="btn">Login</a>
            <?php endif; ?>
            <a href="mycart.php" class="btn">View Cart</a>
        </div>

        <!-- Tip -->
        <div class="section" style="background: #e8f4f8; border-left: 4px solid #3498db;">
            <h2> Pro Tip</h2>
            <p style="color: #2c3e50;">
                <strong>To ensure your orders always show up:</strong>
            </p>
            <ul style="margin: 10px 0 0 20px; color: #34495e;">
                <li>Always use the same name when placing orders as your account username</li>
                <li>Your account username: <code style="background: white; color: #2c3e50;"><?php echo htmlspecialchars($username); ?></code></li>
                <li>Orders are matched by name (partial match) OR phone number</li>
                <li>This means if your order name contains your username, it will show up!</li>
            </ul>
        </div>
    </div>
</body>
</html>