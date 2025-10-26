<?php
session_start();
require_once '../../database/starroofing_db.php';

if (!isset($_GET['product_id'])) {
    die('Product not specified.');
}

$product_id = intval($_GET['product_id']);
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die('Product not found.');
}

// Check for 3D model in generated_3d_models table
$model3dPath = null;
if ($product['generated_model_id']) {
    $modelStmt = $conn->prepare("SELECT model_path FROM generated_3d_models WHERE id = ? AND generation_status = 'succeeded'");
    $modelStmt->bind_param("i", $product['generated_model_id']);
    $modelStmt->execute();
    $modelResult = $modelStmt->get_result()->fetch_assoc();
    if ($modelResult && !empty($modelResult['model_path'])) {
        $model3dPath = $modelResult['model_path'];
    }
}

// Fallback to product's model_path if no generated model
if (!$model3dPath && !empty($product['model_path'])) {
    $model3dPath = $product['model_path'];
}

// Prepare full path for 3D model
$modelPath = null;
if ($model3dPath) {
    $fullModelPath = $_SERVER['DOCUMENT_ROOT'] . '/STARROOFING/' . ltrim($model3dPath, '/');
    if (file_exists($fullModelPath) && is_file($fullModelPath)) {
        $modelPath = '/STARROOFING/' . ltrim($model3dPath, '/');
    }
}

$imagePathFromDb = $product['image_path'] ?? 'images/no-image.png';
$imagePath = '/STARROOFING/' . ltrim($imagePathFromDb, '/');

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = max(1, intval($_POST['quantity']));
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    echo "<script>
        alert('Added to cart successfully!');
        window.location.href='../materials.php';
    </script>";
    exit;
}

