<?php
ob_start();
ob_clean();

header('Content-Type: application/json');

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../database/starroofing_db.php';

function logDebug($message, $data = null) {
    $logFile = __DIR__ . '/debug_' . date('Y-m-d') . '.log';
    $entry = date('H:i:s') . " - $message";
    if ($data) {
        $entry .= " | " . json_encode($data);
    }
    file_put_contents($logFile, $entry . "\n", FILE_APPEND);
}

try {
    logDebug("=== meshy_upload.php started ===");
    
    // Load .env
    $envFile = __DIR__ . '/../../.env';
    if (!file_exists($envFile)) {
        throw new Exception('.env file not found');
    }
    
    $envContent = file_get_contents($envFile);
    $lines = explode("\n", $envContent);
    
    $apiKey = null;
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        if (strpos($line, 'MESHY_API_KEY=') === 0) {
            $apiKey = trim(str_replace('MESHY_API_KEY=', '', $line));
            break;
        }
    }
    
    if (!$apiKey) {
        throw new Exception('MESHY_API_KEY not found in .env');
    }
    
    logDebug("POST data", $_POST);
    logDebug("FILES data", isset($_FILES['images']) ? 'YES' : 'NO');
    
    // Check if this is a product image request
    $isProductImage = isset($_POST['product_id']) && isset($_POST['image_path']);
    
    $productId = null;
    $firstFile = null;
    $fileName = null;
    $fileType = null;
    $fileSize = null;
    $imageHash = null;
    
    if ($isProductImage) {
        // Using product image
        logDebug("Using product image mode");
        
        $productId = intval($_POST['product_id']);
        $imagePath = $_POST['image_path'];
        
        // Clean the path - remove any leading slashes or '../'
        $imagePath = ltrim($imagePath, './');
        
        // Try different path combinations
        $possiblePaths = [
            __DIR__ . '/../../' . $imagePath,  // From meshy folder
            __DIR__ . '/../' . $imagePath,     // From admin folder
            __DIR__ . '/../../uploads/' . basename($imagePath)  // Direct to uploads
        ];
        
        $foundPath = null;
        foreach ($possiblePaths as $testPath) {
            logDebug("Testing path: " . $testPath);
            if (file_exists($testPath)) {
                $foundPath = $testPath;
                logDebug("Found at: " . $foundPath);
                break;
            }
        }
        
        if (!$foundPath) {
            throw new Exception('Product image not found. Tried paths: ' . implode(', ', $possiblePaths));
        }
        
        $firstFile = $foundPath;
        $fileName = basename($foundPath);
        $fileType = mime_content_type($foundPath);
        $fileSize = filesize($foundPath);
        $imageHash = md5_file($foundPath);
        
        logDebug("Product image loaded", [
            'path' => $firstFile,
            'name' => $fileName,
            'type' => $fileType,
            'size' => $fileSize,
            'hash' => $imageHash
        ]);
        
    } else {
        // Using uploaded files
        logDebug("Using uploaded files mode");
        
        if (!isset($_FILES['images'])) {
            throw new Exception('No images provided');
        }
        
        $images = $_FILES['images'];
        
        if (is_array($images['name'])) {
            $firstFile = $images['tmp_name'][0];
            $fileName = $images['name'][0];
            $fileType = $images['type'][0];
            $fileSize = $images['size'][0];
        } else {
            $firstFile = $images['tmp_name'];
            $fileName = $images['name'];
            $fileType = $images['type'];
            $fileSize = $images['size'];
        }
        
        if (empty($firstFile) || !file_exists($firstFile)) {
            throw new Exception('No valid image file uploaded');
        }
        
        $imageHash = md5_file($firstFile);
        
        logDebug("Upload file loaded", [
            'name' => $fileName,
            'type' => $fileType,
            'size' => $fileSize,
            'hash' => $imageHash
        ]);
    }
    
    // Validate image type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!in_array(strtolower($fileType), $allowedTypes)) {
        throw new Exception('Invalid file type: ' . $fileType);
    }
    
    // Check file size (max 10MB)
    if ($fileSize > 10 * 1024 * 1024) {
        throw new Exception('File too large. Max 10MB allowed.');
    }
    
    // Check for duplicates
    $checkStmt = $conn->prepare("
        SELECT id, model_path, generation_status, meshy_task_id 
        FROM generated_3d_models 
        WHERE image_hash = ? AND generation_status IN ('succeeded', 'pending', 'processing')
    ");
    $checkStmt->bind_param("s", $imageHash);
    $checkStmt->execute();
    $existingResult = $checkStmt->get_result();
    
    if ($existingResult->num_rows > 0) {
        $existing = $existingResult->fetch_assoc();
        logDebug("Duplicate found", $existing);
        
        if ($existing['generation_status'] === 'succeeded') {
            ob_end_clean();
            echo json_encode([
                'status' => 'success',
                'existing' => true,
                'model_url' => $existing['model_path'],
                'model_path' => $existing['model_path'],
                'message' => 'Image already converted'
            ]);
            exit;
        } else {
            ob_end_clean();
            echo json_encode([
                'status' => 'pending',
                'existing' => true,
                'task_id' => $existing['meshy_task_id'],
                'message' => 'Image already processing'
            ]);
            exit;
        }
    }
    
    logDebug("No duplicate found, proceeding with API call");
    
    // Read file contents
    $imageData = file_get_contents($firstFile);
    if ($imageData === false) {
        throw new Exception('Failed to read image file');
    }
    
    $base64Image = base64_encode($imageData);
    $mimeType = $fileType;
    $imageDataUri = "data:$mimeType;base64,$base64Image";
    
    logDebug("Data URI created", ['length' => strlen($imageDataUri)]);
    
    // Prepare API request
    $data = [
        'image_url' => $imageDataUri,
        'enable_pbr' => true,
        'surface_mode' => 'hard'
    ];
    
    $jsonData = json_encode($data);
    logDebug("JSON payload size: " . strlen($jsonData) . " bytes");
    
    $apiUrl = 'https://api.meshy.ai/openapi/v1/image-to-3d';
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL Error: ' . $error);
    }
    
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    logDebug("API Response", ['code' => $statusCode, 'response' => substr($response, 0, 500)]);
    
    if ($statusCode !== 202 && $statusCode !== 200) {
        throw new Exception("Meshy API Error (HTTP $statusCode): $response");
    }
    
    $result = json_decode($response, true);
    
    if (!$result) {
        throw new Exception('Invalid JSON response from Meshy API');
    }
    
    if (isset($result['result'])) {
        $taskId = $result['result'];
        logDebug("Task created successfully: $taskId");
        
        // Insert into database
        $insertStmt = $conn->prepare("
            INSERT INTO generated_3d_models 
            (product_id, meshy_task_id, image_hash, original_image_name, generation_status) 
            VALUES (?, ?, ?, ?, 'pending')
        ");
        $insertStmt->bind_param("isss", $productId, $taskId, $imageHash, $fileName);
        
        if (!$insertStmt->execute()) {
            logDebug("Database insert failed: " . $insertStmt->error);
        } else {
            logDebug("Database record created for task: $taskId");
            
            // Update products table with task_id if product_id is provided
            if ($productId) {
                $updateProductStmt = $conn->prepare("
                    UPDATE products 
                    SET meshy_task_id = ? 
                    WHERE product_id = ?
                ");
                $updateProductStmt->bind_param("si", $taskId, $productId);
                $updateProductStmt->execute();
                logDebug("Product table updated with task_id", ['product_id' => $productId]);
            }
        }
        
        ob_end_clean();
        echo json_encode([
            'status' => 'pending',
            'task_id' => $taskId,
            'message' => 'Task created successfully. Processing...'
        ]);
        
    } else {
        throw new Exception('Unexpected API response format');
    }
    
    logDebug("=== meshy_upload.php completed ===");
    
} catch (Exception $e) {
    logDebug("ERROR: " . $e->getMessage());
    logDebug("Trace: " . $e->getTraceAsString());
    
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

ob_end_flush();
?>