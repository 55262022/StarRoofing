<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once __DIR__ . '/../../database/starroofing_db.php';

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
    
    // Check Meshy API for task status
    $url = "https://api.meshy.ai/openapi/v1/image-to-3d/$taskId";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception('cURL Error: ' . curl_error($ch));
    }
    
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($statusCode !== 200) {
        throw new Exception("Meshy API Error (HTTP $statusCode): $response");
    }
    
    $result = json_decode($response, true);
    if (!$result) {
        throw new Exception("Invalid JSON response from Meshy API: $response");
    }
    
    // Handle different task states
    $state = strtolower($result['status'] ?? 'unknown');
    
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
    
    // Create upload directory if it doesn't exist
    $saveDir = __DIR__ . '/../../uploads/3dmodels/';
    if (!is_dir($saveDir)) {
        mkdir($saveDir, 0777, true);
    }
    
    // Generate unique filename
    $filename = 'model_' . $taskId . '.glb';
    $savePath = $saveDir . $filename;
    $relativePath = 'uploads/3dmodels/' . $filename;
    
    // Download .glb file
    $modelData = file_get_contents($modelUrl);
    if ($modelData === false) {
        throw new Exception('Failed to download .glb file from Meshy.');
    }
    
    // Save file to server
    file_put_contents($savePath, $modelData);
    $fileSize = filesize($savePath);
    
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
                generation_status = 'succeeded'
            WHERE meshy_task_id = ?
        ");
        $stmt->bind_param("sssis", $filename, $relativePath, $modelUrl, $fileSize, $taskId);
        $stmt->execute();
    } else {
        // Insert new record (shouldn't happen normally, but just in case)
        $stmt = $conn->prepare("
            INSERT INTO generated_3d_models 
            (meshy_task_id, model_filename, model_path, model_url, file_size, generation_status) 
            VALUES (?, ?, ?, ?, ?, 'succeeded')
        ");
        $stmt->bind_param("ssssi", $taskId, $filename, $relativePath, $modelUrl, $fileSize);
        $stmt->execute();
        $generatedModelId = $conn->insert_id;
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Database error: ' . $stmt->error);
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
        $productStmt->bind_param("ssii", $modelUrl, $relativePath, $generatedModelId, $productId);
        $productStmt->execute();
    } else {
        // Also check if any product has this task_id
        $productStmt = $conn->prepare("
            UPDATE products 
            SET model_url = ?, 
                model_path = ?, 
                generated_model_id = ?
            WHERE meshy_task_id = ?
        ");
        $productStmt->bind_param("ssis", $modelUrl, $relativePath, $generatedModelId, $taskId);
        $productStmt->execute();
    }
    
    echo json_encode([
        'status' => 'succeeded',
        'message' => 'Model generated and saved successfully.',
        'model_url' => $modelUrl,
        'model_path' => $relativePath,
        'file_size' => $fileSize,
        'task_id' => $taskId,
        'generated_model_id' => $generatedModelId
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>