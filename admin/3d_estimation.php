<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Automated Estimation - Star Roofing & Construction</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        :root {
            --primary: #3498db;
            --primary-dark: #2980b9;
            --secondary: #2c3e50;
            --success: #2ecc71;
            --danger: #e74c3c;
            --warning: #f39c12;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --gray: #7f8c8d;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }
        
        body {
            background-color: #f5f7f9;
            color: #2c3e50;
        }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: var(--secondary);
            color: white;
            padding: 20px 0;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-logo {
            font-size: 24px;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-logo i {
            color: var(--primary);
        }
        
        .sidebar-menu {
            list-style: none;
            margin-top: 20px;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
            border-left: 4px solid var(--primary);
        }
        
        .sidebar-menu a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .top-navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .breadcrumb {
            font-size: 14px;
            color: var(--gray);
        }
        
        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
        }
        
        .user-profile {
            position: relative;
            cursor: pointer;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .user-name {
            font-weight: 500;
        }
        
        /* Estimation Content */
        .estimation-content {
            flex: 1;
            padding: 30px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--dark);
            margin: 0 0 5px 0;
        }
        
        .page-description {
            color: var(--gray);
            margin: 0;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid #bdc3c7;
            color: var(--gray);
        }
        
        .btn-outline:hover {
            background-color: #f8f9fa;
        }
        
        /* Estimation Layout */
        .estimation-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        @media (max-width: 1200px) {
            .estimation-layout {
                grid-template-columns: 1fr;
            }
        }
        
        /* Input Panel */
        .input-panel {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .panel-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus, .form-group select:focus {
            border-color: var(--primary);
            outline: none;
        }
        
        .dimension-inputs {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        /* 3D Model Container */
        .model-container {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            height: 400px;
            position: relative;
        }
        
        #model-viewer {
            width: 100%;
            height: 100%;
            border-radius: 6px;
            background-color: #f8f9fa;
        }
        
        .model-controls {
            position: absolute;
            bottom: 15px;
            right: 15px;
            display: flex;
            gap: 10px;
        }
        
        .model-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: white;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .model-btn:hover {
            background-color: var(--primary);
            color: white;
        }
        
        /* Material Selection */
        .material-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .material-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        
        .material-card:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .material-card.selected {
            border-color: var(--primary);
            background-color: #e8f4fc;
        }
        
        .material-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        
        .material-name {
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .material-price {
            color: var(--primary);
            font-weight: 600;
        }
        
        .material-stock {
            font-size: 12px;
            color: var(--gray);
        }
        
        /* Results Panel */
        .results-panel {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            grid-column: 1 / -1;
            margin-top: 20px;
        }
        
        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .result-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
        }
        
        .result-label {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 5px;
        }
        
        .result-value {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .result-value.total {
            color: var(--primary);
            font-size: 24px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
            justify-content: flex-end;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .dimension-inputs {
                grid-template-columns: 1fr;
            }
            
            .material-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <a href="#" class="sidebar-logo">
                    <i class="fas fa-home"></i>
                    <span>Star Roofing</span>
                </a>
            </div>
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="inventory.php">
                        <i class="fas fa-boxes"></i>
                        <span>Inventory</span>
                    </a>
                </li>
                <li>
                    <a href="estimation.php" class="active">
                        <i class="fas fa-calculator"></i>
                        <span>Estimation</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-users-cog"></i>
                        <span>Admin Accounts</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation -->
            <nav class="top-navbar">
                <div class="breadcrumb">
                    <a href="dashboard.php">Dashboard</a> <span>/</span> <span>Automated Estimation</span>
                </div>
                
                <div class="user-profile">
                    <div class="user-info">
                        <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=80" alt="Admin" class="user-avatar">
                        <div class="user-name">Admin User</div>
                    </div>
                </div>
            </nav>
            
            <!-- Estimation Content -->
            <div class="estimation-content">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Automated Estimation</h1>
                        <p class="page-description">Create accurate project estimates with 3D visualization</p>
                    </div>
                    <button class="btn btn-primary">
                        <i class="fas fa-file-pdf"></i> Export Estimate
                    </button>
                </div>
                
                <div class="estimation-layout">
                    <!-- Input Panel -->
                    <div class="input-panel">
                        <h2 class="panel-title">Project Dimensions</h2>
                        
                        <div class="form-group">
                            <label for="projectType">Project Type</label>
                            <select id="projectType">
                                <option value="roofing">Roofing</option>
                                <option value="siding">Siding</option>
                                <option value="decking">Decking</option>
                                <option value="fencing">Fencing</option>
                                <option value="custom">Custom Structure</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Dimensions (in meters)</label>
                            <div class="dimension-inputs">
                                <div class="form-group">
                                    <input type="number" id="length" placeholder="Length" min="1" step="0.1" value="10">
                                </div>
                                <div class="form-group">
                                    <input type="number" id="width" placeholder="Width" min="1" step="0.1" value="8">
                                </div>
                                <div class="form-group">
                                    <input type="number" id="height" placeholder="Height" min="1" step="0.1" value="3">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="pitch">Roof Pitch (degrees)</label>
                            <input type="number" id="pitch" placeholder="Roof pitch angle" min="0" max="60" value="30">
                        </div>
                        
                        <h2 class="panel-title" style="margin-top: 30px;">Material Selection</h2>
                        
                        <div class="material-grid" id="materialGrid">
                            <!-- Materials will be populated by JavaScript -->
                        </div>
                    </div>
                    
                    <!-- 3D Model Container -->
                    <div class="model-container">
                        <h2 class="panel-title">3D Model Visualization</h2>
                        <canvas id="model-viewer"></canvas>
                        <div class="model-controls">
                            <div class="model-btn" id="rotate-btn">
                                <i class="fas fa-sync-alt"></i>
                            </div>
                            <div class="model-btn" id="zoom-in-btn">
                                <i class="fas fa-search-plus"></i>
                            </div>
                            <div class="model-btn" id="zoom-out-btn">
                                <i class="fas fa-search-minus"></i>
                            </div>
                            <div class="model-btn" id="reset-btn">
                                <i class="fas fa-expand"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Results Panel -->
                    <div class="results-panel">
                        <h2 class="panel-title">Estimation Results</h2>
                        
                        <div class="results-grid">
                            <div class="result-card">
                                <div class="result-label">Surface Area</div>
                                <div class="result-value" id="surfaceArea">0 sqm</div>
                            </div>
                            
                            <div class="result-card">
                                <div class="result-label">Material Cost</div>
                                <div class="result-value" id="materialCost">₱0.00</div>
                            </div>
                            
                            <div class="result-card">
                                <div class="result-label">Labor Cost</div>
                                <div class="result-value" id="laborCost">₱0.00</div>
                            </div>
                            
                            <div class="result-card">
                                <div class="result-label">Total Estimated Cost</div>
                                <div class="result-value total" id="totalCost">₱0.00</div>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <button class="btn btn-outline">
                                <i class="fas fa-times"></i> Clear
                            </button>
                            <button class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Estimate
                            </button>
                            <button class="btn btn-primary">
                                <i class="fas fa-shopping-cart"></i> Order Materials
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Load Babylon.js -->
    <script src="https://cdn.babylonjs.com/babylon.js"></script>
    <script src="https://cdn.babylonjs.com/loaders/babylonjs.loaders.min.js"></script>
    <script src="https://cdn.babylonjs.com/materialsLibrary/babylonjs.materials.min.js"></script>

    <script>
        // Sample material data (in a real app, this would come from your database)
        const materials = [
            {
                id: 1,
                name: 'Metal Roofing',
                category: 'roofing',
                price: 450,
                unit: 'sqm',
                thumbnail: 'https://images.unsplash.com/photo-1591955506264-3f5a6834570a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=300&q=80',
                stock: 150,
                laborCost: 200
            },
            {
                id: 2,
                name: 'Concrete Tiles',
                category: 'roofing',
                price: 600,
                unit: 'sqm',
                thumbnail: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=300&q=80',
                stock: 85,
                laborCost: 250
            },
            {
                id: 3,
                name: 'Asphalt Shingles',
                category: 'roofing',
                price: 350,
                unit: 'sqm',
                thumbnail: 'https://images.unsplash.com/photo-1623298317882-5e2e4e234d13?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=300&q=80',
                stock: 200,
                laborCost: 180
            },
            {
                id: 4,
                name: 'Wood Siding',
                category: 'siding',
                price: 700,
                unit: 'sqm',
                thumbnail: 'https://images.unsplash.com/photo-1567495243894-2c367bce35dc?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=300&q=80',
                stock: 60,
                laborCost: 300
            },
            {
                id: 5,
                name: 'Vinyl Siding',
                category: 'siding',
                price: 400,
                unit: 'sqm',
                thumbnail: 'https://images.unsplash.com/photo-1620622930643-1bb1872367d8?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=300&q=80',
                stock: 120,
                laborCost: 220
            }
        ];

        // Babylon.js variables
        let engine, scene, camera, house;
        let isRotating = false;
        let animationFrameId;
        
        // Initialize Babylon.js
        function initBabylon() {
            const canvas = document.getElementById("model-viewer");
            engine = new BABYLON.Engine(canvas, true);
            
            // Create scene
            scene = new BABYLON.Scene(engine);
            scene.clearColor = new BABYLON.Color3(0.95, 0.95, 0.95);
            
            // Create camera
            camera = new BABYLON.ArcRotateCamera("camera", -Math.PI / 2, Math.PI / 2, 15, BABYLON.Vector3.Zero(), scene);
            camera.attachControl(canvas, true);
            camera.lowerRadiusLimit = 5;
            camera.upperRadiusLimit = 50;
            
            // Add lights
            const light = new BABYLON.HemisphericLight("light", new BABYLON.Vector3(0, 1, 0), scene);
            light.intensity = 0.7;
            
            const directionalLight = new BABYLON.DirectionalLight("directionalLight", new BABYLON.Vector3(0, -1, -1), scene);
            directionalLight.intensity = 0.5;
            
            // Create initial model
            updateModel();
            
            // Handle window resize
            window.addEventListener("resize", function () {
                engine.resize();
            });
            
            // Run the render loop
            engine.runRenderLoop(function () {
                scene.render();
            });
        }
        
        function updateModel() {
            // Remove existing house
            if (house) {
                house.dispose();
            }
            
            // Get dimensions
            const length = parseFloat(document.getElementById('length').value) || 10;
            const width = parseFloat(document.getElementById('width').value) || 8;
            const height = parseFloat(document.getElementById('height').value) || 3;
            const pitch = parseFloat(document.getElementById('pitch').value) || 30;
            const projectType = document.getElementById('projectType').value;
            
            // Create new model based on project type
            if (projectType === 'roofing') {
                createRoofModel(length, width, height, pitch);
            } else if (projectType === 'siding') {
                createWallModel(length, width, height);
            } else if (projectType === 'decking') {
                createDeckModel(length, width, height);
            } else if (projectType === 'fencing') {
                createFenceModel(length, width, height);
            } else {
                createCustomModel(length, width, height);
            }
            
            // Update calculations
            calculateEstimate();
        }
        
        function createRoofModel(length, width, height, pitch) {
            // Create a parent mesh to hold the house
            house = new BABYLON.Mesh("house", scene);
            
            // Create building base
            const base = BABYLON.MeshBuilder.CreateBox("base", {
                width: length,
                height: 0.5,
                depth: width
            }, scene);
            base.position.y = -height/2 - 0.25;
            base.material = createMaterial("#7f8c8d");
            base.parent = house;
            
            // Create walls
            const wallMaterial = createMaterial("#95a5a6");
            
            // Front wall
            const frontWall = BABYLON.MeshBuilder.CreateBox("frontWall", {
                width: length,
                height: height,
                depth: 0.5
            }, scene);
            frontWall.position = new BABYLON.Vector3(0, 0, -width/2);
            frontWall.material = wallMaterial;
            frontWall.parent = house;
            
            // Back wall
            const backWall = BABYLON.MeshBuilder.CreateBox("backWall", {
                width: length,
                height: height,
                depth: 0.5
            }, scene);
            backWall.position = new BABYLON.Vector3(0, 0, width/2);
            backWall.material = wallMaterial;
            backWall.parent = house;
            
            // Left wall
            const leftWall = BABYLON.MeshBuilder.CreateBox("leftWall", {
                width: 0.5,
                height: height,
                depth: width
            }, scene);
            leftWall.position = new BABYLON.Vector3(-length/2, 0, 0);
            leftWall.material = wallMaterial;
            leftWall.parent = house;
            
            // Right wall
            const rightWall = BABYLON.MeshBuilder.CreateBox("rightWall", {
                width: 0.5,
                height: height,
                depth: width
            }, scene);
            rightWall.position = new BABYLON.Vector3(length/2, 0, 0);
            rightWall.material = wallMaterial;
            rightWall.parent = house;
            
            // Create roof (gabled roof)
            const roofHeight = (width/2) * Math.tan(pitch * Math.PI/180);
            
            // Create roof using a custom shape
            const roof = BABYLON.MeshBuilder.CreateCylinder("roof", {
                diameterTop: 0,
                diameterBottom: width * 1.2,
                tessellation: 3,
                height: length
            }, scene);
            
            roof.rotation.x = Math.PI / 2;
            roof.rotation.z = Math.PI / 2;
            roof.position.y = height/2;
            roof.scaling.x = 1;
            roof.scaling.y = roofHeight / (width/2) * 2;
            roof.material = createMaterial("#e74c3c");
            roof.parent = house;
            
            // Calculate surface area for roofing (both sides of the roof)
            const roofArea = length * Math.sqrt(Math.pow(width/2, 2) + Math.pow(roofHeight, 2)) * 2;
            return roofArea;
        }
        
        function createWallModel(length, width, height) {
            // Create a simple wall
            house = BABYLON.MeshBuilder.CreateBox("wall", {
                width: length,
                height: height,
                depth: 0.2
            }, scene);
            house.material = createMaterial("#3498db");
            
            // Return wall area for calculation
            return length * height;
        }
        
        function createDeckModel(length, width, height) {
            // Create a deck
            house = BABYLON.MeshBuilder.CreateBox("deck", {
                width: length,
                height: 0.2,
                depth: width
            }, scene);
            house.position.y = -0.1;
            house.material = createMaterial("#8e44ad");
            
            // Return deck area for calculation
            return length * width;
        }
        
        function createFenceModel(length, width, height) {
            // Create a fence
            house = BABYLON.MeshBuilder.CreateBox("fence", {
                width: length,
                height: height,
                depth: 0.1
            }, scene);
            house.material = createMaterial("#f1c40f");
            
            // Return fence area for calculation
            return length * height;
        }
        
        function createCustomModel(length, width, height) {
            // Simple box model for custom projects
            house = BABYLON.MeshBuilder.CreateBox("custom", {
                width: length,
                height: height,
                depth: width
            }, scene);
            
            const material = createMaterial("#2ecc71");
            material.wireframe = true;
            house.material = material;
            
            // Return surface area for calculation
            return 2 * (length * width + length * height + width * height);
        }
        
        function createMaterial(color) {
            const material = new BABYLON.StandardMaterial("mat", scene);
            material.diffuseColor = BABYLON.Color3.FromHexString(color);
            material.specularColor = BABYLON.Color3.Black();
            return material;
        }
        
        function calculateEstimate() {
            const length = parseFloat(document.getElementById('length').value) || 10;
            const width = parseFloat(document.getElementById('width').value) || 8;
            const height = parseFloat(document.getElementById('height').value) || 3;
            const pitch = parseFloat(document.getElementById('pitch').value) || 30;
            const projectType = document.getElementById('projectType').value;
            
            let surfaceArea = 0;
            
            // Calculate surface area based on project type
            switch(projectType) {
                case 'roofing':
                    const roofHeight = (width/2) * Math.tan(pitch * Math.PI/180);
                    surfaceArea = length * Math.sqrt(Math.pow(width/2, 2) + Math.pow(roofHeight, 2)) * 2;
                    break;
                case 'siding':
                    // All four walls minus openings (simplified)
                    surfaceArea = 2 * (length * height) + 2 * (width * height);
                    // Assume 20% of area is openings (windows, doors)
                    surfaceArea *= 0.8;
                    break;
                case 'decking':
                    surfaceArea = length * width;
                    break;
                case 'fencing':
                    surfaceArea = length * height;
                    break;
                case 'custom':
                    surfaceArea = 2 * (length * width + length * height + width * height);
                    break;
            }
            
            // Get selected material
            const selectedMaterial = materials.find(m => m.id === parseInt(localStorage.getItem('selectedMaterial') || 1));
            
            // Calculate costs
            const materialCost = surfaceArea * selectedMaterial.price;
            const laborCost = surfaceArea * selectedMaterial.laborCost;
            const totalCost = materialCost + laborCost;
            
            // Update UI
            document.getElementById('surfaceArea').textContent = `${surfaceArea.toFixed(2)} sqm`;
            document.getElementById('materialCost').textContent = `₱${materialCost.toFixed(2)}`;
            document.getElementById('laborCost').textContent = `₱${laborCost.toFixed(2)}`;
            document.getElementById('totalCost').textContent = `₱${totalCost.toFixed(2)}`;
            
            // Check inventory
            if (selectedMaterial.stock < surfaceArea) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Low Inventory',
                    text: `Only ${selectedMaterial.stock} ${selectedMaterial.unit} available. You need ${surfaceArea.toFixed(2)} ${selectedMaterial.unit}.`,
                    confirmButtonText: 'OK'
                });
            }
        }
        
        function populateMaterials() {
            const materialGrid = document.getElementById('materialGrid');
            materialGrid.innerHTML = '';
            
            const projectType = document.getElementById('projectType').value;
            const filteredMaterials = materials.filter(m => m.category === projectType);
            
            if (filteredMaterials.length === 0) {
                // Show all materials if none match the category
                filteredMaterials = materials;
            }
            
            filteredMaterials.forEach(material => {
                const isSelected = localStorage.getItem('selectedMaterial') == material.id;
                
                const materialCard = document.createElement('div');
                materialCard.className = `material-card ${isSelected ? 'selected' : ''}`;
                materialCard.dataset.id = material.id;
                materialCard.innerHTML = `
                    <img src="${material.thumbnail}" alt="${material.name}" class="material-thumb">
                    <div class="material-name">${material.name}</div>
                    <div class="material-price">₱${material.price}/${material.unit}</div>
                    <div class="material-stock">Stock: ${material.stock} ${material.unit}</div>
                `;
                
                materialCard.addEventListener('click', () => {
                    document.querySelectorAll('.material-card').forEach(card => {
                        card.classList.remove('selected');
                    });
                    materialCard.classList.add('selected');
                    localStorage.setItem('selectedMaterial', material.id);
                    calculateEstimate();
                });
                
                materialGrid.appendChild(materialCard);
            });
            
            // Select first material by default if none selected
            if (!localStorage.getItem('selectedMaterial') && filteredMaterials.length > 0) {
                localStorage.setItem('selectedMaterial', filteredMaterials[0].id);
                materialGrid.querySelector('.material-card').classList.add('selected');
            }
        }
        
        // Event listeners
        document.getElementById('length').addEventListener('input', updateModel);
        document.getElementById('width').addEventListener('input', updateModel);
        document.getElementById('height').addEventListener('input', updateModel);
        document.getElementById('pitch').addEventListener('input', updateModel);
        document.getElementById('projectType').addEventListener('change', function() {
            populateMaterials();
            updateModel();
        });
        
        document.getElementById('rotate-btn').addEventListener('click', function() {
            isRotating = !isRotating;
            this.style.backgroundColor = isRotating ? '#3498db' : '';
            this.style.color = isRotating ? 'white' : '';
            
            if (isRotating && house) {
                // Start rotation
                scene.registerBeforeRender(function() {
                    if (isRotating) {
                        house.rotation.y += 0.01;
                    }
                });
            }
        });
        
        document.getElementById('zoom-in-btn').addEventListener('click', function() {
            camera.radius -= 1;
        });
        
        document.getElementById('zoom-out-btn').addEventListener('click', function() {
            camera.radius += 1;
        });
        
        document.getElementById('reset-btn').addEventListener('click', function() {
            camera.setPosition(new BABYLON.Vector3(0, 0, 15));
            camera.setTarget(BABYLON.Vector3.Zero());
            if (house) {
                house.rotation = BABYLON.Vector3.Zero();
            }
            isRotating = false;
            document.getElementById('rotate-btn').style.backgroundColor = '';
            document.getElementById('rotate-btn').style.color = '';
        });
        
        // Initialize the application
        window.addEventListener('load', function() {
            initBabylon();
            populateMaterials();
            calculateEstimate();
        });
    </script>
</body>
</html>