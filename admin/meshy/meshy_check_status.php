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

function getProgressStage($progress) {
    if ($progress < 20) {
        return ['stage' => 'upload', 'message' => 'Uploading and analyzing image...'];
    } elseif ($progress < 40) {
        return ['stage' => 'ai_processing', 'message' => 'AI is processing your image...'];
    } elseif ($progress < 60) {
        return ['stage' => 'geometry', 'message' => 'Generating 3D geometry...'];
    } elseif ($progress < 85) {
        return ['stage' => 'textures', 'message' => 'Applying textures and materials...'];
    } else {
        return ['stage' => 'finalizing', 'message' => 'Finalizing your 3D model...'];
    }
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
    $progress = intval($result['progress'] ?? 0);
    
    logDebug("Task state", ['status' => $state, 'progress' => $progress]);
    
    if ($state === 'failed') {
        // Update database status to failed
        $stmt = $conn->prepare("UPDATE generated_3d_models SET generation_status = 'failed' WHERE meshy_task_id = ?");
        $stmt->bind_param("s", $taskId);
        $stmt->execute();
        
        echo json_encode([
            'status' => 'failed', 
            'message' => 'Model generation failed.',
            'progress' => 0
        ]);
        exit;
    }
    
    if ($state !== 'succeeded') {
        // Still processing - return progress information
        $stageInfo = getProgressStage($progress);
        
        // Update database with current progress
        $stmt = $conn->prepare("UPDATE generated_3d_models SET generation_status = 'processing' WHERE meshy_task_id = ?");
        $stmt->bind_param("s", $taskId);
        $stmt->execute();
        
        echo json_encode([
            'status' => 'pending',
            'message' => $stageInfo['message'],
            'progress' => $progress,
            'stage' => $stageInfo['stage']
        ]);
        exit;
    }
    
    // Model generation succeeded - download and store it
    $modelUrl = $result['model_urls']['glb'] ?? null;
    if (!$modelUrl) {
        throw new Exception('Model URL not found in Meshy response.');
    }
    
    logDebug("Model URL found", ['url' => $modelUrl]);
    
    // Create upload directory if it doesn't exist
    $projectRoot = __DIR__ . '/../../';
    $saveDir = $projectRoot . 'uploads/3dmodels/';
    
    if (!is_dir($saveDir)) {
        mkdir($saveDir, 0777, true);
        chmod($saveDir, 0777);
    }
    
    // Generate unique filename
    $uniqueId = uniqid();
    $filename = "model_{$uniqueId}.glb";
    $savePath = $saveDir . $filename;
    $relativePath = 'uploads/3dmodels/' . $filename;
    
    // Download .glb file using cURL
    $ch = curl_init($modelUrl);
    $fp = fopen($savePath, 'w+');
    
    if ($fp === false) {
        throw new Exception('Failed to open file for writing: ' . $savePath);
    }
    
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $success = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    fclose($fp);
    
    if (!$success || $httpCode !== 200) {
        unlink($savePath);
        throw new Exception("Failed to download model from Meshy. HTTP $httpCode: $error");
    }
    
    $fileSize = filesize($savePath);
    
    if ($fileSize === 0) {
        unlink($savePath);
        throw new Exception('Downloaded file is empty');
    }
    
    chmod($savePath, 0644);
    
    if (!file_exists($savePath)) {
        throw new Exception('File was not saved properly');
    }
    
    logDebug("Model downloaded successfully", [
        'path' => $savePath,
        'relative_path' => $relativePath,
        'size' => $fileSize
    ]);
    
    // Check if record already exists
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
    
    // Update products table
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
        $productStmt = $conn->prepare("
            UPDATE products 
            SET model_url = ?, 
                model_path = ?, 
                generated_model_id = ?
            WHERE meshy_task_id = ?
        ");
        $productStmt->bind_param("ssis", $relativePath, $relativePath, $generatedModelId, $taskId);
        $productStmt->execute();
        
        if ($conn->affected_rows > 0) {
            logDebug("Product table updated via task_id");
        }
    }
    
    echo json_encode([
        'status' => 'succeeded',
        'message' => 'Model generated and saved successfully!',
        'model_url' => $relativePath,
        'model_path' => $relativePath,
        'file_size' => $fileSize,
        'task_id' => $taskId,
        'generated_model_id' => $generatedModelId,
        'progress' => 100,
        'stage' => 'complete'
    ]);
    
} catch (Exception $e) {
    logDebug("ERROR", ['message' => $e->getMessage()]);
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'progress' => 0
    ]);
}

ob_end_flush();
?>