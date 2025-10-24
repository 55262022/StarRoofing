<?php
// include '../authentication/auth.php';
require_once '../database/starroofing_db.php';

// Fetch all generated 3D models
$query = "
    SELECT 
        g.*,
        p.name as product_name,
        a.email as created_by_email
    FROM generated_3d_models g
    LEFT JOIN products p ON g.product_id = p.product_id
    LEFT JOIN accounts a ON g.created_by = a.id
    ORDER BY g.created_at DESC
";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>3D Models Gallery — Star Roofing</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: #f5f7fb;
        }
        .model-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: transform 0.2s;
        }
        .model-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        .model-preview {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .model-preview canvas {
            width: 100%;
            height: 100%;
            display: block;
        }
        .badge-status {
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
        }
    </style>

    <!-- Three.js & GLTFLoader -->
    <script type="module">
        import * as THREE from 'https://cdn.jsdelivr.net/npm/three@0.158.0/build/three.module.js';
        import { GLTFLoader } from 'https://cdn.jsdelivr.net/npm/three@0.158.0/examples/jsm/loaders/GLTFLoader.js';

        window.initModelViewer = (canvasId, modelPath) => {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;

            const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
            renderer.setSize(canvas.clientWidth, canvas.clientHeight);
            renderer.setPixelRatio(window.devicePixelRatio);

            const scene = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(45, canvas.clientWidth / canvas.clientHeight, 0.1, 1000);
            camera.position.set(0, 1, 3);

            const light = new THREE.HemisphereLight(0xffffff, 0x444444, 1.2);
            light.position.set(0, 20, 0);
            scene.add(light);

            const loader = new GLTFLoader();
            loader.load(modelPath, (gltf) => {
                const model = gltf.scene;
                scene.add(model);

                // Center and scale model
                const box = new THREE.Box3().setFromObject(model);
                const size = new THREE.Vector3();
                box.getSize(size);
                const maxDim = Math.max(size.x, size.y, size.z);
                model.scale.multiplyScalar(1 / maxDim);
                const center = box.getCenter(new THREE.Vector3());
                model.position.sub(center);

                function animate() {
                    requestAnimationFrame(animate);
                    model.rotation.y += 0.005;
                    renderer.render(scene, camera);
                }
                animate();
            }, undefined, (error) => {
                console.error('Error loading GLB model:', error);
            });
        };
    </script>
</head>
<body>
<div class="container py-4">
<div class="row g-4">
    <!-- Direct GLB model -->
    <div class="col-md-4 col-lg-3">
        <div class="model-card">
            <canvas id="canvas-direct" class="model-preview"></canvas>
            <script>
                initModelViewer("canvas-direct", "uploads/3dmodels/model_019a0d11-a19b-71ac-b972-f25ae4ef9593.glb");
            </script>
            <div class="p-3">
                <h6>Direct Model</h6>
                <div class="d-grid gap-2">
                    <a href="uploads/3dmodels/model_019a0d11-a19b-71ac-b972-f25ae4ef9593.glb" download class="btn btn-sm btn-outline-secondary">
                        <i class="fa fa-download me-1"></i>Download
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if ($result->num_rows === 0): ?>
        <div class="alert alert-info">
            <i class="fa fa-info-circle me-2"></i>
            No 3D models generated yet. Start by uploading images in the 3D Editor!
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-4 col-lg-3">
                    <div class="model-card">
                        <?php if ($row['generation_status'] === 'succeeded' && $row['model_path']): ?>
                            <canvas id="canvas-<?= $row['id'] ?>" class="model-preview"></canvas>
                            <script>
                                initModelViewer("canvas-<?= $row['id'] ?>", "<?= htmlspecialchars($row['model_path']) ?>");
                            </script>
                        <?php else: ?>
                            <div class="model-preview">
                                <i class="fa fa-cube"></i>
                            </div>
                        <?php endif; ?>

                        <div class="p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0 text-truncate" style="flex: 1;">
                                    <?= htmlspecialchars($row['product_name'] ?? $row['original_image_name'] ?? 'Model') ?>
                                </h6>
                                <?php
                                $statusClass = match($row['generation_status']) {
                                    'succeeded' => 'bg-success',
                                    'failed' => 'bg-danger',
                                    default => 'bg-warning'
                                };
                                ?>
                                <span class="badge <?= $statusClass ?> badge-status ms-2">
                                    <?= ucfirst($row['generation_status']) ?>
                                </span>
                            </div>
                            
                            <div class="small text-muted mb-2">
                                <div><i class="fa fa-calendar me-1"></i><?= date('M j, Y', strtotime($row['created_at'])) ?></div>
                                <?php if ($row['file_size']): ?>
                                    <div><i class="fa fa-file me-1"></i><?= number_format($row['file_size'] / 1024, 2) ?> KB</div>
                                <?php endif; ?>
                                <?php if ($row['created_by_email']): ?>
                                    <div class="text-truncate"><i class="fa fa-user me-1"></i><?= htmlspecialchars($row['created_by_email']) ?></div>
                                <?php endif; ?>
                            </div>

                            <?php if ($row['generation_status'] === 'succeeded' && $row['model_path']): ?>
                                <div class="d-grid gap-2">
                                    <a href="3dmodel.php?load=<?= urlencode($row['model_path']) ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fa fa-eye me-1"></i>View in Editor
                                    </a>
                                    <a href="<?= htmlspecialchars($row['model_path']) ?>" 
                                       download 
                                       class="btn btn-sm btn-outline-secondary">
                                        <i class="fa fa-download me-1"></i>Download
                                    </a>
                                </div>
                            <?php elseif ($row['generation_status'] === 'pending'): ?>
                                <button class="btn btn-sm btn-warning w-100" disabled>
                                    <i class="fa fa-spinner fa-spin me-1"></i>Processing...
                                </button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-danger w-100" disabled>
                                    <i class="fa fa-exclamation-triangle me-1"></i>Failed
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
