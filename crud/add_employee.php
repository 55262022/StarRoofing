<?php
require_once '../authentication/auth.php';
require_once '../database/starroofing_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone'] ?? '');
    $position = trim($_POST['position']);
    $department = trim($_POST['department']);
    $hire_date = $_POST['hire_date'];
    $salary = $_POST['salary'];
    $status = $_POST['status'];

    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($email) || empty($position) || 
        empty($department) || empty($hire_date) || empty($salary)) {
        header("Location: ../admin/employees.php?error=Please fill all required fields");
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../admin/employees.php?error=Invalid email format");
        exit();
    }

    // Check if email already exists
    $check_stmt = $conn->prepare("SELECT employee_id FROM employees WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        header("Location: ../admin/employees.php?error=Email already exists");
        exit();
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
        }
    }

    // Insert new employee
    $stmt = $conn->prepare("INSERT INTO employees 
        (first_name, last_name, email, phone, position, department, hire_date, salary, status, image_path) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssdss", $first_name, $last_name, $email, $phone, $position, 
                      $department, $hire_date, $salary, $status, $image_path);

    if ($stmt->execute()) {
        header("Location: ../admin/employees.php?success=Employee added successfully");
    } else {
        header("Location: ../admin/employees.php?error=Error adding employee: " . $conn->error);
    }

    exit();
}
