<?php
require_once '../database/starroofing_db.php';
require_once '../authentication/auth.php';

// Fetch category IDs
$categoryMap = [];
$sql = "SELECT category_id, category_code FROM categories WHERE category_code IN ('roofing','upvc','door')";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categoryMap[$row['category_code']] = $row['category_id'];
    }
}

// API for fetching products
if (isset($_GET['action']) && $_GET['action'] === 'getProducts' && isset($_GET['category'])) {
    $catCode = $_GET['category'];
    if (!isset($categoryMap[$catCode])) {
        echo json_encode(['success'=>false, 'data'=>[], 'message'=>'Category not found']);
        exit;
    }
    $categoryId = $categoryMap[$catCode];
    // Fixed: Changed model_url to model_path
    $stmt = $conn->prepare("SELECT name, image_path, model_path FROM products WHERE category_id=? AND is_archived=0");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $res = $stmt->get_result();
    $products = [];
    while ($row = $res->fetch_assoc()) {
        $products[] = $row;
    }
    echo json_encode(['success'=>true, 'data'=>$products]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>3D Design Workspace - Star Roofing</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Montserrat', sans-serif;
    }

    body, html {
      height: 100%;
      width: 100%;
      overflow: hidden;
      background-color: #0a0a0a;
      color: #fff;
    }

    header {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      background: rgba(26, 26, 46, 0.95);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(233, 185, 73, 0.2);
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
      padding: 15px 30px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      z-index: 100;
    }

    .logo-section {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .logo-icon {
      font-size: 1.8rem;
    }

    header h1 {
      font-size: 1.5rem;
      font-weight: 700;
      background: linear-gradient(to right, #ffffff, #e9b949);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      letter-spacing: 1px;
    }

    .header-controls {
      display: flex;
      gap: 12px;
      align-items: center;
    }

    .btn {
      border: none;
      border-radius: 50px;
      color: #fff;
      font-weight: 600;
      padding: 10px 20px;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 0.85rem;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn-primary { 
      background: #e9b949;
      color: #1a1a2e;
      border: 2px solid #e9b949;
    }
    
    .btn-primary:hover { 
      background: transparent;
      color: #e9b949;
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(233, 185, 73, 0.3);
    }

    .btn-secondary { 
      background: rgba(255, 255, 255, 0.05);
      border: 2px solid rgba(255, 255, 255, 0.2);
    }
    
    .btn-secondary:hover { 
      background: rgba(255, 255, 255, 0.1);
      border-color: rgba(233, 185, 73, 0.5);
      transform: translateY(-2px);
    }

    .btn-success { 
      background: rgba(34, 197, 94, 0.2);
      border: 2px solid rgba(34, 197, 94, 0.5);
      color: #22c55e;
    }
    
    .btn-success:hover { 
      background: rgba(34, 197, 94, 0.3);
      border-color: #22c55e;
      transform: translateY(-2px);
    }

    .btn-danger { 
      background: rgba(239, 68, 68, 0.2);
      border: 2px solid rgba(239, 68, 68, 0.5);
      color: #ef4444;
    }
    
    .btn-danger:hover { 
      background: rgba(239, 68, 68, 0.3);
      border-color: #ef4444;
      transform: translateY(-2px);
    }

    main {
      height: 100vh;
      width: 100%;
      padding-top: 80px;
      background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 100%);
      position: relative;
    }

    .control-panel {
      position: fixed;
      left: 30px;
      top: 120px;
      background: rgba(26, 26, 46, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(233, 185, 73, 0.2);
      border-radius: 20px;
      padding: 20px;
      width: 280px;
      max-height: calc(100vh - 200px);
      overflow-y: auto;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
      z-index: 50;
    }

    .control-panel h3 {
      color: #e9b949;
      font-size: 1rem;
      font-weight: 700;
      margin-bottom: 15px;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .control-section {
      margin-bottom: 25px;
      padding-bottom: 20px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .control-section:last-child {
      border-bottom: none;
    }

    .control-label {
      color: rgba(255, 255, 255, 0.7);
      font-size: 0.8rem;
      font-weight: 600;
      margin-bottom: 8px;
      display: block;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .control-input {
      width: 100%;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 10px;
      color: white;
      padding: 10px 15px;
      font-size: 0.9rem;
      transition: all 0.3s ease;
    }

    .control-input:focus {
      outline: none;
      background: rgba(255, 255, 255, 0.08);
      border-color: rgba(233, 185, 73, 0.5);
      box-shadow: 0 0 0 3px rgba(233, 185, 73, 0.1);
    }

    .control-btn {
      width: 100%;
      margin-top: 10px;
    }

    #threeCanvas {
      width: 100%;
      height: calc(100vh - 80px);
      display: block;
      background: radial-gradient(circle at 50% 50%, #1a1a2e 0%, #0a0a0a 100%);
    }

    .info-badge {
      position: fixed;
      bottom: 30px;
      right: 30px;
      background: rgba(26, 26, 46, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(233, 185, 73, 0.2);
      border-radius: 15px;
      padding: 15px 20px;
      color: rgba(255, 255, 255, 0.8);
      font-size: 0.85rem;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
      z-index: 50;
    }

    .info-badge strong {
      color: #e9b949;
      display: block;
      margin-bottom: 5px;
    }

    .swal2-popup {
      background: #1a1a2e !important;
      border: 1px solid rgba(233, 185, 73, 0.3) !important;
      border-radius: 20px !important;
    }

    .swal2-title {
      color: #e9b949 !important;
      font-family: 'Montserrat', sans-serif !important;
    }

    .swal2-html-container {
      color: rgba(255, 255, 255, 0.8) !important;
    }

    .swal2-confirm {
      background: #e9b949 !important;
      color: #1a1a2e !important;
      border-radius: 50px !important;
      padding: 10px 30px !important;
      font-weight: 600 !important;
    }

    .swal2-cancel {
      background: rgba(255, 255, 255, 0.1) !important;
      border: 2px solid rgba(255, 255, 255, 0.2) !important;
      border-radius: 50px !important;
      padding: 10px 30px !important;
    }

    .design-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 15px;
      max-height: 500px;
      overflow-y: auto;
      padding: 10px;
    }

    .design-card {
      cursor: pointer;
      border-radius: 12px;
      overflow: hidden;
      background: rgba(255, 255, 255, 0.05);
      border: 2px solid rgba(255, 255, 255, 0.1);
      transition: all 0.3s ease;
    }

    .design-card:hover {
      transform: translateY(-5px);
      border-color: rgba(233, 185, 73, 0.5);
      box-shadow: 0 10px 30px rgba(233, 185, 73, 0.2);
    }

    .design-card img {
      width: 100%;
      height: 140px;
      object-fit: cover;
    }

    .design-card p {
      text-align: center;
      padding: 12px;
      font-size: 0.85rem;
      font-weight: 600;
      color: rgba(255, 255, 255, 0.9);
    }

    .loading-ring {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid rgba(233, 185, 73, 0.3);
      border-radius: 50%;
      border-top-color: #e9b949;
      animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    @media (max-width: 768px) {
      header {
        padding: 12px 15px;
        flex-wrap: wrap;
        gap: 10px;
      }

      header h1 {
        font-size: 1.2rem;
      }

      .header-controls {
        width: 100%;
        justify-content: space-between;
      }

      .btn {
        padding: 8px 15px;
        font-size: 0.75rem;
      }

      .control-panel {
        left: 15px;
        top: 100px;
        width: calc(100% - 30px);
        max-width: 320px;
      }

      .info-badge {
        bottom: 15px;
        right: 15px;
        left: 15px;
        font-size: 0.75rem;
      }
    }

    ::-webkit-scrollbar {
      width: 8px;
      height: 8px;
    }

    ::-webkit-scrollbar-track {
      background: rgba(255, 255, 255, 0.05);
      border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
      background: rgba(233, 185, 73, 0.5);
      border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: rgba(233, 185, 73, 0.7);
    }
  </style>
</head>

<body>
  <header>
    <div class="logo-section">
      <span class="logo-icon">🏠</span>
      <h1>3D Design Workspace</h1>
    </div>

    <div class="header-controls">
      <button id="btnLoadModel" class="btn btn-primary">Load Model</button>
      <button id="btnUploadModel" class="btn btn-secondary">Upload</button>
      <button id="btnSaveDesign" class="btn btn-success">Save</button>
      <button id="btnResetScene" class="btn btn-danger">Reset</button>
    </div>
  </header>

  <main>
    <aside class="control-panel">
      <div class="control-section">
        <h3>Scene Controls</h3>
        <label class="control-label">Camera Position</label>
        <input type="range" class="control-input" id="cameraDistance" min="3" max="15" value="5" step="0.5">
      </div>

      <div class="control-section">
        <h3>Lighting</h3>
        <label class="control-label">Ambient Intensity</label>
        <input type="range" class="control-input" id="ambientLight" min="0" max="2" value="0.6" step="0.1">
        
        <label class="control-label" style="margin-top: 10px;">Directional Intensity</label>
        <input type="range" class="control-input" id="directionalLight" min="0" max="2" value="0.8" step="0.1">
      </div>

      <div class="control-section">
        <h3>Quick Actions</h3>
        <button class="btn btn-secondary control-btn" id="btnToggleGrid">Toggle Grid</button>
        <button class="btn btn-secondary control-btn" id="btnCenterCamera">Center Camera</button>
      </div>
    </aside>

    <canvas id="threeCanvas"></canvas>

    <div class="info-badge">
      <strong>Tip:</strong> Click on roof, windows, doors, or walls to customize them with different designs!
    </div>
  </main>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>

<script>
// Three.js setup
const canvas = document.getElementById('threeCanvas');
const scene = new THREE.Scene();
scene.background = new THREE.Color(0x0a0a0a);

const camera = new THREE.PerspectiveCamera(75, window.innerWidth / (window.innerHeight - 70), 0.1, 1000);
camera.position.set(0, 2, 5);

const renderer = new THREE.WebGLRenderer({ canvas, antialias: true });
renderer.setSize(window.innerWidth, window.innerHeight - 70);

const controls = new THREE.OrbitControls(camera, renderer.domElement);
controls.enableDamping = true;

const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
directionalLight.position.set(5, 10, 7.5);
scene.add(ambientLight, directionalLight);

const groundGeometry = new THREE.PlaneGeometry(20, 20);
const groundMaterial = new THREE.MeshStandardMaterial({ color: 0x90ee90 });
const ground = new THREE.Mesh(groundGeometry, groundMaterial);
ground.rotation.x = -Math.PI / 2;
ground.position.y = -0.5;
scene.add(ground);

const loader = new THREE.GLTFLoader();
let house = null;
let groupedMeshes = {};
let replacedParts = {};

// Load House Model
function loadHouseModel() {
  const houseModels = [
    { name: "Modern House", url: "../assets/models/house.glb", thumbnail: "../assets/images/house_modern.jpg" },
    { name: "Classic House", url: "../assets/models/house_classic.glb", thumbnail: "../assets/images/house_classic.jpg" },
    { name: "Villa House", url: "../assets/models/house_villa.glb", thumbnail: "../assets/images/house_villa.jpg" }
  ];

  const html = houseModels.map(h => `
    <div class="design-card" data-url="${h.url}">
      <img src="${h.thumbnail}" alt="${h.name}" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22%3E%3Crect fill=%22%23ddd%22 width=%22100%22 height=%22100%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3ENo Image%3C/text%3E%3C/svg%3E'">
      <p>${h.name}</p>
    </div>
  `).join('');

  Swal.fire({
    title: 'Select a House Model',
    html: `<div class="design-grid">${html}</div>`,
    showConfirmButton: false,
    showCloseButton: true,
    width: 800,
    didOpen: () => {
      document.querySelectorAll('.design-card').forEach(card => {
        card.addEventListener('click', () => {
          loadSelectedHouse(card.getAttribute('data-url'));
          Swal.close();
        });
      });
    }
  });
}

// Load selected house
function loadSelectedHouse(url) {
  Swal.fire({ title: 'Loading 3D Model...', didOpen: () => Swal.showLoading(), allowOutsideClick: false });

  loader.load(url, (gltf) => {
    if (house) scene.remove(house);
    house = gltf.scene;
    scene.add(house);
    groupedMeshes = groupMeshesByBaseName(house);
    replacedParts = {};

    console.log("%c=== MODEL PART NAMES ===", "color: #e9b949; font-weight: bold;");
    for (const key in groupedMeshes) {
      console.log(`🧩 ${key}:`, groupedMeshes[key].map(m => {
        const hasUV = m.geometry.attributes.uv ? '✓' : '✗';
        return `${m.name} [UV:${hasUV}]`;
      }));
    }
    console.log("%c========================", "color: #e9b949; font-weight: bold;");

    Swal.close();
    Swal.fire('Loaded!', 'The house model has been loaded.', 'success');
  }, undefined, (error) => {
    Swal.close();
    Swal.fire('Error', 'Failed to load model: ' + error.message, 'error');
  });
}

// Upload custom model
function uploadModel() {
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = '.glb,.gltf';
  input.click();

  input.onchange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const url = URL.createObjectURL(file);

    Swal.fire({ title: 'Loading Your Model...', didOpen: () => Swal.showLoading(), allowOutsideClick: false });

    loader.load(url, (gltf) => {
      if (house) scene.remove(house);
      house = gltf.scene;
      scene.add(house);
      groupedMeshes = groupMeshesByBaseName(house);
      replacedParts = {};

      console.log("%c=== MODEL PART NAMES ===", "color: #e9b949; font-weight: bold;");
      for (const key in groupedMeshes) {
        console.log(`🧩 ${key}:`, groupedMeshes[key].map(m => {
          const hasUV = m.geometry.attributes.uv ? '✓' : '✗';
          return `${m.name} [UV:${hasUV}]`;
        }));
      }
      console.log("%c========================", "color: #e9b949; font-weight: bold;");

      URL.revokeObjectURL(url);
      Swal.close();
      Swal.fire('Model Loaded', 'Your 3D model has been uploaded!', 'success');
    }, undefined, (err) => {
      URL.revokeObjectURL(url);
      Swal.close();
      Swal.fire('Error', 'Failed to load uploaded model.', 'error');
    });
  };
}

// Group meshes by base name
function groupMeshesByBaseName(root) {
  const groups = {};
  root.traverse(node => {
    if (node.isMesh) {
      const baseName = node.name.split('_')[0].toLowerCase();
      if (!groups[baseName]) groups[baseName] = [];
      groups[baseName].push(node);
    }
  });
  return groups;
}

// Calculate bounding box for meshes
function calculateBoundingBox(meshes) {
  const box = new THREE.Box3();
  meshes.forEach(mesh => {
    const meshBox = new THREE.Box3().setFromObject(mesh);
    box.union(meshBox);
  });
  return box;
}

// Raycasting & Selection
const raycaster = new THREE.Raycaster();
const mouse = new THREE.Vector2();

canvas.addEventListener('click', async (event) => {
  const rect = canvas.getBoundingClientRect();
  mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
  mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

  raycaster.setFromCamera(mouse, camera);
  const intersects = raycaster.intersectObjects(scene.children, true);

  if (intersects.length > 0) {
    const clicked = intersects[0].object;
    const baseName = clicked.name.split('_')[0].toLowerCase();
    const meshes = groupedMeshes[baseName] || [clicked];

    let categoryCode = null;
    let partName = null;

    if (baseName.includes("roof")) { categoryCode = 'roofing'; partName = "Roof"; }
    else if (baseName.includes("window")) { categoryCode = 'upvc'; partName = "Window"; }
    else if (baseName.includes("door")) { categoryCode = 'door'; partName = "Door"; }
    else if (baseName.includes("wall")) { partName = "Wall"; }

    if (!partName) return;

    if (partName === "Wall") {
      Swal.fire({
        title: 'Select Wall Color',
        html: `<input type="color" id="wallColorPicker" value="#ffffff" style="width:100%; height:50px; border:none;">`,
        showCancelButton: true,
        confirmButtonText: 'Apply',
        preConfirm: () => document.getElementById('wallColorPicker').value
      }).then(result => {
        if (result.isConfirmed) {
          const color = new THREE.Color(result.value);
          meshes.forEach(mesh => {
            if (mesh.material) {
              mesh.material.color.set(color);
              mesh.material.needsUpdate = true;
            }
          });
          Swal.fire('Applied!', 'The wall color has been updated.', 'success');
        }
      });
    } else {
      try {
        const res = await fetch(`workspace.php?action=getProducts&category=${categoryCode}`);
        const data = await res.json();
        if (!data.success || !data.data || data.data.length === 0) {
          return Swal.fire('No Designs', 'No designs found for this category.', 'warning');
        }
        openPartSelector(partName, baseName, meshes, data.data);
      } catch (err) {
        Swal.fire('Error', 'Failed to load designs.', 'error');
        console.error(err);
      }
    }
  }
});

// Open design selector
function openPartSelector(partName, baseName, meshes, designs) {
  const html = designs.map(d => {
    const hasModel = d.model_path && d.model_path.trim() !== '';
    const hasTexture = d.image_path && d.image_path.trim() !== '';
    
    return `
      <div class="design-card" 
           data-model="${hasModel ? '../' + d.model_path : ''}"
           data-texture="${hasTexture ? '../' + d.image_path : ''}"
           data-has-model="${hasModel}"
           data-has-texture="${hasTexture}">
        <img src="${hasTexture ? '../' + d.image_path : ''}" 
             alt="${d.name}" 
             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22%3E%3Crect fill=%22%23ddd%22 width=%22100%22 height=%22100%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3ENo Image%3C/text%3E%3C/svg%3E'">
        <p>${d.name}</p>
        ${hasModel ? '<span style="color:#e9b949;font-size:0.7rem;">🔷 3D Model</span>' : '<span style="color:#6b7280;font-size:0.7rem;">🖼️ Texture</span>'}
      </div>
    `;
  }).join('');

  Swal.fire({
    title: `Select ${partName} Design`,
    html: `<div class="design-grid">${html}</div>`,
    showConfirmButton: false,
    showCloseButton: true,
    width: 800,
    didOpen: () => {
      document.querySelectorAll('.design-card').forEach(card => {
        card.addEventListener('click', () => {
          const modelURL = card.getAttribute('data-model');
          const textureURL = card.getAttribute('data-texture');
          const hasModel = card.getAttribute('data-has-model') === 'true';
          const hasTexture = card.getAttribute('data-has-texture') === 'true';
          
          if (partName === "Roof" && hasModel && modelURL) {
            // For roofs with 3D models, place actual models
            placeModelsOnRoof(meshes, modelURL, partName);
          } else if (hasModel && modelURL) {
            // For other parts, convert to texture
            convertModelToTexture(modelURL, (texture) => {
              if (texture) {
                applyTextureToPart(meshes, texture, partName, true);
              }
            });
          } else if (hasTexture && textureURL) {
            applyTextureToPart(meshes, textureURL, partName, false);
          } else {
            Swal.fire('No Design', 'This product has no 3D model or texture available.', 'warning');
          }
          Swal.close();
        });
      });
    }
  });
}

// Convert 3D model to texture - SINGLE UNIFIED VERSION
function convertModelToTexture(modelURL, callback) {
  let isPopupOpen = true;
  
  Swal.fire({ 
    title: 'Converting 3D Model to Texture...', 
    html: '<p>Loading 3D model...</p>',
    didOpen: () => Swal.showLoading(), 
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false
  });

  loader.load(modelURL, (gltf) => {
    try {
      const model = gltf.scene;

      if (Swal.isVisible()) {
        Swal.update({ html: '<p>Processing model geometry...</p>' });
      }

      const renderScene = new THREE.Scene();
      renderScene.background = new THREE.Color(0xffffff);

      // Enhanced lighting for corrugated metal appearance
      const ambLight = new THREE.AmbientLight(0xffffff, 0.4);
      const dirLight1 = new THREE.DirectionalLight(0xffffff, 1.2);
      const dirLight2 = new THREE.DirectionalLight(0xffffff, 0.6);
      const dirLight3 = new THREE.DirectionalLight(0xffffff, 0.4);
      
      dirLight1.position.set(15, 5, 0);
      dirLight2.position.set(-10, 8, 5);
      dirLight3.position.set(0, 3, -10);
      
      renderScene.add(ambLight, dirLight1, dirLight2, dirLight3);
      renderScene.add(model);

      const box = new THREE.Box3().setFromObject(model);
      const center = new THREE.Vector3();
      const size = new THREE.Vector3();
      box.getCenter(center);
      box.getSize(size);

      model.position.sub(center);

      const maxDim = Math.max(size.x, size.y, size.z);
      if (maxDim > 10) {
        const scale = 5 / maxDim;
        model.scale.multiplyScalar(scale);
      }

      // Angled view to capture corrugation ridges
      const renderCamera = new THREE.OrthographicCamera(
        -maxDim * 1.5, maxDim * 1.5,
        maxDim * 1.5, -maxDim * 1.5,
        0.1, 1000
      );
      
      renderCamera.position.set(maxDim * 2, maxDim * 2, maxDim * 2);
      renderCamera.lookAt(0, 0, 0);

      const textureSize = 2048;
      const renderTarget = new THREE.WebGLRenderTarget(textureSize, textureSize, {
        minFilter: THREE.LinearFilter,
        magFilter: THREE.LinearFilter,
        format: THREE.RGBAFormat,
        type: THREE.UnsignedByteType,
        generateMipmaps: true
      });

      if (Swal.isVisible()) {
        Swal.update({ html: '<p>Rendering 3D model to texture...</p>' });
      }

      setTimeout(() => {
        try {
          const currentRenderTarget = renderer.getRenderTarget();
          const currentViewport = new THREE.Vector4();
          renderer.getViewport(currentViewport);
          
          renderer.setRenderTarget(renderTarget);
          renderer.setViewport(0, 0, textureSize, textureSize);
          renderer.clear();
          renderer.render(renderScene, renderCamera);

          const pixels = new Uint8Array(textureSize * textureSize * 4);
          renderer.readRenderTargetPixels(renderTarget, 0, 0, textureSize, textureSize, pixels);

          renderer.setRenderTarget(currentRenderTarget);
          renderer.setViewport(currentViewport);

          const canvas = document.createElement('canvas');
          canvas.width = textureSize;
          canvas.height = textureSize;
          const ctx = canvas.getContext('2d');
          const imageData = ctx.createImageData(textureSize, textureSize);
          
          for (let y = 0; y < textureSize; y++) {
            for (let x = 0; x < textureSize; x++) {
              const srcIdx = (y * textureSize + x) * 4;
              const dstIdx = ((textureSize - 1 - y) * textureSize + x) * 4;
              imageData.data[dstIdx + 0] = pixels[srcIdx + 0];
              imageData.data[dstIdx + 1] = pixels[srcIdx + 1];
              imageData.data[dstIdx + 2] = pixels[srcIdx + 2];
              imageData.data[dstIdx + 3] = pixels[srcIdx + 3];
            }
          }
          
          ctx.putImageData(imageData, 0, 0);

          const texture = new THREE.CanvasTexture(canvas);
          texture.wrapS = THREE.RepeatWrapping;
          texture.wrapT = THREE.RepeatWrapping;
          texture.minFilter = THREE.LinearMipMapLinearFilter;
          texture.magFilter = THREE.LinearFilter;
          texture.anisotropy = renderer.capabilities.getMaxAnisotropy();
          texture.generateMipmaps = true;
          texture.needsUpdate = true;

          console.log('%c✅ 3D Model converted to texture successfully', 'color: #22c55e; font-weight: bold;');
          console.log(`   Resolution: ${textureSize}x${textureSize}px`);

          renderScene.remove(model);
          renderTarget.dispose();
          
          if (Swal.isVisible()) {
            Swal.close();
          }
          
          callback(texture);
          
        } catch (renderError) {
          console.error('Rendering error:', renderError);
          if (Swal.isVisible()) {
            Swal.close();
          }
          Swal.fire('Error', 'Failed to render texture: ' + renderError.message, 'error');
          callback(null);
        }
      }, 150);

    } catch (error) {
      console.error('Error during conversion:', error);
      if (Swal.isVisible()) {
        Swal.close();
      }
      Swal.fire('Error', 'Failed to convert 3D model: ' + error.message, 'error');
      callback(null);
    }

  }, 
  (progress) => {
    if (progress.lengthComputable && Swal.isVisible()) {
      const percentComplete = (progress.loaded / progress.total) * 100;
      Swal.update({ 
        html: `<p>Loading 3D model... ${Math.round(percentComplete)}%</p>` 
      });
    }
  }, 
  (error) => {
    if (Swal.isVisible()) {
      Swal.close();
    }
    console.error('Model loading error:', error);
    Swal.fire('Error', 'Failed to load 3D model: ' + error.message, 'error');
    callback(null);
  });
}

// Place 3D models on roof surface (for corrugated sheets, tiles, etc.)
// Advanced roof surface analysis and model placement
function placeModelsOnRoof(roofMeshes, modelURL, partName) {
  Swal.fire({ 
    title: 'Analyzing Roof Surface...', 
    html: '<p>Calculating roof geometry and placement...</p>',
    didOpen: () => Swal.showLoading(), 
    allowOutsideClick: false 
  });

  loader.load(modelURL, (gltf) => {
    try {
      const templateModel = gltf.scene;
      
      // Remove existing roof models
      const existingGroup = scene.getObjectByName('roof_models_group');
      if (existingGroup) {
        scene.remove(existingGroup);
        existingGroup.traverse(obj => {
          if (obj.geometry) obj.geometry.dispose();
          if (obj.material) {
            if (Array.isArray(obj.material)) {
              obj.material.forEach(mat => mat.dispose());
            } else {
              obj.material.dispose();
            }
          }
        });
      }
      
      console.log('%c═══════════════════════════════════════', 'color: #e9b949; font-weight: bold');
      console.log('%c🏠 ROOF ANALYSIS STARTED', 'color: #e9b949; font-weight: bold; font-size: 14px');
      console.log('%c═══════════════════════════════════════', 'color: #e9b949; font-weight: bold');
      
      // STEP 1: Analyze roof geometry
      const roofAnalysis = analyzeRoofGeometry(roofMeshes);
      console.log('%c📐 Roof Dimensions:', 'color: #3b82f6; font-weight: bold');
      console.log(`   Width (X): ${roofAnalysis.dimensions.x.toFixed(3)}m`);
      console.log(`   Height (Y): ${roofAnalysis.dimensions.y.toFixed(3)}m`);
      console.log(`   Depth (Z): ${roofAnalysis.dimensions.z.toFixed(3)}m`);
      console.log(`   Angle: ${roofAnalysis.angle.toFixed(2)}° from horizontal`);
      console.log(`   Normal: (${roofAnalysis.normal.x.toFixed(3)}, ${roofAnalysis.normal.y.toFixed(3)}, ${roofAnalysis.normal.z.toFixed(3)})`);
      
      // STEP 2: Calculate model scale
      const modelBox = new THREE.Box3().setFromObject(templateModel);
      const originalModelSize = new THREE.Vector3();
      modelBox.getSize(originalModelSize);
      
      console.log('%c🔷 Original Model Size:', 'color: #8b5cf6; font-weight: bold');
      console.log(`   ${originalModelSize.x.toFixed(3)} × ${originalModelSize.y.toFixed(3)} × ${originalModelSize.z.toFixed(3)}`);
      
      // Calculate optimal scale based on roof surface area
      const roofSurfaceWidth = Math.max(roofAnalysis.dimensions.x, roofAnalysis.dimensions.z);
      const targetTileCount = 10; // Aim for 10 tiles across the width
      const targetTileSize = roofSurfaceWidth / targetTileCount;
      const modelMaxDim = Math.max(originalModelSize.x, originalModelSize.z);
      const scaleFactor = targetTileSize / modelMaxDim;
      
      templateModel.scale.set(scaleFactor, scaleFactor, scaleFactor);
      templateModel.updateMatrixWorld(true);
      
      const scaledModelBox = new THREE.Box3().setFromObject(templateModel);
      const modelSize = new THREE.Vector3();
      scaledModelBox.getSize(modelSize);
      
      console.log('%c✨ Scaled Model Size:', 'color: #22c55e; font-weight: bold');
      console.log(`   ${modelSize.x.toFixed(3)} × ${modelSize.y.toFixed(3)} × ${modelSize.z.toFixed(3)}`);
      console.log(`   Scale Factor: ${(scaleFactor * 100).toFixed(2)}%`);
      
      // STEP 3: Calculate tile grid
      const tileSpacingX = modelSize.x * 0.99; // 1% overlap
      const tileSpacingZ = modelSize.z * 0.99;
      
      // Adjust grid based on roof orientation
      const isWideRoof = roofAnalysis.dimensions.x > roofAnalysis.dimensions.z;
      const tilesX = Math.ceil((isWideRoof ? roofAnalysis.dimensions.x : roofAnalysis.dimensions.z) / tileSpacingX);
      const tilesZ = Math.ceil((isWideRoof ? roofAnalysis.dimensions.z : roofAnalysis.dimensions.x) / tileSpacingZ);
      
      console.log('%c📊 Tile Grid Layout:', 'color: #f59e0b; font-weight: bold');
      console.log(`   Grid: ${tilesX} × ${tilesZ} = ${tilesX * tilesZ} tiles`);
      console.log(`   Spacing: ${tileSpacingX.toFixed(3)}m × ${tileSpacingZ.toFixed(3)}m`);
      console.log(`   Orientation: ${isWideRoof ? 'Wide (X-dominant)' : 'Deep (Z-dominant)'}`);
      
      // STEP 4: Create placement group
      const roofGroup = new THREE.Group();
      roofGroup.name = 'roof_models_group';
      
      const roofCenter = new THREE.Vector3();
      roofAnalysis.boundingBox.getCenter(roofCenter);
      
      // Calculate rotation quaternion from roof normal
      const upVector = new THREE.Vector3(0, 1, 0);
      const rotationQuaternion = new THREE.Quaternion().setFromUnitVectors(upVector, roofAnalysis.normal);
      
      let placedCount = 0;
      const placementData = [];
      
      // STEP 5: Use advanced surface mapping for precise placement
      console.log('%c🎯 PRECISE TILE PLACEMENT', 'color: #ec4899; font-weight: bold; font-size: 12px');
      
      // Tag roof faces with plane indices
      roofAnalysis.roofPlanes.forEach((plane, planeIdx) => {
        plane.faces.forEach(face => {
          face.planeIndex = planeIdx;
        });
      });
      
      // Place tiles on each roof plane separately
      roofAnalysis.roofPlanes.forEach((plane, planeIdx) => {
        console.log(`   🏔️ Processing Plane ${planeIdx + 1}/${roofAnalysis.roofPlanes.length}`);
        
        const planeBBox = plane.boundingBox;
        const planeSize = new THREE.Vector3();
        planeBBox.getSize(planeSize);
        
        // Calculate tile grid for this plane
        const planeTilesX = Math.ceil(planeSize.x / tileSpacingX);
        const planeTilesZ = Math.ceil(planeSize.z / tileSpacingZ);
        
        console.log(`      Grid: ${planeTilesX} × ${planeTilesZ} tiles`);
        
        // Place tiles on this plane
        for (let ix = 0; ix < planeTilesX; ix++) {
          for (let iz = 0; iz < planeTilesZ; iz++) {
            const modelClone = templateModel.clone();
            
            // Calculate position within plane bounds
            const localX = planeBBox.min.x + (ix + 0.5) * tileSpacingX;
            const localZ = planeBBox.min.z + (iz + 0.5) * tileSpacingZ;
            
            // Find exact surface point using ray-triangle intersection
            const rayOrigin = new THREE.Vector3(localX, planeBBox.max.y + 10, localZ);
            const surfacePoint = findClosestRoofPoint(rayOrigin, plane.faces);
            
            if (surfacePoint) {
              // Position model on surface
              modelClone.position.copy(surfacePoint.position);
              
              // Align model to surface normal using quaternion
              const upVector = new THREE.Vector3(0, 1, 0);
              const modelRotation = new THREE.Quaternion().setFromUnitVectors(
                upVector,
                surfacePoint.normal
              );
              
              modelClone.quaternion.copy(modelRotation);
              
              // Calculate proper vertical offset based on model's local bounds
              const modelLocalBBox = new THREE.Box3().setFromObject(modelClone);
              const modelLocalSize = new THREE.Vector3();
              modelLocalBBox.getSize(modelLocalSize);
              
              // Offset slightly above surface
              const offset = surfacePoint.normal.clone().multiplyScalar(modelLocalSize.y * 0.02);
              modelClone.position.add(offset);
              
              roofGroup.add(modelClone);
              placedCount++;
              
              placementData.push({
                position: surfacePoint.position.clone(),
                normal: surfacePoint.normal.clone(),
                gridCoords: { x: ix, z: iz },
                planeIndex: planeIdx
              });
            } else {
              console.warn(`      ⚠️ No surface found at grid position (${ix}, ${iz})`);
            }
          }
        }
        
        console.log(`      ✅ Placed ${placedCount} tiles on plane ${planeIdx + 1}`);
      });
      
      scene.add(roofGroup);
      
      console.log('%c═══════════════════════════════════════', 'color: #22c55e; font-weight: bold');
      console.log(`%c✅ PLACEMENT COMPLETE: ${placedCount}/${tilesX * tilesZ} tiles`, 'color: #22c55e; font-weight: bold; font-size: 14px');
      console.log('%c═══════════════════════════════════════', 'color: #22c55e; font-weight: bold');
      
      // Store metadata
      if (!replacedParts['roof']) replacedParts['roof'] = [];
      replacedParts['roof'].push({
        type: '3d_models',
        modelURL: modelURL,
        groupName: 'roof_models_group',
        count: placedCount,
        grid: { x: tilesX, z: tilesZ },
        analysis: roofAnalysis,
        scaleFactor: scaleFactor,
        placementData: placementData
      });
      
      Swal.close();
      Swal.fire({
        icon: 'success',
        title: 'Roof Models Placed Successfully!',
        html: `
          <div style="text-align: left; padding: 10px;">
            <strong>📊 Placement Summary:</strong><br>
            • Tiles Placed: <strong>${placedCount}</strong><br>
            • Grid Layout: <strong>${tilesX} × ${tilesZ}</strong><br>
            • Roof Angle: <strong>${roofAnalysis.angle.toFixed(1)}°</strong><br>
            • Scale Factor: <strong>${(scaleFactor * 100).toFixed(1)}%</strong><br>
            • Coverage: <strong>${((placedCount / (tilesX * tilesZ)) * 100).toFixed(1)}%</strong>
          </div>
        `,
        timer: 4000,
        showConfirmButton: true,
        confirmButtonText: 'OK'
      });
      
    } catch (error) {
      console.error('%c❌ ERROR:', 'color: #ef4444; font-weight: bold', error);
      Swal.close();
      Swal.fire('Error', 'Failed to place roof models: ' + error.message, 'error');
    }
  }, 
  (progress) => {
    if (progress.lengthComputable && Swal.isVisible()) {
      const percent = (progress.loaded / progress.total) * 100;
      Swal.update({ html: `<p>Loading model... ${Math.round(percent)}%</p>` });
    }
  },
  (error) => {
    console.error('Model loading error:', error);
    Swal.close();
    Swal.fire('Error', 'Failed to load roof model: ' + error.message, 'error');
  });
}

// Advanced roof geometry analysis with surface mapping
function analyzeRoofGeometry(roofMeshes) {
  const boundingBox = calculateBoundingBox(roofMeshes);
  const dimensions = new THREE.Vector3();
  boundingBox.getSize(dimensions);
  
  console.log('%c🔬 DETAILED ROOF SURFACE ANALYSIS', 'color: #8b5cf6; font-weight: bold; font-size: 12px');
  
  // Collect all vertices and normals from roof surfaces
  const surfacePoints = [];
  const surfaceNormals = [];
  const roofFaces = [];
  
  roofMeshes.forEach(mesh => {
    if (mesh.geometry) {
      mesh.updateMatrixWorld(true);
      const geometry = mesh.geometry;
      const positions = geometry.attributes.position;
      const normals = geometry.attributes.normal;
      
      if (positions && normals) {
        // Extract all face data
        const vertexCount = positions.count;
        
        for (let i = 0; i < vertexCount; i += 3) {
          // Get triangle vertices
          const v1 = new THREE.Vector3(
            positions.getX(i),
            positions.getY(i),
            positions.getZ(i)
          ).applyMatrix4(mesh.matrixWorld);
          
          const v2 = new THREE.Vector3(
            positions.getX(i + 1),
            positions.getY(i + 1),
            positions.getZ(i + 1)
          ).applyMatrix4(mesh.matrixWorld);
          
          const v3 = new THREE.Vector3(
            positions.getX(i + 2),
            positions.getY(i + 2),
            positions.getZ(i + 2)
          ).applyMatrix4(mesh.matrixWorld);
          
          // Get face normal
          const n1 = new THREE.Vector3(
            normals.getX(i),
            normals.getY(i),
            normals.getZ(i)
          ).transformDirection(mesh.matrixWorld).normalize();
          
          // Calculate face center
          const center = new THREE.Vector3()
            .add(v1).add(v2).add(v3)
            .divideScalar(3);
          
          // Calculate face area
          const edge1 = new THREE.Vector3().subVectors(v2, v1);
          const edge2 = new THREE.Vector3().subVectors(v3, v1);
          const area = edge1.cross(edge2).length() / 2;
          
          // Only include upward-facing surfaces (roof tops)
          if (n1.y > 0.3) { // Filter for roof surfaces
            roofFaces.push({
              vertices: [v1, v2, v3],
              center: center,
              normal: n1,
              area: area,
              mesh: mesh
            });
            
            surfacePoints.push(center);
            surfaceNormals.push(n1);
          }
        }
      }
    }
  });
  
  console.log(`   📍 Detected ${roofFaces.length} roof surface faces`);
  console.log(`   📊 Surface points: ${surfacePoints.length}`);
  
  // Calculate weighted average normal (by face area)
  let totalArea = 0;
  let weightedNormal = new THREE.Vector3(0, 0, 0);
  
  roofFaces.forEach(face => {
    weightedNormal.add(face.normal.clone().multiplyScalar(face.area));
    totalArea += face.area;
  });
  
  if (totalArea > 0) {
    weightedNormal.divideScalar(totalArea).normalize();
  } else {
    weightedNormal.set(0, 1, 0);
  }
  
  // Detect roof planes (for multi-slope roofs)
  const roofPlanes = detectRoofPlanes(roofFaces);
  
  console.log(`   🏔️ Detected ${roofPlanes.length} roof plane(s)`);
  roofPlanes.forEach((plane, idx) => {
    const angle = Math.acos(plane.normal.y) * (180 / Math.PI);
    console.log(`      Plane ${idx + 1}: Angle ${angle.toFixed(2)}°, Area ${plane.totalArea.toFixed(3)}m², Faces: ${plane.faces.length}`);
  });
  
  // Calculate overall roof angle
  const angle = Math.acos(weightedNormal.y) * (180 / Math.PI);
  
  // Find min/max heights for each plane
  const planeHeights = roofPlanes.map(plane => {
    const heights = plane.faces.flatMap(face => 
      face.vertices.map(v => v.y)
    );
    return {
      min: Math.min(...heights),
      max: Math.max(...heights),
      avg: heights.reduce((a, b) => a + b, 0) / heights.length
    };
  });
  
  return {
    boundingBox: boundingBox,
    dimensions: dimensions,
    normal: weightedNormal,
    angle: angle,
    isSloped: angle > 5,
    surfacePoints: surfacePoints,
    surfaceNormals: surfaceNormals,
    roofFaces: roofFaces,
    roofPlanes: roofPlanes,
    planeHeights: planeHeights,
    totalArea: totalArea
  };
}

// Detect distinct roof planes (for gabled, hipped, or complex roofs)
function detectRoofPlanes(faces) {
  if (faces.length === 0) return [];
  
  const planes = [];
  const normalThreshold = 0.95; // Cosine similarity threshold (about 18 degrees)
  const usedFaces = new Set();
  
  faces.forEach((face, faceIdx) => {
    if (usedFaces.has(faceIdx)) return;
    
    // Start a new plane
    const plane = {
      normal: face.normal.clone(),
      faces: [face],
      totalArea: face.area,
      center: face.center.clone()
    };
    
    usedFaces.add(faceIdx);
    
    // Find all faces with similar normals
    faces.forEach((otherFace, otherIdx) => {
      if (usedFaces.has(otherIdx)) return;
      
      const similarity = face.normal.dot(otherFace.normal);
      
      if (similarity > normalThreshold) {
        plane.faces.push(otherFace);
        plane.totalArea += otherFace.area;
        usedFaces.add(otherIdx);
        
        // Update weighted normal
        plane.normal.add(otherFace.normal.clone().multiplyScalar(otherFace.area));
      }
    });
    
    // Normalize the plane normal
    plane.normal.normalize();
    
    // Calculate plane bounding box
    const planeBox = new THREE.Box3();
    plane.faces.forEach(face => {
      face.vertices.forEach(v => planeBox.expandByPoint(v));
    });
    plane.boundingBox = planeBox;
    
    // Calculate plane center (weighted by area)
    plane.center.set(0, 0, 0);
    plane.faces.forEach(face => {
      plane.center.add(face.center.clone().multiplyScalar(face.area));
    });
    plane.center.divideScalar(plane.totalArea);
    
    planes.push(plane);
  });
  
  // Sort planes by area (largest first)
  planes.sort((a, b) => b.totalArea - a.totalArea);
  
  return planes;
}

// Create a surface sampling grid for precise tile placement
function createRoofSurfaceGrid(roofAnalysis, tileSize) {
  const gridPoints = [];
  const bbox = roofAnalysis.boundingBox;
  const gridResolution = Math.max(tileSize.x, tileSize.z) * 0.5; // Half tile size for precision
  
  console.log('%c🗺️ CREATING SURFACE GRID', 'color: #10b981; font-weight: bold; font-size: 12px');
  console.log(`   Grid resolution: ${gridResolution.toFixed(3)}m`);
  
  // Create a 2D grid over the bounding box
  const startX = bbox.min.x;
  const endX = bbox.max.x;
  const startZ = bbox.min.z;
  const endZ = bbox.max.z;
  
  const stepsX = Math.ceil((endX - startX) / gridResolution);
  const stepsZ = Math.ceil((endZ - startZ) / gridResolution);
  
  console.log(`   Grid dimensions: ${stepsX} × ${stepsZ} = ${stepsX * stepsZ} sample points`);
  
  // Sample each grid point
  for (let ix = 0; ix <= stepsX; ix++) {
    for (let iz = 0; iz <= stepsZ; iz++) {
      const x = startX + (ix / stepsX) * (endX - startX);
      const z = startZ + (iz / stepsZ) * (endZ - startZ);
      
      // Cast ray down to find roof surface
      const rayOrigin = new THREE.Vector3(x, bbox.max.y + 10, z);
      const closestPoint = findClosestRoofPoint(rayOrigin, roofAnalysis.roofFaces);
      
      if (closestPoint) {
        gridPoints.push({
          position: closestPoint.position,
          normal: closestPoint.normal,
          gridCoords: { x: ix, z: iz },
          planeIndex: closestPoint.planeIndex
        });
      }
    }
  }
  
  console.log(`   ✅ Generated ${gridPoints.length} valid surface points`);
  
  return gridPoints;
}

// Find closest roof surface point using raycasting
function findClosestRoofPoint(rayOrigin, roofFaces) {
  const rayDirection = new THREE.Vector3(0, -1, 0);
  let closestDistance = Infinity;
  let closestPoint = null;
  
  roofFaces.forEach(face => {
    // Ray-triangle intersection
    const intersection = rayIntersectsTriangle(
      rayOrigin,
      rayDirection,
      face.vertices[0],
      face.vertices[1],
      face.vertices[2]
    );
    
    if (intersection) {
      const distance = rayOrigin.distanceTo(intersection);
      if (distance < closestDistance) {
        closestDistance = distance;
        closestPoint = {
          position: intersection,
          normal: face.normal.clone(),
          planeIndex: face.planeIndex || 0
        };
      }
    }
  });
  
  return closestPoint;
}

// Ray-triangle intersection (Möller–Trumbore algorithm)
function rayIntersectsTriangle(rayOrigin, rayDirection, v0, v1, v2) {
  const EPSILON = 0.0000001;
  const edge1 = new THREE.Vector3().subVectors(v1, v0);
  const edge2 = new THREE.Vector3().subVectors(v2, v0);
  const h = new THREE.Vector3().crossVectors(rayDirection, edge2);
  const a = edge1.dot(h);
  
  if (a > -EPSILON && a < EPSILON) return null; // Ray parallel to triangle
  
  const f = 1.0 / a;
  const s = new THREE.Vector3().subVectors(rayOrigin, v0);
  const u = f * s.dot(h);
  
  if (u < 0.0 || u > 1.0) return null;
  
  const q = new THREE.Vector3().crossVectors(s, edge1);
  const v = f * rayDirection.dot(q);
  
  if (v < 0.0 || u + v > 1.0) return null;
  
  const t = f * edge2.dot(q);
  
  if (t > EPSILON) {
    return new THREE.Vector3().addVectors(
      rayOrigin,
      rayDirection.clone().multiplyScalar(t)
    );
  }
  
  return null;
}

// Apply texture to part
function applyTextureToPart(meshes, textureSource, partName, isGeneratedTexture = false) {
  Swal.fire({ 
    title: `Applying ${partName} Design...`, 
    didOpen: () => Swal.showLoading(), 
    allowOutsideClick: false 
  });

  if (isGeneratedTexture && textureSource instanceof THREE.Texture) {
    applyTextureToMeshes(meshes, textureSource, partName);
    return;
  }

  if (!textureSource || textureSource.trim() === '') {
    Swal.close();
    return Swal.fire('No Texture', 'This design has no texture image.', 'warning');
  }

  const texLoader = new THREE.TextureLoader();
  texLoader.load(textureSource, (texture) => {
    applyTextureToMeshes(meshes, texture, partName);
  }, 
  undefined, 
  (error) => {
    Swal.close();
    console.error('Texture loading error:', error);
    Swal.fire('Error', 'Failed to apply texture.', 'error');
  });
}

// Apply texture to meshes with dynamic scaling
function applyTextureToMeshes(meshes, texture, partName) {
  texture.wrapS = THREE.RepeatWrapping;
  texture.wrapT = THREE.RepeatWrapping;

  const boundingBox = calculateBoundingBox(meshes);
  const size = new THREE.Vector3();
  boundingBox.getSize(size);

  let repeatX, repeatY;
  
  if (partName === "Roof") {
    repeatX = Math.max(1, Math.ceil(size.x / 16));
    repeatY = Math.max(1, Math.ceil(size.z / 16));
    console.log(`Roof size: ${size.x.toFixed(2)} x ${size.z.toFixed(2)}`);
    console.log(`Applying texture repeat: ${repeatX} x ${repeatY}`);
  } else if (partName === "Window" || partName === "Door") {
    repeatX = 1;
    repeatY = 1;
  } else if (partName === "Wall") {
    repeatX = Math.max(1, Math.ceil(size.x / 2));
    repeatY = Math.max(1, Math.ceil(size.y / 2));
  } else {
    repeatX = 2;
    repeatY = 2;
  }

  texture.repeat.set(repeatX, repeatY);

  meshes.forEach(mesh => {
    if (mesh.material) {
      mesh.material = mesh.material.clone();
      mesh.material.map = texture;
      mesh.material.needsUpdate = true;
      
      if (mesh.material.color) {
        mesh.material.color.set(0xffffff);
      }

      mesh.material.side = THREE.DoubleSide;
      
      if (!mesh.geometry.attributes.uv) {
        console.warn(`Mesh ${mesh.name} has no UV coordinates, attempting to generate basic UVs`);
        generateBasicUVs(mesh.geometry);
      }
    }
  });

  console.log(`✅ Applied texture to ${meshes.length} ${partName} meshes with ${repeatX}x${repeatY} tiling`);

  Swal.close();
  Swal.fire({
    icon: 'success',
    title: 'Applied!',
    text: `${partName} design has been applied successfully.`,
    timer: 2000,
    showConfirmButton: false
  });
}

// Generate basic UV coordinates
function generateBasicUVs(geometry) {
  const pos = geometry.attributes.position;
  const uvs = [];
  
  geometry.computeBoundingBox();
  const bbox = geometry.boundingBox;
  const sizeX = bbox.max.x - bbox.min.x;
  const sizeY = bbox.max.y - bbox.min.y;
  const sizeZ = bbox.max.z - bbox.min.z;
  
  console.log(`Generating UVs for geometry with size: ${sizeX.toFixed(2)} x ${sizeY.toFixed(2)} x ${sizeZ.toFixed(2)}`);
  
  const isHorizontal = sizeX > sizeY && sizeZ > sizeY;
  const isVerticalXY = sizeX > sizeZ && sizeY > sizeZ;
  
  for (let i = 0; i < pos.count; i++) {
    const x = pos.getX(i);
    const y = pos.getY(i);
    const z = pos.getZ(i);
    
    let u, v;
    
    if (isHorizontal) {
      u = (x - bbox.min.x) / sizeX;
      v = (z - bbox.min.z) / sizeZ;
    } else if (isVerticalXY) {
      u = (x - bbox.min.x) / sizeX;
      v = (y - bbox.min.y) / sizeY;
    } else {
      u = (y - bbox.min.y) / sizeY;
      v = (z - bbox.min.z) / sizeZ;
    }
    
    uvs.push(u, v);
  }
  
  geometry.setAttribute('uv', new THREE.Float32BufferAttribute(uvs, 2));
  console.log(`✅ Generated ${uvs.length / 2} UV coordinates (${isHorizontal ? 'horizontal/roof' : isVerticalXY ? 'vertical/wall' : 'other'} mapping)`);
}

// Reset scene
function resetScene() {
  Swal.fire({
    title: 'Reset Scene?',
    text: 'This will remove all models and customizations.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, Reset',
    cancelButtonText: 'Cancel'
  }).then(result => {
    if (result.isConfirmed) {
      if (house) {
        scene.remove(house);
        house = null;
      }
      
      groupedMeshes = {};
      replacedParts = {};
      
      Swal.fire('Scene Reset', 'All models have been cleared.', 'info');
    }
  });
}

// Save design
function saveDesign() {
  if (!house) {
    return Swal.fire('Nothing to Save', 'Please load a house model first.', 'warning');
  }

  Swal.fire({
    title: 'Save Design',
    input: 'text',
    inputLabel: 'Design Name',
    inputPlaceholder: 'Enter a name for your design',
    showCancelButton: true,
    confirmButtonText: 'Save',
    inputValidator: (value) => !value && 'Please enter a design name!'
  }).then(result => {
    if (result.isConfirmed) {
      const designData = {
        name: result.value,
        appliedTextures: replacedParts,
        timestamp: new Date().toISOString()
      };
      
      console.log('Design to save:', designData);
      
      Swal.fire({
        icon: 'success',
        title: 'Saved!',
        text: `Design "${result.value}" has been saved.`,
        timer: 2000,
        showConfirmButton: false
      });
    }
  });
}

// Event Listeners
window.addEventListener('resize', () => {
  camera.aspect = window.innerWidth / (window.innerHeight - 70);
  camera.updateProjectionMatrix();
  renderer.setSize(window.innerWidth, window.innerHeight - 70);
});

document.getElementById('btnLoadModel').addEventListener('click', loadHouseModel);
document.getElementById('btnUploadModel').addEventListener('click', uploadModel);
document.getElementById('btnSaveDesign').addEventListener('click', saveDesign);
document.getElementById('btnResetScene').addEventListener('click', resetScene);

document.getElementById('cameraDistance').addEventListener('input', (e) => {
  const distance = parseFloat(e.target.value);
  controls.maxDistance = distance;
  controls.minDistance = distance * 0.5;
});

document.getElementById('ambientLight').addEventListener('input', (e) => {
  ambientLight.intensity = parseFloat(e.target.value);
});

document.getElementById('directionalLight').addEventListener('input', (e) => {
  directionalLight.intensity = parseFloat(e.target.value);
});

document.getElementById('btnToggleGrid').addEventListener('click', () => {
  const gridHelper = scene.getObjectByName('gridHelper');
  if (!gridHelper) {
    const newGrid = new THREE.GridHelper(20, 20, 0xe9b949, 0x444444);
    newGrid.name = 'gridHelper';
    scene.add(newGrid);
  } else {
    gridHelper.visible = !gridHelper.visible;
  }
});

document.getElementById('btnCenterCamera').addEventListener('click', () => {
  controls.reset();
  camera.position.set(0, 2, 5);
});

// Animation loop
function animate() {
  requestAnimationFrame(animate);
  controls.update();
  renderer.render(scene, camera);
}
animate();

// Log startup info
console.log('%c🏠 Star Roofing 3D Workspace Ready!', 'color: #e9b949; font-size: 16px; font-weight: bold;');
console.log('%cClick on house parts to customize them with inventory designs', 'color: #fff; font-size: 12px;');
</script>

</body>
</html>