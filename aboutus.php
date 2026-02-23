<?php
  require_once __DIR__ . '/config/config.php';
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }

  /* ========================
    HANDLE MODAL ENQUIRY SUBMISSION
  ======================== */
  $modal_enquiry_success = false;
  $modal_enquiry_error = '';
  $modal_enquiry_message = '';

  // Check for flash messages from session
  if (isset($_SESSION['modal_enquiry_success'])) {
      $modal_enquiry_success = $_SESSION['modal_enquiry_success'];
      $modal_enquiry_message = $_SESSION['modal_enquiry_message'] ?? '';
      $modal_enquiry_error = $_SESSION['modal_enquiry_error'] ?? '';
      
      // Clear session variables
      unset($_SESSION['modal_enquiry_success']);
      unset($_SESSION['modal_enquiry_message']);
      unset($_SESSION['modal_enquiry_error']);
  }

  // Handle form submission
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_modal_enquiry'])) {
      
      // Get and sanitize form data
      $full_name = trim($_POST['modal_full_name'] ?? '');
      $email = trim($_POST['modal_email'] ?? '');
      $phone = trim($_POST['modal_phone'] ?? '');
      $package_name = trim($_POST['modal_package'] ?? '');
      $travel_date = trim($_POST['modal_travel_date'] ?? '');
      $travelers = trim($_POST['modal_travelers'] ?? '');
      $message = trim($_POST['modal_message'] ?? '');
      $source = trim($_POST['source'] ?? 'aboutus');
      
      // Validation
      $errors = [];
      if (empty($full_name)) $errors[] = 'Full name is required';
      if (empty($email)) $errors[] = 'Email is required';
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
      if (empty($phone)) $errors[] = 'Phone number is required';
      if (empty($travel_date)) $errors[] = 'Travel date is required';
      if (empty($travelers)) $errors[] = 'Number of travelers is required';
      
      if (empty($errors)) {
          try {
              // Insert enquiry into enquiries table
              $insertStmt = $pdo->prepare("
                  INSERT INTO enquiries (
                      full_name, 
                      email, 
                      phone, 
                      package_name, 
                      travel_date, 
                      travelers, 
                      message, 
                      source, 
                      status
                  ) VALUES (
                      :full_name, 
                      :email, 
                      :phone, 
                      :package_name, 
                      :travel_date, 
                      :travelers, 
                      :message, 
                      :source, 
                      'New'
                  )
              ");
              
              $insertStmt->execute([
                  ':full_name' => $full_name,
                  ':email' => $email,
                  ':phone' => $phone,
                  ':package_name' => !empty($package_name) ? $package_name : null,
                  ':travel_date' => $travel_date,
                  ':travelers' => $travelers,
                  ':message' => !empty($message) ? $message : null,
                  ':source' => $source
              ]);
              
              // Set success message in session
              $_SESSION['modal_enquiry_success'] = true;
              $_SESSION['modal_enquiry_message'] = 'Thank you for your enquiry! We will contact you within 24 hours.';
              
          } catch (PDOException $e) {
              // Log error (you can add error logging here)
              error_log("Enquiry submission error: " . $e->getMessage());
              
              $_SESSION['modal_enquiry_success'] = false;
              $_SESSION['modal_enquiry_error'] = 'Failed to submit enquiry. Please try again.';
          }
      } else {
          $_SESSION['modal_enquiry_success'] = false;
          $_SESSION['modal_enquiry_error'] = implode('<br>', $errors);
      }
      
      // Redirect to prevent form resubmission
      header('Location: ' . $_SERVER['PHP_SELF'] . '?success=' . ($_SESSION['modal_enquiry_success'] ? '1' : '0'));
      exit;
  }

  /* ========================
    FETCH ANY NEEDED DATA
  ======================== */
  $page_title = "About Us - ExploreWorld Travel";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" sizes="32x32" href="uploads/lg-tra (1).png">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&family=Source+Sans+Pro:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Your existing styles remain exactly the same */
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
            --shadow-xl: 0 20px 25px rgba(0,0,0,0.1);
            --navbar-height: 72px;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-color);
            line-height: 1.6;
            opacity: 0;
            transition: opacity 0.4s ease-in-out;
        }
        body.page-loaded { opacity: 1; }
        body.page-exit { opacity: 0; }

        /* Navbar */
        .navbar {
            position: fixed;
            top: 40px;
            left: 0;
            width: 100%;
            background: var(--white) !important;
            box-shadow: var(--shadow-sm);
            z-index: 9999;
            padding: 8px 20px;
            transition: top 0.4s ease;
            height: auto;
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

        /* Navbar dropdown */
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

        /* Top CTA */
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

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
            padding: calc(var(--navbar-height) + 40px + 2rem) 0 4rem;
            color: var(--white);
            text-align: center;
            position: relative;
            margin-top: 8%;
        }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .page-header .breadcrumb {
            background: transparent;
            justify-content: center;
        }

        .page-header .breadcrumb-item,
        .page-header .breadcrumb-item a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
        }

        .page-header .breadcrumb-item.active {
            color: var(--secondary-color);
        }

        /* About Sections */
        .about-section { padding: 5rem 0; }
        .about-section:nth-child(even) { background: var(--light-color); }

        .section-title {
            font-size: 2.75rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 15px;
        }

        .section-title i {
            color: var(--secondary-color);
            margin-right: 10px;
            font-size: 2rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary-color), var(--accent-color));
            border-radius: 2px;
        }

        .text-center .section-title::after {
            left: 50%;
            transform: translateX(-50%);
        }

        /* Service Cards */
        .service-card-mini {
            background: white;
            border-radius: 15px;
            padding: 2rem 1.5rem;
            box-shadow: var(--shadow-md);
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid var(--border-color);
        }

        .service-card-mini:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
            border-color: var(--secondary-color);
        }

        .service-card-mini .icon-circle {
            width: 70px;
            height: 70px;
            background: rgba(242,140,40,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .service-card-mini .icon-circle i {
            font-size: 2rem;
            color: var(--secondary-color);
        }

        .service-card-mini h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .service-card-mini ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .service-card-mini li {
            padding: 0.4rem 0;
            border-bottom: 1px dashed var(--border-color);
        }

        .service-card-mini li:last-child {
            border-bottom: none;
        }

        .service-card-mini li i {
            color: var(--success-color);
            width: 20px;
            margin-right: 8px;
        }

        /* Contact Cards */
        .contact-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
        }

        .contact-detail-item {
            display: flex;
            margin-bottom: 1.5rem;
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            background: rgba(242,140,40,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }

        .contact-icon i {
            font-size: 1.3rem;
            color: var(--secondary-color);
        }

        .contact-text h5 {
            font-weight: 600;
            margin-bottom: 0.2rem;
            color: var(--primary-color);
        }

        .contact-text p {
            color: var(--text-light);
            margin: 0;
        }

        /* Buttons */
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

        /* Footer */
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
        }

        .footer-links a:hover {
            color: var(--secondary-color);
            padding-left: 0.5rem;
        }

        .contact-info {
            list-style: none;
            padding: 0;
        }

        .contact-info li {
            margin-bottom: 1rem;
            color: #CBD5E0;
            font-size: 0.95rem;
        }

        .contact-info i {
            color: var(--secondary-color);
            margin-right: 0.75rem;
            width: 20px;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .social-link {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: var(--secondary-color);
            transform: translateY(-3px);
        }

        .copyright {
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: 3rem;
            padding-top: 1.5rem;
            text-align: center;
            color: #A0AEC0;
            font-size: 0.875rem;
        }

        /* Modal Styles */
        #enquiryModal .modal-dialog {
            margin-top: 10%;
        }

        @media (max-width: 576px) {
            #enquiryModal .modal-dialog {
                margin: 0;
                height: 100%;
                max-width: 100%;
                margin-top: 46%;
            }

            #enquiryModal .modal-content {
                height: 100%;
                border-radius: 0;
            }

            #enquiryModal .modal-body {
                overflow-y: auto;
                padding-top: 1rem;
            }
        }

        .modal-header {
            background: var(--accent-color);
            color: var(--white);
            border-bottom: none;
            padding: 1.5rem;
        }

        .modal-title {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
        }

        .btn-close-white {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        /* Map Section Styles */
        .map-section {
            position: relative;
            overflow: hidden;
        }

        .map-container {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .map-container:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl) !important;
        }

        /* Alert Styles */
        .alert {
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header h1 { font-size: 2.5rem; }
            .section-title { font-size: 2rem; }
            
            .map-section {
                padding: 3rem 0;
            }
            
            .map-container {
                margin: 0 -10px;
                border-radius: 15px !important;
            }
            
            .map-container iframe {
                border-radius: 10px;
            }
            
            .icon-circle {
                width: 50px !important;
                height: 50px !important;
            }
            
            .icon-circle i {
                font-size: 1.2rem !important;
            }
        }

        @media (max-width: 576px) {
            .map-section .col-md-4 {
                margin-bottom: 1rem;
            }
            
            .map-container {
                margin: 0;
                padding: 5px !important;
            }
            
            .btn-lg {
                display: block;
                width: 100%;
                margin-right: 0 !important;
            }
            
            .btn-outline {
                margin-top: 0.5rem;
            }
        }
        
    </style>
