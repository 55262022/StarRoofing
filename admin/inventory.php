<?php
include '../authentication/auth.php';
require_once '../database/starroofing_db.php';

// Require admin access
requireAdmin();

// stock status logic
function getStockStatus($quantity) {
    if ($quantity <= 0) {
        return 'out-of-stock';
    } else {
        return 'in-stock';
    }
}

// Fetch categories from database
$categories = [];
$result = $conn->query("SELECT * FROM categories ORDER BY category_name");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Determine category filter
$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';

// Determine search term
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Determine view mode (table or 3d)
$view_mode = isset($_GET['view']) ? $_GET['view'] : 'table';

// Build base SQL with category and search
$sql = "SELECT p.*, c.category_name, c.category_code 
        FROM products p 
        JOIN categories c ON p.category_id = c.category_id 
        WHERE p.is_archived = 0";

$params = [];
$types  = "";

// Category filter
if ($category_filter !== 'all') {
    $sql .= " AND c.category_code = ?";
    $params[] = $category_filter;
    $types .= "s";
}

// Search filter
if (!empty($search_term)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $like = "%" . $search_term . "%";
    $params[] = $like;
    $params[] = $like;
    $types .= "ss";
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// --- Pagination setup ---
$limit = 6; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$where = [];
$where[] = "p.is_archived = 0";
if (!empty($search)) {
    $s = $conn->real_escape_string($search);
    $where[] = "(p.name LIKE '%$s%' OR p.description LIKE '%$s%')";
}
if (!empty($category) && $category !== 'all') {
    $c = $conn->real_escape_string($category);
    $where[] = "c.category_code = '$c'";
}
$whereSQL = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// Count total rows
$count_sql = "SELECT COUNT(*) as total 
              FROM products p 
              JOIN categories c ON p.category_id = c.category_id 
              $whereSQL";
$count_result = $conn->query($count_sql);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch paginated products
$sql = "SELECT p.*, c.category_name, c.category_code 
        FROM products p 
        JOIN categories c ON p.category_id = c.category_id 
        $whereSQL 
        ORDER BY p.created_at DESC 
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - Star Roofing & Construction</title>
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Model Viewer -->
    <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.3.0/model-viewer.min.js"></script>
    <!-- CSS style -->
    <link rel="stylesheet" href="../css/admin_main.css">
     <style>
        /* Your existing CSS styles */        
        .user-profile {
            position: relative;
            cursor: pointer;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .user-name {
            font-weight: 500;
        }
        
        .user-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            width: 200px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            z-index: 100;
            margin-top: 10px;
        }
        
        .user-dropdown.active {
            display: block;
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #2c3e50;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .dropdown-item:hover {
            background-color: #f8f9fa;
        }
        
        .dropdown-item i {
            margin-right: 10px;
            width: 16px;
            text-align: center;
        }
        
        .dropdown-divider {
            height: 1px;
            background-color: #eee;
            margin: 5px 0;
        }
        
        .inventory-content {
            flex: 1;
            padding: 30px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 5px 0;
        }
        
        .page-description {
            color: #7f8c8d;
            margin: 0;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            gap: 8px;
        }

        .btn-info {
            background-color: #17a2b8;
            color: white;
        }

        .btn-info:hover {
            background-color: #138496;
        }

        .btn-success {
            background-color: #27ae60;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #229954;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-warning {
            background-color: #dce73cff;
            color: black;
        }
        
        .btn-warning:hover {
            background-color: #b9c331ff;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid #bdc3c7;
            color: #7f8c8d;
        }
        
        .btn-outline:hover {
            background-color: #f8f9fa;
        }
        
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }

        /* View Toggle Buttons */
        .view-toggle {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            padding: 5px;
            background-color: #f8f9fa;
            border-radius: 8px;
            width: fit-content;
        }

        .view-btn {
            padding: 10px 20px;
            background-color: transparent;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            color: #7f8c8d;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .view-btn.active {
            background-color: #3498db;
            color: white;
        }

        .view-btn:hover:not(.active) {
            background-color: #e9ecef;
        }

        /* Search Form */
        .search-form {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        /* Search Input */
        .search-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .search-input:focus {
            border-color: #007bff;
        }

        .search-btn {
            padding: 10px 18px;
            background-color: #3498db;
            border: none;
            border-radius: 6px;
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background-color 0.2s ease;
        }

        .search-btn:hover {
            background-color: #2980b9;
        }
        
        .category-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 30px;
        }
        
        .category-btn {
            padding: 8px 16px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .category-btn.active,
        .category-btn:hover {
            background-color: #3498db;
            color: white;
            border-color: #3498db;
        }
        
        /* Table styles */
        .inventory-table table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .inventory-table th, .inventory-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .inventory-table th {
            background-color: #3498db;
            color: white;
            font-weight: 600;
        }

        .inventory-table tr:hover {
            background-color: #f9f9f9;
        }

        .inventory-table img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }

        /* 3D Model Card View */
        .model-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .model-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .model-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .model-card-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background-color: #f8f9fa;
        }

        .model-card-3d-container {
            width: 100%;
            height: 250px;
            background-color: #2c3e50;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .model-card-3d-container model-viewer {
            width: 100%;
            height: 100%;
        }

        .model-card-no-3d {
            width: 100%;
            height: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .model-card-no-3d i {
            font-size: 48px;
            margin-bottom: 10px;
            opacity: 0.8;
        }

        .model-card-no-3d span {
            font-size: 14px;
            opacity: 0.9;
        }

        .model-card-content {
            padding: 20px;
        }

        .model-card-category {
            font-size: 12px;
            color: #7f8c8d;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .model-card-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 10px 0;
        }

        .model-card-description {
            color: #7f8c8d;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 15px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .model-card-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .model-card-price {
            font-size: 20px;
            font-weight: 600;
            color: #3498db;
        }

        .model-card-stock {
            font-size: 14px;
            color: #7f8c8d;
        }

        .model-card-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .model-card-actions .btn {
            flex: 1;
            min-width: 100px;
            font-size: 13px;
            padding: 8px 12px;
        }

        .no-model-badge {
            display: inline-block;
            padding: 5px 10px;
            background-color: #fdedec;
            color: #e74c3c;
            border-radius: 6px;
            font-size: 12px;
            margin-bottom: 10px;
        }

        .has-model-badge {
            display: inline-block;
            padding: 5px 10px;
            background-color: #e8f6f3;
            color: #1abc9c;
            border-radius: 6px;
            font-size: 12px;
            margin-bottom: 10px;
        }

        /* Pagination */
        .pagination {
            margin-top: 20px;
            text-align: center;
        }

        .page-btn {
            display: inline-block;
            margin: 0 5px;
            padding: 8px 14px;
            border-radius: 6px;
            border: 1px solid #3498db;
            color: #3498db;
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }

        .page-btn:hover {
            background-color: #3498db;
            color: white;
        }

        .page-btn.active {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }
        
        .placeholder {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            color: #bdc3c7;
            font-size: 40px;
        }        
        
        .product-category {
            font-size: 12px;
            color: #7f8c8d;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .product-name {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 10px 0;
        }
        
        .product-description {
            color: #7f8c8d;
            font-size: 14px;
            line-height: 1.5;
            margin: 0 0 15px 0;
            overflow: hidden;
            display: -webkit-box;
            display: box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            box-orient: vertical;                       
        }

        .product-detail {
            display: flex;
            flex-direction: column;
        }
        
        .detail-label {
            font-size: 12px;
            color: #7f8c8d;
        }
        
        .detail-value {
            font-weight: 500;
            color: #2c3e50;
        }
        
        .status {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status.in-stock {
            background-color: #e8f6f3;
            color: #1abc9c;
        }
        
        .status.low-stock {
            background-color: #fef9e7;
            color: #f1c40f;
        }
        
        .status.out-of-stock {
            background-color: #fdedec;
            color: #e74c3c;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background-color: white;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .modal-title {
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #7f8c8d;
            transition: color 0.3s;
        }
        
        .modal-close:hover {
            color: #34495e;
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #34495e;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            transition: border-color 0.3s;
            font-family: 'Montserrat', sans-serif;
        }
        
        input:focus, select:focus, textarea:focus {
            border-color: #3498db;
            outline: none;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .model-grid {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .category-filter {
                overflow-x: auto;
                padding-bottom: 10px;
            }
        }
     </style>
</head>
<body>
    <div class="main-container">
        <!-- Main Content -->
        <div class="main-content">           
            <!-- Inventory Content -->
            <div class="inventory-content">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Inventory Information</h1>
                        <p class="page-description">Manage your products</p>
                    </div>
                    <button class="btn btn-primary" id="addProductBtn">
                        <i class="fas fa-plus"></i> Add New Product
                    </button>
                </div>

                <!-- View Toggle Buttons -->
                <div class="view-toggle">
                    <button class="view-btn <?= $view_mode === 'table' ? 'active' : '' ?>" data-view="table">
                        <i class="fas fa-table"></i> View Table
                    </button>
                    <button class="view-btn <?= $view_mode === '3d' ? 'active' : '' ?>" data-view="3d">
                        <i class="fas fa-cube"></i> View 3D Models
                    </button>
                </div>

                <!-- Search Bar -->
                <form method="GET" action="inventory.php" class="search-form">
                    <!-- Keep category filter and view mode in query -->
                    <input type="hidden" name="category" value="<?= htmlspecialchars($category_filter) ?>">
                    <input type="hidden" name="view" value="<?= htmlspecialchars($view_mode) ?>">

                    <input type="text" name="search" placeholder="Search products..." 
                        value="<?= htmlspecialchars($search_term) ?>" class="search-input">
                    
                    <!-- Search Button -->
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Search
                    </button>

                    <!-- Reset Button -->
                    <button type="button" class="search-btn" onclick="window.location='inventory.php?category=<?= htmlspecialchars($category_filter) ?>&view=<?= htmlspecialchars($view_mode) ?>'">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </form>

                <!-- Category Filter -->
                <div class="category-filter">
                    <button class="category-btn <?= $category_filter === 'all' ? 'active' : '' ?>" data-category="all">All Products</button>
                    <?php foreach ($categories as $category): ?>
                        <button class="category-btn <?= $category_filter === $category['category_code'] ? 'active' : '' ?>" data-category="<?= $category['category_code'] ?>">
                            <?= $category['category_name'] ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                
                <!-- TABLE VIEW -->
                <?php if ($view_mode === 'table'): ?>
                <div class="inventory-container">
                    <div class="inventory-table">
                        <?php if ($result && $result->num_rows > 0): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Item Name</th>
                                        <th>Category</th>
                                        <th>Quantity</th>
                                        <th>Unit</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($row['image_path'])): ?>
                                                    <img src="../<?= htmlspecialchars($row['image_path']) ?>" width="50" height="50">
                                                <?php else: ?>
                                                    No Image
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($row['name']) ?></td>
                                            <td><?= htmlspecialchars($row['category_name']) ?></td>
                                            <td><?= htmlspecialchars($row['stock_quantity']) ?></td>
                                            <td><?= htmlspecialchars($row['unit']) ?></td>
                                            <td><?= "₱" . number_format($row['price'], 2) ?></td>
                                            <td>
                                                <span class="status <?= getStockStatus($row['stock_quantity']) ?>">
                                                    <?= ucfirst(str_replace("-", " ", getStockStatus($row['stock_quantity']))) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-success restock-btn" 
                                                        data-id="<?= $row['product_id'] ?>" 
                                                        data-name="<?= htmlspecialchars($row['name']) ?>"
                                                        data-current-stock="<?= $row['stock_quantity'] ?>"
                                                        data-unit="<?= htmlspecialchars($row['unit']) ?>"
                                                        data-category="<?= htmlspecialchars($row['category_name']) ?>"
                                                        data-price="<?= $row['price'] ?>">
                                                    <i class="fas fa-box"></i> Restock
                                                </button>

                                                <?php if (!empty($row['model_path']) || !empty($row['model_url'])): ?>
                                                    <button class="btn btn-info" onclick="window.location.href='3dmodel.php?product_id=<?= $row['product_id'] ?>'">
                                                        <i class="fas fa-cube"></i> View 3D
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-info" onclick="window.location.href='3dmodel.php?product_id=<?= $row['product_id'] ?>'">
                                                        <i class="fas fa-plus"></i> Add 3D
                                                    </button>
                                                <?php endif; ?>

                                                <button class="btn btn-warning edit-btn" data-id="<?= $row['product_id'] ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>

                                                <button class="btn btn-danger archive-btn" 
                                                        data-id="<?= $row['product_id'] ?>" 
                                                        data-name="<?= htmlspecialchars($row['name']) ?>">
                                                    <i class="fas fa-archive"></i> Archive
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No products found.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 3D MODEL CARD VIEW -->
                <?php if ($view_mode === '3d'): ?>
                <div class="model-grid">
                    <?php 
                    $result->data_seek(0); // Reset result pointer
                    if ($result && $result->num_rows > 0): 
                        while ($row = $result->fetch_assoc()): 
                    ?>
                        <div class="model-card">
                            <?php if (!empty($row['model_path']) || !empty($row['model_url'])): ?>
                                <!-- Display 3D Model -->
                                <div class="model-card-3d-container">
                                    <model-viewer 
                                        src="../<?= !empty($row['model_path']) ? htmlspecialchars($row['model_path']) : htmlspecialchars($row['model_url']) ?>"
                                        alt="<?= htmlspecialchars($row['name']) ?> 3D Model"
                                        camera-controls
                                        auto-rotate
                                        shadow-intensity="1"
                                        style="width: 100%; height: 100%;">
                                    </model-viewer>
                                </div>
                            <?php else: ?>
                                <!-- No 3D Model Available -->
                                <div class="model-card-no-3d">
                                    <i class="fas fa-cube"></i>
                                    <span>No 3D Model Available</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="model-card-content">
                                <div class="model-card-category"><?= htmlspecialchars($row['category_name']) ?></div>
                                <h3 class="model-card-title"><?= htmlspecialchars($row['name']) ?></h3>
                                <p class="model-card-description"><?= htmlspecialchars($row['description'] ?? 'No description available') ?></p>
                                
                                <?php if (!empty($row['model_path']) || !empty($row['model_url'])): ?>
                                    <span class="has-model-badge"><i class="fas fa-check-circle"></i> Has 3D Model</span>
                                <?php else: ?>
                                    <span class="no-model-badge"><i class="fas fa-times-circle"></i> No 3D Model</span>
                                <?php endif; ?>

                                <div class="model-card-details">
                                    <div class="model-card-price">₱<?= number_format($row['price'], 2) ?></div>
                                    <div class="model-card-stock">
                                        <span class="status <?= getStockStatus($row['stock_quantity']) ?>">
                                            <?= $row['stock_quantity'] ?> <?= htmlspecialchars($row['unit']) ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="model-card-actions">
                                    <button class="btn btn-success restock-btn" 
                                            data-id="<?= $row['product_id'] ?>" 
                                            data-name="<?= htmlspecialchars($row['name']) ?>"
                                            data-current-stock="<?= $row['stock_quantity'] ?>"
                                            data-unit="<?= htmlspecialchars($row['unit']) ?>"
                                            data-category="<?= htmlspecialchars($row['category_name']) ?>"
                                            data-price="<?= $row['price'] ?>">
                                        <i class="fas fa-box"></i> Restock
                                    </button>
                                    
                                    <?php if (!empty($row['model_path']) || !empty($row['model_url'])): ?>
                                        <button class="btn btn-info" onclick="window.location.href='3dmodel.php?product_id=<?= $row['product_id'] ?>'">
                                            <i class="fas fa-cube"></i> View 3D
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-info" onclick="window.location.href='3dmodel.php?product_id=<?= $row['product_id'] ?>'">
                                            <i class="fas fa-plus"></i> Add 3D
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-warning edit-btn" data-id="<?= $row['product_id'] ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-danger archive-btn" 
                                            data-id="<?= $row['product_id'] ?>" 
                                            data-name="<?= htmlspecialchars($row['name']) ?>">
                                        <i class="fas fa-archive"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endwhile;
                    else: 
                    ?>
                        <p>No products found.</p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Pagination -->
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page-1 ?>&category=<?= urlencode($category_filter) ?>&search=<?= urlencode($search_term) ?>&view=<?= urlencode($view_mode) ?>" class="page-btn">Prev</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>&category=<?= urlencode($category_filter) ?>&search=<?= urlencode($search_term) ?>&view=<?= urlencode($view_mode) ?>" 
                        class="page-btn <?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page+1 ?>&category=<?= urlencode($category_filter) ?>&search=<?= urlencode($search_term) ?>&view=<?= urlencode($view_mode) ?>" class="page-btn">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal" id="addProductModal">
  <div class="modal-content">
    <div class="modal-header">
      <h2 class="modal-title">Add New Product</h2>
      <button class="modal-close" id="closeAddModal">&times;</button>
    </div>
    <div class="modal-body">
      <form id="addProductForm" method="POST" action="../crud/add_product.php" enctype="multipart/form-data">
        <input type="hidden" name="add_product" value="1">
        
        <!-- Category -->
        <div class="form-group">
          <label for="addProductCategory">Category</label>
          <select id="addProductCategory" name="category_id" required>
            <option value="" disabled selected>Select Category</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?= $category['category_id'] ?>"><?= $category['category_name'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Name -->
        <div class="form-group">
          <label for="addProductName">Product Name</label>
          <input type="text" id="addProductName" name="name" required>
        </div>

        <!-- Description -->
        <div class="form-group">
          <label for="addProductDescription">Description</label>
          <textarea id="addProductDescription" name="description"></textarea>
        </div>

        <!-- Price & Stock -->
        <div class="form-row">
          <div class="form-group">
            <label for="addProductPrice">Price (₱)</label>
            <input type="number" id="addProductPrice" name="price" step="0.01" min="0" required>
          </div>
          <div class="form-group">
            <label for="addProductStock">Stock Quantity</label>
            <input type="number" id="addProductStock" name="stock_quantity" min="0" required>
          </div>
        </div>

        <!-- Unit -->
        <div class="form-group">
        <label for="addProductUnit">Unit</label>
        <select id="addProductUnit" name="unit" required>
            <option value="" disabled selected>Select Unit</option>

            <optgroup label="Count Units">
            <option value="piece">Piece (pc)</option>
            <option value="set">Set</option>
            <option value="box">Box</option>
            <option value="bundle">Bundle</option>
            <option value="pack">Pack</option>
            <option value="dozen">Dozen</option>
            </optgroup>

            <optgroup label="Weight Units">
            <option value="kg">Kilogram (kg)</option>
            <option value="g">Gram (g)</option>
            <option value="ton">Ton (t)</option>
            <option value="lb">Pound (lb)</option>
            </optgroup>

            <optgroup label="Length Units">
            <option value="m">Meter (m)</option>
            <option value="cm">Centimeter (cm)</option>
            <option value="mm">Millimeter (mm)</option>
            <option value="ft">Foot (ft)</option>
            </optgroup>

            <optgroup label="Area Units">
            <option value="sqm">Square Meter (sqm)</option>
            <option value="sqft">Square Foot (sqft)</option>
            </optgroup>

            <optgroup label="Volume Units">
            <option value="liter">Liter (L)</option>
            <option value="ml">Milliliter (ml)</option>
            <option value="cubic_meter">Cubic Meter (m³)</option>
            <option value="cubic_ft">Cubic Foot (ft³)</option>
            </optgroup>

            <optgroup label="Other Units">
            <option value="roll">Roll</option>
            <option value="sheet">Sheet</option>
            <option value="bag">Bag</option>
            <option value="sack">Sack</option>
            <option value="pair">Pair</option>
            <option value="can">Can</option>
            <option value="drum">Drum</option>
            </optgroup>
        </select>
        </div>

        <!-- Image -->
        <div class="form-group">
          <label for="addProductImage">Upload Product Image</label>
          <input type="file" id="addProductImage" name="image_file" accept="image/*">
          <img id="addPreviewImage" style="display:none; max-width:150px; margin-top:10px;">
        </div>

        <!-- Status -->
        <div class="form-group">
          <label for="addProductStatus">Status</label>
          <input type="text" id="addProductStatus" readonly value="In Stock">
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" id="cancelAddBtn">Cancel</button>
      <button type="submit" form="addProductForm" class="btn btn-primary">Add Product</button>
    </div>
  </div>
</div>

<!-- Edit Product Modal -->
<div class="modal" id="editProductModal">
  <div class="modal-content">
    <div class="modal-header">
      <h2 class="modal-title">Edit Product</h2>
      <button class="modal-close" id="closeEditModal">&times;</button>
    </div>
    <div class="modal-body">
      <form id="editProductForm" method="POST" action="../crud/edit_product.php" enctype="multipart/form-data">
        <input type="hidden" name="edit_product" value="1">
        <input type="hidden" id="editProductId" name="product_id">
        
        <!-- Category -->
        <div class="form-group">
          <label for="editProductCategory">Category</label>
          <select id="editProductCategory" name="category_id" required>
            <option value="">Select Category</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?= $category['category_id'] ?>"><?= $category['category_name'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Name -->
        <div class="form-group">
          <label for="editProductName">Product Name</label>
          <input type="text" id="editProductName" name="name" required>
        </div>

        <!-- Description -->
        <div class="form-group">
          <label for="editProductDescription">Description</label>
          <textarea id="editProductDescription" name="description"></textarea>
        </div>

        <!-- Price & Stock -->
        <div class="form-row">
          <div class="form-group">
            <label for="editProductPrice">Price (₱)</label>
            <input type="number" id="editProductPrice" name="price" step="0.01" min="0" required>
          </div>
          <div class="form-group">
            <label for="editProductStock">Stock Quantity</label>
            <input type="number" id="editProductStock" name="stock_quantity" min="0" required>
          </div>
        </div>

        <!-- Unit -->
        <div class="form-group">
          <label for="editProductUnit">Unit</label>
          <select id="editProductUnit" name="unit" required>
            <option value="">Select Unit</option>

            <optgroup label="Count Units">
            <option value="piece">Piece (pc)</option>
            <option value="set">Set</option>
            <option value="box">Box</option>
            <option value="bundle">Bundle</option>
            <option value="pack">Pack</option>
            <option value="dozen">Dozen</option>
            </optgroup>

            <optgroup label="Weight Units">
            <option value="kg">Kilogram (kg)</option>
            <option value="g">Gram (g)</option>
            <option value="ton">Ton (t)</option>
            <option value="lb">Pound (lb)</option>
            </optgroup>

            <optgroup label="Length Units">
            <option value="m">Meter (m)</option>
            <option value="cm">Centimeter (cm)</option>
            <option value="mm">Millimeter (mm)</option>
            <option value="ft">Foot (ft)</option>
            </optgroup>

            <optgroup label="Area Units">
            <option value="sqm">Square Meter (sqm)</option>
            <option value="sqft">Square Foot (sqft)</option>
            </optgroup>

            <optgroup label="Volume Units">
            <option value="liter">Liter (L)</option>
            <option value="ml">Milliliter (ml)</option>
            <option value="cubic_meter">Cubic Meter (m³)</option>
            <option value="cubic_ft">Cubic Foot (ft³)</option>
            </optgroup>

            <optgroup label="Other Units">
            <option value="roll">Roll</option>
            <option value="sheet">Sheet</option>
            <option value="bag">Bag</option>
            <option value="sack">Sack</option>
            <option value="pair">Pair</option>
            <option value="can">Can</option>
            <option value="drum">Drum</option>
            </optgroup>
          </select>
        </div>

        <!-- Image -->
        <div class="form-group">
            <label for="editProductImage">Upload Product Image</label>
            <input type="file" id="editProductImage" name="image_file" accept="image/*">
            <!-- Existing Image Preview -->
            <img id="editPreviewImage" style="display:none; max-width:150px; margin-top:10px; border:1px solid #ddd; border-radius:6px;">
        </div>

        <!-- Status -->
        <div class="form-group">
          <label for="editProductStatus">Status</label>
          <input type="text" id="editProductStatus" readonly>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" id="cancelEditBtn">Cancel</button>
      <button type="submit" form="editProductForm" class="btn btn-primary">Update Product</button>
    </div>
  </div>
</div>

<!-- Archive Confirmation Modal -->
<div class="modal" id="archiveModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Confirm Archive</h2>
            <button class="modal-close" id="closeArchiveModal">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to archive <strong id="archiveProductName"></strong>? You can restore it later.</p>
            <form id="archiveForm" method="POST" action="../crud/archive_product.php">
                <input type="hidden" name="product_id" id="archiveProductId">
                <input type="hidden" name="archive_product" value="1">
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" id="cancelArchiveBtn">Cancel</button>
            <button type="submit" form="archiveForm" class="btn btn-danger" id="confirmArchiveBtn">Archive Product</button>
        </div>
    </div>
</div>

<!-- Restock Modal -->
<div class="modal" id="restockModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title"><i class="fas fa-box"></i> Restock Product</h2>
            <button class="modal-close" id="closeRestockModal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="restockForm" method="POST" action="../crud/restock_product.php">
                <input type="hidden" name="restock_product" value="1">
                <input type="hidden" id="restockProductId" name="product_id">
                
                <!-- Product Info (Read-only) -->
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" id="restockProductName" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Category</label>
                        <input type="text" id="restockCategory" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                    </div>
                    <div class="form-group">
                        <label>Price</label>
                        <input type="text" id="restockPrice" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Current Stock</label>
                        <input type="text" id="restockCurrentStock" readonly style="background-color: #f8f9fa; cursor: not-allowed; font-weight: 600; color: #e74c3c;">
                    </div>
                    <div class="form-group">
                        <label>Unit</label>
                        <input type="text" id="restockUnit" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                    </div>
                </div>

                <!-- Restock Quantity Input -->
                <div class="form-group">
                    <label for="restockQuantity">Add Quantity <span style="color: red;">*</span></label>
                    <input type="number" id="restockQuantity" name="restock_quantity" min="1" required 
                           placeholder="Enter quantity to add" style="border: 2px solid #27ae60; font-size: 16px;">
                    <small style="color: #7f8c8d; display: block; margin-top: 5px;">
                        <i class="fas fa-info-circle"></i> Enter the number of units you want to add to the current stock
                    </small>
                </div>

                <!-- New Stock Preview -->
                <div class="form-group" style="background-color: #e8f6f3; padding: 20px; border-radius: 8px; border-left: 4px solid #27ae60; margin-top: 20px;">
                    <label style="color: #27ae60; margin-bottom: 10px; font-size: 14px;">
                        <i class="fas fa-calculator"></i> New Stock After Restock
                    </label>
                    <div style="font-size: 32px; font-weight: 700; color: #27ae60; display: flex; align-items: center; gap: 10px;">
                        <span id="newStockPreview">0</span> 
                        <span id="newStockUnit" style="font-size: 20px; font-weight: 500;"></span>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" id="cancelRestockBtn">Cancel</button>
            <button type="submit" form="restockForm" class="btn btn-success">
                <i class="fas fa-check-circle"></i> Confirm Restock
            </button>
        </div>
    </div>
</div>

<script>
    // JavaScript code for handling UI interactions
    document.addEventListener('DOMContentLoaded', function() {
        // View toggle buttons
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const view = btn.dataset.view;
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('view', view);
                window.location.href = currentUrl.toString();
            });
        });

        // Category filter buttons
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const category = btn.dataset.category;
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('category', category);
                window.location.href = currentUrl.toString();
            });
        });

        /* -- ADD PRODUCT MODAL -- */
        const addProductBtn   = document.getElementById("addProductBtn");
        const addProductModal = document.getElementById("addProductModal");
        const closeAddModal   = document.getElementById("closeAddModal");
        const cancelAddBtn    = document.getElementById("cancelAddBtn");

        if (addProductBtn) {
            addProductBtn.addEventListener("click", () => {
                document.getElementById("addProductForm").reset();
                document.getElementById("addProductStatus").value = "In Stock";
                addProductModal.classList.add("active");
            });
        }

        if (closeAddModal) {
            closeAddModal.addEventListener("click", () => {
                addProductModal.classList.remove("active");
            });
        }

        if (cancelAddBtn) {
            cancelAddBtn.addEventListener("click", (e) => {
                e.preventDefault();
                addProductModal.classList.remove("active");
            });
        }

        /* -------------------------------
           EDIT PRODUCT MODAL
        ------------------------------- */
        const editProductModal = document.getElementById("editProductModal");
        const closeEditModal   = document.getElementById("closeEditModal");
        const cancelEditBtn    = document.getElementById("cancelEditBtn");

        // Attach click event to all Edit buttons
        document.querySelectorAll(".edit-btn").forEach(button => {
            button.addEventListener("click", function () {
                const productId = this.getAttribute("data-id");

                // Fetch product data via AJAX
                fetch(`../crud/get_product.php?id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Fill in the edit form fields
                        document.getElementById("editProductId").value = data.product.product_id;
                        document.getElementById("editProductCategory").value = data.product.category_id;
                        document.getElementById("editProductName").value = data.product.name;
                        document.getElementById("editProductDescription").value = data.product.description;
                        document.getElementById("editProductPrice").value = data.product.price;
                        document.getElementById("editProductStock").value = data.product.stock_quantity;
                        document.getElementById("editProductUnit").value = data.product.unit;

                        // Show status
                        const stock = parseInt(data.product.stock_quantity);
                        let status = stock === 0 ? 'Out of Stock' :
                                    stock < 50 ? 'Low Stock' : 'In Stock';
                        document.getElementById("editProductStatus").value = status;

                        // Show existing product image
                        const previewImg = document.getElementById("editPreviewImage");
                        if (data.product.image_path && data.product.image_path !== "") {
                            previewImg.src = `../${data.product.image_path}`;
                            previewImg.style.display = "block";
                        } else {
                            previewImg.style.display = "none";
                        }

                        // Show modal
                        editProductModal.classList.add("active");
                    } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: "Failed to fetch product details."
                            });
                        }
                    })
                    .catch(error => console.error("Error:", error));
            });
        });

        if (closeEditModal) {
            closeEditModal.addEventListener("click", () => {
                editProductModal.classList.remove("active");
            });
        }

        if (cancelEditBtn) {
            cancelEditBtn.addEventListener("click", (e) => {
                e.preventDefault();
                editProductModal.classList.remove("active");
            });
        }

        /* -------------------------------
           ARCHIVE MODAL
        ------------------------------- */
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('archive-btn') || e.target.closest('.archive-btn')) {
                const btn = e.target.classList.contains('archive-btn') ? e.target : e.target.closest('.archive-btn');
                document.getElementById('archiveProductName').textContent = btn.dataset.name;
                document.getElementById('archiveProductId').value = btn.dataset.id;
                document.getElementById('archiveModal').classList.add('active');
            }
        });

        document.getElementById('closeArchiveModal').addEventListener('click', () => {
            document.getElementById('archiveModal').classList.remove('active');
        });
        document.getElementById('cancelArchiveBtn').addEventListener('click', () => {
            document.getElementById('archiveModal').classList.remove('active');
        });

        /* -------------------------------
           RESTOCK MODAL
        ------------------------------- */
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('restock-btn') || e.target.closest('.restock-btn')) {
                const btn = e.target.classList.contains('restock-btn') ? e.target : e.target.closest('.restock-btn');
                
                // Fill in restock modal data
                document.getElementById('restockProductId').value = btn.dataset.id;
                document.getElementById('restockProductName').value = btn.dataset.name;
                document.getElementById('restockCategory').value = btn.dataset.category;
                document.getElementById('restockPrice').value = '₱' + parseFloat(btn.dataset.price).toFixed(2);
                document.getElementById('restockCurrentStock').value = btn.dataset.currentStock + ' ' + btn.dataset.unit;
                document.getElementById('restockUnit').value = btn.dataset.unit;
                document.getElementById('newStockUnit').textContent = btn.dataset.unit;
                
                // Reset quantity input
                document.getElementById('restockQuantity').value = '';
                document.getElementById('newStockPreview').textContent = btn.dataset.currentStock;
                
                // Store current stock for calculation
                document.getElementById('restockModal').dataset.currentStock = btn.dataset.currentStock;
                
                // Show modal
                document.getElementById('restockModal').classList.add('active');
            }
        });

        // Calculate new stock preview when quantity changes
        document.getElementById('restockQuantity').addEventListener('input', function() {
            const currentStock = parseInt(document.getElementById('restockModal').dataset.currentStock) || 0;
            const addQuantity = parseInt(this.value) || 0;
            const newStock = currentStock + addQuantity;
            document.getElementById('newStockPreview').textContent = newStock;
        });

        // Close restock modal
        document.getElementById('closeRestockModal').addEventListener('click', () => {
            document.getElementById('restockModal').classList.remove('active');
        });
        
        document.getElementById('cancelRestockBtn').addEventListener('click', () => {
            document.getElementById('restockModal').classList.remove('active');
        });

        /* -------------------------------
           IMAGE PREVIEWS
        ------------------------------- */
        const addImageInput  = document.getElementById("addProductImage");
        const addPreview     = document.getElementById("addPreviewImage");
        if (addImageInput) {
            addImageInput.addEventListener("change", function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        addPreview.src = e.target.result;
                        addPreview.style.display = "block";
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        const editImageInput = document.getElementById("editProductImage");
        const editPreview    = document.getElementById("editPreviewImage");
        if (editImageInput) {
            editImageInput.addEventListener("change", function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        editPreview.src = e.target.result;
                        editPreview.style.display = "block";
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });

    // Image validation function
    function validateImage(input, previewId) {
        const file = input.files[0];
        const preview = document.getElementById(previewId);

        if (!file) {
            preview.style.display = "none";
            return true;
        }

        const allowedTypes = ["image/jpeg", "image/png", "image/gif"];
        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: "error",
                title: "Invalid File Type",
                text: "Please upload an image (JPG, PNG, GIF only)."
            });
            input.value = "";
            preview.style.display = "none";
            return false;
        }

        const maxSize = 2 * 1024 * 1024; 
        if (file.size > maxSize) {
            Swal.fire({
                icon: "error",
                title: "File Too Large",
                text: "Image size must be less than 2MB."
            });
            input.value = "";
            preview.style.display = "none";
            return false;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = "block";
        };
        reader.readAsDataURL(file);

        return true;
    }

    document.getElementById("addProductImage").addEventListener("change", function() {
        validateImage(this, "addPreviewImage");
    });

    document.getElementById("editProductImage").addEventListener("change", function() {
        validateImage(this, "editPreviewImage");
    });
</script>

<?php if (isset($_GET['success'])): ?>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '<?= htmlspecialchars($_GET['success']) ?>',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
      }).then(() => {
        window.history.replaceState({}, document.title, "inventory.php");
      });
    });
  </script>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '<?= htmlspecialchars($_GET['error']) ?>',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
      }).then(() => {
        window.history.replaceState({}, document.title, "inventory.php");
      });
    });
  </script>
<?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const editForm = document.getElementById("editProductForm");

        if (editForm) {
            editForm.addEventListener("submit", function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you really want to update this product?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3498db',
                    cancelButtonColor: '#e74c3c',
                    confirmButtonText: 'Update',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        editForm.submit();
                    }
                });
            });
        }

        // Restock form confirmation
        const restockForm = document.getElementById("restockForm");

        if (restockForm) {
            restockForm.addEventListener("submit", function(e) {
                e.preventDefault();

                const productName = document.getElementById("restockProductName").value;
                const currentStock = document.getElementById("restockModal").dataset.currentStock;
                const addQuantity = document.getElementById("restockQuantity").value;
                const newStock = parseInt(currentStock) + parseInt(addQuantity);

                Swal.fire({
                    title: 'Confirm Restock',
                    html: `
                        <div style="text-align: left; padding: 10px;">
                            <p><strong>Product:</strong> ${productName}</p>
                            <p><strong>Current Stock:</strong> ${currentStock}</p>
                            <p><strong>Adding:</strong> ${addQuantity}</p>
                            <hr>
                            <p style="color: #27ae60; font-size: 18px;"><strong>New Stock:</strong> ${newStock}</p>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#27ae60',
                    cancelButtonColor: '#e74c3c',
                    confirmButtonText: '<i class="fas fa-check"></i> Yes, Restock',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        restockForm.submit();
                    }
                });
            });
        }
    });
    </script>

</body>
</html>