<?php
session_start();
require_once '../database/starroofing_db.php';

// Fetch products and their category names
$products = [];

$query = "
    SELECT 
        p.product_id,
        p.name,
        p.description,
        p.price,
        p.stock_quantity,
        p.unit,
        p.image_path,
        c.category_name
    FROM products AS p
    LEFT JOIN categories AS c ON p.category_id = c.category_id
    WHERE p.is_archived = 0
    ORDER BY p.name ASC
";

$result = $conn->query($query);

if (!$result) {
    die('Query failed: ' . $conn->error);
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Handle add to cart (session-based)
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
//     $product_id = intval($_POST['product_id']);
//     $quantity = max(1, intval($_POST['quantity']));
//     if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
//     if (isset($_SESSION['cart'][$product_id])) {
//         $_SESSION['cart'][$product_id] += $quantity;
//     } else {
//         $_SESSION['cart'][$product_id] = $quantity;
//     }
//     $added = true;
// }

// // Handle remove from cart
// if (isset($_GET['remove'])) {
//     $remove_id = intval($_GET['remove']);
//     unset($_SESSION['cart'][$remove_id]);
// }

// // Prepare cart details
// $cart_items = [];
// $total = 0;
// if (!empty($_SESSION['cart'])) {
//     $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
//     $cart_result = $conn->query("SELECT product_id, name, price FROM products WHERE product_id IN ($ids)");
//     while ($item = $cart_result->fetch_assoc()) {
//         $pid = $item['product_id'];
//         $qty = $_SESSION['cart'][$pid];
//         $item['quantity'] = $qty;
//         $item['subtotal'] = $qty * $item['price'];
//         $cart_items[] = $item;
//         $total += $item['subtotal'];
//     }
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Materials - Star Roofing & Construction</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { margin: 0; font-family: 'Montserrat', sans-serif; background: #f5f7f9; }
        .dashboard-container { display: flex; min-height: 100vh; }
        .main-content { flex: 1; padding: 0; display: flex; flex-direction: column; }
        .materials-content { padding: 32px; }
        .page-title { font-size: 2rem; font-weight: 700; color: #1a365d; margin-bottom: 24px; }

        /* Product grid styling */
        .filter-bar select:hover, .filter-bar input:hover { border-color: #1a365d; }
        .products-grid { display: flex; flex-wrap: wrap; gap: 24px; }
        .product-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(26,54,93,0.08);
            width: 260px;
            padding: 18px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .product-card img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 12px;
            background: #f5f7f9;
        }
        .product-card h3 { margin: 0 0 8px 0; font-size: 1.1rem; color: #1a365d; text-align: center; }
        .product-card p { font-size: 0.9rem; color: #7f8c8d; margin: 0 0 8px 0; text-align: center; }
        .product-card .price { font-weight: 700; color: #e9b949; margin-bottom: 8px; }
        .product-card .stock { font-size: 0.9rem; color: #27ae60; margin-bottom: 10px; }
        .product-card .out-stock { color: #e74c3c; }
        .product-card form { display: flex; gap: 8px; align-items: center; justify-content: center; }
        .product-card input[type="number"] {
            width: 50px; padding: 4px 6px; border-radius: 4px; border: 1px solid #ccc; font-size: 1rem;
        }
        .product-card button {
            background: #1a365d; color: #fff; border: none; border-radius: 4px; padding: 6px 14px;
            font-weight: 600; cursor: pointer; transition: background 0.2s;
        }
        .view-details-btn {
            display: inline-block;
            background: #1a365d;
            color: #fff;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .view-details-btn:hover {
            background: #2b4a83;
        }
        .product-card button:disabled { background: #ccc; cursor: not-allowed; }

        /* Cart styling */
        .cart-section { margin-top: 40px; background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 2px 8px rgba(26,54,93,0.08); }
        .cart-section h2 { color: #1a365d; margin-top: 0; }
        .cart-table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        .cart-table th, .cart-table td { padding: 10px 8px; text-align: left; }
        .cart-table th { background: #f5f7f9; }
        .cart-table td { background: #fff; }
        .cart-table .remove-btn {
            color: #e74c3c; background: none; border: none; cursor: pointer; font-size: 1.1rem;
        }
        .cart-total { text-align: right; font-weight: 700; color: #1a365d; margin-top: 12px; }
        .cart-checkout-btn {
            background: #e9b949; color: #1a365d; border: none; border-radius: 4px; padding: 10px 28px;
            font-weight: 700; font-size: 1rem; margin-top: 18px; cursor: pointer; float: right;
        }
        @media (max-width: 900px) {
            .dashboard-container { flex-direction: column; }
            .products-grid { flex-direction: column; align-items: center; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
            <div class="materials-content">
                <div class="page-title"><i class="fa fa-boxes"></i> Products</div>

                <!-- 🔍 Search & Filter Section -->
                <div class="filter-bar" style="display: flex; gap: 16px; margin-bottom: 24px; align-items: center;">
                    <input type="text" id="searchInput" placeholder="Search materials..." 
                        style="flex: 1; padding: 10px 14px; border-radius: 6px; border: 1px solid #ccc; font-size: 1rem;">
                    <select id="categoryFilter" style="padding: 10px 14px; border-radius: 6px; border: 1px solid #ccc; font-size: 1rem;">
                        <option value="">All Categories</option>
                        <?php
                        $cat_result = $conn->query("SELECT category_name FROM categories ORDER BY category_name ASC");
                        while ($cat = $cat_result->fetch_assoc()):
                        ?>
                            <option value="<?= htmlspecialchars(strtolower($cat['category_name'])) ?>">
                                <?= htmlspecialchars($cat['category_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <?php if (!empty($products)): ?>
                <div class="products-grid" id="productGrid">
                    <?php foreach ($products as $product): ?>
                    <div class="product-card"
                        data-name="<?= strtolower(htmlspecialchars($product['name'])) ?>"
                        data-category="<?= strtolower(htmlspecialchars($product['category_name'] ?? '')) ?>">
                        <?php
                            // file path corrector
                            $imagePath = $product['image_path'] ?? '';
                            if (!empty($imagePath)) {
                                // If it doesn't already start with "../", prepend it
                                if (strpos($imagePath, '../') !== 0) {
                                    $imagePath = '../' . $imagePath;
                                }
                            } else {
                                $imagePath = '../images/no-image.png';
                            }
                            ?>
                            <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($product['name']) ?>">

                        <p><?= htmlspecialchars($product['description']) ?></p>
                        <p><strong>Category:</strong> <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></p>
                        <div class="price">₱<?= number_format($product['price'], 2) ?></div>

                        <?php if ($product['stock_quantity'] > 0): ?>
                            <div class="stock">
                                <?= $product['stock_quantity'] . ' ' . htmlspecialchars($product['unit']) ?> available
                            </div>
                            <a href="pages/item-details.php?product_id=<?= $product['product_id'] ?>" class="view-details-btn">
                                <i class="fa fa-cart-plus"></i> View Details
                            </a>
                        <?php else: ?>
                            <div class="stock out-stock">Out of stock</div>
                            <button type="button" disabled><i class="fa fa-cart-plus"></i> Add</button>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <p>No materials available at the moment.</p>
                <?php endif; ?>

                <!-- 🛒 Cart Section -->
                <div class="cart-section">
                    <h2><i class="fa fa-shopping-cart"></i> My Cart</h2>
                    <?php if (!empty($cart_items)): ?>
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Material</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td>₱<?= number_format($item['price'], 2) ?></td>
                                <td>₱<?= number_format($item['subtotal'], 2) ?></td>
                                <td>
                                    <form method="get" action="">
                                        <input type="hidden" name="remove" value="<?= $item['product_id'] ?>">
                                        <button type="submit" class="remove-btn" title="Remove"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="cart-total">Total: ₱<?= number_format($total, 2) ?></div>
                    <button class="cart-checkout-btn" onclick="alert('Checkout functionality coming soon!')">
                        <i class="fa fa-credit-card"></i> Checkout
                    </button>
                    <?php else: ?>
                        <p>Your cart is empty.</p>
                    <?php endif; ?>
                </div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                // 🔍 Search and filter logic
                const searchInput = document.getElementById('searchInput');
                const categoryFilter = document.getElementById('categoryFilter');
                const productCards = document.querySelectorAll('.product-card');

                function filterProducts() {
                    const searchTerm = searchInput.value.toLowerCase();
                    const selectedCategory = categoryFilter.value.toLowerCase();

                    productCards.forEach(card => {
                        const name = card.getAttribute('data-name');
                        const category = card.getAttribute('data-category');
                        const matchSearch = name.includes(searchTerm);
                        const matchCategory = selectedCategory === '' || category === selectedCategory;

                        card.style.display = (matchSearch && matchCategory) ? 'flex' : 'none';
                    });
                }

                searchInput.addEventListener('input', filterProducts);
                categoryFilter.addEventListener('change', filterProducts);

                // 🛒 Add-to-cart confirmation
                document.querySelectorAll('.addToCartForm').forEach(form => {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'Add to cart?',
                            text: 'Do you want to add this item to your cart?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#1a365d',
                            cancelButtonColor: '#ccc',
                            confirmButtonText: 'Yes, add it'
                        }).then(result => {
                            if (result.isConfirmed) form.submit();
                        });
                    });
                });
            });
            </script>

            <?php if (isset($added) && $added): ?>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Added to cart!',
                    showConfirmButton: false,
                    timer: 1200
                });
            </script>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
