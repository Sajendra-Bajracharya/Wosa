<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('location: admin_login.php');
    exit();
}

$db = mysqli_connect("localhost", "root", "", "testing", 3307);

// Handle Add Product
if (isset($_POST['add_product'])) {
    $product_name = mysqli_real_escape_string($db, $_POST['product_name']);
    $category = mysqli_real_escape_string($db, $_POST['category']);
    $price = floatval($_POST['price']);
    $description = mysqli_real_escape_string($db, $_POST['description']);
    $image_path = mysqli_real_escape_string($db, $_POST['image_path']);
    $stock_quantity = intval($_POST['stock_quantity']);
    
    $insert_query = "INSERT INTO products (product_name, category, price, description, image_path, stock_quantity) 
                     VALUES ('$product_name', '$category', $price, '$description', '$image_path', $stock_quantity)";
    
    if (mysqli_query($db, $insert_query)) {
        $success_message = "Product added successfully!";
    } else {
        $error_message = "Error adding product: " . mysqli_error($db);
    }
}

// Handle Update Product
if (isset($_POST['update_product'])) {
    $product_id = intval($_POST['product_id']);
    $product_name = mysqli_real_escape_string($db, $_POST['product_name']);
    $category = mysqli_real_escape_string($db, $_POST['category']);
    $price = floatval($_POST['price']);
    $description = mysqli_real_escape_string($db, $_POST['description']);
    $image_path = mysqli_real_escape_string($db, $_POST['image_path']);
    $stock_quantity = intval($_POST['stock_quantity']);
    
    $update_query = "UPDATE products SET 
                     product_name='$product_name', 
                     category='$category', 
                     price=$price, 
                     description='$description', 
                     image_path='$image_path', 
                     stock_quantity=$stock_quantity 
                     WHERE id=$product_id";
    
    if (mysqli_query($db, $update_query)) {
        $success_message = "Product updated successfully!";
    } else {
        $error_message = "Error updating product: " . mysqli_error($db);
    }
}

// Handle Delete Product
if (isset($_GET['delete'])) {
    $product_id = intval($_GET['delete']);
    $delete_query = "DELETE FROM products WHERE id = $product_id";
    if (mysqli_query($db, $delete_query)) {
        $success_message = "Product deleted successfully!";
    } else {
        $error_message = "Error deleting product: " . mysqli_error($db);
    }
}

// Search and Filter
$search = '';
$category_filter = '';
$where_conditions = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($db, $_GET['search']);
    $where_conditions[] = "(product_name LIKE '%$search%' OR description LIKE '%$search%')";
}

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $category_filter = mysqli_real_escape_string($db, $_GET['category']);
    $where_conditions[] = "category = '$category_filter'";
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(' AND ', $where_conditions);
}

$products_query = "SELECT * FROM products $where_clause ORDER BY created_at DESC";
$products_result = mysqli_query($db, $products_query);

