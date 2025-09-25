<?php
// filepath: c:\xampp\htdocs\starroofing\admin\3D_model.php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['length'], $_POST['width'], $_POST['height'], $_POST['material'])) {
    // API endpoint for estimation
    $length = floatval($_POST['length']);
    $width = floatval($_POST['width']);
    $height = floatval($_POST['height']);
    $material = $_POST['material'];

    $volume = $length * $width * $height;
    $rates = ['brick' => 5000, 'concrete' => 7000];
    $cost = $volume * ($rates[$material] ?? 0);

    header('Content-Type: application/json');
    echo json_encode([
        'volume' => round($volume, 2),
        'cost' => round($cost, 2),
        'material' => $material
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Roofing Estimation 3D Model</title>
  <style>
    body { margin: 0; overflow: hidden; }
    #info {
      position: absolute; top: 10px; left: 10px;
      background: rgba(255,255,255,0.9); padding: 10px; border-radius: 8px;
      font-family: Arial, sans-serif;
      z-index: 10;
    }
    #info input, #info select, #info button { margin: 4px 0; }
  </style>
</head>
<body>
  <div id="info">
    <form id="estimateForm">
      <label>Length (m): <input type="number" step="0.01" id="length" value="5" required></label><br>
      <label>Width (m): <input type="number" step="0.01" id="width" value="3" required></label><br>
      <label>Height (m): <input type="number" step="0.01" id="height" value="2.5" required></label><br>
      <label>Material:
        <select id="material">
          <option value="brick">Brick</option>
          <option value="concrete">Concrete</option>
        </select>
      </label><br>
      <button type="submit">Estimate</button>
    </form>
    <p id="estimate">Volume: 0 | Cost: 0</p>
  </div>

  <!-- Three.js & GLTFLoader -->
  <script src="https://cdn.jsdelivr.net/npm/three@0.152.0/build/three.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/three@0.152.0/examples/js/controls/OrbitControls.js"></script>

  <script>
    let scene, camera, renderer, controls, wallMesh;

    const textureLoader = new THREE.TextureLoader();
    const materials = {
      brick: new THREE.MeshStandardMaterial({ color: 0xb97a56 }),
      concrete: new THREE.MeshStandardMaterial({ color: 0xcccccc })
    };

    function createWall(length, width, height, materialType) {
      if (wallMesh) scene.remove(wallMesh);
      const geometry = new THREE.BoxGeometry(length, height, width);
      wallMesh = new THREE.Mesh(geometry, materials[materialType]);
      wallMesh.position.y = height / 2;
      scene.add(wallMesh);
    }

    function init() {
      scene = new THREE.Scene();
      scene.background = new THREE.Color(0xf0f0f0);

      camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 0.1, 1000);
      camera.position.set(6, 6, 8);

      renderer = new THREE.WebGLRenderer({ antialias: true });
      renderer.setSize(window.innerWidth, window.innerHeight);
      document.body.appendChild(renderer.domElement);

      controls = new THREE.OrbitControls(camera, renderer.domElement);

      // Lights
      const light = new THREE.HemisphereLight(0xffffff, 0x444444, 1.2);
      light.position.set(0, 20, 0);
      scene.add(light);

      // Initial wall
      createWall(5, 3, 2.5, 'brick');

      animate();
      window.addEventListener('resize', onWindowResize);
    }

    function onWindowResize() {
      camera.aspect = window.innerWidth / window.innerHeight;
      camera.updateProjectionMatrix();
      renderer.setSize(window.innerWidth, window.innerHeight);
    }

    function animate() {
      requestAnimationFrame(animate);
      renderer.render(scene, camera);
    }

    // Handle estimation form
    document.addEventListener('DOMContentLoaded', function() {
      init();

      document.getElementById('estimateForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const length = parseFloat(document.getElementById('length').value);
        const width = parseFloat(document.getElementById('width').value);
        const height = parseFloat(document.getElementById('height').value);
        const material = document.getElementById('material').value;

        // Update 3D model
        createWall(length, width, height, material);

        // Fetch estimation from PHP API
        fetch('', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `length=${length}&width=${width}&height=${height}&material=${material}`
        })
        .then(res => res.json())
        .then(data => {
          document.getElementById('estimate').innerText =
            `Volume: ${data.volume} m³ | Cost: ₱${data.cost}`;
        });
      });
    });
  </script>
</body>
</html>