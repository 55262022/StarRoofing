<link rel="stylesheet" href="../css/navbar.css">

<header class="navbar" id="navbar">
        <div class="navbar-container">
            <div class="navbar-logo">
                <img src="../assets/images/logo.png" alt="star roofing logo">
            </div>
            
            <!-- Desktop Nav -->
            <nav class="navbar-links">
                <a href="homepage.php#home">Home</a>
                <a href="homepage.php#about">About</a>
                <a href="homepage.php#projects">Projects</a>
                <a href="homepage.php#services">Services</a>
                <a href="homepage.php#contact">Contact</a>
                <a href="login.php">Log In</a>
            </nav>

            <!-- Hamburger Menu Button -->
            <button id="menu-toggle" class="menu-toggle" data-aos="fade-left">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="mobile-menu">
            <div class="mobile-logo" data-aos="fade-right">Star Roofing</div>
            <a href="homepage.php#home">Home</a>
            <a href="homepage.php#about">About</a>
            <a href="homepage.php#projects">Projects</a>
            <a href="homepage.php#services">Services</a>
            <a href="homepage.php#contact">Contact</a>
            <a href="login.php">Log In</a>
        </div>
    </header>
        <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        // Initialize AOS animations
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
        
        // Mobile menu functionality
        const menuToggle = document.getElementById('menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuLinks = document.querySelectorAll('#mobile-menu a');

        menuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('open');
            document.body.style.overflow = mobileMenu.classList.contains('open') ? 'hidden' : '';
        });

        menuLinks.forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.remove('open');
                document.body.style.overflow = '';
            });
        });
    </script>