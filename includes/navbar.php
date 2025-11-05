<link rel="stylesheet" href="../css/navbar.css">
<link rel="stylesheet" href="../css/home-sidebar.css">

<header class="navbar" id="navbar">
    <div class="navbar-container">
        <!-- Left side: Burger Button + Logo -->
        <div class="navbar-left">
            <!-- Burger Button -->
            <button class="sidebar-toggle" id="sidebar-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <!-- Logo -->
            <div class="navbar-logo">
                <img src="../assets/images/logo.png" alt="Star Roofing Logo">
            </div>
        </div>

        <!-- Right-side icons -->
        <div class="navbar-icons">
            <a href="../client/pages/client-profile.php" class="icon-btn" title="Profile">
                <i class="fa-regular fa-user"></i>
            </a>
            <a href="../client/pages/my_basket.php" class="icon-btn" title="Basket">
                <i class="fa-solid fa-bag-shopping"></i>
            </a>
            <a href="../client/pages/my_orders.php" class="icon-btn" title="My Orders">
                <i class="fa-solid fa-clipboard-list"></i>
            </a>
        </div>
    </div>
</header>

<!-- Sidebar -->
<aside class="sidebar" id="homepage-sidebar">
    <ul class="sidebar-menu">
        <li><a href="#" onclick="showSection('home')"><i class="fas fa-home"></i> <span>Home</span></a></li>
        <li><a href="#" onclick="showSection('about')"><i class="fas fa-info-circle"></i> <span>About</span></a></li>
        <li><a href="#" onclick="showSection('products')"><i class="fas fa-building"></i> <span>Products</span></a></li>
        <li><a href="#" onclick="showSection('services')"><i class="fas fa-tools"></i> <span>Services</span></a></li>
        <li><a href="#" onclick="showSection('3d-modeling')"><i class="fas fa-tools"></i> <span>3D Modeling</span></a></li>
        <!-- <li><a href="#" onclick="showSection('contact')"><i class="fas fa-envelope"></i> <span>Contact</span></a></li> -->
        
        <?php if (isset($_SESSION['account_id']) && $_SESSION['role_id'] == 2): ?>
            <!-- Show My Profile for logged-in clients -->
            <!-- <li><a href="../client/pages/client-profile.php"><i class="fas fa-user-circle"></i> <span>My Profile</span></a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li> -->
        <?php else: ?>
            <!-- Show Sign In for guests -->
            <li><a href="login.php"><i class="fas fa-user"></i> <span>Sign In</span></a></li>
        <?php endif; ?>
    </ul>
</aside>

<!-- Scripts -->
<script>
const sidebar = document.getElementById('homepage-sidebar');
const toggleBtn = document.getElementById('sidebar-toggle');

// Toggle sidebar function
toggleBtn.addEventListener('click', function() {
    if (window.innerWidth <= 768) {
        sidebar.classList.toggle('show'); // Mobile
    } else {
        sidebar.classList.toggle('hide'); // Desktop
        document.body.classList.toggle('sidebar-collapsed');
    }
});

function showSection(section) {
    const allSections = document.querySelectorAll('section');
    const productsIframe = document.getElementById('products-section');
    const modelingIframe = document.getElementById('3d-modeling-section');
    const loginIframe = document.getElementById('login-section');

    // Hide all iframe sections first
    if (productsIframe) productsIframe.classList.add('hidden');
    if (modelingIframe) modelingIframe.classList.add('hidden');
    if (loginIframe) loginIframe.classList.add('hidden');

    // Case: Products section
    if (section === 'products') {
        allSections.forEach(sec => sec.classList.add('hidden'));
        if (productsIframe) productsIframe.classList.remove('hidden');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Case: 3D Modeling section
    else if (section === '3d-modeling') {
        allSections.forEach(sec => sec.classList.add('hidden'));
        if (modelingIframe) modelingIframe.classList.remove('hidden');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Case: other normal sections (Home, About, Services)
    else {
        allSections.forEach(sec => {
            if (['products-section', 'login-section', '3d-modeling-section'].includes(sec.id)) {
                sec.classList.add('hidden');
            } else {
                sec.classList.remove('hidden');
            }
        });

        // Scroll to the right section anchor
        if (section !== 'home') {
            const target = document.getElementById(section);
            if (target) {
                setTimeout(() => {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 100);
            }
        } else {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    // Highlight active sidebar link
    document.querySelectorAll('.sidebar-menu li a').forEach(link => link.classList.remove('active'));
    const activeLink = document.querySelector(`.sidebar-menu li a[onclick*="showSection('${section}')"]`);
    if (activeLink) activeLink.classList.add('active');

    // Close sidebar on mobile
    if (window.innerWidth <= 768) {
        sidebar.classList.remove('show');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const productsSection = document.getElementById('products-section');
    const modelingSection = document.getElementById('3d-modeling-section');

    if (productsSection && !window.location.hash.includes('products')) {
        productsSection.classList.add('hidden');
    }
    if (modelingSection && !window.location.hash.includes('3d-modeling')) {
        modelingSection.classList.add('hidden');
    }

    // Set home as active by default
    const homeLink = document.querySelector(`.sidebar-menu li a[onclick*="showSection('home')"]`);
    if (homeLink) homeLink.classList.add('active');
});

// Handle window resize
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        sidebar.classList.remove('show');
    }
});
</script>