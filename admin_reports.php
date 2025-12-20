<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('location: admin_login.php');
    exit();
}

$db = mysqli_connect("localhost", "root", "", "testing", 3307);

// Date range for reports
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');

// Sales Statistics
$sales_query = "SELECT 
    COUNT(*) as total_orders,
    SUM(Paid_Amount) as total_revenue,
    AVG(Paid_Amount) as avg_order_value
    FROM order_manager 
    WHERE DATE(Order_Date) BETWEEN '$date_from' AND '$date_to'";
$sales_result = mysqli_query($db, $sales_query);
$sales_data = mysqli_fetch_assoc($sales_result);

// Orders by Status
$status_query = "SELECT 
    order_status, 
    COUNT(*) as count 
    FROM order_manager 
    WHERE DATE(Order_Date) BETWEEN '$date_from' AND '$date_to'
    GROUP BY order_status";
$status_result = mysqli_query($db, $status_query);

// Top Selling Products
$products_query = "SELECT 
    uo.Item_Name,
    SUM(uo.Quantity) as total_sold,
    SUM(uo.Price * uo.Quantity) as revenue
    FROM user_orders uo
    JOIN order_manager om ON uo.Order_Id = om.Order_Id
    WHERE DATE(om.Order_Date) BETWEEN '$date_from' AND '$date_to'
    GROUP BY uo.Item_Name
    ORDER BY total_sold DESC
    LIMIT 10";
$products_result = mysqli_query($db, $products_query);

// Daily Sales Chart Data
$daily_sales_query = "SELECT 
    DATE(Order_Date) as sale_date,
    COUNT(*) as orders_count,
    SUM(Paid_Amount) as daily_revenue
    FROM order_manager
    WHERE DATE(Order_Date) BETWEEN '$date_from' AND '$date_to'
    GROUP BY DATE(Order_Date)
    ORDER BY sale_date ASC";
$daily_sales_result = mysqli_query($db, $daily_sales_query);

// New Users in Period
$users_query = "SELECT COUNT(*) as new_users 
    FROM user_manager 
    WHERE DATE(created_at) BETWEEN '$date_from' AND '$date_to'";
$users_result = mysqli_query($db, $users_query);
$new_users = mysqli_fetch_assoc($users_result)['new_users'];

// Payment Methods Distribution
$payment_query = "SELECT 
    Pay_Mode,
    COUNT(*) as count,
    SUM(Paid_Amount) as total
    FROM order_manager
    WHERE DATE(Order_Date) BETWEEN '$date_from' AND '$date_to'
    GROUP BY Pay_Mode";
