<link rel="stylesheet" href="../css/admin_sidebar.css">

<aside class="sidebar">
    <div class="sidebar-header">
        <img src="https://via.placeholder.com/150x50/ffffff/1a365d?text=Star+Roofing" alt="Logo" class="sidebar-logo">
        <div class="sidebar-title">Admin Dashboard</div>
    </div>
    
    <ul class="sidebar-menu">
        <li><a href="dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
        <li><a href="inventory.php"><i class="fas fa-boxes"></i> <span>Inventory</span></a></li>
        <li><a href="employees.php"><i class="fas fa-users"></i> <span>Employees</span></a></li>
        <li><a href="clients.php"><i class="fas fa-users"></i> <span>Clients</span></a></li>
        <li><a href="messages.php"><i class="fas fa-file-invoice-dollar"></i> <span>Messages</span></a></li>
        <li><a href="project.php"><i class="fas fa-hard-hat"></i> <span>Projects</span></a></li>
        <li><a href="3D_model.php"><i class="fas fa-tools"></i> <span>3D Estimate</span></a></li>
        <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> <span>Schedule</span></a></li>
        <li><a href="reports.php"><i class="fas fa-chart-bar"></i> <span>Reports</span></a></li>
        
        <!-- Settings with Dropdown -->
        <li class="has-dropdown">
            <a href="#" class="dropdown-toggle"><i class="fas fa-cog"></i> <span>Settings</span></a>
            <ul class="dropdown-menu">
                <li><a href="archive.php"><i class="fas fa-archive"></i> <span>Archive</span></a></li>
            </ul>
        </li>
    </ul>
</aside>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
            
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Close other open dropdowns
                    dropdownToggles.forEach(otherToggle => {
                        if (otherToggle !== toggle) {
                            otherToggle.classList.remove('active');
                            otherToggle.nextElementSibling.classList.remove('show');
                        }
                    });
                    
                    // Toggle current dropdown
                    this.classList.toggle('active');
                    const dropdownMenu = this.nextElementSibling;
                    dropdownMenu.classList.toggle('show');
                });
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.sidebar-menu')) {
                    dropdownToggles.forEach(toggle => {
                        toggle.classList.remove('active');
                        toggle.nextElementSibling.classList.remove('show');
                    });
                }
            });
        });
    </script>