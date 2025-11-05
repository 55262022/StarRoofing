import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { GLTFExporter } from 'three/addons/exporters/GLTFExporter.js';
import { TransformControls } from 'three/addons/controls/TransformControls.js';

const $ = (sel) => document.querySelector(sel);
const log = (msg) => {
  const c = $('#consoleLog');
  const empty = $('#consoleEmpty');
  if (empty) empty.remove();
  
  const div = document.createElement('div');
  div.textContent = `[${new Date().toLocaleTimeString()}] ${msg}`;
  c.insertBefore(div, c.firstChild);
};

const state = {
  scene: null,
  camera: null,
  renderer: null,
  controls: null,
  transformControls: null,
  loadedModel: null,
  textureLoader: null,
  uploadFiles: [],
  currentTool: 'select',
  productId: typeof PRODUCT_ID !== 'undefined' ? PRODUCT_ID : null,
  productImagePath: typeof PRODUCT_IMAGE_PATH !== 'undefined' ? PRODUCT_IMAGE_PATH : null,
  productName: typeof PRODUCT_NAME !== 'undefined' ? PRODUCT_NAME : null,
  existingModelPath: typeof EXISTING_MODEL_PATH !== 'undefined' ? EXISTING_MODEL_PATH : null,
  hasModel: typeof HAS_MODEL !== 'undefined' ? HAS_MODEL : false,
  modelLoaded: false
};

// Progress bar elements
const loadingOverlay = $('#loadingOverlay');
const progressBarFill = $('#progressBarFill');
const progressPercentage = $('#progressPercentage');
const progressStatus = $('#progressStatus');

// Stage elements
const stage1 = $('#stage1');
const stage2 = $('#stage2');
const stage3 = $('#stage3');
const stage4 = $('#stage4');
const stage5 = $('#stage5');

function updateStatus(txt, isError = false) {
  const statusEl = $('#statusText');
  statusEl.textContent = txt;
  statusEl.style.color = isError ? '#dc3545' : '';
  log(txt);
}

// ==================== PROGRESS BAR FUNCTIONS ====================

function showLoading() {
  if (loadingOverlay) {
    loadingOverlay.classList.add('active');
    updateProgress(0, 'Initializing...');
    log('Loading overlay shown');
  }
}

function hideLoading() {
  if (loadingOverlay) {
    loadingOverlay.classList.remove('active');
    resetProgress();
    log('Loading overlay hidden');
  }
}

function updateProgress(percentage, message = '') {
  if (progressBarFill) {
    progressBarFill.style.width = percentage + '%';
  }
  if (progressPercentage) {
    progressPercentage.textContent = percentage + '%';
  }
  if (message && progressStatus) {
    progressStatus.textContent = message;
  }
  
  // Update stages based on progress
  updateStages(percentage);
  
  // Also update status bar
  updateStatus(`Processing: ${percentage}%`);
}

