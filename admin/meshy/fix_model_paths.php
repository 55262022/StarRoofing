<?php
// Place in: admin/meshy/fix_model_paths.php
// Run once: http://localhost:8000/admin/meshy/fix_model_paths.php

require_once __DIR__ . '/../../database/starroofing_db.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Model Paths</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h2 {
            color: #333;
            border-bottom: 3px solid #e9b949;
            padding-bottom: 10px;
        }
        h3 {
            color: #555;
            margin-top: 30px;
        }
        .record {
            margin: 10px 0;
            padding: 15px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .record strong {
            color: #333;
        }
        .status-fixed {
            color: #28a745;
            font-weight: bold;
        }
        .status-error {
            color: #dc3545;
            font-weight: bold;
        }
        .status-ok {
            color: #17a2b8;
        }
        .summary {
            background: #e9b949;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            color: #1a1a2e;
        }
        .summary p {
            margin: 5px 0;
            font-size: 16px;
        }
        .links {
            margin-top: 30px;
            padding: 20px;
            background: #1a1a2e;
            border-radius: 8px;
        }
        .links a {
            color: #e9b949;
            text-decoration: none;
            margin-right: 20px;
            font-weight: bold;
        }
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<h2>🔧 Fixing Model Paths</h2>

<?php
// Get all models with succeeded status
$query = "SELECT id, model_path, model_filename FROM generated_3d_models WHERE generation_status = 'succeeded'";
$result = $conn->query($query);

$fixed = 0;
$alreadyCorrect = 0;
$errors = [];

while ($row = $result->fetch_assoc()) {
    $oldPath = $row['model_path'];
    $filename = $row['model_filename'];
    
    // Remove any ../ prefix and ensure correct path format
    $cleanFilename = basename($filename);
    $correctPath = 'uploads/3dmodels/' . $cleanFilename;
    
    // Check if file actually exists
    $fullPath = __DIR__ . '/../../uploads/3dmodels/' . $cleanFilename;
    $exists = file_exists($fullPath);
    
    echo '<div class="record">';
    echo "<strong>ID:</strong> {$row['id']}<br>";
    echo "<strong>Old Path:</strong> " . htmlspecialchars($oldPath) . "<br>";
    echo "<strong>New Path:</strong> " . htmlspecialchars($correctPath) . "<br>";
    echo "<strong>Filename:</strong> " . htmlspecialchars($cleanFilename) . "<br>";
    echo "<strong>File Exists:</strong> " . ($exists ? '✅ YES' : '❌ NO') . " ($fullPath)<br>";
    
    if ($oldPath !== $correctPath) {
        // Update the path
        $stmt = $conn->prepare("UPDATE generated_3d_models SET model_path = ? WHERE id = ?");
        $stmt->bind_param("si", $correctPath, $row['id']);
        
        if ($stmt->execute()) {
            echo '<strong>Status:</strong> <span class="status-fixed">✅ FIXED</span>';
            $fixed++;
        } else {
            echo '<strong>Status:</strong> <span class="status-error">❌ ERROR: ' . htmlspecialchars($stmt->error) . '</span>';
            $errors[] = $row['id'];
        }
    } else {
        echo '<strong>Status:</strong> <span class="status-ok">✓ Already correct</span>';
        $alreadyCorrect++;
    }
    
    echo '</div>';
}

echo '<div class="summary">';
echo '<h3>📊 Summary - generated_3d_models Table</h3>';
echo "<p><strong>Total Records Processed:</strong> " . ($fixed + $alreadyCorrect) . "</p>";
echo "<p><strong>Fixed:</strong> $fixed</p>";
echo "<p><strong>Already Correct:</strong> $alreadyCorrect</p>";
if (count($errors) > 0) {
    echo "<p><strong>Errors:</strong> " . implode(', ', $errors) . "</p>";
} else {
    echo "<p><strong>Errors:</strong> None ✅</p>";
}
echo '</div>';

// Also update products table
echo '<h3>🔧 Fixing Products Table</h3>';
$productQuery = "
    SELECT p.product_id, p.model_path, p.model_url, g.model_filename 
    FROM products p
    JOIN generated_3d_models g ON p.generated_model_id = g.id
    WHERE g.generation_status = 'succeeded'
";
$productResult = $conn->query($productQuery);

$fixedProducts = 0;
$alreadyCorrectProducts = 0;

if ($productResult && $productResult->num_rows > 0) {
    while ($row = $productResult->fetch_assoc()) {
        $oldPath = $row['model_path'];
        $oldUrl = $row['model_url'];
        $filename = $row['model_filename'];
        $cleanFilename = basename($filename);
        $correctPath = 'uploads/3dmodels/' . $cleanFilename;
        
        echo '<div class="record">';
        echo "<strong>Product ID:</strong> {$row['product_id']}<br>";
        echo "<strong>Old Path:</strong> " . htmlspecialchars($oldPath ?? 'NULL') . "<br>";
        echo "<strong>New Path:</strong> " . htmlspecialchars($correctPath) . "<br>";
        
        if ($oldPath !== $correctPath || $oldUrl !== $correctPath) {
            $stmt = $conn->prepare("UPDATE products SET model_path = ?, model_url = ? WHERE product_id = ?");
            $stmt->bind_param("ssi", $correctPath, $correctPath, $row['product_id']);
            
            if ($stmt->execute()) {
                echo '<strong>Status:</strong> <span class="status-fixed">✅ Fixed</span>';
                $fixedProducts++;
            } else {
                echo '<strong>Status:</strong> <span class="status-error">❌ Error: ' . htmlspecialchars($stmt->error) . '</span>';
            }
        } else {
            echo '<strong>Status:</strong> <span class="status-ok">✓ Already correct</span>';
            $alreadyCorrectProducts++;
        }
        
        echo '</div>';
    }
} else {
    echo '<div class="record">No products with 3D models found.</div>';
}

echo '<div class="summary">';
echo '<h3>📊 Summary - products Table</h3>';
echo "<p><strong>Total Products Processed:</strong> " . ($fixedProducts + $alreadyCorrectProducts) . "</p>";
echo "<p><strong>Fixed:</strong> $fixedProducts</p>";
echo "<p><strong>Already Correct:</strong> $alreadyCorrectProducts</p>";
echo '</div>';

echo '<div class="links">';
echo '<h3>🔗 Quick Links</h3>';
echo '<a href="../../admin/3dmodel_gallery.php">📁 Go to Gallery</a>';
echo '<a href="../../admin/3dmodel.php">✏️ Go to Editor</a>';
echo '<a href="../../admin/products/manage_products.php">📦 Manage Products</a>';
echo '</div>';
?>

</body>
</html>