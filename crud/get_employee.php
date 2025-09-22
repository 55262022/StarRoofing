<?php
include '../includes/auth.php';
require_once '../database/starroofing_db.php';

if (isset($_GET['id'])) {
    $employee_id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT * FROM employees WHERE employee_id = ?");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $employee = $result->fetch_assoc();
        echo json_encode(['success' => true, 'employee' => $employee]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Employee not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No employee ID provided']);
}