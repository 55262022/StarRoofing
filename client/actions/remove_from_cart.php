<?php
require_once '../../database/starroofing_db.php';
require_once '../../authentication/auth.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_id'])) {
    $cart_id = intval($_POST['cart_id']);
    $account_id = $_SESSION['account_id'];

    $query = "DELETE FROM cart WHERE cart_id = ? AND account_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("ii", $cart_id, $account_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: ../pages/my_basket.php");
    exit;
} else {
    header("Location: ../pages/my_basket.php");
    exit;
}
?>