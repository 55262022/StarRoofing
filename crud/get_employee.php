<?php
require_once '../authentication/auth.php';
require_once '../database/starroofing_db.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Employee ID is required']);
    exit();
}

$employee_id = intval($_GET['id']);

// Join employees with accounts table to get email
$sql = "SELECT e.*, a.email 
        FROM employees e 
        LEFT JOIN accounts a ON e.account_id = a.id 
        WHERE e.employee_id = ? AND e.is_archived = 0";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $employee = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'employee' => $employee
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Employee not found'
    ]);
}

$stmt->close();
$conn->close();
?>