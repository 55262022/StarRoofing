<?php
session_start();
require_once '../../database/starroofing_db.php';

if (!isset($_GET['product_id'])) die('Product not specified.');

$product_id = intval($_GET['product_id']);
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) die('Product not found.');

// Fetch logged-in client's data
$clientData = array(
    'firstname' => '',
    'lastname' => '',
    'email' => '',
    'phone' => '',
    'region_code' => '',
    'region_name' => '',
    'province_code' => '',
    'province_name' => '',
    'city_code' => '',
    'city_name' => '',
    'barangay_code' => '',
    'barangay_name' => '',
    'street' => ''
);

if (isset($_SESSION['account_id'])) {
    $account_id = intval($_SESSION['account_id']);
    $query = "SELECT 
                a.email,
                up.first_name,
                up.last_name,
                up.contact_number,
                up.region_code,
                up.region_name,
                up.province_code,
                up.province_name,
                up.city_code,
                up.city_name,
                up.barangay_code,
                up.barangay_name,
                up.street
              FROM accounts a
              LEFT JOIN user_profiles up ON a.id = up.account_id
              WHERE a.id = ?";
    
    $stmt2 = $conn->prepare($query);
    $stmt2->bind_param("i", $account_id);
    $stmt2->execute();
    $result = $stmt2->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $clientData['firstname'] = htmlspecialchars($user['first_name'] ?? '');
        $clientData['lastname'] = htmlspecialchars($user['last_name'] ?? '');
        $clientData['email'] = htmlspecialchars($user['email'] ?? '');
        $clientData['phone'] = htmlspecialchars($user['contact_number'] ?? '');
        $clientData['region_code'] = htmlspecialchars($user['region_code'] ?? '');
        $clientData['region_name'] = htmlspecialchars($user['region_name'] ?? '');
        $clientData['province_code'] = htmlspecialchars($user['province_code'] ?? '');
        $clientData['province_name'] = htmlspecialchars($user['province_name'] ?? '');
        $clientData['city_code'] = htmlspecialchars($user['city_code'] ?? '');
        $clientData['city_name'] = htmlspecialchars($user['city_name'] ?? '');
        $clientData['barangay_code'] = htmlspecialchars($user['barangay_code'] ?? '');
        $clientData['barangay_name'] = htmlspecialchars($user['barangay_name'] ?? '');
        $clientData['street'] = htmlspecialchars($user['street'] ?? '');
    }
    $stmt2->close();
}

$modelPathFromDb = $product['model_path'] ?? null;
$fullModelPath = $_SERVER['DOCUMENT_ROOT'] . '/STARROOFING/' . ltrim($modelPathFromDb, '/');

$modelPath = ($modelPathFromDb && file_exists($fullModelPath) && is_file($fullModelPath))
    ? '/STARROOFING/' . ltrim($modelPathFromDb, '/')
    : null;

