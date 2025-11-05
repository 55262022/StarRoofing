<?php
require_once '../database/starroofing_db.php';
require_once '../authentication/auth.php';

// Get product ID from URL if provided
$productId = isset($_GET['product_id']) ? intval($_GET['product_id']) : null;
$productData = null;

// Fetch product details if product_id is provided
if ($productId) {
    $stmt = $conn->prepare("
        SELECT p.product_id, p.name, p.image_path, p.model_path, p.model_url, 
               g.model_path as generated_model_path, g.model_filename, g.generation_status
        FROM products p
        LEFT JOIN generated_3d_models g ON p.generated_model_id = g.id
        WHERE p.product_id = ?
    ");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $productData = $result->fetch_assoc();
        
        // Determine which model path to use
        if (!empty($productData['generated_model_path'])) {
            $productData['final_model_path'] = $productData['generated_model_path'];
        } elseif (!empty($productData['model_path'])) {
            $productData['final_model_path'] = $productData['model_path'];
        } else {
            $productData['final_model_path'] = null;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>3D Editor — Star Roofing</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    :root {
      --bg: #f5f7fb;
      --panel: #ffffff;
      --muted: #6b7280;
      --accent: #0d6efd;
    }
    body {
      font-family: 'Montserrat', sans-serif;
      background: var(--bg);
      color: #111827;
      margin: 0;
      -webkit-font-smoothing: antialiased;
    }

    .app-shell {
      min-height: 100vh;
      display: flex;
      gap: 1.25rem;
      padding: 1.25rem;
    }

    .left-col {
      width: 300px;
      max-width: 30%;
      min-width: 260px;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .canvas-col {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .right-col {
      width: 320px;
      max-width: 32%;
      min-width: 280px;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .card-panel {
      background: var(--panel);
      border-radius: 12px;
      box-shadow: 0 6px 18px rgba(255, 255, 255, 0.06);
      padding: 1rem;
    }

    #threeViewport {
      height: 72vh;
      border-radius: 10px;
      overflow: hidden;
      background: #ffffff;
      border: 1px solid rgba(0, 0, 0, 0.1);
      position: relative;
    }

    /* FIXED: CENTERED LOADING OVERLAY WITH SCROLL */
    .loading-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.85);
      backdrop-filter: blur(8px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      overflow-y: auto;
      padding: 20px;
    }

    .loading-overlay.active {
      display: flex;
    }

    .loading-content {
      text-align: center;
      padding: 30px;
      max-width: 500px;
      width: 100%;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      margin: auto;
      max-height: 90vh;
    }

    .loading-spinner {
      width: 80px;
      height: 80px;
      margin: 0 auto 20px;
      border: 6px solid #e0e7ff;
      border-top: 6px solid #0d6efd;
      border-radius: 50%;
      animation: spin 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
      box-shadow: 0 4px 15px rgba(13, 110, 253, 0.2);
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .loading-title {
      font-size: 24px;
      font-weight: 700;
      color: #111827;
      margin-bottom: 12px;
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .loading-message {
      font-size: 16px;
      color: #4b5563;
      margin-bottom: 25px;
      line-height: 1.4;
    }

    /* PROGRESS BAR */
    .progress-container {
      width: 100%;
      margin: 0 auto 20px;
      padding: 12px;
      background: rgba(255, 255, 255, 0.9);
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .progress-bar-wrapper {
      width: 100%;
      height: 12px;
      background: #e5e7eb;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
      position: relative;
    }

    .progress-bar-fill {
      height: 100%;
      background: linear-gradient(90deg, #0d6efd 0%, #0a58ca 100%);
      border-radius: 8px;
      width: 0%;
      transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 2px 8px rgba(13, 110, 253, 0.4);
      position: relative;
      overflow: hidden;
    }

    .progress-bar-fill::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(
        90deg,
        rgba(255, 255, 255, 0) 0%,
        rgba(255, 255, 255, 0.3) 50%,
        rgba(255, 255, 255, 0) 100%
      );
      animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
      0% { transform: translateX(-100%); }
      100% { transform: translateX(100%); }
    }

    .progress-percentage {
      font-size: 20px;
      font-weight: 700;
      color: #0d6efd;
      margin-top: 10px;
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .progress-status {
      font-size: 14px;
      color: #4b5563;
      margin-top: 6px;
      font-style: italic;
      font-weight: 500;
    }

    /* GENERATION STAGES */
    .generation-stages {
      margin-top: 20px;
      text-align: left;
    }

    .stage-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 8px;
      border-radius: 6px;
      margin-bottom: 6px;
      background: #f9fafb;
      transition: all 0.3s ease;
    }

    .stage-item.active {
      background: #e0e7ff;
      border-left: 3px solid #0d6efd;
    }

    .stage-item.completed {
      background: #d1fae5;
      border-left: 3px solid #10b981;
    }

    .stage-icon {
      font-size: 16px;
      width: 24px;
      text-align: center;
    }

    .stage-icon.pending {
      color: #9ca3af;
    }

    .stage-icon.active {
      color: #0d6efd;
      animation: pulse 1.5s ease-in-out infinite;
    }

    .stage-icon.completed {
      color: #10b981;
    }

    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }

    .stage-text {
      flex: 1;
      font-size: 13px;
      font-weight: 500;
      color: #374151;
    }

    .upload-drop {
      border: 2px dashed rgba(13,110,253,0.18);
      border-radius: 8px;
      padding: 14px;
      display:flex;
      align-items:center;
      gap:12px;
      cursor:pointer;
      transition: all .15s;
      background: linear-gradient(180deg, rgba(13,110,253,0.03), rgba(13,110,253,0.01));
    }
    .upload-drop.dragover {
      background: rgba(13,110,253,0.06);
      transform: translateY(-2px);
      border-color: rgba(13,110,253,0.6);
    }

    .muted { color: var(--muted); font-size: .95rem; }
    .small { font-size:.88rem; }

    .status-bar {
      display:flex; gap:1rem; align-items:center; justify-content:space-between;
      padding:.5rem 1rem; background: #fff; border-radius:8px; box-shadow: 0 4px 10px rgba(2,6,23,0.04);
    }

    /* Product image display */
    .product-image-display {
      border: 2px solid rgba(13,110,253,0.3);
      border-radius: 8px;
      padding: 14px;
      background: #f8f9fa;
      text-align: center;
    }

    .product-image-display img {
      max-width: 100%;
      max-height: 200px;
      border-radius: 6px;
      margin-bottom: 10px;
    }

    .product-image-display .product-name {
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 5px;
    }

    .product-image-display .product-note {
      font-size: 0.85rem;
      color: #6b7280;
    }

    /* Tool button active state */
    .btn-tool-active {
      background-color: #0d6efd !important;
      color: white !important;
      border-color: #0d6efd !important;
      box-shadow: 0 0 10px rgba(13, 110, 253, 0.5) !important;
    }

    /* Tool buttons styling */
    .btn.btn-outline-secondary {
      background-color: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      transition: all 0.3s ease;
      border: 2px solid #6c757d;
    }

    .btn.btn-outline-secondary:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
      background-color: rgba(255, 255, 255, 1);
    }

    @media (max-width: 1100px) {
      .app-shell { flex-direction: column; padding: .75rem; }
      .left-col, .right-col { width: 100%; max-width: none; min-width: auto; }
      #threeViewport { height: 56vh; }
      
      .loading-content {
        padding: 20px;
        max-height: 85vh;
      }
      
      .loading-title {
        font-size: 20px;
      }
      
      .loading-spinner {
        width: 60px;
        height: 60px;
      }
    }
  </style>
</head>
<body>

<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between py-3">
    <h3 class="mb-0">3D Model Editor</h3>
    <div class="d-flex gap-2">
      <a href="inventory.php" class="btn btn-sm btn-outline-secondary">
        <i class="fa fa-arrow-left"></i> Back to Inventory
      </a>
    </div>
  </div>

  <div class="app-shell">
    <!-- LEFT -->
    <aside class="left-col">
      <div class="card-panel">
        <h6 class="mb-2">Import</h6>

        <?php if ($productData): ?>
          <!-- Show product image if coming from inventory -->
          <div class="product-image-display">
            <div class="product-name"><?= htmlspecialchars($productData['name']) ?></div>
            <img src="../<?= htmlspecialchars($productData['image_path']) ?>" alt="Product Image">
            <?php if (isset($productData['final_model_path']) && $productData['final_model_path']): ?>
              <div class="product-note" style="color: #28a745; font-weight: 600;">
                <i class="fa fa-check-circle"></i> 3D Model Available
              </div>
              <div class="product-note" style="font-size: 0.8rem;">
                Model will load automatically
              </div>
            <?php else: ?>
              <div class="product-note">This image will be used to generate the 3D model</div>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <!-- Show upload zone if no product selected -->
          <div id="uploadZone" class="upload-drop" tabindex="0">
            <div>
              <i class="fa fa-cloud-upload fa-2x text-primary"></i>
            </div>
            <div>
              <div class="small fw-semibold">Drag & drop images</div>
              <div class="muted small">Or click to select (jpg/png)</div>
            </div>
          </div>
          <input type="file" id="fileInput" accept="image/*" multiple class="d-none">
        <?php endif; ?>

        <div class="d-grid gap-2 mt-3">
          <button id="generateBtn" class="btn btn-primary btn-lg">
            <i class="fa fa-gear me-2"></i>Generate 3D Model
          </button>
          <button id="importGLBBtn" class="btn btn-outline-secondary">
            <i class="fa fa-file-import me-2"></i>Import .glb / .gltf
          </button>
          <button id="print3DBtn" class="btn btn-warning">
            <i class="fa fa-print me-2"></i>Prepare for 3D Print
          </button>
          <button id="downloadBtn" class="btn btn-success">
            <i class="fa fa-download me-2"></i>Export .gltf
          </button>
        </div>

        <hr class="my-3">

        <div class="small muted">
          <strong>Tips:</strong>
          <ul class="small mb-0">
            <li>Use clear photos from different angles.</li>
            <li>Prefer high-res images (avoid heavy compression).</li>
          </ul>
        </div>
      </div>
    </aside>

    <!-- CENTER -->
    <main class="canvas-col">
      <div class="card-panel" style="position: relative;">
        <!-- FIXED: CENTERED LOADING OVERLAY WITH FULL VISIBILITY -->
        <div id="loadingOverlay" class="loading-overlay">
          <div class="loading-content">
            <div class="loading-spinner"></div>
            <div class="loading-title">Generating 3D Model</div>
            <div class="loading-message">Please wait while we process your image...</div>
            
            <!-- Progress Bar -->
            <div class="progress-container">
              <div class="progress-bar-wrapper">
                <div id="progressBarFill" class="progress-bar-fill"></div>
              </div>
              <div id="progressPercentage" class="progress-percentage">0%</div>
              <div id="progressStatus" class="progress-status">Initializing...</div>
            </div>

            <!-- Generation Stages -->
            <div class="generation-stages">
              <div id="stage1" class="stage-item">
                <div class="stage-icon pending"><i class="fas fa-upload"></i></div>
                <div class="stage-text">Uploading Image</div>
              </div>
              <div id="stage2" class="stage-item">
                <div class="stage-icon pending"><i class="fas fa-brain"></i></div>
                <div class="stage-text">AI Processing</div>
              </div>
              <div id="stage3" class="stage-item">
                <div class="stage-icon pending"><i class="fas fa-cube"></i></div>
                <div class="stage-text">Generating Geometry</div>
              </div>
              <div id="stage4" class="stage-item">
                <div class="stage-icon pending"><i class="fas fa-paint-brush"></i></div>
                <div class="stage-text">Applying Textures</div>
              </div>
              <div id="stage5" class="stage-item">
                <div class="stage-icon pending"><i class="fas fa-check-circle"></i></div>
                <div class="stage-text">Finalizing Model</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tools Panel - Vertical Layout (Upper Left) -->
        <div style="position: absolute; top: 15px; left: 15px; z-index: 10; display: flex; flex-direction: column; gap: 8px;">
          <button id="selectTool" class="btn btn-outline-secondary btn-sm" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;" title="Select Tool - Navigate Camera">
            <i class="fa fa-mouse-pointer fa-lg"></i>
          </button>
          <button id="moveTool" class="btn btn-outline-secondary btn-sm" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;" title="Move Tool - Translate Model">
            <i class="fa fa-arrows fa-lg"></i>
          </button>
          <button id="rotateTool" class="btn btn-outline-secondary btn-sm" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;" title="Rotate Tool - Rotate Model">
            <i class="fa fa-rotate fa-lg"></i>
          </button>
          <button id="scaleTool" class="btn btn-outline-secondary btn-sm" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;" title="Scale Tool - Resize Model">
            <i class="fa fa-expand fa-lg"></i>
          </button>
          <button id="wireframeBtn" class="btn btn-outline-secondary btn-sm" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;" title="Toggle Wireframe">
            <i class="fa fa-border-all fa-lg"></i>
          </button>
        </div>
        
        <div id="threeViewport"></div>
      </div>

      <div class="status-bar mt-1">
        <div>
          <span id="statusText" class="muted small">Ready</span>
        </div>
        <div class="d-flex gap-2 align-items-center">
          <span id="modelInfo" class="small muted"></span>
          <button id="resetCameraBtn" class="btn btn-sm btn-outline-primary">Reset View</button>
        </div>
      </div>
    </main>

    <!-- RIGHT -->
    <aside class="right-col">
      <div class="card-panel">
        <h6>Transform</h6>

        <label class="small">Scale <span id="scaleVal" class="ms-2 small muted">1.00</span></label>
        <input id="scaleRange" type="range" min="0.05" max="3" step="0.01" value="1" class="form-range">

        <div class="row g-2 mt-2">
          <div class="col-6">
            <label class="small">Rotate X</label>
            <input id="rotateX" type="range" min="0" max="360" step="1" value="0" class="form-range">
          </div>
          <div class="col-6">
            <label class="small">Rotate Y</label>
            <input id="rotateY" type="range" min="0" max="360" step="1" value="0" class="form-range">
          </div>
          <div class="col-12 mt-2">
            <label class="small">Rotate Z</label>
            <input id="rotateZ" type="range" min="0" max="360" step="1" value="0" class="form-range">
          </div>
        </div>
      </div>

      <div class="card-panel">
        <h6>Material</h6>
        <label class="small">Color</label>
        <input id="colorPicker" type="color" value="#808080" class="form-control form-control-color p-1 mb-2">

        <label class="small">Metalness <span id="metalVal" class="ms-2 small muted">0.50</span></label>
        <input id="metalness" type="range" min="0" max="1" step="0.01" value="0.5" class="form-range">

        <label class="small mt-2">Roughness <span id="roughVal" class="ms-2 small muted">0.50</span></label>
        <input id="roughness" type="range" min="0" max="1" step="0.01" value="0.5" class="form-range">

        <label class="small mt-2">Texture</label>
        <input id="textureUpload" type="file" accept="image/*" class="form-control form-control-sm">
      </div>

      <div class="card-panel">
        <h6>Console</h6>
        <div id="consoleLog" style="height:160px; overflow:auto; background:#f8fafc; border-radius:6px; padding:8px; font-family:monospace; font-size:.85rem; color:#111;">
          <div id="consoleEmpty" class="muted small">No logs yet.</div>
        </div>
      </div>
    </aside>
  </div>
</div>

<script>
// Pass PHP data to JavaScript
const PRODUCT_ID = <?= $productId ? $productId : 'null' ?>;
const PRODUCT_IMAGE_PATH = <?= $productData ? json_encode($productData['image_path']) : 'null' ?>;
const PRODUCT_NAME = <?= $productData ? json_encode($productData['name']) : 'null' ?>;
const EXISTING_MODEL_PATH = <?= ($productData && isset($productData['final_model_path']) && $productData['final_model_path']) ? json_encode($productData['final_model_path']) : 'null' ?>;
const HAS_MODEL = <?= ($productData && isset($productData['final_model_path']) && $productData['final_model_path']) ? 'true' : 'false' ?>;
</script>

<script type="importmap">
{
  "imports": {
    "three": "https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js",
    "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/"
  }
}
</script>

<script type="module" src="../javascript/3dmodel_editor.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>