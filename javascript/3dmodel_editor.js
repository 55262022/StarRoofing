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
  hasModel: typeof HAS_MODEL !== 'undefined' ? HAS_MODEL : false
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

// Load existing model from product
async function loadExistingModel() {
  if (!state.existingModelPath) {
    log('No existing model path');
    return;
  }
  
  updateStatus('Loading existing 3D model...');
  log('Model path from DB: ' + state.existingModelPath);
  
  // Clean the path
  let modelPath = state.existingModelPath;
  
  // If path doesn't start with ../, add it
  if (!modelPath.startsWith('../')) {
    modelPath = '../' + modelPath;
  }
  
  log('Final model path: ' + modelPath);
  
  try {
    await loadModel(modelPath);
    
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

async function generateFromProductImage() {
  Swal.fire({
    title: 'Generating 3D Model',
    html: `Processing ${state.productName}...<br><small>This may take a few minutes</small>`,
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading()
  });

  updateStatus('Uploading product image to Meshy API...');

  const formData = new FormData();
  formData.append('product_id', state.productId);
  formData.append('image_path', state.productImagePath);

  try {
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

      await loadModel(json.model_url);
      updateStatus('Model loaded successfully');

    } else if (json.status === 'pending') {
      showLoadingWithTaskStatus(json.task_id, json.existing);
      pollTaskStatus(json.task_id);

    } else {
      throw new Error(json.message || 'Unknown error from API');
    }

  } catch (err) {
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
  Swal.fire({
    title: 'Generating 3D Model',
    html: 'Uploading image to Meshy AI...<br><small>This may take a few minutes</small>',
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading()
  });

  updateStatus('Uploading to Meshy API...');

  const formData = new FormData();
  formData.append('images[]', state.uploadFiles[0]);

  try {
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

      await loadModel(json.model_url);
      state.uploadFiles = [];
      updateStatus('Model loaded successfully');

    } else if (json.status === 'pending') {
      showLoadingWithTaskStatus(json.task_id, json.existing);
      pollTaskStatus(json.task_id);

    } else {
      throw new Error(json.message || 'Unknown error from API');
    }

  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'Upload Failed',
      text: err.message
    });
    updateStatus('Upload failed: ' + err.message, true);
    console.error('ERROR:', err);
  }
}

function showLoadingWithTaskStatus(taskId, isExisting) {
  const title = isExisting ? 'Already Processing' : 'Processing...';
  
  fetch('../includes/loader.php')
    .then(response => response.text())
    .then(loaderHTML => {
      Swal.fire({
        title: title,
        html: `${loaderHTML}<br><div style="margin-top: 20px;">Task ID: ${taskId}</div><div style="margin-top: 10px; font-size: 14px; color: #666;">Checking status every 10 seconds...</div>`,
        showConfirmButton: false,
        allowOutsideClick: false
      });
    })
    .catch(() => {
      Swal.fire({
        icon: 'info',
        title: title,
        html: `Task ID: ${taskId}<br><br>Checking status every 10 seconds...`,
        showConfirmButton: false,
        allowOutsideClick: false
      });
    });
}

async function pollTaskStatus(taskId) {
  const maxAttempts = 60;
  let attempts = 0;

  const checkStatus = async () => {
    attempts++;
    try {
      const response = await fetch(`meshy/meshy_check_status.php?task_id=${taskId}`);
      const json = await response.json();
      
      log(`Status check ${attempts}: ${json.status}`);

      if (json.status === 'succeeded') {
        Swal.fire({
          icon: 'success',
          title: 'Model Generated!',
          text: 'Loading 3D model...',
          timer: 2000,
          showConfirmButton: false
        });

        await loadModel(json.model_url);
        updateStatus('Model loaded successfully');
        
        // Redirect back to inventory if this was a product
        if (state.productId) {
          setTimeout(() => {
            window.location.href = `inventory.php?success=3D model generated successfully for ${state.productName}`;
          }, 2000);
        }

      } else if (json.status === 'pending') {
        const progress = json.progress || 0;
        updateStatus(`Processing: ${progress}% (${attempts}/${maxAttempts})`);

        if (attempts < maxAttempts) {
          setTimeout(checkStatus, 10000);
        } else {
          throw new Error('Timeout: Model generation took too long');
        }

      } else if (json.status === 'failed') {
        throw new Error('Model generation failed');
        
      } else {
        throw new Error(json.message || 'Unknown status: ' + json.status);
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

// Check URL for existing model to load
function checkURLForModel() {
  const urlParams = new URLSearchParams(window.location.search);
  const modelPath = urlParams.get('load');
  
  if (modelPath) {
    log('Loading model from URL parameter: ' + modelPath);
    
    let decodedPath = decodeURIComponent(modelPath);
    let finalPath = decodedPath;
    
    if (decodedPath.startsWith('uploads/')) {
      finalPath = '../' + decodedPath;
    } else if (!decodedPath.includes('/')) {
      finalPath = '../uploads/3dmodels/' + decodedPath;
    }
    
    log('Resolved path: ' + finalPath);
    
    setTimeout(() => {
      loadModel(finalPath);
    }, 500);
  }
}

// Initialize
initThree();
setupUI();
updateStatus('Ready');
checkURLForModel();