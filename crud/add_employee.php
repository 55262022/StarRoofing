<?php
require_once '../authentication/auth.php';
require_once '../database/starroofing_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone'] ?? '');
    $department = trim($_POST['department']);
    $hire_date = $_POST['hire_date'];
    $password = $_POST['password'] ?? 'star123'; // Default password

    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($email) || 
        empty($department) || empty($hire_date)) {
        header("Location: ../admin/employees.php?error=Please fill all required fields");
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../admin/employees.php?error=Invalid email format");
        exit();
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Check if email already exists in accounts
        $check_stmt = $conn->prepare("SELECT id FROM accounts WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            throw new Exception("Email already exists in the system");
        }

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $employee_role_id = 3; // Employee role

        // 1. Create account first
        $account_stmt = $conn->prepare("INSERT INTO accounts (email, password, role_id, account_status) VALUES (?, ?, ?, 'active')");
        $account_stmt->bind_param("ssi", $email, $hashed_password, $employee_role_id);
        
        if (!$account_stmt->execute()) {
            throw new Exception("Error creating account: " . $conn->error);
        }

        $account_id = $conn->insert_id;

        // Handle file upload
        $image_path = null;
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/employees/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $file_extension;
            $target_path = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target_path)) {
                $image_path = 'uploads/employees/' . $filename;
            }
        }

        // 2. Create employee record with account_id (removed position, salary, status)
        $employee_stmt = $conn->prepare("INSERT INTO employees 
            (account_id, first_name, last_name, phone, department, hire_date, image_path) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $employee_stmt->bind_param("issssss", $account_id, $first_name, $last_name, $phone, 
                          $department, $hire_date, $image_path);

        if (!$employee_stmt->execute()) {
            throw new Exception("Error creating employee: " . $conn->error);
        }

        // Commit transaction
        $conn->commit();

        header("Location: ../admin/employees.php?success=Employee added successfully! Login: $email / Password: $password");
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        header("Location: ../admin/employees.php?error=" . urlencode($e->getMessage()));
    }

    exit();
}
?>