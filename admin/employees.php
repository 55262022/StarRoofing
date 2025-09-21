<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['account_id'])) {
    header("Location: public/login.php");
    exit();
}

require_once '../database/starroofing_db.php'; 

// Fetch employees from database
$employees = [];
$sql = "SELECT * FROM employees ORDER BY created_at DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management - Star Roofing & Construction</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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

        .dashboard-container {
            display: flex;
            min-height: 100vh;
            font-family: 'Montserrat', sans-serif;
            background-color: #f5f7f9;
        }
        
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .top-navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .breadcrumb {
            font-size: 14px;
            color: #7f8c8d;
        }
        
        .breadcrumb a {
            color: #3498db;
            text-decoration: none;
        }
        
        .user-profile {
            position: relative;
            cursor: pointer;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .user-name {
            font-weight: 500;
        }
        
        .user-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            width: 200px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            z-index: 100;
            margin-top: 10px;
        }
        
        .user-dropdown.active {
            display: block;
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #2c3e50;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .dropdown-item:hover {
            background-color: #f8f9fa;
        }
        
        .dropdown-item i {
            margin-right: 10px;
            width: 16px;
            text-align: center;
        }
        
        .dropdown-divider {
            height: 1px;
            background-color: #eee;
            margin: 5px 0;
        }
        
        .employee-content {
            flex: 1;
            padding: 30px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 5px 0;
        }
        
        .page-description {
            color: #7f8c8d;
            margin: 0;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid #bdc3c7;
            color: #7f8c8d;
        }
        
        .btn-outline:hover {
            background-color: #f8f9fa;
        }
        
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        .btn-success {
            background-color: #2ecc71;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #27ae60;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        .btn-icon {
            padding: 8px;
            width: 36px;
            height: 36px;
        }
        
        .table-container {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            overflow-x: auto;
        }
        
        .employees-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .employees-table th {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 1px solid #eee;
        }
        
        .employees-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .employees-table tr:last-child td {
            border-bottom: none;
        }
        
        .employees-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .employee-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .avatar-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }
        
        .employee-name {
            font-weight: 500;
            color: #2c3e50;
        }
        
        .employee-position {
            color: #6c757d;
            font-size: 13px;
        }
        
        .status {
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        
        .status.active {
            background-color: #e8f6f3;
            color: #1abc9c;
        }
        
        .status.inactive {
            background-color: #fdedec;
            color: #e74c3c;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }
        
        .no-employees {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background-color: white;
            border-bottom: 1px solid #eee;
        }
        
        .filter-controls {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .filter-control {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background-color: white;
            font-size: 14px;
        }
        
        .search-box {
            display: flex;
            align-items: center;
            background: white;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 0 10px;
        }
        
        .search-box input {
            border: none;
            padding: 8px;
            outline: none;
            width: 200px;
        }
        
        .search-box i {
            color: #6c757d;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background-color: white;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            background-color: white;
            z-index: 10;
        }
        
        .modal-title {
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #7f8c8d;
            transition: color 0.3s;
        }
        
        .modal-close:hover {
            color: #34495e;
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #34495e;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            transition: border-color 0.3s;
            font-family: 'Montserrat', sans-serif;
        }
        
        input:focus, select:focus, textarea:focus {
            border-color: #3498db;
            outline: none;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            position: sticky;
            bottom: 0;
            background-color: white;
            z-index: 10;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .filter-controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box input {
                width: 100%;
            }
            
            .table-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .action-buttons {
                justify-content: flex-start;
            }
        }
        
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        
        .loading-overlay.active {
            display: flex;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
            
            <!-- Employee Content -->
            <div class="employee-content">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Employee Management</h1>
                        <p class="page-description">Manage your employees and staff</p>
                    </div>
                    <button class="btn btn-primary" id="addEmployeeBtn">
                        <i class="fas fa-user-plus"></i> Add New Employee
                    </button>
                </div>
                
                <div class="table-container">
                    <div class="table-header">
                        <div class="filter-controls">
                            <select id="statusFilter" class="filter-control">
                                <option value="all">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            
                            <select id="departmentFilter" class="filter-control">
                                <option value="all">All Departments</option>
                                <option value="Construction">Construction</option>
                                <option value="Roofing">Roofing</option>
                                <option value="Administration">Administration</option>
                                <option value="Sales">Sales</option>
                                <option value="Management">Management</option>
                            </select>
                            
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" id="searchInput" placeholder="Search employees...">
                            </div>
                        </div>
                    </div>
                    
                    <table class="employees-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Contact</th>
                                <th>Department</th>
                                <th>Position</th>
                                <th>Hire Date</th>
                                <th>Salary</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="employeeTableBody">
                            <?php if (count($employees) > 0): ?>
                                <?php foreach ($employees as $employee): ?>
                                    <tr data-status="<?= $employee['status'] ?>" data-department="<?= $employee['department'] ?>" data-name="<?= strtolower($employee['first_name'] . ' ' . $employee['last_name']) ?>">
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 12px;">
                                                <?php if (!empty($employee['image_path'])): ?>
                                                    <img src="../<?= htmlspecialchars($employee['image_path']) ?>" alt="<?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?>" class="employee-avatar">
                                                <?php else: ?>
                                                    <div class="avatar-placeholder">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <div class="employee-name"><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div><?= htmlspecialchars($employee['email']) ?></div>
                                            <div style="color: #6c757d; font-size: 13px;"><?= htmlspecialchars($employee['phone'] ?? 'N/A') ?></div>
                                        </td>
                                        <td><?= htmlspecialchars($employee['department']) ?></td>
                                        <td><?= htmlspecialchars($employee['position']) ?></td>
                                        <td><?= date('M j, Y', strtotime($employee['hire_date'])) ?></td>
                                        <td>₱<?= number_format($employee['salary'], 2) ?></td>
                                        <td>
                                            <span class="status <?= $employee['status'] ?>">
                                                <?= ucfirst($employee['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-outline btn-sm edit-btn" data-id="<?= $employee['employee_id'] ?>" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($employee['status'] === 'active'): ?>
                                                    <button class="btn btn-danger btn-sm deactivate-btn" data-id="<?= $employee['employee_id'] ?>" data-name="<?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?>" title="Deactivate">
                                                        <i class="fas fa-user-times"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-success btn-sm activate-btn" data-id="<?= $employee['employee_id'] ?>" data-name="<?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?>" title="Activate">
                                                        <i class="fas fa-user-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="no-employees">
                                        <p>No employees found. Add your first employee to get started.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <!-- Add/Edit Employee Modal -->
    <div class="modal" id="employeeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Add New Employee</h2>
                <button class="modal-close" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="employeeForm" method="POST" action="../crud/save_employee.php" enctype="multipart/form-data">
                    <input type="hidden" id="employeeId" name="employee_id">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName">First Name *</label>
                            <input type="text" id="firstName" name="first_name" placeholder="Enter first name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="lastName">Last Name *</label>
                            <input type="text" id="lastName" name="last_name" placeholder="Enter last name" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" placeholder="Enter email address" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" placeholder="Enter phone number">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="position">Position *</label>
                            <input type="text" id="position" name="position" placeholder="Enter position" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="department">Department *</label>
                            <select id="department" name="department" required>
                                <option value="">Select Department</option>
                                <option value="Construction">Construction</option>
                                <option value="Roofing">Roofing</option>
                                <option value="Administration">Administration</option>
                                <option value="Sales">Sales</option>
                                <option value="Management">Management</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="hireDate">Hire Date *</label>
                            <input type="date" id="hireDate" name="hire_date" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="salary">Salary (₱) *</label>
                            <input type="number" id="salary" name="salary" placeholder="0.00" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="employeeImage">Upload Employee Photo</label>
                        <input type="file" id="employeeImage" name="image_file" accept="image/*">
                    
                        <!-- Preview Box -->
                        <div style="margin-top:10px;">
                            <img id="previewImage" src="#" alt="Image Preview" style="display:none; max-width:150px; border:1px solid #ccc; padding:5px;">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelBtn">Cancel</button>
                <button type="submit" form="employeeForm" class="btn btn-primary" id="saveEmployeeBtn">Save Employee</button>
            </div>
        </div>
    </div>

    <!-- Status Change Confirmation Modal -->
    <div class="modal" id="statusModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="statusModalTitle">Confirm Status Change</h2>
                <button class="modal-close" id="closeStatusModal">&times;</button>
            </div>
            <div class="modal-body">
                <p id="statusModalMessage"></p>
                <form id="statusForm" method="POST" action="../crud/update_employee_status.php">
                    <input type="hidden" name="employee_id" id="statusEmployeeId">
                    <input type="hidden" name="status" id="statusValue">
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelStatusBtn">Cancel</button>
                <button type="submit" form="statusForm" class="btn btn-primary" id="confirmStatusBtn">Confirm</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add employee button
            document.getElementById('addEmployeeBtn').addEventListener('click', () => {
                document.getElementById('modalTitle').textContent = 'Add New Employee';
                document.getElementById('employeeForm').reset();
                document.getElementById('employeeId').value = '';
                document.getElementById('hireDate').valueAsDate = new Date();
                document.getElementById('status').value = 'active';
                
                // Clear preview image
                document.getElementById('previewImage').style.display = 'none';
                
                document.getElementById('employeeModal').classList.add('active');
            });
            
            // Modal close buttons
            document.getElementById('closeModal').addEventListener('click', closeModal);
            document.getElementById('cancelBtn').addEventListener('click', closeModal);
            
            // Status modal close buttons
            document.getElementById('closeStatusModal').addEventListener('click', closeStatusModal);
            document.getElementById('cancelStatusBtn').addEventListener('click', closeStatusModal);
            
            // Edit button functionality
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('edit-btn') || e.target.closest('.edit-btn')) {
                    const btn = e.target.classList.contains('edit-btn') ? e.target : e.target.closest('.edit-btn');
                    const employeeId = btn.dataset.id;
                    
                    // Show loading overlay
                    document.getElementById('loadingOverlay').classList.add('active');

                    // Fetch employee details via AJAX
                    fetch(`../crud/get_employee.php?id=${employeeId}`)
                        .then(response => response.json())
                        .then(data => {
                            // Hide loading overlay
                            document.getElementById('loadingOverlay').classList.remove('active');
                            
                            if (data.success) {
                                // Update modal title
                                document.getElementById('modalTitle').textContent = 'Edit Employee';

                                // Fill form with employee data
                                document.getElementById('employeeId').value = data.employee.employee_id;
                                document.getElementById('firstName').value = data.employee.first_name;
                                document.getElementById('lastName').value = data.employee.last_name;
                                document.getElementById('email').value = data.employee.email;
                                document.getElementById('phone').value = data.employee.phone || '';
                                document.getElementById('position').value = data.employee.position;
                                document.getElementById('department').value = data.employee.department;
                                document.getElementById('hireDate').value = data.employee.hire_date;
                                document.getElementById('salary').value = data.employee.salary;
                                document.getElementById('status').value = data.employee.status;

                                // If employee already has an image, show it
                                const preview = document.getElementById('previewImage');
                                if (data.employee.image_path) {
                                    preview.src = '../' + data.employee.image_path;
                                    preview.style.display = "block";
                                } else {
                                    preview.style.display = "none";
                                }

                                // Show modal
                                document.getElementById('employeeModal').classList.add('active');
                            } else {
                                // SweetAlert for error
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.message || 'Failed to fetch employee data.',
                                    confirmButtonColor: '#e74c3c'
                                });
                            }
                        })
                        .catch(error => {
                            // Hide loading overlay
                            document.getElementById('loadingOverlay').classList.remove('active');
                            
                            console.error('Error:', error);
                            // SweetAlert for fetch error
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred while fetching employee data.',
                                confirmButtonColor: '#e74c3c'
                            });
                        });
                }

                // Status change buttons
                if (e.target.classList.contains('deactivate-btn') || e.target.closest('.deactivate-btn')) {
                    const btn = e.target.classList.contains('deactivate-btn') ? e.target : e.target.closest('.deactivate-btn');
                    document.getElementById('statusModalTitle').textContent = 'Confirm Deactivation';
                    document.getElementById('statusModalMessage').textContent = `Are you sure you want to deactivate ${btn.dataset.name}?`;
                    document.getElementById('statusEmployeeId').value = btn.dataset.id;
                    document.getElementById('statusValue').value = 'inactive';
                    document.getElementById('statusModal').classList.add('active');
                }

                if (e.target.classList.contains('activate-btn') || e.target.closest('.activate-btn')) {
                    const btn = e.target.classList.contains('activate-btn') ? e.target : e.target.closest('.activate-btn');
                    document.getElementById('statusModalTitle').textContent = 'Confirm Activation';
                    document.getElementById('statusModalMessage').textContent = `Are you sure you want to activate ${btn.dataset.name}?`;
                    document.getElementById('statusEmployeeId').value = btn.dataset.id;
                    document.getElementById('statusValue').value = 'active';
                    document.getElementById('statusModal').classList.add('active');
                }
            });

            // Filter functionality
            const statusFilter = document.getElementById('statusFilter');
            const departmentFilter = document.getElementById('departmentFilter');
            const searchInput = document.getElementById('searchInput');
            const employeeRows = document.querySelectorAll('#employeeTableBody tr');

            function filterEmployees() {
                const statusValue = statusFilter.value;
                const departmentValue = departmentFilter.value;
                const searchValue = searchInput.value.toLowerCase();

                employeeRows.forEach(row => {
                    const status = row.dataset.status;
                    const department = row.dataset.department;
                    const name = row.dataset.name;

                    const statusMatch = statusValue === 'all' || status === statusValue;
                    const departmentMatch = departmentValue === 'all' || department === departmentValue;
                    const searchMatch = name.includes(searchValue);

                    if (statusMatch && departmentMatch && searchMatch) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            statusFilter.addEventListener('change', filterEmployees);
            departmentFilter.addEventListener('change', filterEmployees);
            searchInput.addEventListener('input', filterEmployees);

            // Image preview functionality
            document.getElementById("employeeImage").addEventListener("change", function(event) {
                const file = event.target.files[0];
                const preview = document.getElementById("previewImage");

                preview.style.display = "none";

                if (file) {
                    // Allowed types
                    const allowedTypes = ["image/jpeg", "image/png", "image/gif", "image/webp"];
                    if (!allowedTypes.includes(file.type)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid File Type',
                            text: 'Only JPG, PNG, GIF, and WebP files are allowed.',
                            confirmButtonColor: '#e74c3c'
                        });
                        event.target.value = "";
                        return;
                    }

                    // Max size 5MB
                    if (file.size > 5 * 1024 * 1024) {
                        Swal.fire({
                            icon: 'error',
                            title: 'File Too Large',
                            text: 'Maximum file size is 5MB.',
                            confirmButtonColor: '#e74c3c'
                        });
                        event.target.value = "";
                        return;
                    }

                    // Preview image
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = "block";
                    };
                    reader.readAsDataURL(file);
                }
            });

            function closeModal() {
                document.getElementById('employeeModal').classList.remove('active');
            }

            function closeStatusModal() {
                document.getElementById('statusModal').classList.remove('active');
            }
        });

        // SweetAlert for form submission
        document.getElementById("employeeForm").addEventListener("submit", function(e) {
            e.preventDefault();
            
            // Basic form validation
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            let firstInvalidField = null;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    if (!firstInvalidField) {
                        firstInvalidField = field;
                        field.focus();
                    }
                }
            });
            
            if (!isValid) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Information',
                    text: 'Please fill in all required fields.',
                    confirmButtonColor: '#3498db'
                });
                return;
            }
            
            // Validate email format
            const emailField = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailField.value)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Email',
                    text: 'Please enter a valid email address.',
                    confirmButtonColor: '#3498db'
                });
                emailField.focus();
                return;
            }
            
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to save this employee?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3498db',
                cancelButtonColor: '#e74c3c',
                confirmButtonText: 'Yes, save it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading overlay during submission
                    document.getElementById('loadingOverlay').classList.add('active');
                    this.submit();
                }
            });
        });

        // SweetAlert for status change form submission
        document.getElementById("statusForm").addEventListener("submit", function(e) {
            e.preventDefault();
            
            const status = document.getElementById('statusValue').value;
            const action = status === 'active' ? 'activate' : 'deactivate';
            
            Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to ${action} this employee?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3498db',
                cancelButtonColor: '#e74c3c',
                confirmButtonText: `Yes, ${action} it!`,
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading overlay during submission
                    document.getElementById('loadingOverlay').classList.add('active');
                    this.submit();
                }
            });
        });
    </script>

    <?php if (isset($_GET['success'])): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '<?= htmlspecialchars($_GET['success']) ?>',
                    confirmButtonColor: '#3498db'
                }).then(() => {
                    // Clean URL without query parameters
                    window.history.replaceState({}, document.title, window.location.pathname);
                });
            });
        </script>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '<?= htmlspecialchars($_GET['error']) ?>',
                    confirmButtonColor: '#e74c3c'
                }).then(() => {
                    // Clean URL without query parameters
                    window.history.replaceState({}, document.title, window.location.pathname);
                });
            });
        </script>
    <?php endif; ?>
</body>
</html>