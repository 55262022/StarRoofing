<?php
session_start();

// Redirect if not authenticated
if (!isset($_SESSION['account_id'])) {
    header("Location: ../public/404.php");
    exit();
}

// Redirect if the file is accessed directly via URL
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    header("Location: ../public/404.php");
    exit();
}
?>