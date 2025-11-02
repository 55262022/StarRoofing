<?php
require_once '../../database/starroofing_db.php';
require_once '../../authentication/auth.php';
requireAuth();

header('Content-Type: application/json');

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit();
}

try {
    // Extract print settings
    $product_id = isset($data['product_id']) ? intval($data['product_id']) : null;
    $product_name = $data['product_name'] ?? 'Unknown Product';
    $material = $data['material'] ?? 'pla';
    $quality = $data['quality'] ?? 'normal';
    $infill = intval($data['infill'] ?? 20);
    $support = $data['support'] ?? 'none';
    $scale = intval($data['scale'] ?? 100);
    $orientation = $data['orientation'] ?? 'auto';
    $raft = isset($data['raft']) && $data['raft'] ? 1 : 0;
    $brim = isset($data['brim']) && $data['brim'] ? 1 : 0;
    $hollow = isset($data['hollow']) && $data['hollow'] ? 1 : 0;
    $notes = $data['notes'] ?? '';
    $estimated_time = $data['estimated_time'] ?? 'Unknown';
    $estimated_cost = $data['estimated_cost'] ?? '₱0.00';
    $account_id = $_SESSION['account_id'];
    
    // Generate unique job ID
    $job_id = 'PRINT-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));
    
    // Create 3d_print_jobs table if it doesn't exist
    $create_table_query = "
        CREATE TABLE IF NOT EXISTS 3d_print_jobs (
            id INT(11) NOT NULL AUTO_INCREMENT,
            job_id VARCHAR(50) NOT NULL UNIQUE,
            account_id INT(11) NOT NULL,
            product_id INT(11) NULL,
            product_name VARCHAR(255) NOT NULL,
            material VARCHAR(50) NOT NULL,
            quality VARCHAR(50) NOT NULL,
            infill INT(11) NOT NULL,
            support VARCHAR(50) NOT NULL,
            scale INT(11) NOT NULL,
            orientation VARCHAR(50) NOT NULL,
            use_raft TINYINT(1) DEFAULT 0,
            use_brim TINYINT(1) DEFAULT 0,
            is_hollow TINYINT(1) DEFAULT 0,
            notes TEXT,
            estimated_time VARCHAR(50),
            estimated_cost VARCHAR(50),
            status ENUM('pending', 'queued', 'printing', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
            printer_id VARCHAR(50) NULL,
            started_at TIMESTAMP NULL,
            completed_at TIMESTAMP NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
            INDEX idx_job_id (job_id),
            INDEX idx_account_id (account_id),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";
    
    $conn->query($create_table_query);
    
    // Insert print job
    $insert_query = "
        INSERT INTO 3d_print_jobs (
            job_id, account_id, product_id, product_name,
            material, quality, infill, support, scale, orientation,
            use_raft, use_brim, is_hollow, notes,
            estimated_time, estimated_cost, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'queued')
    ";
    
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param(
        "siisssisisiiiss",
        $job_id,
        $account_id,
        $product_id,
        $product_name,
        $material,
        $quality,
        $infill,
        $support,
        $scale,
        $orientation,
        $raft,
        $brim,
        $hollow,
        $notes,
        $estimated_time,
        $estimated_cost
    );
    
    if ($stmt->execute()) {
        $print_job_id = $stmt->insert_id;
        $stmt->close();
        
        // Log the activity
        $log_query = "
            INSERT INTO activity_log (account_id, action, details, created_at)
            VALUES (?, 'submit_3d_print', ?, NOW())
        ";
        
        // Create activity_log table if it doesn't exist
        $create_log_table = "
            CREATE TABLE IF NOT EXISTS activity_log (
                id INT(11) NOT NULL AUTO_INCREMENT,
                account_id INT(11) NOT NULL,
                action VARCHAR(100) NOT NULL,
                details TEXT,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
                INDEX idx_account_id (account_id),
                INDEX idx_action (action),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        $conn->query($create_log_table);
        
        $log_stmt = $conn->prepare($log_query);
        $log_details = json_encode([
            'job_id' => $job_id,
            'product_name' => $product_name,
            'material' => $material,
            'quality' => $quality
        ]);
        $log_stmt->bind_param("is", $account_id, $log_details);
        $log_stmt->execute();
        $log_stmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => '3D print job submitted successfully',
            'job_id' => $job_id,
            'print_job_id' => $print_job_id,
            'status' => 'queued',
            'estimated_time' => $estimated_time,
            'estimated_cost' => $estimated_cost
        ]);
        
    } else {
        throw new Exception('Failed to insert print job');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error submitting print job: ' . $e->getMessage()
    ]);
}
?>