<?php
require_once __DIR__ . '/config/config.php';

/* ============================
   1. GET DESTINATION BY SLUG
============================ */
$slug = $_GET['slug'] ?? '';

if (!$slug) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT * FROM popular_destinations 
    WHERE slug = ? AND status = 'Active'
");
$stmt->execute([$slug]);
$destination = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$destination) {
    header("Location: index.php");
    exit;
}

$destination_id = $destination['id'];

/* ============================
   2. FILTERS
============================ */
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$duration = $_GET['duration'] ?? '';

$where = " WHERE destination_id = ? AND status='Active' ";
$params = [$destination_id];

if ($minPrice !== '') {
    $where .= " AND price >= ? ";
    $params[] = $minPrice;
}

if ($maxPrice !== '') {
    $where .= " AND price <= ? ";
    $params[] = $maxPrice;
}

if ($duration !== '') {
    $where .= " AND duration LIKE ? ";
    $params[] = "%$duration%";
}

/* ============================
   3. PAGINATION
============================ */
$limit = 8;
$page = $_GET['page'] ?? 1;
$page = max(1, (int)$page);
$offset = ($page - 1) * $limit;

/* ============================
   4. FETCH PACKAGES
============================ */
$packageStmt = $pdo->prepare("
    SELECT SQL_CALC_FOUND_ROWS * 
    FROM tour_packages
    $where
    ORDER BY price ASC
    LIMIT $limit OFFSET $offset
");
$packageStmt->execute($params);
$packages = $packageStmt->fetchAll();

$totalPackages = $pdo->query("SELECT FOUND_ROWS()")->fetchColumn();
$totalPages = ceil($totalPackages / $limit);

/* ============================
   5. FETCH HOTELS
============================ */
$hotelStmt = $pdo->prepare("
    SELECT * FROM hotels
    WHERE destination_id = ? AND status='Active'
    ORDER BY price_per_night ASC
");
$hotelStmt->execute([$destination_id]);
$hotels = $hotelStmt->fetchAll();

/* ============================
   6. FETCH OTHER DESTINATIONS
============================ */
$otherDestStmt = $pdo->prepare("
    SELECT title, slug, image 
    FROM popular_destinations 
    WHERE status = 'Active' AND slug != ?
    ORDER BY RAND()
    LIMIT 6
");
$otherDestStmt->execute([$slug]);
$otherDestinations = $otherDestStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($destination['title']) ?> Travelcation</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" sizes="32x32" href="uploads/lg-tra (1).png">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&family=Source+Sans+Pro:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
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
            --shadow-xl: 0 20px 25px rgba(0,0,0,0.1);
            --navbar-height: 72px;
        }

        @media (max-width: 768px) {
            :root {
                --navbar-height: 64px;
            }
        }

        @media (max-width: 480px) {
            :root {
                --navbar-height: 60px;
            }
        }

        /* ================= BODY ================= */
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--text-color);
            line-height: 1.6;
            font-weight: 400;
            overflow-x: hidden;
        }
/* Page transition */
body {
    opacity: 0;
    transition: opacity 0.4s ease-in-out;
}

body.page-loaded {
    opacity: 1;
}

