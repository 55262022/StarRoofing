<?php
// authentication/auth.php
session_start();

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['account_id']) && !empty($_SESSION['account_id']);
}

/**
 * Check if user is admin (role_id = 1)
 */
function isAdmin() {
    return isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1;
}

/**
 * Check if user is client (role_id = 2)
 */
function isClient() {
    return isset($_SESSION['role_id']) && $_SESSION['role_id'] == 2;
}

/**
 * Require authentication - redirect to login if not logged in
 */
function requireAuth($redirectPath = null) {
    if (!isLoggedIn()) {
        // Auto-detect the correct path to login
        if ($redirectPath === null) {
            $redirectPath = getLoginPath();
        }
        header("Location: $redirectPath");
        exit();
    }
}

/**
 * Require admin access - redirect if not admin
 */
function requireAdmin() {
    if (!isLoggedIn()) {
        header("Location: " . getLoginPath());
        exit();
    }
    if (!isAdmin()) {
        header("Location: " . getHomePath());
        exit();
    }
}

/**
 * Require client access - redirect if not client
 */
function requireClient() {
    if (!isLoggedIn()) {
        header("Location: " . getLoginPath());
        exit();
    }
    if (!isClient()) {
        header("Location: " . getHomePath());
        exit();
    }
}

/**
 * Get the correct path to login page based on current directory
 */
function getLoginPath() {
    // Check current directory structure
    $currentPath = $_SERVER['SCRIPT_FILENAME'];
    
    if (strpos($currentPath, '/admin/') !== false) {
        return '../public/login.php';
    } elseif (strpos($currentPath, '/client/') !== false) {
        return '../public/login.php';
    } elseif (strpos($currentPath, '/public/') !== false) {
        return 'login.php';
    } else {
        return 'public/login.php';
    }
}

/**
 * Get the correct path to homepage based on current directory
 */
function getHomePath() {
    $currentPath = $_SERVER['SCRIPT_FILENAME'];
    
    if (strpos($currentPath, '/admin/') !== false) {
        return '../index.php';
    } elseif (strpos($currentPath, '/client/') !== false) {
        return '../index.php';
    } else {
        return 'index.php';
    }
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['account_id'] ?? null;
}

/**
 * Get current user role
 */
function getCurrentUserRole() {
    return $_SESSION['role_id'] ?? null;
}

/**
 * Get current user name
 */
function getCurrentUserName() {
    $firstName = $_SESSION['first_name'] ?? '';
    $lastName = $_SESSION['last_name'] ?? '';
    return trim($firstName . ' ' . $lastName);
}

/**
 * Get current user email
 */
function getCurrentUserEmail() {
    return $_SESSION['email'] ?? null;
}

/**
 * Get user initials for avatar
 */
function getUserInitials() {
    $firstName = $_SESSION['first_name'] ?? '';
    $lastName = $_SESSION['last_name'] ?? '';
    
    $initials = '';
    if (!empty($firstName)) {
        $initials .= strtoupper(substr($firstName, 0, 1));
    }
    if (!empty($lastName)) {
        $initials .= strtoupper(substr($lastName, 0, 1));
    }
    
    return !empty($initials) ? $initials : 'U';
}

/**
 * Check if user has specific privilege
 */
function hasPrivilege($privilege) {
    // Future implementation for granular permissions
    return isAdmin();
}

/**
 * Redirect user based on role after login
 */
function redirectByRole() {
    if (isAdmin()) {
        header("Location: ../admin/dashboard.php");
    } elseif (isClient()) {
        header("Location: ../index.php");
    } else {
        header("Location: ../index.php");
    }
    exit();
}
?>