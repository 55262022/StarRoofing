<?php
// Place in: admin/meshy/fix_model_paths.php
// Run once: http://localhost:8000/admin/meshy/fix_model_paths.php

require_once __DIR__ . '/../../database/starroofing_db.php';

echo "<h2>Fixing Model Paths</h2>";

// Get all models with succeeded status
$query = "SELECT id, model_path, model_filename FROM generated_3d_models WHERE generation_status = 'succeeded'";
$result = $conn->query($query);

$fixed = 0;
$errors = [];

while ($row = $result->fetch_assoc()) {
    $oldPath = $row['model_path'];
    $filename = $row['model_filename'];
    
    // Correct path should be: ../uploads/3dmodels/filename.glb
    $correctPath = '../uploads/3dmodels/' . $filename;
    
    // Check if file actually exists
    $fullPath = __DIR__ . '/../../uploads/3dmodels/' . $filename;
    $exists = file_exists($fullPath);
    
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
    echo "<strong>ID:</strong> {$row['id']}<br>";
    echo "<strong>Old Path:</strong> $oldPath<br>";
    echo "<strong>New Path:</strong> $correctPath<br>";
    echo "<strong>File Exists:</strong> " . ($exists ? '✅ YES' : '❌ NO') . "<br>";
    
    if ($oldPath !== $correctPath) {
        // Update the path
        $stmt = $conn->prepare("UPDATE generated_3d_models SET model_path = ? WHERE id = ?");
        $stmt->bind_param("si", $correctPath, $row['id']);
        
        if ($stmt->execute()) {
            echo "<strong>Status:</strong> <span style='color: green;'>✅ FIXED</span>";
            $fixed++;
        } else {
            echo "<strong>Status:</strong> <span style='color: red;'>❌ ERROR: " . $stmt->error . "</span>";
            $errors[] = $row['id'];
        }
    } else {
        echo "<strong>Status:</strong> ✓ Already correct";
    }
    
    echo "</div>";
}

echo "<h3>Summary</h3>";
echo "<p><strong>Total Fixed:</strong> $fixed</p>";
if (count($errors) > 0) {
    echo "<p><strong>Errors:</strong> " . implode(', ', $errors) . "</p>";
}

// Also update products table
echo "<h3>Fixing Products Table</h3>";
$productQuery = "
    SELECT p.product_id, p.model_path, g.model_filename 
    FROM products p
    JOIN generated_3d_models g ON p.generated_model_id = g.id
    WHERE g.generation_status = 'succeeded'
";
$productResult = $conn->query($productQuery);

$fixedProducts = 0;
while ($row = $productResult->fetch_assoc()) {
    $oldPath = $row['model_path'];
    $filename = $row['model_filename'];
    $correctPath = '../uploads/3dmodels/' . $filename;
    
    if ($oldPath !== $correctPath) {
        $stmt = $conn->prepare("UPDATE products SET model_path = ? WHERE product_id = ?");
        $stmt->bind_param("si", $correctPath, $row['product_id']);
        
        if ($stmt->execute()) {
            echo "Product {$row['product_id']}: ✅ Fixed<br>";
            $fixedProducts++;
        }
    }
}

echo "<p><strong>Products Fixed:</strong> $fixedProducts</p>";

echo "<hr>";
echo "<p><a href='../../admin/3dmodel_gallery.php'>Go to Gallery</a> | <a href='../../admin/3dmodel.php'>Go to Editor</a></p>";
?>