</head>
<body>
    <!-- Top CTA Header -->
    <div class="top-cta" id="topCta">
        <div class="container d-flex justify-content-between align-items-center">
            <span>✈️ Flat 20% Off on International Packages!</span>
            <a href="tel:+919033186905" class="btn btn-sm btn-outline-light">Call Now</a>
        </div>
    </div>

   <!-- Navigation Bar -->
   <nav class="navbar navbar-expand-lg navbar-light fixed-top">
          <div class="container">
              <a class="navbar-brand" href="index.php">
                  <img src="uploads/lg-tra (1).png" alt="ExploreWorld Travel" class="img-fluid" style="width: 120px; height: 120px;">
              </a>
              <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                  <span class="navbar-toggler-icon"></span>
              </button>
              <div class="collapse navbar-collapse" id="navbarNav">
                  <ul class="navbar-nav ms-auto">
                      <li class="nav-item">
                          <a class="nav-link active" href="#home">Home</a>
                      </li>
                      <li class="nav-item">
                          <a class="nav-link" href="aboutus.php">About</a>
                      </li>
                      <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink123" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Explore
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink123">
                        <a class="dropdown-item" href="#packages">Packages</a>
                        <a class="dropdown-item" href="#hotels">Hotels</a>
                        <a class="dropdown-item" href="offers.php">Exclusive Offers</a>
                        <a class="dropdown-item" href="alldestinations.php">Destinations</a>
                        </div>
                    </li>
                      
                      <li class="nav-item">
                          <a class="nav-link" href="#contact-section">Contact</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" href="submit_feedback.php">Submit Feedback</a>
                    </li>
                  </ul>
                  <button class="btn btn-primary ms-lg-3 mt-3 mt-lg-0" data-bs-toggle="modal" data-bs-target="#enquiryModal">
                      <i class="fas fa-paper-plane me-2"></i>Quick Enquiry
                  </button>
              </div>
          </div>
      </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>About Us</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">About Us</li>
                </ol>
            </nav>
        </div>
    </section>

    <!-- Welcome & Introduction -->
    <section class="about-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <h2 class="section-title"><i class="fas fa-smile"></i> Welcome to Travelcation</h2>
                    <p class="lead" style="color: var(--accent-color); font-weight: 600;">Your trusted travel partner since 2012.</p>
                    <p style="font-size: 1.1rem;">With years of experience in the travel industry, we specialize in providing complete travel solutions designed to make your journey smooth, memorable, and stress-free.</p>
                    <p style="font-size: 1.1rem;">At Travelcation, we believe that travel is not just about reaching a destination, but about creating unforgettable experiences. Whether you are planning a holiday, business trip, or international travel, we offer end-to-end services to take care of everything — from planning to documentation.</p>
                    <div class="d-flex gap-3 mt-4">
                        <div><i class="fas fa-check-circle text-success me-2"></i> 12+ Years Experience</div>
                        <div><i class="fas fa-check-circle text-success me-2"></i> 5000+ Happy Travellers</div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="uploads/lg-tra (1).png" alt="Travelcation Team" class="img-fluid rounded-4 shadow-lg" style="height:550px;width:550px;">
                </div>
            </div>
        </div>
    </section>

    <!-- Our Services -->
    <section class="about-section" style="background: var(--light-color);">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title d-inline-block"><i class="fas fa-concierge-bell"></i> Our Services</h2>
                <p class="lead mt-3">We offer a wide range of services to meet all your travel needs.</p>
            </div>

            <div class="row g-4">
                <!-- Tour Packages Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="service-card-mini">
                        <div class="icon-circle"><i class="fas fa-gem"></i></div>
                        <h3>✈️ Tour Packages</h3>
                        <ul>
                            <li><i class="fas fa-globe"></i> International Tour Packages</li>
                            <li><i class="fas fa-map-marker-alt"></i> Domestic Tour Packages</li>
                            <li><i class="fas fa-calendar-alt"></i> Festival & Special Offers</li>
                            <li><i class="fas fa-pencil-alt"></i> Customized Trips</li>
                        </ul>
                        <p class="mt-3 small text-muted">Whether you prefer group tours or individual travel planning, we provide flexible options for both.</p>
                    </div>
                </div>
                <!-- Hotel & Travel Booking -->
                <div class="col-md-6 col-lg-3">
                    <div class="service-card-mini">
                        <div class="icon-circle"><i class="fas fa-hotel"></i></div>
                        <h3>🏨 Hotel & Travel</h3>
                        <ul>
                            <li><i class="fas fa-hotel"></i> Hotel & Resort Bookings</li>
                            <li><i class="fas fa-plane"></i> Flight Ticket Booking</li>
                            <li><i class="fas fa-train"></i> Train Ticket Booking</li>
                        </ul>
                        <p class="mt-3 small text-muted">We ensure comfortable stays and smooth travel arrangements for every trip.</p>
                    </div>
                </div>
                <!-- Passport Services -->
                <div class="col-md-6 col-lg-3">
                    <div class="service-card-mini">
                        <div class="icon-circle"><i class="fas fa-passport"></i></div>
                        <h3>🛂 Passport Services</h3>
                        <ul>
                            <li><i class="fas fa-file-alt"></i> New Passport Applications</li>
                            <li><i class="fas fa-sync-alt"></i> Passport Renewal</li>
                            <li><i class="fas fa-edit"></i> Corrections & Updates</li>
                        </ul>
                        <p class="mt-3 small text-muted">Our team simplifies the entire process and ensures quick and hassle-free service.</p>
                    </div>
                </div>
                <!-- Visa Services -->
                <div class="col-md-6 col-lg-3">
                    <div class="service-card-mini">
                        <div class="icon-circle"><i class="fas fa-stamp"></i></div>
                        <h3>🌍 Visa Services</h3>
                        <ul>
                            <li><i class="fas fa-passport"></i> New Visa Applications</li>
                            <li><i class="fas fa-redo-alt"></i> Visa Renewal & Extension</li>
                            <li><i class="fas fa-folder-open"></i> Documentation & Processing</li>
                        </ul>
                        <p class="mt-3 small text-muted">We help you navigate visa procedures with ease and confidence.</p>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5">
                <p class="lead">Complete travel solutions under one roof — from planning to documentation.</p>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="about-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-6">
                    <h2 class="section-title"><i class="fas fa-thumbs-up"></i> Why Choose Travelcation?</h2>
                    <div class="d-flex flex-column gap-3 mt-4">
                        <div class="d-flex">
                            <div style="min-width: 40px;"><i class="fas fa-check-circle text-success fs-4"></i></div>
                            <div><strong>Trusted travel agency since 2012</strong> — over a decade of excellence.</div>
                        </div>
                        <div class="d-flex">
                            <div style="min-width: 40px;"><i class="fas fa-check-circle text-success fs-4"></i></div>
                            <div><strong>Complete travel solutions under one roof</strong> — packages, hotels, flights, passport, visa.</div>
                        </div>
                        <div class="d-flex">
                            <div style="min-width: 40px;"><i class="fas fa-check-circle text-success fs-4"></i></div>
                            <div><strong>Customized packages for individuals and groups</strong> — we tailor to your needs.</div>
                        </div>
                        <div class="d-flex">
                            <div style="min-width: 40px;"><i class="fas fa-check-circle text-success fs-4"></i></div>
                            <div><strong>Competitive pricing and transparent service</strong> — no hidden costs.</div>
                        </div>
                        <div class="d-flex">
                            <div style="min-width: 40px;"><i class="fas fa-check-circle text-success fs-4"></i></div>
                            <div><strong>Expert support for passport & visa processes</strong> — hassle-free documentation.</div>
                        </div>
                        <div class="d-flex">
                            <div style="min-width: 40px;"><i class="fas fa-check-circle text-success fs-4"></i></div>
                            <div><strong>Dedicated customer service</strong> for a hassle-free experience.</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="contact-card">
                        <h3 class="mb-4" style="color: var(--primary-color);"><i class="fas fa-address-card me-2"></i>Connect With Us</h3>
                        
                        <div class="contact-detail-item">
                            <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div class="contact-text">
                                <h5>Address</h5>
                                <p>214, Oberon, Opp. Mercedes-Benz Showroom,<br>New City Light Road, Surat – 395017</p>
                            </div>
                        </div>

                        <div class="contact-detail-item">
                            <div class="contact-icon"><i class="fas fa-phone-alt"></i></div>
                            <div class="contact-text">
                                <h5>Phone</h5>
                                <p><a href="tel:+919033186905" style="color: var(--text-color); text-decoration: none;">+91-90331 86905</a></p>
                            </div>
                        </div>

                        <div class="contact-detail-item">
                            <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                            <div class="contact-text">
                                <h5>Email</h5>
                                <p><a href="mailto:travelcation.co.in" style="color: var(--text-color); text-decoration: none;">travelcation.co.in</a></p>
                            </div>
                        </div>

                        <div class="contact-detail-item">
                            <div class="contact-icon"><i class="fas fa-globe"></i></div>
                            <div class="contact-text">
                                <h5>Website</h5>
                                <p><a href="https://travelcation.co.in" target="_blank" style="color: var(--text-color); text-decoration: none;">travelcation.co.in</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quote / Tagline -->
    <section style="background: var(--accent-color); color: white; padding: 3rem 0; text-align: center;">
        <div class="container">
            <i class="fas fa-quote-left fa-3x mb-3" style="opacity: 0.5;"></i>
            <h3 style="font-family: 'Playfair Display', serif; font-size: 2rem;">We don't just plan trips — we manage your entire travel journey from start to finish.</h3>
            <p class="mt-3 fs-4">Let us turn your travel dreams into reality ✈️🌍</p>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section py-5" style="background: var(--light-color);">
        <div class="container">
            <div class="text-center mb-4">
                <h2 class="section-title d-inline-block"><i class="fas fa-map-marked-alt"></i> Find Us Here</h2>
                <p class="lead mt-3 text-muted">214, Oberon, Opp. Mercedes-Benz Showroom, New City Light Road, Surat</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="map-container" style="position: relative; overflow: hidden; border-radius: 20px; box-shadow: var(--shadow-xl); background: white; padding: 10px;">
                        <div style="position: relative; width: 100%; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 12px;">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3536.986712233326!2d72.79851049999999!3d21.1482659!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be0535482a6d31d%3A0x168767862d155ac5!2sOberon%20Business%20Complex!5e1!3m2!1sen!2sin!4v1771697007199!5m2!1sen!2sin"
                                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;"
                                allowfullscreen=""
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                title="Travelcation Office at Oberon Business Complex, Surat">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-4">
                <div class="col-md-4">
                    <div class="text-center p-3 h-100" style="background: white; border-radius: 15px; box-shadow: var(--shadow-md);">
                        <div class="icon-circle mx-auto mb-3" style="width: 60px; height: 60px; background: rgba(242,140,40,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-map-pin" style="font-size: 1.5rem; color: var(--secondary-color);"></i>
                        </div>
                        <h5 style="color: var(--primary-color); font-weight: 600;">Exact Location</h5>
                        <p class="mb-0 text-muted" style="font-size: 0.95rem;">214, Oberon Business Complex<br>Opp. Mercedes-Benz Showroom</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3 h-100" style="background: white; border-radius: 15px; box-shadow: var(--shadow-md);">
                        <div class="icon-circle mx-auto mb-3" style="width: 60px; height: 60px; background: rgba(242,140,40,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-road" style="font-size: 1.5rem; color: var(--secondary-color);"></i>
                        </div>
                        <h5 style="color: var(--primary-color); font-weight: 600;">Landmark</h5>
                        <p class="mb-0 text-muted">New City Light Road<br>Near VIP Circle, Surat</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3 h-100" style="background: white; border-radius: 15px; box-shadow: var(--shadow-md);">
                        <div class="icon-circle mx-auto mb-3" style="width: 60px; height: 60px; background: rgba(242,140,40,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-clock" style="font-size: 1.5rem; color: var(--secondary-color);"></i>
                        </div>
                        <h5 style="color: var(--primary-color); font-weight: 600;">Office Hours</h5>
                        <p class="mb-0 text-muted">Mon-Sat: 10:00 AM – 7:00 PM<br>Sunday: Closed</p>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5">
                <a href="https://www.google.com/maps/dir/?api=1&destination=Oberon+Business+Complex+Surat" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-lg me-3 mb-2">
                    <i class="fas fa-directions me-2"></i>Get Directions
                </a>
                <a href="contact.php" class="btn btn-outline btn-lg mb-2">
                    <i class="fas fa-phone-alt me-2"></i>Contact Us
                </a>
            </div>
        </div>
    </section>

   
      <!-- Footer -->
      <footer id="contact">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <h3 class="footer-title">Travelcation</h3>
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
                        <li><a href="home.php"><i class="fas fa-chevron-right"></i> Home</a></li>
                        <li><a href="aboutus.php"><i class="fas fa-chevron-right"></i> About Us</a></li>
                        <li><a href="home.php#packages"><i class="fas fa-chevron-right"></i> Packages</a></li>
                        <li><a href="home.php#hotels"><i class="fas fa-chevron-right"></i> Hotels</a></li>
                        <li><a href="home.php#contact"><i class="fas fa-chevron-right"></i> Contact</a></li>
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
                <p>&copy; 2026 Travelcation. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Enquiry Modal with PHP Form Handling -->
    <div class="modal fade enquiry-modal" id="enquiryModal" tabindex="-1" aria-labelledby="enquiryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="enquiryModalLabel"><i class="fas fa-paper-plane me-2"></i> Travel Enquiry Form</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    
                    <!-- Success Message -->
                    <?php if ($modal_enquiry_success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= htmlspecialchars($modal_enquiry_message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Error Message -->
                    <?php if (!$modal_enquiry_success && $modal_enquiry_error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= $modal_enquiry_error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Enquiry Form -->
                    <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" id="enquiryForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="modal_full_name" class="form-label">Full Name *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="modal_full_name" 
                                       name="modal_full_name" 
                                       value="<?= htmlspecialchars($_POST['modal_full_name'] ?? '') ?>"
                                       required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="modal_email" class="form-label">Email Address *</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="modal_email" 
                                       name="modal_email" 
                                       value="<?= htmlspecialchars($_POST['modal_email'] ?? '') ?>"
                                       required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="modal_phone" class="form-label">Phone Number *</label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="modal_phone" 
                                       name="modal_phone" 
                                       value="<?= htmlspecialchars($_POST['modal_phone'] ?? '') ?>"
                                       placeholder="e.g., 9033186905"
                                       required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="modal_package" class="form-label">Package Interested In</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="modal_package" 
                                       name="modal_package" 
                                       value="<?= htmlspecialchars($_POST['modal_package'] ?? '') ?>"
                                       placeholder="Enter package name (optional)">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="modal_travel_date" class="form-label">Travel Date *</label>
                                <input type="date" 
                                       class="form-control" 
                                       id="modal_travel_date" 
                                       name="modal_travel_date" 
                                       value="<?= htmlspecialchars($_POST['modal_travel_date'] ?? '') ?>"
                                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>" 
                                       required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="modal_travelers" class="form-label">Number of Travelers *</label>
                                <select class="form-select" id="modal_travelers" name="modal_travelers" required>
                                    <option value="">Select</option>
                                    <option value="1" <?= (($_POST['modal_travelers'] ?? '') == '1') ? 'selected' : '' ?>>1 Traveler</option>
                                    <option value="2" <?= (($_POST['modal_travelers'] ?? '') == '2') ? 'selected' : '' ?>>2 Travelers</option>
                                    <option value="3" <?= (($_POST['modal_travelers'] ?? '') == '3') ? 'selected' : '' ?>>3 Travelers</option>
                                    <option value="4" <?= (($_POST['modal_travelers'] ?? '') == '4') ? 'selected' : '' ?>>4 Travelers</option>
                                    <option value="5+" <?= (($_POST['modal_travelers'] ?? '') == '5+') ? 'selected' : '' ?>>5+ Travelers</option>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <label for="modal_message" class="form-label">Additional Requirements</label>
                                <textarea class="form-control" 
                                          id="modal_message" 
                                          name="modal_message" 
                                          rows="4" 
                                          placeholder="Please share any specific requirements or questions..."><?= htmlspecialchars($_POST['modal_message'] ?? '') ?></textarea>
                            </div>
                        </div>
                        
                        <input type="hidden" name="source" value="aboutus">
                        
                        <div class="d-grid mt-4">
                            <button type="submit" name="submit_modal_enquiry" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Submit Enquiry
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Top CTA hide/show
        const cta = document.getElementById("topCta");
        const navbar = document.querySelector(".navbar");
        const headerHeight = document.querySelector('.page-header')?.offsetHeight || 300;
        
        window.addEventListener("scroll", () => {
            if (window.scrollY > headerHeight - 100) {
                cta.classList.add("hidden");
                navbar.classList.add("sticky");
            } else {
                cta.classList.remove("hidden");
                navbar.classList.remove("sticky");
            }
        });

        // Page transitions
        document.addEventListener("DOMContentLoaded", () => {
            document.body.classList.add("page-loaded");
            
            document.querySelectorAll("a[href]").forEach(link => {
                const url = link.getAttribute("href");
                if (!url || url.startsWith("#") || url.startsWith("javascript") || link.target === "_blank") return;
                
                link.addEventListener("click", function(e) {
                    e.preventDefault();
                    document.body.classList.remove("page-loaded");
                    document.body.classList.add("page-exit");
                    
                    setTimeout(() => { 
                        window.location.href = url; 
                    }, 300);
                });
            });
        });

        window.addEventListener("pageshow", () => {
            document.body.classList.remove("page-exit");
            document.body.classList.add("page-loaded");
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Map touch handling for mobile
        document.addEventListener('DOMContentLoaded', function() {
            const mapContainer = document.querySelector('.map-container');
            const mapIframe = document.querySelector('.map-container iframe');
            
            if (mapContainer && mapIframe) {
                let touchStartY;
                
                mapContainer.addEventListener('touchstart', function(e) {
                    touchStartY = e.touches[0].clientY;
                }, { passive: true });
                
                mapContainer.addEventListener('touchmove', function(e) {
                    if (!touchStartY) return;
                    
                    const touchY = e.touches[0].clientY;
                    const diffY = Math.abs(touchY - touchStartY);
                    
                    if (diffY > 10) {
                        mapIframe.style.pointerEvents = 'none';
                    }
                }, { passive: true });
                
                mapContainer.addEventListener('touchend', function() {
                    setTimeout(() => {
                        mapIframe.style.pointerEvents = 'auto';
                    }, 100);
                    touchStartY = null;
                });
                
                mapContainer.addEventListener('dblclick', function() {
                    mapIframe.style.pointerEvents = 'auto';
                });
            }
        });

        // Show modal if there were errors (to keep form visible)
        <?php if (!$modal_enquiry_success && $modal_enquiry_error): ?>
        document.addEventListener('DOMContentLoaded', function() {
            var enquiryModal = new bootstrap.Modal(document.getElementById('enquiryModal'));
            enquiryModal.show();
        });
        <?php endif; ?>
    </script>
     <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>