function updateStages(progress) {
  if (!stage1 || !stage2 || !stage3 || !stage4 || !stage5) return;
  
  // Reset all stages
  [stage1, stage2, stage3, stage4, stage5].forEach(stage => {
    stage.classList.remove('active', 'completed');
    const icon = stage.querySelector('.stage-icon');
    if (icon) {
      icon.classList.remove('active', 'completed');
      icon.classList.add('pending');
    }
  });
  
  if (progress >= 0 && progress < 20) {
    // Stage 1: Uploading
    stage1.classList.add('active');
    const icon1 = stage1.querySelector('.stage-icon');
    if (icon1) {
      icon1.classList.remove('pending');
      icon1.classList.add('active');
    }
  } else if (progress >= 20 && progress < 40) {
    // Stage 2: AI Processing
    stage1.classList.add('completed');
    const icon1 = stage1.querySelector('.stage-icon');
    if (icon1) {
      icon1.classList.remove('pending');
      icon1.classList.add('completed');
    }
    
    stage2.classList.add('active');
    const icon2 = stage2.querySelector('.stage-icon');
    if (icon2) {
      icon2.classList.remove('pending');
      icon2.classList.add('active');
    }
  } else if (progress >= 40 && progress < 60) {
    // Stage 3: Generating Geometry
    stage1.classList.add('completed');
    const icon1 = stage1.querySelector('.stage-icon');
    if (icon1) {
      icon1.classList.remove('pending');
      icon1.classList.add('completed');
    }
    
    stage2.classList.add('completed');
    const icon2 = stage2.querySelector('.stage-icon');
    if (icon2) {
      icon2.classList.remove('pending');
      icon2.classList.add('completed');
    }
    
    stage3.classList.add('active');
    const icon3 = stage3.querySelector('.stage-icon');
    if (icon3) {
      icon3.classList.remove('pending');
      icon3.classList.add('active');
    }
  } else if (progress >= 60 && progress < 85) {
    // Stage 4: Applying Textures
    [stage1, stage2, stage3].forEach(stage => {
      stage.classList.add('completed');
      const icon = stage.querySelector('.stage-icon');
      if (icon) {
        icon.classList.remove('pending');
        icon.classList.add('completed');
      }
    });
    
    stage4.classList.add('active');
    const icon4 = stage4.querySelector('.stage-icon');
    if (icon4) {
      icon4.classList.remove('pending');
      icon4.classList.add('active');
    }
  } else if (progress >= 85) {
    // Stage 5: Finalizing
    [stage1, stage2, stage3, stage4].forEach(stage => {
      stage.classList.add('completed');
      const icon = stage.querySelector('.stage-icon');
      if (icon) {
        icon.classList.remove('pending');
        icon.classList.add('completed');
      }
    });
    
    stage5.classList.add('active');
    const icon5 = stage5.querySelector('.stage-icon');
    if (icon5) {
      icon5.classList.remove('pending');
      icon5.classList.add('active');
    }
  }
  
  if (progress === 100) {
    // All stages completed
    [stage1, stage2, stage3, stage4, stage5].forEach(stage => {
      stage.classList.add('completed');
      const icon = stage.querySelector('.stage-icon');
      if (icon) {
        icon.classList.remove('pending', 'active');
        icon.classList.add('completed');
      }
    });
  }
}

function resetProgress() {
  if (progressBarFill) {
    progressBarFill.style.width = '0%';
  }
  if (progressPercentage) {
    progressPercentage.textContent = '0%';
  }
  if (progressStatus) {
    progressStatus.textContent = 'Initializing...';
  }
  
  // Reset all stages
  [stage1, stage2, stage3, stage4, stage5].forEach(stage => {
    if (stage) {
      stage.classList.remove('active', 'completed');
      const icon = stage.querySelector('.stage-icon');
      if (icon) {
        icon.classList.remove('active', 'completed');
        icon.classList.add('pending');
      }
    }
  });
}

// ==================== THREE.JS INITIALIZATION ====================

function initThree() {
  const container = $('#threeViewport');
  container.innerHTML = '';

  state.scene = new THREE.Scene();
  state.scene.background = new THREE.Color(0xffffff);

  const w = container.clientWidth;
  const h = container.clientHeight;
  
  state.camera = new THREE.PerspectiveCamera(60, w / h, 0.1, 1000);
  state.camera.position.set(2.5, 2.0, 3.5);

  state.renderer = new THREE.WebGLRenderer({ antialias: true });
  state.renderer.setPixelRatio(window.devicePixelRatio);
  state.renderer.setSize(w, h);
  state.renderer.outputColorSpace = THREE.SRGBColorSpace;
  container.appendChild(state.renderer.domElement);

  const dirLight = new THREE.DirectionalLight(0xffffff, 1.2);
  dirLight.position.set(5, 10, 7.5);
  state.scene.add(dirLight);
  state.scene.add(new THREE.AmbientLight(0xffffff, 0.8));

  const grid = new THREE.GridHelper(20, 40, 0x999999, 0xcccccc);
  state.scene.add(grid);

  const axesHelper = new THREE.AxesHelper(5);
  state.scene.add(axesHelper);

  state.controls = new OrbitControls(state.camera, state.renderer.domElement);
  state.controls.enableDamping = true;
  state.controls.dampingFactor = 0.05;

  // Initialize TransformControls
  state.transformControls = new TransformControls(state.camera, state.renderer.domElement);
  state.transformControls.addEventListener('dragging-changed', (event) => {
    state.controls.enabled = !event.value;
  });
  state.scene.add(state.transformControls);

  state.textureLoader = new THREE.TextureLoader();

  window.addEventListener('resize', onResize);
  animate();
  
  log('3D Editor initialized with transform controls');
  
  if (state.productId) {
    log(`Product Mode: ${state.productName} (ID: ${state.productId})`);
    
    // Auto-load existing model if available
    if (state.hasModel && state.existingModelPath) {
      log(`Existing model found: ${state.existingModelPath}`);
      setTimeout(() => {
        loadExistingModel();
      }, 500);
    } else {
      log('No existing model. Ready to generate.');
    }
  }
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
  
  // Detach from transform controls
  if (state.transformControls) {
    state.transformControls.detach();
  }
  
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
  state.modelLoaded = false;
  $('#modelInfo').textContent = '';
}

