<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/svgs.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseUrl = '/Travelcation/'; // 🔥 IMPORTANT

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
    $source = trim($_POST['source'] ?? 'offers');

    // Validation
    $errors = [];
    if (empty($full_name))
        $errors[] = 'Full name is required';
    if (empty($email))
        $errors[] = 'Email is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = 'Valid email is required';
    if (empty($phone))
        $errors[] = 'Phone number is required';
    if (empty($travel_date))
        $errors[] = 'Travel date is required';
    if (empty($travelers))
        $errors[] = 'Number of travelers is required';

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
            $_SESSION['modal_enquiry_message'] = 'Thank you for your interest in our offers! We will contact you within 24 hours.';

        } catch (PDOException $e) {
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

// Fetch promotions
$promotions = $pdo->query("
    SELECT p.*, 
           tp.title as package_title,
           pd.title as destination_title
    FROM promotions p
    LEFT JOIN tour_packages tp ON p.package_id = tp.id
    LEFT JOIN popular_destinations pd ON p.destination_id = pd.id
    WHERE p.is_active = 1
    ORDER BY p.display_order, p.created_at DESC
")->fetchAll();

$page_title = "Exclusive Offers & Deals - ExploreWorld Travel";
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
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&family=Source+Sans+Pro:wght@300;400;600;700&display=swap"
        rel="stylesheet">
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
            --white: #FFFFFF;

            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px rgba(0, 0, 0, 0.1);
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

        body.page-loaded {
            opacity: 1;
        }

        body.page-exit {
            opacity: 0;
        }

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
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .page-header .breadcrumb {
            background: transparent;
            justify-content: center;
        }

        .page-header .breadcrumb-item,
        .page-header .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }

        .page-header .breadcrumb-item.active {
            color: var(--secondary-color);
        }

        /* Offers Grid - Full Width */
        .offers-section {
            padding: 3rem 0;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 2rem;
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

        .offers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
        }

        .offer-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
        }

        .offer-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
            border-color: var(--secondary-color);
        }

        /* Full size flyer image */
        .offer-flyer {
            width: 100%;
            height: auto;
            max-height: 500px;
            object-fit: contain;
            background: #f5f5f5;
            cursor: default;
        }

        /* Instagram Reels style video container */
        .offer-video-container {
            position: relative;
            width: 100%;
            background: #000;
            aspect-ratio: 9/16;
            /* Instagram Reels portrait aspect ratio */
            max-height: 600px;
            overflow: hidden;
        }

        .offer-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* Cover the container like Instagram Reels */
        }

        .video-controls {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            gap: 20px;
            z-index: 20;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .offer-video-container:hover .video-controls {
            opacity: 1;
        }

        .video-control-btn {
            width: 45px;
            height: 45px;
            background: rgba(242, 140, 40, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid white;
            box-shadow: var(--shadow-lg);
        }

        .video-control-btn:hover {
            background: var(--secondary-color);
            transform: scale(1.1);
        }

        .media-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 20;
            box-shadow: var(--shadow-md);
        }

        .flyer-badge {
            background: var(--primary-color);
            color: white;
        }

        .video-badge {
            background: var(--secondary-color);
            color: white;
        }

        .offer-content {
            padding: 1.5rem;
        }

        .offer-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .offer-description {
            color: var(--text-light);
            font-size: 1rem;
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        /* Enquiry Modal Styles */
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

        .offer-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px dashed var(--border-color);
            font-size: 0.95rem;
            color: var(--text-light);
        }

        .offer-meta i {
            color: var(--secondary-color);
            margin-right: 5px;
        }

        .btn-offer {
            background: var(--secondary-color);
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
            border: none;
        }

        .btn-offer:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-md);
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

        /* Modal */
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
            .page-header h1 {
                font-size: 2.5rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .offers-section {
                padding: 2rem 0;
            }

            .offers-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .offer-flyer {
                max-height: 400px;
            }
        }

        @media (max-width: 576px) {
            .page-header {
                margin-top: 15%;
            }
        }

        .bg-plane-watermark {
            position: absolute;
            z-index: 0;
            pointer-events: none;
        }

        .bg-plane-watermark svg {
            width: 420px;
            height: auto;
        }

        .bg-plane-tl {
            top: -40px;
            left: -60px;
        }

        .bg-plane-tr {
            top: -40px;
            right: -60px;
            transform: scaleX(-1);
        }

        .bg-plane-bl {
            bottom: -40px;
            left: -60px;
        }

        .bg-plane-br {
            bottom: -40px;
            right: -60px;
            transform: scaleX(-1);
        }

        .suitcase-deco {
            position: absolute;
            pointer-events: none;
            z-index: 3;
            width: 140px;
        }

        .suitcase-right {
            right: -35px;
            top: 50%;
            transform: translateY(-50%);
            animation: suitcaseBounce 4s ease-in-out infinite;
        }

        .suitcase-left {
            left: -35px;
            top: 45%;
            transform: translateY(-50%);
            animation: suitcaseBounce 4.5s ease-in-out infinite 0.5s;
        }

        .suitcase-deco svg {
            width: 100%;
            height: auto;
            filter: drop-shadow(3px 6px 12px rgba(0, 0, 0, 0.15));
        }

        @keyframes suitcaseBounce {

            0%,
            100% {
                transform: translateY(-50%) rotate(-3deg);
            }

            50% {
                transform: translateY(calc(-50% - 12px)) rotate(3deg);
            }
        }

        @media (max-width:992px) {
            .suitcase-deco {
                width: 100px;
            }

            .bg-plane-watermark svg {
                width: 280px;
            }
        }

        @media (max-width:768px) {
            .suitcase-deco {
                display: none;
            }
        }
    </style>
