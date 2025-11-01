<?php
require_once '../authentication/auth.php';
require_once '../database/starroofing_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'] ?? null;
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone'] ?? '');
    $position = trim($_POST['position']);
    $department = trim($_POST['department']);
    $hire_date = $_POST['hire_date'];
    $salary = $_POST['salary'];
    $status = $_POST['status'];
    $new_password = $_POST['password'] ?? null; // Optional password change

    // Validate required fields
    if (empty($employee_id) || empty($first_name) || empty($last_name) || empty($email) || empty($position) || 
        empty($department) || empty($hire_date) || empty($salary)) {
        header("Location: ../admin/employees.php?error=Please fill all required fields");
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../admin/employees.php?error=Invalid email format");
        exit();
    }

    // Get employee's current account_id and email
    $get_emp_stmt = $conn->prepare("SELECT account_id FROM employees WHERE employee_id = ?");
    $get_emp_stmt->bind_param("i", $employee_id);
    $get_emp_stmt->execute();
    $emp_result = $get_emp_stmt->get_result();
    
    if ($emp_result->num_rows === 0) {
        header("Location: ../admin/employees.php?error=Employee not found");
        exit();
    }
    
    $employee_data = $emp_result->fetch_assoc();
    $account_id = $employee_data['account_id'];

    if (empty($account_id)) {
        header("Location: ../admin/employees.php?error=Employee has no linked account. Please contact administrator.");
        exit();
    }

    // Check if email already exists for another account
    $check_stmt = $conn->prepare("SELECT id FROM accounts WHERE email = ? AND id != ?");
    $check_stmt->bind_param("si", $email, $account_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        header("Location: ../admin/employees.php?error=Email already exists");
        exit();
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update account email (and password if provided)
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $account_stmt = $conn->prepare("UPDATE accounts SET email = ?, password = ?, account_status = ? WHERE id = ?");
            $account_status = ($status === 'active') ? 'active' : 'inactive';
            $account_stmt->bind_param("sssi", $email, $hashed_password, $account_status, $account_id);
        } else {
            $account_stmt = $conn->prepare("UPDATE accounts SET email = ?, account_status = ? WHERE id = ?");
            $account_status = ($status === 'active') ? 'active' : 'inactive';
            $account_stmt->bind_param("ssi", $email, $account_status, $account_id);
        }

        if (!$account_stmt->execute()) {
            throw new Exception("Error updating account: " . $conn->error);
        }

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

                // Delete old image if exists
                $old_image_stmt = $conn->prepare("SELECT image_path FROM employees WHERE employee_id = ?");
                $old_image_stmt->bind_param("i", $employee_id);
                $old_image_stmt->execute();
                $old_image_result = $old_image_stmt->get_result();

                if ($old_image_result->num_rows > 0) {
                    $old_image = $old_image_result->fetch_assoc()['image_path'];
                    if ($old_image && file_exists('../' . $old_image)) {
                        unlink('../' . $old_image);
                    }
                }
            }
        }

        // Update employee record
        if ($image_path) {
            $emp_stmt = $conn->prepare("UPDATE employees 
                SET first_name = ?, last_name = ?, phone = ?, position = ?, department = ?, 
                    hire_date = ?, salary = ?, status = ?, image_path = ? 
                WHERE employee_id = ?");
            $emp_stmt->bind_param("ssssssdssi", $first_name, $last_name, $phone, $position, $department, 
                              $hire_date, $salary, $status, $image_path, $employee_id);
        } else {
            $emp_stmt = $conn->prepare("UPDATE employees 
                SET first_name = ?, last_name = ?, phone = ?, position = ?, department = ?, 
                    hire_date = ?, salary = ?, status = ? 
                WHERE employee_id = ?");
            $emp_stmt->bind_param("ssssssdsi", $first_name, $last_name, $phone, $position, $department, 
                              $hire_date, $salary, $status, $employee_id);
        }

        if (!$emp_stmt->execute()) {
            throw new Exception("Error updating employee: " . $conn->error);
        }

        // Commit transaction
        $conn->commit();

        $success_msg = "Employee updated successfully";
        if (!empty($new_password)) {
            $success_msg .= "! New password: $new_password";
        }
        header("Location: ../admin/employees.php?success=" . urlencode($success_msg));

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        header("Location: ../admin/employees.php?error=" . urlencode($e->getMessage()));
    }

    exit();
}
?>