$imagePathFromDb = $product['image_path'] ?? 'images/no-image.png';
$imagePath = '/STARROOFING/' . ltrim($imagePathFromDb, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inquiry - <?= htmlspecialchars($product['name']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
<style>
body { margin:0; font-family:'Montserrat',sans-serif; background:#f5f7f9; }
.back { display:inline-block; margin:16px; text-decoration:none; color:#1a365d; font-weight:600; }
.page-container { display:flex; flex-wrap:wrap; justify-content:center; padding:20px; gap:20px; }
.model-card, .inquiry-card { background:#fff; border-radius:10px; box-shadow:0 2px 8px rgba(26,54,93,0.1); padding:20px; flex:1 1 400px; max-width:500px; }
.model-card { display:flex; flex-direction:column; align-items:center; }
.model-card img, model-viewer { width:100%; border-radius:10px; margin-bottom:15px; }
.model-controls { display:flex; gap:15px; align-items:center; margin-bottom:10px; }
.model-controls label { font-weight:500; color:#1a365d; }
.inquiry-card h2 { color:#1a365d; margin-bottom:20px; }
.inquiry-form { display:grid; grid-template-columns:1fr 1fr; gap:15px; }
.inquiry-form .full-width { grid-column:1/-1; }
.inquiry-form label { display:block; margin-bottom:5px; font-weight:500; color:#1a365d; }
.inquiry-form input, .inquiry-form select, .inquiry-form textarea { width:100%; padding:10px 12px; border:1px solid #ddd; border-radius:6px; font-size:1rem; font-family:'Montserrat',sans-serif; transition:border-color 0.3s; box-sizing:border-box; }
.inquiry-form input:focus, .inquiry-form select:focus, .inquiry-form textarea:focus { outline:none; border-color:#1a365d; box-shadow:0 0 0 2px rgba(26,54,93,0.2); }
.inquiry-form input.error, .inquiry-form select.error, .inquiry-form textarea.error { border-color:#ef4444; }
.inquiry-form textarea { min-height:120px; resize:vertical; }
.button-container { grid-column:1/-1; text-align:center; }
button[type="submit"] { background:#1a365d; color:#fff; border:none; border-radius:6px; padding:12px 25px; font-weight:600; cursor:pointer; transition:0.2s; }
button[type="submit"]:hover { background:#2c5282; }
button[type="submit"]:disabled { opacity:0.6; cursor:not-allowed; }
.form-row { display:flex; gap:15px; }
.form-row .form-group { flex:1; }
.section-title { margin-top:10px; color:#1a365d; font-size:1.1rem; }
@media(max-width:900px){ .page-container{flex-direction:column;align-items:center;} .inquiry-form{grid-template-columns:1fr;} .form-row { flex-direction:column; } }
</style>
</head>
<body>

<a href="item-details.php?product_id=<?= urlencode($product_id) ?>" class="back">
  <i class="fa fa-arrow-left"></i> Back
</a>

<div class="page-container">
    <!-- Product / 3D Model -->
    <div class="model-card">
        <?php if ($modelPath): ?>
        <model-viewer id="modelViewer" src="<?= htmlspecialchars($modelPath) ?>" alt="3D Model" auto-rotate camera-controls ar style="height:350px;"></model-viewer>
        <div class="model-controls">
            <label for="colorPicker">Color:</label>
            <input type="color" id="colorPicker" value="#ffffff">
            <label for="sizeSlider">Size:</label>
            <input type="range" id="sizeSlider" min="0.5" max="2" step="0.1" value="1">
        </div>
        <?php else: ?>
        <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        <?php endif; ?>
        <h3><?= htmlspecialchars($product['name']) ?></h3>
        <p style="color:#e9b949; font-weight:700;">₱<?= number_format($product['price'],2) ?></p>
        <p><?= nl2br(htmlspecialchars($product['description'] ?: 'No description available.')) ?></p>
    </div>

    <!-- Inquiry Form -->
    <div class="inquiry-card">
    <h2>Make an Inquiry</h2>
    <form id="inquiryForm">
        <input type="hidden" name="product_id" value="<?= $product_id ?>">

        <div class="inquiry-form">
        <!-- Personal Info -->
        <div class="form-group"><label>First Name *</label><input type="text" name="firstname" value="<?= $clientData['firstname'] ?>" required></div>
        <div class="form-group"><label>Last Name *</label><input type="text" name="lastname" value="<?= $clientData['lastname'] ?>" required></div>
        <div class="form-group"><label>Email *</label><input type="email" name="email" value="<?= $clientData['email'] ?>" required></div>
        <div class="form-group"><label>Phone *</label><input type="tel" name="phone" value="<?= $clientData['phone'] ?>" required></div>

        <!-- Address Section -->
        <div class="form-section full-width">
            <h3 class="section-title">Address Information</h3>

            <div class="address-group">
                <!-- Region -->
                <div class="form-group full-width">
                    <label for="region">Region *</label>
                    <select id="region" name="region_code" required>
                        <option value="">Select Region</option>
                    </select>
                    <input type="hidden" id="region_name" name="region_name" value="<?= $clientData['region_name'] ?>">
                </div>

                <!-- Province & City -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="province">Province *</label>
                        <select id="province" name="province_code" required disabled>
                            <option value="">Select Province</option>
                        </select>
                        <input type="hidden" id="province_name" name="province_name" value="<?= $clientData['province_name'] ?>">
                    </div>

                    <div class="form-group">
                        <label for="city">City *</label>
                        <select id="city" name="city_code" required disabled>
                            <option value="">Select City</option>
                        </select>
                        <input type="hidden" id="city_name" name="city_name" value="<?= $clientData['city_name'] ?>">
                    </div>
                </div>

                <!-- Barangay & Street -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="barangay">Barangay *</label>
                        <select id="barangay" name="barangay_code" required disabled>
                            <option value="">Select Barangay</option>
                        </select>
                        <input type="hidden" id="barangay_name" name="barangay_name" value="<?= $clientData['barangay_name'] ?>">
                    </div>

                    <div class="form-group">
                        <label for="street">Street</label>
                        <textarea id="street" name="street" placeholder="House No., Street Name, Subdivision, etc."><?= $clientData['street'] ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message -->
        <div class="form-group full-width">
            <label>Your Inquiry *</label>
            <textarea name="message" placeholder="Tell us about your inquiry..." required></textarea>
        </div>

        <!-- Button -->
        <div class="button-container">
            <button type="submit"><i class="fa fa-paper-plane"></i> Submit Inquiry</button>
        </div>
        </div>
    </form>
    </div>
</div>

<!-- Address API -->
<script src="../../javascript/inquiry-address-selector.js"></script>

<script>
// Store client data for address initialization
const clientAddressData = {
    region_code: '<?= $clientData['region_code'] ?>',
    province_code: '<?= $clientData['province_code'] ?>',
    city_code: '<?= $clientData['city_code'] ?>',
    barangay_code: '<?= $clientData['barangay_code'] ?>'
};

$(document).ready(function(){
    const submitBtn = $('#inquiryForm button[type="submit"]');
    const originalText = submitBtn.html();

    // Initialize address selectors with client data after API loads
    setTimeout(function() {
        if (clientAddressData.region_code) {
            $('#region').val(clientAddressData.region_code).trigger('change');
            setTimeout(function() {
                if (clientAddressData.province_code) {
                    $('#province').val(clientAddressData.province_code).trigger('change');
                    setTimeout(function() {
                        if (clientAddressData.city_code) {
                            $('#city').val(clientAddressData.city_code).trigger('change');
                            setTimeout(function() {
                                if (clientAddressData.barangay_code) {
                                    $('#barangay').val(clientAddressData.barangay_code).trigger('change');
                                }
                            }, 300);
                        }
                    }, 300);
                }
            }, 300);
        }
    }, 500);

    // Form submission handler
    $('#inquiryForm').on('submit', function(e){
        e.preventDefault();

        // Validate required fields
        let valid = true;
        $(this).find('input, select, textarea').each(function() {
            if ($(this).prop('required') && !$(this).val()) {
                valid = false; 
                $(this).addClass('error');
            } else { 
                $(this).removeClass('error'); 
            }
        });
        
        if (!valid) {
            Swal.fire({
                icon:'error', 
                title:'Missing Information', 
                text:'Please fill all required fields.', 
                confirmButtonColor:'#1a365d'
            });
            return;
        }

        // Disable button and show loading
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...').prop('disabled', true);
        
        // Submit form via AJAX
        $.ajax({
            url: '../process/save_inquiry.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res){
                console.log('Response:', res);
                if(res.status === 'success'){
                    Swal.fire({
                        icon: 'success', 
                        title: 'Inquiry Submitted!', 
                        html: `
                            <p>Thank you for your inquiry about <strong><?= htmlspecialchars($product['name']) ?></strong>.</p>
                            <p style="margin-top: 15px;">We will review it and get back to you within 24 hours.</p>
                            <p style="margin-top: 10px; font-weight: 600; color: #1a365d;">You can track your inquiry status in the Messages page.</p>
                        `,
                        confirmButtonColor: '#1a365d',
                        confirmButtonText: 'View My Messages',
                        showCancelButton: true,
                        cancelButtonText: 'Continue Browsing'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Redirect to messages page
                            window.location.href = 'client-messages.php';
                        } else {
                            // Go back to product details
                            window.location.href = 'item-details.php?product_id=<?= $product_id ?>';
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error', 
                        title: 'Error', 
                        text: res.message || 'Something went wrong.', 
                        confirmButtonColor: '#1a365d'
                    });
                    submitBtn.html(originalText).prop('disabled', false);
                }
            },
            error: function(xhr, status, error){
                console.error('AJAX Error:', xhr.responseText);
                Swal.fire({
                    icon: 'error', 
                    title: 'Submission Failed', 
                    text: 'An error occurred while submitting your inquiry. Please try again.', 
                    confirmButtonColor: '#1a365d'
                }); 
                submitBtn.html(originalText).prop('disabled', false); 
            }
        });
    });
});

// 3D Model Controls
const viewer = document.getElementById('modelViewer');
if(viewer){
    const colorPicker = document.getElementById('colorPicker');
    const sizeSlider = document.getElementById('sizeSlider');

    viewer.addEventListener('load',()=>{
        const mats = viewer.model?.materials;
        if(mats?.length){
            colorPicker.addEventListener('input',()=>{
                const c=colorPicker.value;
                const rgb=[parseInt(c.slice(1,3),16)/255, parseInt(c.slice(3,5),16)/255, parseInt(c.slice(5,7),16)/255, 1.0];
                mats.forEach(m=>m.pbrMetallicRoughness.setBaseColorFactor(rgb));
            });
        }
    });
    sizeSlider.addEventListener('input',()=>{ const s=parseFloat(sizeSlider.value); viewer.scale=`${s} ${s} ${s}`; });
}
</script>
</body>
</html>