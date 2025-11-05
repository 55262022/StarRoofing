<?php
require_once '../../database/starroofing_db.php';
require_once '../../authentication/auth.php';
requireAuth();

$order_number = $_GET['order_number'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Failed - Star Roofing & Construction</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Montserrat', sans-serif;
        background: #0a0a0a;
        color: #fff;
        min-height: 100vh;
        padding: 40px 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .failed-container {
        max-width: 600px;
        width: 100%;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 24px;
        padding: 40px;
        backdrop-filter: blur(10px);
        text-align: center;
        animation: fadeInUp 0.6s ease;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .failed-icon {
        margin-bottom: 30px;
    }

    .failed-icon i {
        font-size: 5rem;
        color: #ef4444;
        animation: scaleIn 0.5s ease;
    }

    @keyframes scaleIn {
        from { transform: scale(0); }
        to { transform: scale(1); }
    }

    .failed-title {
        font-size: 2rem;
        font-weight: 800;
        color: #ef4444;
        margin-bottom: 10px;
    }

    .failed-message {
        color: rgba(255,255,255,0.7);
        margin-bottom: 30px;
        line-height: 1.6;
    }

    .order-number-box {
        background: rgba(239,68,68,0.1);
        border: 2px solid #ef4444;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 30px;
    }

    .order-number {
        font-size: 1.5rem;
        font-weight: 800;
        color: #ef4444;
        letter-spacing: 2px;
    }

    .action-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn {
        padding: 15px 30px;
        border-radius: 12px;
        border: none;
        font-weight: 700;
        cursor: pointer;
        transition: 0.3s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .btn-primary {
        background: #e9b949;
        color: #1a1a2e;
    }

    .btn-primary:hover {
        background: transparent;
        border: 2px solid #e9b949;
        color: #e9b949;
        box-shadow: 0 0 25px rgba(233,185,73,0.4);
        transform: translateY(-3px);
    }

    .btn-secondary {
        background: rgba(255,255,255,0.1);
        color: #fff;
        border: 2px solid rgba(255,255,255,0.2);
    }

    .btn-secondary:hover {
        background: rgba(255,255,255,0.15);
        transform: translateY(-3px);
    }

    .help-text {
        margin-top: 30px;
        padding: 20px;
        background: rgba(239,68,68,0.05);
        border-radius: 12px;
        color: rgba(255,255,255,0.7);
        font-size: 0.9rem;
    }

    @media (max-width: 768px) {
        .action-buttons {
            flex-direction: column;
        }
        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
</head>
<body>
    <div class="failed-container">
        <div class="failed-icon">
            <i class="fas fa-times-circle"></i>
        </div>

        <h1 class="failed-title">Payment Failed</h1>
        <p class="failed-message">
            We're sorry, but your payment could not be processed. Your order has been saved, 
            but payment was not successful.
        </p>

        <?php if (!empty($order_number)): ?>
        <div class="order-number-box">
            <div>Order Number</div>
            <div class="order-number"><?= htmlspecialchars($order_number) ?></div>
        </div>
        <?php endif; ?>

        <div class="action-buttons">
            <a href="checkout.php<?= !empty($order_number) ? '?retry=' . urlencode($order_number) : '' ?>" class="btn btn-primary">
                <i class="fas fa-redo"></i> Try Again
            </a>
            <a href="../materials.php" class="btn btn-secondary">
                <i class="fas fa-shopping-bag"></i> Back to Shop
            </a>
        </div>

        <div class="help-text">
            <i class="fas fa-info-circle"></i>
            <strong>Need help?</strong> Please check your payment details and try again, 
            or contact our support team for assistance.
        </div>
    </div>
</body>
</html>