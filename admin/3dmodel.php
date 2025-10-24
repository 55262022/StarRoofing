<?php
include '../authentication/auth.php';
require_once '../database/starroofing_db.php';
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
      box-shadow: 0 6px 18px rgba(15,23,42,0.06);
      padding: 1rem;
    }

    #threeViewport {
      height: 72vh;
      border-radius: 10px;
      overflow: hidden;
      background: linear-gradient(180deg,#e9eef6 0,#cfdaf0 100%);
      border: 1px solid rgba(15,23,42,0.04);
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
    .prop-row { display:flex; gap:.5rem; align-items:center; }

    .status-bar {
      display:flex; gap:1rem; align-items:center; justify-content:space-between;
      padding:.5rem 1rem; background: #fff; border-radius:8px; box-shadow: 0 4px 10px rgba(2,6,23,0.04);
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
    <div>
      <a href="inventory.php" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
  </div>

  <div class="app-shell">
    <!-- LEFT -->
    <aside class="left-col">
      <div class="card-panel">
        <h6 class="mb-2">Import</h6>

        <div id="uploadZone" class="upload-drop" tabindex="0">
          <div>
            <i class="fa fa-cloud-upload fa-2x text-primary"></i>
          </div>
          <div>
            <div class="small fw-semibold">Drag & drop images</div>
            <div class="muted small">Or click to select (jpg/png) — 3+ images recommended</div>
          </div>
        </div>

        <input type="file" id="fileInput" accept="image/*" multiple class="d-none">

        <div class="d-grid gap-2 mt-3">
          <button id="generateBtn" class="btn btn-primary btn-lg"><i class="fa fa-gear me-2"></i>Generate 3D Model</button>
          <button id="importGLBBtn" class="btn btn-outline-secondary"><i class="fa fa-file-import me-2"></i>Import .glb / .gltf</button>
          <button id="downloadBtn" class="btn btn-success"><i class="fa fa-download me-2"></i>Export .gltf</button>
        </div>

        <hr class="my-3">

        <div class="small muted">
          <strong>Tips:</strong>
          <ul class="small mb-0">
            <li>Use clear photos from different angles.</li>
            <li>Prefer high-res images (avoid heavy compression).</li>
            <li>Meshy requires a subscription for task creation.</li>
          </ul>
        </div>
      </div>

      <div class="card-panel mt-2">
        <h6 class="mb-2">Tools</h6>
        <div class="d-flex gap-2 flex-wrap">
          <button id="selectTool" class="btn btn-outline-secondary btn-sm"><i class="fa fa-mouse-pointer"></i> Select</button>
          <button id="moveTool" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrows"></i> Move</button>
          <button id="rotateTool" class="btn btn-outline-secondary btn-sm"><i class="fa fa-rotate"></i> Rotate</button>
          <button id="scaleTool" class="btn btn-outline-secondary btn-sm"><i class="fa fa-expand"></i> Scale</button>
          <button id="wireframeBtn" class="btn btn-outline-secondary btn-sm"><i class="fa fa-border-all"></i> Wire</button>
        </div>
      </div>
    </aside>

    <!-- CENTER -->
    <main class="canvas-col">
      <div class="card-panel">
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
        <input id="colorPicker" type="color" value="#ffffff" class="form-control form-control-color p-1 mb-2">

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

<!-- IMPORTANT: Use importmap for Three.js modules -->
<script type="importmap">
{
  "imports": {
    "three": "https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js",
    "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/"
  }
}
</script>


<script type="module">
import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { GLTFExporter } from 'three/addons/exporters/GLTFExporter.js';

// Product ID no longer required
const productId = null;

// Utilities
const $ = (sel) => document.querySelector(sel);
const log = (msg) => {
  const c = $('#consoleLog');
  const empty = $('#consoleEmpty');
  if (empty) empty.remove();
  
  const div = document.createElement('div');
  div.textContent = `[${new Date().toLocaleTimeString()}] ${msg}`;
  c.insertBefore(div, c.firstChild);
};

// Global state
const state = {
  scene: null,
  camera: null,
  renderer: null,
  controls: null,
  loadedModel: null,
  textureLoader: null,
  uploadFiles: []
};

function updateStatus(txt, isError = false) {
  const statusEl = $('#statusText');
  statusEl.textContent = txt;
  statusEl.style.color = isError ? '#dc3545' : '';
  log(txt);
}

function initThree() {
  const container = $('#threeViewport');
  container.innerHTML = '';

  state.scene = new THREE.Scene();
  state.scene.background = new THREE.Color(0xeaeef6);

  const w = container.clientWidth;
  const h = container.clientHeight;
  
  state.camera = new THREE.PerspectiveCamera(60, w / h, 0.1, 1000);
  state.camera.position.set(2.5, 2.0, 3.5);

  state.renderer = new THREE.WebGLRenderer({ antialias: true });
  state.renderer.setPixelRatio(window.devicePixelRatio);
  state.renderer.setSize(w, h);
  state.renderer.outputColorSpace = THREE.SRGBColorSpace;
  container.appendChild(state.renderer.domElement);

  // Lighting
  const dirLight = new THREE.DirectionalLight(0xffffff, 1);
  dirLight.position.set(5, 10, 7.5);
  state.scene.add(dirLight);
  state.scene.add(new THREE.AmbientLight(0xffffff, 0.6));

  // Grid
  const grid = new THREE.GridHelper(10, 20, 0xbfc9df, 0xe9eef6);
  state.scene.add(grid);

  // Controls
  state.controls = new OrbitControls(state.camera, state.renderer.domElement);
  state.controls.enableDamping = true;
  state.controls.dampingFactor = 0.05;

  state.textureLoader = new THREE.TextureLoader();

  window.addEventListener('resize', onResize);
  animate();
  
  log('3D Editor initialized');
}

function onResize() {
  const container = $('#threeViewport');
  if (!container) return;
  
  const w = container.clientWidth;
  const h = container.clientHeight;
  
  state.camera.aspect = w / h;
  state.camera.updateProjectionMatrix();
  state.renderer.setSize(w, h);
}

function animate() {
  requestAnimationFrame(animate);
  if (state.controls) state.controls.update();
  if (state.renderer && state.scene && state.camera) {
    state.renderer.render(state.scene, state.camera);
  }
}

function clearModel() {
  if (!state.loadedModel) return;
  
  state.scene.remove(state.loadedModel);
  
  state.loadedModel.traverse(child => {
    if (child.geometry) child.geometry.dispose();
    if (child.material) {
      if (Array.isArray(child.material)) {
        child.material.forEach(m => m.dispose());
      } else {
        child.material.dispose();
      }
    }
  });
  
  state.loadedModel = null;
  $('#modelInfo').textContent = '';
}

async function loadModel(url) {
  updateStatus('Loading model...');
  
  try {
    clearModel();
    
    const loader = new GLTFLoader();
    
    await new Promise((resolve, reject) => {
      loader.load(
        url,
        (gltf) => {
          state.loadedModel = gltf.scene;
          
          // Center model
          const box = new THREE.Box3().setFromObject(state.loadedModel);
          const center = box.getCenter(new THREE.Vector3());
          state.loadedModel.position.sub(center);
          
          state.scene.add(state.loadedModel);
          
          const size = box.getSize(new THREE.Vector3());
          $('#modelInfo').textContent = 
            `Model: ${size.x.toFixed(2)} × ${size.y.toFixed(2)} × ${size.z.toFixed(2)} units`;
          
          updateStatus('Model loaded successfully');
          resolve();
        },
        (xhr) => {
          if (xhr.lengthComputable) {
            const percent = Math.round((xhr.loaded / xhr.total) * 100);
            updateStatus(`Loading: ${percent}%`);
          }
        },
        (error) => {
          reject(error);
        }
      );
    });
  } catch (err) {
    console.error('Load error:', err);
    updateStatus('Failed to load model', true);
    Swal.fire('Error', 'Unable to load model: ' + err.message, 'error');
  }
}

function exportModel() {
  if (!state.loadedModel) {
    Swal.fire('No Model', 'Please load or generate a model first', 'info');
    return;
  }
  
  updateStatus('Exporting model...');
  
  const exporter = new GLTFExporter();
  exporter.parse(
    state.loadedModel,
    (result) => {
      const output = result instanceof ArrayBuffer 
        ? result 
        : JSON.stringify(result, null, 2);
      
      const blob = new Blob([output], {
        type: result instanceof ArrayBuffer 
          ? 'model/gltf-binary' 
          : 'model/gltf+json'
      });
      
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = 'model.gltf';
      link.click();
      URL.revokeObjectURL(url);
      
      updateStatus('Export complete');
    },
    (error) => {
      console.error('Export error:', error);
      updateStatus('Export failed', true);
    },
    { binary: false }
  );
}

function applyMaterial() {
  if (!state.loadedModel) return;
  
  const color = $('#colorPicker').value;
  const metalness = parseFloat($('#metalness').value);
  const roughness = parseFloat($('#roughness').value);
  
  state.loadedModel.traverse(child => {
    if (child.isMesh && child.material) {
      if (!child.material.isMeshStandardMaterial) {
        child.material = new THREE.MeshStandardMaterial({
          map: child.material.map,
          color: child.material.color || color
        });
      }
      
      child.material.color.set(color);
      child.material.metalness = metalness;
      child.material.roughness = roughness;
      child.material.needsUpdate = true;
    }
  });
}

function handleFiles(files) {
  const images = files.filter(f => f.type.startsWith('image/'));
  
  if (images.length === 0) {
    Swal.fire('Invalid Files', 'Please select image files only', 'warning');
    return;
  }
  
  state.uploadFiles = state.uploadFiles.concat(images);
  updateStatus(`${state.uploadFiles.length} image(s) ready`);
  log(`Added ${images.length} image(s). Total: ${state.uploadFiles.length}`);
}

function setupUI() {
  const uploadZone = $('#uploadZone');
  const fileInput = $('#fileInput');
  
  // Drag & Drop
  uploadZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadZone.classList.add('dragover');
  });
  
  ['dragleave', 'dragend'].forEach(evt => {
    uploadZone.addEventListener(evt, () => {
      uploadZone.classList.remove('dragover');
    });
  });
  
  uploadZone.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    handleFiles(Array.from(e.dataTransfer.files));
  });
  
  uploadZone.addEventListener('click', () => fileInput.click());
  fileInput.addEventListener('change', (e) => {
    handleFiles(Array.from(e.target.files));
  });
  
  // Import GLB/GLTF
  $('#importGLBBtn').addEventListener('click', () => {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.gltf,.glb';
    input.onchange = (e) => {
      const file = e.target.files[0];
      if (file) {
        const url = URL.createObjectURL(file);
        loadModel(url);
      }
    };
    input.click();
  });
  
  // Generate 3D Model
  $('#generateBtn').addEventListener('click', async () => {
    if (state.uploadFiles.length === 0) {
      Swal.fire('No Images', 'Please add at least one image', 'warning');
      return;
    }

    Swal.fire({
      title: 'Generating 3D Model',
      html: 'Uploading image to Meshy AI...<br><small>This may take a few minutes</small>',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    updateStatus('Uploading to Meshy API...');

    const formData = new FormData();
    formData.append('images[]', state.uploadFiles[0]);

    try {
      const response = await fetch('meshy/meshy_upload.php', {
        method: 'POST',
        body: formData
      });
      
      if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);

      const text = await response.text();
      log('Server response: ' + text.substring(0, 500));
      const json = JSON.parse(text);

      if (json.status === 'success' && json.model_url) {
        Swal.fire({
          icon: 'success',
          title: 'Model Ready!',
          text: 'Loading 3D model...',
          timer: 2000,
          showConfirmButton: false
        });

        await loadModel(json.model_url);
        state.uploadFiles = [];
        updateStatus('Model loaded successfully');

      } else if (json.status === 'pending' && json.task_id) {
        Swal.fire({
          icon: 'info',
          title: 'Processing...',
          html: `Task ID: ${json.task_id}<br><br>Your model is being generated.<br>Checking status every 10 seconds...`,
          showConfirmButton: false,
          allowOutsideClick: false
        });

        updateStatus('Checking task status...');
        pollTaskStatus(json.task_id);

      } else {
        throw new Error(json.message || 'Unknown error from API');
      }

    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'Upload Failed',
        html: `<strong>Error:</strong> ${err.message}<br><br><small>Check console for details</small>`,
        confirmButtonText: 'OK'
      });

      updateStatus('Upload failed: ' + err.message, true);
      log('ERROR: ' + err.message);
    }
  });

  // Polling function
  async function pollTaskStatus(taskId) {
    const maxAttempts = 60;
    let attempts = 0;

    const checkStatus = async () => {
      attempts++;
      try {
        const response = await fetch(`meshy/meshy_check_status.php?task_id=${taskId}`);
        const json = await response.json();

        if (json.status === 'success' && json.model_url) {
          Swal.fire({
            icon: 'success',
            title: 'Model Generated!',
            text: 'Loading 3D model...',
            timer: 2000,
            showConfirmButton: false
          });

          await loadModel(json.model_url);
          updateStatus('Model loaded successfully');

        } else if (json.status === 'pending') {
          const progress = json.progress || 0;
          updateStatus(`Processing: ${progress}% (${attempts}/${maxAttempts})`);

          if (attempts < maxAttempts) {
            setTimeout(checkStatus, 10000);
          } else {
            throw new Error('Timeout: Model generation took too long');
          }

        } else {
          throw new Error(json.message || 'Model generation failed');
        }

      } catch (err) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: err.message
        });

        updateStatus('Error: ' + err.message, true);
      }
    };

    checkStatus();
  }
  
  // Export
  $('#downloadBtn').addEventListener('click', exportModel);
  
  // Transform controls
  $('#scaleRange').addEventListener('input', (e) => {
    const val = parseFloat(e.target.value);
    $('#scaleVal').textContent = val.toFixed(2);
    if (state.loadedModel) {
      state.loadedModel.scale.setScalar(val);
    }
  });
  
  ['rotateX', 'rotateY', 'rotateZ'].forEach(id => {
    $('#' + id).addEventListener('input', (e) => {
      if (!state.loadedModel) return;
      const rad = THREE.MathUtils.degToRad(parseFloat(e.target.value));
      const axis = id.replace('rotate', '').toLowerCase();
      state.loadedModel.rotation[axis] = rad;
    });
  });
  
  // Material controls
  $('#colorPicker').addEventListener('input', applyMaterial);
  $('#metalness').addEventListener('input', (e) => {
    $('#metalVal').textContent = parseFloat(e.target.value).toFixed(2);
    applyMaterial();
  });
  $('#roughness').addEventListener('input', (e) => {
    $('#roughVal').textContent = parseFloat(e.target.value).toFixed(2);
    applyMaterial();
  });
  
  // Texture
  $('#textureUpload').addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file || !state.loadedModel) return;
    
    const url = URL.createObjectURL(file);
    state.textureLoader.load(url, (texture) => {
      state.loadedModel.traverse(child => {
        if (child.isMesh && child.material) {
          child.material.map = texture;
          child.material.needsUpdate = true;
        }
      });
      log('Texture applied');
      URL.revokeObjectURL(url);
    });
  });
  
  // Wireframe
  $('#wireframeBtn').addEventListener('click', () => {
    if (!state.loadedModel) return;
    
    let isWireframe = false;
    state.loadedModel.traverse(child => {
      if (child.isMesh && child.material) {
        isWireframe = child.material.wireframe;
      }
    });
    
    state.loadedModel.traverse(child => {
      if (child.isMesh && child.material) {
        child.material.wireframe = !isWireframe;
      }
    });
    
    log('Wireframe: ' + (isWireframe ? 'OFF' : 'ON'));
  });
  
  // Reset camera
  $('#resetCameraBtn').addEventListener('click', () => {
    state.camera.position.set(2.5, 2.0, 3.5);
    state.controls.target.set(0, 0, 0);
    state.controls.update();
    log('Camera reset');
  });
}

// Initialize everything
initThree();
setupUI();
updateStatus('Ready');
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>