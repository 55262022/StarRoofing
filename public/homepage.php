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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css"/>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Additional styles for the Get Started button */
        .hero-cta {
            margin-top: 2rem;
            text-align: center;
        }
        
        .cta-button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #1a365d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            border: 2px solid #1a365d;
            font-size: 1rem;
        }
        
        .cta-button:hover {
            background-color: #e9b949;
            color: #1a365d;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        @media (max-width: 768px) {
            .cta-button {
                padding: 10px 25px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body id="home">
    <?php include '../includes/navbar.php'?>

    <section class="hero">
        <div class="container">
            <p>"We Build With Passion"</p>
            <p>Star Roofing and Construction is located at San Juan Accfa District, Cabanatuan City (In-front of Hall of Justice)</p>
            
            <!-- Get Started Button -->
            <div class="hero-cta">
                <a href="login.php" class="cta-button">Get Started</a>
            </div>
        </div>
    </section>

    <section class="mission-vision">
        <div class="container">
            <h2 class="section-title">Our Mission & Vision</h2>
            <p>Our Mission
            Dj Star Roofing and Construction is a company that committed to build a strong relationships based on integrity, honesty, performance and client satisfaction.
            Continue to meet the changing needs of every customer to provide quality services and delivered by most qualified people.</p>

            <p>Our Vision
            To be the best Roofing and Construction company not only in Nueva Ecija but around the Philippines, by delivering safety and successful projects and services. We STAR ROOFING AND CONSTRUCTION "BUILD WITH PASSION"</p>
        </div>
    </section>

    <section class="about" id="about">
        <div class="container">
            <h2 class="section-title">About Us</h2>
            <div class="about-content">
                <div class="about-text">
                    <p>Star Roofing and Construction established last February 10, 2013 and owned by Mr. Don Jerome F. Empania. This construction company is located at San Juan Accfa District, Cabanatuan City and offers Design and Construction, All kinds of Roofing, Steel truss, Glass and other services. And we assure our clients that our work is "Build With Passion".</p>
                </div>
            </div>
        </div>
    </section>

    <section class="services" id="services">
        <div class="container">
            <h2 class="section-title">Services/Products Offered</h2>
            <div class="services-grid">
                <div class="service-item">
                    <h3>All Kinds of Roofing</h3>
                </div>
                <div class="service-item">
                    <h3>Steel Truss</h3>
                </div>
                <div class="service-item">
                    <h3>Glass and Aluminum</h3>
                </div>
                <div class="service-item">
                    <h3>Stainless</h3>
                </div>
                <div class="service-item">
                    <h3>Bender</h3>
                </div>
                <div class="service-item">
                    <h3>Insulator</h3>
                </div>
            </div>
        </div>
    </section>

    <section class="contact" id="contact">
        <div class="container">
            <h2 class="section-title">Contact Us</h2>
            <p>For more information contact the following:
            Ms. Janice M. Francisco - Account Supervisor
            (Smart) 0908-620-23-813/ (Sun) 0933-628-3312 / Tel. No.: (044) 329-0881
            or kindly write your concerns below. Thank You!</p>
            
            <form id="contactForm">
            <div class="contact-form">
                <div class="form-group">
                <input type="text" name="firstname" placeholder="Your Name" required>
                </div>
                <div class="form-group">
                <input type="text" name="lastname" placeholder="Lastname" required>
                </div>
                <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                <textarea name="message" placeholder="Your Message" required></textarea>
                </div>
                <div class="form-group button-container">
                <button type="submit">SUBMIT</button>
                </div>
            </div>
            </form>
        </div>
    </section>

    <?php include '../includes/footer.php'?>

    <script>
        $(document).ready(function(){
            $("#contactForm").on("submit", function(e){
            e.preventDefault(); // stop normal form submission

            $.ajax({
                url: "save_contact.php",
                type: "POST",
                data: $(this).serialize(),
                success: function(response){
                // Inject the response (SweetAlert JS) into page
                $("body").append(response);
                }
            });
            });
        });
    </script>
</body>
</html>