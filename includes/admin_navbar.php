<link rel="stylesheet" href="../css/admin_navbar.css">
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<nav class="top-navbar">
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search...">
    </div>
    
    <div class="user-profile" id="userProfile">
        <div class="user-info">
            <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=80" alt="Admin" class="user-avatar">
            <div class="user-name">
                <?php 
                if (isset($_SESSION['first_name']) && isset($_SESSION['last_name'])) {
                    echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
                } else {
                    echo 'Admin';
                }
                ?>
            </div>
            <i class="fas fa-chevron-down"></i>
        </div>
        
        <div class="user-dropdown" id="userDropdown">
            <a href="profile.php" class="dropdown-item">
                <i class="fas fa-user"></i> My Profile
            </a>
            <!-- <a href="#" class="dropdown-item">
                <i class="fas fa-cog"></i> Account Settings
            </a>
            <a href="#" class="dropdown-item">
                <i class="fas fa-users-cog"></i> Manage Users
            </a> -->
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item" id="logoutButton">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</nav>

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Toggle sidebar on mobile
    document.getElementById('menuToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('active');
    });
    
    // Toggle user dropdown
    document.getElementById('userProfile').addEventListener('click', function(e) {
        e.stopPropagation();
        document.getElementById('userDropdown').classList.toggle('active');
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#userProfile')) {
            document.getElementById('userDropdown').classList.remove('active');
        }
    });
    
    // Prevent dropdown from closing when clicking inside it
    document.getElementById('userDropdown').addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    // SweetAlert for logout confirmation
    document.getElementById('logoutButton').addEventListener('click', function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Are you sure you want to logout?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3B71CA',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Logout',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to logout page
                window.location.href = '../public/logout.php';
            }
        });
    });
    
    // Open profile modal
    function openProfileModal() {
        profileModal.classList.add('active');
    }
    
    // Close profile modal
    function closeProfileModal() {
        profileModal.classList.remove('active');
    }
</script>