// Predefined colors
$colors = [
    ['name' => 'White', 'hex' => '#FFFFFF', 'rgb' => [1.0, 1.0, 1.0]],
    ['name' => 'Silver', 'hex' => '#C0C0C0', 'rgb' => [0.75, 0.75, 0.75]],
    ['name' => 'Gray', 'hex' => '#808080', 'rgb' => [0.5, 0.5, 0.5]],
    ['name' => 'Black', 'hex' => '#000000', 'rgb' => [0.0, 0.0, 0.0]],
    ['name' => 'Red', 'hex' => '#DC143C', 'rgb' => [0.86, 0.08, 0.24]],
    ['name' => 'Blue', 'hex' => '#1E90FF', 'rgb' => [0.12, 0.56, 1.0]],
    ['name' => 'Green', 'hex' => '#228B22', 'rgb' => [0.13, 0.55, 0.13]],
    ['name' => 'Brown', 'hex' => '#8B4513', 'rgb' => [0.55, 0.27, 0.07]],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - Product Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>

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
            min-height: 100vh;
            padding-bottom: 100px;
        }

        /* Back Button */
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #fff;
            font-weight: 600;
            padding: 20px 30px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
            margin: 20px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .back-button:hover {
            background: rgba(233, 185, 73, 0.1);
            color: #e9b949;
            transform: translateX(-5px);
        }

        /* Main Container */
        .product-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 40px;
            margin-bottom: 120px;
        }

        /* Left Column - Image/Model */
        .product-media {
            position: relative;
        }

        /* View Toggle Buttons */
        .view-toggle {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .view-toggle button {
            flex: 1;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: rgba(255, 255, 255, 0.6);
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .view-toggle button:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.3);
            color: rgba(255, 255, 255, 0.8);
        }

        .view-toggle button.active {
            background: rgba(233, 185, 73, 0.15);
            border-color: #e9b949;
            color: #e9b949;
        }

        .view-toggle button:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        /* Media Container */
        .media-container {
            position: relative;
            width: 100%;
            height: 500px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.05);
            overflow: hidden;
        }

        .product-media img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 20px;
            display: none;
        }

        .product-media img.active {
            display: block;
        }

        model-viewer {
            width: 100%;
            height: 100%;
            display: none;
        }

        model-viewer.active {
            display: block;
        }

        /* 3D Model Controls Info */
        .model-controls-info {
            position: absolute;
            bottom: 15px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            padding: 12px 20px;
            border-radius: 20px;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.8);
            display: none;
            z-index: 10;
            white-space: nowrap;
        }

        model-viewer.active ~ .model-controls-info {
            display: block;
        }

        .model-controls-info i {
            color: #e9b949;
            margin: 0 5px;
        }

        /* Right Column - Info */
        .product-info h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 15px;
            line-height: 1.2;
        }

        .product-price {
            font-size: 2.5rem;
            font-weight: 800;
            color: #e9b949;
            margin-bottom: 25px;
        }

        .product-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .meta-item {
            background: rgba(255, 255, 255, 0.05);
            padding: 12px 20px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .meta-item i {
            color: #e9b949;
            margin-right: 8px;
        }

        .meta-label {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.6);
            display: block;
            margin-bottom: 4px;
        }

        .meta-value {
            font-weight: 600;
            color: #fff;
        }

        /* Description */
        .product-description {
            margin-bottom: 30px;
        }

        .product-description h3 {
            font-size: 1.2rem;
            color: #e9b949;
            margin-bottom: 12px;
            font-weight: 700;
        }

        .product-description p {
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.8;
        }

        /* Customization Section */
        .customization-section {
            margin: 30px 0;
            padding: 25px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .customization-section h3 {
            font-size: 1.1rem;
            color: #e9b949;
            margin-bottom: 20px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Color Selection */
        .color-selection {
            margin-bottom: 25px;
        }

        .color-label {
            display: block;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 12px;
            font-weight: 600;
        }

        .color-options {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .color-btn {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            border: 3px solid transparent;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .color-btn:hover {
            transform: scale(1.1);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .color-btn.active {
            border-color: #e9b949;
            transform: scale(1.15);
            box-shadow: 0 0 0 4px rgba(233, 185, 73, 0.2);
        }

        .color-btn::after {
            content: attr(data-name);
            position: absolute;
            bottom: -25px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.6);
            white-space: nowrap;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .color-btn:hover::after,
        .color-btn.active::after {
            opacity: 1;
        }

        /* Input Fields */
        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 8px;
            font-weight: 600;
        }

        .input-group input,
        .input-group select {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: #fff;
            font-size: 1rem;
            font-family: 'Montserrat', sans-serif;
            transition: all 0.3s ease;
        }

        .input-group input:focus,
        .input-group select:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.08);
            border-color: #e9b949;
            box-shadow: 0 0 0 3px rgba(233, 185, 73, 0.1);
        }

        .input-group select option {
            background: #1a1a2e;
            color: #fff;
        }

        /* Quantity */
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 25px 0;
        }

        .quantity-selector label {
            font-weight: 600;
            color: rgba(255, 255, 255, 0.8);
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .quantity-controls button {
            background: transparent;
            border: none;
            color: #e9b949;
            font-size: 1.2rem;
            padding: 10px 15px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .quantity-controls button:hover {
            background: rgba(233, 185, 73, 0.1);
        }

        .quantity-controls input {
            width: 60px;
            text-align: center;
            background: transparent;
            border: none;
            color: #fff;
            font-weight: 600;
            font-size: 1.1rem;
        }

        /* Fixed Bottom Bar */
        .bottom-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(26, 26, 46, 0.98);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            box-shadow: 0 -5px 30px rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .bottom-bar button {
            flex: 1;
            max-width: 300px;
            padding: 16px 32px;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-family: 'Montserrat', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-buy-now {
            background: #e9b949;
            color: #1a1a2e;
            border: 2px solid #e9b949;
        }

        .btn-buy-now:hover {
            background: transparent;
            color: #e9b949;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(233, 185, 73, 0.4);
        }

        .btn-add-cart {
            background: transparent;
            color: #fff;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .btn-add-cart:hover {
            border-color: #e9b949;
            color: #e9b949;
            background: rgba(233, 185, 73, 0.1);
            transform: translateY(-3px);
        }

        /* Responsive */
        @media (max-width: 968px) {
            .product-grid {
                grid-template-columns: 1fr;
                gap: 30px;
                padding: 30px;
            }

            .product-info h1 {
                font-size: 2rem;
            }

            .product-price {
                font-size: 2rem;
            }
        }

        @media (max-width: 768px) {
            .product-container {
                padding: 10px;
            }

            .product-grid {
                padding: 20px;
            }

            .back-button {
                margin: 10px;
            }

            .bottom-bar {
                flex-direction: column;
                padding: 15px;
            }

            .bottom-bar button {
                width: 100%;
                max-width: 100%;
            }

            .color-options {
                gap: 8px;
            }

            .color-btn {
                width: 45px;
                height: 45px;
            }

            .media-container {
                height: 400px;
            }
        }

        @media (max-width: 480px) {
            .product-info h1 {
                font-size: 1.5rem;
            }

            .product-price {
                font-size: 1.8rem;
            }

            .product-meta {
                flex-direction: column;
                gap: 10px;
            }

            .customization-section {
                padding: 15px;
            }

            .media-container {
                height: 350px;
            }

            .view-toggle button {
                font-size: 0.8rem;
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>

    <a href="../materials.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back to Products
    </a>

    <div class="product-container">
        <div class="product-grid">
            <!-- Left Column - Media -->
            <div class="product-media">
                <!-- View Toggle Buttons -->
                <div class="view-toggle">
                    <button id="imageViewBtn" class="active" onclick="switchView('image')">
                        <i class="fas fa-image"></i> Image
                    </button>
                    <button id="modelViewBtn" onclick="switchView('model')" <?= !$modelPath ? 'disabled' : '' ?>>
                        <i class="fas fa-cube"></i> 3D Model
                    </button>
                </div>

                <!-- Media Container -->
                <div class="media-container">
                    <img id="productImage" 
                         src="<?= htmlspecialchars($imagePath) ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>"
                         class="active">
                    
                    <?php if ($modelPath): ?>
                    <model-viewer 
                        id="modelViewer"
                        src="<?= htmlspecialchars($modelPath) ?>"
                        alt="3D model of <?= htmlspecialchars($product['name']) ?>"
                        auto-rotate
                        camera-controls
                        ar
                        shadow-intensity="1">
                    </model-viewer>
                    <div class="model-controls-info">
                        <i class="fas fa-mouse"></i> Drag to rotate
                        <i class="fas fa-search-plus"></i> Scroll to zoom
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column - Info -->
            <div class="product-info">
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                <div class="product-price">₱<?= number_format($product['price'], 2) ?></div>

                <div class="product-meta">
                    <div class="meta-item">
                        <span class="meta-label">Stock</span>
                        <div class="meta-value">
                            <i class="fas fa-box"></i>
                            <?= $product['stock_quantity'] ?> <?= htmlspecialchars($product['unit']) ?>
                        </div>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Unit</span>
                        <div class="meta-value">
                            <i class="fas fa-ruler"></i>
                            <?= htmlspecialchars($product['unit']) ?>
                        </div>
                    </div>
                </div>

                <div class="product-description">
                    <h3><i class="fas fa-info-circle"></i> Description</h3>
                    <p><?= nl2br(htmlspecialchars($product['description'] ?: 'No description available.')) ?></p>
                </div>

                <?php if ($modelPath): ?>
                <!-- Customization Options -->
                <div class="customization-section">
                    <h3><i class="fas fa-palette"></i> Customize Your Product</h3>
                    
                    <!-- Color Selection -->
                    <div class="color-selection">
                        <span class="color-label">Select Color:</span>
                        <div class="color-options">
                            <?php foreach ($colors as $index => $color): ?>
                                <button 
                                    class="color-btn <?= $index === 0 ? 'active' : '' ?>" 
                                    style="background-color: <?= $color['hex'] ?>"
                                    data-name="<?= $color['name'] ?>"
                                    data-rgb="<?= implode(',', $color['rgb']) ?>"
                                    onclick="changeColor(this)">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Length Input -->
                    <div class="input-group">
                        <label for="lengthInput">
                            <i class="fas fa-ruler-horizontal"></i> Length (meters):
                        </label>
                        <input 
                            type="number" 
                            id="lengthInput" 
                            name="length" 
                            min="0.1" 
                            step="0.1" 
                            value="1.0"
                            placeholder="Enter length in meters">
                    </div>

                    <!-- Material Input -->
                    <div class="input-group">
                        <label for="materialSelect">
                            <i class="fas fa-layer-group"></i> Material Type:
                        </label>
                        <select id="materialSelect" name="material">
                            <option value="steel">Steel</option>
                            <option value="aluminum">Aluminum</option>
                            <option value="galvanized">Galvanized Iron</option>
                            <option value="stainless">Stainless Steel</option>
                            <option value="copper">Copper</option>
                            <option value="zinc">Zinc</option>
                        </select>
                    </div>

                    <!-- Size Slider -->
                    <div class="input-group">
                        <label for="sizeSlider">
                            <i class="fas fa-expand"></i> Scale: <span id="scaleValue">1.0x</span>
                        </label>
                        <input 
                            type="range" 
                            id="sizeSlider" 
                            min="0.5" 
                            max="2" 
                            step="0.1" 
                            value="1"
                            style="width: 100%;">
                    </div>
                </div>
                <?php endif; ?>

                <!-- Quantity Selector -->
                <form method="post" id="cartForm">
                    <div class="quantity-selector">
                        <label>Quantity:</label>
                        <div class="quantity-controls">
                            <button type="button" onclick="decreaseQuantity()">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input 
                                type="number" 
                                name="quantity" 
                                id="quantityInput"
                                value="1" 
                                min="1" 
                                max="<?= $product['stock_quantity'] ?>"
                                readonly>
                            <button type="button" onclick="increaseQuantity()">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="add_to_cart" value="1">
                </form>
            </div>
        </div>
    </div>

    <!-- Fixed Bottom Bar -->
    <div class="bottom-bar">
        <button class="btn-buy-now" onclick="buyNow()">
            <i class="fas fa-shopping-bag"></i> Buy Now
        </button>
        <button class="btn-add-cart" type="submit" form="cartForm">
            <i class="fas fa-cart-plus"></i> Add to Cart
        </button>
    </div>

    <script>
        // View Toggle Function
        function switchView(view) {
            const imageViewBtn = document.getElementById('imageViewBtn');
            const modelViewBtn = document.getElementById('modelViewBtn');
            const productImage = document.getElementById('productImage');
            const modelViewer = document.getElementById('modelViewer');

            if (view === 'image') {
                imageViewBtn.classList.add('active');
                modelViewBtn.classList.remove('active');
                productImage.classList.add('active');
                if (modelViewer) {
                    modelViewer.classList.remove('active');
                }
            } else if (view === 'model' && modelViewer) {
                modelViewBtn.classList.add('active');
                imageViewBtn.classList.remove('active');
                modelViewer.classList.add('active');
                productImage.classList.remove('active');
            }
        }

        // Quantity Controls
        function decreaseQuantity() {
            const input = document.getElementById('quantityInput');
            const min = parseInt(input.min);
            if (input.value > min) {
                input.value = parseInt(input.value) - 1;
            }
        }

        function increaseQuantity() {
            const input = document.getElementById('quantityInput');
            const max = parseInt(input.max);
            if (input.value < max) {
                input.value = parseInt(input.value) + 1;
            }
        }

        // Buy Now Function
        function buyNow() {
            const form = document.getElementById('cartForm');
            const formData = new FormData(form);
            
            alert('Proceeding to checkout...');
            // window.location.href = 'checkout.php?product_id=<?= $product_id ?>&quantity=' + formData.get('quantity');
        }

        <?php if ($modelPath): ?>
        // 3D Model Customization
        document.addEventListener('DOMContentLoaded', function () {
            const viewer = document.getElementById('modelViewer');
            if (!viewer) return;

            const sizeSlider = document.getElementById('sizeSlider');
            const scaleValue = document.getElementById('scaleValue');

            // Wait for model to load
            viewer.addEventListener('load', () => {
                console.log('3D model loaded successfully');
            });

            // Handle scale change
            if (sizeSlider) {
                sizeSlider.addEventListener('input', () => {
                    const scale = parseFloat(sizeSlider.value);
                    scaleValue.textContent = scale.toFixed(1) + 'x';
                    viewer.scale = `${scale} ${scale} ${scale}`;
                });
            }
        });

        // Color Change Function
        function changeColor(button) {
            document.querySelectorAll('.color-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            button.classList.add('active');
            
            const rgbString = button.getAttribute('data-rgb');
            const rgb = rgbString.split(',').map(parseFloat);
            rgb.push(1.0);

            const viewer = document.getElementById('modelViewer');
            if (viewer && viewer.model) {
                const applyColor = () => {
                    const materials = viewer.model?.materials;
                    if (materials && materials.length > 0) {
                        materials.forEach(material => {
                            material.pbrMetallicRoughness.setBaseColorFactor(rgb);
                        });
                    }
                };

                if (viewer.loaded) {
                    applyColor();
                } else {
                    viewer.addEventListener('load', applyColor, { once: true });
                }
            }
        }
        <?php endif; ?>
    </script>
</body>
</html>