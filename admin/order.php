<?php
require_once '../database/starroofing_db.php';
require_once '../authentication/auth.php';
requireAuth();

// Check if admin
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header('Location: ../index.php');
    exit();
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build query
$query = "
    SELECT o.*, 
           p.image_path,
           CONCAT(o.customer_first_name, ' ', o.customer_last_name) as customer_name,
           CONCAT(e.first_name, ' ', e.last_name) as assigned_employee_name
    FROM orders o
    LEFT JOIN products p ON o.product_id = p.product_id
    LEFT JOIN employees e ON o.assigned_employee_id = e.employee_id
    WHERE 1=1
";

$count_query = "SELECT COUNT(*) as total FROM orders WHERE 1=1";

$params = [];
$types = "";

if ($status_filter !== 'all') {
    $query .= " AND o.order_status = ?";
    $count_query .= " AND order_status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($search)) {
    $search_condition = " AND (o.order_number LIKE ? OR o.customer_first_name LIKE ? OR o.customer_last_name LIKE ? OR o.customer_email LIKE ?)";
    $query .= $search_condition;
    $count_query .= $search_condition;
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ssss";
}

if (!empty($date_from)) {
    $date_condition = " AND DATE(o.created_at) >= ?";
    $query .= $date_condition;
    $count_query .= str_replace('o.created_at', 'created_at', $date_condition);
    $params[] = $date_from;
    $types .= "s";
}

if (!empty($date_to)) {
    $date_condition = " AND DATE(o.created_at) <= ?";
    $query .= $date_condition;
    $count_query .= str_replace('o.created_at', 'created_at', $date_condition);
    $params[] = $date_to;
    $types .= "s";
}

// Get total count
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_orders = $count_stmt->get_result()->fetch_assoc()['total'];
$count_stmt->close();

$total_pages = ceil($total_orders / $per_page);

// Get orders with pagination
$query .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$orders = $stmt->get_result();
$stmt->close();