// Get product for editing
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_query = "SELECT * FROM products WHERE id = $edit_id";
    $edit_result = mysqli_query($db, $edit_query);
    $edit_product = mysqli_fetch_assoc($edit_result);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Products - Admin</title>
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
            margin-bottom: 20px;
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
            min-width: 200px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
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
            gap: 10px;
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
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            font-family: 'Montserrat', sans-serif;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .badge-men {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .badge-women {
            background: #fce4ec;
            color: #c2185b;
        }
        
        .badge-accessories {
            background: #fff3e0;
            color: #f57c00;
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
            max-width: 600px;
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
    </style>
    <script>
        function confirmDelete(productName) {
            return confirm('Are you sure you want to delete "' + productName + '"? This action cannot be undone.');
        }
        
        function openAddModal() {
            document.getElementById('productModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('productModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('productModal');
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
                <li><a href="admin_products.php" class="active"><i class="fas fa-box"></i> Manage Products</a></li>
                <li><a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> Manage Orders</a></li>
                <li><a href="admin_reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="admin_settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="top-bar">
                <h1>Manage Products</h1>
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
                    <h2><i class="fas fa-box"></i> All Products</h2>
                    <button onclick="openAddModal()" class="btn btn-success">
                        <i class="fas fa-plus"></i> Add New Product
                    </button>
                </div>

                <!-- Filter Box -->
                <form method="GET" action="admin_products.php" class="filter-box">
                    <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                    <select name="category">
                        <option value="">All Categories</option>
                        <option value="men" <?php echo $category_filter === 'men' ? 'selected' : ''; ?>>Men</option>
                        <option value="women" <?php echo $category_filter === 'women' ? 'selected' : ''; ?>>Women</option>
                        <option value="accessories" <?php echo $category_filter === 'accessories' ? 'selected' : ''; ?>>Accessories</option>
                    </select>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <?php if ($search || $category_filter): ?>
                        <a href="admin_products.php" class="btn btn-warning">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    <?php endif; ?>
                </form>

                <!-- Products Table -->
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($products_result) > 0): ?>
                            <?php while($product = mysqli_fetch_assoc($products_result)): ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td><img src="<?php echo htmlspecialchars($product['image_path']); ?>" class="product-image" alt=""></td>
                                <td><strong><?php echo htmlspecialchars($product['product_name']); ?></strong></td>
                                <td>
                                    <span class="badge badge-<?php echo $product['category']; ?>">
                                        <?php echo ucfirst($product['category']); ?>
                                    </span>
                                </td>
                                <td>Rs. <?php echo number_format($product['price'], 2); ?></td>
                                <td><?php echo $product['stock_quantity']; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="admin_products.php?edit=<?php echo $product['id']; ?>" class="btn btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="admin_products.php?delete=<?php echo $product['id']; ?>" 
                                           class="btn btn-danger" 
                                           onclick="return confirmDelete('<?php echo htmlspecialchars($product['product_name']); ?>')"
                                           title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 30px;">
                                    <i class="fas fa-box-open" style="font-size: 3rem; color: #bdc3c7;"></i>
                                    <p style="margin-top: 10px; color: #7f8c8d;">No products found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Add/Edit Product Modal -->
    <div id="productModal" class="modal" style="display: <?php echo $edit_product ? 'block' : 'none'; ?>;">
        <div class="modal-content">
            <div class="modal-header">
                <h2><?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?></h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" action="admin_products.php">
                <?php if ($edit_product): ?>
                    <input type="hidden" name="product_id" value="<?php echo $edit_product['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" name="product_name" required 
                           value="<?php echo $edit_product ? htmlspecialchars($edit_product['product_name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" required>
                        <option value="">Select Category</option>
                        <option value="men" <?php echo ($edit_product && $edit_product['category'] === 'men') ? 'selected' : ''; ?>>Men</option>
                        <option value="women" <?php echo ($edit_product && $edit_product['category'] === 'women') ? 'selected' : ''; ?>>Women</option>
                        <option value="accessories" <?php echo ($edit_product && $edit_product['category'] === 'accessories') ? 'selected' : ''; ?>>Accessories</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Price (Rs.) *</label>
                    <input type="number" step="0.01" name="price" required 
                           value="<?php echo $edit_product ? $edit_product['price'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"><?php echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Image Path *</label>
                    <input type="text" name="image_path" required 
                           value="<?php echo $edit_product ? htmlspecialchars($edit_product['image_path']) : ''; ?>"
                           placeholder="e.g., img/product.png">
                </div>
                
                <div class="form-group">
                    <label>Stock Quantity *</label>
                    <input type="number" name="stock_quantity" required 
                           value="<?php echo $edit_product ? $edit_product['stock_quantity'] : '0'; ?>">
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="<?php echo $edit_product ? 'update_product' : 'add_product'; ?>" class="btn btn-success">
                        <i class="fas fa-save"></i> <?php echo $edit_product ? 'Update Product' : 'Add Product'; ?>
                    </button>
                    <button type="button" onclick="closeModal()" class="btn btn-danger">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>