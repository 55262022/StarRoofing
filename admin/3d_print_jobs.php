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
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Check if table exists
$table_check = $conn->query("SHOW TABLES LIKE '3d_print_jobs'");
if ($table_check->num_rows === 0) {
    // Table doesn't exist yet
    $print_jobs = null;
    $total_jobs = 0;
    $counts = ['total' => 0, 'pending' => 0, 'queued' => 0, 'printing' => 0, 'completed' => 0, 'failed' => 0, 'cancelled' => 0];
} else {
    // Build query
    $query = "
        SELECT pj.*, 
               a.email as customer_email,
               up.first_name, up.last_name
        FROM 3d_print_jobs pj
        LEFT JOIN accounts a ON pj.account_id = a.id
        LEFT JOIN user_profiles up ON a.id = up.account_id
        WHERE 1=1
    ";

    $count_query = "SELECT COUNT(*) as total FROM 3d_print_jobs WHERE 1=1";

    $params = [];
    $types = "";

    if ($status_filter !== 'all') {
        $query .= " AND pj.status = ?";
        $count_query .= " AND status = ?";
        $params[] = $status_filter;
        $types .= "s";
    }

    if (!empty($search)) {
        $search_condition = " AND (pj.job_id LIKE ? OR pj.product_name LIKE ? OR a.email LIKE ?)";
        $query .= $search_condition;
        $count_query .= $search_condition;
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= "sss";
    }

    // Get total count
    $count_stmt = $conn->prepare($count_query);
    if (!empty($params)) {
        $count_stmt->bind_param($types, ...$params);
    }
    $count_stmt->execute();
    $total_jobs = $count_stmt->get_result()->fetch_assoc()['total'];
    $count_stmt->close();

    $total_pages = ceil($total_jobs / $per_page);

    // Get print jobs with pagination
    $query .= " ORDER BY pj.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $print_jobs = $stmt->get_result();
    $stmt->close();

    // Get job counts by status
    $counts_query = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'queued' THEN 1 ELSE 0 END) as queued,
            SUM(CASE WHEN status = 'printing' THEN 1 ELSE 0 END) as printing,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
        FROM 3d_print_jobs
    ";
    $counts_result = $conn->query($counts_query);
    $counts = $counts_result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>3D Print Jobs - Admin</title>
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

    .back-btn {
        padding: 12px 24px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 10px;
        color: #fff;
        text-decoration: none;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .back-btn:hover {
        background: rgba(233,185,73,0.1);
        color: #e9b949;
    }

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

    .badge {
        background: rgba(255,255,255,0.1);
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.85rem;
    }

    .filters {
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 15px;
        align-items: end;
    }

    .filter-input {
        padding: 12px 16px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 10px;
        color: #fff;
        font-size: 1rem;
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

    .jobs-container {
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 15px;
        overflow: hidden;
    }

    .job-card {
        padding: 25px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        transition: 0.3s;
    }

    .job-card:hover {
        background: rgba(255,255,255,0.05);
    }

    .job-header {
        display: grid;
        grid-template-columns: auto 1fr auto auto;
        gap: 20px;
        align-items: center;
        margin-bottom: 20px;
    }

    .job-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        background: rgba(233,185,73,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #e9b949;
    }

    .job-info {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .job-id {
        font-size: 1.2rem;
        font-weight: 700;
        color: #e9b949;
    }

    .job-meta {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        color: rgba(255,255,255,0.7);
        font-size: 0.9rem;
    }

    .job-status {
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: capitalize;
    }

    .status-pending { background: rgba(234, 179, 8, 0.2); color: #eab308; }
    .status-queued { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }
    .status-printing { background: rgba(168, 85, 247, 0.2); color: #a855f7; }
    .status-completed { background: rgba(34, 197, 94, 0.2); color: #22c55e; }
    .status-failed { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
    .status-cancelled { background: rgba(107, 114, 128, 0.2); color: #6b7280; }

    .job-details {
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

    .btn-update {
        background: rgba(168, 85, 247, 0.2);
        color: #a855f7;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: rgba(255,255,255,0.5);
    }

    @media (max-width: 768px) {
        .job-header {
            grid-template-columns: 1fr;
        }
    }
</style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-print"></i> 3D Print Jobs
            </h1>
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <?php if ($print_jobs !== null): ?>
        <div class="status-tabs">
            <a href="?status=all" class="status-tab <?= $status_filter === 'all' ? 'active' : '' ?>">
                All Jobs <span class="badge"><?= $counts['total'] ?></span>
            </a>
            <a href="?status=pending" class="status-tab <?= $status_filter === 'pending' ? 'active' : '' ?>">
                Pending <span class="badge"><?= $counts['pending'] ?></span>
            </a>
            <a href="?status=queued" class="status-tab <?= $status_filter === 'queued' ? 'active' : '' ?>">
                Queued <span class="badge"><?= $counts['queued'] ?></span>
            </a>
            <a href="?status=printing" class="status-tab <?= $status_filter === 'printing' ? 'active' : '' ?>">
                Printing <span class="badge"><?= $counts['printing'] ?></span>
            </a>
            <a href="?status=completed" class="status-tab <?= $status_filter === 'completed' ? 'active' : '' ?>">
                Completed <span class="badge"><?= $counts['completed'] ?></span>
            </a>
            <a href="?status=failed" class="status-tab <?= $status_filter === 'failed' ? 'active' : '' ?>">
                Failed <span class="badge"><?= $counts['failed'] ?></span>
            </a>
        </div>

        <div class="filters">
            <form method="GET" class="filter-grid">
                <input type="text" name="search" class="filter-input" 
                       placeholder="Search by Job ID, product name, or email..." 
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="filter-btn">
                    <i class="fas fa-search"></i> Search
                </button>
                <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
            </form>
        </div>

        <div class="jobs-container">
            <?php if ($print_jobs->num_rows > 0): ?>
                <?php while ($job = $print_jobs->fetch_assoc()): ?>
                    <div class="job-card">
                        <div class="job-header">
                            <div class="job-icon">
                                <i class="fas fa-cube"></i>
                            </div>
                            <div class="job-info">
                                <div class="job-id"><?= htmlspecialchars($job['job_id']) ?></div>
                                <div class="job-meta">
                                    <span><i class="fas fa-box"></i> <?= htmlspecialchars($job['product_name']) ?></span>
                                    <span><i class="fas fa-user"></i> <?= htmlspecialchars($job['customer_email']) ?></span>
                                    <span><i class="fas fa-clock"></i> <?= date('M j, Y - g:i A', strtotime($job['created_at'])) ?></span>
                                </div>
                            </div>
                            <div class="job-status status-<?= $job['status'] ?>">
                                <?= ucfirst($job['status']) ?>
                            </div>
                        </div>

                        <div class="job-details">
                            <div class="detail-item">
                                <span class="detail-label">Material</span>
                                <span class="detail-value"><?= strtoupper($job['material']) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Quality</span>
                                <span class="detail-value"><?= ucfirst($job['quality']) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Infill</span>
                                <span class="detail-value"><?= $job['infill'] ?>%</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Scale</span>
                                <span class="detail-value"><?= $job['scale'] ?>%</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Estimated Time</span>
                                <span class="detail-value"><?= htmlspecialchars($job['estimated_time']) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Estimated Cost</span>
                                <span class="detail-value"><?= htmlspecialchars($job['estimated_cost']) ?></span>
                            </div>
                        </div>

                        <?php if ($job['notes']): ?>
                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.05);">
                            <span class="detail-label">Notes:</span>
                            <p style="color: rgba(255,255,255,0.8); margin-top: 5px;"><?= htmlspecialchars($job['notes']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox" style="font-size: 4rem; opacity: 0.3; margin-bottom: 20px;"></i>
                    <h2>No Print Jobs Found</h2>
                    <p>No 3D print jobs match your current filters.</p>
                </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="empty-state" style="background: rgba(255,255,255,0.03); border-radius: 15px; padding: 60px;">
            <i class="fas fa-print" style="font-size: 4rem; opacity: 0.3; margin-bottom: 20px;"></i>
            <h2>No 3D Print Jobs Yet</h2>
            <p>3D print jobs will appear here once users start submitting them.</p>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>