// FIXED: Load model with correct path handling
async function loadModel(url) {
  updateStatus('Loading model...');
  
  try {
    clearModel();
    
    // FIXED: Build correct absolute URL from project root
    let modelUrl = url;
    
    // Clean path - remove leading '../' and '/'
    let cleanPath = url.replace(/^\.\.\//, '').replace(/^\//, '');
    
    // If path doesn't start with http and doesn't have protocol
    if (!cleanPath.startsWith('http') && !cleanPath.startsWith('blob:')) {
      // Build absolute URL from current location
      const baseUrl = window.location.origin + window.location.pathname.replace(/admin\/.*$/, '');
      modelUrl = baseUrl + cleanPath;
    }
    
    log(`Loading from: ${modelUrl}`);
    
    const loader = new GLTFLoader();
    
    await new Promise((resolve, reject) => {
      loader.load(
        modelUrl,
        (gltf) => {
          state.loadedModel = gltf.scene;
          state.modelLoaded = true;
          
          state.loadedModel.traverse(child => {
            if (child.isMesh) {
              child.material = new THREE.MeshStandardMaterial({
                color: 0x808080,
                metalness: 0.5,
                roughness: 0.5
              });
            }
          });
          
          const box = new THREE.Box3().setFromObject(state.loadedModel);
          const center = box.getCenter(new THREE.Vector3());
          state.loadedModel.position.sub(center);
          
          state.scene.add(state.loadedModel);
          
          // Attach to transform controls if a tool is active
          if (state.currentTool !== 'select') {
            state.transformControls.attach(state.loadedModel);
          }
          
          const size = box.getSize(new THREE.Vector3());
          $('#modelInfo').textContent = 
            `Model: ${size.x.toFixed(2)} × ${size.y.toFixed(2)} × ${size.z.toFixed(2)} units`;
          
          updateStatus('Model loaded successfully');
          log('Model loaded successfully');
          resolve();
        },
        (xhr) => {
          if (xhr.lengthComputable) {
            const percent = Math.round((xhr.loaded / xhr.total) * 100);
            updateStatus(`Loading: ${percent}%`);
          }
        },
        (error) => {
          console.error('Load error:', error);
          reject(error);
        }
      );
    });
  } catch (err) {
    console.error('Load error:', err);
    log(`Failed to load model: ${err.message}`);
    updateStatus('Failed to load model', true);
    
    Swal.fire({
      icon: 'error',
      title: 'Load Failed',
      html: `Unable to load model.<br><small>${err.message}</small>`,
      confirmButtonColor: '#e74c3c'
    });
  }
}

// Load existing model from product
async function loadExistingModel() {
  if (!state.existingModelPath || !state.hasModel) {
    log('No existing model available');
    return;
  }
  
  updateStatus('Loading existing 3D model...');
  log('Loading existing model: ' + state.existingModelPath);
  
  try {
    // FIXED: Use loadModel which now handles path correctly
    await loadModel(state.existingModelPath);
    
    Swal.fire({
      icon: 'success',
      title: 'Model Loaded',
      text: `3D model for ${state.productName} is ready!`,
      timer: 2000,
      showConfirmButton: false
    });
  } catch (err) {
    log('Failed to load existing model: ' + err.message);
    updateStatus('Model not found or corrupted', true);
    
    Swal.fire({
      icon: 'warning',
      title: 'Model Not Found',
      html: 'The 3D model file could not be loaded.<br>You may need to regenerate it.',
      confirmButtonText: 'OK'
    });
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
      link.download = 'model_' + Date.now() + '.gltf';
      link.click();
      URL.revokeObjectURL(url);
      
      updateStatus('Export complete');
      log('Model exported successfully');
    },
    (error) => {
      console.error('Export error:', error);
      updateStatus('Export failed', true);
      log('Export failed: ' + error.message);
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

function setupToolButtons() {
  const toolButtons = {
    selectTool: 'select',
    moveTool: 'translate',
    rotateTool: 'rotate',
    scaleTool: 'scale'
  };

  // Set active tool styling and transform mode
  function setActiveTool(toolName) {
    // Remove active class from all tool buttons
    Object.keys(toolButtons).forEach(btnId => {
      const btn = document.getElementById(btnId);
      if (btn) btn.classList.remove('btn-tool-active');
    });

    // Add active class to current tool
    const activeBtn = Object.keys(toolButtons).find(id => toolButtons[id] === toolName);
    if (activeBtn) {
      document.getElementById(activeBtn)?.classList.add('btn-tool-active');
    }

    state.currentTool = toolName;
    
    // Handle transform controls
    if (toolName === 'select') {
      // Detach transform controls
      state.transformControls.detach();
      state.controls.enabled = true;
      log('Select mode: OrbitControls enabled');
    } else {
      // Attach transform controls to model
      if (state.loadedModel) {
        state.transformControls.setMode(toolName);
        state.transformControls.attach(state.loadedModel);
        state.controls.enabled = false;
        log(`${toolName.toUpperCase()} mode: Transform active`);
      } else {
        Swal.fire({
          icon: 'info',
          title: 'No Model',
          text: 'Please load a model first to use transform tools',
          timer: 2000
        });
        // Reset to select mode
        setActiveTool('select');
        return;
      }
    }
  }

  // Add click handlers for each tool
  Object.entries(toolButtons).forEach(([btnId, toolName]) => {
    const btn = document.getElementById(btnId);
    if (btn) {
      btn.addEventListener('click', () => {
        setActiveTool(toolName);
      });
    }
  });

  // Wireframe button
  const wireframeBtn = document.getElementById('wireframeBtn');
  if (wireframeBtn) {
    wireframeBtn.addEventListener('click', () => {
      if (!state.loadedModel) {
        Swal.fire('No Model', 'Please load a model first', 'info');
        return;
      }
      
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
      
      // Toggle button styling
      if (!isWireframe) {
        wireframeBtn.classList.add('btn-tool-active');
      } else {
        wireframeBtn.classList.remove('btn-tool-active');
      }
    });
  }

  // Set default tool
  setActiveTool('select');
}

function setupUI() {
  const uploadZone = $('#uploadZone');
  const fileInput = $('#fileInput');
  
  // Only setup upload if no product is selected
  if (uploadZone && fileInput) {
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
      const files = Array.from(e.dataTransfer.files);
      handleFiles(files);
    });
    
    uploadZone.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', (e) => {
      handleFiles(Array.from(e.target.files));
    });
  }
  
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
  
  $('#generateBtn').addEventListener('click', async () => {
    if (state.productId && state.productImagePath) {
      // Check if model already exists
      if (state.hasModel) {
        const result = await Swal.fire({
          title: '3D Model Exists',
          text: 'This product already has a 3D model. Do you want to regenerate it?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Yes, Regenerate',
          cancelButtonText: 'No, Keep Current',
          confirmButtonColor: '#3498db',
          cancelButtonColor: '#95a5a6'
        });
        
        if (!result.isConfirmed) {
          return;
        }
      }
      
      // Generate from product image
      await generateFromProductImage();
    } else {
      // Generate from uploaded files
      if (state.uploadFiles.length === 0) {
        Swal.fire('No Images', 'Please add at least one image', 'warning');
        return;
      }
      await generateFromUploadedFiles();
    }
  });
  
  $('#downloadBtn').addEventListener('click', exportModel);
  
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
  
  $('#colorPicker').addEventListener('input', applyMaterial);
  $('#metalness').addEventListener('input', (e) => {
    $('#metalVal').textContent = parseFloat(e.target.value).toFixed(2);
    applyMaterial();
  });
  $('#roughness').addEventListener('input', (e) => {
    $('#roughVal').textContent = parseFloat(e.target.value).toFixed(2);
    applyMaterial();
  });
  
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
  
  $('#resetCameraBtn').addEventListener('click', () => {
    state.camera.position.set(2.5, 2.0, 3.5);
    state.controls.target.set(0, 0, 0);
    state.controls.update();
    log('Camera reset');
  });
  
  // Setup tool buttons
  setupToolButtons();
}

