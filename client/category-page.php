<?php
session_start();
require_once '../database/starroofing_db.php';

// Fetch all categories
$categories = [];
$query = "SELECT category_id, category_name, description FROM categories ORDER BY category_name ASC";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Category icons mapping (you can customize these)
$category_icons = [
    'Roofing Materials' => 'fa-building-shield',
    'Concrete & Cement' => 'fa-box',
    'Steel & Metal' => 'fa-bars-staggered',
    'Wood & Lumber' => 'fa-tree',
    'Paint & Coatings' => 'fa-paint-roller',
    'Tools & Equipment' => 'fa-wrench',
    'Electrical' => 'fa-bolt',
    'Plumbing' => 'fa-pipe',
    'Hardware' => 'fa-screwdriver-wrench',
    'Insulation' => 'fa-layer-group',
    'Accessories' => 'fa-box'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Categories - Star Roofing & Construction</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css"/>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body { 
            font-family: 'Montserrat', sans-serif; 
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #1a1a2e 100%);
            color: #fff;
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* Decorative Background Elements */
        .bg-decoration {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .bg-decoration::before,
        .bg-decoration::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(233, 185, 73, 0.08) 0%, transparent 70%);
        }

        .bg-decoration::before {
            width: 600px;
            height: 600px;
            top: -300px;
            right: -200px;
            animation: float 20s ease-in-out infinite;
        }

        .bg-decoration::after {
            width: 400px;
            height: 400px;
            bottom: -150px;
            left: -100px;
            animation: float 15s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(50px, 50px); }
        }

        /* Hero Section */
        .categories-hero {
            position: relative;
            padding: 100px 20px 60px;
            text-align: center;
            z-index: 1;
        }

        .categories-hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(to right, #ffffff, #e9b949);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
            letter-spacing: 1px;
        }

        .categories-hero p {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.75);
            max-width: 700px;
            margin: 0 auto 40px;
            line-height: 1.8;
        }

        .hero-divider {
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, transparent, #e9b949, transparent);
            margin: 0 auto;
        }

        /* Main Content */
        .categories-content { 
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px 80px;
            position: relative;
            z-index: 1;
        }

        /* Search Bar */
        .search-container {
            max-width: 600px;
            margin: 0 auto 60px;
            position: relative;
        }

        .search-container input {
            width: 100%;
            padding: 18px 60px 18px 24px;
            border-radius: 50px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            color: #fff;
            font-size: 1rem;
            font-family: 'Montserrat', sans-serif;
            transition: all 0.3s ease;
        }

        .search-container input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .search-container input:focus {
            outline: none;
            border-color: #e9b949;
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 0 4px rgba(233, 185, 73, 0.15);
        }

        .search-icon {
            position: absolute;
            right: 24px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.5);
            font-size: 1.2rem;
            pointer-events: none;
        }

        /* Categories Grid */
        .categories-grid { 
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
        }

        .category-card {
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 24px;
            padding: 40px 30px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            text-decoration: none;
            display: block;
        }

        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(233, 185, 73, 0.05) 0%, transparent 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .category-card:hover::before {
            opacity: 1;
        }

        .category-card:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(233, 185, 73, 0.5);
            transform: translateY(-12px);
            box-shadow: 0 20px 50px rgba(233, 185, 73, 0.25);
        }

        .category-icon-wrapper {
            width: 100px;
            height: 100px;
            margin: 0 auto 25px;
            background: rgba(233, 185, 73, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.4s ease;
            position: relative;
            z-index: 1;
        }

        .category-card:hover .category-icon-wrapper {
            background: rgba(233, 185, 73, 0.25);
            transform: scale(1.1) rotate(5deg);
        }

        .category-icon-wrapper i {
            font-size: 2.5rem;
            color: #e9b949;
            transition: transform 0.4s ease;
        }

        .category-card:hover .category-icon-wrapper i {
            transform: scale(1.15);
        }

        .category-card h3 {
            font-size: 1.5rem;
            color: #fff;
            margin-bottom: 15px;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }

        .category-card p {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.65);
            line-height: 1.6;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .category-badge {
            display: inline-block;
            background: rgba(233, 185, 73, 0.2);
            color: #e9b949;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            position: relative;
            z-index: 1;
        }

        .view-all-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #e9b949;
            color: #1a1a2e;
            padding: 14px 32px;
            border-radius: 50px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            margin-top: 10px;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            border: 2px solid #e9b949;
            position: relative;
            z-index: 1;
        }

        .view-all-btn:hover {
            background: transparent;
            color: #e9b949;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(233, 185, 73, 0.4);
        }

        .view-all-btn i {
            transition: transform 0.3s ease;
        }

        .view-all-btn:hover i {
            transform: translateX(5px);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: rgba(255, 255, 255, 0.5);
        }

        .empty-state i {
            font-size: 4rem;
            color: rgba(233, 185, 73, 0.3);
            margin-bottom: 20px;
        }

        .empty-state p {
            font-size: 1.2rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .categories-hero {
                padding: 80px 20px 40px;
            }

            .categories-hero h1 {
                font-size: 2.5rem;
            }

            .categories-hero p {
                font-size: 1rem;
            }

            .categories-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .category-card {
                padding: 35px 25px;
            }

            .search-container {
                margin-bottom: 40px;
            }
        }

        @media (max-width: 480px) {
            .categories-hero h1 {
                font-size: 2rem;
            }

            .categories-hero p {
                font-size: 0.9rem;
                margin-bottom: 30px;
            }

            .category-icon-wrapper {
                width: 80px;
                height: 80px;
            }

            .category-icon-wrapper i {
                font-size: 2rem;
            }

            .category-card h3 {
                font-size: 1.3rem;
            }

            .category-card p {
                font-size: 0.85rem;
            }
        }

        /* Animation Classes */
        [data-aos] {
            opacity: 0;
            transition-property: opacity, transform;
        }

        [data-aos].aos-animate {
            opacity: 1;
        }
    </style>
