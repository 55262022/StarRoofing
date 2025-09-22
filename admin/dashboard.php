<?php
include '../includes/auth.php';
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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- <link rel="stylesheet" href="../css/admin_dashboard.css"> -->
     <style>
                /* Base styles and reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f7f9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Dashboard Layout */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Main Content Area */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        /* Dashboard Content */
        .dashboard-content {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
        }
        
        .page-title {
            font-size: 1.8rem;
            color: #1a365d;
            margin-bottom: 1.5rem;
            font-weight: 700;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.5rem;
        }
        
        .stat-icon.clients {
            background: rgba(66, 153, 225, 0.1);
            color: #4299e1;
        }
        
        .stat-icon.projects {
            background: rgba(72, 187, 120, 0.1);
            color: #48bb78;
        }
        
        .stat-icon.revenue {
            background: rgba(246, 173, 85, 0.1);
            color: #f6ad55;
        }
        
        .stat-icon.tasks {
            background: rgba(245, 101, 101, 0.1);
            color: #f56565;
        }
        
        .stat-info h3 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1a365d;
            margin-bottom: 0.2rem;
        }
        
        .stat-info p {
            color: #718096;
            font-size: 0.9rem;
        }
        
        /* Recent Activity */
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        
        .card-header {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1a365d;
        }
        
        .card-action {
            color: #4299e1;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead th {
            background: #f5f7f9;
            padding: 0.8rem 1rem;
            text-align: left;
            font-weight: 600;
            color: #1a365d;
            border-bottom: 1px solid #e2e8f0;
        }
        
        tbody td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            color: #4a5568;
        }
        
        tbody tr:hover {
            background: #f5f7f9;
        }
        
        .status {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status.completed {
            background: #c6f6d5;
            color: #2d7738;
        }
        
        .status.pending {
            background: #feebcb;
            color: #b44d12;
        }
        
        .status.progress {
            background: #c3ddfd;
            color: #2c5282;
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar-header, .sidebar-menu span {
                display: none;
            }
            
            .sidebar-menu a {
                justify-content: center;
                padding: 1rem;
            }
            
            .sidebar-menu i {
                margin-right: 0;
                font-size: 1.3rem;
            }
            
            .search-box {
                width: 200px;
            }
        }
        
        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }
            
            .sidebar {
                position: fixed;
                left: -250px;
                height: 100%;
                z-index: 1000;
            }
            
            .sidebar.active {
                left: 0;
            }
            
            .search-box {
                display: none;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .user-name {
                display: none;
            }
        }
     </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation -->
             <?php include '../includes/admin_navbar.php'; ?>
            
            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <h1 class="page-title">Dashboard Overview</h1>
                
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon clients">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3>248</h3>
                            <p>Total Clients</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon projects">
                            <i class="fas fa-hard-hat"></i>
                        </div>
                        <div class="stat-info">
                            <h3>54</h3>
                            <p>Active Projects</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon revenue">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>₱1.2M</h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon tasks">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="stat-info">
                            <h3>18</h3>
                            <p>Pending Tasks</p>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Projects -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Recent Projects</h2>
                        <a href="#" class="card-action">View All</a>
                    </div>
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
                </div>
                
                <!-- Recent Clients -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Recent Clients</h2>
                        <a href="#" class="card-action">View All</a>
                    </div>
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
                </div>
            </div>
        </div>
    </div>

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