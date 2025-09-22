<?php
include '../includes/auth.php';
require_once '../database/starroofing_db.php';

// Check for success message from restore operation
$success_message = '';
if (isset($_SESSION['restore_success'])) {
    $success_message = $_SESSION['restore_success'];
    unset($_SESSION['restore_success']);
}

// Check for error message
$error_message = '';
if (isset($_SESSION['restore_error'])) {
    $error_message = $_SESSION['restore_error'];
    unset($_SESSION['restore_error']);
}

// Fetch archived products
$archived_products = [];
$sql = "SELECT p.*, c.category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.category_id 
        WHERE p.is_archived = 1 
        ORDER BY p.updated_at DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $archived_products[] = $row;
    }
}

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Products - Star Roofing & Construction</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../css/sidebar.css">
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
        
        .content-area {
            padding: 20px 30px;
            flex: 1;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .page-title {
            font-size: 1.8rem;
            color: #2d3748;
            margin: 0;
        }
        
        .back-button {
            background-color: #e9b949;
            color: #1a365d;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .back-button:hover {
            background-color: #1a365d;
            color: white;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .product-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }
        
        .product-image {
            height: 200px;
            overflow: hidden;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .placeholder {
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f7fafc;
            color: #a0aec0;
            font-size: 3rem;
        }
        
        .product-info {
            padding: 1.5rem;
        }
        
        .product-category {
            font-size: 0.8rem;
            color: #718096;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }
        
        .product-name {
            font-size: 1.2rem;
            color: #2d3748;
            margin: 0 0 1rem 0;
            font-weight: 600;
        }
        
        .product-description {
            color: #4a5568;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }
        
        .product-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .product-detail {
            display: flex;
            flex-direction: column;
        }
        
        .detail-label {
            font-size: 0.8rem;
            color: #718096;
            margin-bottom: 0.2rem;
        }
        
        .detail-value {
            font-weight: 500;
            color: #2d3748;
        }
        
        .status {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .status.in-stock {
            background-color: #c6f6d5;
            color: #276749;
        }
        
        .status.low-stock {
            background-color: #feebcb;
            color: #975a16;
        }
        
        .status.out-of-stock {
            background-color: #fed7d7;
            color: #c53030;
        }
        
        .product-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .restore-btn {
            background-color: #38a169;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.3s ease;
        }
        
        .restore-btn:hover {
            background-color: #2f855a;
        }
        
        .no-products {
            text-align: center;
            padding: 3rem;
            color: #718096;
            font-size: 1.1rem;
        }
        
        .archived-badge {
            background-color: #e53e3e;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
            margin-left: 1rem;
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
            
            <div class="content-area">
                <div class="page-header">
                    <h1 class="page-title">Archived Products <span class="archived-badge">Archived</span></h1>
                    <a href="products.php" class="back-button">
                        <i class="fas fa-arrow-left"></i> Back to Products
                    </a>
                </div>
                
                <?php if (count($archived_products) > 0): ?>
                    <div class="products-grid">
                        <?php foreach ($archived_products as $product): 
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
                                        <div class="product-detail">
                                            <span class="detail-label">Archived</span>
                                            <span class="detail-value"><?= date('M j, Y', strtotime($product['updated_at'])) ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="product-actions">
                                        <form action="../crud/restore_product.php" method="POST" class="restore-form" style="display: inline;">
                                            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                            <button type="button" class="restore-btn" data-product-name="<?= htmlspecialchars($product['name']) ?>">
                                                <i class="fas fa-undo"></i> Restore
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-products">
                        <i class="fas fa-archive" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <h3>No Archived Products</h3>
                        <p>There are currently no archived products in the system.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
            
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const parent = this.parentElement;
                    
                    // Close other dropdowns
                    document.querySelectorAll('.has-dropdown').forEach(item => {
                        if (item !== parent) {
                            item.classList.remove('active');
                        }
                    });
                    
                    // Toggle current dropdown
                    parent.classList.toggle('active');
                });
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.has-dropdown')) {
                    document.querySelectorAll('.has-dropdown').forEach(item => {
                        item.classList.remove('active');
                    });
                }
            });

            // SweetAlert for restore confirmation
            const restoreButtons = document.querySelectorAll('.restore-btn');
            
            restoreButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const productName = this.getAttribute('data-product-name');
                    const form = this.closest('.restore-form');
                    
                    Swal.fire({
                        title: 'Restore Product?',
                        html: `Are you sure you want to restore <strong>${productName}</strong>?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#38a169',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, restore it!',
                        cancelButtonText: 'Cancel',
                        showLoaderOnConfirm: true,
                        preConfirm: () => {
                            return fetch(form.action, {
                                method: 'POST',
                                body: new FormData(form)
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Restoration failed');
                                }
                                return response;
                            })
                            .catch(error => {
                                Swal.showValidationMessage(
                                    `Request failed: ${error}`
                                );
                            });
                        },
                        allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Restored!',
                                text: `"${productName}" has been successfully restored.`,
                                icon: 'success',
                                confirmButtonColor: '#38a169',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Refresh the page to show updated list
                                location.reload();
                            });
                        }
                    });
                });
            });

            // Show success message if exists
            <?php if (!empty($success_message)): ?>
                Swal.fire({
                    title: 'Success!',
                    text: '<?= $success_message ?>',
                    icon: 'success',
                    confirmButtonColor: '#38a169',
                    confirmButtonText: 'OK'
                });
            <?php endif; ?>
            
            // Show error message if exists
            <?php if (!empty($error_message)): ?>
                Swal.fire({
                    title: 'Error!',
                    text: '<?= $error_message ?>',
                    icon: 'error',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'OK'
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>