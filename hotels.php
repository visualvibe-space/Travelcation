<?php
require_once __DIR__ . '/config/config.php';

/* ========================
   FETCH ALL HOTELS
======================== */
$stmt = $pdo->query("
    SELECT * FROM hotels 
    WHERE status = 'Active' 
    ORDER BY id DESC
");
$hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get hero carousel images (optional)
$carousel_images = $pdo->query("SELECT * FROM hero_carousel WHERE is_active = 1 ORDER BY display_order, created_at ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Hotels | Travelcation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Browse our premium hotel collection - luxury accommodations for your perfect stay">
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css">
    <link rel="icon" type="image/png" sizes="32x32" href="uploads/lg-tra (1).png">
    
    <!-- Google Fonts -->
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
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--text-color);
            line-height: 1.6;
            background: var(--white);
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

        /* Top CTA Bar */
        .top-cta {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 40px;
            background: linear-gradient(90deg, var(--accent-color), #0087A3);
            color: var(--white);
            z-index: 10000;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-sm);
        }

        .top-cta.hidden {
            transform: translateY(-100%);
        }

        .top-cta .btn-outline-light {
            border-width: 2px;
            font-weight: 600;
            padding: 0.25rem 1rem;
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
            padding: 8px 20px;
        }
        .navbar {
            position: fixed;
            top: 40px;
            width: 100%;
            transition: top 0.4s ease;
        }
        .navbar.sticky {
            top: 0;
        }

        .navbar a {
            padding: 6px 12px;
            line-height: 1.2;
            font-size: 17px;
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

        .btn-outline-primary {
            border: 2px solid var(--secondary-color);
            color: var(--secondary-color);
            background: transparent;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background: var(--secondary-color);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(242, 140, 40, 0.3);
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
            padding: 120px 0 60px;
            margin-top: 40px;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('uploads/hotelbook.jpg') center/cover no-repeat;
            opacity: 0.1;
            z-index: 0;
        }

        .page-header .container {
            position: relative;
            z-index: 1;
        }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 1rem;
        }

        .page-breadcrumb {
            background: transparent;
            padding: 0;
        }

        .page-breadcrumb .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }

        .page-breadcrumb .breadcrumb-item a:hover {
            color: var(--secondary-color);
        }

        .page-breadcrumb .breadcrumb-item.active {
            color: var(--secondary-color);
        }

        .page-breadcrumb .breadcrumb-item+.breadcrumb-item::before {
            color: rgba(255, 255, 255, 0.5);
        }

        /* Filters Section */
        .filters-section {
            background: var(--white);
            padding: 2rem 0;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 110px;
            z-index: 100;
            box-shadow: var(--shadow-sm);
        }

        .filter-select {
            border: 2px solid var(--border-color);
            border-radius: 8px;
            padding: 0.6rem 1rem;
            font-size: 0.95rem;
            color: var(--text-color);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(0, 166, 200, 0.1);
            outline: none;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            border: 2px solid var(--border-color);
            border-radius: 8px;
            padding: 0.6rem 1rem 0.6rem 2.5rem;
            width: 100%;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(0, 166, 200, 0.1);
            outline: none;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }

        /* Hotels Grid */
        .hotels-grid {
            padding: 3rem 0 5rem;
            background: var(--light-color);
            min-height: 60vh;
        }

        .hotel-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            box-shadow: var(--shadow-md);
            background: var(--white);
            position: relative;
        }

        .hotel-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--accent-color), var(--secondary-color));
            z-index: 1;
        }

        .hotel-card:hover {
            transform: translateY(-12px) scale(1.02);
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
            transition: transform 0.6s ease;
        }

        .hotel-card:hover .card-img-container img {
            transform: scale(1.1);
        }

        .hotel-category-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: linear-gradient(135deg, var(--secondary-color), #f39c12);
            color: var(--white);
            padding: 0.4rem 1.2rem;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(242, 140, 40, 0.3);
            z-index: 2;
        }

        .hotel-rating-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--white);
            color: var(--secondary-color);
            padding: 0.4rem 1rem;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: var(--shadow-md);
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .hotel-rating-badge i {
            color: var(--secondary-color);
            font-size: 0.8rem;
        }

        .hotel-price {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.95);
            padding: 0.6rem 1.2rem;
            border-radius: 30px;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--accent-color);
            box-shadow: var(--shadow-sm);
            border: 2px solid rgba(0, 166, 200, 0.2);
            z-index: 2;
        }

        .hotel-price small {
            font-size: 0.8rem;
            font-weight: 400;
            color: var(--text-light);
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }

        .hotel-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .hotel-meta i {
            color: var(--accent-color);
            margin-right: 0.3rem;
        }

        .card-text {
            color: var(--text-light);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .feature-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin-bottom: 1.5rem;
        }

        .feature-badge {
            background: var(--light-color);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            color: var(--text-color);
            border: 1px solid var(--border-color);
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .feature-badge i {
            color: var(--success-color);
            font-size: 0.7rem;
        }

        .card-footer {
            background: transparent;
            border-top: 1px solid var(--border-color);
            padding: 1rem 1.5rem 1.5rem;
        }

        .btn-card {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: var(--white);
            border: none;
            padding: 0.6rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            width: 100%;
            text-align: center;
        }

        .btn-card:hover {
            background: linear-gradient(135deg, var(--accent-color), var(--primary-color));
            transform: translateY(-2px);
            color: var(--white);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--white);
            border-radius: 15px;
            box-shadow: var(--shadow-md);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--text-light);
            margin-bottom: 1.5rem;
        }

        .empty-state h3 {
            font-family: 'Playfair Display', serif;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: var(--text-light);
            margin-bottom: 2rem;
        }

        /* Pagination */
        .pagination {
            justify-content: center;
            margin-top: 2rem;
        }

        .page-item .page-link {
            border: 2px solid var(--border-color);
            color: var(--text-color);
            font-weight: 500;
            padding: 0.5rem 1rem;
            margin: 0 0.2rem;
            border-radius: 8px !important;
            transition: all 0.3s ease;
        }

        .page-item .page-link:hover {
            background: var(--accent-color);
            border-color: var(--accent-color);
            color: var(--white);
        }

        .page-item.active .page-link {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
            color: var(--white);
        }

        /* Footer */
        footer {
            background: linear-gradient(135deg, var(--dark-color), #0F172A);
            color: var(--white);
            padding: 4rem 0 2rem;
            margin-top: 0;
            position: relative;
        }

        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--secondary-color), var(--accent-color));
        }

        .footer-title {
            color: var(--white);
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
        }

        .footer-links a {
            color: #CBD5E0;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .footer-links a:hover {
            color: var(--secondary-color);
            padding-left: 0.3rem;
        }

        .contact-info {
            list-style: none;
            padding: 0;
        }

        .contact-info li {
            margin-bottom: 0.75rem;
            color: #CBD5E0;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }

        .contact-info i {
            color: var(--secondary-color);
            margin-right: 0.75rem;
            width: 20px;
        }

        .social-links {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.25rem;
        }

        .social-link {
            width: 36px;
            height: 36px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1rem;
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
            color: #94A3B8;
            font-size: 0.85rem;
        }

        /* Modal */
        .modal-header {
            background: linear-gradient(135deg, var(--accent-color), #0087A3);
            color: var(--white);
            border-bottom: none;
        }

        .modal-title {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .form-label {
            font-weight: 500;
            color: var(--primary-color);
            margin-bottom: 0.3rem;
            font-size: 0.9rem;
        }

        .form-control, .form-select {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.6rem 1rem;
            font-size: 0.95rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(0, 166, 200, 0.1);
        }

        #enquiryModal .modal-dialog {
            margin-top: 10%;
        }
        
        @media (max-width: 576px) {
            #enquiryModal .modal-dialog {
                margin: 0;
                height: 100%;
                max-width: 100%;
                margin-top: 45%;
            }
            
            #enquiryModal .modal-content {
                height: 100%;
                border-radius: 0;
            }
        }

        /* Back to Top Button */
        .btn-back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: none;
            z-index: 1000;
            box-shadow: var(--shadow-lg);
            align-items: center;
            justify-content: center;
            background: var(--secondary-color);
            border: none;
            color: white;
        }

        .btn-back-to-top:hover {
            background: var(--primary-color);
            transform: translateY(-3px);
        }

        /* Animations */
        .fade-in {
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: var(--white);
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header {
                padding: 100px 0 40px;
            }
            
            .page-title {
                font-size: 2.2rem;
            }
            
            .filters-section {
                position: relative;
                top: 0;
            }
            
            .filter-select, .search-box {
                margin-bottom: 1rem;
            }
            
            .hotel-price {
                font-size: 1rem;
                padding: 0.4rem 1rem;
            }
        }
        .page-header{
            padding-top:15%;
        }
        





        /* Video Section */
