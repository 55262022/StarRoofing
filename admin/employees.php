<?php
include '../authentication/auth.php';
require_once '../database/starroofing_db.php';

// --- Pagination setup ---
$limit = 10; // number of employees per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// --- Filters ---
$department_filter = isset($_GET['department']) ? trim($_GET['department']) : '';
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// --- Base Query with JOIN to get email from accounts ---
$sql = "SELECT e.*, a.email 
        FROM employees e 
        LEFT JOIN accounts a ON e.account_id = a.id 
        WHERE e.is_archived = 0";

// --- Apply Department Filter ---
if (!empty($department_filter)) {
    $sql .= " AND e.department = '" . $conn->real_escape_string($department_filter) . "'";
}

// --- Apply Search Filter (by name, email, phone) ---
if (!empty($search_term)) {
    $search_safe = $conn->real_escape_string($search_term);
    $sql .= " AND (e.first_name LIKE '%$search_safe%' 
                OR e.last_name LIKE '%$search_safe%' 
                OR a.email LIKE '%$search_safe%' 
                OR e.phone LIKE '%$search_safe%')";
}

// --- Count total for pagination ---
$count_sql = str_replace("SELECT e.*, a.email", "SELECT COUNT(*) AS total", $sql);
$count_result = $conn->query($count_sql);
$total_records = ($count_result && $count_result->num_rows > 0) 
    ? $count_result->fetch_assoc()['total'] 
    : 0;

$total_pages = ceil($total_records / $limit);

// --- Apply LIMIT for pagination ---
$sql .= " ORDER BY e.created_at DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