body.page-exit {
    opacity: 0;
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

        #enquiryModal .modal-dialog {
    margin-top: 10%; /* adjust to your navbar height */
}
@media (max-width: 576px) {
    #enquiryModal .modal-dialog {
        margin: 0;
        height: 100%;
        max-width: 100%;
        margin-top:46%;
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
:root {
    --cta-height: 40px;
    --navbar-height: 72px;
}

        /* ================= HERO SECTION ================= */
        .destination-hero {
            position: relative;
            min-height: 90vh;
            padding-top: var(--navbar-height);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .destination-hero-bg {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--dark-color) 100%);
        }

        .destination-hero-image {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            opacity: 0.6;
        }

        .destination-hero-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 1;
        }

        .destination-hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            width: 100%;
            padding: 2rem;
            margin: 0 auto;
            text-align: center;
            color: var(--white);
        }

        .destination-hero-title {
          font-family: Inter, sans-serif;
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .destination-hero-subtitle {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            line-height: 1.8;
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

        /* ================= FILTERS ================= */
        .filters-section {
            background: var(--light-color);
            padding: 2rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        .filter-card {
            background: var(--white);
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }

        .form-control, .form-select {
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(0, 166, 200, 0.1);
        }

        /* ================= PACKAGE CARDS ================= */
        .package-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            box-shadow: var(--shadow-md);
            background: var(--white);
        }
        
        .package-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }
        
        .card-img-container {
            position: relative;
            overflow: hidden;
            height: 240px;
        }
        
        .card-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .package-card:hover .card-img-container img {
            transform: scale(1.05);
        }
        
        .package-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--accent-color);
            color: var(--white);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .package-price {
            position: absolute;
            bottom: 15px;
            left: 15px;
            background: rgba(255, 255, 255, 0.95);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--accent-color);
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .card-title {
          font-family: Inter, sans-serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.75rem;
        }
        
        .card-text {
            color: var(--text-light);
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }
        
        .features-list {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }
        
        .features-list li {
            padding: 0.25rem 0;
            color: var(--text-color);
            font-size: 0.9rem;
        }
        
        .features-list li i {
            color: var(--success-color);
            margin-right: 0.5rem;
            font-size: 0.875rem;
        }

        /* ================= HOTEL CARDS ================= */
        .hotel-card {
            border: 1px solid var(--border-color);
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .hotel-card:hover {
            border-color: var(--accent-color);
            box-shadow: var(--shadow-lg);
        }
        
        .hotel-rating {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--white);
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-weight: 600;
            color: var(--secondary-color);
            box-shadow: var(--shadow-sm);
        }

        /* ================= OTHER DESTINATIONS ================= */
        .other-destinations {
            background: var(--light-color);
        }

        .destination-img {
            height: 220px;
            border-radius: 14px;
            background-size: cover;
            background-position: center;
            position: relative;
            overflow: hidden;
            transition: transform 0.4s ease;
        }

        .destination-img:hover {
            transform: translateY(-8px);
        }

        .destination-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.6), transparent);
            display: flex;
            align-items: flex-end;
            padding: 15px;
        }

        .destination-overlay h5 {
            color: var(--white);
            font-weight: 700;
            margin: 0;
            text-shadow: 0 3px 8px rgba(0,0,0,0.6);
        }

        /* ================= PAGINATION ================= */
        .pagination .page-item .page-link {
            color: var(--primary-color);
            border: 1px solid var(--border-color);
            margin: 0 2px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .pagination .page-item.active .page-link {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
            color: var(--white);
        }

        .pagination .page-item .page-link:hover {
            background: var(--light-color);
            border-color: var(--accent-color);
        }

        /* ================= SECTIONS ================= */
        .section-title {
          font-family: Inter, sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1rem;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: -10px;
            width: 60px;
            height: 3px;
            background: var(--secondary-color);
        }

        .section-subtitle {
            color: var(--text-light);
            font-size: 1.1rem;
            margin-bottom: 3rem;
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
            background: rgba(255, 255, 255, 0.1);
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
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 3rem;
            padding-top: 1.5rem;
            text-align: center;
            color: #A0AEC0;
            font-size: 0.875rem;
        }

        /* ================= MODAL ================= */
        .modal-header {
            background: var(--primary-color);
            color: var(--white);
            border-bottom: none;
            padding: 1.5rem;
        }
        
        .modal-title {
          font-family: inter, sans-serif;
            font-weight: 600;
        }
        
        .modal-body {
            padding: 2rem;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        /* ================= RESPONSIVE ================= */
        @media (max-width: 576px) {
    .section-title::after {
        left: 50%;
        transform: translateX(-50%);
    }
}


       


@media (max-width: 768px) {
            .destination-hero-title {
                font-size: 2.5rem;
            }
            
            .destination-hero-subtitle {
                font-size: 1.1rem;
            }
            
            .destination-hero {
                min-height: 50vh;
                margin-top:40%;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            
        }
        
        @media (max-width: 576px) {
            .destination-hero-title {
                font-size: 2rem;
            }
            
            .destination-hero-subtitle {
                font-size: 1rem;
            }
            
            .btn-primary {
                padding: 0.75rem 1.5rem;
                width: 100%;
            }
            
            .section-title {
                font-size: 1.75rem;
            }
        }

        /* ================= UTILITY CLASSES ================= */
        .text-gradient {
            background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .bg-gradient {
            background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
        }

        .rounded-lg {
            border-radius: 12px;
        }

        .shadow-card {
            box-shadow: var(--shadow-md);
        }

        .shadow-card:hover {
            box-shadow: var(--shadow-xl);
        }

        /* ================= ANIMATIONS ================= */
        .fade-in {
            animation: fadeIn 0.8s ease-in-out;
        }
        
        .slide-up {
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
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
    const triggerPoint = document.querySelector(".hero-section").offsetHeight - 100;

    window.addEventListener("scroll", () => {
        if (window.scrollY > triggerPoint) {
            cta.classList.add("hidden");
            navbar.classList.add("sticky");
        } else {
            cta.classList.remove("hidden");
            navbar.classList.remove("sticky");
        }
    });
    
</script>
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
                          <a class="nav-link " href="home.php">Home</a>
                      </li>
                      <li class="nav-item">
                          <a class="nav-link" href="aboutus.php">About</a>
                      </li>
                      <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink123" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Explore
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink123">
                        <a class="dropdown-item" href="home.php#packages">Packages</a>
                        <a class="dropdown-item" href="home.php#hotels">Hotels</a>
                        <a class="dropdown-item" href="offers.php">Exclusive Offers</a>
                        <a class="dropdown-item active" href="alldestinations.php">Destinations</a>
                        </div>
                    </li>
                      
                      <li class="nav-item">
                          <a class="nav-link" href="home.php#contact-section">Contact</a>
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

    <!-- =========================
         HERO SECTION
    ========================= -->
    <section class="destination-hero">
        <div class="destination-hero-bg"></div>
        <div class="destination-hero-image" style="background-image: url('uploads/<?= htmlspecialchars($destination['image']) ?>');"></div>
        <div class="destination-hero-overlay"></div>
        
        <div class="container">
            <div class="destination-hero-content fade-in">
                <h1 class="destination-hero-title"><?= htmlspecialchars($destination['title']) ?></h1>
                <p class="destination-hero-subtitle">Discover amazing tour packages and luxurious hotels in this beautiful destination</p>
                <div class="d-flex flex-column flex-md-row gap-3 align-items-center justify-content-center">
                    <a href="#packages" class="btn btn-primary btn-lg">
                        <i class="fas fa-suitcase-rolling me-2"></i>View Packages
                    </a>
                    <a href="#hotels" class="btn btn-outline btn-lg">
                        <i class="fas fa-hotel me-2"></i>View Hotels
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================
         FILTERS SECTION
    ========================= -->
    <section class="filters-section">
        <div class="container">
            <div class="filter-card">
                <form method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="slug" value="<?= htmlspecialchars($slug) ?>">

                    <div class="col-md-3">
                        <label class="form-label">Min Price (₹)</label>
                        <input type="number" name="min_price" class="form-control"
                               placeholder="Min Price" value="<?= htmlspecialchars($minPrice) ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Max Price (₹)</label>
                        <input type="number" name="max_price" class="form-control"
                               placeholder="Max Price" value="<?= htmlspecialchars($maxPrice) ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Duration</label>
                        <select name="duration" class="form-control">
                            <option value="">Any Duration</option>
                            <option value="3" <?= $duration == '3' ? 'selected' : '' ?>>3 Days</option>
                            <option value="5" <?= $duration == '5' ? 'selected' : '' ?>>5 Days</option>
                            <option value="7" <?= $duration == '7' ? 'selected' : '' ?>>7 Days</option>
                            <option value="10" <?= $duration == '10' ? 'selected' : '' ?>>10+ Days</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <button class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i>Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- =========================
         PACKAGES SECTION
    ========================= -->
    <section class="py-5" id="packages">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title text-center">Tour Packages in <?= htmlspecialchars($destination['title']) ?></h2>
                <p class="section-subtitle text-center">Choose from our carefully curated selection of tour packages</p>
            </div>

            <?php if (empty($packages)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-suitcase fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No packages found</h4>
                    <p class="text-muted">Try adjusting your filters or check back later for new packages.</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($packages as $p):
                        $features = !empty($p['features'])
                            ? array_map('trim', explode(',', $p['features']))
                            : [];
                        ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="package-card slide-up">
                            <div class="card-img-container">
                                <img src="uploads/<?= htmlspecialchars($p['image']) ?>" 
                                     alt="<?= htmlspecialchars($p['title']) ?>"
                                     class="img-fluid">
                                <span class="package-badge"><?= htmlspecialchars($p['package_type']) ?></span>
                                <div class="package-price">₹<?= number_format($p['price']) ?></div>
                            </div>
                            <div class="card-body">
                                <h3 class="card-title"><?= htmlspecialchars($p['title']) ?></h3>
                                <p class="card-text"><?= htmlspecialchars(substr($p['description'], 0, 100)) ?>...</p>
                                
                                <?php if (!empty($features)): ?>
                                    <ul class="features-list">
                                        <?php foreach (array_slice($features, 0, 3) as $feature): ?>
                                            <li><i class="fas fa-check-circle"></i> <?= htmlspecialchars($feature, ENT_QUOTES, 'UTF-8') ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="text-muted"><i class="fas fa-clock me-1"></i> <?= htmlspecialchars($p['duration']) ?></span>
                                    <button class="btn btn-primary enquiry-btn" 
                                            data-package-id="<?= $p['id'] ?>"
                                            data-package-title="<?= htmlspecialchars($p['title']) ?>">
                                        Enquire Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- =========================
                     PAGINATION
                ========================= -->
                <?php if ($totalPages > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link"
                                   href="?slug=<?= $slug ?>&page=<?= $i ?>&min_price=<?= $minPrice ?>&max_price=<?= $maxPrice ?>&duration=<?= $duration ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- =========================
         HOTELS SECTION
    ========================= -->
    <section class="py-5 bg-light" id="hotels">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title text-center">Hotels in <?= htmlspecialchars($destination['title']) ?></h2>
                <p class="section-subtitle text-center">Luxurious accommodations for your perfect stay</p>
            </div>

            <?php if (empty($hotels)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-hotel fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No hotels found</h4>
                    <p class="text-muted">Check back later for hotel listings in this destination.</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($hotels as $h):
                        $features = !empty($h['features'])
                            ? array_map('trim', explode(',', $h['features']))
                            : [];
                        ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="hotel-card slide-up">
                            <div class="card-img-container">
                                <img src="uploads/<?= htmlspecialchars($h['image']) ?>" 
                                     alt="<?= htmlspecialchars($h['hotel_name']) ?>"
                                     class="img-fluid">
                                <div class="hotel-rating">
                                    <i class="fas fa-star"></i> <?= $h['rating'] ?? '4.5' ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <h3 class="card-title"><?= htmlspecialchars($h['hotel_name']) ?></h3>
                                <p class="card-text"><?= htmlspecialchars(substr($h['description'], 0, 100)) ?>...</p>
                                
                                <?php if (!empty($features)): ?>
                                    <ul class="features-list">
                                        <?php foreach (array_slice($features, 0, 3) as $feature): ?>
                                            <li><i class="fas fa-check"></i> <?= htmlspecialchars($feature, ENT_QUOTES, 'UTF-8') ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        <span class="fw-bold fs-5" style="color: var(--accent-color);">₹<?= number_format($h['price_per_night']) ?></span>
                                        <span class="text-muted">/night</span>
                                    </div>
                                    <button class="btn btn-primary book-hotel-btn"
                                            data-hotel-name="<?= htmlspecialchars($h['hotel_name']) ?>">
                                        Book Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- =========================
         OTHER DESTINATIONS
    ========================= -->
    <?php if (!empty($otherDestinations)): ?>
    <section class="py-5 other-destinations">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title text-center">Explore Other Destinations</h2>
                <p class="section-subtitle text-center">Discover more amazing places to visit</p>
            </div>

            <div class="row justify-content-center g-4">
                <?php foreach ($otherDestinations as $d): ?>
                <div class="col-lg-2 col-md-4 col-6">
                    <a href="destination.php?slug=<?= urlencode($d['slug']) ?>" class="destination-card">
                        <div class="destination-img"
                             style="background-image:url('uploads/<?= htmlspecialchars($d['image']) ?>');">
                            <div class="destination-overlay">
                                <h5><?= htmlspecialchars($d['title']) ?></h5>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

  
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
                        <li><i class="fas fa-envelope"></i>info@travelcation.co.in</li>
                        <li><i class="fas fa-clock"></i> Mon-Sat: 11:00 AM - 8:00 PM</li>
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

    <!-- =========================
         ENQUIRY MODAL
    ========================= -->
    <div class="modal fade enquiry-modal" id="enquiryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Travel Enquiry Form</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="enquiryForm">
                        <input type="hidden" name="destination" value="<?= htmlspecialchars($destination['title']) ?>">
                        <input type="hidden" name="item" id="enquiryItem">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="fullName" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="fullName" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="phone" required>
                            </div>
                            <div class="col-md-6">
                                <label for="travelDate" class="form-label">Travel Date *</label>
                                <input type="date" class="form-control" id="travelDate" required>
                            </div>
                            <div class="col-md-6">
                                <label for="travelers" class="form-label">Number of Travelers *</label>
                                <select class="form-select" id="travelers" required>
                                    <option value="1">1 Traveler</option>
                                    <option value="2" selected>2 Travelers</option>
                                    <option value="3">3 Travelers</option>
                                    <option value="4">4 Travelers</option>
                                    <option value="5+">5+ Travelers</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="budget" class="form-label">Approx. Budget</label>
                                <select class="form-select" id="budget">
                                    <option value="">Select Budget</option>
                                    <option value="Under 50k">Under ₹50,000</option>
                                    <option value="50k-1L">₹50,000 - ₹1,00,000</option>
                                    <option value="1L-2L">₹1,00,000 - ₹2,00,000</option>
                                    <option value="Above 2L">Above ₹2,00,000</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="message" class="form-label">Additional Requirements</label>
                                <textarea class="form-control" id="message" rows="4" placeholder="Please share any specific requirements or questions..."></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="newsletter">
                                    <label class="form-check-label" for="newsletter">
                                        Subscribe to our newsletter for exclusive offers
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Submit Enquiry
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Navbar scroll effect
            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.navbar');
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });

            // Set minimum date for travel date
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('travelDate').min = today;
            
            // Handle enquiry button clicks
            const packageInput = document.getElementById('enquiryItem');
            
            document.querySelectorAll('.enquiry-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const packageTitle = this.getAttribute('data-package-title');
                    packageInput.value = packageTitle;
                    
                    const modal = new bootstrap.Modal(document.getElementById('enquiryModal'));
                    modal.show();
                });
            });
            
            // Handle hotel booking button clicks
            document.querySelectorAll('.book-hotel-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const hotelName = this.getAttribute('data-hotel-name');
                    packageInput.value = 'Hotel Booking - ' + hotelName;
                    
                    const modal = new bootstrap.Modal(document.getElementById('enquiryModal'));
                    modal.show();
                });
            });
            
            // Form submission
            const enquiryForm = document.getElementById('enquiryForm');
            enquiryForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Form validation
                const formData = {
                    name: document.getElementById('fullName').value.trim(),
                    email: document.getElementById('email').value.trim(),
                    phone: document.getElementById('phone').value.trim(),
                    item: packageInput.value.trim(),
                    travelDate: document.getElementById('travelDate').value,
                    travelers: document.getElementById('travelers').value,
                    budget: document.getElementById('budget').value,
                    message: document.getElementById('message').value.trim(),
                    newsletter: document.getElementById('newsletter').checked
                };
                
                // Basic validation
                if (!formData.name || !formData.email || !formData.phone || !formData.travelDate) {
                    alert('Please fill in all required fields.');
                    return;
                }
                
                // Email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(formData.email)) {
                    alert('Please enter a valid email address.');
                    return;
                }
                
                // Show loading state
                const submitBtn = enquiryForm.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
                submitBtn.disabled = true;
                
                // Simulate API call (replace with actual API call)
                setTimeout(() => {
                    // Reset button
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    // Show success message
                    alert('Thank you for your enquiry! Our travel consultant will contact you within 24 hours.');
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('enquiryModal'));
                    modal.hide();
                    
                    // Reset form
                    enquiryForm.reset();
                    packageInput.value = '';
                }, 1500);
            });

            // Add smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href === '#') return;
                    
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        window.scrollTo({
                            top: target.offsetTop - 100,
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Add animation on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('fade-in');
                    }
                });
            }, observerOptions);

            // Observe elements for animation
            document.querySelectorAll('.package-card, .hotel-card').forEach(card => {
                observer.observe(card);
            });
        });
    </script>
    <script>
document.addEventListener("DOMContentLoaded", () => {
    document.body.classList.add("page-loaded");

    document.querySelectorAll("a[href]").forEach(link => {
        const url = link.getAttribute("href");

        // Skip anchors, JS links, new tabs
        if (
            !url ||
            url.startsWith("#") ||
            url.startsWith("javascript") ||
            link.target === "_blank"
        ) return;

        link.addEventListener("click", function (e) {
            e.preventDefault();
            document.body.classList.remove("page-loaded");
            document.body.classList.add("page-exit");

            setTimeout(() => {
                window.location.href = url;
            }, 100);
        });
    });
});
</script>
<script>
  window.addEventListener("pageshow", function (event) {
    // Fix for back/forward cache
    document.body.classList.remove("page-exit");
    document.body.classList.add("page-loaded");
});
</script>
 <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>