function handleFiles(files) {
  const images = files.filter(f => f.type.startsWith('image/'));
  
  if (images.length === 0) {
    Swal.fire('Invalid Files', 'Please select image files only', 'warning');
    return;
  }
  
  state.uploadFiles = images;
  updateStatus(`${state.uploadFiles.length} image(s) ready`);
  log(`Added ${images.length} image(s)`);
}

// ==================== GENERATION FUNCTIONS WITH PROGRESS ====================

async function generateFromProductImage() {
  // Show loading overlay with progress bar
  showLoading();
  updateProgress(5, 'Preparing upload...');

  updateStatus('Uploading product image to Meshy API...');

  const formData = new FormData();
  formData.append('product_id', state.productId);
  formData.append('image_path', state.productImagePath);

  try {
    updateProgress(10, 'Uploading image...');
    
    // FIXED: Correct path - meshy folder is directly in admin
    const response = await fetch('meshy/meshy_upload.php', {
      method: 'POST',
      body: formData
    });
    
    // Check if response is OK
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    
    // Try to parse JSON
    let json;
    const text = await response.text();
    log('Raw response: ' + text.substring(0, 200));
    
    try {
      json = JSON.parse(text);
    } catch (parseErr) {
      throw new Error('Invalid JSON response: ' + text.substring(0, 100));
    }
    
    log('Upload response: ' + JSON.stringify(json));

    if (json.status === 'success') {
      updateProgress(100, 'Complete!');
      
      setTimeout(async () => {
        hideLoading();
        
        if (json.existing) {
          Swal.fire({
            icon: 'info',
            title: 'Already Converted',
            text: 'This product was already converted to 3D!',
            timer: 2000
          });
        } else {
          Swal.fire({
            icon: 'success',
            title: 'Model Ready!',
            timer: 2000,
            showConfirmButton: false
          });
        }

        // FIXED: Use model_path instead of model_url
        await loadModel(json.model_path || json.model_url);
        updateStatus('Model loaded successfully');
      }, 500);

    } else if (json.status === 'pending') {
      updateProgress(15, 'Task created, starting processing...');
      pollTaskStatusWithProgress(json.task_id);

    } else {
      throw new Error(json.message || 'Unknown error from API');
    }

  } catch (err) {
    hideLoading();
    Swal.fire({
      icon: 'error',
      title: 'Upload Failed',
      text: err.message
    });
    updateStatus('Upload failed: ' + err.message, true);
    console.error('ERROR:', err);
  }
}

