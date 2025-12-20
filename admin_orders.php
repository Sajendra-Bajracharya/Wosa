<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('location: admin_login.php');
    exit();
}

$db = mysqli_connect("localhost", "root", "", "testing", 3307);

// Handle Update Order Status
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = mysqli_real_escape_string($db, $_POST['status']);
    
    $update_query = "UPDATE order_manager SET order_status='$new_status' WHERE Order_Id=$order_id";
    if (mysqli_query($db, $update_query)) {
        $success_message = "Order status updated successfully!";
    } else {
        $error_message = "Error updating status: " . mysqli_error($db);
    }
}

// Handle Delete Order
if (isset($_GET['delete'])) {
    $order_id = intval($_GET['delete']);
    
    // Delete order items first
    mysqli_query($db, "DELETE FROM user_orders WHERE Order_Id = $order_id");
    
    // Delete main order
    $delete_query = "DELETE FROM order_manager WHERE Order_Id = $order_id";
    if (mysqli_query($db, $delete_query)) {
        $success_message = "Order deleted successfully!";
    } else {
        $error_message = "Error deleting order: " . mysqli_error($db);
    }
}

// Search and Filter
$search = '';
$status_filter = '';
$date_from = '';
$date_to = '';
$where_conditions = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($db, $_GET['search']);
    $where_conditions[] = "(Order_Id LIKE '%$search%' OR Full_Name LIKE '%$search%' OR Phone_No LIKE '%$search%')";
}

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status_filter = mysqli_real_escape_string($db, $_GET['status']);
    $where_conditions[] = "order_status = '$status_filter'";
}

if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
    $date_from = mysqli_real_escape_string($db, $_GET['date_from']);
    $where_conditions[] = "DATE(Order_Date) >= '$date_from'";
}

if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
    $date_to = mysqli_real_escape_string($db, $_GET['date_to']);
    $where_conditions[] = "DATE(Order_Date) <= '$date_to'";
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(' AND ', $where_conditions);
}

$orders_query = "SELECT * FROM order_manager $where_clause ORDER BY Order_Date DESC";
$orders_result = mysqli_query($db, $orders_query);

