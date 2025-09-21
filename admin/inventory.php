<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['account_id'])) {
    header("Location: ../public/login.php");
    exit();
}

require_once '../database/starroofing_db.php'; 

// Function to determine status based on stock quantity
function getStockStatus($quantity) {
    if ($quantity == 0) {
        return 'out-of-stock';
    } elseif ($quantity < 50) {
        return 'low-stock';
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

// Fetch products from database
$products = [];
if ($category_filter === 'all') {
    $sql = "SELECT p.*, c.category_name, c.category_code 
            FROM products p 
            JOIN categories c ON p.category_id = c.category_id 
            WHERE p.is_archived = 0 
            ORDER BY p.created_at DESC";
    $result = $conn->query($sql);
} else {
    $stmt = $conn->prepare("SELECT p.*, c.category_name, c.category_code 
            FROM products p 
            JOIN categories c ON p.category_id = c.category_id 
            WHERE c.category_code = ? 
            ORDER BY p.created_at DESC");
    $stmt->bind_param("s", $category_filter);
    $stmt->execute();
    $result = $stmt->get_result();
}

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - Star Roofing & Construction</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- <link rel="stylesheet" href="../css/inventory.css"> -->
     <style>
                /* Your existing CSS styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f7f9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
            font-family: 'Montserrat', sans-serif;
            background-color: #f5f7f9;
        }
        
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .top-navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .breadcrumb {
            font-size: 14px;
            color: #7f8c8d;
        }
        
        .breadcrumb a {
            color: #3498db;
            text-decoration: none;
        }
        
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
        
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
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
        
        .inventory-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .product-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .product-image {
            height: 200px;
            overflow: hidden;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.05);
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
        
        .product-info {
            padding: 20px;
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

        .product-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 15px;
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
        
        .product-actions {
            display: flex;
            gap: 10px;
        }
        
        .product-actions .btn {
            flex: 1;
            padding: 8px 12px;
            font-size: 14px;
        }
        
        .no-products {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
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
            
            .inventory-grid {
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
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation -->
            <?php include '../includes/admin_navbar.php'; ?>
            
            <!-- Inventory Content -->
            <div class="inventory-content">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Inventory Management</h1>
                        <p class="page-description">Manage your products and services</p>
                    </div>
                    <button class="btn btn-primary" id="addProductBtn">
                        <i class="fas fa-plus"></i> Add New Product
                    </button>
                </div>
                
                <!-- Category Filter -->
                <div class="category-filter">
                    <button class="category-btn <?= $category_filter === 'all' ? 'active' : '' ?>" data-category="all">All Products</button>
                    <?php foreach ($categories as $category): ?>
                        <button class="category-btn <?= $category_filter === $category['category_code'] ? 'active' : '' ?>" data-category="<?= $category['category_code'] ?>">
                            <?= $category['category_name'] ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                
                <!-- Inventory Grid -->
                <div class="inventory-grid" id="productGrid">
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $product): 
                            $status = getStockStatus($product['stock_quantity']);
                            $status_text = $status === 'in-stock' ? 'In Stock' : 
                                          ($status === 'low-stock' ? 'Low Stock' : 'Out of Stock');
                        ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <?php if (!empty($product['image_path'])): ?>
                                        <img src="../<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                    <?php else: ?>
                                        <div class="placeholder"><i class="fas fa-box"></i></div>
                                    <?php endif; ?>
                                </div>
                            <div class="product-info">
                                <div class="product-category"><?= htmlspecialchars($product['category_name']) ?></div>
                                <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                                
                                <div class="product-details">
                                    <div class="product-detail">
                                        <span class="detail-label">Price</span>
                                        <span class="detail-value">₱<?= number_format($product['price'], 2) ?></span>
                                    </div>
                                    <div class="product-detail">
                                        <span class="detail-label">Stock</span>
                                        <span class="detail-value"><?= $product['stock_quantity'] ?> <?= htmlspecialchars($product['unit']) ?></span>
                                    </div>
                                    <div class="product-detail">
                                        <span class="detail-label">Status</span>
                                        <span class="detail-value">
                                            <span class="status <?= $status ?>">
                                                <?= $status_text ?>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="product-actions">
                                    <button class="btn btn-outline edit-btn" data-id="<?= $product['product_id'] ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-danger archive-btn" data-id="<?= $product['product_id'] ?>" data-name="<?= htmlspecialchars($product['name']) ?>">
                                        <i class="fas fa-archive"></i> Archive
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products">
                        <p>No products found in this category.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Product Modal -->
<div class="modal" id="productModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">Add New Product</h2>
            <button class="modal-close" id="closeModal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="productForm" method="POST" action="../crud/add_product.php" enctype="multipart/form-data">
                <input type="hidden" id="productId" name="product_id">
                <input type="hidden" name="add_product" value="1">
                
                <div class="form-group">
                    <label for="productCategory">Category</label>
                    <select id="productCategory" name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['category_id'] ?>"><?= $category['category_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="productName">Product Name</label>
                    <input type="text" id="productName" name="name" placeholder="Enter product name" required>
                </div>
                
                <div class="form-group">
                    <label for="productDescription">Description</label>
                    <textarea id="productDescription" name="description" placeholder="Enter product description" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="productPrice">Price (₱)</label>
                        <input type="number" id="productPrice" name="price" placeholder="0.00" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="productStock">Stock Quantity</label>
                        <input type="number" id="productStock" name="stock_quantity" placeholder="0" min="0" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="productUnit">Unit</label>
                    <input type="text" id="productUnit" name="unit" placeholder="e.g., sqm, piece, set" required>
                </div>
                
                <div class="form-group">
                    <label for="productImage">Upload Product Image</label>
                    <input type="file" id="productImage" name="image_file" accept="image/*">
                    <small id="fileError" style="color: red; display: none;"></small>
                
                <!-- Preview Box -->
                <div style="margin-top:10px;">
                    <img id="previewImage" src="#" alt="Image Preview" style="display:none; max-width:150px; border:1px solid #ccc; padding:5px;">
                </div>
                </div>
                <!-- Status will be calculated automatically based on stock quantity -->
                <div class="form-group">
                    <label for="productStatus">Status</label>
                    <input type="text" id="productStatus" readonly style="background-color: #f8f9fa;">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" id="cancelBtn">Cancel</button>
            <button type="submit" form="productForm" class="btn btn-primary" id="saveProductBtn">Save Product</button>
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

<script>
    // JavaScript code for handling UI interactions
    document.addEventListener('DOMContentLoaded', function() {
        // Category filter buttons
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const category = btn.dataset.category;
                window.location.href = `inventory.php?category=${category}`;
            });
        });
        
        // Add product button
        document.getElementById('addProductBtn').addEventListener('click', () => {
            document.getElementById('modalTitle').textContent = 'Add New Product';
            document.getElementById('productForm').reset();
            document.getElementById('productId').value = '';
            document.getElementById('productStatus').value = 'In Stock (50+ quantity)';
            document.getElementById('productModal').classList.add('active');
        });
        
        // Show status based on stock quantity in real-time
        document.getElementById('productStock').addEventListener('input', function() {
            const stock = parseInt(this.value) || 0;
            let status = '';
            
            if (stock === 0) {
                status = 'Out of Stock (0 quantity)';
            } else if (stock < 50) {
                status = 'Low Stock (<50 quantity)';
            } else {
                status = 'In Stock (50+ quantity)';
            }
            
            document.getElementById('productStatus').value = status;
        });
        
        // Modal close buttons
        document.getElementById('closeModal').addEventListener('click', closeModal);
        document.getElementById('cancelBtn').addEventListener('click', closeModal);
        
        // Edit button functionality
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('edit-btn') || e.target.closest('.edit-btn')) {
                const btn = e.target.classList.contains('edit-btn') ? e.target : e.target.closest('.edit-btn');
                const productId = btn.dataset.id;

                // Fetch product details via AJAX
                fetch(`../crud/get_product.php?id=${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update modal title
                            document.getElementById('modalTitle').textContent = 'Edit Product';

                            // Fill form with product data
                            document.getElementById('productId').value = data.product.product_id;
                            document.getElementById('productCategory').value = data.product.category_id;
                            document.getElementById('productName').value = data.product.name;
                            document.getElementById('productDescription').value = data.product.description;
                            document.getElementById('productPrice').value = data.product.price;
                            document.getElementById('productStock').value = data.product.stock_quantity;
                            document.getElementById('productUnit').value = data.product.unit;

                            // Show status based on stock
                            const stock = parseInt(data.product.stock_quantity);
                            let status = stock === 0 ? 'Out of Stock (0 quantity)' : 
                                        stock < 50 ? 'Low Stock (<50 quantity)' : 
                                                    'In Stock (50+ quantity)';
                            document.getElementById('productStatus').value = status;

                            // If product already has an image, show it
                            if (data.product.image_file) {
                                const preview = document.getElementById('previewImage');
                                preview.src = data.product.image_file;
                                preview.style.display = "block";
                            }

                            // Change form action for editing
                            const form = document.getElementById('productForm');
                            form.action = "../crud/edit_product.php";
                            form.querySelector("input[name='add_product']").remove();
                            if (!form.querySelector("input[name='edit_product']")) {
                                let editFlag = document.createElement("input");
                                editFlag.type = "hidden";
                                editFlag.name = "edit_product";
                                editFlag.value = "1";
                                form.appendChild(editFlag);
                            }

                            // Show modal
                            document.getElementById('productModal').classList.add('active');
                        } else {
                            alert('Failed to fetch product data.');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }

            // Archive button
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('archive-btn') || e.target.closest('.archive-btn')) {
                    const btn = e.target.classList.contains('archive-btn') ? e.target : e.target.closest('.archive-btn');
                    document.getElementById('archiveProductName').textContent = btn.dataset.name;
                    document.getElementById('archiveProductId').value = btn.dataset.id;
                    document.getElementById('archiveModal').classList.add('active');
                }
            });

            // Close archive modal
            document.getElementById('closeArchiveModal').addEventListener('click', () => {
                document.getElementById('archiveModal').classList.remove('active');
            });
            document.getElementById('cancelArchiveBtn').addEventListener('click', () => {
                document.getElementById('archiveModal').classList.remove('active');
            });
        });

        function closeModal() {
            document.getElementById('productModal').classList.remove('active');
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }
    });

    document.getElementById("productImage").addEventListener("change", function(event) {
    const file = event.target.files[0];
    const errorMsg = document.getElementById("fileError");
    const preview = document.getElementById("previewImage");

    errorMsg.style.display = "none";
    preview.style.display = "none";

    if (file) {
        // Allowed types
        const allowedTypes = ["image/jpeg", "image/png", "image/gif", "image/webp"];
        if (!allowedTypes.includes(file.type)) {
            errorMsg.textContent = "❌ Invalid file type. Only JPG, PNG, GIF, and WebP allowed.";
            errorMsg.style.display = "block";
            event.target.value = ""; // reset input
            return;
        }

        // Max size 5MB
        if (file.size > 5 * 1024 * 1024) {
            errorMsg.textContent = "❌ File too large. Max size is 5MB.";
            errorMsg.style.display = "block";
            event.target.value = ""; // reset input
            return;
        }

        // Preview image
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = "block";
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php if (isset($_GET['success'])): ?>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '<?= htmlspecialchars($_GET['success']) ?>',
        confirmButtonColor: '#3498db'
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
        confirmButtonColor: '#e74c3c'
      }).then(() => {
        window.history.replaceState({}, document.title, "inventory.php");
      });
    });
  </script>
<?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
    const form = document.querySelector("form");

    form.addEventListener("submit", function(e) {
        e.preventDefault();

        Swal.fire({
        title: 'Are you sure?',
        text: "Do want to update this product?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3498db',
        cancelButtonColor: '#e74c3c',
        confirmButtonText: 'Yes, update it!',
        cancelButtonText: 'Cancel'
        }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
        });
    });
    });
    </script>

<?php if (isset($_GET['error'])): ?>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '<?= htmlspecialchars($_GET['error']) ?>',
        confirmButtonColor: '#e74c3c'
      }).then(() => {
        window.history.replaceState({}, document.title, "inventory.php");
      });
    });
  </script>
<?php endif; ?>


</body>
</html>