async function generateFromUploadedFiles() {
  // Show loading overlay with progress bar
  showLoading();
  updateProgress(5, 'Preparing upload...');

  updateStatus('Uploading to Meshy API...');

  const formData = new FormData();
  formData.append('images', state.uploadFiles[0]);

  try {
    updateProgress(10, 'Uploading image...');
    
    // Using correct path in admin/meshy folder
    const response = await fetch('meshy/meshy_upload.php', {
      method: 'POST',
      body: formData
    });
    
    // Check if response is OK
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    
    // Try to parse JSON
    let json;
    const text = await response.text();
    log('Raw response: ' + text.substring(0, 200));
    
    try {
      json = JSON.parse(text);
    } catch (parseErr) {
      throw new Error('Invalid JSON response: ' + text.substring(0, 100));
    }

    if (json.status === 'success') {
      updateProgress(100, 'Complete!');
      
      setTimeout(async () => {
        hideLoading();
        
        if (json.existing) {
          Swal.fire({
            icon: 'info',
            title: 'Already Converted',
            text: 'This image was already converted to 3D!',
            timer: 2000
          });
        } else {
          Swal.fire({
            icon: 'success',
            title: 'Model Ready!',
            timer: 2000,
            showConfirmButton: false
          });
        }

        // FIXED: Use model_path
        await loadModel(json.model_path || json.model_url);
        state.uploadFiles = [];
        updateStatus('Model loaded successfully');
      }, 500);

    } else if (json.status === 'pending') {
      updateProgress(15, 'Task created, starting processing...');
      pollTaskStatusWithProgress(json.task_id);

    } else {
      throw new Error(json.message || 'Unknown error from API');
    }

  } catch (err) {
    hideLoading();
    Swal.fire({
      icon: 'error',
      title: 'Upload Failed',
      text: err.message
    });
    updateStatus('Upload failed: ' + err.message, true);
    console.error('ERROR:', err);
  }
}