// Get order details for modal
$order_details = null;
if (isset($_GET['view'])) {
    $view_id = intval($_GET['view']);
    $order_query = "SELECT * FROM order_manager WHERE Order_Id = $view_id";
    $order_result = mysqli_query($db, $order_query);
    $order_details = mysqli_fetch_assoc($order_result);
    
    if ($order_details) {
        $items_query = "SELECT * FROM user_orders WHERE Order_Id = $view_id";
        $order_items = mysqli_query($db, $items_query);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Orders - Admin</title>
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
            background: #f5f6fa;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 30px;
        }
        
        .sidebar-header i {
            font-size: 3rem;
            margin-bottom: 10px;
            color: #f39c12;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 10px;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 12px 15px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 30px;
        }
        
        .top-bar {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .content-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .filter-box {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-box input, .filter-box select {
            flex: 1;
            min-width: 150px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.95rem;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            transition: all 0.3s;
            font-size: 0.95rem;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-success {
            background: #2ecc71;
            color: white;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-warning {
            background: #f39c12;
            color: white;
        }
        
        .btn-sm {
            padding: 8px 15px;
            font-size: 0.85rem;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table thead {
            background: #f8f9fa;
        }
        
        table th, table td {
            padding: 15px 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        
        table th {
            font-weight: 600;
            color: #2c3e50;
        }
        
        table tr:hover {
            background: #f8f9fa;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-processing {
            background: #cfe2ff;
            color: #084298;
        }
        
        .badge-shipped {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .badge-delivered {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background: white;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 700px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .close {
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            color: #7f8c8d;
        }
        
        .close:hover {
            color: #e74c3c;
        }
        
        .order-details {
            margin-bottom: 20px;
        }
        
        .detail-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .detail-label {
            font-weight: 600;
            width: 150px;
            color: #2c3e50;
        }
        
        .detail-value {
            flex: 1;
            color: #34495e;
        }
        
        .items-list {
            margin-top: 20px;
        }
        
        .items-list h3 {
            margin-bottom: 15px;
            color: #2c3e50;
        }
        
        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            background: #f8f9fa;
            margin-bottom: 8px;
            border-radius: 5px;
        }
    </style>
    <script>
        function confirmDelete(orderId) {
            return confirm('Are you sure you want to delete order #' + orderId + '? This action cannot be undone.');
        }
        
        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-user-shield"></i>
                <h2>Admin Panel</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="admin_users.php"><i class="fas fa-users"></i> Manage Users</a></li>
                <li><a href="admin_products.php"><i class="fas fa-box"></i> Manage Products</a></li>
                <li><a href="admin_orders.php" class="active"><i class="fas fa-shopping-cart"></i> Manage Orders</a></li>
                <li><a href="admin_reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="admin_settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="top-bar">
                <h1>Manage Orders</h1>
                <a href="admin_logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

            <div class="content-section">
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <div class="section-header">
                    <h2><i class="fas fa-shopping-cart"></i> All Orders</h2>
                </div>

                <!-- Filter Box -->
                <form method="GET" action="admin_orders.php" class="filter-box">
                    <input type="text" name="search" placeholder="Search by Order ID, Name, Phone..." value="<?php echo htmlspecialchars($search); ?>">
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    <input type="date" name="date_from" placeholder="From Date" value="<?php echo htmlspecialchars($date_from); ?>">
                    <input type="date" name="date_to" placeholder="To Date" value="<?php echo htmlspecialchars($date_to); ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <?php if ($search || $status_filter || $date_from || $date_to): ?>
                        <a href="admin_orders.php" class="btn btn-warning">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    <?php endif; ?>
                </form>

                <!-- Orders Table -->
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Payment</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($orders_result) > 0): ?>
                            <?php while($order = mysqli_fetch_assoc($orders_result)): ?>
                            <tr>
                                <td><strong>#<?php echo $order['Order_Id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($order['Full_Name']); ?></td>
                                <td><?php echo htmlspecialchars($order['Phone_No']); ?></td>
                                <td><?php echo htmlspecialchars($order['Pay_Mode']); ?></td>
                                <td>Rs. <?php echo number_format($order['Paid_Amount'] / 100, 2); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $order['order_status'] ?? 'pending'; ?>">
                                        <?php echo ucfirst($order['order_status'] ?? 'pending'); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($order['Order_Date'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="admin_orders.php?view=<?php echo $order['Order_Id']; ?>" class="btn btn-primary btn-sm" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="admin_orders.php?delete=<?php echo $order['Order_Id']; ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirmDelete('<?php echo $order['Order_Id']; ?>')"
                                           title="Delete Order">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 30px;">
                                    <i class="fas fa-shopping-cart" style="font-size: 3rem; color: #bdc3c7;"></i>
                                    <p style="margin-top: 10px; color: #7f8c8d;">No orders found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal" style="display: <?php echo $order_details ? 'block' : 'none'; ?>;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Order Details #<?php echo $order_details['Order_Id'] ?? ''; ?></h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            
            <?php if ($order_details): ?>
                <div class="order-details">
                    <div class="detail-row">
                        <div class="detail-label">Order ID:</div>
                        <div class="detail-value">#<?php echo $order_details['Order_Id']; ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Customer Name:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($order_details['Full_Name']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Phone:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($order_details['Phone_No']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Address:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($order_details['Address']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Payment Method:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($order_details['Pay_Mode']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Amount Paid:</div>
                        <div class="detail-value">Rs. <?php echo number_format($order_details['Paid_Amount'] / 100, 2); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Order Date:</div>
                        <div class="detail-value"><?php echo date('M d, Y H:i', strtotime($order_details['Order_Date'])); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Status:</div>
                        <div class="detail-value">
                            <form method="POST" action="admin_orders.php" style="display: flex; gap: 10px;">
                                <input type="hidden" name="order_id" value="<?php echo $order_details['Order_Id']; ?>">
                                <select name="status" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                    <option value="pending" <?php echo ($order_details['order_status'] ?? 'pending') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo ($order_details['order_status'] ?? 'pending') === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="shipped" <?php echo ($order_details['order_status'] ?? 'pending') === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="delivered" <?php echo ($order_details['order_status'] ?? 'pending') === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo ($order_details['order_status'] ?? 'pending') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-success btn-sm">Update</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="items-list">
                    <h3>Order Items</h3>
                    <?php while($item = mysqli_fetch_assoc($order_items)): ?>
                        <div class="item-row">
                            <div>
                                <strong><?php echo htmlspecialchars($item['Item_Name']); ?></strong><br>
                                <small>Quantity: <?php echo $item['Quantity']; ?></small>
                            </div>
                            <div>
                                <strong>Rs. <?php echo number_format($item['Price'] * $item['Quantity'], 2); ?></strong>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>