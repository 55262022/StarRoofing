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
    }
  </style>
</head>
<body>

<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between py-3">
    <h3 class="mb-0">3D Model Editor</h3>
    <div class="d-flex gap-2">
      <?php if ($productId): ?>
        <!-- Coming from inventory - show Back to Inventory button only -->
        <a href="inventory.php" class="btn btn-sm btn-outline-secondary">
          <i class="fa fa-arrow-left"></i> Back to Inventory
        </a>
      <?php else: ?>
        <!-- Accessed directly - show 3D Gallery button only -->
        <a href="3dmodel_gallery.php" class="btn btn-sm btn-outline-primary">
          <i class="fa fa-images"></i> 3D Gallery
        </a>
      <?php endif; ?>
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

<!-- 3D Print Settings Modal -->
<div class="modal fade" id="print3DModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="background: #ffffff; border-radius: 15px;">
      <div class="modal-header" style="border-bottom: 2px solid #f0f0f0;">
        <h5 class="modal-title" style="color: #111827; font-weight: 700;">
          <i class="fa fa-print me-2"></i>3D Print Settings
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="color: #111827;">
        <div class="row g-3">
          <!-- Print Material -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Print Material</label>
            <select id="printMaterial" class="form-select">
              <option value="pla">PLA (Polylactic Acid)</option>
              <option value="abs">ABS (Acrylonitrile Butadiene Styrene)</option>
              <option value="petg">PETG (Polyethylene Terephthalate Glycol)</option>
              <option value="tpu">TPU (Thermoplastic Polyurethane)</option>
              <option value="nylon">Nylon</option>
              <option value="resin">Resin</option>
            </select>
            <small class="text-muted">Select the material for 3D printing</small>
          </div>

          <!-- Print Quality -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Print Quality</label>
            <select id="printQuality" class="form-select">
              <option value="draft">Draft (0.3mm layer height)</option>
              <option value="normal" selected>Normal (0.2mm layer height)</option>
              <option value="fine">Fine (0.1mm layer height)</option>
              <option value="ultra">Ultra Fine (0.05mm layer height)</option>
            </select>
            <small class="text-muted">Higher quality = longer print time</small>
          </div>

          <!-- Infill Density -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Infill Density: <span id="infillValue">20%</span></label>
            <input type="range" class="form-range" id="printInfill" min="0" max="100" step="5" value="20">
            <small class="text-muted">Higher infill = stronger but heavier</small>
          </div>

          <!-- Support Structure -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Support Structure</label>
            <select id="printSupport" class="form-select">
              <option value="none">None</option>
              <option value="touching" selected>Touching Build Plate</option>
              <option value="everywhere">Everywhere</option>
            </select>
            <small class="text-muted">Supports for overhanging parts</small>
          </div>

          <!-- Print Scale -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Scale (%): <span id="printScaleValue">100%</span></label>
            <input type="range" class="form-range" id="printScale" min="10" max="500" step="10" value="100">
            <small class="text-muted">Adjust model size for printing</small>
          </div>

          <!-- Print Orientation -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Print Orientation</label>
            <select id="printOrientation" class="form-select">
              <option value="auto" selected>Auto (Optimal)</option>
              <option value="flat">Flat (XY Plane)</option>
              <option value="side">Side (XZ Plane)</option>
              <option value="upright">Upright (YZ Plane)</option>
            </select>
            <small class="text-muted">Best orientation for printing</small>
          </div>

          <!-- Estimated Print Time -->
          <div class="col-12">
            <div class="alert alert-info mb-0">
              <h6 class="mb-2"><i class="fa fa-clock me-2"></i>Estimated Print Time</h6>
              <div id="estimatedPrintTime" class="fw-bold" style="font-size: 1.1rem;">Calculating...</div>
              <small class="text-muted d-block mt-1">Based on selected settings</small>
            </div>
          </div>

          <!-- Material Cost -->
          <div class="col-12">
            <div class="alert alert-success mb-0">
              <h6 class="mb-2"><i class="fa fa-coins me-2"></i>Estimated Material Cost</h6>
              <div id="estimatedCost" class="fw-bold" style="font-size: 1.1rem;">₱0.00</div>
              <small class="text-muted d-block mt-1">Approximate cost based on material and infill</small>
            </div>
          </div>

          <!-- Additional Options -->
          <div class="col-12">
            <label class="form-label fw-semibold">Additional Options</label>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="printRaft" checked>
              <label class="form-check-label" for="printRaft">
                Add Raft (Build Plate Adhesion)
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="printBrim">
              <label class="form-check-label" for="printBrim">
                Add Brim (Edge Adhesion)
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="hollowModel">
              <label class="form-check-label" for="hollowModel">
                Hollow Model (Save Material)
              </label>
            </div>
          </div>

          <!-- Notes -->
          <div class="col-12">
            <label class="form-label fw-semibold">Print Notes (Optional)</label>
            <textarea id="printNotes" class="form-control" rows="3" placeholder="Add any special instructions or notes for the 3D print..."></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer" style="border-top: 2px solid #f0f0f0;">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fa fa-times me-2"></i>Cancel
        </button>
        <button type="button" id="exportSTLBtn" class="btn btn-primary">
          <i class="fa fa-file-export me-2"></i>Export STL for Printing
        </button>
        <button type="button" id="sendToPrinterBtn" class="btn btn-success">
          <i class="fa fa-print me-2"></i>Send to 3D Printer
        </button>
      </div>
    </div>
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
<script src="../javascript/print3d_handler.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>