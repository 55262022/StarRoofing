<?php
include '../authentication/auth.php';
requireAdmin(); // Automatic redirect kung hindi admin
require_once '../database/starroofing_db.php';

$welcome_message = '';
if (isset($_SESSION['success'])) {
    $welcome_message = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Initialize default values
$total_clients = 0;
$total_inquiries = 0;
$active_orders = 0;
$completed_orders = 0;
$total_revenue = 0;
$recent_projects = [];
$recent_clients = [];

// Check if database connection exists
if (isset($conn) && $conn) {
    // Total Clients (accounts with role_id = 2, assuming 2 is client role)
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM accounts WHERE role_id = 2 AND account_status = 'active'");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $total_clients = $row['total'];
    }

    // Total Inquiries (both accepted and pending)
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM inquiries");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $total_inquiries = $row['total'];
    }

    // Active Orders (pending, confirmed, processing, shipped)
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE order_status IN ('pending', 'confirmed', 'processing', 'shipped')");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $active_orders = $row['total'];
    }

    // Completed Orders (delivered)
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE order_status = 'delivered'");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $completed_orders = $row['total'];
    }

    // Total Revenue (from delivered orders only)
    $result = mysqli_query($conn, "SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE order_status = 'delivered' AND payment_status = 'paid'");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $total_revenue = $row['total'];
    }

    // Recent Projects (Latest 5 orders)
    $query = "
        SELECT 
            o.order_number,
            CONCAT(o.customer_first_name, ' ', o.customer_last_name) as client_name,
            o.product_name,
            DATE_FORMAT(o.created_at, '%b %d, %Y') as start_date,
            o.order_status,
            o.total_amount
        FROM orders o
        ORDER BY o.created_at DESC
        LIMIT 5
    ";
    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $recent_projects[] = $row;
        }
    }

    // Recent Clients (Latest 5 accounts)
    $query = "
        SELECT 
            up.first_name,
            up.last_name,
            a.email,
            up.contact_number,
            DATE_FORMAT(a.created_at, '%b %d, %Y') as joined_date,
            (SELECT COUNT(*) FROM orders WHERE account_id = a.id) as project_count
        FROM accounts a
        LEFT JOIN user_profiles up ON a.id = up.account_id
        WHERE a.role_id = 2
        ORDER BY a.created_at DESC
        LIMIT 5
    ";
    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $recent_clients[] = $row;
        }
    }
}

// Format revenue
$formatted_revenue = '₱' . number_format($total_revenue, 2);

