<link rel="stylesheet" href="../css/client_sidebar.css">

<aside class="sidebar">
    <div class="sidebar-header">
        <img src="https://via.placeholder.com/150x50/ffffff/1a365d?text=Star+Roofing" alt="Logo" class="sidebar-logo">
        <div class="sidebar-title">Client Dashboard</div>
    </div>
    
    <ul class="sidebar-menu">
        <li><a href="#" onclick="showSection('dashboard')"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
        <li><a href="#" onclick="showSection('materials')"><i class="fas fa-home"></i> <span>Products</span></a></li>
        <li><a href="#" onclick="showSection('chats')"><i class="fas fa-home"></i> <span>Chats</span></a></li>

        
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
        else if (section === 'materials') {
            document.getElementById('materials-section').classList.remove('hidden');
        } 
        else if (section === 'inquiry') {
            document.getElementById('inquiry-section').classList.remove('hidden');
        }
        else if (section === 'chats') {
            document.getElementById('chats-section').classList.remove('hidden');
        }

        // Highlight the active sidebar link
        document.querySelectorAll('.sidebar-menu li a').forEach(link => link.classList.remove('active'));
        const clickedLink = document.querySelector(`.sidebar-menu li a[onclick="showSection('${section}')"]`);
        if (clickedLink) clickedLink.classList.add('active');
    }

    // Sidebar dropdown logic
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
    });
</script>

