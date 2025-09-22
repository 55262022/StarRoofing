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
    <title>Project Monitoring - Star Roofing & Construction</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/three@0.132.2/build/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.132.2/examples/js/controls/OrbitControls.js"></script>
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
        
        .stat-icon.progress {
            background: rgba(66, 153, 225, 0.1);
            color: #4299e1;
        }
        
        .stat-icon.completed {
            background: rgba(246, 173, 85, 0.1);
            color: #f6ad55;
        }
        
        .stat-icon.issues {
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
        
        /* Project Controls */
        .project-controls {
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
        
        /* 3D Model Container */
        .model-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
        }
        
        .model-viewer {
            flex: 1;
            min-width: 300px;
            height: 400px;
            background: #f8fafc;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            border: 1px solid #e2e8f0;
        }
        
        .model-controls {
            position: absolute;
            bottom: 10px;
            left: 10px;
            display: flex;
            gap: 5px;
            z-index: 10;
        }
        
        .model-controls button {
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: 4px;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .model-info {
            flex: 1;
            min-width: 300px;
        }
        
        /* Progress Bars */
        .progress-item {
            margin-bottom: 1.5rem;
        }
        
        .progress-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .progress-label {
            font-weight: 500;
            color: #4a5568;
        }
        
        .progress-percent {
            font-weight: 600;
            color: #1a365d;
        }
        
        .progress-bar {
            height: 10px;
            background: #e2e8f0;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            border-radius: 5px;
            transition: width 0.5s ease;
        }
        
        .progress-fill.foundation {
            background: #4299e1;
            width: 85%;
        }
        
        .progress-fill.structure {
            background: #48bb78;
            width: 75%;
        }
        
        .progress-fill.roofing {
            background: #ed8936;
            width: 60%;
        }
        
        .progress-fill.finishing {
            background: #9f7aea;
            width: 30%;
        }
        
        /* Image Gallery */
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .gallery-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            height: 150px;
            cursor: pointer;
        }
        
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .gallery-item:hover img {
            transform: scale(1.05);
        }
        
        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 0.5rem;
            font-size: 0.8rem;
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
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 8px;
            max-width: 800px;
            width: 90%;
            max-height: 90vh;
            overflow: auto;
            position: relative;
        }
        
        .modal-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1a365d;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #718096;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-image {
            width: 100%;
            max-height: 500px;
            object-fit: contain;
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
            
            .project-controls {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filter-controls {
                width: 100%;
                flex-wrap: wrap;
            }
            
            .model-container {
                flex-direction: column;
            }
            
            .model-viewer, .model-info {
                min-width: 100%;
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
                <h1 class="page-title">Project Monitoring - Valmonte Residence</h1>
                
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon projects">
                            <i class="fas fa-hard-hat"></i>
                        </div>
                        <div class="stat-info">
                            <h3>68%</h3>
                            <p>Overall Progress</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon progress">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="stat-info">
                            <h3>24</h3>
                            <p>Tasks Completed</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon completed">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3>120</h3>
                            <p>Days Elapsed</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon issues">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-info">
                            <h3>3</h3>
                            <p>Active Issues</p>
                        </div>
                    </div>
                </div>
                
                <!-- Project Controls -->
                <div class="project-controls">
                    <div class="filter-controls">
                        <select class="filter-select">
                            <option>Valmonte Residence</option>
                            <option>Garcia Roofing Project</option>
                            <option>San Juan Complex</option>
                            <option>Santos Steel Installation</option>
                        </select>
                        <select class="filter-select">
                            <option>All Progress</option>
                            <option>Foundation Stage</option>
                            <option>Structural Stage</option>
                            <option>Roofing Stage</option>
                            <option>Finishing Stage</option>
                        </select>
                    </div>
                    <button class="btn btn-primary">
                        <i class="fas fa-plus"></i> Upload Progress Image
                    </button>
                </div>
                
                <!-- 3D Model Visualization -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">3D Model Progress Visualization</h2>
                        <a href="#" class="card-action">View Full Model</a>
                    </div>
                    <div class="card-body">
                        <div class="model-container">
                            <div class="model-viewer" id="modelViewer">
                                <!-- 3D model will be rendered here -->
                                <div class="model-controls">
                                    <button id="rotateLeft"><i class="fas fa-undo"></i></button>
                                    <button id="rotateRight"><i class="fas fa-redo"></i></button>
                                    <button id="zoomIn"><i class="fas fa-search-plus"></i></button>
                                    <button id="zoomOut"><i class="fas fa-search-minus"></i></button>
                                    <button id="resetView"><i class="fas fa-sync"></i></button>
                                </div>
                            </div>
                            <div class="model-info">
                                <h3>Construction Progress Breakdown</h3>
                                <div class="progress-item">
                                    <div class="progress-header">
                                        <span class="progress-label">Foundation & Excavation</span>
                                        <span class="progress-percent">85%</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill foundation"></div>
                                    </div>
                                </div>
                                <div class="progress-item">
                                    <div class="progress-header">
                                        <span class="progress-label">Structural Framework</span>
                                        <span class="progress-percent">75%</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill structure"></div>
                                    </div>
                                </div>
                                <div class="progress-item">
                                    <div class="progress-header">
                                        <span class="progress-label">Roofing System</span>
                                        <span class="progress-percent">60%</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill roofing"></div>
                                    </div>
                                </div>
                                <div class="progress-item">
                                    <div class="progress-header">
                                        <span class="progress-label">Finishing Works</span>
                                        <span class="progress-percent">30%</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill finishing"></div>
                                    </div>
                                </div>
                                
                                <div style="margin-top: 1.5rem;">
                                    <h4>Latest Update</h4>
                                    <p><strong>Date:</strong> March 15, 2025</p>
                                    <p><strong>Activity:</strong> Concrete pouring for second floor completed. Steel roof framing in progress.</p>
                                    <p><strong>Next Milestone:</strong> Roof installation (April 1, 2025)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Progress Images -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Progress Photo Gallery</h2>
                        <a href="#" class="card-action">View All Images</a>
                    </div>
                    <div class="card-body">
                        <div class="image-gallery">
                            <div class="gallery-item" data-image="foundation.jpg">
                                <img src="https://images.unsplash.com/photo-1581091226033-d5c48150dbaa?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Foundation Work">
                                <div class="gallery-overlay">Foundation (Jan 2025)</div>
                            </div>
                            <div class="gallery-item" data-image="structure.jpg">
                                <img src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Structural Work">
                                <div class="gallery-overlay">Structural (Feb 2025)</div>
                            </div>
                            <div class="gallery-item" data-image="roof-framing.jpg">
                                <img src="https://images.unsplash.com/photo-1586023492125-27a2dfa0e5e3?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Roof Framing">
                                <div class="gallery-overlay">Roof Framing (Mar 2025)</div>
                            </div>
                            <div class="gallery-item" data-image="electrical.jpg">
                                <img src="https://images.unsplash.com/photo-1594223274512-ad4803739b7c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Electrical Work">
                                <div class="gallery-overlay">Electrical (Mar 2025)</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Progress Timeline -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Construction Timeline</h2>
                        <a href="#" class="card-action">Export Report</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Phase</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Progress</th>
                                        <th>Status</th>
                                        <th>Responsible Team</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Site Preparation</td>
                                        <td>Jan 3, 2025</td>
                                        <td>Jan 20, 2025</td>
                                        <td>100%</td>
                                        <td><span class="status completed">Completed</span></td>
                                        <td>Site Prep Team</td>
                                    </tr>
                                    <tr>
                                        <td>Foundation Work</td>
                                        <td>Jan 21, 2025</td>
                                        <td>Feb 15, 2025</td>
                                        <td>100%</td>
                                        <td><span class="status completed">Completed</span></td>
                                        <td>Foundation Team</td>
                                    </tr>
                                    <tr>
                                        <td>Structural Framework</td>
                                        <td>Feb 16, 2025</td>
                                        <td>Mar 30, 2025</td>
                                        <td>85%</td>
                                        <td><span class="status progress">In Progress</span></td>
                                        <td>Structural Team</td>
                                    </tr>
                                    <tr>
                                        <td>Roofing System</td>
                                        <td>Mar 25, 2025</td>
                                        <td>Apr 30, 2025</td>
                                        <td>60%</td>
                                        <td><span class="status progress">In Progress</span></td>
                                        <td>Roofing Team</td>
                                    </tr>
                                    <tr>
                                        <td>Exterior Finishing</td>
                                        <td>Apr 15, 2025</td>
                                        <td>May 30, 2025</td>
                                        <td>0%</td>
                                        <td><span class="status pending">Pending</span></td>
                                        <td>Finishing Team</td>
                                    </tr>
                                    <tr>
                                        <td>Interior Finishing</td>
                                        <td>May 15, 2025</td>
                                        <td>Jun 30, 2025</td>
                                        <td>0%</td>
                                        <td><span class="status pending">Pending</span></td>
                                        <td>Interior Team</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal" id="imageModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Progress Image</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <img class="modal-image" id="modalImage" src="" alt="Progress Image">
                <div id="imageDetails" style="margin-top: 1rem;">
                    <p><strong>Date Taken:</strong> <span id="imageDate">March 15, 2025</span></p>
                    <p><strong>Description:</strong> <span id="imageDesc">Concrete pouring for second floor completed</span></p>
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
    
    // Simple 3D Model Visualization
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize a simple 3D scene (simplified version)
        const container = document.getElementById('modelViewer');
        
        // Create a placeholder for the 3D model
        const modelPlaceholder = document.createElement('div');
        modelPlaceholder.style.width = '100%';
        modelPlaceholder.style.height = '100%';
        modelPlaceholder.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        modelPlaceholder.style.display = 'flex';
        modelPlaceholder.style.alignItems = 'center';
        modelPlaceholder.style.justifyContent = 'center';
        modelPlaceholder.style.color = 'white';
        modelPlaceholder.style.fontSize = '1.2rem';
        modelPlaceholder.innerHTML = '<div style="text-align: center;"><i class="fas fa-cube" style="font-size: 3rem; margin-bottom: 1rem;"></i><br>3D Model Visualization<br><small>Interactive building model showing progress</small></div>';
        
        container.appendChild(modelPlaceholder);
        
        // Model controls functionality
        document.getElementById('rotateLeft').addEventListener('click', function() {
            Swal.fire('Model Control', 'Rotating model left', 'info');
        });
        
        document.getElementById('rotateRight').addEventListener('click', function() {
            Swal.fire('Model Control', 'Rotating model right', 'info');
        });
        
        document.getElementById('zoomIn').addEventListener('click', function() {
            Swal.fire('Model Control', 'Zooming in', 'info');
        });
        
        document.getElementById('zoomOut').addEventListener('click', function() {
            Swal.fire('Model Control', 'Zooming out', 'info');
        });
        
        document.getElementById('resetView').addEventListener('click', function() {
            Swal.fire('Model Control', 'View reset', 'info');
        });
        
        // Image gallery modal functionality
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        const imageDate = document.getElementById('imageDate');
        const imageDesc = document.getElementById('imageDesc');
        const closeModal = document.querySelector('.close-modal');
        
        document.querySelectorAll('.gallery-item').forEach(item => {
            item.addEventListener('click', function() {
                const imgSrc = this.querySelector('img').src;
                const caption = this.querySelector('.gallery-overlay').textContent;
                
                modalImage.src = imgSrc;
                imageDate.textContent = caption.match(/\(([^)]+)\)/)[1];
                imageDesc.textContent = 'Progress image showing ' + caption.split('(')[0].trim();
                
                modal.style.display = 'flex';
            });
        });
        
        closeModal.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
        
        // Filter functionality
        const filterSelects = document.querySelectorAll('.filter-select');
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                // In a real implementation, this would filter the content
                console.log('Filter changed to:', this.value);
            });
        });
        
        // Upload button functionality
        document.querySelector('.btn-primary').addEventListener('click', function() {
            Swal.fire({
                title: 'Upload Progress Image',
                html: `
                    <input type="file" class="swal2-file" accept="image/*">
                    <input type="text" class="swal2-input" placeholder="Image Description">
                    <input type="date" class="swal2-input" value="${new Date().toISOString().split('T')[0]}">
                `,
                showCancelButton: true,
                confirmButtonText: 'Upload',
                preConfirm: () => {
                    // Handle file upload in real implementation
                    return true;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Success', 'Image uploaded successfully!', 'success');
                }
            });
        });
    });
    </script>

</body>
</html>