$employees = [];
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../css/admin_main.css">
    <style>  
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

        .btn-warning {
            background-color: #dce73cff;
            color: black;
        }
        
        .btn-warning:hover {
            background-color: #b9c331ff;
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

        .search-form {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .search-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .search-input:focus {
            border-color: #007bff;
        }

        .search-btn {
            padding: 10px 18px;
            background-color: #3498db;
            border: none;
            border-radius: 6px;
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background-color 0.2s ease;
        }

        .search-btn:hover {
            background-color: #2980b9;
        }
        
        .department-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 30px;
        }
        
        .department-btn {
            padding: 8px 16px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .department-btn.active,
        .department-btn:hover {
            background-color: #3498db;
            color: white;
            border-color: #3498db;
        }
        
        .employee-table table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .employee-table th, .employee-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .employee-table th {
            background-color: #3498db;
            color: white;
            font-weight: 600;
        }

        .employee-table tr:hover {
            background-color: #f9f9f9;
        }

        .employee-table img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
        }

        .pagination {
            margin-top: 20px;
            text-align: center;
        }

        .page-btn {
            display: inline-block;
            margin: 0 5px;
            padding: 8px 14px;
            border-radius: 6px;
            border: 1px solid #3498db;
            color: #3498db;
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }

        .page-btn:hover {
            background-color: #3498db;
            color: white;
        }

        .page-btn.active {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }
        
        .avatar-placeholder {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }
        
        .employee-name {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
        }

        .detail-label {
            font-size: 12px;
            color: #7f8c8d;
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
            max-width: 700px;
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
        
        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
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
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #e8f6f3;
            color: #1abc9c;
            border: 1px solid #1abc9c;
        }
        
        .alert-error {
            background-color: #fdedec;
            color: #e74c3c;
            border: 1px solid #e74c3c;
        }
        
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        
        .loading-overlay.active {
            display: flex;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
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
    <div class="main-container">
        <div class="main-content">
            <div class="employee-content">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Employees Information</h1>
                        <p class="page-description">Manage your employees and staff</p>
                    </div>
                    <button class="btn btn-primary" id="addEmployeeBtn">
                        <i class="fas fa-plus"></i> Add New Employee
                    </button>
                </div>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?= $_SESSION['message_type'] ?>">
                        <?= $_SESSION['message'] ?>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <form method="GET" action="" class="search-form">
                    <input type="hidden" name="department" value="<?= htmlspecialchars($department_filter) ?>">
                    <input type="text" name="search" placeholder="Search employees..." 
                        value="<?= htmlspecialchars($search_term) ?>" class="search-input">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <button type="button" class="search-btn" onclick="window.location='employees.php?department=<?= htmlspecialchars($department_filter) ?>'">
                        <i class="fas fa-times"></i> Reset
                    </button>
                </form>

                <div class="department-filter">
                    <button class="department-btn <?= $department_filter === '' ? 'active' : '' ?>" data-department="">All Departments</button>
                    <button class="department-btn <?= $department_filter === 'Administration' ? 'active' : '' ?>" data-department="Administration">Administration</button>
                    <button class="department-btn <?= $department_filter === 'Sales Manager' ? 'active' : '' ?>" data-department="Sales Manager">Sales Manager</button>
                    <button class="department-btn <?= $department_filter === 'Operational Manager' ? 'active' : '' ?>" data-department="Operational Manager">Operational Manager</button>
                    <button class="department-btn <?= $department_filter === 'Logistics and Services' ? 'active' : '' ?>" data-department="Logistics and Services">Logistics and Services</button>
                </div>
                
                <div class="employee-container">
                    <div class="employee-table">
                        <?php if (count($employees) > 0): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Contact</th>
                                        <th>Hire Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($employees as $employee): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($employee['image_path'])): ?>
                                                    <img src="../<?= htmlspecialchars($employee['image_path']) ?>" alt="<?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?>">
                                                <?php else: ?>
                                                    <div class="avatar-placeholder">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="employee-name"><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></div>
                                            </td>
                                            <td><?= htmlspecialchars($employee['department']) ?></td>
                                            <td>
                                                <div><?= htmlspecialchars($employee['email']) ?></div>
                                                <div class="detail-label"><?= htmlspecialchars($employee['phone'] ?? 'N/A') ?></div>
                                            </td>
                                            <td><?= date('M j, Y', strtotime($employee['hire_date'])) ?></td>
                                            <td>
                                                <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                                    <button class="btn btn-warning edit-btn" data-id="<?= $employee['employee_id'] ?>">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <button class="btn btn-danger archive-btn" 
                                                            data-id="<?= $employee['employee_id'] ?>" 
                                                            data-name="<?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?>">
                                                        <i class="fas fa-archive"></i> Archive
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?= $page-1 ?>&department=<?= urlencode($department_filter) ?>&search=<?= urlencode($search_term) ?>" class="page-btn">Prev</a>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <a href="?page=<?= $i ?>&department=<?= urlencode($department_filter) ?>&search=<?= urlencode($search_term) ?>" 
                                    class="page-btn <?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?= $page+1 ?>&department=<?= urlencode($department_filter) ?>&search=<?= urlencode($search_term) ?>" class="page-btn">Next</a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: 40px; background: white; border-radius: 10px;">
                                <p>No employees found.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <!-- Add Employee Modal -->
    <div class="modal" id="addEmployeeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Add New Employee</h2>
                <button class="modal-close" id="closeAddModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addEmployeeForm" method="POST" action="../crud/add_employee.php" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="addFirstName">First Name *</label>
                            <input type="text" id="addFirstName" name="first_name" placeholder="Enter first name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="addLastName">Last Name *</label>
                            <input type="text" id="addLastName" name="last_name" placeholder="Enter last name" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="addEmail">Email Address *</label>
                            <input type="email" id="addEmail" name="email" placeholder="Enter email address" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="addPhone">Phone Number</label>
                            <input type="tel" id="addPhone" name="phone" placeholder="Enter phone number"  maxlength="11">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="addDepartment">Department *</label>
                            <select id="addDepartment" name="department" required>
                                <option value="">Select Department</option>
                                <option value="Administration">Administration</option>
                                <option value="Sales Manager">Sales Manager</option>
                                <option value="Operational Manager">Operational Manager</option>
                                <option value="Logistics and Services">Logistics and Services</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="addHireDate">Hire Date *</label>
                            <input type="date" id="addHireDate" name="hire_date" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="addEmployeeImage">Upload Employee Photo</label>
                        <input type="file" id="addEmployeeImage" name="image_file" accept="image/*">
                        <div style="margin-top:10px;">
                            <img id="addPreviewImage" src="#" alt="Image Preview" style="display:none; max-width:150px; border:1px solid #ccc; padding:5px;">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelAddBtn">Cancel</button>
                <button type="submit" form="addEmployeeForm" class="btn btn-primary">Save Employee</button>
            </div>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div class="modal" id="editEmployeeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Edit Employee</h2>
                <button class="modal-close" id="closeEditModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editEmployeeForm" method="POST" action="../crud/edit_employee.php" enctype="multipart/form-data">
                    <input type="hidden" id="editEmployeeId" name="employee_id">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editFirstName">First Name *</label>
                            <input type="text" id="editFirstName" name="first_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="editLastName">Last Name *</label>
                            <input type="text" id="editLastName" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editEmail">Email Address *</label>
                            <input type="email" id="editEmail" name="email" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="editPhone">Phone Number</label>
                            <input type="tel" id="editPhone" name="phone" maxlength="11">
                        </div>
                        
                        <div class="form-group">
                            <label for="editDepartment">Department *</label>
                            <select id="editDepartment" name="department" required>
                                <option value="" disabled selected>Select Department</option>
                                <option value="Administration">Administration</option>
                                <option value="Sales Manager">Sales Manager</option>
                                <option value="Operational Manager">Operational Manager</option>
                                <option value="Logistics and Services">Logistics and Services</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="editHireDate">Hire Date *</label>
                        <input type="date" id="editHireDate" name="hire_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editEmployeeImage">Upload Employee Photo</label>
                        <input type="file" id="editEmployeeImage" name="image_file" accept="image/*">
                        <div style="margin-top:10px;">
                            <img id="editPreviewImage" src="#" alt="Image Preview" style="display:none; max-width:150px; border:1px solid #ccc; padding:5px;">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelEditBtn">Cancel</button>
                <button type="submit" form="editEmployeeForm" class="btn btn-primary">Update Employee</button>
            </div>
        </div>
    </div>

    <!-- Archive Confirmation Modal -->
    <div class="modal" id="archiveModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Confirm Archive</h2>
                <button class="modal-close" id="closeArchiveModal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to archive <strong id="archiveEmployeeName"></strong>? You can restore it later.</p>
                <form id="archiveForm" method="POST" action="../crud/archive_employee.php">
                    <input type="hidden" name="employee_id" id="archiveEmployeeId">
                    <input type="hidden" name="archive_employee" value="1">
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelArchiveBtn">Cancel</button>
                <button type="submit" form="archiveForm" class="btn btn-danger">Archive Employee</button>
            </div>
        </div>
    </div>
    <!-- Phone number validation -->
    <script>
        // Add Employee - Allow only numbers and max 11 digits
        document.getElementById('addPhone').addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, ''); // Remove non-numeric
        });

        // Edit Employee - Same logic
        document.getElementById('editPhone').addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.department-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const department = btn.dataset.department;
                    window.location.href = `employees.php?department=${department}`;
                });
            });

            const addEmployeeBtn = document.getElementById("addEmployeeBtn");
            const addEmployeeModal = document.getElementById("addEmployeeModal");
            const closeAddModal = document.getElementById("closeAddModal");
            const cancelAddBtn = document.getElementById("cancelAddBtn");

            if (addEmployeeBtn) {
                addEmployeeBtn.addEventListener("click", () => {
                    document.getElementById("addEmployeeForm").reset();
                    document.getElementById("addHireDate").valueAsDate = new Date();
                    document.getElementById("addPreviewImage").style.display = "none";
                    addEmployeeModal.classList.add("active");
                });
            }

            if (closeAddModal) {
                closeAddModal.addEventListener("click", () => {
                    addEmployeeModal.classList.remove("active");
                });
            }

            if (cancelAddBtn) {
                cancelAddBtn.addEventListener("click", (e) => {
                    e.preventDefault();
                    addEmployeeModal.classList.remove("active");
                });
            }

            const editEmployeeModal = document.getElementById("editEmployeeModal");
            const closeEditModal = document.getElementById("closeEditModal");
            const cancelEditBtn = document.getElementById("cancelEditBtn");

            document.querySelectorAll(".edit-btn").forEach(button => {
                button.addEventListener("click", function () {
                    const employeeId = this.getAttribute("data-id");
                    document.getElementById('loadingOverlay').classList.add('active');

                    fetch(`../crud/get_employee.php?id=${employeeId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('loadingOverlay').classList.remove('active');
                        
                        if (data.success) {
                            document.getElementById("editEmployeeId").value = data.employee.employee_id;
                            document.getElementById("editFirstName").value = data.employee.first_name;
                            document.getElementById("editLastName").value = data.employee.last_name;
                            document.getElementById("editEmail").value = data.employee.email;
                            document.getElementById("editPhone").value = data.employee.phone || '';
                            document.getElementById("editDepartment").value = data.employee.department;
                            document.getElementById("editHireDate").value = data.employee.hire_date;

                            const previewImg = document.getElementById("editPreviewImage");
                            if (data.employee.image_path && data.employee.image_path !== "") {
                                previewImg.src = `../${data.employee.image_path}`;
                                previewImg.style.display = "block";
                            } else {
                                previewImg.style.display = "none";
                            }

                            editEmployeeModal.classList.add("active");
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: "Failed to fetch employee details."
                            });
                        }
                    })
                    .catch(error => {
                        document.getElementById('loadingOverlay').classList.remove('active');
                        console.error("Error:", error);
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "An error occurred while fetching employee data."
                        });
                    });
                });
            });

            if (closeEditModal) {
                closeEditModal.addEventListener("click", () => {
                    editEmployeeModal.classList.remove("active");
                });
            }

            if (cancelEditBtn) {
                cancelEditBtn.addEventListener("click", (e) => {
                    e.preventDefault();
                    editEmployeeModal.classList.remove("active");
                });
            }

            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('archive-btn') || e.target.closest('.archive-btn')) {
                    const btn = e.target.classList.contains('archive-btn') ? e.target : e.target.closest('.archive-btn');
                    document.getElementById('archiveEmployeeName').textContent = btn.dataset.name;
                    document.getElementById('archiveEmployeeId').value = btn.dataset.id;
                    document.getElementById('archiveModal').classList.add('active');
                }
            });

            document.getElementById('closeArchiveModal').addEventListener('click', () => {
                document.getElementById('archiveModal').classList.remove('active');
            });
            document.getElementById('cancelArchiveBtn').addEventListener('click', () => {
                document.getElementById('archiveModal').classList.remove('active');
            });

            const addImageInput = document.getElementById("addEmployeeImage");
            const addPreview = document.getElementById("addPreviewImage");
            if (addImageInput) {
                addImageInput.addEventListener("change", function () {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = e => {
                            addPreview.src = e.target.result;
                            addPreview.style.display = "block";
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            const editImageInput = document.getElementById("editEmployeeImage");
            const editPreview = document.getElementById("editPreviewImage");
            if (editImageInput) {
                editImageInput.addEventListener("change", function () {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = e => {
                            editPreview.src = e.target.result;
                            editPreview.style.display = "block";
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            function validateImage(input, previewId) {
                const file = input.files[0];
                const preview = document.getElementById(previewId);

                if (!file) {
                    preview.style.display = "none";
                    return true;
                }

                const allowedTypes = ["image/jpeg", "image/png", "image/gif"];
                if (!allowedTypes.includes(file.type)) {
                    Swal.fire({
                        icon: "error",
                        title: "Invalid File Type",
                        text: "Please upload an image (JPG, PNG, GIF only)."
                    });
                    input.value = "";
                    preview.style.display = "none";
                    return false;
                }

                const maxSize = 2 * 1024 * 1024; 
                if (file.size > maxSize) {
                    Swal.fire({
                        icon: "error",
                        title: "File Too Large",
                        text: "Image size must be less than 2MB."
                    });
                    input.value = "";
                    preview.style.display = "none";
                    return false;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = "block";
                };
                reader.readAsDataURL(file);

                return true;
            }

            document.getElementById("addEmployeeImage").addEventListener("change", function() {
                validateImage(this, "addPreviewImage");
            });

            document.getElementById("editEmployeeImage").addEventListener("change", function() {
                validateImage(this, "editPreviewImage");
            });

            function validateEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            document.getElementById("addEmployeeForm").addEventListener("submit", function(e) {
                const email = document.getElementById("addEmail").value;
                if (!validateEmail(email)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: "error",
                        title: "Invalid Email",
                        text: "Please enter a valid email address."
                    });
                    return false;
                }
                
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to add this employee?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3498db',
                    cancelButtonColor: '#e74c3c',
                    confirmButtonText: 'Yes, add it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });

            document.getElementById("editEmployeeForm").addEventListener("submit", function(e) {
                const email = document.getElementById("editEmail").value;
                if (!validateEmail(email)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: "error",
                        title: "Invalid Email",
                        text: "Please enter a valid email address."
                    });
                    return false;
                }
                
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to update this employee?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3498db',
                    cancelButtonColor: '#e74c3c',
                    confirmButtonText: 'Yes, update it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        });

        document.getElementById("archiveForm").addEventListener("submit", function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: "This employee will be archived.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#3498db',
                confirmButtonText: 'Yes, archive it!'
            }).then((result) => {
                if (result.isConfirmed) {
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
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    window.history.replaceState({}, document.title, "employees.php");
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
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    window.history.replaceState({}, document.title, "employees.php");
                });
            });
        </script>
    <?php endif; ?>
</body>
</html>