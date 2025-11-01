<?php
// Place in: admin/meshy/verify_paths.php
// Run anytime: http://localhost:8000/admin/meshy/verify_paths.php

require_once __DIR__ . '/../../database/starroofing_db.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Path Verification</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1400px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f7fb;
        }
        h2 {
            color: #1a1a2e;
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 10px;
        }
        .test-card {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .pass {
            color: #28a745;
            font-weight: bold;
        }
        .fail {
            color: #dc3545;
            font-weight: bold;
        }
        .warn {
            color: #ffc107;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.85em;
        }
        .summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin: 30px 0;
        }
        .summary h3 {
            color: white;
            margin-top: 0;
        }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .stat-box {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-box .number {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-box .label {
            font-size: 0.9em;
            opacity: 0.9;
        }
    </style>
</head>
<body>

<h1>🔍 Path Verification Report</h1>
<p style="color: #6c757d;">Generated: <?= date('Y-m-d H:i:s') ?></p>

<?php

$issues = [];
$warnings = [];
$passed = 0;

// ===================================
// Test 1: Check for ../uploads/ patterns
// ===================================
echo "<div class='test-card'>";
echo "<h2>Test 1: Legacy Path Pattern Check</h2>";

$legacyCheck = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM generated_3d_models WHERE model_path LIKE '../%' OR model_url LIKE '../%') as gen_count,
        (SELECT COUNT(*) FROM products WHERE model_path LIKE '../%' OR model_url LIKE '../%') as prod_count
");
$legacyData = $legacyCheck->fetch_assoc();

if ($legacyData['gen_count'] == 0 && $legacyData['prod_count'] == 0) {
    echo "<p class='pass'>✅ PASS: No legacy '../' patterns found</p>";
    $passed++;
} else {
    echo "<p class='fail'>❌ FAIL: Found legacy patterns</p>";
    echo "<ul>";
    if ($legacyData['gen_count'] > 0) {
        echo "<li>generated_3d_models: {$legacyData['gen_count']} records</li>";
        $issues[] = "Legacy paths in generated_3d_models table";
    }
    if ($legacyData['prod_count'] > 0) {
        echo "<li>products: {$legacyData['prod_count']} records</li>";
        $issues[] = "Legacy paths in products table";
    }
    echo "</ul>";
}
echo "</div>";

// ===================================
// Test 2: Path consistency
// ===================================
echo "<div class='test-card'>";
echo "<h2>Test 2: Path Consistency (model_path vs model_url)</h2>";

$consistencyCheck = $conn->query("
    SELECT id, model_path, model_url 
    FROM generated_3d_models 
    WHERE generation_status = 'succeeded' 
    AND (model_path != model_url OR model_path IS NULL OR model_url IS NULL)
");

if ($consistencyCheck->num_rows == 0) {
    echo "<p class='pass'>✅ PASS: All paths are consistent</p>";
    $passed++;
} else {
    echo "<p class='fail'>❌ FAIL: Found inconsistent paths</p>";
    echo "<table>";
    echo "<tr><th>ID</th><th>model_path</th><th>model_url</th></tr>";
    while ($row = $consistencyCheck->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td><span class='code'>" . htmlspecialchars($row['model_path'] ?? 'NULL') . "</span></td>";
        echo "<td><span class='code'>" . htmlspecialchars($row['model_url'] ?? 'NULL') . "</span></td>";
        echo "</tr>";
        $issues[] = "Inconsistent paths in record ID {$row['id']}";
    }
    echo "</table>";
}
echo "</div>";

// ===================================
// Test 3: File existence verification
// ===================================
echo "<div class='test-card'>";
echo "<h2>Test 3: Physical File Existence</h2>";

$fileCheck = $conn->query("
    SELECT id, model_filename, model_path 
    FROM generated_3d_models 
    WHERE generation_status = 'succeeded'
");

$existingFiles = 0;
$missingFiles = [];

while ($row = $fileCheck->fetch_assoc()) {
    $filename = basename($row['model_filename']);
    $fullPath = __DIR__ . '/../../uploads/3dmodels/' . $filename;
    
    if (file_exists($fullPath)) {
        $existingFiles++;
    } else {
        $missingFiles[] = [
            'id' => $row['id'],
            'filename' => $filename,
            'path' => $fullPath
        ];
    }
}

$totalFiles = $existingFiles + count($missingFiles);

if (count($missingFiles) == 0) {
    echo "<p class='pass'>✅ PASS: All {$totalFiles} files exist</p>";
    $passed++;
} else {
    echo "<p class='warn'>⚠️ WARNING: {$existingFiles}/{$totalFiles} files found</p>";
    echo "<p>Missing files:</p>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Filename</th><th>Expected Path</th></tr>";
    foreach ($missingFiles as $file) {
        echo "<tr>";
        echo "<td>{$file['id']}</td>";
        echo "<td><span class='code'>{$file['filename']}</span></td>";
        echo "<td><span class='code' style='font-size: 0.75em;'>{$file['path']}</span></td>";
        echo "</tr>";
        $warnings[] = "Missing file: {$file['filename']}";
    }
    echo "</table>";
}
echo "</div>";

// ===================================
// Test 4: Path format validation
// ===================================
echo "<div class='test-card'>";
echo "<h2>Test 4: Path Format Validation</h2>";

$formatCheck = $conn->query("
    SELECT id, model_path 
    FROM generated_3d_models 
    WHERE generation_status = 'succeeded' 
    AND model_path NOT LIKE 'uploads/3dmodels/%'
");

if ($formatCheck->num_rows == 0) {
    echo "<p class='pass'>✅ PASS: All paths follow correct format (uploads/3dmodels/)</p>";
    $passed++;
} else {
    echo "<p class='fail'>❌ FAIL: Found incorrect path formats</p>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Path</th><th>Issue</th></tr>";
    while ($row = $formatCheck->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td><span class='code'>" . htmlspecialchars($row['model_path']) . "</span></td>";
        echo "<td>Should start with 'uploads/3dmodels/'</td>";
        echo "</tr>";
        $issues[] = "Incorrect format in record ID {$row['id']}";
    }
    echo "</table>";
}
echo "</div>";

// ===================================
// Test 5: Products-Generated Models Link
// ===================================
echo "<div class='test-card'>";
echo "<h2>Test 5: Products ↔ Generated Models Relationship</h2>";

$linkCheck = $conn->query("
    SELECT 
        p.product_id, 
        p.name,
        p.model_path as product_path,
        g.model_path as generated_path,
        p.generated_model_id
    FROM products p
    LEFT JOIN generated_3d_models g ON p.generated_model_id = g.id
    WHERE p.generated_model_id IS NOT NULL
    AND (p.model_path != g.model_path OR p.model_url != g.model_url)
");

if ($linkCheck->num_rows == 0) {
    echo "<p class='pass'>✅ PASS: All product-model links are synchronized</p>";
    $passed++;
} else {
    echo "<p class='fail'>❌ FAIL: Found mismatched links</p>";
    echo "<table>";
    echo "<tr><th>Product ID</th><th>Name</th><th>Product Path</th><th>Generated Path</th></tr>";
    while ($row = $linkCheck->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['product_id']}</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td><span class='code'>" . htmlspecialchars($row['product_path'] ?? 'NULL') . "</span></td>";
        echo "<td><span class='code'>" . htmlspecialchars($row['generated_path'] ?? 'NULL') . "</span></td>";
        echo "</tr>";
        $issues[] = "Mismatched paths for product ID {$row['product_id']}";
    }
    echo "</table>";
}
echo "</div>";

// ===================================
// Test 6: Duplicate file check
// ===================================
echo "<div class='test-card'>";
echo "<h2>Test 6: Duplicate Model Files</h2>";

$duplicateCheck = $conn->query("
    SELECT model_filename, COUNT(*) as count 
    FROM generated_3d_models 
    WHERE generation_status = 'succeeded'
    GROUP BY model_filename 
    HAVING count > 1
");

if ($duplicateCheck->num_rows == 0) {
    echo "<p class='pass'>✅ PASS: No duplicate files found</p>";
    $passed++;
} else {
    echo "<p class='warn'>⚠️ WARNING: Found duplicate filenames</p>";
    echo "<table>";
    echo "<tr><th>Filename</th><th>Count</th></tr>";
    while ($row = $duplicateCheck->fetch_assoc()) {
        echo "<tr>";
        echo "<td><span class='code'>" . htmlspecialchars($row['model_filename']) . "</span></td>";
        echo "<td>{$row['count']}</td>";
        echo "</tr>";
        $warnings[] = "Duplicate filename: {$row['model_filename']}";
    }
    echo "</table>";
}
echo "</div>";

// ===================================
// Test 7: Directory permissions
// ===================================
echo "<div class='test-card'>";
echo "<h2>Test 7: Directory Permissions</h2>";

$uploadDir = __DIR__ . '/../../uploads/3dmodels/';

echo "<table>";
echo "<tr><th>Check</th><th>Status</th></tr>";

// Check if directory exists
echo "<tr>";
echo "<td>Directory exists</td>";
if (is_dir($uploadDir)) {
    echo "<td class='pass'>✅ YES</td>";
} else {
    echo "<td class='fail'>❌ NO</td>";
    $issues[] = "Upload directory does not exist: $uploadDir";
}
echo "</tr>";

// Check if readable
echo "<tr>";
echo "<td>Directory readable</td>";
if (is_readable($uploadDir)) {
    echo "<td class='pass'>✅ YES</td>";
} else {
    echo "<td class='fail'>❌ NO</td>";
    $issues[] = "Upload directory is not readable";
}
echo "</tr>";

// Check if writable
echo "<tr>";
echo "<td>Directory writable</td>";
if (is_writable($uploadDir)) {
    echo "<td class='pass'>✅ YES</td>";
    $passed++;
} else {
    echo "<td class='fail'>❌ NO</td>";
    $issues[] = "Upload directory is not writable";
}
echo "</tr>";

echo "</table>";
echo "<p class='code' style='padding: 10px; background: #f8f9fa;'>Path: $uploadDir</p>";
echo "</div>";

// ===================================
// SUMMARY
// ===================================
$totalTests = 7;
$failedTests = count($issues) > 0 ? (count(array_unique(array_map(function($i) { 
    return explode(':', $i)[0]; 
}, $issues)))) : 0;

echo "<div class='summary'>";
echo "<h3>📊 Verification Summary</h3>";

echo "<div class='stat-grid'>";

echo "<div class='stat-box'>";
echo "<div class='number'>{$totalTests}</div>";
echo "<div class='label'>Total Tests</div>";
echo "</div>";

echo "<div class='stat-box'>";
echo "<div class='number' style='color: #d4edda;'>{$passed}</div>";
echo "<div class='label'>Passed</div>";
echo "</div>";

echo "<div class='stat-box'>";
echo "<div class='number' style='color: #f8d7da;'>" . count($issues) . "</div>";
echo "<div class='label'>Issues Found</div>";
echo "</div>";

echo "<div class='stat-box'>";
echo "<div class='number' style='color: #fff3cd;'>" . count($warnings) . "</div>";
echo "<div class='label'>Warnings</div>";
echo "</div>";

echo "</div>";

if (count($issues) == 0 && count($warnings) == 0) {
    echo "<div style='margin-top: 20px; padding: 15px; background: rgba(255,255,255,0.2); border-radius: 8px;'>";
    echo "<h4 style='margin: 0; color: white;'>🎉 All Clear!</h4>";
    echo "<p style='margin: 10px 0 0 0;'>Your 3D model paths are configured correctly and all files are accessible.</p>";
    echo "</div>";
} else {
    if (count($issues) > 0) {
        echo "<div style='margin-top: 20px; padding: 15px; background: rgba(255,0,0,0.2); border-radius: 8px;'>";
        echo "<h4 style='margin: 0; color: white;'>⚠️ Issues Detected</h4>";
        echo "<ul style='margin: 10px 0 0 20px;'>";
        foreach (array_unique($issues) as $issue) {
            echo "<li>" . htmlspecialchars($issue) . "</li>";
        }
        echo "</ul>";
        echo "<p style='margin: 10px 0 0 0;'><strong>Action:</strong> Run <a href='complete_path_fix.php' style='color: #ffc107;'>complete_path_fix.php</a> to fix these issues.</p>";
        echo "</div>";
    }
    
    if (count($warnings) > 0) {
        echo "<div style='margin-top: 20px; padding: 15px; background: rgba(255,193,7,0.2); border-radius: 8px;'>";
        echo "<h4 style='margin: 0; color: white;'>⚠️ Warnings</h4>";
        echo "<ul style='margin: 10px 0 0 20px;'>";
        foreach (array_unique($warnings) as $warning) {
            echo "<li>" . htmlspecialchars($warning) . "</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
}

echo "</div>";

// ===================================
// Quick Actions
// ===================================
echo "<div style='margin-top: 30px; padding: 25px; background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);'>";
echo "<h3 style='margin-top: 0;'>🔧 Quick Actions</h3>";
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;'>";

if (count($issues) > 0) {
    echo "<a href='complete_path_fix.php' style='padding: 15px; background: #dc3545; color: white; text-decoration: none; border-radius: 8px; text-align: center; font-weight: bold;'>";
    echo "🔧 Fix All Issues";
    echo "</a>";
}

echo "<a href='verify_paths.php' style='padding: 15px; background: #0d6efd; color: white; text-decoration: none; border-radius: 8px; text-align: center; font-weight: bold;'>";
echo "🔄 Re-run Verification";
echo "</a>";

echo "<a href='../../admin/3dmodel_gallery.php' style='padding: 15px; background: #28a745; color: white; text-decoration: none; border-radius: 8px; text-align: center; font-weight: bold;'>";
echo "📁 View Gallery";
echo "</a>";

echo "<a href='../../admin/3dmodel.php' style='padding: 15px; background: #6f42c1; color: white; text-decoration: none; border-radius: 8px; text-align: center; font-weight: bold;'>";
echo "✏️ Open Editor";
echo "</a>";

echo "</div>";
echo "</div>";

// ===================================
// Detailed file listing
// ===================================
echo "<div class='test-card'>";
echo "<h2>📋 All Generated Models</h2>";

$allModels = $conn->query("
    SELECT 
        g.id,
        g.model_filename,
        g.model_path,
        g.file_size,
        g.created_at,
        p.name as product_name
    FROM generated_3d_models g
    LEFT JOIN products p ON g.product_id = p.product_id
    WHERE g.generation_status = 'succeeded'
    ORDER BY g.id DESC
");

echo "<table>";
echo "<tr><th>ID</th><th>Product</th><th>Filename</th><th>Path</th><th>Size</th><th>Exists</th></tr>";

while ($row = $allModels->fetch_assoc()) {
    $filename = basename($row['model_filename']);
    $fullPath = __DIR__ . '/../../uploads/3dmodels/' . $filename;
    $exists = file_exists($fullPath);
    
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>" . htmlspecialchars($row['product_name'] ?? 'N/A') . "</td>";
    echo "<td><span class='code'>{$filename}</span></td>";
    echo "<td><span class='code'>" . htmlspecialchars($row['model_path']) . "</span></td>";
    echo "<td>" . ($row['file_size'] ? number_format($row['file_size'] / 1024, 2) . ' KB' : 'N/A') . "</td>";
    echo "<td>" . ($exists ? '<span class="pass">✅</span>' : '<span class="fail">❌</span>') . "</td>";
    echo "</tr>";
}

echo "</table>";
echo "</div>";

?>

<div style="margin-top: 30px; padding: 15px; background: #e3f2fd; border-left: 4px solid #0d6efd; border-radius: 6px;">
    <strong>💡 Tip:</strong> Bookmark this page to quickly verify your 3D model paths after making changes or uploading new models.
</div>

</body>
</html>