$payment_result = mysqli_query($db, $payment_query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reports - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <script src="https://kit.fontawesome.com/144a91ca19.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        .date-filter {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .date-filter form {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .date-filter input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }
        
        .stat-card.blue::before { background: #3498db; }
        .stat-card.green::before { background: #2ecc71; }
        .stat-card.orange::before { background: #f39c12; }
        .stat-card.purple::before { background: #9b59b6; }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        
        .blue .stat-icon { background: #e8f4f8; color: #3498db; }
        .green .stat-icon { background: #e8f8f0; color: #2ecc71; }
        .orange .stat-icon { background: #fef5e7; color: #f39c12; }
        .purple .stat-icon { background: #f4ecf7; color: #9b59b6; }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .chart-container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .chart-container h3 {
            margin-bottom: 20px;
            color: #2c3e50;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table thead {
            background: #f8f9fa;
        }
        
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        
        table th {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-success {
            background: #2ecc71;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
        }
    </style>
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
                <li><a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> Manage Orders</a></li>
                <li><a href="admin_reports.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="admin_settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="top-bar">
                <h1>Sales Reports & Analytics</h1>
                <a href="admin_logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

            <!-- Date Filter -->
            <div class="date-filter">
                <form method="GET" action="admin_reports.php">
                    <label><strong>Date Range:</strong></label>
                    <input type="date" name="date_from" value="<?php echo $date_from; ?>" required>
                    <label>to</label>
                    <input type="date" name="date_to" value="<?php echo $date_to; ?>" required>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Generate Report
                    </button>
                    <button type="button" onclick="exportReport()" class="btn btn-success">
                        <i class="fas fa-download"></i> Export CSV
                    </button>
                </form>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-value"><?php echo $sales_data['total_orders'] ?? 0; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>

                <div class="stat-card green">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-value">Rs. <?php echo number_format(($sales_data['total_revenue'] ?? 0) / 100, 2); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>

                <div class="stat-card orange">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-value">Rs. <?php echo number_format(($sales_data['avg_order_value'] ?? 0) / 100, 2); ?></div>
                    <div class="stat-label">Average Order Value</div>
                </div>

                <div class="stat-card purple">
                    <div class="stat-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="stat-value"><?php echo $new_users; ?></div>
                    <div class="stat-label">New Users</div>
                </div>
            </div>

            <!-- Daily Sales Chart -->
            <div class="chart-container">
                <h3><i class="fas fa-chart-area"></i> Daily Sales Trend</h3>
                <canvas id="salesChart" height="80"></canvas>
            </div>

            <!-- Top Products -->
            <div class="chart-container">
                <h3><i class="fas fa-trophy"></i> Top Selling Products</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Units Sold</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($product = mysqli_fetch_assoc($products_result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['Item_Name']); ?></td>
                            <td><?php echo $product['total_sold']; ?></td>
                            <td>Rs. <?php echo number_format($product['revenue'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Payment Methods & Status -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="chart-container">
                    <h3><i class="fas fa-credit-card"></i> Payment Methods</h3>
                    <canvas id="paymentChart"></canvas>
                </div>
                
                <div class="chart-container">
                    <h3><i class="fas fa-tasks"></i> Order Status Distribution</h3>
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Daily Sales Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: [
                    <?php 
                    mysqli_data_seek($daily_sales_result, 0);
                    while($row = mysqli_fetch_assoc($daily_sales_result)) {
                        echo "'" . date('M d', strtotime($row['sale_date'])) . "',";
                    }
                    ?>
                ],
                datasets: [{
                    label: 'Revenue (Rs.)',
                    data: [
                        <?php 
                        mysqli_data_seek($daily_sales_result, 0);
                        while($row = mysqli_fetch_assoc($daily_sales_result)) {
                            echo ($row['daily_revenue'] / 100) . ",";
                        }
                        ?>
                    ],
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });

        // Payment Methods Chart
        const paymentCtx = document.getElementById('paymentChart').getContext('2d');
        const paymentChart = new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    <?php 
                    while($payment = mysqli_fetch_assoc($payment_result)) {
                        echo "'" . $payment['Pay_Mode'] . "',";
                    }
                    ?>
                ],
                datasets: [{
                    data: [
                        <?php 
                        mysqli_data_seek($payment_result, 0);
                        while($payment = mysqli_fetch_assoc($payment_result)) {
                            echo $payment['count'] . ",";
                        }
                        ?>
                    ],
                    backgroundColor: ['#3498db', '#2ecc71', '#f39c12', '#e74c3c']
                }]
            }
        });

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: [
                    <?php 
                    while($status = mysqli_fetch_assoc($status_result)) {
                        echo "'" . ucfirst($status['order_status']) . "',";
                    }
                    ?>
                ],
                datasets: [{
                    data: [
                        <?php 
                        mysqli_data_seek($status_result, 0);
                        while($status = mysqli_fetch_assoc($status_result)) {
                            echo $status['count'] . ",";
                        }
                        ?>
                    ],
                    backgroundColor: ['#ffc107', '#17a2b8', '#6c757d', '#28a745', '#dc3545']
                }]
            }
        });

        function exportReport() {
            alert('CSV export functionality would be implemented here');
        }
    </script>
</body>
</html>