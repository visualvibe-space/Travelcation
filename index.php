<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExploreWorld Travel Agency - Your Gateway to Amazing Journeys</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2A4365;
            --secondary-color: #F28C28;
            --accent-color: #00A6C8;
            --dark-color: #1A202C;
            --light-color: #F7FAFC;
            --text-color: #2D3748;
            --text-light: #718096;
            --border-color: #E2E8F0;
            --success-color: #38A169;
            --white: #FFFFFF;
            
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.07);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --shadow-xl: 0 20px 25px rgba(0,0,0,0.15);
            --shadow-2xl: 0 25px 50px -12px rgba(0,0,0,0.25);
            
            --navbar-height: 72px;
            --cta-height: 40px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-color);
            line-height: 1.6;
            overflow-x: hidden;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            opacity: 0;
            transition: opacity 0.4s ease-in-out;
        }

        body.page-loaded {
            opacity: 1;
        }

        /* ================= TOP CTA BAR ================= */
        .top-cta {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 40px;
            background: var(--accent-color);
            color: #fff;
            z-index: 10000;
            font-size: 14px;
            display: flex;
            align-items: center;
            transition: transform 0.4s ease;
        }

        .top-cta.hidden {
            transform: translateY(-100%);
        }

        .top-cta .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-cta span {
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .top-cta .btn-outline-light {
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 0.25rem 1rem;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .top-cta .btn-outline-light:hover {
            background: white;
            color: var(--accent-color);
            border-color: white;
            transform: translateY(-2px);
        }


    /* Navbar */
    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      background: var(--white) !important;
      box-shadow: var(--shadow-sm);
      z-index: 9999;

      padding: 8px 20px; /* 🔽 reduce this */
  }
  .navbar {
      position: fixed;
      top: 40px; /* below CTA */
      width: 100%;
      transition: top 0.4s ease;
  }
  /* When CTA is hidden */
  .navbar.sticky {
      top: 0;
  }

  .navbar a {
      padding: 6px 12px; /* 🔽 reduce */
      line-height: 1.2;
      font-size: 17px; /* optional */
  }
  .navbar {
      height: 150px; 
  }

          .navbar.sticky {
              top: 0;
              background: var(--white) !important;
              box-shadow: var(--shadow-md);
          }

          .navbar-brand img {
              width: 140px;
              height: auto;
              transition: all 0.3s ease;
          }

          @media (max-width: 768px) {
              .navbar-brand img {
                  width: 100px;
              }
          }

          .nav-link {
              color: var(--dark-color) !important;
              font-weight: 600;
              padding: 0.5rem 1rem !important;
              margin: 0 0.25rem;
              border-radius: 6px;
              transition: all 0.3s ease;
              position: relative;
          }

          .nav-link:hover {
              color: var(--secondary-color) !important;
              background: rgba(242, 140, 40, 0.1);
          }

          .nav-link.active {
              color: var(--secondary-color) !important;
          }

          .nav-link.active::after {
              content: '';
              position: absolute;
              bottom: -5px;
              left: 50%;
              transform: translateX(-50%);
              width: 20px;
              height: 3px;
              background: var(--secondary-color);
              border-radius: 2px;
          }
              /* Navbar dropdown background */
  .navbar .dropdown-menu {
      background-color: #ffffff !important;
      border: 1px solid var(--border-color);
      box-shadow: var(--shadow-lg);
      padding: 0.5rem 0;
      z-index: 1055;
  }
  @media (max-width: 768px) {
      .navbar-collapse {
          background-color: #ffffff;
          padding: 1rem;
          box-shadow: var(--shadow-md);
      }

      .navbar .dropdown-menu {
          position: static;
          float: none;
          box-shadow: none;
          border: none;
          padding-left: 1rem;
      }
  }
  .navbar .dropdown-item {
      color: var(--text-color);
      font-weight: 500;
  }

  .navbar .dropdown-item:hover {
      background-color: var(--light-color);
      color: var(--primary-color);
  }

  .navbar .dropdown-menu {
      background-color: #ffffff !important;
      border: 1px solid var(--border-color);
      box-shadow: var(--shadow-lg);
      padding: 0.5rem 0;
      z-index: 1055;
  }
  @media (max-width: 768px) {
      .navbar-collapse {
          background-color: #ffffff;
          padding: 1rem;
          box-shadow: var(--shadow-md);
      }

      .navbar .dropdown-menu {
          position: static;
          float: none;
          box-shadow: none;
          border: none;
          padding-left: 1rem;
      }
  }


  .navbar .dropdown-item {
      color: var(--text-color);
      font-weight: 500;
  }

  .navbar .dropdown-item:hover {
      background-color: var(--light-color);
      color: var(--primary-color);
  }
  /* ================= TOP CTA BAR ================= */
  .top-cta {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 40px;
      background: var(--accent-color);
      color: #fff;
      z-index: 10000;
      font-size: 14px;
      display: flex;
      align-items: center;
      transition: transform 0.4s ease;
  }

  .top-cta.hidden {
      transform: translateY(-100%);
  }

         /* ================= BUTTONS ================= */
  .btn-primary {
      background: var(--secondary-color);
      border: none;
      padding: 0.75rem 2rem;
      font-weight: 600;
      border-radius: 6px;
      transition: all 0.3s ease;
  }

  .btn-primary:hover {
      background: var(--primary-color);
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
  }

  .btn-outline {
      border: 2px solid var(--secondary-color);
      background: transparent;
      color: var(--secondary-color);
  }

  .btn-outline:hover {
      background: var(--secondary-color);
      color: var(--white);
  }

        /* ================= HERO SECTION ================= */
        .hero-section {
            position: relative;
            min-height: 100vh;
            padding-top: calc(var(--navbar-height) + var(--cta-height));
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--dark-color) 100%);
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 1;
        }

        .hero-background {
            position: absolute;
            inset: 0;
            z-index: 0;
        }

        .bg-slide {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }

        .bg-slide.active {
            opacity: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 900px;
            width: 100%;
            padding: 2rem;
            margin: 0 auto;
            text-align: center;
            color: var(--white);
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            animation: fadeInUp 1s ease;
        }

        .hero-description {
            font-size: 1.3rem;
            opacity: 0.95;
            margin-bottom: 2.5rem;
            line-height: 1.8;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            animation: fadeInUp 1s ease 0.2s both;
        }

        .hero-buttons {
            animation: fadeInUp 1s ease 0.4s both;
        }

        /* ================= FOOTER ================= */
        footer {
            background: var(--dark-color);
            color: var(--white);
            padding: 4rem 0 2rem;
        }

        .footer-title {
            color: var(--white);
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.75rem;
        }

        .footer-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, var(--secondary-color), var(--accent-color));
            border-radius: 2px;
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 0.75rem;
        }

        .footer-links a {
            color: #CBD5E0;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            display: inline-block;
        }

        .footer-links a:hover {
            color: var(--secondary-color);
            transform: translateX(5px);
        }

        .footer-links a i {
            margin-right: 0.5rem;
            font-size: 0.8rem;
            color: var(--secondary-color);
        }

        .contact-info {
            list-style: none;
            padding: 0;
        }

        .contact-info li {
            margin-bottom: 1rem;
            color: #CBD5E0;
            font-size: 0.95rem;
            display: flex;
            align-items: flex-start;
        }

        .contact-info i {
            color: var(--secondary-color);
            margin-right: 0.75rem;
            width: 20px;
            margin-top: 3px;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .social-link {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            text-decoration: none;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .social-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 0;
        }

        .social-link i {
            position: relative;
            z-index: 1;
        }

        .social-link:hover {
            transform: translateY(-5px) scale(1.1);
        }

        .social-link:hover::before {
            opacity: 1;
        }

        .copyright {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 3rem;
            padding-top: 1.5rem;
            text-align: center;
            color: #A0AEC0;
            font-size: 0.875rem;
        }

        /* ================= ANIMATIONS ================= */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.8s ease forwards;
        }

        /* ================= RESPONSIVE ================= */
        @media (max-width: 1200px) {
            .hero-title {
                font-size: 3.5rem;
            }
        }

        @media (max-width: 992px) {
            .hero-title {
                font-size: 3rem;
            }
            
            .hero-description {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 768px) {
            .hero-section {
                min-height: 80vh;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-description {
                font-size: 1.1rem;
            }
            
            .hero-content {
                padding: 1.5rem;
            }
        }

        @media (max-width: 576px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .hero-description {
                font-size: 1rem;
            }
            
            .btn-primary, .btn-outline {
                width: 100%;
                margin: 0.5rem 0;
            }
            
            .hero-buttons {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }
        }

        /* ================= MOBILE HERO: SHOW ONLY BUTTONS ================= */
        @media (max-width: 768px) {
            .hero-title,
            .hero-description {
                display: none !important;
            }

            .hero-content {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding-top: 1rem;
                padding-bottom: 1rem;
            }

            .hero-buttons {
                width: 100%;
                max-width: 280px;
            }

            .hero-buttons .btn-primary,
            .hero-buttons .btn-outline {
                width: 100%;
                margin: 0.5rem 0;
            }
        }
    </style>
</head>
<body>
    <!-- Top CTA Header -->
    <div class="top-cta" id="topCta">
        <div class="container d-flex justify-content-between align-items-center">
            <span>
                ✈️ Flat 20% Off on International Packages!
            </span>
            <a href="tel:+919033186905" class="btn btn-sm btn-outline-light">
                Call Now
            </a>
        </div>
    </div>

    <script>
        const cta = document.getElementById("topCta");
        const navbar = document.querySelector(".navbar");
        const heroSection = document.querySelector(".hero-section");
        
        if (heroSection) {
            const triggerPoint = heroSection.offsetHeight - 100;

            window.addEventListener("scroll", () => {
                if (window.scrollY > triggerPoint) {
                    cta.classList.add("hidden");
                    navbar.classList.add("sticky");
                } else {
                    cta.classList.remove("hidden");
                    navbar.classList.remove("sticky");
                }
            });
        }

        // Page load animation
        document.addEventListener("DOMContentLoaded", () => {
            document.body.classList.add("page-loaded");
        });
    </script>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="uploads/lg-tra (1).png" alt="ExploreWorld Travel" class="img-fluid" style="width: 120px; height: 120px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink123" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Explore
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink123">
                            <a class="dropdown-item" href="#packages">Packages</a>
                            <a class="dropdown-item" href="#hotels">Hotels</a>
                            <a class="dropdown-item" href="#offers">Exclusive Offers</a>
                            <a class="dropdown-item" href="#destinations">Destinations</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="home.php#contact-section">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="submit_feedback.php">Submit Feedback</a>
                    </li>
                </ul>
                <a href="#contact" class="btn btn-primary ms-lg-3 mt-3 mt-lg-0">
                    <i class="fas fa-paper-plane me-2"></i>Quick Enquiry
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section" id="home">
        <div class="hero-background">
            <div class="bg-slide active" style="background-image: url('https://images.unsplash.com/photo-1469474968028-56623f02e42e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1474&q=80');"></div>
            <div class="bg-slide" style="background-image: url('https://images.unsplash.com/photo-1441974231531-c6227db76b6e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80');"></div>
            <div class="bg-slide" style="background-image: url('https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?ixlib=rb-4.0.3&auto=format&fit=crop&w=1574&q=80');"></div>
        </div>
        
        <div class="hero-overlay"></div>
        
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Discover Amazing Destinations</h1>
                <p class="hero-description">Your dream vacation is just a click away! Explore breathtaking locations, book luxurious stays, and create memories that last a lifetime.</p>
                <div class="hero-buttons">
                    <a href="home.php" class="btn btn-primary btn-lg me-md-3">
                        Explore Packages, Hotels & other services 
                    </a>
                    <a href="#contact" class="btn btn-outline btn-lg">
                        <i class="fas fa-headset me-2"></i>Contact Us
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <h3 class="footer-title">ExploreWorld Travel</h3>
                    <p class="mb-3 text-white-50">Your trusted partner for creating unforgettable travel experiences with personalized service and expert guidance.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h3 class="footer-title">Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="#home"><i class="fas fa-chevron-right"></i> Home</a></li>
                        <li><a href="#about"><i class="fas fa-chevron-right"></i> About Us</a></li>
                        <li><a href="#packages"><i class="fas fa-chevron-right"></i> Packages</a></li>
                        <li><a href="#hotels"><i class="fas fa-chevron-right"></i> Hotels</a></li>
                        <li><a href="#contact"><i class="fas fa-chevron-right"></i> Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h3 class="footer-title">Contact Info</h3>
                    <ul class="contact-info">
                        <li><i class="fas fa-map-marker-alt"></i> 214, Oberon, Opp. Mercedes-Benz Showroom, New City Light Road, Surat – 395017</li>
                        <li><i class="fas fa-phone"></i> +91-90331 86905</li>
                        <li><i class="fas fa-envelope"></i> travelcation.co.in</li>
                        <li><i class="fas fa-clock"></i> Mon-Sun: 9:00 AM - 8:00 PM</li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h3 class="footer-title">Newsletter</h3>
                    <p class="mb-3 text-white-50">Subscribe to receive exclusive travel deals and updates.</p>
                    <div class="input-group">
                        <input type="email" class="form-control bg-dark text-white border-secondary" placeholder="Your email address">
                        <button class="btn btn-primary" type="button">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; 2024 ExploreWorld Travel Agency. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Hero carousel functionality
        const slides = document.querySelectorAll('.bg-slide');
        let currentSlide = 0;
        
        function nextSlide() {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }
        
        // Auto slide every 5 seconds
        if (slides.length > 1) {
            setInterval(nextSlide, 5000);
        }

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const offset = 80; // Height of fixed navbar
                    const targetPosition = target.offsetTop - offset;
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
    
    <!-- Optional Bootstrap JS for dropdowns -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>