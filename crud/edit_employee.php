<?php
require_once '../authentication/auth.php';
require_once '../database/starroofing_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'] ?? null;
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone'] ?? '');
    $department = trim($_POST['department']);
    $hire_date = $_POST['hire_date'];
    $new_password = $_POST['password'] ?? null; // Optional password change

    // Validate required fields
    if (empty($employee_id) || empty($first_name) || empty($last_name) || 
        empty($email) || empty($department) || empty($hire_date)) {
        header("Location: ../admin/employees.php?error=Please fill all required fields");
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../admin/employees.php?error=Invalid email format");
        exit();
    }

    // Get employee's current account_id and email
    $get_emp_stmt = $conn->prepare("SELECT account_id, email FROM employees WHERE employee_id = ?");
    $get_emp_stmt->bind_param("i", $employee_id);
    $get_emp_stmt->execute();
    $emp_result = $get_emp_stmt->get_result();
    
    if ($emp_result->num_rows === 0) {
        header("Location: ../admin/employees.php?error=Employee not found");
        exit();
    }
    
    $employee_data = $emp_result->fetch_assoc();
    $account_id = $employee_data['account_id'];
    $old_email = $employee_data['email'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Check if new email already exists (for another employee)
        if ($email !== $old_email) {
            $check_emp = $conn->prepare("SELECT employee_id FROM employees WHERE email = ? AND employee_id != ?");
            $check_emp->bind_param("si", $email, $employee_id);
            $check_emp->execute();
            $check_result = $check_emp->get_result();

            if ($check_result->num_rows > 0) {
                throw new Exception("Email already exists");
            }

            // Check in accounts table too
            if ($account_id) {
                $check_acc = $conn->prepare("SELECT id FROM accounts WHERE email = ? AND id != ?");
                $check_acc->bind_param("si", $email, $account_id);
                $check_acc->execute();
                $acc_result = $check_acc->get_result();

                if ($acc_result->num_rows > 0) {
                    throw new Exception("Email already exists in accounts");
                }
            }
        }

        // Update account if exists (update email and optionally password)
        if ($account_id) {
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $account_stmt = $conn->prepare("UPDATE accounts SET email = ?, password = ? WHERE id = ?");
                $account_stmt->bind_param("ssi", $email, $hashed_password, $account_id);
            } else {
                $account_stmt = $conn->prepare("UPDATE accounts SET email = ? WHERE id = ?");
                $account_stmt->bind_param("si", $email, $account_id);
            }

            if (!$account_stmt->execute()) {
                throw new Exception("Error updating account: " . $conn->error);
            }
        }

        // Handle file upload
        $image_path = null;
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/employees/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['image_file']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                throw new Exception("Invalid file type. Only JPG, PNG, and GIF allowed");
            }

            if ($_FILES['image_file']['size'] > 104857600) { // 100MB
                throw new Exception("File too large. Maximum size is 100MB");
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

        // Update employee record - match exact database fields
        if ($image_path) {
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $emp_stmt = $conn->prepare("UPDATE employees 
                    SET first_name = ?, last_name = ?, email = ?, password = ?, phone = ?, 
                        department = ?, hire_date = ?, image_path = ? 
                    WHERE employee_id = ?");
                $emp_stmt->bind_param("ssssssssi", 
                    $first_name, $last_name, $email, $hashed_password, $phone, 
                    $department, $hire_date, $image_path, $employee_id);
            } else {
                $emp_stmt = $conn->prepare("UPDATE employees 
                    SET first_name = ?, last_name = ?, email = ?, phone = ?, 
                        department = ?, hire_date = ?, image_path = ? 
                    WHERE employee_id = ?");
                $emp_stmt->bind_param("sssssssi", 
                    $first_name, $last_name, $email, $phone, 
                    $department, $hire_date, $image_path, $employee_id);
            }
        } else {
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $emp_stmt = $conn->prepare("UPDATE employees 
                    SET first_name = ?, last_name = ?, email = ?, password = ?, phone = ?, 
                        department = ?, hire_date = ? 
                    WHERE employee_id = ?");
                $emp_stmt->bind_param("sssssssi", 
                    $first_name, $last_name, $email, $hashed_password, $phone, 
                    $department, $hire_date, $employee_id);
            } else {
                $emp_stmt = $conn->prepare("UPDATE employees 
                    SET first_name = ?, last_name = ?, email = ?, phone = ?, 
                        department = ?, hire_date = ? 
                    WHERE employee_id = ?");
                $emp_stmt->bind_param("ssssssi", 
                    $first_name, $last_name, $email, $phone, 
                    $department, $hire_date, $employee_id);
            }
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
        
        // Delete uploaded file if exists
        if (isset($image_path) && file_exists('../' . $image_path)) {
            unlink('../' . $image_path);
        }
        
        header("Location: ../admin/employees.php?error=" . urlencode($e->getMessage()));
    }

    exit();
}
?>