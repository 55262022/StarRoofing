<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Create debug log
$debugLog = __DIR__ . '/debug.log';
$errorLog = __DIR__ . '/error.log';

// Create log files if they don't exist
if (!file_exists($debugLog)) {
    file_put_contents($debugLog, "=== DEBUG LOG START ===\n");
}
if (!file_exists($errorLog)) {
    file_put_contents($errorLog, "=== ERROR LOG START ===\n");
}

function logDebug($message) {
    global $debugLog;
    file_put_contents($debugLog, date('Y-m-d H:i:s') . " - $message\n", FILE_APPEND);
}

function logError($message) {
    global $errorLog;
    file_put_contents($errorLog, date('Y-m-d H:i:s') . " - ERROR: $message\n", FILE_APPEND);
}

try {
    logDebug("Script started");
    logDebug("FILES: " . print_r($_FILES, true));
    logDebug("POST: " . print_r($_POST, true));
    
    // Load .env manually (without Composer)
    $envFile = __DIR__ . '/../../.env';
    if (!file_exists($envFile)) {
        throw new Exception('.env file not found at: ' . $envFile);
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
    
    logDebug("API Key loaded: " . ($apiKey ? "YES" : "NO"));
    
    if (!$apiKey) {
        throw new Exception('MESHY_API_KEY not found in .env file');
    }
    
    // Check if files were uploaded
    if (!isset($_FILES['images'])) {
        throw new Exception('No images field in upload');
    }

    logDebug("Images field exists");

    $images = $_FILES['images'];

    // Handle both single and multiple uploads
    if (is_array($images['name'])) {
        $firstFile = $images['tmp_name'][0];
        $fileName  = $images['name'][0];
    } else {
        $firstFile = $images['tmp_name'];
        $fileName  = $images['name'];
    }

    if (empty($firstFile) || !file_exists($firstFile)) {
        throw new Exception('No valid image file uploaded');
    }

    logDebug("Image file detected: $fileName");

    
    // Get the first image
    $file = [
        'name' => $_FILES['images']['name'][0],
        'type' => $_FILES['images']['type'][0],
        'tmp_name' => $_FILES['images']['tmp_name'][0],
        'error' => $_FILES['images']['error'][0],
        'size' => $_FILES['images']['size'][0]
    ];
    
    logDebug("File details: " . print_r($file, true));
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error code: ' . $file['error']);
    }
    
    // Validate image type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!in_array(strtolower($file['type']), $allowedTypes)) {
        throw new Exception('Invalid file type: ' . $file['type'] . '. Only JPG and PNG allowed.');
    }
    
    // Check file size (max 10MB)
    if ($file['size'] > 10 * 1024 * 1024) {
        throw new Exception('File too large. Max 10MB allowed.');
    }
    
    logDebug("File validation passed");
    
    // Read file contents
    if (!file_exists($file['tmp_name'])) {
        throw new Exception('Uploaded file not found at: ' . $file['tmp_name']);
    }
    
    $imageData = file_get_contents($file['tmp_name']);
    if ($imageData === false) {
        throw new Exception('Failed to read uploaded file');
    }
    
    logDebug("File read successfully. Size: " . strlen($imageData) . " bytes");
    
    $base64Image = base64_encode($imageData);
    $mimeType = $file['type'];
    
    // Create data URI
    $imageDataUri = "data:$mimeType;base64,$base64Image";
    
    logDebug("Data URI created. Length: " . strlen($imageDataUri));
    
    // Prepare API request to Meshy
    $data = [
        'image_url' => $imageDataUri,
        'enable_pbr' => true,
        'surface_mode' => 'hard'
    ];
    
    $jsonData = json_encode($data);
    logDebug("JSON payload size: " . strlen($jsonData) . " bytes");
    
    $apiUrl = 'https://api.meshy.ai/openapi/v1/image-to-3d';
    logDebug("Calling Meshy API: $apiUrl");
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For localhost testing
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL Error: ' . $error);
    }
    
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    logDebug("API Response Code: $statusCode");
    logDebug("API Response: " . substr($response, 0, 500));
    
    if ($statusCode !== 202 && $statusCode !== 200) {
        throw new Exception("Meshy API Error (HTTP $statusCode): $response");
    }
    
    $result = json_decode($response, true);
    
    if (!$result) {
        throw new Exception('Invalid JSON response from Meshy API: ' . $response);
    }
    
    logDebug("Parsed result: " . print_r($result, true));
    
    // Check if task was created successfully
    if (isset($result['result'])) {
        $taskId = $result['result'];
        
        logDebug("Task created successfully: $taskId");
        
        echo json_encode([
            'status' => 'pending',
            'task_id' => $taskId,
            'message' => 'Task created successfully. Processing...'
        ]);
        
    } elseif (isset($result['model_urls'])) {
        // Model is already ready
        $modelUrl = $result['model_urls']['glb'] ?? $result['model_urls']['gltf'] ?? null;
        
        logDebug("Model ready immediately: $modelUrl");
        
        echo json_encode([
            'status' => 'success',
            'model_url' => $modelUrl,
            'message' => 'Model generated successfully'
        ]);
        
    } else {
        throw new Exception('Unexpected API response format: ' . json_encode($result));
    }
    
    logDebug("Script completed successfully");
    
} catch (Exception $e) {
    logError($e->getMessage());
    logError($e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>