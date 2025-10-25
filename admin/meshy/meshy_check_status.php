<?php
// CRITICAL: Clear output buffer first
ob_start();
ob_clean();

// Set JSON header FIRST before any output
header('Content-Type: application/json');

// Disable error display
// ini_set('display_errors', 0);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);

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
    
    // Validate task_id
    if (!isset($_GET['task_id']) || empty($_GET['task_id'])) {
        throw new Exception('Missing task_id parameter');
    }
    
    $taskId = $_GET['task_id'];
    logDebug("Checking status", ['task_id' => $taskId]);
    
    // Check Meshy API for task status
    $url = "https://api.meshy.ai/openapi/v1/image-to-3d/$taskId";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception('cURL Error: ' . curl_error($ch));
    }
    
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    logDebug("API Response", ['code' => $statusCode]);
    
    if ($statusCode !== 200) {
        throw new Exception("Meshy API Error (HTTP $statusCode)");
    }
    
    $result = json_decode($response, true);
    if (!$result) {
        throw new Exception("Invalid JSON response from Meshy API");
    }
    
    // Handle different task states
    $state = strtolower($result['status'] ?? 'unknown');
    logDebug("Task state", ['status' => $state]);
    
    if ($state === 'failed') {
        // Update database status to failed
        $stmt = $conn->prepare("UPDATE generated_3d_models SET generation_status = 'failed' WHERE meshy_task_id = ?");
        $stmt->bind_param("s", $taskId);
        $stmt->execute();
        
        echo json_encode(['status' => 'failed', 'message' => 'Model generation failed.']);
        exit;
    }
    
    if ($state !== 'succeeded') {
        $progress = $result['progress'] ?? 0;
        echo json_encode(['status' => 'pending', 'message' => 'Model is still processing...', 'progress' => $progress]);
        exit;
    }
    
    // Model generation succeeded - download and store it
    $modelUrl = $result['model_urls']['glb'] ?? null;
    if (!$modelUrl) {
        throw new Exception('Model URL not found in Meshy response.');
    }
    
    logDebug("Model URL found", ['url' => $modelUrl]);
    
    // Create upload directory if it doesn't exist
    // Path: C:\xampp\htdocs\starroofing\uploads\3dmodels\
    $projectRoot = __DIR__ . '/../../';  // Go up to starroofing/
    $saveDir = $projectRoot . 'uploads/3dmodels/';
    
    if (!is_dir($saveDir)) {
        mkdir($saveDir, 0777, true);
        chmod($saveDir, 0777);
    }
    
    // Generate unique filename
    $uniqueId = uniqid();
    $filename = "model_{$uniqueId}.glb";
    $savePath = $saveDir . $filename;
    
    // CRITICAL: Path relative to where 3dmodel.php is located (admin/)
    // 3dmodel.php is in: starroofing/admin/3dmodel.php
    // So we need: ../uploads/3dmodels/file.glb
    $relativePath = '../uploads/3dmodels/' . $filename;
    
    // Download .glb file using cURL (better for large files and CORS bypass)
    $ch = curl_init($modelUrl);
    $fp = fopen($savePath, 'w+');
    
    if ($fp === false) {
        throw new Exception('Failed to open file for writing: ' . $savePath);
    }
    
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 minutes timeout
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $success = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    fclose($fp);
    
    if (!$success || $httpCode !== 200) {
        unlink($savePath); // Delete failed file
        throw new Exception("Failed to download model from Meshy. HTTP $httpCode: $error");
    }
    
    $fileSize = filesize($savePath);
    
    if ($fileSize === 0) {
        unlink($savePath);
        throw new Exception('Downloaded file is empty');
    }
    
    // Set proper permissions
    chmod($savePath, 0644);
    
    // Verify file really exists
    if (!file_exists($savePath)) {
        throw new Exception('File was not saved properly');
    }
    
    logDebug("Model downloaded successfully", [
        'path' => $savePath,
        'size' => $fileSize,
        'exists' => file_exists($savePath),
        'readable' => is_readable($savePath)
    ]);
    
    // Check if record already exists in generated_3d_models table
    $checkStmt = $conn->prepare("SELECT id, product_id FROM generated_3d_models WHERE meshy_task_id = ?");
    $checkStmt->bind_param("s", $taskId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    $generatedModelId = null;
    $productId = null;
    
    if ($checkResult->num_rows > 0) {
        // Update existing record
        $row = $checkResult->fetch_assoc();
        $generatedModelId = $row['id'];
        $productId = $row['product_id'];
        
        $stmt = $conn->prepare("
            UPDATE generated_3d_models 
            SET model_filename = ?, 
                model_path = ?, 
                model_url = ?, 
                file_size = ?,
                generation_status = 'succeeded',
                updated_at = NOW()
            WHERE meshy_task_id = ?
        ");
        $stmt->bind_param("sssis", $filename, $relativePath, $modelUrl, $fileSize, $taskId);
        
        if (!$stmt->execute()) {
            throw new Exception('Database update error: ' . $stmt->error);
        }
        
        logDebug("Database updated", ['id' => $generatedModelId]);
        
    } else {
        // Insert new record
        $stmt = $conn->prepare("
            INSERT INTO generated_3d_models 
            (meshy_task_id, model_filename, model_path, model_url, file_size, generation_status) 
            VALUES (?, ?, ?, ?, ?, 'succeeded')
        ");
        $stmt->bind_param("ssssi", $taskId, $filename, $relativePath, $modelUrl, $fileSize);
        
        if (!$stmt->execute()) {
            throw new Exception('Database insert error: ' . $stmt->error);
        }
        
        $generatedModelId = $conn->insert_id;
        logDebug("Database inserted", ['id' => $generatedModelId]);
    }
    
    // Update products table if this model is linked to a product
    if ($productId) {
        $productStmt = $conn->prepare("
            UPDATE products 
            SET model_url = ?, 
                model_path = ?, 
                generated_model_id = ?
            WHERE product_id = ?
        ");
        $productStmt->bind_param("ssii", $relativePath, $relativePath, $generatedModelId, $productId);
        $productStmt->execute();
        logDebug("Product table updated", ['product_id' => $productId]);
    } else {
        // Also check if any product has this task_id
        $productStmt = $conn->prepare("
            UPDATE products 
            SET model_url = ?, 
                model_path = ?, 
                generated_model_id = ?
            WHERE meshy_task_id = ?
        ");
        $productStmt->bind_param("ssis", $relativePath, $relativePath, $generatedModelId, $taskId);
        $productStmt->execute();
    }
    
    // IMPORTANT: Return the LOCAL path, not the Meshy URL
    echo json_encode([
        'status' => 'succeeded',
        'message' => 'Model generated and saved successfully.',
        'model_url' => $relativePath,  // Changed: Use local path
        'model_path' => $relativePath,
        'file_size' => $fileSize,
        'task_id' => $taskId,
        'generated_model_id' => $generatedModelId
    ]);
    
} catch (Exception $e) {
    logDebug("ERROR", ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

// Clean output buffer
ob_end_flush();
?>