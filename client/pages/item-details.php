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

    // Corrected RGB from hex
    ['name' => 'Clay', 'hex' => '#2E2E32', 'rgb' => [0.18, 0.18, 0.20]],
    ['name' => 'Sangria', 'hex' => '#5E1914', 'rgb' => [0.37, 0.10, 0.08]],
    ['name' => 'Eggshell', 'hex' => '#DCCE92', 'rgb' => [0.86, 0.81, 0.57]],
    ['name' => 'Dark Royal Blue', 'hex' => '#202073', 'rgb' => [0.13, 0.13, 0.45]],
    ['name' => 'Chili Red', 'hex' => '#951411', 'rgb' => [0.58, 0.08, 0.07]],
    ['name' => 'Dark Green', 'hex' => '#006400', 'rgb' => [0.0, 0.39, 0.0]],
    ['name' => 'Brown', 'hex' => '#3D2411', 'rgb' => [0.24, 0.14, 0.07]],
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
    <link rel="stylesheet" href="../../css/item-details.css">
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
        <button class="btn-add-cart" type="button" id="addToCartBtn">
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
            window.location.href = 'checkout.php?product_id=<?= $product_id ?>&quantity=' + formData.get('quantity');
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Replace the existing SweetAlert script with this custom modal
document.getElementById('addToCartBtn').addEventListener('click', function() {
    // Create modal HTML
    const modalHTML = `
        <div class="cart-modal-overlay" id="cartModal">
            <div class="cart-modal">
                <button class="modal-close" onclick="closeCartModal()">
                    <i class="fas fa-times"></i>
                </button>
                
                <div class="modal-header">
                    <i class="fas fa-shopping-cart"></i>
                    <h2>Add to Cart</h2>
                </div>

                <div class="modal-body">
                    <div class="product-preview">
                        <img src="<?= htmlspecialchars($imagePath) ?>" alt="Product">
                        <div class="product-details">
                            <h3><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="price">₱<?= number_format($product['price'], 2) ?></p>
                        </div>
                    </div>

                    <form id="modalCartForm">
                        <div class="modal-input-group">
                            <label>
                                <i class="fas fa-hashtag"></i> Quantity
                            </label>
                            <div class="quantity-control-modal">
                                <button type="button" onclick="modalDecrease()">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" id="modalQty" value="1" min="1" max="<?= $product['stock_quantity'] ?>" readonly>
                                <button type="button" onclick="modalIncrease()">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="modal-input-group">
                            <label>
                                <i class="fas fa-ruler"></i> Size
                            </label>
                            <select id="modalSize" required>
                                <option value="">Select Size</option>
                                <option value="Small">Small</option>
                                <option value="Medium">Medium</option>
                                <option value="Large">Large</option>
                                <option value="XL">XL</option>
                            </select>
                        </div>

                        <div class="modal-input-group">
                            <label>
                                <i class="fas fa-palette"></i> Color
                            </label>
                            <div class="color-grid">
                                <div class="color-option" data-color="White" onclick="selectModalColor(this)">
                                    <div class="color-swatch" style="background: #FFFFFF; border: 2px solid #ddd;"></div>
                                    <span>White</span>
                                </div>
                                <div class="color-option" data-color="Silver" onclick="selectModalColor(this)">
                                    <div class="color-swatch" style="background: #C0C0C0;"></div>
                                    <span>Silver</span>
                                </div>
                                <div class="color-option" data-color="Gray" onclick="selectModalColor(this)">
                                    <div class="color-swatch" style="background: #808080;"></div>
                                    <span>Gray</span>
                                </div>
                                <div class="color-option" data-color="Black" onclick="selectModalColor(this)">
                                    <div class="color-swatch" style="background: #000000;"></div>
                                    <span>Black</span>
                                </div>
                                <div class="color-option" data-color="Red" onclick="selectModalColor(this)">
                                    <div class="color-swatch" style="background: #DC143C;"></div>
                                    <span>Red</span>
                                </div>
                                <div class="color-option" data-color="Blue" onclick="selectModalColor(this)">
                                    <div class="color-swatch" style="background: #1E90FF;"></div>
                                    <span>Blue</span>
                                </div>
                                <div class="color-option" data-color="Green" onclick="selectModalColor(this)">
                                    <div class="color-swatch" style="background: #228B22;"></div>
                                    <span>Green</span>
                                </div>
                            </div>
                            <input type="hidden" id="modalColor" required>
                        </div>

                        <div class="modal-summary">
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span class="summary-value" id="modalSubtotal">₱<?= number_format($product['price'], 2) ?></span>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" onclick="closeCartModal()">
                        Cancel
                    </button>
                    <button type="button" class="btn-modal-add" onclick="submitModalCart()">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                </div>
            </div>
        </div>
    `;

    // Inject modal into body
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
});

