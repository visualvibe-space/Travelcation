<?php
require_once __DIR__ . '/config/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseUrl = '/Travelcation/'; // 🔥 IMPORTANT

/* ========================
   FETCH ALL DESTINATIONS
======================== */
// Removed 'description' from the SELECT query since it doesn't exist
$destStmt = $pdo->prepare("
    SELECT id, title, slug, image, status, display_order, created_at
    FROM popular_destinations 
    WHERE status = 'Active' 
    ORDER BY display_order ASC, title ASC
");
$destStmt->execute();
$destinations = $destStmt->fetchAll(PDO::FETCH_ASSOC);

/* ========================
   FETCH STATS FOR EACH DESTINATION
======================== */
$destinationStats = [];
foreach ($destinations as $dest) {
    // Count packages for this destination
    $pkgStmt = $pdo->prepare("SELECT COUNT(*) FROM tour_packages WHERE destination_id = ? AND status = 'Active'");
    $pkgStmt->execute([$dest['id']]);
    $packageCount = $pkgStmt->fetchColumn();
    
    // Count hotels for this destination
    $hotelStmt = $pdo->prepare("SELECT COUNT(*) FROM hotels WHERE destination_id = ? AND status = 'Active'");
    $hotelStmt->execute([$dest['id']]);
    $hotelCount = $hotelStmt->fetchColumn();
    
    $destinationStats[$dest['id']] = [
        'packages' => $packageCount,
        'hotels' => $hotelCount
    ];
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
    
    unset($_SESSION['modal_enquiry_success']);
    unset($_SESSION['modal_enquiry_message']);
    unset($_SESSION['modal_enquiry_error']);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_modal_enquiry'])) {
    
    $full_name = trim($_POST['modal_full_name'] ?? '');
    $email = trim($_POST['modal_email'] ?? '');
    $phone = trim($_POST['modal_phone'] ?? '');
    $package_name = trim($_POST['modal_package'] ?? '');
    $travel_date = trim($_POST['modal_travel_date'] ?? '');
    $travelers = trim($_POST['modal_travelers'] ?? '');
    $message = trim($_POST['modal_message'] ?? '');
    $source = trim($_POST['source'] ?? 'alldestinations');
    
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
            $insertStmt = $pdo->prepare("
                INSERT INTO enquiries (full_name, email, phone, package_name, travel_date, travelers, message, source, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'New')
            ");
            
            $insertStmt->execute([
                $full_name,
                $email,
                $phone,
                !empty($package_name) ? $package_name : null,
                !empty($travel_date) ? $travel_date : null,
                $travelers,
                !empty($message) ? $message : null,
                $source
            ]);
            
            $_SESSION['modal_enquiry_success'] = true;
            $_SESSION['modal_enquiry_message'] = 'Thank you for your enquiry! We will contact you within 24 hours.';
            
        } catch (PDOException $e) {
            $_SESSION['modal_enquiry_success'] = false;
            $_SESSION['modal_enquiry_error'] = 'Failed to submit enquiry. Please try again.';
        }
    } else {
        $_SESSION['modal_enquiry_success'] = false;
        $_SESSION['modal_enquiry_error'] = implode('<br>', $errors);
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Check for modal flash messages in session
if (isset($_SESSION['modal_enquiry_success'])) {
    $modal_enquiry_success = $_SESSION['modal_enquiry_success'];
    $modal_enquiry_message = $_SESSION['modal_enquiry_message'] ?? '';
    $modal_enquiry_error = $_SESSION['modal_enquiry_error'] ?? '';
    
    unset($_SESSION['modal_enquiry_success']);
    unset($_SESSION['modal_enquiry_message']);
    unset($_SESSION['modal_enquiry_error']);
}

$page_title = "All Destinations - ExploreWorld Travel";
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
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&family=Source+Sans+Pro:wght@300;400;600;700&display=swap" rel="stylesheet">
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

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-color);
            line-height: 1.6;
            opacity: 0;
            transition: opacity 0.4s ease-in-out;
            background-color: var(--light-color);
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

        /* Destinations Grid */
        .destinations-section {
            padding: 3rem 0;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
            padding-bottom: 15px;
        }

        .section-title i {
            color: var(--secondary-color);
            margin-right: 10px;
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

        .section-subtitle {
            color: var(--text-light);
            font-size: 1.1rem;
            max-width: 700px;
            margin: 1rem auto 0;
        }

        .destinations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 2rem;
        }

        /* Destination Card - Exact same as index page */
        .destination-card {
            display: block;
            text-decoration: none;
            position: relative;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            height: 100%;
        }

        .destination-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .destination-img {
            position: relative;
            width: 100%;
            padding-top: 120%; /* aspect ratio */
            background-size: cover;
            background-position: center;
            border-radius: 14px;
            overflow: hidden;
        }

        .destination-img::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.2) 50%, transparent 100%);
            transition: all 0.3s ease;
        }

        .destination-card:hover .destination-img::after {
            background: linear-gradient(to top, rgba(242,140,40,0.8) 0%, rgba(0,0,0,0.3) 70%);
        }

        .destination-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 20px;
            z-index: 2;
        }

        .destination-overlay h5 {
            color: #fff;
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            transform: translateY(0);
            transition: transform 0.3s ease;
        }

        .destination-card:hover .destination-overlay h5 {
            transform: translateY(-5px);
        }

        .destination-stats {
            display: flex;
            gap: 15px;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s ease;
        }

        .destination-card:hover .destination-stats {
            opacity: 1;
            transform: translateY(0);
        }

        .stat-badge {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(5px);
            padding: 5px 12px;
            border-radius: 20px;
            color: white;
            font-size: 0.85rem;
            font-weight: 500;
            border: 1px solid rgba(255,255,255,0.3);
        }

        .stat-badge i {
            margin-right: 5px;
            color: var(--secondary-color);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-md);
            grid-column: 1 / -1;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--text-light);
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
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
            padding: 0.75rem 2rem;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline:hover {
            background: var(--secondary-color);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Footer */
        footer {
            background: var(--dark-color);
            color: var(--white);
            padding: 4rem 0 2rem;
            margin-top: 3rem;
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
            color: white;
            border: none;
        }

        .modal-header .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
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
            
            .destinations-section {
                padding: 2rem 0;
            }
            
            .destinations-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
            }
            
            .destination-overlay h5 {
                font-size: 1.3rem;
            }
        }

        @media (max-width: 576px) {
            .page-header {
                margin-top: 15%;
            }
            
            .destinations-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Top CTA Header -->
    <div class="top-cta" id="topCta">
        <div class="container d-flex justify-content-between align-items-center">
            <span>✈️ Explore Beautiful Destinations Worldwide!</span>
            <a href="tel:+919033186905" class="btn btn-sm btn-outline-light">Call Now</a>
        </div>
    </div>

    <script>
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
                          <a class="nav-link" href="home.php">Home</a>
                      </li>
                      <li class="nav-item">
                          <a class="nav-link" href="aboutus.php">About</a>
                      </li>
                      <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink123" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Explore
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink123">
                        <a class="dropdown-item" href="packages.php">Packages</a>
                        <a class="dropdown-item" href="hotels.php">Hotels</a>
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

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>All Destinations</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">All Destinations</li>
                </ol>
            </nav>
        </div>
    </section>

    <!-- Destinations Section -->
    <section class="destinations-section">
        <div class="container">
            <!-- Success/Error Messages -->
            <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    Thank you for your enquiry! We will contact you within 24 hours.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success']) && $_GET['success'] == '0'): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Failed to submit enquiry. Please try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Section heading -->
            <div class="text-center mb-5">
                <h2 class="section-title"><i class="fas fa-map-marked-alt"></i> Popular Destinations</h2>
                <p class="section-subtitle">
                    Explore our hand-picked locations across the world that travelers absolutely love.
                    Each destination offers unique experiences, packages, and accommodations.
                </p>
            </div>

            <!-- Destinations Grid -->
            <?php if (!empty($destinations)): ?>
                <div class="destinations-grid">
                    <?php foreach ($destinations as $dest):
                        $stats = $destinationStats[$dest['id']] ?? ['packages' => 0, 'hotels' => 0];
                        $citySlug = strtolower(trim($dest['title']));
                    ?>
                        <a href="destination.php?slug=<?= urlencode($citySlug) ?>" class="destination-card">
                            <div class="destination-img" style="background-image:url('uploads/<?= htmlspecialchars($dest['image']) ?>');">
                                <div class="destination-overlay">
                                    <h5><?= htmlspecialchars($dest['title']) ?></h5>
                                    <div class="destination-stats">
                                        <?php if ($stats['packages'] > 0): ?>
                                            <span class="stat-badge">
                                                <i class="fas fa-suitcase-rolling"></i> <?= $stats['packages'] ?> Packages
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($stats['hotels'] > 0): ?>
                                            <span class="stat-badge">
                                                <i class="fas fa-hotel"></i> <?= $stats['hotels'] ?> Hotels
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-map-marked-alt"></i>
                    <h3>No Destinations Available</h3>
                    <p class="text-muted">We're currently updating our destinations. Please check back later!</p>
                    <a href="index.php" class="btn btn-primary mt-3">
                        <i class="fas fa-home me-2"></i>Back to Home
                    </a>
                </div>
            <?php endif; ?>

            <!-- Additional Info -->
            <div class="text-center mt-5">
                <p class="text-muted mb-3">Can't find what you're looking for? Contact our travel experts!</p>
                <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#enquiryModal">
                    <i class="fas fa-headset me-2"></i>Speak to an Expert
                </button>
            </div>
        </div>
    </section>

    <!-- Enquiry Modal -->
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
                                <label for="modal_package" class="form-label">Destination Interested In</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="modal_package" 
                                       name="modal_package" 
                                       value="<?= htmlspecialchars($_POST['modal_package'] ?? '') ?>"
                                       placeholder="Enter destination name (optional)">
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
                        
                        <input type="hidden" name="source" value="alldestinations">
                        
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

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <h3 class="footer-title">ExploreWorld Travel</h3>
                    <p class="mb-3">Your trusted partner for creating unforgettable travel experiences with personalized service and expert guidance.</p>
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
                        <li><a href="index.php">Home</a></li>
                        <li><a href="aboutus.php">About Us</a></li>
                        <li><a href="packages.php">Packages</a></li>
                        <li><a href="hotels.php">Hotels</a></li>
                        <li><a href="offers.php">Offers</a></li>
                        <li><a href="alldestinations.php">Destinations</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h3 class="footer-title">Contact Info</h3>
                    <ul class="contact-info">
                        <li><i class="fas fa-map-marker-alt"></i> 214, Oberon, Opp. Mercedes-Benz Showroom, New City Light Road, Surat – 395017</li>
                        <li><i class="fas fa-phone"></i> +91 90331 86905</li>
                        <li><i class="fas fa-envelope"></i> travelcation.co.in</li>
                        <li><i class="fas fa-clock"></i> Mon-Sat: 10:00 AM – 7:00 PM</li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h3 class="footer-title">Newsletter</h3>
                    <p class="mb-3">Subscribe to receive exclusive travel deals and updates.</p>
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Your email">
                        <button class="btn btn-primary" type="button">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; <?= date('Y') ?> ExploreWorld Travel Agency. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);

        // Set minimum date for travel date inputs
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.querySelectorAll('input[type="date"]').forEach(input => {
                if (!input.value) {
                    input.min = today;
                }
            });
        });

        // Show modal if there were errors
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