async function pollTaskStatusWithProgress(taskId) {
  const maxAttempts = 120; // 10 minutes max (120 * 5 seconds)
  let attempts = 0;

  const checkStatus = async () => {
    attempts++;
    
    try {
      // Using correct path in admin/meshy folder
      const response = await fetch(`meshy/meshy_check_status.php?task_id=${taskId}`);
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }
      
      const json = await response.json();
      
      log(`Status check ${attempts}: ${json.status} - Progress: ${json.progress || 0}%`);

      if (json.status === 'succeeded') {
        // Complete!
        updateProgress(100, 'Model generated successfully!');
        
        setTimeout(async () => {
          hideLoading();
          
          Swal.fire({
            icon: 'success',
            title: 'Model Generated!',
            text: 'Loading 3D model...',
            timer: 2000,
            showConfirmButton: false
          });

          // FIXED: Use model_path for loading
          await loadModel(json.model_path || json.model_url);
          updateStatus('Model loaded successfully');
          
          // Redirect back to inventory if this was a product
          if (state.productId) {
            setTimeout(() => {
              window.location.href = `inventory.php?success=3D model generated successfully for ${state.productName}`;
            }, 2000);
          }
        }, 1000);

      } else if (json.status === 'pending') {
        // Update progress from API
        const progress = json.progress || 0;
        const message = json.message || 'Processing...';
        
        updateProgress(progress, message);
        log(`Progress: ${progress}% - ${message}`);

        if (attempts < maxAttempts) {
          // Continue polling every 5 seconds
          setTimeout(checkStatus, 5000);
        } else {
          throw new Error('Timeout: Model generation took too long (10 minutes)');
        }

      } else if (json.status === 'failed') {
        hideLoading();
        throw new Error('Model generation failed on Meshy server');
        
      } else {
        hideLoading();
        throw new Error(json.message || 'Unknown status: ' + json.status);
      }

    } catch (err) {
      hideLoading();
      log('Error during status check: ' + err.message);
      
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: err.message,
        confirmButtonText: 'OK'
      });
      updateStatus('Error: ' + err.message, true);
    }
  };

  // Start polling after 3 seconds
  setTimeout(checkStatus, 3000);
}

// Check URL for existing model to load
function checkURLForModel() {
  const urlParams = new URLSearchParams(window.location.search);
  const modelPath = urlParams.get('load');
  
  if (modelPath) {
    log('Loading model from URL parameter: ' + modelPath);
    
    let decodedPath = decodeURIComponent(modelPath);
    
    log('Decoded path: ' + decodedPath);
    
    setTimeout(() => {
      loadModel(decodedPath);
    }, 500);
  }
}

// Initialize
initThree();
setupUI();
updateStatus('Ready');
checkURLForModel();