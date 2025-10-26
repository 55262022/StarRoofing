<?php
include '../authentication/auth.php';
requireAdmin(); // Automatic redirect kung hindi admin
require_once '../database/starroofing_db.php';

$welcome_message = '';
if (isset($_SESSION['success'])) {
    $welcome_message = $_SESSION['success'];
    unset($_SESSION['success']);
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
                            <h3>248</h3>
                            <p>Total Clients</p>
                        </div>
                    </article>

                    <article class="stat-card">
                        <div class="stat-icon projects"><i class="fas fa-hard-hat"></i></div>
                        <div class="stat-info">
                            <h3>54</h3>
                            <p>Active Projects</p>
                        </div>
                    </article>

                    <article class="stat-card">
                        <div class="stat-icon revenue"><i class="fas fa-dollar-sign"></i></div>
                        <div class="stat-info">
                            <h3>₱1.2M</h3>
                            <p>Total Revenue</p>
                        </div>
                    </article>

                    <article class="stat-card">
                        <div class="stat-icon tasks"><i class="fas fa-tasks"></i></div>
                        <div class="stat-info">
                            <h3>18</h3>
                            <p>Pending Tasks</p>
                        </div>
                    </article>
                </section>

                <!-- Recent Projects Section -->
                <section class="card recent-projects" aria-labelledby="recent-projects-title">
                    <header class="card-header">
                        <h2 id="recent-projects-title" class="card-title">Recent Projects</h2>
                        <a href="#" class="card-action" aria-label="View all projects">View All</a>
                    </header>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Project Name</th>
                                        <th>Client</th>
                                        <th>Start Date</th>
                                        <th>Deadline</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Garcia Residence Roofing</td>
                                        <td>Rodrigo Garcia</td>
                                        <td>Oct 10, 2023</td>
                                        <td>Nov 15, 2023</td>
                                        <td><span class="status progress">In Progress</span></td>
                                    </tr>
                                    <tr>
                                        <td>San Juan Commercial Complex</td>
                                        <td>San Juan Development Corp</td>
                                        <td>Sep 28, 2023</td>
                                        <td>Dec 20, 2023</td>
                                        <td><span class="status progress">In Progress</span></td>
                                    </tr>
                                    <tr>
                                        <td>Santos Steel Truss Installation</td>
                                        <td>Maria Santos</td>
                                        <td>Oct 5, 2023</td>
                                        <td>Oct 30, 2023</td>
                                        <td><span class="status pending">Pending</span></td>
                                    </tr>
                                    <tr>
                                        <td>Rivera Roof Repair</td>
                                        <td>Carlos Rivera</td>
                                        <td>Sep 15, 2023</td>
                                        <td>Oct 5, 2023</td>
                                        <td><span class="status completed">Completed</span></td>
                                    </tr>
                                    <tr>
                                        <td>Nueva Ecija Government Building</td>
                                        <td>Nueva Ecija LGU</td>
                                        <td>Aug 20, 2023</td>
                                        <td>Nov 30, 2023</td>
                                        <td><span class="status progress">In Progress</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- Recent Clients Section -->
                <section class="card recent-clients" aria-labelledby="recent-clients-title">
                    <header class="card-header">
                        <h2 id="recent-clients-title" class="card-title">Recent Clients</h2>
                        <a href="#" class="card-action" aria-label="View all clients">View All</a>
                    </header>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Client Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Projects</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Antonio Dela Cruz</td>
                                        <td>antonio.dc@example.com</td>
                                        <td>0917-123-4567</td>
                                        <td>2</td>
                                        <td>Oct 15, 2023</td>
                                    </tr>
                                    <tr>
                                        <td>Elena Rodriguez</td>
                                        <td>elena.r@example.com</td>
                                        <td>0918-987-6543</td>
                                        <td>1</td>
                                        <td>Oct 12, 2023</td>
                                    </tr>
                                    <tr>
                                        <td>Roberto Santiago</td>
                                        <td>roberto.s@example.com</td>
                                        <td>0919-555-1234</td>
                                        <td>3</td>
                                        <td>Oct 10, 2023</td>
                                    </tr>
                                    <tr>
                                        <td>Marisol Hernandez</td>
                                        <td>marisol.h@example.com</td>
                                        <td>0916-777-8888</td>
                                        <td>1</td>
                                        <td>Oct 8, 2023</td>
                                    </tr>
                                    <tr>
                                        <td>Francisco Lim</td>
                                        <td>francisco.l@example.com</td>
                                        <td>0915-222-3333</td>
                                        <td>2</td>
                                        <td>Oct 5, 2023</td>
                                    </tr>
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
            <section id="estimation-section" class="section hidden">
                <iframe src="estimation.php" width="100%" height="100%" style="border:none; min-height:90vh;"></iframe>
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
