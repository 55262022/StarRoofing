<?php
// submit_info.php
session_start();
include '../database/starroofing_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize form data
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    $middleName = mysqli_real_escape_string($conn, $_POST['middleName']);
    $birthdate = mysqli_real_escape_string($conn, $_POST['birthdate']);
    $contactNumber = mysqli_real_escape_string($conn, $_POST['contactNumber']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    
    // Address information with both codes and names
    $regionCode = mysqli_real_escape_string($conn, $_POST['region']);
    $regionName = mysqli_real_escape_string($conn, $_POST['region_name']);
    $provinceCode = mysqli_real_escape_string($conn, $_POST['province']);
    $provinceName = mysqli_real_escape_string($conn, $_POST['province_name']);
    $cityCode = mysqli_real_escape_string($conn, $_POST['city']);
    $cityName = mysqli_real_escape_string($conn, $_POST['city_name']);
    $barangayCode = mysqli_real_escape_string($conn, $_POST['barangay']);
    $barangayName = mysqli_real_escape_string($conn, $_POST['barangay_name']);
    $street = mysqli_real_escape_string($conn, $_POST['street']);
    
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Check if email already exists
    $checkEmail = "SELECT id FROM accounts WHERE email = '$email'";
    $result = mysqli_query($conn, $checkEmail);
    
    if (mysqli_num_rows($result) > 0) {
        $_SESSION['error'] = "Email address already exists. Please use a different email.";
        header("Location: register.php");
        exit();
    }
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Insert into accounts table
        $sql = "INSERT INTO accounts (email, password, first_name, last_name, role_id, account_status) 
                VALUES ('$email', '$password', '$firstName', '$lastName', 2, 'active')";
        
        if (!mysqli_query($conn, $sql)) {
            throw new Exception("Error creating account: " . mysqli_error($conn));
        }
        
        $accountId = mysqli_insert_id($conn);
        
        // Insert into user_profiles table
        $profileSql = "INSERT INTO user_profiles (account_id, middle_name, birthdate, contact_number, gender, 
                      region_code, region_name, province_code, province_name, city_code, city_name, 
                      barangay_code, barangay_name, street) 
                      VALUES ('$accountId', '$middleName', '$birthdate', '$contactNumber', '$gender',
                      '$regionCode', '$regionName', '$provinceCode', '$provinceName', '$cityCode', '$cityName',
                      '$barangayCode', '$barangayName', '$street')";
        
        if (!mysqli_query($conn, $profileSql)) {
            throw new Exception("Error creating user profile: " . mysqli_error($conn));
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        // Set session variables
        $_SESSION['account_id'] = $accountId;
        $_SESSION['email'] = $email;
        $_SESSION['first_name'] = $firstName;
        $_SESSION['last_name'] = $lastName;
        $_SESSION['role_id'] = 2;
        
        $_SESSION['success'] = "Registration successful!";
        header("Location: login.php");
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../public/register.php");
        exit();
    }
} else {
    header("Location: register.php");
    exit();
}
?>