</head>

<body>
    <!-- Top CTA Header -->
    <div class="top-cta" id="topCta">
        <div class="container d-flex justify-content-between align-items-center">
            <span>🎉 Exclusive Offers & Deals - Limited Time Only!</span>
            <a href="tel:+919033186905" class="btn btn-sm btn-outline-light">Call Now</a>
        </div>
    </div>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="uploads/lg-tra (1).png" alt="ExploreWorld Travel" class="img-fluid"
                    style="width: 120px; height: 120px;">
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
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink123"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Explore
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink123">
                            <a class="dropdown-item" href="home.php#packages">Packages</a>
                            <a class="dropdown-item" href="home.php#hotels">Hotels</a>
                            <a class="dropdown-item active" href="offers.php">Exclusive Offers</a>
                            <a class="dropdown-item" href="alldestinations.php">Destinations</a>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="home.php#contact-section">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="submit_feedback.php">Submit Feedback</a>
                    </li>
                </ul>
                <button class="btn btn-primary ms-lg-3 mt-3 mt-lg-0" data-bs-toggle="modal"
                    data-bs-target="#enquiryModal">
                    <i class="fas fa-paper-plane me-2"></i>Quick Enquiry
                </button>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Exclusive Offers & Deals</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Exclusive Offers</li>
                </ol>
            </nav>
        </div>
    </section>

    <!-- Main Content - Full Width -->
    <section class="offers-section" style="position:relative;overflow:hidden;">
        <div class="bg-plane-watermark bg-plane-tl" style="opacity:0.10;"><?= $svg_airplane ?></div>
        <div class="suitcase-deco suitcase-right" style="top:40%; right:-25px;"><?= $svg_suitcase ?></div>
        <div class="container">
            <!-- Success/Error Messages from URL parameter -->
            <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    Thank you for your interest in our offers! We will contact you within 24 hours.
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

            <h2 class="section-title">
                <i class="fas fa-tag"></i> Current Offers
            </h2>

            <?php if (!empty($promotions)): ?>
                <div class="offers-grid">
                    <?php foreach ($promotions as $index => $promo):
                        $isVideo = ($promo['media_type'] == 'video');
                        $mediaPath = $baseUrl . $promo['file_path'];
                        $thumbPath = $baseUrl . ($promo['thumbnail_path'] ?: $promo['file_path']);
                        $videoId = 'videoPlayer_' . $index;
                        ?>
                        <div class="offer-card">
                            <!-- Media Section -->
                            <?php if ($isVideo): ?>
                                <!-- Instagram Reels style portrait video with inline playback -->
                                <div class="offer-video-container">
                                    <span class="media-badge video-badge">
                                        <i class="fas fa-video me-1"></i>Video
                                    </span>
                                    <video id="<?= $videoId ?>" class="offer-video" poster="<?= $thumbPath ?>" loop>
                                        <source src="<?= $mediaPath ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                    <div class="video-controls">
                                        <div class="video-control-btn" onclick="playVideo('<?= $videoId ?>')">
                                            <i class="fas fa-play" id="<?= $videoId ?>_playIcon"></i>
                                        </div>
                                        <div class="video-control-btn" onclick="pauseVideo('<?= $videoId ?>')">
                                            <i class="fas fa-pause"></i>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Full size flyer image - displayed directly -->
                                <span class="media-badge flyer-badge">
                                    <i class="fas fa-image me-1"></i>Flyer
                                </span>
                                <img src="<?= $mediaPath ?>" alt="<?= htmlspecialchars($promo['title']) ?>" class="offer-flyer"
                                    onerror="this.onerror=null;this.src='https://via.placeholder.com/400x500?text=Image+Not+Found';">
                            <?php endif; ?>

                            <!-- Content Section -->
                            <div class="offer-content">
                                <h3 class="offer-title"><?= htmlspecialchars($promo['title']) ?></h3>

                                <?php if (!empty($promo['description'])): ?>
                                    <p class="offer-description"><?= htmlspecialchars($promo['description']) ?></p>
                                <?php endif; ?>

                                <?php if (!empty($promo['package_title']) || !empty($promo['destination_title'])): ?>
                                    <div class="offer-meta">
                                        <?php if (!empty($promo['package_title'])): ?>
                                            <span><i class="fas fa-suitcase-rolling"></i>
                                                <?= htmlspecialchars($promo['package_title']) ?></span>
                                        <?php endif; ?>

                                        <?php if (!empty($promo['destination_title'])): ?>
                                            <span><i class="fas fa-map-marker-alt"></i>
                                                <?= htmlspecialchars($promo['destination_title']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($promo['offer_link'])): ?>
                                    <a href="<?= htmlspecialchars($promo['offer_link']) ?>" target="_blank" class="btn-offer">
                                        View Offer <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- No Offers Found -->
                <div class="empty-state">
                    <i class="fas fa-tag"></i>
                    <h3>No Offers Available</h3>
                    <p class="text-muted">We're currently updating our offers. Please check back later!</p>
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#enquiryModal">
                        <i class="fas fa-paper-plane me-2"></i>Enquire Now
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Enquiry Modal -->
    <div class="modal fade enquiry-modal" id="enquiryModal" tabindex="-1" aria-labelledby="enquiryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="enquiryModalLabel"><i class="fas fa-paper-plane me-2"></i> Travel
                        Enquiry Form</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
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
                                <input type="text" class="form-control" id="modal_full_name" name="modal_full_name"
                                    value="<?= htmlspecialchars($_POST['modal_full_name'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="modal_email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="modal_email" name="modal_email"
                                    value="<?= htmlspecialchars($_POST['modal_email'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="modal_phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="modal_phone" name="modal_phone"
                                    value="<?= htmlspecialchars($_POST['modal_phone'] ?? '') ?>"
                                    placeholder="e.g., 9033186905" required>
                            </div>

                            <div class="col-md-6">
                                <label for="modal_package" class="form-label">Offer Interested In</label>
                                <input type="text" class="form-control" id="modal_package" name="modal_package"
                                    value="<?= htmlspecialchars($_POST['modal_package'] ?? '') ?>"
                                    placeholder="Enter offer name (optional)">
                            </div>

                            <div class="col-md-6">
                                <label for="modal_travel_date" class="form-label">Travel Date *</label>
                                <input type="date" class="form-control" id="modal_travel_date" name="modal_travel_date"
                                    value="<?= htmlspecialchars($_POST['modal_travel_date'] ?? '') ?>"
                                    min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="modal_travelers" class="form-label">Number of Travelers *</label>
                                <select class="form-select" id="modal_travelers" name="modal_travelers" required>
                                    <option value="">Select</option>
                                    <option value="1" <?= (($_POST['modal_travelers'] ?? '') == '1') ? 'selected' : '' ?>>1
                                        Traveler</option>
                                    <option value="2" <?= (($_POST['modal_travelers'] ?? '') == '2') ? 'selected' : '' ?>>2
                                        Travelers</option>
                                    <option value="3" <?= (($_POST['modal_travelers'] ?? '') == '3') ? 'selected' : '' ?>>3
                                        Travelers</option>
                                    <option value="4" <?= (($_POST['modal_travelers'] ?? '') == '4') ? 'selected' : '' ?>>4
                                        Travelers</option>
                                    <option value="5+" <?= (($_POST['modal_travelers'] ?? '') == '5+') ? 'selected' : '' ?>>5+ Travelers</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="modal_message" class="form-label">Additional Requirements</label>
                                <textarea class="form-control" id="modal_message" name="modal_message" rows="4"
                                    placeholder="Please share any specific requirements or questions..."><?= htmlspecialchars($_POST['modal_message'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <input type="hidden" name="source" value="offers">

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
    <footer id="contact">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <h3 class="footer-title">Travelcation</h3>
                    <p class="mb-3 text-white-50">Your trusted partner for creating unforgettable travel experiences
                        with personalized service and expert guidance.</p>
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
                        <li><i class="fas fa-map-marker-alt"></i> 214, Oberon, Opp. Mercedes-Benz Showroom, New City
                            Light Road, Surat – 395017</li>
                        <li><i class="fas fa-phone"></i> +91-90331 86905</li>
                        <li><i class="fas fa-envelope"></i>info@travelcation.co.in</li>
                        <li><i class="fas fa-clock"></i> Mon-Sat: 11:00 AM - 8:00 PM</li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h3 class="footer-title">Newsletter</h3>
                    <p class="mb-3 text-white-50">Subscribe to receive exclusive travel deals and updates.</p>
                    <div class="input-group">
                        <input type="email" class="form-control bg-dark text-white border-secondary"
                            placeholder="Your email address">
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

                link.addEventListener("click", function (e) {
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

        // Video control functions for inline playback
        function playVideo(videoId) {
            const video = document.getElementById(videoId);
            const playIcon = document.getElementById(videoId + '_playIcon');

            if (video.paused) {
                video.play();
                playIcon.className = 'fas fa-stop';
            } else {
                video.pause();
                playIcon.className = 'fas fa-play';
            }
        }

        function pauseVideo(videoId) {
            const video = document.getElementById(videoId);
            const playIcon = document.getElementById(videoId + '_playIcon');

            video.pause();
            playIcon.className = 'fas fa-play';
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);

        // Pause all videos when another starts playing
        document.querySelectorAll('video').forEach(video => {
            video.addEventListener('play', function () {
                document.querySelectorAll('video').forEach(otherVideo => {
                    if (otherVideo !== this) {
                        otherVideo.pause();
                        // Update play icons
                        const otherId = otherVideo.id;
                        const otherPlayIcon = document.getElementById(otherId + '_playIcon');
                        if (otherPlayIcon) {
                            otherPlayIcon.className = 'fas fa-play';
                        }
                    }
                });
            });
        });

        // Show modal if there were errors (to keep form visible)
        <?php if (!$modal_enquiry_success && $modal_enquiry_error): ?>
            document.addEventListener('DOMContentLoaded', function () {
                var enquiryModal = new bootstrap.Modal(document.getElementById('enquiryModal'));
                enquiryModal.show();
            });
        <?php endif; ?>

        // Set minimum date for travel date inputs
        document.addEventListener('DOMContentLoaded', function () {
            const today = new Date().toISOString().split('T')[0];
            document.querySelectorAll('input[type="date"]').forEach(input => {
                if (!input.value) {
                    input.min = today;
                }
            });
        });
    </script>
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
        crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
        crossorigin="anonymous"></script>
</body>

</html>