.video-section {
    position: relative;
    width: 100%;
    height: 90vh;
    overflow: hidden;
    margin: 3rem 0;
}

.bg-video {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transform: translate(-50%, -50%);
}

.video-overlay {
    position: relative;
    z-index: 2;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    color: white;
    background: rgba(0,0,0,0.4);
    padding: 20px;
}

.video-overlay h2 {
    font-size: 3rem;
    font-weight: bold;
    margin-bottom: 1rem;
}

.video-overlay p {
    font-size: 1.2rem;
    margin: 10px 0 20px;
}

.video-overlay .btn-warning {
    background: linear-gradient(135deg, var(--secondary-color), #f39c12);
    border: none;
    padding: 1rem 2.5rem;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 50px;
    color: white;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.video-overlay .btn-warning:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(242,140,40,0.4);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .video-section {
        height: 60vh;
    }

    .video-overlay h2 {
        font-size: 1.8rem;
    }

    .video-overlay p {
        font-size: 1rem;
    }
}

@media (max-width: 576px) {
    .video-section {
        height: 50vh;
    }
    
    .video-overlay .btn-warning {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }
}
    </style>
</head>

<body>

<!-- Top CTA Header -->
<div class="top-cta" id="topCta">
    <div class="container d-flex justify-content-between align-items-center">
        <span>
            <i class="fas fa-gift me-2"></i>Special Offer: Book Now & Get 20% Off!
        </span>
        <a href="tel:+919033186905" class="btn btn-sm btn-outline-light">
            <i class="fas fa-phone-alt me-1"></i> Call Now
        </a>
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
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="page-title animate__animated animate__fadeInDown">Premium Hotels</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb page-breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">All Hotels</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Filters Section -->
<section class="filters-section">
    <div class="container">
        <div class="row align-items-end">
            <div class="col-lg-4 col-md-6">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search hotels..." class="form-control">
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <select class="form-select filter-select" id="categoryFilter">
                    <option value="">All Categories</option>
                    <option value="Luxury">Luxury</option>
                    <option value="Beachfront">Beachfront</option>
                    <option value="Mountain">Mountain</option>
                    <option value="Boutique">Boutique</option>
                    <option value="Resort">Resort</option>
                    <option value="Business">Business</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <select class="form-select filter-select" id="sortFilter">
                    <option value="newest">Newest First</option>
                    <option value="price_low">Price: Low to High</option>
                    <option value="price_high">Price: High to Low</option>
                    <option value="name_asc">Name: A to Z</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <button class="btn btn-primary w-100" id="resetFilters">
                    <i class="fas fa-redo-alt me-2"></i>Reset
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Hotels Grid -->
<section class="hotels-grid">
    <div class="container">
        <?php if (empty($hotels)): ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="fas fa-hotel"></i>
                <h3>No Hotels Found</h3>
                <p>We couldn't find any hotels at the moment. Please check back later.</p>
                <a href="index.php" class="btn btn-primary">Return to Home</a>
            </div>
        <?php else: ?>
            <!-- Hotel Cards -->
            <div class="row g-4" id="hotelsContainer">
                <?php foreach ($hotels as $hotel): 
                    // Handle features
                    $features = [];
                    if (isset($hotel['features']) && !empty($hotel['features'])) {
                        $featuresRaw = $hotel['features'];
                        if (strpos($featuresRaw, '[') === 0) {
                            $features = json_decode($featuresRaw, true) ?? [];
                        } else {
                            $features = array_map('trim', explode(',', $featuresRaw));
                        }
                    }
                    $features = array_slice($features, 0, 3); // Show only first 3 features
                    
                    // Random rating for demo (you can add rating field to database if needed)
                    $rating = mt_rand(40, 50) / 10;
                    $reviews = mt_rand(50, 500);
                ?>
                    <div class="col-lg-4 col-md-6 hotel-item" 
                         data-category="<?= htmlspecialchars($hotel['category'] ?? '') ?>"
                         data-price="<?= $hotel['price_per_night'] ?>"
                         data-name="<?= htmlspecialchars($hotel['hotel_name']) ?>">
                        <div class="hotel-card">
                            <div class="card-img-container">
                                <img src="uploads/<?= htmlspecialchars($hotel['image']) ?>" 
                                     alt="<?= htmlspecialchars($hotel['hotel_name']) ?>">
                                
                                <?php if (isset($hotel['category']) && !empty($hotel['category'])): ?>
                                    <div class="hotel-category-badge">
                                        <?= htmlspecialchars($hotel['category']) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="hotel-rating-badge">
                                    <i class="fas fa-star"></i> <?= $rating ?>
                                </div>
                                
                                <div class="hotel-price">
                                    ₹<?= number_format($hotel['price_per_night']) ?> <small>/night</small>
                                </div>
                            </div>
                            <div class="card-body">
                                <h3 class="card-title"><?= htmlspecialchars($hotel['hotel_name']) ?></h3>
                                
                                <!-- <div class="hotel-meta">
                                    <span><i class="fas fa-map-marker-alt"></i> Premium Location</span>
                                    <span><i class="fas fa-star"></i> <?= $reviews ?> reviews</span>
                                </div> -->
                                
                                <p class="card-text">
                                    <?= htmlspecialchars(substr($hotel['description'], 0, 100)) ?>...
                                </p>
                                
                                <?php if (!empty($features)): ?>
                                    <div class="feature-badges">
                                        <?php foreach ($features as $feature): ?>
                                            <span class="feature-badge">
                                                <i class="fas fa-check-circle"></i>
                                                <?= htmlspecialchars($feature) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer">
                                <a href="hotel_details.php?id=<?= $hotel['id'] ?>" class="btn-card">
                                    <i class="fas fa-eye me-2"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <div class="row mt-5">
                <div class="col-12">
                    <nav aria-label="Page navigation">
                        <ul class="pagination" id="pagination">
                            <li class="page-item disabled" id="prevPage">
                                <a class="page-link" href="#" tabindex="-1">Previous</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item" id="nextPage">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
<!-- Video Section -->
<section class="video-section">
    <video autoplay muted loop playsinline class="bg-video">
        <source src="uploads/beach.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <div class="video-overlay">
        <h2>Experience Luxury Like Never Before</h2>
        <p>Discover our premium hotels with world-class amenities & breathtaking views</p>
        <a href="#details" class="btn-warning">Explore Rooms</a>
    </div>
</section>
<!-- Enquiry Modal -->
<div class="modal fade" id="enquiryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-paper-plane me-2"></i> Hotel Booking Enquiry</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="enquiryForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="fullName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone *</label>
                            <input type="tel" class="form-control" id="phone" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hotel Name</label>
                            <input type="text" class="form-control" id="hotelName" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Check-in Date *</label>
                            <input type="date" class="form-control" id="checkInDate" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Check-out Date *</label>
                            <input type="date" class="form-control" id="checkOutDate" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Guests *</label>
                            <select class="form-select" id="guests" required>
                                <option value="">Select</option>
                                <option value="1">1 Guest</option>
                                <option value="2">2 Guests</option>
                                <option value="3">3 Guests</option>
                                <option value="4">4 Guests</option>
                                <option value="5+">5+ Guests</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rooms *</label>
                            <select class="form-select" id="rooms" required>
                                <option value="">Select</option>
                                <option value="1">1 Room</option>
                                <option value="2">2 Rooms</option>
                                <option value="3">3 Rooms</option>
                                <option value="4">4+ Rooms</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Special Requests</label>
                            <textarea class="form-control" id="message" rows="3" placeholder="Any specific requirements..."></textarea>
                        </div>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary">Submit Enquiry</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


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

<!-- Back to Top Button -->
<button class="btn-back-to-top" id="backToTop">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    // Page transition
    document.addEventListener("DOMContentLoaded", () => {
        document.body.classList.add("page-loaded");
    });

    // CTA and Navbar scroll effect
    const cta = document.getElementById("topCta");
    const navbar = document.querySelector(".navbar");
    const header = document.querySelector(".page-header");
    
    window.addEventListener("scroll", () => {
        if (header) {
            const headerHeight = header.offsetHeight;
            const hidePoint = headerHeight * 0.3;
            
            if (window.scrollY > hidePoint) {
                cta.classList.add("hidden");
                navbar.classList.add("sticky");
            } else {
                cta.classList.remove("hidden");
                navbar.classList.remove("sticky");
            }
        }
    });

    // Back to Top
    const backToTop = document.getElementById('backToTop');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            backToTop.style.display = 'flex';
        } else {
            backToTop.style.display = 'none';
        }
    });

    backToTop.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Set min dates for check-in/check-out
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    const dayAfterTomorrow = new Date(today);
    dayAfterTomorrow.setDate(dayAfterTomorrow.getDate() + 2);
    
    const checkInInput = document.getElementById('checkInDate');
    const checkOutInput = document.getElementById('checkOutDate');
    
    if (checkInInput) {
        checkInInput.min = tomorrow.toISOString().split('T')[0];
        checkInInput.value = tomorrow.toISOString().split('T')[0];
    }
    
    if (checkOutInput) {
        checkOutInput.min = dayAfterTomorrow.toISOString().split('T')[0];
        checkOutInput.value = dayAfterTomorrow.toISOString().split('T')[0];
    }
    
    // Update check-out min date when check-in changes
    if (checkInInput && checkOutInput) {
        checkInInput.addEventListener('change', function() {
            const checkInDate = new Date(this.value);
            const minCheckOut = new Date(checkInDate);
            minCheckOut.setDate(minCheckOut.getDate() + 1);
            checkOutInput.min = minCheckOut.toISOString().split('T')[0];
            
            if (new Date(checkOutInput.value) <= checkInDate) {
                checkOutInput.value = minCheckOut.toISOString().split('T')[0];
            }
        });
    }

    // Filtering and Search functionality
    $(document).ready(function() {
        // Search input
        $("#searchInput").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $(".hotel-item").filter(function() {
                $(this).toggle($(this).find(".card-title").text().toLowerCase().indexOf(value) > -1)
            });
        });

        // Category filter
        $("#categoryFilter").on("change", function() {
            var category = $(this).val().toLowerCase();
            if (category === "") {
                $(".hotel-item").show();
            } else {
                $(".hotel-item").each(function() {
                    var hotelCategory = $(this).data("category") ? $(this).data("category").toLowerCase() : "";
                    $(this).toggle(hotelCategory === category);
                });
            }
        });

        // Sort filter
        $("#sortFilter").on("change", function() {
            var sortBy = $(this).val();
            var hotels = $(".hotel-item").get();
            
            hotels.sort(function(a, b) {
                if (sortBy === "price_low") {
                    return $(a).data("price") - $(b).data("price");
                } else if (sortBy === "price_high") {
                    return $(b).data("price") - $(a).data("price");
                } else if (sortBy === "name_asc") {
                    return $(a).data("name").localeCompare($(b).data("name"));
                } else {
                    // Default - newest first (by ID, assuming higher ID is newer)
                    return $(b).data("id") - $(a).data("id");
                }
            });
            
            $("#hotelsContainer").html(hotels);
        });

        // Reset filters
        $("#resetFilters").on("click", function() {
            $("#searchInput").val("");
            $("#categoryFilter").val("");
            $("#sortFilter").val("newest");
            $(".hotel-item").show();
        });
    });

    // Form submission
    document.getElementById('enquiryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Basic validation
        const required = ['fullName', 'email', 'phone', 'checkInDate', 'checkOutDate', 'guests', 'rooms'];
        let isValid = true;
        
        required.forEach(id => {
            const field = document.getElementById(id);
            if (!field.value) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            alert('Please fill all required fields');
            return;
        }
        
        // Email validation
        const email = document.getElementById('email').value;
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            alert('Please enter a valid email');
            return;
        }
        
        // Phone validation
        const phone = document.getElementById('phone').value.replace(/\D/g, '');
        if (phone.length < 10) {
            alert('Please enter a valid phone number');
            return;
        }
        
        // Show loading
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<div class="spinner"></div>';
        btn.disabled = true;
        
        // Simulate submission
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            
            alert('Thank you! Our team will contact you soon with hotel availability and best rates.');
            
            // Close modal and reset
            bootstrap.Modal.getInstance(document.getElementById('enquiryModal')).hide();
            this.reset();
            
            // Reset dates
            if (checkInInput) checkInInput.value = tomorrow.toISOString().split('T')[0];
            if (checkOutInput) checkOutInput.value = dayAfterTomorrow.toISOString().split('T')[0];
        }, 1500);
    });

    // Handle enquiry button clicks from hotel cards
    document.querySelectorAll('.btn-card').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const hotelName = $(this).closest('.hotel-card').find('.card-title').text();
            document.getElementById('hotelName').value = hotelName;
        });
    });
</script>

<!-- Page Transition for Links -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("a[href]").forEach(link => {
        const url = link.getAttribute("href");

        if (
            !url ||
            url.startsWith("#") ||
            url.startsWith("javascript") ||
            link.target === "_blank" ||
            url.includes("tel:") ||
            url.includes("mailto:")
        ) return;

        link.addEventListener("click", function (e) {
            if (this.getAttribute('data-bs-toggle') === 'modal') return;
            
            e.preventDefault();
            document.body.classList.remove("page-loaded");
            document.body.classList.add("page-exit");

            setTimeout(() => {
                window.location.href = url;
            }, 300);
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