// Modal control functions
function closeCartModal() {
    const modal = document.getElementById('cartModal');
    modal.classList.add('closing');
    setTimeout(() => {
        modal.remove();
        document.body.style.overflow = '';
    }, 300);
}

function modalDecrease() {
    const input = document.getElementById('modalQty');
    if (input.value > 1) {
        input.value = parseInt(input.value) - 1;
        updateModalSubtotal();
    }
}

function modalIncrease() {
    const input = document.getElementById('modalQty');
    const max = parseInt(input.max);
    if (input.value < max) {
        input.value = parseInt(input.value) + 1;
        updateModalSubtotal();
    }
}

function selectModalColor(element) {
    document.querySelectorAll('.color-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    element.classList.add('selected');
    document.getElementById('modalColor').value = element.dataset.color;
}

function updateModalSubtotal() {
    const qty = parseInt(document.getElementById('modalQty').value);
    const price = <?= $product['price'] ?>;
    const subtotal = qty * price;
    document.getElementById('modalSubtotal').textContent = '₱' + subtotal.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

async function submitModalCart() {
    const qty = document.getElementById('modalQty').value;
    const size = document.getElementById('modalSize').value;
    const color = document.getElementById('modalColor').value;

    // Validation
    if (!size) {
        showModalError('Please select a size');
        return;
    }
    if (!color) {
        showModalError('Please select a color');
        return;
    }

    // Show loading state
    const addBtn = document.querySelector('.btn-modal-add');
    const originalText = addBtn.innerHTML;
    addBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    addBtn.disabled = true;

    try {
        const response = await fetch('../actions/add_to_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                product_id: '<?= $product_id ?>',
                quantity: qty,
                size: size,
                color: color
            })
        });

        const data = await response.json();

        if (data.status === 'success') {
            showSuccessMessage(qty, size, color);
            closeCartModal();
        } else {
            showModalError(data.message || 'Failed to add to cart');
            addBtn.innerHTML = originalText;
            addBtn.disabled = false;
        }
    } catch (error) {
        showModalError('Network error. Please try again.');
        addBtn.innerHTML = originalText;
        addBtn.disabled = false;
    }
}

function showModalError(message) {
    const existingError = document.querySelector('.modal-error');
    if (existingError) existingError.remove();

    const errorDiv = document.createElement('div');
    errorDiv.className = 'modal-error';
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
    
    const modalBody = document.querySelector('.modal-body');
    modalBody.insertBefore(errorDiv, modalBody.firstChild);

    setTimeout(() => errorDiv.remove(), 3000);
}

function showSuccessMessage(qty, size, color) {
    const successHTML = `
        <div class="success-toast">
            <div class="success-content">
                <i class="fas fa-check-circle"></i>
                <div>
                    <h4>Added to Cart!</h4>
                    <p>${qty}x ${size} - ${color}</p>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', successHTML);
    
    setTimeout(() => {
        document.querySelector('.success-toast').classList.add('show');
    }, 10);
    
    setTimeout(() => {
        const toast = document.querySelector('.success-toast');
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Close modal on overlay click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('cart-modal-overlay')) {
        closeCartModal();
    }
});

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('cartModal')) {
        closeCartModal();
    }
});
</script>


</body>
</html>