// Status badges helper function
function getStatusBadge($status) {
    $badges = [
        'pending' => 'pending',
        'confirmed' => 'progress',
        'processing' => 'progress',
        'shipped' => 'progress',
        'delivered' => 'completed',
        'cancelled' => 'pending'
    ];
    return $badges[$status] ?? 'pending';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Star Roofing & Construction</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS Styles -->
    <link rel="stylesheet" href="../css/admin_main.css">
    <link rel="stylesheet" href="../css/admin_dashboard.css">
</head>
<style>
    .hidden{
        display: none;
    }
</style>
<body>
    <div class="main-container">
        
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <?php include '../includes/admin_sidebar.php'; ?>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            
            <!-- Top Navigation Bar -->
            <header class="top-nav">
                <?php include '../includes/admin_navbar.php'; ?>
            </header>

            <!-- Dashboard Section -->
            <section id="dashboard-section" class="section hidden dashboard-content" aria-labelledby="dashboard-title">
                <h1 id="dashboard-title" class="page-title">Dashboard Overview</h1>

                <!-- Statistics Section -->
                <section class="stats-grid" aria-label="Dashboard Statistics">
                    <article class="stat-card">
                        <div class="stat-icon clients"><i class="fas fa-users"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $total_clients; ?></h3>
                            <p>Total Clients</p>
                        </div>
                    </article>

                    <article class="stat-card">
                        <div class="stat-icon projects"><i class="fas fa-hard-hat"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $active_orders; ?></h3>
                            <p>Active Orders</p>
                        </div>
                    </article>

                    <article class="stat-card">
                        <div class="stat-icon revenue"><i class="fas fa-dollar-sign"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $formatted_revenue; ?></h3>
                            <p>Total Revenue</p>
                        </div>
                    </article>

                    <article class="stat-card">
                        <div class="stat-icon tasks"><i class="fas fa-envelope"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $total_inquiries; ?></h3>
                            <p>Total Inquiries</p>
                        </div>
                    </article>
                </section>

                <!-- Recent Projects Section -->
                <section class="card recent-projects" aria-labelledby="recent-projects-title">
                    <header class="card-header">
                        <h2 id="recent-projects-title" class="card-title">Recent Orders</h2>
                        <a href="#order-section" class="card-action" aria-label="View all orders">View All</a>
                    </header>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Order Number</th>
                                        <th>Client</th>
                                        <th>Product</th>
                                        <th>Order Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_projects)): ?>
                                        <tr>
                                            <td colspan="6" style="text-align: center;">No recent orders found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_projects as $project): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($project['order_number']); ?></td>
                                                <td><?php echo htmlspecialchars($project['client_name']); ?></td>
                                                <td><?php echo htmlspecialchars($project['product_name']); ?></td>
                                                <td><?php echo htmlspecialchars($project['start_date']); ?></td>
                                                <td>₱<?php echo number_format($project['total_amount'], 2); ?></td>
                                                <td>
                                                    <span class="status <?php echo getStatusBadge($project['order_status']); ?>">
                                                        <?php echo ucfirst($project['order_status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- Recent Clients Section -->
                <section class="card recent-clients" aria-labelledby="recent-clients-title">
                    <header class="card-header">
                        <h2 id="recent-clients-title" class="card-title">Recent Clients</h2>
                        <a href="#clients-section" class="card-action" aria-label="View all clients">View All</a>
                    </header>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Client Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Orders</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_clients)): ?>
                                        <tr>
                                            <td colspan="5" style="text-align: center;">No clients found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_clients as $client): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($client['email']); ?></td>
                                                <td><?php echo htmlspecialchars($client['contact_number'] ?? 'N/A'); ?></td>
                                                <td><?php echo $client['project_count']; ?></td>
                                                <td><?php echo htmlspecialchars($client['joined_date']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </section>

            <!-- 3d model page -->
            <section id="3dmodel-section" class="section hidden">
                <iframe src="3dmodel.php" width="100%" height="100%" style="border:none; min-height:90vh;"></iframe>
            </section>
            <!-- inventory page -->
            <section id="inventory-section" class="section hidden">
                <iframe src="inventory.php" width="100%" height="100%" style="border:none; min-height:90vh;"></iframe>
            </section>
            <!-- estimation page -->
            <section id="order-section" class="section hidden">
                <iframe src="order.php" width="100%" height="100%" style="border:none; min-height:90vh;"></iframe>
            </section>
            <!-- employees page -->
            <section id="employees-section" class="section hidden">
                <iframe src="employees.php" width="100%" height="100%" style="border:none; min-height:90vh;"></iframe>
            </section>
            <!-- clients page -->
            <section id="clients-section" class="section hidden">
                <iframe src="clients.php" width="100%" height="100%" style="border:none; min-height:90vh;"></iframe>
            </section>
            <!-- messages page -->
            <section id="messages-section" class="section hidden">
                <iframe src="messages.php" width="100%" height="100%" style="border:none; min-height:90vh;"></iframe>
            </section>
            <!-- reports page -->
            <section id="reports-section" class="section hidden">
                <iframe src="reports.php" width="100%" height="100%" style="border:none; min-height:90vh;"></iframe>
            </section>
            <!-- archive page -->
            <section id="archive-section" class="section hidden">
                <iframe src="archive.php" width="100%" height="100%" style="border:none; min-height:90vh;"></iframe>
            </section>
        </main>
    </div>

    <!-- SweetAlert for Welcome Message -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    <?php if (!empty($welcome_message)): ?>
        Swal.fire({
            icon: 'info',
            title: 'Welcome Admin',
            text: '<?php echo addslashes($welcome_message); ?>',
            timer: 3000,
            confirmButtonColor: '#3B71CA'
        });
    <?php endif; ?>
    </script>
</body>
</html>