</head>
<body>
    <!-- Background Decoration -->
    <div class="bg-decoration"></div>

    <!-- Hero Section -->
    <div class="categories-hero">
        <h1 data-aos="fade-down">Product Categories</h1>
        <p data-aos="fade-up" data-aos-delay="100">
            Explore our comprehensive range of roofing and construction materials. 
            Choose a category to discover premium quality products for your project.
        </p>
        <div class="hero-divider" data-aos="fade-up" data-aos-delay="200"></div>
    </div>

    <!-- Main Content -->
    <div class="categories-content">
        <!-- Search Bar -->
        <div class="search-container" data-aos="fade-up" data-aos-delay="300">
            <input type="text" id="searchInput" placeholder="Search categories...">
            <i class="fas fa-search search-icon"></i>
        </div>

        <!-- Categories Grid -->
        <?php if (!empty($categories)): ?>
        <div class="categories-grid" id="categoriesGrid">
            <?php 
            $delay = 100;
            foreach ($categories as $category): 
                // Get icon for category or use default
                $icon = 'fa-box';
                foreach ($category_icons as $key => $value) {
                    if (stripos($category['category_name'], $key) !== false) {
                        $icon = $value;
                        break;
                    }
                }
            ?>
            <a href="materials.php?category=<?= urlencode($category['category_name']) ?>" 
               class="category-card"
               data-aos="fade-up" 
               data-aos-delay="<?= $delay ?>"
               data-name="<?= strtolower(htmlspecialchars($category['category_name'])) ?>"
               data-description="<?= strtolower(htmlspecialchars($category['description'] ?? '')) ?>">
                
                <div class="category-icon-wrapper">
                    <i class="fas <?= $icon ?>"></i>
                </div>

                <h3><?= htmlspecialchars($category['category_name']) ?></h3>
                
                <?php if (!empty($category['description'])): ?>
                <p><?= htmlspecialchars($category['description']) ?></p>
                <?php else: ?>
                <p>Explore our selection of quality <?= strtolower(htmlspecialchars($category['category_name'])) ?> for your construction needs.</p>
                <?php endif; ?>

                <span class="category-badge">View Products</span>
            </a>
            <?php 
                $delay += 50;
                if ($delay > 400) $delay = 100;
            endforeach; 
            ?>
        </div>

        <!-- View All Products Button -->
        <div style="text-align: center; margin-top: 60px;" data-aos="fade-up" data-aos-delay="500">
            <a href="materials.php" class="view-all-btn">
                <i class="fas fa-th"></i>
                View All Products
            </a>
        </div>

        <?php else: ?>
            <div class="empty-state" data-aos="fade-up">
                <i class="fas fa-box-open"></i>
                <p>No categories available at the moment.</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
    // Initialize AOS (Animate On Scroll)
    AOS.init({
        duration: 800,
        once: true,
        offset: 100
    });

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const categoryCards = document.querySelectorAll('.category-card');

        // Search functionality
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            let visibleCount = 0;

            categoryCards.forEach(card => {
                const name = card.getAttribute('data-name');
                const description = card.getAttribute('data-description');
                const matchSearch = name.includes(searchTerm) || description.includes(searchTerm);

                if (matchSearch) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Show/hide empty state
            const categoriesGrid = document.getElementById('categoriesGrid');
            let emptyState = document.querySelector('.search-empty-state');
            
            if (visibleCount === 0 && searchTerm !== '' && !emptyState) {
                emptyState = document.createElement('div');
                emptyState.className = 'empty-state search-empty-state';
                emptyState.innerHTML = '<i class="fas fa-search"></i><p>No categories found matching "' + searchTerm + '"</p>';
                categoriesGrid.parentNode.insertBefore(emptyState, categoriesGrid.nextSibling);
            } else if ((visibleCount > 0 || searchTerm === '') && emptyState) {
                emptyState.remove();
            }
        });
    });
    </script>
</body>
</html>