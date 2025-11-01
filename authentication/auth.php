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
 * Check if user is employee (role_id = 3)
 */
function isEmployee() {
    return isset($_SESSION['role_id']) && $_SESSION['role_id'] == 3;
}

/**
 * Require authentication - redirect to login if not logged in
 */
function requireAuth($redirectPath = null) {
    if (!isLoggedIn()) {
        if ($redirectPath === null) {
            $redirectPath = getLoginPath();
        }

        // Get the current full URL so we can redirect back after login
        $currentUrl = $_SERVER['REQUEST_URI'];
        $redirectUrl = $redirectPath . '?return_url=' . urlencode($currentUrl);

        echo "<script>
            if (window.top !== window.self) {
                // Break out of iframe and redirect the top window
                window.top.location.href = '$redirectUrl';
            } else {
                window.location.href = '$redirectUrl';
            }
        </script>";
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
 * Require employee access - redirect if not employee
 */
function requireEmployee() {
    if (!isLoggedIn()) {
        header("Location: " . getLoginPath());
        exit();
    }
    if (!isEmployee()) {
        header("Location: " . getHomePath());
        exit();
    }
}

/**
 * Get the correct path to login page based on current directory
 */
function getLoginPath() {
    $currentPath = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);

    // If script is inside client/pages (e.g. /client/pages/checkout.php)
    if (strpos($currentPath, '/client/pages/') !== false) {
        return '../../public/login.php';
    }

    // If script is inside client/ (but not pages)
    if (strpos($currentPath, '/client/') !== false) {
        return '../public/login.php';
    }

    // If inside admin (common)
    if (strpos($currentPath, '/admin/') !== false) {
        return '../public/login.php';
    }

    // If inside employee
    if (strpos($currentPath, '/employee/') !== false) {
        return '../public/login.php';
    }

    // If current script is in public folder already
    if (strpos($currentPath, '/public/') !== false) {
        return 'login.php';
    }

    // Fallback: absolute path from webroot to public
    return '/public/login.php';
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
    } elseif (strpos($currentPath, '/employee/') !== false) {
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
    } elseif (isEmployee()) {
        header("Location: ../employee/dashboard.php");
    } else {
        header("Location: ../index.php");
    }
    exit();
}
?>