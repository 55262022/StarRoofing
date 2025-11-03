<link rel="stylesheet" href="../css/admin_sidebar.css">

<aside class="sidebar">
    <div class="sidebar-header">
        <img src="../assets/images/admin_logo.jpg" alt="Star Roofing Logo" class="sidebar-logo">
        <div class="sidebar-title">Admin Dashboard</div>
    </div>

    <ul class="sidebar-menu">
        <li><a href="#" onclick="showSection('dashboard')"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
        <li><a href="#" onclick="showSection('inventory')"><i class="fas fa-boxes"></i> <span>Inventory</span></a></li>
        <li><a href="#" onclick="showSection('order')"><i class="fa-solid fa-bag-shopping"></i> <span>Order</span></a></li>
        <li><a href="#" onclick="showSection('employees')"><i class="fa-solid fa-user-tie"></i> <span>Employees</span></a></li>
        <li><a href="#" onclick="showSection('clients')"><i class="fas fa-users"></i> <span>Clients</span></a></li>
        <li><a href="#" onclick="showSection('messages')"><i class="fa-solid fa-inbox"></i> <span>Inbox</span></a></li>
        <li><a href="#" onclick="showSection('reports')"><i class="fas fa-chart-bar"></i> <span>Reports</span></a></li>

        <!-- Settings Dropdown -->
        <li class="has-dropdown">
            <a href="#" class="dropdown-toggle"><i class="fas fa-cog"></i> <span>Settings</span></a>
            <ul class="dropdown-menu">
                <li><a href="#" onclick="showSection('archive')"><i class="fas fa-archive"></i> <span>Archive</span></a></li>
            </ul>
        </li>
    </ul>
</aside>

<script>
    function showSection(section) {
        // Hide all section elements
        document.querySelectorAll('.section').forEach(sec => sec.classList.add('hidden'));

        // Hide the dashboard by default
        const dashboardContent = document.querySelector('.dashboard-content');
        if (dashboardContent) dashboardContent.style.display = 'none';

        // Show selected section
        if (section === 'dashboard') {
            if (dashboardContent) dashboardContent.style.display = 'block';
        } 
        else if (section === 'inventory') {
            document.getElementById('inventory-section').classList.remove('hidden');
        }
        else if (section === 'order') {
            document.getElementById('order-section').classList.remove('hidden');
        } 
        else if (section === 'employees') {
            document.getElementById('employees-section').classList.remove('hidden');
        }
        else if (section === 'clients') {
            document.getElementById('clients-section').classList.remove('hidden');
        } 
        else if (section === 'messages') {
            document.getElementById('messages-section').classList.remove('hidden');
        }
        else if (section === 'reports') {
            document.getElementById('reports-section').classList.remove('hidden');
        }

        // Highlight the active sidebar link
        document.querySelectorAll('.sidebar-menu li a').forEach(link => link.classList.remove('active'));
        const clickedLink = document.querySelector(`.sidebar-menu li a[onclick="showSection('${section}')"]`);
        if (clickedLink) clickedLink.classList.add('active');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();

                dropdownToggles.forEach(otherToggle => {
                    if (otherToggle !== toggle) {
                        otherToggle.classList.remove('active');
                        otherToggle.nextElementSibling.classList.remove('show');
                    }
                });

                this.classList.toggle('active');
                const dropdownMenu = this.nextElementSibling;
                dropdownMenu.classList.toggle('show');
            });
        });
        showSection('dashboard');
    });
</script>
