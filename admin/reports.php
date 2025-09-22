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
    <title>Reports - Star Roofing & Construction</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
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
        
        .stat-icon.reports {
            background: rgba(72, 187, 120, 0.1);
            color: #48bb78;
        }
        
        .stat-icon.projects {
            background: rgba(66, 153, 225, 0.1);
            color: #4299e1;
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
        
        /* Report Controls */
        .report-controls {
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
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: #4299e1;
            color: white;
        }
        
        .btn-primary:hover {
            background: #3182ce;
        }
        
        .btn-success {
            background: #48bb78;
            color: white;
        }
        
        .btn-success:hover {
            background: #38a169;
        }
        
        .btn-danger {
            background: #f56565;
            color: white;
        }
        
        .btn-danger:hover {
            background: #e53e3e;
        }
        
        .btn-group {
            display: flex;
            gap: 0.5rem;
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
        
        /* Report Cards */
        .report-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .report-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #4299e1;
            transition: transform 0.3s;
        }
        
        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }
        
        .report-card h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: #1a365d;
        }
        
        .report-card p {
            color: #718096;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        
        .report-card .report-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: #a0aec0;
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
        
        /* Charts */
        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .chart-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .chart-placeholder {
            height: 250px;
            background: #f8fafc;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #a0aec0;
            font-size: 1rem;
        }
        
        /* Print Styles */
        @media print {
            body * {
                visibility: hidden;
            }
            
            .print-section, .print-section * {
                visibility: visible;
            }
            
            .print-section {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            
            .no-print {
                display: none !important;
            }
            
            .card {
                box-shadow: none;
                border: 1px solid #e2e8f0;
            }
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
            
            .report-controls {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filter-controls {
                width: 100%;
                flex-wrap: wrap;
            }
            
            .btn-group {
                width: 100%;
                justify-content: flex-start;
            }
            
            .report-cards, .charts-container {
                grid-template-columns: 1fr;
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
                <h1 class="page-title">Reports & Analytics</h1>
                
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon reports">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="stat-info">
                            <h3>24</h3>
                            <p>Total Reports</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon projects">
                            <i class="fas fa-hard-hat"></i>
                        </div>
                        <div class="stat-info">
                            <h3>8</h3>
                            <p>Active Projects</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon revenue">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>₱2.4M</h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon tasks">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="stat-info">
                            <h3>42</h3>
                            <p>Completed Tasks</p>
                        </div>
                    </div>
                </div>
                
                <!-- Report Controls -->
                <div class="report-controls">
                    <div class="filter-controls">
                        <select class="filter-select" id="reportType">
                            <option value="all">All Reports</option>
                            <option value="project">Project Reports</option>
                            <option value="financial">Financial Reports</option>
                            <option value="progress">Progress Reports</option>
                            <option value="inventory">Inventory Reports</option>
                        </select>
                        <select class="filter-select" id="timeRange">
                            <option value="all">All Time</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="quarter">This Quarter</option>
                            <option value="year">This Year</option>
                        </select>
                        <select class="filter-select" id="projectFilter">
                            <option value="all">All Projects</option>
                            <option value="valmonte">Valmonte Residence</option>
                            <option value="garcia">Garcia Roofing</option>
                            <option value="san-juan">San Juan Complex</option>
                        </select>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-primary" id="generatePdf">
                            <i class="fas fa-file-pdf"></i> PDF Report
                        </button>
                        <button class="btn btn-success" id="printReport">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                        <button class="btn btn-danger" id="exportData">
                            <i class="fas fa-file-export"></i> Export Data
                        </button>
                    </div>
                </div>
                
                <!-- Report Cards -->
                <div class="report-cards">
                    <div class="report-card">
                        <h3>Project Progress Report</h3>
                        <p>Detailed analysis of project milestones, timelines, and completion status.</p>
                        <div class="report-meta">
                            <span>Updated: Mar 15, 2025</span>
                            <span>12 Pages</span>
                        </div>
                    </div>
                    
                    <div class="report-card">
                        <h3>Financial Summary Q1 2025</h3>
                        <p>Revenue, expenses, and profitability analysis for the first quarter.</p>
                        <div class="report-meta">
                            <span>Updated: Mar 31, 2025</span>
                            <span>8 Pages</span>
                        </div>
                    </div>
                    
                    <div class="report-card">
                        <h3>Inventory Status Report</h3>
                        <p>Current stock levels, material usage, and procurement recommendations.</p>
                        <div class="report-meta">
                            <span>Updated: Mar 28, 2025</span>
                            <span>6 Pages</span>
                        </div>
                    </div>
                </div>
                
                <!-- Charts Section -->
                <div class="charts-container">
                    <div class="chart-card">
                        <h3>Project Progress Overview</h3>
                        <div class="chart-placeholder">
                            <i class="fas fa-chart-pie" style="font-size: 2rem; margin-right: 1rem;"></i>
                            Progress Chart Visualization
                        </div>
                    </div>
                    
                    <div class="chart-card">
                        <h3>Revenue vs Expenses</h3>
                        <div class="chart-placeholder">
                            <i class="fas fa-chart-line" style="font-size: 2rem; margin-right: 1rem;"></i>
                            Financial Chart Visualization
                        </div>
                    </div>
                </div>
                
                <!-- Report Data Table -->
                <div class="card print-section">
                    <div class="card-header">
                        <h2 class="card-title">Detailed Project Report - Valmonte Residence</h2>
                        <div class="btn-group no-print">
                            <button class="btn btn-primary" onclick="exportTableToPDF()">
                                <i class="fas fa-file-pdf"></i> Export Table
                            </button>
                            <button class="btn btn-success" onclick="window.print()">
                                <i class="fas fa-print"></i> Print Table
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="reportTable">
                                <thead>
                                    <tr>
                                        <th>Task Description</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Progress</th>
                                        <th>Status</th>
                                        <th>Cost (₱)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Site Preparation & Excavation</td>
                                        <td>Jan 3, 2025</td>
                                        <td>Jan 20, 2025</td>
                                        <td>100%</td>
                                        <td><span class="status completed">Completed</span></td>
                                        <td>125,000</td>
                                    </tr>
                                    <tr>
                                        <td>Foundation Construction</td>
                                        <td>Jan 21, 2025</td>
                                        <td>Feb 15, 2025</td>
                                        <td>100%</td>
                                        <td><span class="status completed">Completed</span></td>
                                        <td>350,000</td>
                                    </tr>
                                    <tr>
                                        <td>Structural Framework</td>
                                        <td>Feb 16, 2025</td>
                                        <td>Mar 30, 2025</td>
                                        <td>85%</td>
                                        <td><span class="status progress">In Progress</span></td>
                                        <td>620,000</td>
                                    </tr>
                                    <tr>
                                        <td>Roofing System Installation</td>
                                        <td>Mar 25, 2025</td>
                                        <td>Apr 30, 2025</td>
                                        <td>60%</td>
                                        <td><span class="status progress">In Progress</span></td>
                                        <td>280,000</td>
                                    </tr>
                                    <tr>
                                        <td>Electrical & Plumbing</td>
                                        <td>Apr 10, 2025</td>
                                        <td>May 15, 2025</td>
                                        <td>30%</td>
                                        <td><span class="status progress">In Progress</span></td>
                                        <td>180,000</td>
                                    </tr>
                                    <tr>
                                        <td>Interior Finishing</td>
                                        <td>May 15, 2025</td>
                                        <td>Jun 30, 2025</td>
                                        <td>0%</td>
                                        <td><span class="status pending">Pending</span></td>
                                        <td>350,000</td>
                                    </tr>
                                    <tr>
                                        <td>Landscaping & Exterior</td>
                                        <td>Jun 15, 2025</td>
                                        <td>Jul 15, 2025</td>
                                        <td>0%</td>
                                        <td><span class="status pending">Pending</span></td>
                                        <td>120,000</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" style="text-align: right; font-weight: bold;">Total Budget:</td>
                                        <td style="font-weight: bold;">₱2,025,000</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" style="text-align: right; font-weight: bold;">Amount Spent:</td>
                                        <td style="font-weight: bold;">₱1,375,000</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" style="text-align: right; font-weight: bold;">Remaining Budget:</td>
                                        <td style="font-weight: bold; color: #48bb78;">₱650,000</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Reports -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Recently Generated Reports</h2>
                        <a href="#" class="card-action">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Report Name</th>
                                        <th>Type</th>
                                        <th>Generated Date</th>
                                        <th>Size</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Valmonte_Progress_Mar2025.pdf</td>
                                        <td>Progress Report</td>
                                        <td>Mar 15, 2025</td>
                                        <td>2.4 MB</td>
                                        <td>
                                            <button class="btn btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">
                                                <i class="fas fa-download"></i> Download
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Financial_Summary_Q1_2025.pdf</td>
                                        <td>Financial Report</td>
                                        <td>Mar 31, 2025</td>
                                        <td>1.8 MB</td>
                                        <td>
                                            <button class="btn btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">
                                                <i class="fas fa-download"></i> Download
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Inventory_Status_Mar2025.pdf</td>
                                        <td>Inventory Report</td>
                                        <td>Mar 28, 2025</td>
                                        <td>1.2 MB</td>
                                        <td>
                                            <button class="btn btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">
                                                <i class="fas fa-download"></i> Download
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Project_Timeline_Valmonte.pdf</td>
                                        <td>Timeline Report</td>
                                        <td>Mar 20, 2025</td>
                                        <td>3.1 MB</td>
                                        <td>
                                            <button class="btn btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">
                                                <i class="fas fa-download"></i> Download
                                            </button>
                                        </td>
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
    
    // PDF and Print Functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Generate PDF Report
        document.getElementById('generatePdf').addEventListener('click', function() {
            Swal.fire({
                title: 'Generating PDF Report',
                html: 'Please wait while we prepare your report...',
                timer: 2000,
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                }
            }).then((result) => {
                if (result.dismiss === Swal.DismissReason.timer) {
                    // In a real implementation, this would generate an actual PDF
                    // For demo purposes, we'll show a success message
                    Swal.fire({
                        icon: 'success',
                        title: 'PDF Generated',
                        text: 'Your report has been generated successfully!',
                        confirmButtonText: 'Download PDF'
                    }).then(() => {
                        // Simulate download
                        const link = document.createElement('a');
                        link.href = '#'; // In real implementation, this would be the PDF URL
                        link.download = 'StarRoofing_Report_' + new Date().toISOString().split('T')[0] + '.pdf';
                        link.click();
                    });
                }
            });
        });
        
        // Print Report
        document.getElementById('printReport').addEventListener('click', function() {
            Swal.fire({
                title: 'Print Report',
                text: 'Prepare your printer. The report will be sent to print.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Print',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.print();
                }
            });
        });
        
        // Export Data
        document.getElementById('exportData').addEventListener('click', function() {
            Swal.fire({
                title: 'Export Data',
                text: 'Select the format for exporting your data',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'CSV Format',
                cancelButtonText: 'Excel Format',
                showDenyButton: true,
                denyButtonText: 'JSON Format'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Export Started', 'Your data is being exported to CSV format.', 'success');
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire('Export Started', 'Your data is being exported to Excel format.', 'success');
                } else if (result.isDenied) {
                    Swal.fire('Export Started', 'Your data is being exported to JSON format.', 'success');
                }
            });
        });
        
        // Filter functionality
        const filterSelects = document.querySelectorAll('.filter-select');
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                // In a real implementation, this would filter the reports
                console.log('Filter changed to:', this.value);
            });
        });
        
        // Download buttons for recent reports
        document.querySelectorAll('.btn-primary').forEach(btn => {
            if (btn.textContent.includes('Download')) {
                btn.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const reportName = row.cells[0].textContent;
                    
                    Swal.fire({
                        title: 'Download Report',
                        text: `Are you sure you want to download ${reportName}?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Download',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire('Download Started', 'Your report is being downloaded.', 'success');
                        }
                    });
                });
            }
        });
    });
    
    // Function to export table to PDF
    function exportTableToPDF() {
        Swal.fire({
            title: 'Exporting Table to PDF',
            html: 'Please wait while we prepare your table export...',
            timer: 2000,
            timerProgressBar: true,
            didOpen: () => {
                Swal.showLoading();
            }
        }).then((result) => {
            if (result.dismiss === Swal.DismissReason.timer) {
                // In a real implementation, use jsPDF to create PDF from table
                // For demo, we'll show a success message
                Swal.fire({
                    icon: 'success',
                    title: 'Table Exported',
                    text: 'Your table has been exported to PDF successfully!',
                    confirmButtonText: 'Download PDF'
                }).then(() => {
                    // Simulate download
                    const link = document.createElement('a');
                    link.href = '#'; // In real implementation, this would be the PDF URL
                    link.download = 'Project_Table_' + new Date().toISOString().split('T')[0] + '.pdf';
                    link.click();
                });
            }
        });
    }
    </script>

</body>
</html>