// Get order counts by status
$counts_query = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN order_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
        SUM(CASE WHEN order_status = 'to_ship' THEN 1 ELSE 0 END) as to_ship,
        SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered,
        SUM(CASE WHEN order_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
    FROM orders
";
$counts_result = $conn->query($counts_query);
$counts = $counts_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Management - Admin</title>
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
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 20px;
    }

    .page-title {
        font-size: 2rem;
        font-weight: 800;
        color: #e9b949;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    /* Status Tabs */
    .status-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 30px;
        overflow-x: auto;
        padding-bottom: 10px;
    }

    .status-tab {
        padding: 12px 24px;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 10px;
        color: rgba(255,255,255,0.7);
        text-decoration: none;
        transition: 0.3s;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
    }

    .status-tab.active {
        background: rgba(233,185,73,0.15);
        border-color: #e9b949;
        color: #e9b949;
    }

    .status-tab:hover {
        background: rgba(255,255,255,0.05);
    }

    .badge {
        background: rgba(255,255,255,0.1);
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.85rem;
    }

    /* Filters */
    .filters {
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr auto;
        gap: 15px;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .filter-group label {
        font-size: 0.9rem;
        color: rgba(255,255,255,0.7);
        font-weight: 600;
    }

    .filter-input {
        padding: 12px 16px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 10px;
        color: #fff;
        font-size: 1rem;
    }

    .filter-input:focus {
        outline: none;
        border-color: #e9b949;
        box-shadow: 0 0 0 3px rgba(233,185,73,0.15);
    }

    .filter-btn {
        padding: 12px 24px;
        background: #e9b949;
        border: none;
        border-radius: 10px;
        color: #1a1a2e;
        font-weight: 700;
        cursor: pointer;
        transition: 0.3s;
    }

    .filter-btn:hover {
        background: #d4a847;
        transform: translateY(-2px);
    }

    /* Orders Table */
    .orders-container {
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 15px;
        overflow: hidden;
    }

    .order-card {
        padding: 25px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        transition: 0.3s;
    }

    .order-card:hover {
        background: rgba(255,255,255,0.05);
    }

    .order-card:last-child {
        border-bottom: none;
    }

    .order-header {
        display: grid;
        grid-template-columns: auto 1fr auto auto;
        gap: 20px;
        align-items: center;
        margin-bottom: 20px;
    }

    .order-image {
        width: 80px;
        height: 80px;
        border-radius: 12px;
        overflow: hidden;
        background: rgba(255,255,255,0.05);
    }

    .order-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .order-info {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .order-number {
        font-size: 1.2rem;
        font-weight: 700;
        color: #e9b949;
    }

    .order-meta {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        color: rgba(255,255,255,0.7);
        font-size: 0.9rem;
    }

    .order-meta span {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .order-status {
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: capitalize;
    }

    .status-pending { background: rgba(234, 179, 8, 0.2); color: #eab308; }
    .status-confirmed { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }
    .status-to_ship { background: rgba(14, 165, 233, 0.2); color: #0ea5e9; }
    .status-delivered { background: rgba(34, 197, 94, 0.2); color: #22c55e; }
    .status-cancelled { background: rgba(239, 68, 68, 0.2); color: #ef4444; }

    .order-total {
        font-size: 1.3rem;
        font-weight: 700;
        color: #e9b949;
    }

    .order-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
    }

    .btn-view {
        background: rgba(59, 130, 246, 0.2);
        color: #3b82f6;
    }

    .btn-view:hover {
        background: rgba(59, 130, 246, 0.3);
    }

    .order-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        padding-top: 15px;
        border-top: 1px solid rgba(255,255,255,0.1);
    }

    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .detail-label {
        font-size: 0.85rem;
        color: rgba(255,255,255,0.6);
    }

    .detail-value {
        color: #fff;
        font-weight: 600;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: rgba(255,255,255,0.5);
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.3;
    }

    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-top: 30px;
    }

    .pagination a, .pagination span {
        padding: 10px 16px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px;
        color: #fff;
        text-decoration: none;
        transition: 0.3s;
    }

    .pagination a:hover {
        background: rgba(233,185,73,0.1);
        border-color: #e9b949;
        color: #e9b949;
    }

    .pagination .active {
        background: rgba(233,185,73,0.2);
        border-color: #e9b949;
        color: #e9b949;
    }

    .assigned-employee {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #22c55e;
        font-size: 0.9rem;
        margin-top: 5px;
    }

    @media (max-width: 1024px) {
        .filter-grid {
            grid-template-columns: 1fr 1fr;
        }

        .order-header {
            grid-template-columns: 1fr;
        }

        .order-actions {
            flex-direction: column;
        }
    }

    @media (max-width: 768px) {
        .filter-grid {
            grid-template-columns: 1fr;
        }

        .status-tabs {
            flex-wrap: wrap;
        }

        .order-details {
            grid-template-columns: 1fr;
        }
    }
</style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-box"></i> Product Orders
            </h1>
        </div>

        <!-- Status Tabs -->
        <div class="status-tabs">
            <a href="?status=all" class="status-tab <?= $status_filter === 'all' ? 'active' : '' ?>">
                All Orders <span class="badge"><?= $counts['total'] ?></span>
            </a>
            <a href="?status=pending" class="status-tab <?= $status_filter === 'pending' ? 'active' : '' ?>">
                Pending <span class="badge"><?= $counts['pending'] ?></span>
            </a>
            <a href="?status=confirmed" class="status-tab <?= $status_filter === 'confirmed' ? 'active' : '' ?>">
                Confirmed <span class="badge"><?= $counts['confirmed'] ?></span>
            </a>
            <a href="?status=to_ship" class="status-tab <?= $status_filter === 'to_ship' ? 'active' : '' ?>">
                To Ship <span class="badge"><?= $counts['to_ship'] ?></span>
            </a>
            <a href="?status=delivered" class="status-tab <?= $status_filter === 'delivered' ? 'active' : '' ?>">
                Delivered <span class="badge"><?= $counts['delivered'] ?></span>
            </a>
            <a href="?status=cancelled" class="status-tab <?= $status_filter === 'cancelled' ? 'active' : '' ?>">
                Cancelled <span class="badge"><?= $counts['cancelled'] ?></span>
            </a>
        </div>

        <!-- Filters -->
        <div class="filters">
            <form method="GET" class="filter-grid">
                <div class="filter-group">
                    <label>Search</label>
                    <input type="text" name="search" class="filter-input" 
                           placeholder="Order number, customer name, email..." 
                           value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="filter-group">
                    <label>From Date</label>
                    <input type="date" name="date_from" class="filter-input" 
                           value="<?= htmlspecialchars($date_from) ?>">
                </div>
                <div class="filter-group">
                    <label>To Date</label>
                    <input type="date" name="date_to" class="filter-input" 
                           value="<?= htmlspecialchars($date_to) ?>">
                </div>
                <button type="submit" class="filter-btn">
                    <i class="fas fa-search"></i> Filter
                </button>
                <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
            </form>
        </div>

        <!-- Orders List -->
        <div class="orders-container">
            <?php if ($orders->num_rows > 0): ?>
                <?php while ($order = $orders->fetch_assoc()): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-image">
                                <img src="/STARROOFING/<?= htmlspecialchars($order['image_path'] ?? 'no-image.png') ?>" 
                                     alt="Product">
                            </div>
                            <div class="order-info">
                                <div class="order-number">#<?= htmlspecialchars($order['order_number']) ?></div>
                                <div class="order-meta">
                                    <span><i class="fas fa-user"></i> <?= htmlspecialchars($order['customer_name']) ?></span>
                                    <span><i class="fas fa-envelope"></i> <?= htmlspecialchars($order['customer_email']) ?></span>
                                    <span><i class="fas fa-clock"></i> <?= date('M j, Y - g:i A', strtotime($order['created_at'])) ?></span>
                                </div>
                                <?php if (!empty($order['assigned_employee_name'])): ?>
                                    <div class="assigned-employee">
                                        <i class="fas fa-user-check"></i>
                                        Assigned to: <?= htmlspecialchars($order['assigned_employee_name']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="order-status status-<?= $order['order_status'] ?>">
                                <?= ucfirst($order['order_status']) ?>
                            </div>
                            <div class="order-total">
                                ₱<?= number_format($order['total_amount'], 2) ?>
                            </div>
                        </div>

                        <div class="order-details">
                            <div class="detail-item">
                                <span class="detail-label">Product</span>
                                <span class="detail-value"><?= htmlspecialchars($order['product_name']) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Quantity</span>
                                <span class="detail-value"><?= $order['quantity'] ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Payment Method</span>
                                <span class="detail-value"><?= strtoupper($order['payment_method']) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Delivery Address</span>
                                <span class="detail-value">
                                    <?= htmlspecialchars($order['delivery_city'] . ', ' . $order['delivery_province']) ?>
                                </span>
                            </div>
                        </div>

                        <div class="order-actions" style="margin-top: 20px;">
                            <a href="order-product/view_order.php?order_id=<?= $order['order_id'] ?>" class="btn btn-view">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h2>No Orders Found</h2>
                    <p>No orders match your current filters.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?status=<?= $status_filter ?>&search=<?= urlencode($search) ?>&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>&page=<?= $page - 1 ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>

            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <a href="?status=<?= $status_filter ?>&search=<?= urlencode($search) ?>&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>&page=<?= $i ?>" 
                   class="<?= $i === $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?status=<?= $status_filter ?>&search=<?= urlencode($search) ?>&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>&page=<?= $page + 1 ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>