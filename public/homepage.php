<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Star Roofing & Construction</title>
    <link rel="stylesheet" href="../css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<style>
    .hidden{
    display: none;
    }

    #products-section {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 0;
    margin: 0;
    }

    #products-section iframe {
        width: 100%;
        height: 100vh;
        border: none;
        display: block;
    }

</style>
<body id="home">

    <?php include '../includes/navbar.php'?>
    <!-- Hero Section - Modern Roofing Banner -->
    <section class="hero-modern" id="hero-section">
    
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title">We build with passion</h1>
            <p class="hero-subtitle">
                Expert Roofing and Construction Services for Homes and Businesses
            </p>

            <div class="hero-cta-buttons">
            <?php if(!isset($_SESSION['account_id'])): ?>
                <a href="register.php" class="cta-primary">GET STARTED</a>
            <?php endif; ?>
                <a href="#about" class="cta-secondary">LEARN MORE</a>
            </div>

            <div class="hero-tagline">
                "Quality That Stands Above the Rest"
            </div>
        </div>
         
        <div class="scroll-indicator">
            <div class="scroll-arrow"></div>
        </div>
    </section>


    <!-- Mission & Vision - Modern Card Layout -->
    <section class="mission-vision-modern-section">
        <div class="container">
            <div class="section-header">
                <span class="section-label">OUR FOUNDATION</span>
                <h2 class="section-title-modern">Mission & Vision</h2>
            </div>
            
            <div class="mv-grid">
                <div class="mv-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="mv-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h3>Our Mission</h3>
                    <p>To deliver exceptional roofing and construction services through innovation, quality craftsmanship, and unwavering commitment to customer satisfaction, while maintaining the highest standards of professionalism and integrity.</p>
                </div>
                
                <div class="mv-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="mv-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3>Our Vision</h3>
                    <p>To be the leading provider of roofing and construction solutions in the region, recognized for excellence, reliability, and innovative approaches to sustainable building practices.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-modern" id="about">
        <div class="container">
            <div class="about-split">
                                <div class="about-image" data-aos="fade-right">
                    <div class="image-placeholder">
                        <!-- image ng company -->
                        <img src="../company-pic/starroofing-bldg1.jpg" alt="About Star Roofing">
                        <div class="about-badge">
                            <span class="badge-year">15+</span>
                            <span class="badge-number">Years of Excellence</span>
                        </div>
                    </div>
                </div>
                
                <div class="about-content" data-aos="fade-left">
                    <div class="section-header">
                        <span class="section-label">ABOUT US</span>
                        <h2 class="section-title-modern">Excellence in Roofing & Construction Since 2008</h2>
                    </div>
                    <p class="about-description">
                        Star Roofing & Construction has been a trusted name in the industry for over 15 years. We take pride in delivering top-quality roofing solutions and construction services that stand the test of time.
                    </p>
                    <div class="about-stats">
                        <div class="stat-item">
                            <span class="stat-number">500+</span>
                            <span class="stat-label">Projects Completed</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">95%</span>
                            <span class="stat-label">Client Satisfaction</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">50+</span>
                            <span class="stat-label">Expert Team Members</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services-modern" id="services">
        <div class="container">
            <div class="section-header centered">
                <span class="section-label">WHAT WE OFFER</span>
                <h2 class="section-title-modern">Our Services</h2>
            </div>
            
            <div class="services-grid-modern">
                <div class="service-card" data-aos="fade-up" data-aos-delay="100">
                    <span class="service-number">01</span>
                    <h3>Roofing Installation</h3>
                    <p>Professional installation of various roofing systems, including metal, shingle, and flat roofs.</p>
                </div>
                <div class="service-card" data-aos="fade-up" data-aos-delay="200">
                    <span class="service-number">02</span>
                    <h3>Roof Repair & Maintenance</h3>
                    <p>Expert repair services and regular maintenance to extend your roof's lifespan.</p>
                </div>
                <div class="service-card" data-aos="fade-up" data-aos-delay="300">
                    <span class="service-number">03</span>
                    <h3>Construction Services</h3>
                    <p>Complete construction solutions from residential to commercial projects.</p>
                </div>
                <div class="service-card" data-aos="fade-up" data-aos-delay="400">
                    <span class="service-number">04</span>
                    <h3>Renovation & Remodeling</h3>
                    <p>Transform your space with our professional renovation and remodeling services.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="contact-modern" id="contact">
        <div class="container">
                        <div class="contact-split">
                <div class="contact-info-side" data-aos="fade-right">
                    <div class="section-header">
                        <span class="section-label">GET IN TOUCH</span>
                        <h2 class="section-title-modern">Let's Start Your Project Together</h2>
                    </div>
                    <p class="contact-description">
                        Ready to start your roofing or construction project? Contact us today for a free consultation and estimate. Our team of experts is here to help bring your vision to life.
                    </p>
                    <div class="contact-details">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h4>Visit Us</h4>
                                <p>San Juan Accfa District, Cabanatuan City (In-front of Hall of Justice)</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <h4>Call Us</h4>
                                <p>(044) 329-0881<br>0908-620-2381<br>0933-628-3312</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h4>Email Us</h4>
                                <p>info@starroofing.com<br>contact@starroofing.com</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form-side" data-aos="fade-left">
                    <form id="contactForm" class="modern-form">
                        <div class="form-row">
                            <div class="form-group">
                                <input type="text" name="name" placeholder="Your Name" required>
                            </div>
                            <div class="form-group">
                                <input type="email" name="email" placeholder="Your Email" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="text" name="subject" placeholder="Subject" required>
                        </div>
                        <div class="form-group">
                            <textarea name="message" placeholder="Your Message" rows="6" required></textarea>
                        </div>
                        <button type="submit" class="submit-btn">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section id="products-section" class="section hidden">
        <iframe src="../client/category-page.php"></iframe>
    </section>

    <?php include '../includes/footer.php'?>
    <?php include '../includes/chat-bot.php'?>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Contact Form
        $(document).ready(function(){
            $("#contactForm").on("submit", function(e){
                e.preventDefault();
                $.ajax({
                    url: "save_contact.php",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response){
                        Swal.fire({
                            icon: 'success',
                            title: 'Message Sent!',
                            text: 'Thank you for contacting us. We will get back to you soon.',
                            confirmButtonColor: '#1a365d'
                        });
                        $("#contactForm")[0].reset();
                    }
                });
            });
        });
    </script>
</body>
</html>