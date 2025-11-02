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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Materials - Star Roofing & Construction</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body { 
            font-family: 'Montserrat', sans-serif; 
            background: #0a0a0a;
            color: #fff;
            overflow-x: hidden;
        }

        .materials-hero {
            background: linear-gradient(135deg, rgba(26, 26, 46, 0.95) 0%, rgba(15, 15, 15, 0.85) 100%);
            padding: 60px 20px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .materials-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 20%, rgba(233, 185, 73, 0.1) 0%, transparent 60%);
        }

        /* Back Button */
        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            z-index: 2;
        }

        .back-button:hover {
            background: rgba(233, 185, 73, 0.2);
            border-color: rgba(233, 185, 73, 0.4);
            color: #e9b949;
            transform: translateX(-5px);
        }

        .back-button i {
            font-size: 1rem;
        }

        .materials-hero h1 {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(to right, #ffffff, #e9b949);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }

        .materials-hero p {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.7);
            position: relative;
            z-index: 1;
        }

        .materials-content { 
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* Filter Bar */
        .filter-bar {
            display: flex;
            gap: 16px;
            margin-bottom: 40px;
            align-items: center;
            flex-wrap: wrap;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
        }

        .filter-bar input[type="text"],
        .filter-bar select {
            flex: 1;
            min-width: 200px;
            padding: 12px 18px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            font-size: 0.95rem;
            font-family: 'Montserrat', sans-serif;
            transition: all 0.3s ease;
        }

        .filter-bar input[type="text"]::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .filter-bar input[type="text"]:focus,
        .filter-bar select:focus {
            outline: none;
            border-color: #e9b949;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(233, 185, 73, 0.1);
        }

        .filter-bar select option {
            background: #1a1a2e;
            color: #fff;
        }

        /* Products Grid */
        .products-grid { 
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .product-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #e9b949, transparent);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .product-card:hover {
            background: rgba(233, 185, 73, 0.05);
            border-color: rgba(233, 185, 73, 0.3);
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(233, 185, 73, 0.2);
        }

        .product-card:hover::before {
            transform: scaleX(1);
        }

        .product-image-container {
            width: 100%;
            height: 200px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-card:hover img {
            transform: scale(1.1);
        }

        .product-category {
            display: inline-block;
            background: rgba(233, 185, 73, 0.2);
            color: #e9b949;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
        }

        .product-card h3 { 
            margin: 0 0 12px 0; 
            font-size: 1.3rem; 
            color: #fff;
            font-weight: 700;
            line-height: 1.3;
        }

        .product-card .description { 
            font-size: 0.9rem; 
            color: rgba(255, 255, 255, 0.6);
            margin: 0 0 15px 0;
            line-height: 1.6;
            flex-grow: 1;
        }

        .product-card .price { 
            font-weight: 800; 
            color: #e9b949;
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .product-card .stock { 
            font-size: 0.85rem; 
            color: #27ae60;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .product-card .stock i {
            font-size: 0.9rem;
        }

        .product-card .out-stock { 
            color: #e74c3c;
        }

        .view-details-btn {
            display: block;
            background: #e9b949;
            color: #1a1a2e;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 700;
            text-align: center;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.9rem;
            border: 2px solid #e9b949;
        }

        .view-details-btn:hover {
            background: transparent;
            color: #e9b949;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(233, 185, 73, 0.3);
        }

        .view-details-btn:disabled,
        button:disabled {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.3);
            cursor: not-allowed;
            border-color: transparent;
        }

        .view-details-btn:disabled:hover {
            transform: none;
            box-shadow: none;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: rgba(255, 255, 255, 0.5);
        }

        .empty-state i {
            font-size: 4rem;
            color: rgba(233, 185, 73, 0.3);
            margin-bottom: 20px;
        }

        .empty-state p {
            font-size: 1.2rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .back-button {
                top: 15px;
                left: 15px;
                padding: 8px 16px;
                font-size: 0.85rem;
            }

            .materials-hero h1 {
                font-size: 2rem;
            }

            .materials-hero p {
                font-size: 0.95rem;
            }

            .filter-bar {
                flex-direction: column;
                padding: 15px;
            }

            .filter-bar input[type="text"],
            .filter-bar select {
                width: 100%;
                min-width: 100%;
            }

            .products-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .product-card {
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .back-button {
                position: relative;
                top: 0;
                left: 0;
                margin-bottom: 15px;
                width: fit-content;
            }

            .materials-hero {
                padding: 40px 15px 30px;
            }

            .materials-content {
                padding: 30px 15px;
            }

            .product-card h3 {
                font-size: 1.1rem;
            }

            .product-card .price {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <div class="materials-hero">
        <a href="category-page.php" class="back-button">
            <i class="fa fa-arrow-left"></i> Back
        </a>
        <h1><i class="fa fa-boxes"></i> Our Products</h1>
        <p>Premium Quality Roofing & Construction Materials</p>
    </div>

    <div class="materials-content">
        <!-- Search & Filter Section -->
        <div class="filter-bar">
            <input type="text" id="searchInput" placeholder="🔍 Search materials..." />
        </div>

        <!-- Products Grid -->
        <?php if (!empty($products)): ?>
        <div class="products-grid" id="productGrid">
            <?php foreach ($products as $product): ?>
            <div class="product-card"
                data-name="<?= strtolower(htmlspecialchars($product['name'])) ?>"
                data-category="<?= strtolower(htmlspecialchars($product['category_name'] ?? '')) ?>">
                
                <div class="product-image-container">
                    <?php
                        $imagePath = $product['image_path'] ?? '';
                        if (!empty($imagePath)) {
                            if (strpos($imagePath, '../') !== 0) {
                                $imagePath = '../' . $imagePath;
                            }
                        } else {
                            $imagePath = '../images/no-image.png';
                        }
                    ?>
                    <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                </div>

                <span class="product-category">
                    <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?>
                </span>

                <h3><?= htmlspecialchars($product['name']) ?></h3>
                <p class="description"><?= htmlspecialchars($product['description']) ?></p>
                
                <div class="price">₱<?= number_format($product['price'], 2) ?></div>

                <div class="stock <?= $product['stock_quantity'] > 0 ? '' : 'out-stock' ?>">

                    <?php if ($product['stock_quantity'] > 0): ?>
                        <i class="fa fa-check-circle"></i>
                        <?= $product['stock_quantity'] . ' ' . htmlspecialchars($product['unit']) ?> available
                    <?php else: ?>
                        <i class="fa fa-times-circle"></i>
                        Out of stock
                    <?php endif; ?>
                </div>
                <a href="pages/item-details.php?product_id=<?= $product['product_id'] ?>" class="view-details-btn">
                    <i class="fa fa-eye"></i> View Details
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa fa-box-open"></i>
                <p>No products available at the moment.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const categoryFilter = document.getElementById('categoryFilter');
        const productCards = document.querySelectorAll('.product-card');

        function filterProducts() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedCategory = categoryFilter.value.toLowerCase();
            let visibleCount = 0;

            productCards.forEach(card => {
                const name = card.getAttribute('data-name');
                const category = card.getAttribute('data-category');
                const matchSearch = name.includes(searchTerm);
                const matchCategory = selectedCategory === '' || category === selectedCategory;

                if (matchSearch && matchCategory) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Show empty state if no results
            const productGrid = document.getElementById('productGrid');
            let emptyState = document.querySelector('.empty-state');
            
            if (visibleCount === 0 && !emptyState) {
                emptyState = document.createElement('div');
                emptyState.className = 'empty-state';
                emptyState.innerHTML = '<i class="fa fa-search"></i><p>No materials found matching your search.</p>';
                productGrid.parentNode.appendChild(emptyState);
            } else if (visibleCount > 0 && emptyState) {
                emptyState.remove();
            }
        }

        searchInput.addEventListener('input', filterProducts);
        categoryFilter.addEventListener('change', filterProducts);
    });
    </script>
</body>
</html>