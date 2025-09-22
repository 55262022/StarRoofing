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
    <title>Schedule - Star Roofing & Construction</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        
        .stat-icon.projects {
            background: rgba(72, 187, 120, 0.1);
            color: #48bb78;
        }
        
        .stat-icon.tasks {
            background: rgba(245, 101, 101, 0.1);
            color: #f56565;
        }
        
        .stat-icon.completed {
            background: rgba(66, 153, 225, 0.1);
            color: #4299e1;
        }
        
        .stat-icon.duration {
            background: rgba(246, 173, 85, 0.1);
            color: #f6ad55;
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
        
        /* Schedule Controls */
        .schedule-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .filter-controls {
            display: flex;
            gap: 1rem;
        }
        
        .filter-select {
            padding: 0.5rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            background: white;
            font-family: 'Montserrat', sans-serif;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-primary {
            background: #4299e1;
            color: white;
        }
        
        .btn-primary:hover {
            background: #3182ce;
        }
        
        /* Card Styles */
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
        
        .status.delayed {
            background: #fed7d7;
            color: #c53030;
        }
        
        /* Gantt Chart */
        .gantt-container {
            margin-top: 2rem;
        }
        
        .gantt-bar {
            height: 30px;
            background: #4299e1;
            border-radius: 4px;
            margin-bottom: 10px;
            position: relative;
            display: flex;
            align-items: center;
            padding: 0 10px;
            color: white;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .gantt-bar.delayed {
            background: #f56565;
        }
        
        .gantt-bar.completed {
            background: #48bb78;
        }
        
        .gantt-bar.pending {
            background: #ed8936;
        }
        
        .gantt-timeline {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            padding: 0 10px;
            font-size: 0.8rem;
            color: #718096;
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
            
            .schedule-controls {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filter-controls {
                width: 100%;
                flex-wrap: wrap;
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
                <h1 class="page-title">Project Schedule</h1>
                
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon projects">
                            <i class="fas fa-hard-hat"></i>
                        </div>
                        <div class="stat-info">
                            <h3>8</h3>
                            <p>Active Tasks</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon tasks">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="stat-info">
                            <h3>3</h3>
                            <p>Pending Tasks</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon completed">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3>5</h3>
                            <p>Completed Tasks</p>
                        </div>
                    </div>
                    
                    <div class="stat-icon duration">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3>180</h3>
                        <p>Total Project Days</p>
                    </div>
                </div>
                
                <!-- Schedule Controls -->
                <div class="schedule-controls">
                    <div class="filter-controls">
                        <select class="filter-select">
                            <option>All Projects</option>
                            <option>Valmonte Residence</option>
                            <option>Garcia Roofing</option>
                            <option>San Juan Complex</option>
                        </select>
                        <select class="filter-select">
                            <option>All Status</option>
                            <option>In Progress</option>
                            <option>Completed</option>
                            <option>Pending</option>
                            <option>Delayed</option>
                        </select>
                        <select class="filter-select">
                            <option>Sort by Start Date</option>
                            <option>Sort by Deadline</option>
                            <option>Sort by Priority</option>
                        </select>
                    </div>
                    <button class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Task
                    </button>
                </div>
                
                <!-- Schedule Table -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Project Timeline</h2>
                        <a href="#" class="card-action">Export Schedule</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Task Name</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Duration</th>
                                        <th>Progress</th>
                                        <th>Status</th>
                                        <th>Assigned To</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Site Preparation & Excavation</td>
                                        <td>Jan 3, 2025</td>
                                        <td>Feb 7, 2025</td>
                                        <td>36 days</td>
                                        <td>100%</td>
                                        <td><span class="status completed">Completed</span></td>
                                        <td>Construction Team A</td>
                                    </tr>
                                    <tr>
                                        <td>Foundation & Concrete Works</td>
                                        <td>Feb 10, 2025</td>
                                        <td>Mar 30, 2025</td>
                                        <td>49 days</td>
                                        <td>85%</td>
                                        <td><span class="status progress">In Progress</span></td>
                                        <td>Construction Team B</td>
                                    </tr>
                                    <tr>
                                        <td>Structural Steel & Roof Framing</td>
                                        <td>Apr 1, 2025</td>
                                        <td>May 15, 2025</td>
                                        <td>45 days</td>
                                        <td>60%</td>
                                        <td><span class="status progress">In Progress</span></td>
                                        <td>Steel Team</td>
                                    </tr>
                                    <tr>
                                        <td>Masonry & Wall Construction</td>
                                        <td>Mar 25, 2025</td>
                                        <td>Apr 25, 2025</td>
                                        <td>32 days</td>
                                        <td>75%</td>
                                        <td><span class="status progress">In Progress</span></td>
                                        <td>Masonry Team</td>
                                    </tr>
                                    <tr>
                                        <td>Roof Installation</td>
                                        <td>May 1, 2025</td>
                                        <td>May 20, 2025</td>
                                        <td>20 days</td>
                                        <td>0%</td>
                                        <td><span class="status pending">Pending</span></td>
                                        <td>Roofing Team</td>
                                    </tr>
                                    <tr>
                                        <td>Interior Finishing</td>
                                        <td>May 16, 2025</td>
                                        <td>Jun 20, 2025</td>
                                        <td>36 days</td>
                                        <td>0%</td>
                                        <td><span class="status pending">Pending</span></td>
                                        <td>Finishing Team</td>
                                    </tr>
                                    <tr>
                                        <td>Final Inspection & Handover</td>
                                        <td>Jun 21, 2025</td>
                                        <td>Jun 25, 2025</td>
                                        <td>5 days</td>
                                        <td>0%</td>
                                        <td><span class="status pending">Pending</span></td>
                                        <td>Quality Team</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Gantt Chart -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Project Gantt Chart</h2>
                        <a href="#" class="card-action">View Full Chart</a>
                    </div>
                    <div class="card-body">
                        <div class="gantt-container">
                            <div class="gantt-bar completed" style="width: 20%">Site Preparation (Jan 3 - Feb 7)</div>
                            <div class="gantt-bar progress" style="width: 27%">Foundation & Concrete (Feb 10 - Mar 30)</div>
                            <div class="gantt-bar progress" style="width: 25%">Structural Steel (Apr 1 - May 15)</div>
                            <div class="gantt-bar progress" style="width: 18%">Masonry (Mar 25 - Apr 25)</div>
                            <div class="gantt-bar pending" style="width: 11%">Roof Installation (May 1 - May 20)</div>
                            <div class="gantt-bar pending" style="width: 20%">Interior Finishing (May 16 - Jun 20)</div>
                            <div class="gantt-bar pending" style="width: 3%">Final Inspection (Jun 21 - Jun 25)</div>
                            
                            <div class="gantt-timeline">
                                <span>Jan 2025</span>
                                <span>Feb 2025</span>
                                <span>Mar 2025</span>
                                <span>Apr 2025</span>
                                <span>May 2025</span>
                                <span>Jun 2025</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Upcoming Milestones -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Upcoming Milestones</h2>
                        <a href="#" class="card-action">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Milestone</th>
                                        <th>Project</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Concrete Pouring Completion</td>
                                        <td>Valmonte Residence</td>
                                        <td>Mar 30, 2025</td>
                                        <td><span class="status progress">In Progress</span></td>
                                        <td>High</td>
                                    </tr>
                                    <tr>
                                        <td>Steel Structure Inspection</td>
                                        <td>Valmonte Residence</td>
                                        <td>Apr 15, 2025</td>
                                        <td><span class="status pending">Pending</span></td>
                                        <td>High</td>
                                    </tr>
                                    <tr>
                                        <td>Roofing Materials Delivery</td>
                                        <td>Valmonte Residence</td>
                                        <td>Apr 28, 2025</td>
                                        <td><span class="status pending">Pending</span></td>
                                        <td>Medium</td>
                                    </tr>
                                    <tr>
                                        <td>Masonry Completion</td>
                                        <td>Valmonte Residence</td>
                                        <td>Apr 25, 2025</td>
                                        <td><span class="status progress">In Progress</span></td>
                                        <td>High</td>
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
    
    // Simple interactivity for the schedule page
    document.addEventListener('DOMContentLoaded', function() {
        // Add click event to table rows for more details
        const tableRows = document.querySelectorAll('tbody tr');
        tableRows.forEach(row => {
            row.addEventListener('click', function() {
                const taskName = this.cells[0].textContent;
                Swal.fire({
                    title: taskName,
                    text: 'Task details would appear here in a real implementation.',
                    icon: 'info',
                    confirmButtonText: 'OK'
                });
            });
        });
        
        // Filter functionality (basic implementation)
        const filterSelects = document.querySelectorAll('.filter-select');
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                // In a real implementation, this would filter the table
                console.log('Filter changed to:', this.value);
            });
        });
    });
    </script>

</body>
</html>