<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Structural Steel Estimator with 3D Modeling</title>
    <script src="https://cdn.jsdelivr.net/npm/three@0.132.2/build/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.132.2/examples/js/controls/OrbitControls.js"></script>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #34495e;
            --success-color: #27ae60;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            grid-column: 1 / -1;
            background: var(--primary-color);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        h1, h2, h3 {
            margin-bottom: 15px;
            color: var(--primary-color);
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        button {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        button:hover {
            background: #2980b9;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: var(--light-color);
            font-weight: 600;
        }
        
        .estimation-section {
            grid-column: 1;
        }
        
        .modeling-section {
            grid-column: 2;
        }
        
        #modeling-container {
            width: 100%;
            height: 400px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .upload-area {
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            margin-bottom: 20px;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        
        .upload-area:hover {
            border-color: var(--secondary-color);
        }
        
        .upload-icon {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 10px;
        }
        
        .results-card {
            background: var(--light-color);
            padding: 20px;
            border-radius: 8px;
        }
        
        .cost-summary {
            background: var(--success-color);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: center;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 20px;
        }
        
        .tab {
            padding: 10px 20px;
            background: #ddd;
            cursor: pointer;
            border-radius: 4px 4px 0 0;
            margin-right: 5px;
        }
        
        .tab.active {
            background: var(--primary-color);
            color: white;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        @media (max-width: 1024px) {
            .container {
                grid-template-columns: 1fr;
            }
            
            .estimation-section, .modeling-section {
                grid-column: 1;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Structural Steel Estimator with 3D Modeling</h1>
        <button id="toggle-view">Switch to 3D View</button>
    </header>

    <div class="container">
        <div class="estimation-section">
            <div class="card">
                <h2>Project Information</h2>
                <div class="form-group">
                    <label for="project-name">Project Name</label>
                    <input type="text" id="project-name" placeholder="Enter project name">
                </div>
                <div class="form-group">
                    <label for="project-description">Project Description</label>
                    <textarea id="project-description" rows="3" placeholder="Describe your project"></textarea>
                </div>
            </div>

            <div class="card">
                <h2>Structural Dimensions</h2>
                <div class="form-group">
                    <label for="length">Length (m)</label>
                    <input type="number" id="length" value="4.00" step="0.01">
                </div>
                <div class="form-group">
                    <label for="width">Width (m)</label>
                    <input type="number" id="width" value="4.00" step="0.01">
                </div>
                <div class="form-group">
                    <label for="height">Height (m)</label>
                    <input type="number" id="height" value="2.00" step="0.01">
                </div>
                <div class="form-group">
                    <label for="spacing">Trustee Spacing (m)</label>
                    <input type="number" id="spacing" value="2.00" step="0.01">
                </div>
            </div>

            <div class="card">
                <h2>Material Selection</h2>
                <div class="form-group">
                    <label for="material-type">Material Type</label>
                    <select id="material-type">
                        <option value="steel">Steel</option>
                        <option value="aluminum">Aluminum</option>
                        <option value="composite">Composite</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="section-type">Section Type</label>
                        <select id="section-type">
                        <option value="w-beam">W Beam</option>
                        <option value="i-beam">I Beam</option>
                        <option value="l-angle">L Angle</option>
                        <option value="tube">Tubular</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="section-size">Section Size</label>
                    <input type="text" id="section-size" value="W50x26">
                </div>
            </div>

            <div class="card">
                <h2>Estimation Results</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Cost</th>
                        </tr>
                    </thead>
                    <tbody id="estimation-results">
                        <tr>
                            <td>Steel Beams</td>
                            <td>12</td>
                            <td>m</td>
                            <td>$1,250.00</td>
                        </tr>
                        <tr>
                            <td>Connectors</td>
                            <td>24</td>
                            <td>pcs</td>
                            <td>$480.00</td>
                        </tr>
                        <tr>
                            <td>Labor</td>
                            <td>16</td>
                            <td>hours</td>
                            <td>$1,200.00</td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="cost-summary">
                    <h3>Total Estimated Cost: $2,930.00</h3>
                </div>
            </div>
        </div>

        <div class="modeling-section">
            <div class="card">
                <h2>3D Model Generation</h2>
                <div class="upload-area" id="upload-area">
                    <div class="upload-icon">📁</div>
                    <p>Click to upload structural images or drag & drop here</p>
                    <input type="file" id="image-upload" accept="image/*" multiple style="display: none;">
                </div>
                
                <div id="modeling-container">
                    <!-- 3D rendering will appear here -->
                </div>
                
                <div class="form-group">
                    <label for="model-complexity">Model Detail Level</label>
                    <select id="model-complexity">
                        <option value="low">Low (Fast)</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High (Slow)</option>
                    </select>
                </div>
                
                <button id="generate-model">Generate 3D Model</button>
            </div>

            <div class="card">
                <h2>Model Controls</h2>
                <div class="form-group">
                    <label for="model-rotation">Rotation</label>
                    <input type="range" id="model-rotation" min="0" max="360" value="0">
                </div>
                <div class="form-group">
                    <label for="model-zoom">Zoom</label>
                    <input type="range" id="model-zoom" min="1" max="100" value="50">
                </div>
                <div class="form-group">
                    <label for="view-mode">View Mode</label>
                    <select id="view-mode">
                        <option value="solid">Solid</option>
                        <option value="wireframe">Wireframe</option>
                        <option value="points">Points</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/three@0.132.2/examples/js/loaders/GLTFLoader.js"></script>
<script>
    let scene, camera, renderer, controls;

    function init3DView() {
        scene = new THREE.Scene();
        scene.background = new THREE.Color(0xf0f0f0);

        camera = new THREE.PerspectiveCamera(
            75,
            document.getElementById('modeling-container').clientWidth / 
            document.getElementById('modeling-container').clientHeight,
            0.1, 1000
        );
        camera.position.z = 5;

        renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setSize(
            document.getElementById('modeling-container').clientWidth,
            document.getElementById('modeling-container').clientHeight
        );
        document.getElementById('modeling-container').appendChild(renderer.domElement);

        controls = new THREE.OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;

        const ambientLight = new THREE.AmbientLight(0x404040);
        scene.add(ambientLight);
        const directionalLight = new THREE.DirectionalLight(0xffffff, 0.5);
        directionalLight.position.set(1, 1, 1);
        scene.add(directionalLight);

        animate();
    }

    function animate() {
        requestAnimationFrame(animate);
        controls.update();
        renderer.render(scene, camera);
    }

    // === NEW: Upload and send to 3D API ===
    let uploadedFiles = [];

    document.getElementById('upload-area').addEventListener('click', () => {
        document.getElementById('image-upload').click();
    });

    document.getElementById('image-upload').addEventListener('change', function(e) {
        uploadedFiles = Array.from(e.target.files);
        if (uploadedFiles.length > 0) {
            alert(`${uploadedFiles.length} images selected. Ready for 3D generation.`);
        }
    });

    document.getElementById('generate-model').addEventListener('click', async function() {
        if (uploadedFiles.length === 0) {
            alert("Please upload construction images first.");
            return;
        }

        // 1. Send images to backend API (you create a PHP/Node API that calls Forge or any photogrammetry service)
        alert("Sending images to 3D modeling API...");

        // Example: send to your backend
        const formData = new FormData();
        uploadedFiles.forEach((file) => formData.append("images[]", file));

        try {
            const response = await fetch("upload_and_generate.php", {
                method: "POST",
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                alert("3D model ready! Loading...");
                load3DModel(result.modelUrl);
            } else {
                alert("Error generating 3D model: " + result.message);
            }
        } catch (err) {
            console.error(err);
            alert("Failed to connect to 3D API.");
        }
    });

    // === NEW: Load generated 3D model into Three.js ===
    function load3DModel(url) {
        const loader = new THREE.GLTFLoader();
        loader.load(url, (gltf) => {
            // Clear previous scene objects
            while(scene.children.length > 0){ 
                scene.remove(scene.children[0]); 
            }
            // Add lights again
            const ambientLight = new THREE.AmbientLight(0x404040);
            scene.add(ambientLight);
            const directionalLight = new THREE.DirectionalLight(0xffffff, 0.5);
            directionalLight.position.set(1, 1, 1);
            scene.add(directionalLight);

            // Add model
            scene.add(gltf.scene);
        }, undefined, (error) => {
            console.error("Error loading 3D model:", error);
        });
    }

    // Handle view modes
    document.getElementById('view-mode').addEventListener('change', function(e) {
        scene.traverse(function(child) {
            if (child.isMesh) {
                if (e.target.value === 'wireframe') {
                    child.material.wireframe = true;
                } else {
                    child.material.wireframe = false;
                }
            }
        });
    });

    // Resize handling
    window.addEventListener('resize', () => {
        camera.aspect = document.getElementById('modeling-container').clientWidth / 
                       document.getElementById('modeling-container').clientHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(
            document.getElementById('modeling-container').clientWidth,
            document.getElementById('modeling-container').clientHeight
        );
    });

    window.onload = init3DView;
</script>
</body>
</html>