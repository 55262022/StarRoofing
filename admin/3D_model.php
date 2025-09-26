<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Construction 3D Configurator</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7f9; }
        .main-layout { display: flex; min-height: 100vh; }
        .sidebar {
            width: 340px;
            background: #1a365d;
            color: #fff;
            padding: 24px 18px 24px 18px;
            box-shadow: 2px 0 16px rgba(26,54,93,0.08);
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .sidebar h2 { font-size: 1.3rem; margin-bottom: 12px; }
        .materials-list { display: flex; flex-direction: column; gap: 12px; }
        .material-item {
            background: #223d6b;
            border-radius: 8px;
            padding: 10px 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .material-item.selected, .material-item:hover { background: #e9b949; color: #1a365d; }
        .material-icon { font-size: 1.5rem; width: 32px; text-align: center; }
        .material-info { flex: 1; }
        .material-title { font-weight: 600; }
        .material-cost { font-size: 0.95em; color: #ffd700; }
        .add-btn {
            background: #fff;
            color: #1a365d;
            border: none;
            border-radius: 5px;
            padding: 6px 14px;
            font-weight: 600;
            cursor: pointer;
            margin-left: 8px;
            transition: background 0.2s;
        }
        .add-btn:hover { background: #e9b949; color: #1a365d; }
        .bill-section {
            background: #fff;
            color: #1a365d;
            border-radius: 10px;
            padding: 16px;
            margin-top: 18px;
            box-shadow: 0 2px 8px rgba(26,54,93,0.08);
        }
        .bill-section h3 { margin: 0 0 10px 0; font-size: 1.1rem; }
        .bill-table { width: 100%; border-collapse: collapse; }
        .bill-table th, .bill-table td { padding: 6px 8px; }
        .bill-table th { background: #f5f7f9; }
        .bill-table td { background: #fff; }
        .bill-table .remove-btn {
            color: #e74c3c; background: none; border: none; cursor: pointer; font-size: 1.1rem;
        }
        .bill-total { text-align: right; font-weight: 700; color: #e9b949; margin-top: 10px; }
        .viewer-container { flex: 1; display: flex; flex-direction: column; }
        .viewer-header {
            background: #fff;
            padding: 18px 32px;
            border-bottom: 1px solid #e9b949;
            font-size: 1.3rem;
            font-weight: 700;
            color: #1a365d;
            letter-spacing: 1px;
        }
        #model-viewer { flex: 1; width: 100%; min-height: 600px; background: #eaeaea; position:relative; }
        .controls-bar {
            background: #fff;
            padding: 10px 24px;
            border-bottom: 1px solid #e9b949;
            display: flex;
            gap: 18px;
            align-items: center;
        }
        .controls-bar label { font-weight: 500; margin-right: 6px; }
        .controls-bar select, .controls-bar input[type="range"] {
            margin-right: 18px;
        }
        .material-select {
            display: flex; gap: 10px; align-items: center;
        }
        .material-swatch {
            width: 28px; height: 28px; border-radius: 6px; border: 2px solid #fff; cursor: pointer;
            display: inline-block; margin-right: 4px;
        }
        .material-swatch.selected { border: 2px solid #e9b949; box-shadow: 0 0 0 2px #e9b949; }
        .selected-element-info {
            position: absolute; top: 20px; right: 30px; background: #fff; color: #1a365d;
            border-radius: 8px; padding: 10px 18px; box-shadow: 0 2px 8px rgba(26,54,93,0.08);
            z-index: 10; min-width: 180px; display: none;
        }
        .selected-element-info.active { display: block; }
        .selected-element-info button {
            background: #e74c3c; color: #fff; border: none; border-radius: 4px; padding: 4px 10px;
            font-size: 0.95em; margin-top: 8px; cursor: pointer;
        }
        .selected-element-info .toggle-move {
            background: #1a365d; color: #fff; border: none; border-radius: 4px; padding: 6px 10px;
            margin-top: 8px; cursor: pointer;
        }
        .selected-element-info .toggle-move.active {
            background: #e9b949; color: #1a365d;
        }
        @media (max-width: 900px) {
            .main-layout { flex-direction: column; }
            .sidebar { width: 100%; }
            #model-viewer { min-height: 350px; }
        }
        .move-btns {
            margin-top: 10px;
            display: flex;
            gap: 6px;
        }
        .move-btns button {
            background: #1a365d;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 1em;
            cursor: pointer;
            transition: background 0.2s;
        }
        .move-btns button:hover {
            background: #e9b949;
            color: #1a365d;
        }
    </style>
</head>
<body>
<div class="main-layout">
    <!-- Sidebar: Materials & Bill of Materials -->
    <div class="sidebar">
        <h2><i class="fa fa-cubes"></i> Construction Materials</h2>
        <div class="materials-list" id="materials-list">
            <!-- JS will populate materials here -->
        </div>
        <div class="bill-section">
            <h3><i class="fa fa-file-invoice-dollar"></i> Bill of Materials</h3>
            <table class="bill-table" id="bill-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Qty</th>
                        <th>Cost</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <!-- JS will populate bill here -->
                </tbody>
            </table>
            <div class="bill-total" id="bill-total">Total: ₱0.00</div>
        </div>
    </div>
    <!-- 3D Viewer -->
    <div class="viewer-container">
        <div class="viewer-header">
            <i class="fa fa-hard-hat"></i> 3D Construction Model Configurator
        </div>
        <div class="controls-bar">
            <label>Roof Type:
                <select id="roof-type">
                    <option value="gable">Gable</option>
                    <option value="hip">Hip</option>
                    <option value="flat">Flat</option>
                    <option value="shed">Shed</option>
                </select>
            </label>
            <label>Pitch:
                <input type="range" id="roof-pitch" min="0" max="60" step="1" value="30">
                <span id="pitch-value">30°</span>
            </label>
            <label>Width:
                <input type="range" id="roof-width" min="5" max="20" step="1" value="10">
                <span id="width-value">10m</span>
            </label>
            <label>Length:
                <input type="range" id="roof-length" min="5" max="20" step="1" value="15">
                <span id="length-value">15m</span>
            </label>
            <div class="material-select">
                <span>Material:</span>
                <span class="material-swatch selected" style="background:#8B4513" data-color="#8B4513" title="Wood"></span>
                <span class="material-swatch" style="background:#A9A9A9" data-color="#A9A9A9" title="Steel"></span>
                <span class="material-swatch" style="background:#FFD700" data-color="#FFD700" title="Aluminum"></span>
                <span class="material-swatch" style="background:#654321" data-color="#654321" title="Dark Wood"></span>
                <span class="material-swatch" style="background:#C0C0C0" data-color="#C0C0C0" title="Metal"></span>
                <span class="material-swatch" style="background:#8B7355" data-color="#8B7355" title="Copper"></span>
            </div>
        </div>
        <div id="model-viewer"></div>
      <div class="selected-element-info" id="selected-element-info">
          <div><b>Type:</b> <span id="selected-type"></span></div>
          <div><b>Material:</b> <span id="selected-material"></span></div>
          <button id="enable-move" class="toggle-move"><i class="fa fa-arrows-alt"></i> Move</button>
          <button id="remove-selected">Remove</button>
        <div class="rotate-controls">
          <label>Rotate:</label>
          <input type="number" id="rotate-x" value="0" step="5">° X
          <input type="number" id="rotate-y" value="0" step="5">° Y
          <input type="number" id="rotate-z" value="0" step="5">° Z
          <button id="apply-rotation">Apply</button>
        </div>
      </div>
    </div>
</div>

<!-- keep your three.js versions for compatibility -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.min.js"></script>
<script>
/* --- MATERIALS/COMPONENTS DATABASE --- */
const MATERIALS = [
    {
        id: 'truss',
        name: 'Truss',
        icon: 'fa-project-diagram',
        color: '#8B4513',
        cost: 1200,
        geometry: () => {
            const shape = new THREE.Shape();
            shape.moveTo(-1, 0);
            shape.lineTo(0, 0.8);
            shape.lineTo(1, 0);
            shape.lineTo(-1, 0);
            return new THREE.ExtrudeGeometry(shape, { depth: 0.15, bevelEnabled: false });
        }
    },
    {
        id: 'beam',
        name: 'Beam',
        icon: 'fa-grip-lines',
        color: '#4a9eff',
        cost: 800,
        geometry: () => new THREE.BoxGeometry(2, 0.15, 0.15)
    },
    {
        id: 'rafter',
        name: 'Rafter',
        icon: 'fa-slash',
        color: '#6bff91',
        cost: 600,
        geometry: () => new THREE.BoxGeometry(0.15, 0.15, 2)
    },
    {
        id: 'purlin',
        name: 'Purlin',
        icon: 'fa-grip-lines-vertical',
        color: '#ffa500',
        cost: 400,
        geometry: () => new THREE.BoxGeometry(0.15, 0.15, 1.5)
    },
    {
        id: 'column',
        name: 'Column',
        icon: 'fa-columns',
        color: '#9b59b6',
        cost: 1000,
        geometry: () => new THREE.CylinderGeometry(0.12, 0.12, 2, 12)
    },
    {
        id: 'bracing',
        name: 'Bracing',
        icon: 'fa-xmark',
        color: '#1abc9c',
        cost: 350,
        geometry: () => new THREE.BoxGeometry(0.1, 0.1, 1.2)
    }
];

/* --- BILL OF MATERIALS STATE --- */
let bill = {}; // { id: { qty, meshIds: [] } }
let meshIdCounter = 1;

/* --- 3D SCENE SETUP --- */
let scene, camera, renderer, controls;
let meshes = {}; 
let roofBase, roofType = 'gable', roofPitch = 30, roofWidth = 10, roofLength = 15;
let currentMaterial = '#8B4513';
let currentMaterialName = 'Wood';
let selectedMeshId = null;

/* --- Interaction globals --- */
const raycaster = new THREE.Raycaster();
const mouse = new THREE.Vector2();
let isMoveMode = false;
let isDragging = false;
let activeMesh = null;
let dragPlane = new THREE.Plane();
let dragOffset = new THREE.Vector3();

/* --- Highlight globals --- */
let highlightedMesh = null;
let originalMaterial = null;

function init3D() {
    scene = new THREE.Scene();
    scene.background = new THREE.Color(0xeaeaea);

    camera = new THREE.PerspectiveCamera(60, document.getElementById('model-viewer').clientWidth / document.getElementById('model-viewer').clientHeight, 0.1, 1000);
    camera.position.set(6, 6, 10);

    renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setSize(document.getElementById('model-viewer').clientWidth, document.getElementById('model-viewer').clientHeight);
    document.getElementById('model-viewer').appendChild(renderer.domElement);

    controls = new THREE.OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;

    scene.add(new THREE.AmbientLight(0xffffff, 0.7));
    const dirLight = new THREE.DirectionalLight(0xffffff, 0.7);
    dirLight.position.set(10, 10, 10);
    scene.add(dirLight);

    scene.add(new THREE.GridHelper(20, 20));
    scene.add(new THREE.AxesHelper(2));

    createRoof();

    renderer.domElement.addEventListener('pointerdown', onPointerDown);
    renderer.domElement.addEventListener('pointermove', onPointerMove);
    renderer.domElement.addEventListener('pointerup', onPointerUp);

    animate();
    window.addEventListener('resize', onResize);
}

function animate() {
    requestAnimationFrame(animate);
    controls.update();
    renderer.render(scene, camera);
}

/* --- Move Material Functions (kept for keyboard arrow moves if needed) --- */
function moveSelectedMaterial(dx, dy, dz) {
    if (!selectedMeshId || !meshes[selectedMeshId]) return;
    const mesh = meshes[selectedMeshId].mesh;
    mesh.position.x += dx;
    mesh.position.y += dy;
    mesh.position.z += dz;
}

/* --- Rotate Material Function --- */
function rotateSelectedMaterial(axis, angleDeg) {
    if (!selectedMeshId || !meshes[selectedMeshId]) return;
    const mesh = meshes[selectedMeshId].mesh;
    const angleRad = THREE.MathUtils.degToRad(angleDeg);

    switch (axis.toLowerCase()) {
        case 'x':
            mesh.rotation.x += angleRad;
            break;
        case 'y':
            mesh.rotation.y += angleRad;
            break;
        case 'z':
            mesh.rotation.z += angleRad;
            break;
        default:
            console.warn("Invalid axis. Use 'x', 'y', or 'z'.");
    }
}

/* --- ROOF GENERATION --- */
function createRoof() {
    // Remove previous roof (keep other scene children)
    if (roofBase) scene.remove(roofBase);

    // Foundation
    const baseGeometry = new THREE.BoxGeometry(roofWidth, 0.2, roofLength);
    const baseMaterial = new THREE.MeshPhongMaterial({ color: 0x666666 });
    roofBase = new THREE.Mesh(baseGeometry, baseMaterial);
    roofBase.position.y = -0.1;
    scene.add(roofBase);

    // Remove previous roof meshes (safe removal)
    const toRemove = [];
    scene.traverse(obj => {
        if (obj.userData && obj.userData.roofMesh) toRemove.push(obj);
    });
    toRemove.forEach(o => scene.remove(o));

    // Add roof mesh
    let roofMesh = null;
    const roofMat = new THREE.MeshPhongMaterial({ color: currentMaterial, transparent: true, opacity: 0.7 });
    const pitchRad = roofPitch * Math.PI / 180;
    if (roofType === 'gable') {
        const roofShape = new THREE.Shape();
        const roofH = (roofWidth / 2) * Math.tan(pitchRad);
        roofShape.moveTo(-roofWidth/2, 0);
        roofShape.lineTo(0, roofH);
        roofShape.lineTo(roofWidth/2, 0);
        const extrudeSettings = { depth: roofLength, bevelEnabled: false };
        const roofGeometry = new THREE.ExtrudeGeometry(roofShape, extrudeSettings);
        roofMesh = new THREE.Mesh(roofGeometry, roofMat);
        roofMesh.rotation.z = Math.PI / 2;
        roofMesh.position.y = 0.2;
    } else if (roofType === 'hip') {
        const roofH = (roofWidth / 2) * Math.tan(pitchRad);
        const roofGeometry = new THREE.ConeGeometry(roofWidth/2 * 1.2, roofH, 4);
        roofMesh = new THREE.Mesh(roofGeometry, roofMat);
        roofMesh.position.y = roofH/2 + 0.2;
    } else if (roofType === 'flat') {
        const roofGeometry = new THREE.BoxGeometry(roofWidth, 0.2, roofLength);
        roofMesh = new THREE.Mesh(roofGeometry, roofMat);
        roofMesh.position.y = 0.2;
    } else if (roofType === 'shed') {
        const roofH = roofWidth * Math.tan(pitchRad);
        const roofShape = new THREE.Shape();
        roofShape.moveTo(-roofWidth/2, 0);
        roofShape.lineTo(-roofWidth/2, roofH);
        roofShape.lineTo(roofWidth/2, 0);
        const extrudeSettings = { depth: roofLength, bevelEnabled: false };
        const roofGeometry = new THREE.ExtrudeGeometry(roofShape, extrudeSettings);
        roofMesh = new THREE.Mesh(roofGeometry, roofMat);
        roofMesh.rotation.z = Math.PI / 2;
        roofMesh.position.y = 0.2;
    }
    if (roofMesh) {
        roofMesh.userData.roofMesh = true;
        scene.add(roofMesh);
    }
}

/* --- MATERIALS SIDEBAR --- */
function renderMaterialsSidebar() {
    const list = document.getElementById('materials-list');
    list.innerHTML = '';
    MATERIALS.forEach(mat => {
        const div = document.createElement('div');
        div.className = 'material-item';
        div.innerHTML = `
            <span class="material-icon"><i class="fa ${mat.icon}"></i></span>
            <div class="material-info">
                <div class="material-title">${mat.name}</div>
                <div class="material-cost">₱${mat.cost}</div>
            </div>
            <button class="add-btn" data-id="${mat.id}"><i class="fa fa-plus"></i> Add</button>
        `;
        list.appendChild(div);
    });
    // Add event listeners for Add buttons
    list.querySelectorAll('.add-btn').forEach(btn => {
        btn.onclick = () => addMaterialToScene(btn.getAttribute('data-id'));
    });
}

/* --- ADD MATERIAL TO 3D SCENE --- */
function addMaterialToScene(materialId) {
    const mat = MATERIALS.find(m => m.id === materialId);
    if (!mat) return;
    // Create mesh
    const geometry = mat.geometry();
    const material = new THREE.MeshPhongMaterial({ color: currentMaterial });
    const mesh = new THREE.Mesh(geometry, material);
    mesh.position.set((Math.random() - 0.5) * (roofWidth-2), 1, (Math.random() - 0.5) * (roofLength-2));
    mesh.userData = { materialId, meshId: meshIdCounter, type: mat.name, color: currentMaterialName };
    scene.add(mesh);
    meshes[meshIdCounter] = { mesh, materialId };
    // Update bill
    if (!bill[materialId]) bill[materialId] = { qty: 0, meshIds: [] };
    bill[materialId].qty += 1;
    bill[materialId].meshIds.push(meshIdCounter);
    meshIdCounter++;
    renderBill();
}

/* --- REMOVE MATERIAL FROM 3D SCENE --- */
function removeMaterialFromScene(materialId, meshId) {
    if (meshes[meshId]) {
        scene.remove(meshes[meshId].mesh);
        delete meshes[meshId];
    }
    if (bill[materialId]) {
        bill[materialId].qty -= 1;
        bill[materialId].meshIds = bill[materialId].meshIds.filter(id => id !== meshId);
        if (bill[materialId].qty <= 0) delete bill[materialId];
    }
    renderBill();
    hideSelectedElementInfo();
}

/* --- BILL OF MATERIALS RENDER --- */
function renderBill() {
    const tbody = document.querySelector('#bill-table tbody');
    tbody.innerHTML = '';
    let total = 0;
    Object.keys(bill).forEach(materialId => {
        const mat = MATERIALS.find(m => m.id === materialId);
        const qty = bill[materialId].qty;
        const cost = mat.cost * qty;
        total += cost;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${mat.name}</td>
            <td>${qty}</td>
            <td>₱${cost}</td>
            <td>
                <button class="remove-btn" title="Remove one" onclick="removeMaterialFromScene('${materialId}', ${bill[materialId].meshIds[0]})">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
    document.getElementById('bill-total').textContent = `Total: ₱${total.toLocaleString(undefined, {minimumFractionDigits:2})}`;
}

/* --- MATERIAL SWATCHES --- */
function setupMaterialSwatches() {
    document.querySelectorAll('.material-swatch').forEach(swatch => {
        swatch.addEventListener('click', function() {
            document.querySelectorAll('.material-swatch').forEach(s => s.classList.remove('selected'));
            this.classList.add('selected');
            currentMaterial = this.getAttribute('data-color');
            currentMaterialName = this.title;
            createRoof();
        });
    });
}

/* --- ROOF CONTROLS --- */
function setupRoofControls() {
    document.getElementById('roof-type').addEventListener('change', function() {
        roofType = this.value;
        createRoof();
    });
    document.getElementById('roof-pitch').addEventListener('input', function() {
        roofPitch = parseInt(this.value);
        document.getElementById('pitch-value').textContent = roofPitch + '°';
        createRoof();
    });
    document.getElementById('roof-width').addEventListener('input', function() {
        roofWidth = parseInt(this.value);
        document.getElementById('width-value').textContent = roofWidth + 'm';
        createRoof();
    });
    document.getElementById('roof-length').addEventListener('input', function() {
        roofLength = parseInt(this.value);
        document.getElementById('length-value').textContent = roofLength + 'm';
        createRoof();
    });
}

/* --- 3D SELECTION + MOVE (UNIFIED) --- */
function onPointerDown(event) {
    // compute normalized mouse coordinates relative to renderer dom
    const rect = renderer.domElement.getBoundingClientRect();
    mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
    mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

    raycaster.setFromCamera(mouse, camera);

    // only raycast against your added meshes (not grid / roof)
    const objects = Object.values(meshes).map(m => m.mesh);
    const intersects = raycaster.intersectObjects(objects, true);

    if (isMoveMode) {
        // If move mode: pick and start dragging
        if (intersects.length > 0) {
            // pick top-level mesh that exists in meshes[]
            let picked = intersects[0].object;
            while (picked && !Object.values(meshes).some(m => m.mesh === picked)) {
                picked = picked.parent;
            }
            if (!picked) return;

            activeMesh = picked;
            // maintain selection ID and show panel
            selectedMeshId = activeMesh.userData.meshId;
            selectElement(activeMesh);

            // create a drag plane that faces the camera and passes through the mesh position
            const camDir = new THREE.Vector3();
            camera.getWorldDirection(camDir);
            // plane normal should point from object toward camera (negate camera direction)
            dragPlane.setFromNormalAndCoplanarPoint(camDir.clone().negate().normalize(), activeMesh.position);

            // get intersection between ray and plane to compute offset
            const intersectPoint = raycaster.ray.intersectPlane(dragPlane, new THREE.Vector3());
            if (intersectPoint) {
                dragOffset.copy(intersectPoint).sub(activeMesh.position);
                isDragging = true;
                controls.enabled = false; // disable orbit while dragging
            }
        } else {
            // clicked empty — hide panel
            hideSelectedElementInfo();
        }
    } else {
        // selection mode: show details of clicked mesh
        if (intersects.length > 0) {
            let picked = intersects[0].object;
            while (picked && !Object.values(meshes).some(m => m.mesh === picked)) {
                picked = picked.parent;
            }
            if (!picked) return;
            selectElement(picked);
        } else {
            hideSelectedElementInfo();
        }
    }
}

function onPointerMove(event) {
    if (!isDragging || !activeMesh) return;

    const rect = renderer.domElement.getBoundingClientRect();
    mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
    mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
    raycaster.setFromCamera(mouse, camera);

    const intersectPoint = raycaster.ray.intersectPlane(dragPlane, new THREE.Vector3());
    if (intersectPoint) {
        // keep original offset so the mesh sticks under the cursor
        activeMesh.position.copy(intersectPoint.sub(dragOffset));
    }
}

function onPointerUp(/*event*/) {
    if (isDragging) {
        isDragging = false;
        activeMesh = null;
        // re-enable orbit after move
        if (!isMoveMode) controls.enabled = true;
        else controls.enabled = false; // keep disabled while Move mode active until user toggles button off
    }
}

/* --- SELECT / HIDE PANEL + HIGHLIGHT --- */
function selectElement(mesh) {
    // Remove highlight from old
    if (highlightedMesh && originalMaterial) {
        highlightedMesh.material = originalMaterial;
    }

    selectedMeshId = mesh.userData.meshId;
    highlightedMesh = mesh;
    originalMaterial = mesh.material;

    // Apply highlight material
    mesh.material = new THREE.MeshPhongMaterial({
        color: mesh.material.color,
        emissive: 0xffff00,
        emissiveIntensity: 0.6
    });

    const info = document.getElementById('selected-element-info');
    info.classList.add('active');
    document.getElementById('selected-type').textContent = mesh.userData.type || '—';
    document.getElementById('selected-material').textContent = mesh.userData.color || currentMaterialName;
}

function hideSelectedElementInfo() {
    selectedMeshId = null;
    const info = document.getElementById('selected-element-info');
    info.classList.remove('active');

    // Reset highlight
    if (highlightedMesh && originalMaterial) {
        highlightedMesh.material = originalMaterial;
        highlightedMesh = null;
        originalMaterial = null;
    }
}

/* --- Rotate Material Function (multi-axis) --- */
function rotateSelectedMaterial(angles) {
    if (!selectedMeshId || !meshes[selectedMeshId]) return;
    const mesh = meshes[selectedMeshId].mesh;

    if (angles.x) mesh.rotation.x += THREE.MathUtils.degToRad(angles.x);
    if (angles.y) mesh.rotation.y += THREE.MathUtils.degToRad(angles.y);
    if (angles.z) mesh.rotation.z += THREE.MathUtils.degToRad(angles.z);
}

/* --- UI: remove, move, rotate handlers --- */
document.addEventListener('DOMContentLoaded', function() {
    // remove selected
    document.getElementById('remove-selected').onclick = function() {
        if (selectedMeshId && meshes[selectedMeshId]) {
            const meshData = meshes[selectedMeshId];
            removeMaterialFromScene(meshData.materialId, selectedMeshId);
        }
    };

    // Move button toggle
    const moveBtn = document.getElementById('enable-move');
    moveBtn.addEventListener('click', () => {
        isMoveMode = !isMoveMode;
        moveBtn.classList.toggle('active', isMoveMode);
        controls.enabled = !isMoveMode;
        if (!isMoveMode) {
            isDragging = false;
            activeMesh = null;
        }
    });

    /* --- ✅ Rotation Apply Button (multi-axis) --- */
    const rotateBtn = document.getElementById('apply-rotation');
    if (rotateBtn) {
        rotateBtn.addEventListener('click', () => {
            if (selectedMeshId && meshes[selectedMeshId]) {
                const angles = {
                    x: parseFloat(document.getElementById('rotate-x').value) || 0,
                    y: parseFloat(document.getElementById('rotate-y').value) || 0,
                    z: parseFloat(document.getElementById('rotate-z').value) || 0
                };
                rotateSelectedMaterial(angles);
            }
        });
    }
});

/* --- INIT --- */
window.addEventListener('DOMContentLoaded', () => {
    renderMaterialsSidebar();
    init3D();
    renderBill();
    setupMaterialSwatches();
    setupRoofControls();
    window.removeMaterialFromScene = removeMaterialFromScene;
});
</script>
</body>
</html>
