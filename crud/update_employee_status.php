<?php
include '../includes/auth.php';
require_once '../database/starroofing_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE employees SET status = ? WHERE employee_id = ?");
    $stmt->bind_param("si", $status, $employee_id);
    
    if ($stmt->execute()) {
        header("Location: ../employees.php?success=Employee status updated successfully");
    } else {
        header("Location: ../employees.php?error=Error updating employee status: " . $conn->error);
    }
    
    exit();
}