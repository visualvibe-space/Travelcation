<?php
require_once __DIR__ . '/config/config.php';
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ========================
  FETCH TOUR PACKAGES
======================== */
$packageStmt = $pdo->query("
      SELECT * FROM tour_packages
      WHERE status='Active'
      ORDER BY id DESC 
      LIMIT 12
  ");

/* ========================
  FETCH HOTELS
======================== */
$hotelStmt = $pdo->query("
      SELECT * FROM hotels
      ORDER BY id DESC
      LIMIT 12
  ");

/* ========================
  HANDLE SERVICE ENQUIRY SUBMISSION TO other_service TABLE
======================== */
$enquiry_success = false;
$enquiry_error = '';
$submitted_service_type = '';

// Check if there's a flash message in session
if (isset($_SESSION['enquiry_success'])) {
    $enquiry_success = $_SESSION['enquiry_success'];
    $enquiry_error = $_SESSION['enquiry_error'] ?? '';
    $submitted_service_type = $_SESSION['submitted_service_type'] ?? '';

    // Clear the session variables
    unset($_SESSION['enquiry_success']);
    unset($_SESSION['enquiry_error']);
    unset($_SESSION['submitted_service_type']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_service_enquiry'])) {

    $service_type = trim($_POST['service_type'] ?? '');
    $package_name = trim($_POST['package_name'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $travel_date = trim($_POST['travel_date'] ?? '');
    $travelers = trim($_POST['travelers'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validation
    $errors = [];
    if (empty($service_type)) {
        $errors[] = 'Service type is required';
    }
    if (empty($package_name)) {
        $errors[] = 'Service selection is required';
    }
    if (empty($full_name)) {
        $errors[] = 'Full name is required';
    }
    if (empty($email)) {
        $errors[] = 'Email is required';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required';
    }
    if (empty($phone)) {
        $errors[] = 'Phone number is required';
    }

    if (empty($errors)) {
        try {
            // Insert enquiry into other_service table
            $insertStmt = $pdo->prepare("
                  INSERT INTO other_service (full_name, email, phone, package_name, travel_date, travelers, message, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, 'New')
              ");

            $insertStmt->execute([
                $full_name,
                $email,
                $phone,
                $package_name . ' (' . $service_type . ')',
                !empty($travel_date) ? $travel_date : null,
                !empty($travelers) ? $travelers : null,
                $message
            ]);

            // Set success message in session
            $_SESSION['enquiry_success'] = true;
            $_SESSION['submitted_service_type'] = $service_type;

        } catch (PDOException $e) {
            // Set error message in session
            $_SESSION['enquiry_success'] = false;
            $_SESSION['enquiry_error'] = 'Failed to submit enquiry. Please try again.';
            $_SESSION['submitted_service_type'] = $service_type;
        }
    } else {
        // Set validation errors in session
        $_SESSION['enquiry_success'] = false;
        $_SESSION['enquiry_error'] = implode('<br>', $errors);
        $_SESSION['submitted_service_type'] = $service_type;
    }

    // Redirect to the same page to prevent form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
// Get hero content
$hero_content = $pdo->query("SELECT * FROM hero_content WHERE is_active = 1 LIMIT 1")->fetch();

// Get active carousel images
$carousel_images = $pdo->query("SELECT * FROM hero_carousel WHERE is_active = 1 ORDER BY display_order, created_at ASC")->fetchAll();

// Store in variables for frontend use
$hero_title = $hero_content['main_title'] ?? 'Discover Amazing Destinations';
$hero_description = $hero_content['main_description'] ?? 'We offer the best national and international tour packages...';
$hero_button_text = $hero_content['button_text'] ?? 'Explore Packages';
$hero_button_link = $hero_content['button_link'] ?? '#packages';

$destStmt = $pdo->prepare("
      SELECT title, image 
      FROM popular_destinations 
      WHERE status = 'Active' 
      ORDER BY display_order ASC
  ");
$destStmt->execute();
$destinations = $destStmt->fetchAll(PDO::FETCH_ASSOC);
/* ========================
  HANDLE MODAL ENQUIRY SUBMISSION
======================== */
$modal_enquiry_success = false;
$modal_enquiry_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_modal_enquiry'])) {

    $full_name = trim($_POST['modal_full_name'] ?? '');
    $email = trim($_POST['modal_email'] ?? '');
    $phone = trim($_POST['modal_phone'] ?? '');
    $package_name = trim($_POST['modal_package'] ?? '');
    $travel_date = trim($_POST['modal_travel_date'] ?? '');
    $travelers = trim($_POST['modal_travelers'] ?? '');
    $message = trim($_POST['modal_message'] ?? '');
    $source = trim($_POST['source'] ?? 'navbar');

    // Validation
    $errors = [];
    if (empty($full_name)) {
        $errors[] = 'Full name is required';
    }
    if (empty($email)) {
        $errors[] = 'Email is required';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required';
    }
    if (empty($phone)) {
        $errors[] = 'Phone number is required';
    }
    if (empty($travel_date)) {
        $errors[] = 'Travel date is required';
    }
    if (empty($travelers)) {
        $errors[] = 'Number of travelers is required';
    }

    if (empty($errors)) {
        try {
            // Insert enquiry into enquiries table
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

            // Set success message in session
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

    // Redirect to the same page to prevent form resubmission
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
// Handle contact form submission
$contact_success = false;
$contact_error = '';
$contact_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validation
    $errors = [];
    if (empty($full_name)) {
        $errors[] = 'Full name is required';
    }
    if (empty($email)) {
        $errors[] = 'Email is required';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required';
    }
    if (empty($message)) {
        $errors[] = 'Message is required';
    }

    if (empty($errors)) {
        try {
            // Get IP and user agent
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

            // Insert message into contact_messages table
            $insertStmt = $pdo->prepare("
                INSERT INTO contact_messages (full_name, email, phone, subject, message, ip_address, user_agent, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'New')
            ");

            $insertStmt->execute([
                $full_name,
                $email,
                !empty($phone) ? $phone : null,
                !empty($subject) ? $subject : null,
                $message,
                $ip_address,
                $user_agent
            ]);

            // Set success message
            $contact_success = true;
            $contact_message = 'Thank you for contacting us! We will get back to you within 24 hours.';

        } catch (PDOException $e) {
            $contact_error = 'Failed to send message. Please try again.';
        }
    } else {
        $contact_error = implode('<br>', $errors);
    }
}
/* ============================
   FETCH PUBLISHED FEEDBACKS
============================ */
$feedbackStmt = $pdo->query("
    SELECT * FROM feedback 
    WHERE status = 'Published' 
    ORDER BY created_at DESC 
    LIMIT 6
");
$published_feedbacks = $feedbackStmt->fetchAll(PDO::FETCH_ASSOC);

// Get rating statistics for display
$ratingStats = $pdo->query("
    SELECT 
        COUNT(*) as total_reviews,
        AVG(rating) as avg_rating,
        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
        SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
    FROM feedback 
    WHERE status = 'Published'
")->fetch(PDO::FETCH_ASSOC);

$total_reviews = $ratingStats['total_reviews'] ?? 0;
$avg_rating = $ratingStats['avg_rating'] ? number_format($ratingStats['avg_rating'], 1) : 0;

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travelcation</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css">
    <!-- Owl Carousel CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" sizes="32x32" href="uploads/lg-tra (1).png">
    <!-- Google Fonts - Professional Selection -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
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
            --success-color: #38A169;
            --white: #FFFFFF;

            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px rgba(0, 0, 0, 0.1);
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px rgba(0, 0, 0, 0.15);
            --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);

            /* ✅ SINGLE SOURCE OF TRUTH */
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

        /* ================= PRELOADER STYLES ================= */
        #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: white;
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.8s ease-in-out, visibility 0.8s ease-in-out;
        }

        #preloader.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .preloader-content {
            text-align: center;
            max-width: 500px;
            padding: 2rem;
        }

        .preloader-gif {
            width: 200px;
            height: auto;
            margin-bottom: 2rem;
            animation: float 3s ease-in-out infinite;
        }

        .preloader-text {
            color: var(--white);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            background: linear-gradient(90deg, var(--secondary-color), var(--accent-color), var(--white));
            background-size: 200% auto;
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            -webkit-text-fill-color: transparent;
            animation: gradient 3s linear infinite;
        }

        .preloader-progress {
            width: 300px;
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            margin: 0 auto 1.5rem;
        }

        .preloader-progress-bar {
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, var(--secondary-color), var(--accent-color));
            border-radius: 10px;
            animation: progress 2.5s ease-in-out forwards;
        }

        .preloader-dots {
            display: flex;
            justify-content: center;
            gap: 8px;
        }

        .preloader-dot {
            width: 8px;
            height: 8px;
            background: var(--white);
            border-radius: 50%;
            opacity: 0.5;
            animation: dotPulse 1.5s ease-in-out infinite;
        }

        .preloader-dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .preloader-dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) scale(1);
            }

            50% {
                transform: translateY(-15px) scale(1.05);
            }
        }

        @keyframes gradient {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        @keyframes progress {
            0% {
                width: 0%;
            }

            20% {
                width: 20%;
            }

            40% {
                width: 40%;
            }

            60% {
                width: 60%;
            }

            80% {
                width: 80%;
            }

            100% {
                width: 100%;
            }
        }

        @keyframes dotPulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.5;
            }

            50% {
                transform: scale(1.5);
                opacity: 1;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .preloader-gif {
                width: 150px;
            }

            .preloader-text {
                font-size: 1.2rem;
            }

            .preloader-progress {
                width: 250px;
            }
        }

        @media (max-width: 576px) {
            .preloader-gif {
                width: 120px;
            }

            .preloader-progress {
                width: 200px;
            }

            .preloader-text {
                font-size: 1rem;
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
            top: 40px;
            left: 0;
            width: 100%;
            background: var(--white) !important;
            box-shadow: var(--shadow-sm);
            z-index: 9999;
            padding: 12px 20px;
            transition: top 0.4s ease, box-shadow 0.3s ease;
            height: auto;
            min-height: 70px;
        }

        .navbar.sticky {
            top: 0;
            background: var(--white) !important;
            box-shadow: var(--shadow-md);
        }

        .navbar-brand img {
            width: 100px;
            height: auto;
            transition: all 0.3s ease;
        }

        @media (max-width: 768px) {
            .navbar-brand img {
                width: 80px;
            }
        }

        .nav-link {
            color: var(--dark-color) !important;
            font-weight: 600;
            padding: 0.5rem 0.75rem !important;
            margin: 0 0.15rem;
            border-radius: 6px;
            transition: all 0.3s ease;
            position: relative;
            font-size: 0.95rem;
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
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 20px;
            height: 3px;
            background: var(--secondary-color);
            border-radius: 2px;
        }

        .navbar .dropdown-menu {
            background-color: #ffffff !important;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-lg);
            padding: 0.5rem 0;
            z-index: 1055;
            border-radius: 8px;
            margin-top: 0.5rem;
        }

        .navbar .dropdown-item {
            color: var(--text-color);
            font-weight: 500;
            padding: 0.5rem 1rem;
        }

        .navbar .dropdown-item:hover {
            background-color: var(--light-color);
            color: var(--primary-color);
        }

        .navbar .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.6rem 1.25rem;
        }

        @media (max-width: 768px) {
            .navbar {
                top: 40px;
                padding: 8px 16px;
            }

            .navbar.sticky {
                top: 0;
            }

            .navbar-collapse {
                background-color: #ffffff;
                padding: 1rem;
                box-shadow: var(--shadow-md);
                border-radius: 0 0 12px 12px;
                margin-top: 8px;
            }

            .navbar .dropdown-menu {
                position: static;
                float: none;
                box-shadow: none;
                border: none;
                padding-left: 1rem;
                margin-bottom: 0.5rem;
            }

            .navbar .dropdown-toggle::after {
                float: right;
                margin-top: 8px;
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
            border: 1px solid rgba(255, 255, 255, 0.3);
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

        @media (max-width: 768px) {
            .top-cta {
                height: 40px;
                padding: 0 10px;
                font-size: 11px;
            }

            .top-cta .container {
                justify-content: space-between;
                gap: 8px;
            }

            .top-cta .cta-text {
                font-size: 11px;
            }

            .top-cta .btn-outline-light {
                padding: 0.2rem 0.7rem;
                font-size: 10px;
                flex-shrink: 0;
                white-space: nowrap;
            }
        }

        @media (max-width: 480px) {
            .top-cta .cta-text {
                font-size: 10px;
            }

            .top-cta .btn-outline-light {
                padding: 0.15rem 0.5rem;
                font-size: 9px;
            }
        }

        /* ================= HERO SECTION (FIXED) ================= */
        .hero-section {
            position: relative;
            min-height: 100vh;
            padding-top: 110px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--dark-color) 100%);
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.3) 0%, rgba(0, 0, 0, 0.6) 100%);
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
            transition: opacity 0.6s ease-in-out;
            will-change: opacity;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
            transform: translateZ(0);
            -webkit-transform: translateZ(0);
        }

        .bg-slide.active {
            opacity: 1;
        }

        /* ================= HERO CONTENT ================= */
        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 850px;
            width: 100%;
            padding: 2rem;
            margin: 0 auto;
            text-align: center;
            color: var(--white);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 60vh;
        }

        .hero-title {
            font-family: 'Inter', sans-serif;
            letter-spacing: -0.03em;
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.25rem;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.4);
            line-height: 1.15;
        }

        .hero-title span {
            color: var(--secondary-color);
        }

        .hero-description {
            font-size: 1.25rem;
            opacity: 0.95;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            line-height: 1.7;
            max-width: 650px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* ================= BUTTONS ================= */
        .btn-primary,
        .btn-outline {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            background: var(--secondary-color);
            border: none;
            padding: 0.875rem 2.25rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 1rem;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        .btn-enquire-primary {
            padding: 0;
        }

        .btn-primary:hover {
            background: #e07a1f;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(242, 140, 40, 0.35);
        }

        .btn-outline {
            border: 2px solid rgba(255, 255, 255, 0.8);
            background: transparent;
            color: var(--white);
        }

        .btn-outline:hover {
            background: var(--white);
            color: var(--dark-color);
            border-color: var(--white);
            transform: translateY(-3px);
        }

        /* ================= PACKAGE CARDS ================= */
        .package-card {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            background: var(--white);
        }

        .package-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .card-img-container {
            height: 240px;
            overflow: hidden;
        }

        .card-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .package-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--accent-color);
            color: var(--white);
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }

        .package-price {
            position: absolute;
            bottom: 15px;
            left: 15px;
            background: rgba(255, 255, 255, 0.95);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 700;
        }

        /* ================= CAROUSEL THUMBNAILS ================= */
        .carousel-thumbnails {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 0.75rem;
            z-index: 3;
        }

        .thumbnail {
            width: 80px;
            height: 60px;
            border-radius: 6px;
            overflow: hidden;
            opacity: 0.7;
            transition: all 0.3s ease;
        }

        .thumbnail:hover,
        .thumbnail.active {
            opacity: 1;
            transform: scale(1.05);
        }

        /* ================= FOOTER ================= */
        footer {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: var(--white);
            padding: 4rem 0 2rem;
            position: relative;
            overflow: hidden;
        }

        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary-color), var(--accent-color), var(--secondary-color));
            background-size: 200% 100%;
            animation: gradientMove 3s linear infinite;
        }

        @keyframes gradientMove {
            0% {
                background-position: 0% 50%;
            }

            100% {
                background-position: 200% 50%;
            }
        }

        .footer-title {
            color: var(--white);
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.75rem;
            font-family: 'Inter', sans-serif;
            letter-spacing: 0.5px;
        }

        .footer-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--secondary-color);
            border-radius: 2px;
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 0.85rem;
        }

        .footer-links a {
            color: #94a3b8;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
        }

        .footer-links a:hover {
            color: var(--secondary-color);
            transform: translateX(6px);
        }

        .footer-links a i {
            margin-right: 0.6rem;
            font-size: 0.7rem;
            color: var(--secondary-color);
        }

        .contact-info {
            list-style: none;
            padding: 0;
        }

        .contact-info li {
            margin-bottom: 1.1rem;
            color: #94a3b8;
            font-size: 0.9rem;
            display: flex;
            align-items: flex-start;
            line-height: 1.5;
        }

        .contact-info i {
            color: var(--secondary-color);
            margin-right: 0.75rem;
            width: 18px;
            margin-top: 4px;
            flex-shrink: 0;
        }

        .social-links {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .social-link {
            width: 42px;
            height: 42px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 10px;
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
            font-size: 1rem;
        }

        .social-link:hover {
            transform: translateY(-4px);
            background: rgba(255, 255, 255, 0.15);
        }

        .social-link:hover::before {
            opacity: 1;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            margin-top: 3rem;
            padding-top: 1.5rem;
            text-align: center;
        }

        .footer-bottom p {
            color: #64748b;
            font-size: 0.9rem;
            margin: 0;
        }

        /* ================= RESPONSIVE ================= */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-description {
                font-size: 1.05rem;
            }

            .carousel-thumbnails {
                position: relative;
                bottom: auto;
                transform: none;
                margin-top: 1.5rem;
            }
        }

        @media (max-width: 576px) {
            .hero-title {
                font-size: 2rem;
            }

            .hero-description {
                font-size: 0.95rem;
            }

            .btn-primary {
                width: 100%;
            }

            .thumbnail {
                width: 60px;
                height: 45px;
            }
        }

        /* ================= RESPONSIVE HERO ================= */
        @media (max-width: 1200px) {
            .hero-title {
                font-size: 3rem;
            }
        }

        @media (max-width: 992px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-description {
                font-size: 1.15rem;
            }

            .hero-section {
                padding-top: 100px;
            }
        }

        @media (max-width: 768px) {
            .hero-section {
                min-height: 85vh;
                padding-top: 90px;
                flex-wrap: wrap;
                align-content: flex-start;
            }

            .hero-title {
                font-size: 2rem;
            }

            .hero-description {
                font-size: 1.05rem;
                margin-bottom: 1.5rem;
            }

            .hero-content {
                padding: 1.5rem;
                width: 100%;
            }

            .hero-buttons {
                flex-direction: column;
                width: 100%;
                max-width: 300px;
                margin: 0 auto;
            }

            .hero-buttons .btn {
                width: 100%;
                text-align: center;
            }

            .carousel-thumbnails {
                position: relative;
                bottom: auto;
                left: auto;
                transform: none;
                width: 100%;
                justify-content: center;
                margin-top: 1.5rem;
                padding: 0 1rem;
            }
        }

        @media (max-width: 576px) {
            .hero-title {
                font-size: 1.75rem;
            }

            .hero-description {
                font-size: 0.95rem;
                line-height: 1.6;
            }
        }

        /* Extra small devices (iPhone SE etc.) */


        /* Package Cards - Professional */
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
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-title {
            font-family: 'Inter', sans-serif;
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

        /* Hotel Cards - Professional */
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



        .copyright {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 3rem;
            padding-top: 1.5rem;
            text-align: center;
            color: #A0AEC0;
            font-size: 0.875rem;
        }

        /* Modal - Professional */
        .modal-header {
            background: var(--accent-color);
            color: var(--white);
            border-bottom: none;
            padding: 1.5rem;
        }

        .modal-title {
            font-family: 'Inter', sans-serif;
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

        .form-control,
        .form-select {
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(0, 166, 200, 0.1);
        }

        /* Carousel Thumbnails - Professional */
        .carousel-thumbnails {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 0.75rem;
            z-index: 3;
        }

        .thumbnail {
            width: 80px;
            height: 60px;
            border-radius: 6px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            opacity: 0.7;
        }

        .thumbnail.active,
        .thumbnail:hover {
            border-color: var(--secondary-color);
            opacity: 1;
            transform: scale(1.05);
        }

        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-description {
                font-size: 1.1rem;
            }

            .hero-section {
                min-height: 70vh;
            }

            .carousel-thumbnails {
                position: relative;
                bottom: auto;
                left: auto;
                transform: none;
                justify-content: center;
                margin-top: 2rem;
            }

            .section-title {
                font-size: 1.75rem;
            }


        }


        @media (max-width: 576px) {
            .hero-title {
                font-size: 2rem;
            }

            .hero-description {
                font-size: 1rem;
            }

            .btn-primary {
                padding: 0.75rem 1.5rem;
                width: 100%;
            }

            .thumbnail {
                width: 60px;
                height: 45px;
            }
        }

        /* Animation Classes */
        .fade-in {
            animation: fadeIn 0.8s ease-in-out;
        }

        .slide-up {
            animation: slideUp 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
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

        /* Utility Classes */
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

        /* Loading States */
        .loading {
            position: relative;
            overflow: hidden;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            from {
                left: -100%;
            }

            to {
                left: 100%;
            }
        }

        :root {
            --cta-height: 40px;
            --navbar-height: 72px;
        }

        /* Hero section offset */
        .hero-section {
            padding-top: calc(var(--navbar-height) + var(--cta-height));
        }

        /* ================= MOBILE PACKAGES SLIDER ================= */
        @media (max-width: 768px) {
            .packagesSwiper {
                padding: 10px 0 30px;
            }

            .packagesSwiper .row {
                flex-wrap: nowrap;
            }

            .packagesSwiper .col-md-6,
            .packagesSwiper .col-lg-3 {
                width: auto;
            }

            .package-card {
                height: 100%;
            }
        }

        #enquiryModal .modal-dialog {
            margin-top: 10%;
            /* adjust to your navbar height */
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



        /* ================= DESKTOP GRID ================= */
        @media (min-width: 769px) {
            .packagesSwiper .swiper-wrapper {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 24px;
            }

            .packagesSwiper .swiper-slide {
                width: 100% !important;
            }
        }

        /* ================= MOBILE SLIDER ================= */
        @media (max-width: 768px) {
            .packagesSwiper {
                overflow: hidden;
            }

            .packagesSwiper .swiper-wrapper {
                display: flex;
            }

            .packagesSwiper .swiper-slide {
                width: auto;
            }

            .package-card {
                min-width: 260px;
            }
        }

        /* ================= DESKTOP GRID ================= */
        @media (min-width: 769px) {
            .destinationsSwiper .swiper-wrapper {
                display: grid;
                grid-template-columns: repeat(6, 1fr);
                /* 👈 same as col-lg-2 */
                gap: 24px;
            }

            .destinationsSwiper .swiper-slide {
                width: 100% !important;
            }
        }

        /* ================= MOBILE SLIDER ================= */
        @media (max-width: 768px) {
            .destinationsSwiper {
                overflow: hidden;
            }

            .destinationsSwiper .swiper-wrapper {
                display: flex;
            }

            .destinationsSwiper .swiper-slide {
                width: auto;
            }

            .destination-card {
                min-width: 160px;
                /* 👈 adjust card width */
            }
        }

        /* ================= CARD STYLING ================= */
        .destination-card {
            display: block;
            text-decoration: none;
        }

        .destination-img {
            position: relative;
            width: 100%;
            padding-top: 120%;
            /* aspect ratio */
            background-size: cover;
            background-position: center;
            border-radius: 14px;
            overflow: hidden;
        }

        .destination-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: flex-end;
            padding: 12px;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.55), transparent);
        }

        .destination-overlay h5 {
            color: #fff;
            font-size: 14px;
            margin: 0;
            font-weight: 600;
        }

        /* ================= DESKTOP GRID ================= */
        @media (min-width: 769px) {
            .hotelsSwiper .swiper-wrapper {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 24px;
            }

            .hotelsSwiper .swiper-slide {
                width: 100% !important;
            }
        }

        /* ================= MOBILE SLIDER ================= */
        @media (max-width: 768px) {
            .hotelsSwiper {
                overflow: hidden;
            }

            .hotelsSwiper .swiper-wrapper {
                display: flex;
            }

            .hotelsSwiper .swiper-slide {
                width: auto;
            }

            .hotel-card {
                min-width: 260px;
            }
        }

        .video-section {
            position: relative;
            width: 100%;
            height: 90vh;
            /* desktop height */
            overflow: hidden;
        }

        .bg-video {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* IMPORTANT for responsiveness */
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
            background: rgba(0, 0, 0, 0.4);
            /* dark overlay */
            padding: 20px;
        }

        .video-overlay h2 {
            font-size: 3rem;
            font-weight: bold;
        }

        .video-overlay p {
            font-size: 1.2rem;
            margin: 10px 0 20px;
        }

        /* 🔥 Mobile Responsive */
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

        /* ================= NEW AESTHETIC SECTION STYLES ================= */

        /* Popular Destinations - Enhanced */
        .popular-destinations {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 5rem 0;
            position: relative;
            overflow: hidden;
        }

        .popular-destinations::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(242, 140, 40, 0.03) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .popular-destinations .section-title {
            font-size: 2.75rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            background-clip: text;
            /* ✅ Standard property */
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }


        .popular-destinations .section-title::after {
            content: '';
            position: absolute;
            bottom: -18px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary-color), var(--accent-color));
            border-radius: 2px;
        }

        .popular-destinations .section-subtitle {
            color: var(--text-light);
            font-size: 1.1rem;
            max-width: 700px;
            margin: 1.5rem auto 0;
        }

        .destination-img {
            height: 280px;
            border-radius: 24px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .destination-img:hover {
            transform: translateY(-12px);
            box-shadow: 0 25px 40px rgba(242, 140, 40, 0.2);
        }

        .destination-overlay {
            /* background: linear-gradient(to top, 
          rgba(42,67,101,0.9) 0%,
          rgba(0,166,200,0.4) 50%,
          transparent 100%); */
            padding: 20px;
        }

        .destination-overlay h5 {
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            transform: translateY(0);
            transition: transform 0.3s ease;
        }

        .destination-img:hover .destination-overlay h5 {
            transform: translateY(-5px);
        }

        /* Packages Section - Enhanced */
        #packages {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            padding: 5rem 0;
            position: relative;
        }

        #packages::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 200px;
            background: linear-gradient(135deg, rgba(242, 140, 40, 0.05) 0%, transparent 100%);
            pointer-events: none;
        }

        #packages .section-title {
            font-size: 2.75rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 1rem;
            position: relative;
            padding-bottom: 15px;
        }

        #packages .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary-color), var(--accent-color));
            border-radius: 2px;
        }

        #packages .lead {
            color: var(--text-light);
            font-size: 1.2rem;
            max-width: 700px;
            margin: 1.5rem auto 0;
        }

        .package-card {
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        }

        .package-card:hover {
            background: white;
            border-color: var(--secondary-color);
        }

        .card-img-container {
            height: 240px;
            position: relative;
            overflow: hidden;
        }

        .card-img-container::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 100px;
            pointer-events: none;
        }

        .package-price {
            background: linear-gradient(135deg, var(--secondary-color), #f39c12);
            color: white;
            padding: 0.5rem 1.2rem;
            border-radius: 30px;
            font-size: 1.2rem;
            font-weight: 700;
            box-shadow: 0 10px 20px rgba(242, 140, 40, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .package-card .card-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.75rem;
        }

        .package-card .card-text {
            color: var(--text-light);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .package-card .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border: none;
            border-radius: 12px;
            padding: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }

        .package-card .btn-primary:hover {
            background: linear-gradient(135deg, var(--accent-color), var(--primary-color));
            transform: scale(1.02);
        }

        /* Video Section - Enhanced */
        .video-section {
            position: relative;
            height: 80vh;
            overflow: hidden;
        }

        .video-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(42, 67, 101, 0.7) 0%, rgba(0, 166, 200, 0.5) 100%);
            z-index: 1;
        }

        .bg-video {
            filter: brightness(0.8) saturate(1.2);
            transition: transform 0.3s ease;
        }

        .video-overlay {
            background: transparent;
            position: relative;
            z-index: 2;
        }

        .video-overlay h2 {
            font-size: 4rem;
            font-weight: 800;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            margin-bottom: 1rem;
            animation: fadeInUp 1s ease;
        }

        .video-overlay p {
            font-size: 1.5rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            animation: fadeInUp 1s ease 0.2s both;
        }

        .video-overlay .btn-warning {
            background: linear-gradient(135deg, var(--secondary-color), #f39c12);
            border: none;
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            box-shadow: 0 10px 20px rgba(242, 140, 40, 0.3);
            animation: fadeInUp 1s ease 0.4s both;
            transition: all 0.3s ease;
        }

        .video-overlay .btn-warning:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(242, 140, 40, 0.4);
        }

        /* Hotels Section - Enhanced */
        #hotels {
            background: linear-gradient(135deg, #f1f5f9 0%, #ffffff 100%);
            padding: 5rem 0;
            position: relative;
        }

        #hotels .section-title {
            font-size: 2.75rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 1rem;
            position: relative;
            padding-bottom: 15px;
        }

        #hotels .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary-color), var(--accent-color));
            border-radius: 2px;
        }

        #hotels .lead {
            color: var(--text-light);
            font-size: 1.2rem;
            max-width: 700px;
            margin: 1.5rem auto 0;
        }

        .hotel-card {
            border-radius: 20px;
            overflow: hidden;
            background: white;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06);
            transition: all 0.4s ease;
            border: 1px solid rgba(0, 0, 0, 0.03);
        }

        .hotel-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 60px rgba(0, 166, 200, 0.15);
        }

        .hotel-card .card-img-container {
            position: relative;
        }

        .hotel-rating {
            background: linear-gradient(135deg, var(--secondary-color), #f39c12);
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 30px;
            font-size: 0.9rem;
            font-weight: 600;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .hotel-card .card-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .hotel-card .card-text {
            color: var(--text-light);
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .hotel-card .fw-bold {
            color: var(--accent-color) !important;
            font-size: 1.3rem;
        }

        .hotel-card .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border: none;
            border-radius: 12px;
            padding: 0.5rem 1rem;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .hotel-card .btn-primary:hover {
            background: linear-gradient(135deg, var(--accent-color), var(--primary-color));
            transform: scale(1.05);
        }

        /* Section Headers Enhancement */
        .section-title {
            position: relative;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .section-title i {
            color: var(--secondary-color);
            margin-right: 10px;
            font-size: 2rem;
        }

        /* View All Buttons Enhancement */
        .btn-outline {
            border: 2px solid var(--accent-color);
            color: var(--accent-color);
            background: transparent;
            padding: 0.75rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-outline:hover {
            background: var(--accent-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 166, 200, 0.2);
        }

        /* Animations */
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

        /* Responsive Enhancements */
        @media (max-width: 768px) {

            .popular-destinations .section-title,
            #packages .section-title,
            #hotels .section-title {
                font-size: 2rem;
            }

            .video-overlay h2 {
                font-size: 2.5rem;
            }

            .video-overlay p {
                font-size: 1.1rem;
            }

            .destination-img {
                height: 200px;
            }
        }

        @media (max-width: 576px) {
            .video-overlay h2 {
                font-size: 2rem;
            }

            .video-overlay p {
                font-size: 1rem;
            }

            .destination-img {
                height: 160px;
            }
        }

        .service-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .service-card .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .service-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            flex: 1;
        }

        .service-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 0.9rem;
        }

        .service-item i {
            margin-top: 3px;
            flex-shrink: 0;
        }

        .service-item span {
            line-height: 1.4;
        }

        .service-item strong {
            color: #333;
        }

        .bg-light {
            background-color: #f8f9fa !important;
        }

        .card-header {
            border-bottom: none;
            padding: 1rem 1.5rem;
        }

        .card-header h3 {
            font-size: 1.3rem;
            font-weight: 600;
        }

        .card-body {
            padding: 1.5rem;
        }

        .btn-outline-primary,
        .btn-outline-success,
        .btn-outline-info {
            border-width: 2px;
        }

        .enquiry-form {
            border-top: 1px solid #dee2e6;
            padding-top: 1rem;
            margin-top: 1rem;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-label {
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
        }

        @media (max-width: 768px) {
            .service-card {
                margin-bottom: 20px;
            }

            .service-item span {
                font-size: 0.85rem;
            }

            .card-header h3 {
                font-size: 1.2rem;
            }
        }

        /* ================= ABOUT SECTION ================= */
        .about-section {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            padding: 5rem 0;
            position: relative;
            overflow: hidden;
        }

        .about-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(242, 140, 40, 0.03) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            animation: rotate 30s linear infinite;
        }

        .about-section .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }

        .about-section .section-title i {
            color: var(--secondary-color);
            margin-right: 10px;
        }

        .about-section .section-title::after {
            content: '';
            position: absolute;
            bottom: -18px;
            left: 0;
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary-color), var(--accent-color));
            border-radius: 2px;
        }

        .about-section .d-flex {
            padding: 0.75rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid transparent;
        }

        .about-section .d-flex:hover {
            background: white;
            border-color: var(--border-color);
            transform: translateX(10px);
            box-shadow: var(--shadow-md);
        }

        .about-section .d-flex i {
            color: var(--success-color);
            filter: drop-shadow(0 2px 4px rgba(56, 161, 105, 0.2));
        }

        .about-section .d-flex strong {
            color: var(--primary-color);
            font-weight: 600;
        }

        /* Contact Card */
        .contact-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: var(--shadow-xl);
            height: 100%;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.03);
        }

        .contact-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 150px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            opacity: 0.05;
            border-radius: 20px 20px 100% 100%;
        }

        .contact-card h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
        }

        .contact-card h3 i {
            color: var(--secondary-color);
        }

        .contact-detail-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            padding: 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(0, 0, 0, 0.02);
            position: relative;
            z-index: 1;
        }

        .contact-detail-item:hover {
            background: linear-gradient(135deg, rgba(242, 140, 40, 0.05), rgba(0, 166, 200, 0.05));
            transform: translateX(10px);
            box-shadow: var(--shadow-md);
            border-color: var(--border-color);
        }

        .contact-detail-item .contact-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            margin-right: 1.2rem;
            flex-shrink: 0;
            box-shadow: var(--shadow-lg);
            transition: all 0.3s ease;
        }

        .contact-detail-item:hover .contact-icon {
            transform: rotate(360deg) scale(1.1);
        }

        .contact-detail-item .contact-text {
            flex: 1;
        }

        .contact-detail-item .contact-text h5 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 0.3rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .contact-detail-item .contact-text p {
            font-size: 1rem;
            color: var(--text-color);
            margin-bottom: 0;
            line-height: 1.5;
            font-weight: 500;
        }

        .contact-detail-item .contact-text a {
            color: var(--text-color);
            text-decoration: none;
            transition: color 0.3s ease;
            font-weight: 500;
        }

        .contact-detail-item .contact-text a:hover {
            color: var(--secondary-color);
        }

        /* Quote Section */
        .quote-section {
            background: linear-gradient(135deg, var(--accent-color), #2C7A9E);
            color: white;
            padding: 4rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .quote-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
        }

        .quote-section .container {
            position: relative;
            z-index: 2;
        }

        .quote-section i {
            opacity: 0.3;
            color: rgba(255, 255, 255, 0.5);
            animation: float 3s ease-in-out infinite;
        }

        .quote-section h3 {
            font-family: 'Inter', sans-serif;
            font-size: 2.2rem;
            font-weight: 700;
            margin: 1.5rem 0;
            line-height: 1.4;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .quote-section p {
            font-size: 1.3rem;
            margin-top: 1rem;
            opacity: 0.95;
            font-weight: 500;
        }

        .quote-section p i {
            margin: 0 5px;
            animation: none;
            opacity: 1;
            font-size: 1.2rem;
        }

        /* Floating animation for quote icon */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .about-section .section-title {
                font-size: 2.2rem;
            }

            .contact-card {
                padding: 2rem;
            }

            .quote-section h3 {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 768px) {
            .about-section {
                padding: 4rem 0;
            }

            .about-section .row>div:first-child {
                margin-bottom: 2rem;
            }

            .contact-card {
                padding: 1.5rem;
            }

            .contact-detail-item {
                padding: 0.75rem;
            }

            .contact-detail-item .contact-icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
                margin-right: 1rem;
            }

            .quote-section h3 {
                font-size: 1.5rem;
            }

            .quote-section p {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 576px) {
            .about-section .section-title {
                font-size: 1.8rem;
            }

            .about-section .d-flex {
                padding: 0.5rem;
            }

            .contact-detail-item {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .contact-detail-item .contact-icon {
                margin-right: 0;
                margin-bottom: 1rem;
            }

            .quote-section h3 {
                font-size: 1.3rem;
            }

            .quote-section p {
                font-size: 1rem;
            }
        }

        /* ================= RESPONSIVE ================= */
        @media (max-width: 1200px) {

            .contact-info-side,
            .contact-form-side {
                padding: 2.5rem;
            }
        }

        @media (max-width: 992px) {
            .page-header h1 {
                font-size: 2.8rem;
            }

            .contact-info-side h2,
            .contact-form-side h2 {
                font-size: 2rem;
            }

            .contact-detail-card {
                padding: 1.5rem;
            }

            .contact-detail-card .icon-wrapper {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 120px 0 60px;
            }

            .page-header h1 {
                font-size: 2.2rem;
            }

            .contact-wrapper {
                transform: translateY(-30px);
                margin-bottom: -30px;
            }

            .contact-info-side,
            .contact-form-side {
                padding: 2rem;
            }

            .contact-detail-card {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .contact-detail-card:hover {
                transform: translateY(-5px) scale(1.02);
            }

            .map-container {
                height: 350px;
            }

            .map-overlay {
                left: 50%;
                transform: translateX(-50%);
                white-space: nowrap;
            }
        }

        @media (max-width: 576px) {
            .page-header h1 {
                font-size: 1.8rem;
            }

            .contact-wrapper {
                transform: translateY(-20px);
                margin-bottom: -20px;
            }

            .contact-info-side,
            .contact-form-side {
                padding: 1.5rem;
            }

            .contact-info-side h2,
            .contact-form-side h2 {
                font-size: 1.4rem;
            }

            .contact-detail-card .icon-wrapper {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }

            .contact-detail-card p,
            .contact-detail-card a {
                font-size: 0.95rem;
            }

            .map-container {
                height: 250px;
            }

            .map-overlay {
                font-size: 0.9rem;
                padding: 0.5rem 1rem;
            }

            .form-group i {
                display: none;
            }

            .form-control {
                padding-left: 1rem;
            }
        }

        /* Floating animation */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        /* ================= PAGE HEADER ================= */
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            padding: 140px 0 80px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .page-header h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            position: relative;
            z-index: 2;
            font-family: 'Inter', sans-serif;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .page-header .breadcrumb {
            background: transparent;
            justify-content: center;
            margin-bottom: 0;
            position: relative;
            z-index: 2;
        }

        .page-header .breadcrumb-item,
        .page-header .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 1.1rem;
        }

        .page-header .breadcrumb-item.active {
            color: white;
        }

        .page-header .breadcrumb-item+.breadcrumb-item::before {
            color: rgba(255, 255, 255, 0.5);
        }

        /* ================= CONTACT SECTION (IMPROVED) ================= */
        .contact-section {
            padding: 5rem 0;
            position: relative;
        }

        .contact-wrapper {
            background: white;
            border-radius: 40px;
            box-shadow: var(--shadow-2xl);
            overflow: hidden;
            position: relative;
            transform: translateY(-50px);
            margin-bottom: -50px;
        }

        .contact-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 10px;
            background: linear-gradient(90deg, var(--secondary-color), var(--accent-color), var(--primary-color));
            z-index: 10;
        }

        /* Left Side - Contact Info (IMPROVED) */
        .contact-info-side {
            background: linear-gradient(135deg, var(--primary-color), #1E2F4A, var(--dark-color));
            color: white;
            padding: 3.5rem;
            height: 100%;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .contact-info-side::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -20%;
            width: 80%;
            height: 80%;
            background: radial-gradient(circle, rgba(242, 140, 40, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }

        .contact-info-side::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -20%;
            width: 80%;
            height: 80%;
            background: radial-gradient(circle, rgba(0, 166, 200, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            animation: floatReverse 10s ease-in-out infinite;
        }

        @keyframes floatReverse {

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            50% {
                transform: translate(-20px, 20px) rotate(5deg);
            }
        }

        .contact-info-side h2 {
            font-size: 2.3rem;
            font-weight: 700;
            margin-bottom: 2.5rem;
            position: relative;
            z-index: 2;
            font-family: 'Inter', sans-serif;
        }

        .contact-info-side h2 i {
            color: var(--secondary-color);
            margin-right: 10px;
            background: rgba(255, 255, 255, 0.1);
            padding: 10px;
            border-radius: 50%;
            box-shadow: var(--shadow-lg);
        }

        .contact-info-side h2::after {
            content: '';
            position: absolute;
            bottom: -18px;
            left: 0;
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary-color), var(--accent-color));
            border-radius: 2px;
        }

        .contact-detail-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 25px;
            padding: 1.8rem;
            margin-bottom: 1.8rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .contact-detail-card:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(15px) scale(1.02);
            border-color: var(--secondary-color);
            box-shadow: var(--shadow-xl);
        }

        .contact-detail-card .icon-wrapper {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            flex-shrink: 0;
            box-shadow: var(--shadow-lg);
            transition: all 0.4s ease;
        }

        .contact-detail-card:hover .icon-wrapper {
            transform: rotateY(180deg) scale(1.1);
            background: linear-gradient(135deg, var(--accent-color), var(--secondary-color));
        }

        .contact-detail-card .content {
            flex: 1;
        }

        .contact-detail-card h3 {
            font-size: 1rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 0.3rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .contact-detail-card p,
        .contact-detail-card a {
            color: white;
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 0;
            text-decoration: none;
            line-height: 1.6;
        }

        .contact-detail-card a:hover {
            color: var(--secondary-color);
        }

        /* Right Side - Contact Form (IMPROVED) */
        .contact-form-side {
            padding: 3.5rem;
            background: white;
            position: relative;
            overflow: hidden;
        }

        .contact-form-side::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(242, 140, 40, 0.03) 0%, transparent 70%);
            border-radius: 50%;
        }

        .contact-form-side h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-family: 'Inter', sans-serif;
        }

        .contact-form-side p {
            color: var(--text-light);
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 1rem;
            transition: all 0.3s ease;
            z-index: 10;
        }

        .form-group.textarea-group i {
            top: 25px;
            transform: none;
        }

        .form-control {
            border: 2px solid var(--border-color);
            border-radius: 15px;
            padding: 1rem 1rem 1rem 3rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--light-color);
            width: 100%;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 4px rgba(242, 140, 40, 0.1);
            background: white;
            transform: translateY(-2px);
        }

        .form-control:focus+i {
            color: var(--secondary-color);
        }

        .form-control::placeholder {
            color: var(--text-light);
            opacity: 0.7;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 140px;
            padding-top: 1rem;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
            color: white;
            border: none;
            padding: 1.2rem 2rem;
            border-radius: 15px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.4s ease;
            width: 100%;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-submit:hover::before {
            left: 100%;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, var(--accent-color), var(--secondary-color));
            transform: translateY(-5px);
            box-shadow: var(--shadow-2xl);
        }

        .btn-submit i {
            margin-right: 0.5rem;
            transition: transform 0.3s ease;
        }

        .btn-submit:hover i {
            transform: translateX(10px) scale(1.2);
        }

        /* Alert Messages */
        .alert-custom {
            border-radius: 15px;
            padding: 1.2rem;
            margin-bottom: 2rem;
            animation: slideInDown 0.5s ease;
            border-left: 4px solid;
            font-weight: 500;
        }

        .alert-success-custom {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border-left-color: #28a745;
        }

        .alert-error-custom {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border-left-color: #dc3545;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ================= MAP SECTION ================= */
        .map-section {
            padding: 5rem 0 3rem;
            background: linear-gradient(135deg, #f8fafc, white);
        }

        .map-container {
            border-radius: 40px;
            overflow: hidden;
            box-shadow: var(--shadow-2xl);
            height: 450px;
            position: relative;
            border: 5px solid white;
        }

        .map-container iframe {
            width: 100%;
            height: 100%;
            border: 0;
            transition: transform 0.3s ease;
        }

        .map-container:hover iframe {
            transform: scale(1.02);
        }

        .map-overlay {
            position: absolute;
            bottom: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            border-radius: 50px;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255, 255, 255, 0.3);
            font-weight: 600;
            color: var(--primary-color);
        }

        .map-overlay i {
            color: var(--secondary-color);
            margin-right: 0.5rem;
        }

        /* =======================
   FEEDBACK CAROUSEL STYLES
======================= */
        .feedback-carousel-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 5rem 0;
            position: relative;
            overflow: hidden;
        }

        .feedback-carousel-section::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: var(--secondary-color);
            opacity: 0.05;
            border-radius: 50%;
            pointer-events: none;
        }

        .feedback-carousel-section::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: -50px;
            width: 300px;
            height: 300px;
            background: var(--primary-color);
            opacity: 0.05;
            border-radius: 50%;
            pointer-events: none;
        }

        .feedback-carousel .owl-stage-outer {
            padding: 20px 0;
        }

        .feedback-carousel .owl-item {
            transition: all 0.3s ease;
        }

        .feedback-carousel .owl-item:hover {
            transform: translateY(-5px);
        }

        .feedback-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .feedback-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--secondary-color), var(--accent-color));
        }

        .feedback-card:hover {
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            transform: translateY(-10px);
        }

        .feedback-rating {
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .feedback-stars {
            color: #ffc107;
            font-size: 1.1rem;
            letter-spacing: 2px;
        }

        .feedback-stars i {
            margin: 0 2px;
        }

        .feedback-badge {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 0.3rem 1rem;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 5px 10px rgba(40, 167, 69, 0.2);
        }

        .feedback-text {
            font-size: 1rem;
            line-height: 1.7;
            color: var(--text-color);
            margin-bottom: 1.5rem;
            font-style: italic;
            position: relative;
            padding-left: 1rem;
            border-left: 3px solid var(--secondary-color);
        }

        .feedback-author {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: auto;
        }

        .author-avatar {
            width: 55px;
            height: 55px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
            font-weight: 600;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .author-info h6 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.2rem;
        }

        .author-info small {
            color: var(--text-light);
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .author-info small i {
            color: var(--secondary-color);
        }

        .feedback-subject {
            display: inline-block;
            background: var(--light-color);
            padding: 0.3rem 1rem;
            border-radius: 30px;
            font-size: 0.8rem;
            color: var(--text-color);
            border: 1px solid var(--border-color);
            margin-top: 0.5rem;
        }

        /* Owl Carousel Navigation */
        .feedback-carousel .owl-nav {
            position: absolute;
            top: -60px;
            right: 0;
            display: flex;
            gap: 10px;
        }

        .feedback-carousel .owl-nav button {
            width: 45px;
            height: 45px;
            background: white !important;
            border-radius: 50% !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            color: var(--primary-color) !important;
            font-size: 1.2rem !important;
            transition: all 0.3s ease;
        }

        .feedback-carousel .owl-nav button:hover {
            background: var(--secondary-color) !important;
            color: white !important;
            transform: scale(1.1);
        }

        .feedback-carousel .owl-dots {
            text-align: center;
            margin-top: 30px;
        }

        .feedback-carousel .owl-dot span {
            width: 10px;
            height: 10px;
            margin: 5px;
            background: #ddd;
            border-radius: 50%;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .feedback-carousel .owl-dot.active span {
            background: var(--secondary-color);
            transform: scale(1.3);
        }

        .feedback-stats {
            background: white;
            border-radius: 60px;
            padding: 0.5rem 2rem;
            display: inline-flex;
            align-items: center;
            gap: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .feedback-stat-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.5rem 0;
        }

        .feedback-stat-item:not(:last-child) {
            border-right: 1px solid var(--border-color);
            padding-right: 2rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            line-height: 1;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .feedback-stats {
                flex-direction: column;
                border-radius: 30px;
                padding: 1.5rem;
                width: 70%;
                gap: 1rem;
            }

            .feedback-stat-item:not(:last-child) {
                border-right: none;
                border-bottom: 1px solid var(--border-color);
                padding-right: 0;
                padding-bottom: 1rem;
            }

            .feedback-carousel .owl-nav {
                position: static;
                justify-content: center;
                margin-top: 20px;
            }
        }

        /* Start side */
        .rounded-start-8 {
            border-start-start-radius: 8px !important;
            border-end-start-radius: 8px !important;
        }

        /* End side */
        .rounded-end-8 {
            border-start-end-radius: 15px !important;
            border-end-end-radius: 15px !important;
        }

        /* Remove start radius on sm+ */
        @media (min-width: 576px) {
            .rounded-start-sm-0 {
                border-start-start-radius: 0 !important;
                border-end-start-radius: 0 !important;
            }

            .rounded-end-sm-0 {
                border-start-end-radius: 0 !important;
                border-end-end-radius: 0 !important;
            }
        }
        }
    </style>
</head>

<body>
    <!-- Preloader with Logo Animation GIF -->
    <div id="preloader">
        <div class="preloader-content">
            <!-- Your Animated Logo GIF -->
            <img src="uploads/logogi.gif" alt="Travelcation" class="preloader-gif">

            <!-- Loading Text -->
            <div class="preloader-text">Loading Amazing Journeys...</div>

            <!-- Progress Bar -->
            <div class="preloader-progress">
                <div class="preloader-progress-bar"></div>
            </div>

            <!-- Animated Dots -->
            <div class="preloader-dots">
                <div class="preloader-dot"></div>
                <div class="preloader-dot"></div>
                <div class="preloader-dot"></div>
            </div>
        </div>
    </div>
    <!-- Top CTA Header -->
    <div class="top-cta" id="topCta">
        <div class="container d-flex justify-content-between align-items-center">
            <span class="cta-text">
                <span class="d-none d-md-inline">✈️ Flat 20% Off on International Packages!</span>
                <span class="d-md-none">✈️ Flat 20% Off on Intl Packages!</span>
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
                <img src="uploads/lg-tra (1).png" alt="Travelcation" class="img-fluid">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="home.php">Home</a>
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
                            <a class="dropdown-item" href="#packages">Packages</a>
                            <a class="dropdown-item" href="#hotels">Hotels</a>
                            <a class="dropdown-item" href="offers.php">Exclusive Offers</a>
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

    <!-- Hero Section -->
    <section class="hero-section" id="home">
        <!-- Background Carousel -->
        <div class="hero-background">
            <?php if (count($carousel_images) > 0): ?>
                <?php foreach ($carousel_images as $index => $image): ?>
                    <div class="bg-slide <?= $index === 0 ? 'active' : '' ?>"
                        style="background-image: url('<?= $image['image_url'] ?>');"></div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="bg-slide active"
                    style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--dark-color) 100%);"></div>
            <?php endif; ?>
        </div>

        <div class="hero-overlay"></div>

        <div class="container">
            <div class="hero-content fade-in">
                <h1 class="hero-title"><?= htmlspecialchars($hero_title) ?></h1>
                <p class="hero-description"><?= htmlspecialchars($hero_description) ?></p>
                <div class="d-flex flex-column flex-md-row gap-3">
                    <a href="<?= htmlspecialchars($hero_button_link) ?>" class="btn btn-primary btn-lg">
                        <?= htmlspecialchars($hero_button_text) ?>
                    </a>
                    <a href="#hotels" class="btn btn-outline btn-lg">
                        <i class="fas fa-compass me-2"></i>View All Hotels
                    </a>
                </div>
            </div>
        </div>


        <!-- Carousel Thumbnails -->
        <?php if (count($carousel_images) > 0): ?>
            <div class="carousel-thumbnails">
                <?php foreach ($carousel_images as $index => $image): ?>
                    <div class="thumbnail <?= $index === 0 ? 'active' : '' ?>" data-slide="<?= $index ?>">
                        <img src="<?= $image['thumbnail_url'] ?>" alt="<?= htmlspecialchars($image['alt_text']) ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- =======================
    POPULAR DESTINATIONS
  ======================= -->
    <section class="py-5 popular-destinations">
        <div class="container">

            <!-- Section heading -->
            <div class="text-center mb-5">
                <h2 class="section-title"><i class="fas fa-map-marked-alt"></i> Popular Destinations</h2>
                <p class="section-subtitle">
                    Explore our hand-picked locations across the world that travelers absolutely love.
                </p>
            </div>

            <!-- Destinations wrapper -->
            <div class="destinationsSwiper">
                <div class="swiper-wrapper">

                    <?php foreach ($destinations as $d):
                        $citySlug = strtolower(trim($d['title']));
                        ?>
                        <div class="swiper-slide">
                            <a href="destination.php?slug=<?= urlencode($citySlug) ?>" class="destination-card">

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
            <!-- View all -->
            <div class="text-center mt-5">
                <a href="alldestinations.php" class="btn btn-outline btn-lg">
                    <i class="fas fa-eye me-2"></i> View All Destinations
                </a>
            </div>
        </div>
    </section>
    <script>
        let destinationsSwiper;

        function initDestinationsSwiper() {
            if (window.innerWidth <= 768 && !destinationsSwiper) {
                destinationsSwiper = new Swiper(".destinationsSwiper", {
                    slidesPerView: 2.2,
                    spaceBetween: 16,
                    freeMode: true,
                    grabCursor: true,
                });
            }

            if (window.innerWidth > 768 && destinationsSwiper) {
                destinationsSwiper.destroy(true, true);
                destinationsSwiper = null;
            }
        }

        document.addEventListener("DOMContentLoaded", initDestinationsSwiper);
        window.addEventListener("resize", initDestinationsSwiper);
    </script>


    <section class="video-section">
        <video autoplay muted loop playsinline class="bg-video">
            <source src="uploads/329674_small.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>

        <div class="video-overlay">
            <h2>Explore The World With Us</h2>
            <p>Discover beautiful destinations & unforgettable experiences</p>
            <a href="#packages" class="btn btn-warning">View Packages</a>
        </div>
    </section>






    <!-- Hotels Section -->
    <section class="py-5" id="hotels">
        <div class="container">

            <div class="text-center mb-5">
                <h2 class="section-title" id="photels"><i class="fas fa-hotel"></i> Premium Hotel Collection</h2>
                <p class="lead">Luxurious accommodations for your perfect stay</p>
            </div>

            <!-- Swiper Container -->
            <div class="swiper hotelsSwiper">
                <div class="swiper-wrapper">

                    <?php while ($h = $hotelStmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <div class="swiper-slide">
                            <div class="hotel-card h-100">

                                <div class="card-img-container">
                                    <img src="uploads/<?= htmlspecialchars($h['image']) ?>"
                                        alt="<?= htmlspecialchars($h['hotel_name']) ?>" class="img-fluid">

                                    <div class="hotel-rating">
                                        <i class="fas fa-star"></i> <?= $h['rating'] ?? '4.5' ?>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <h3 class="card-title"><?= htmlspecialchars($h['hotel_name']) ?></h3>
                                    <p class="card-text"><?= htmlspecialchars($h['description']) ?></p>

                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div>
                                            <span class="fw-bold fs-5" style="color:#00A6C8;">
                                                ₹<?= number_format($h['price_per_night']) ?>
                                            </span>
                                            <span class="text-muted">/night</span>
                                        </div>
                                    </div>
                                    <div class="d-grid mt-3">
                                        <a href="hotel_details.php?id=<?= $h['id'] ?>" class="btn btn-primary">
                                            <i class="fas fa-eye me-2"></i> View Details
                                        </a>
                                    </div>


                                </div>

                            </div>
                        </div>
                    <?php endwhile; ?>

                </div>
            </div>

            <div class="text-center mt-5">
                <a href="hotels.php" class="btn btn-outline btn-lg">
                    <i class="fas fa-eye me-2"></i>View All Hotels
                </a>
            </div>

        </div>
    </section>
    <script>
        let hotelsSwiper;

        function initHotelsSwiper() {
            if (window.innerWidth <= 768 && !hotelsSwiper) {
                hotelsSwiper = new Swiper(".hotelsSwiper", {
                    slidesPerView: 1.2,
                    spaceBetween: 16,
                    freeMode: true,
                    grabCursor: true,
                });
            }

            if (window.innerWidth > 768 && hotelsSwiper) {
                hotelsSwiper.destroy(true, true);
                hotelsSwiper = null;
            }
        }

        document.addEventListener("DOMContentLoaded", initHotelsSwiper);
        window.addEventListener("resize", initHotelsSwiper);
    </script>

    <!-- =======================
      Packages Section
      ======================= -->
    <!-- =======================
    Packages Section
  ======================= -->
    <section class="py-5" id="packages">
        <div class="container">

            <!-- Section heading -->
            <div class="text-center mb-5">
                <h2 class="section-title"><i class="fas fa-gem"></i> Exclusive Offers & Packages</h2>
                <p class="lead">
                    Exclusive travel experiences designed just for you. From adventure to luxury.
                </p>
            </div>

            <!-- Packages wrapper -->
            <div class="packagesSwiper">
                <div class="swiper-wrapper">

                    <?php while ($pkg = $packageStmt->fetch(PDO::FETCH_ASSOC)):
                        $featuresRaw = $pkg['features'];
                        if (strpos($featuresRaw, '[') === 0) {
                            $features = json_decode($featuresRaw, true);
                            if (!is_array($features)) {
                                $features = [];
                            }
                        } else {
                            $features = array_map('trim', explode(',', $featuresRaw));
                        }
                        ?>
                        <div class="swiper-slide">
                            <div class="package-card h-100">

                                <!-- Image -->
                                <div class="card-img-container">
                                    <img src="uploads/<?= htmlspecialchars($pkg['image']) ?>"
                                        alt="<?= htmlspecialchars($pkg['title']) ?>" class="img-fluid">
                                    <div class="package-price">₹<?= number_format($pkg['price']) ?></div>
                                </div>

                                <!-- Content -->
                                <div class="card-body">
                                    <h3 class="card-title"><?= htmlspecialchars($pkg['title']) ?></h3>
                                    <p class="card-text"><?= htmlspecialchars($pkg['description']) ?></p>



                                    <div class="d-grid mt-3">
                                        <a href="package_details.php?id=<?= $pkg['id'] ?>" class="btn btn-primary">
                                            <i class="fas fa-eye me-2"></i> View Details
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    <?php endwhile; ?>

                </div>
            </div>

            <!-- View all -->
            <div class="text-center mt-5">
                <a href="packages.php" class="btn btn-outline btn-lg">
                    <i class="fas fa-eye me-2"></i> View All Packages
                </a>
            </div>

        </div>
    </section>

    <script>
        let packagesSwiper;

        function initPackagesSwiper() {
            if (window.innerWidth <= 768 && !packagesSwiper) {
                packagesSwiper = new Swiper(".packagesSwiper", {
                    slidesPerView: 1.2,
                    spaceBetween: 16,
                    freeMode: true,
                    grabCursor: true,
                });
            }

            if (window.innerWidth > 768 && packagesSwiper) {
                packagesSwiper.destroy(true, true);
                packagesSwiper = null;
            }
        }

        document.addEventListener("DOMContentLoaded", initPackagesSwiper);
        window.addEventListener("resize", initPackagesSwiper);
    </script>
    <!-- =======================
      Other Services Section with Interactive Cards
      ======================= -->
    <section class="py-5 bg-light" id="other-services">
        <div class="container">

            <!-- Section heading -->
            <div class="text-center mb-5">
                <h2 class="section-title"><i class="fas fa-concierge-bell"></i> Other Travel Services</h2>
                <p class="lead">
                    Comprehensive travel solutions beyond just packages. We handle all your travel needs.
                </p>
            </div>

            <div class="row g-4">

                <!-- Passport Services Card -->
                <div class="col-md-4">
                    <div class="service-card h-100">
                        <div class="card-header bg-primary text-white py-3">
                            <h3 class="mb-0"><i class="fas fa-passport me-2"></i> Passport Services</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">Complete assistance for all passport-related needs</p>

                            <div class="service-list mb-4">
                                <div class="service-item">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span><strong>Fresh Passport</strong> - New application assistance</span>
                                </div>
                                <div class="service-item">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span><strong>Renew Passport</strong> - Expired passport renewal</span>
                                </div>
                                <div class="service-item">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span><strong>Damaged Passport</strong> - Replacement for damaged passports</span>
                                </div>
                                <div class="service-item">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span><strong>Lost Passport</strong> - Assistance for lost/stolen passports</span>
                                </div>
                                <div class="service-item">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span><strong>Urgent Passport</strong> - Tatkaal/express passport services</span>
                                </div>
                                <div class="service-item">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span><strong>Non ECR Passport</strong> - ECR to Non-ECR category change</span>
                                </div>
                                <div class="service-item">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span><strong>Change In Personal Particulars</strong> - Name, address updates</span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 mb-3">
                                <button class="btn btn-primary btn-enquire-primary flex-grow-1"
                                    onclick="openEnquiryModal('passport')">
                                    <i class="fas fa-envelope me-2"></i> Enquire Now
                                </button>
                                <a href="tel:+919876543210" class="btn btn-outline-primary">
                                    <i class="fas fa-phone-alt"></i>
                                </a>
                            </div>

                            <!-- Quick Enquiry Form (Hidden by default) -->
                            <div id="passport-enquiry" class="enquiry-form mt-3" style="display: none;">
                                <form method="POST" action="">
                                    <input type="hidden" name="service_type" value="passport">
                                    <div class="mb-3">
                                        <label class="form-label">Select Service</label>
                                        <select class="form-select" name="package_name" required>
                                            <option value="">Choose a service...</option>
                                            <option value="Fresh Passport">Fresh Passport</option>
                                            <option value="Renew Passport">Renew Passport</option>
                                            <option value="Damaged Passport">Damaged Passport</option>
                                            <option value="Lost Passport">Lost Passport</option>
                                            <option value="Urgent Passport">Urgent Passport</option>
                                            <option value="Non ECR Passport">Non ECR Passport</option>
                                            <option value="Change in Personal Particulars">Change in Personal
                                                Particulars</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <input type="text" class="form-control" name="full_name"
                                            placeholder="Your Full Name"
                                            value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <input type="email" class="form-control" name="email" placeholder="Your Email"
                                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <input type="tel" class="form-control" name="phone" placeholder="Phone Number"
                                            value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <input type="date" class="form-control" name="travel_date"
                                            value="<?= htmlspecialchars($_POST['travel_date'] ?? '') ?>"
                                            min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <input type="text" class="form-control" name="travelers"
                                            placeholder="Number of travelers"
                                            value="<?= htmlspecialchars($_POST['travelers'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <textarea class="form-control" name="message" rows="2"
                                            placeholder="Additional details..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                                    </div>
                                    <button type="submit" name="submit_service_enquiry"
                                        class="btn btn-primary w-100">Submit Enquiry</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visa Services Card -->
                <div class="col-md-4">
                    <div class="service-card h-100">
                        <div class="card-header bg-success text-white py-3">
                            <h3 class="mb-0"><i class="fas fa-stamp me-2"></i> Visa Services</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">End-to-end visa assistance for all destinations</p>

                            <div class="service-list mb-4">
                                <div class="service-item">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span><strong>Tourist Visa Assistance</strong> - Leisure travel visas</span>
                                </div>
                                <div class="service-item">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span><strong>Business Visa Services</strong> - Corporate travel visas</span>
                                </div>

                                <div class="service-item">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span><strong>Visa Documentation & Processing</strong> - Complete paperwork
                                        help</span>
                                </div>
                                <div class="service-item">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span><strong>Urgent / Express Visa Services</strong> - Fast-track processing</span>
                                </div>

                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 mb-3">
                                <button class="btn btn-success flex-grow-1" onclick="openEnquiryModal('visa')">
                                    <i class="fas fa-envelope me-2"></i> Enquire Now
                                </button>
                                <a href="tel:+919876543210" class="btn btn-outline-success">
                                    <i class="fas fa-phone-alt"></i>
                                </a>
                            </div>

                            <!-- Quick Enquiry Form (Hidden by default) -->
                            <div id="visa-enquiry" class="enquiry-form mt-3" style="display: none;">
                                <form method="POST" action="">
                                    <input type="hidden" name="service_type" value="visa">
                                    <div class="mb-3">
                                        <label class="form-label">Select Service</label>
                                        <select class="form-select" name="package_name" required>
                                            <option value="">Choose a service...</option>
                                            <option value="Tourist Visa">Tourist Visa Assistance</option>
                                            <option value="Business Visa">Business Visa Services</option>

                                            <option value="Visa Documentation">Visa Documentation & Processing</option>
                                            <option value="Urgent Visa">Urgent / Express Visa Services</option>

                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <input type="text" class="form-control" name="full_name"
                                            placeholder="Your Full Name"
                                            value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <input type="email" class="form-control" name="email" placeholder="Your Email"
                                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <input type="tel" class="form-control" name="phone" placeholder="Phone Number"
                                            value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <input type="date" class="form-control" name="travel_date"
                                            value="<?= htmlspecialchars($_POST['travel_date'] ?? '') ?>"
                                            min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <input type="text" class="form-control" name="travelers"
                                            placeholder="Number of travelers"
                                            value="<?= htmlspecialchars($_POST['travelers'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <textarea class="form-control" name="message" rows="2"
                                            placeholder="Additional details..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                                    </div>
                                    <button type="submit" name="submit_service_enquiry"
                                        class="btn btn-success w-100">Submit Enquiry</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Travel Agent Services Card - INSURANCE SERVICES REMOVED -->
                <div class="col-md-4">
                    <div class="service-card h-100">
                        <div class="card-header bg-info text-white py-3">
                            <h3 class="mb-0"><i class="fas fa-briefcase me-2"></i> Travel Agent Services</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">Additional travel services to complete your journey</p>

                            <div class="service-list mb-4">
                                <div class="service-item">
                                    <i class="fas fa-shield-alt text-info me-2"></i>
                                    <span><strong>Travel Insurance Assistance</strong> - Comprehensive coverage
                                        plans</span>
                                </div>
                                <!-- REMOVED: Medical Travel Insurance -->
                                <!-- REMOVED: Baggage Insurance -->
                                <!-- REMOVED: Trip Cancellation Insurance -->
                                <div class="service-item">
                                    <i class="fas fa-plane text-info me-2"></i>
                                    <span><strong>Flight Bookings</strong> - Domestic & international flights</span>
                                </div>
                                <div class="service-item">
                                    <i class="fas fa-plane text-info me-2"></i>
                                    <span><strong>Group Flight Bookings</strong> - Corporate & family bookings</span>
                                </div>
                                <div class="service-item">
                                    <i class="fas fa-hotel text-info me-2"></i>
                                    <span><strong>Hotel Bookings</strong> - Worldwide accommodation</span>
                                </div>
                                <div class="service-item">
                                    <i class="fas fa-car text-info me-2"></i>
                                    <span><strong>Car Rentals</strong> - Vehicle hire services</span>
                                </div>
                                <div class="service-item">
                                    <i class="fas fa-umbrella-beach text-info me-2"></i>
                                    <span><strong>Holiday Packages</strong> - Customized tour packages</span>
                                </div>
                                <div class="service-item">
                                    <i class="fas fa-ship text-info me-2"></i>
                                    <span><strong>Cruise Bookings</strong> - Luxury cruise vacations</span>
                                </div>
                                <div class="service-item">
                                    <i class="fas fa-train text-info me-2"></i>
                                    <span><strong>Rail Bookings</strong> - Train ticket reservations</span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 mb-3">
                                <button class="btn btn-info flex-grow-1" onclick="openEnquiryModal('travel')">
                                    <i class="fas fa-envelope me-2"></i> Enquire Now
                                </button>
                                <a href="tel:+919876543210" class="btn btn-outline-info">
                                    <i class="fas fa-phone-alt"></i>
                                </a>
                            </div>

                            <!-- Travel Enquiry Form - UPDATED for other_service table -->
                            <div id="travel-enquiry" class="enquiry-form mt-3" style="display: none;">

                                <?php if ($enquiry_success && isset($_POST['service_type']) && $_POST['service_type'] == 'travel'): ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>
                                        Thank you for your enquiry! Our travel agent will contact you within 24 hours.
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>

                                <?php if ($enquiry_error && isset($_POST['service_type']) && $_POST['service_type'] == 'travel'): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <?= $enquiry_error ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" action="">
                                    <input type="hidden" name="service_type" value="travel">

                                    <div class="mb-3">
                                        <label class="form-label">Select Service *</label>
                                        <select class="form-select" name="package_name" required>
                                            <option value="">Choose a service...</option>
                                            <option value="Travel Insurance Assistance">Travel Insurance Assistance
                                            </option>
                                            <option value="Flight Bookings">Flight Bookings</option>
                                            <option value="Group Flight Bookings">Group Flight Bookings</option>
                                            <option value="Hotel Bookings">Hotel Bookings</option>
                                            <option value="Car Rentals">Car Rentals</option>
                                            <option value="Holiday Packages">Holiday Packages</option>
                                            <option value="Cruise Bookings">Cruise Bookings</option>
                                            <option value="Rail Bookings">Rail Bookings</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <input type="text" class="form-control" name="full_name"
                                            placeholder="Your Full Name *"
                                            value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <input type="email" class="form-control" name="email" placeholder="Your Email *"
                                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <input type="tel" class="form-control" name="phone" placeholder="Phone Number *"
                                            value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <input type="date" class="form-control" name="travel_date"
                                            value="<?= htmlspecialchars($_POST['travel_date'] ?? '') ?>"
                                            min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                                        <small class="text-muted">Travel date (optional)</small>
                                    </div>

                                    <div class="mb-3">
                                        <input type="text" class="form-control" name="travelers"
                                            placeholder="Number of travelers"
                                            value="<?= htmlspecialchars($_POST['travelers'] ?? '') ?>">
                                        <small class="text-muted">Number of travelers (optional)</small>
                                    </div>

                                    <div class="mb-3">
                                        <textarea class="form-control" name="message" rows="2"
                                            placeholder="Additional details..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                                    </div>

                                    <button type="submit" name="submit_service_enquiry" class="btn btn-info w-100">
                                        <i class="fas fa-paper-plane me-2"></i> Submit Enquiry
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Additional CTA -->
            <div class="text-center mt-5">
                <p class="mb-3">Need personalized assistance for your travel documents?</p>
                <a href="contact.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-headset me-2"></i> Consult Our Travel Experts
                </a>
            </div>

        </div>
    </section>

    <!-- =======================
    WHY CHOOSE US SECTION (Static)
======================= -->
    <section class="about-section">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <h2 class="section-title"><i class="fas fa-thumbs-up"></i> Why Choose Travelcation?</h2>
                    <div class="d-flex flex-column gap-3 mt-4">
                        <div class="d-flex align-items-center">
                            <div style="min-width: 40px;"><i class="fas fa-check-circle text-success fs-4"></i></div>
                            <div><strong class="me-1">Trusted travel agency since 2012</strong> — over a decade of
                                excellence.</div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div style="min-width: 40px;"><i class="fas fa-check-circle text-success fs-4"></i></div>
                            <div><strong class="me-1">Complete travel solutions under one roof</strong> — packages,
                                hotels, flights, passport, visa.</div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div style="min-width: 40px;"><i class="fas fa-check-circle text-success fs-4"></i></div>
                            <div><strong class="me-1">Customized packages for individuals and groups</strong> — we
                                tailor to your needs.</div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div style="min-width: 40px;"><i class="fas fa-check-circle text-success fs-4"></i></div>
                            <div><strong class="me-1">Competitive pricing and transparent service</strong> — no hidden
                                costs.</div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div style="min-width: 40px;"><i class="fas fa-check-circle text-success fs-4"></i></div>
                            <div><strong class="me-1">Expert support for passport & visa processes</strong> —
                                hassle-free documentation.</div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div style="min-width: 40px;"><i class="fas fa-check-circle text-success fs-4"></i></div>
                            <div><strong class="me-1">Dedicated customer service</strong> for a hassle-free experience.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <img src="uploads/dishant.jpeg" alt="Travelcation Team" class="img-fluid shadow-lg"
                        style="border-radius:100%;height:600px; width:550px;">
                    <h4 style="text-align:center; margin-top:3%;">Dishant Bhagat </h4>
                    <h4 style="text-align:center; margin-top:3%;">CEO | Founder </h4>
                </div>
            </div>
    </section>
    <!-- =======================
    CUSTOMER FEEDBACK CAROUSEL
======================= -->
    <section class="feedback-carousel-section" id="feedbacks">
        <div class="container">
            <!-- Section Header -->
            <div class="text-center mb-4">
                <h2 class="section-title"><i class="fas fa-star text-warning"></i> What Our Travelers Say</h2>
                <p class="lead">Real experiences from our happy customers around the world</p>
            </div>

            <!-- Feedback Stats -->
            <?php if ($total_reviews > 0): ?>
                <div class="d-flex justify-content-center mb-5">
                    <div class="feedback-stats">
                        <div class="feedback-stat-item">
                            <span class="stat-number"><?= $avg_rating ?></span>
                            <div class="stat-label">
                                <div class="text-warning mb-1">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= round($avg_rating)): ?>
                                            <i class="fas fa-star fa-sm"></i>
                                        <?php else: ?>
                                            <i class="far fa-star fa-sm"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                Average Rating
                            </div>
                        </div>
                        <div class="feedback-stat-item">
                            <span class="stat-number"><?= $total_reviews ?></span>
                            <div class="stat-label">Total Reviews</div>
                        </div>
                        <div class="feedback-stat-item">
                            <span class="stat-number"><?= $ratingStats['five_star'] ?? 0 ?></span>
                            <div class="stat-label">5-Star Reviews</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($published_feedbacks)): ?>
                <!-- Feedback Carousel -->
                <div class="owl-carousel owl-theme feedback-carousel">
                    <?php foreach ($published_feedbacks as $feedback): ?>
                        <div class="item">
                            <div class="feedback-card">
                                <!-- Rating -->
                                <div class="feedback-rating">
                                    <div class="feedback-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $feedback['rating']): ?>
                                                <i class="fas fa-star"></i>
                                            <?php else: ?>
                                                <i class="far fa-star"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="feedback-badge">
                                        <i class="fas fa-check-circle me-1"></i> Verified
                                    </span>
                                </div>

                                <!-- Feedback Message -->
                                <p class="feedback-text">
                                    "<?= htmlspecialchars(substr($feedback['message'], 0, 200)) ?><?= strlen($feedback['message']) > 200 ? '...' : '' ?>"
                                </p>

                                <!-- Author Info -->
                                <div class="feedback-author">
                                    <div class="author-avatar">
                                        <?= strtoupper(substr($feedback['name'], 0, 1)) ?>
                                    </div>
                                    <div class="author-info">
                                        <h6><?= htmlspecialchars($feedback['name']) ?></h6>
                                        <small>
                                            <i class="far fa-calendar-alt"></i>
                                            <?= date('M d, Y', strtotime($feedback['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>

                                <!-- Subject (if exists) -->
                                <?php if (!empty($feedback['subject'])): ?>
                                    <div class="feedback-subject mt-3">
                                        <i class="fas fa-tag me-1"></i> <?= htmlspecialchars($feedback['subject']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Call to Action Buttons -->
                <div
                    class="text-center mt-5 d-flex flex-column gap-2 flex-md-row align-items-center justify-content-center">
                    <a href="submit_feedback.php" class="btn btn-feedback-primary btn-primary me-2">
                        <i class="fas fa-pen-fancy me-2"></i>Write a Review
                    </a>
                    <a href="submit_feedback.php#testimonials-section" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-eye me-2"></i>Read All Reviews
                    </a>
                </div>

            <?php else: ?>
                <!-- No Feedbacks Yet -->
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-star fa-4x text-warning opacity-50"></i>
                    </div>
                    <h4 class="text-muted mb-3">No Reviews Yet</h4>
                    <p class="mb-4">Be the first to share your travel experience with us!</p>
                    <a href="submit_feedback.php#feedback" class="btn btn-primary btn-lg">
                        <i class="fas fa-pen-fancy me-2"></i>Write Your Review
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <!-- =======================
    QUOTE / TAGLINE SECTION
======================= -->
    <section class="quote-section">
        <div class="container">
            <i class="fas fa-quote-left fa-3x mb-3"></i>
            <h3>We don't just plan trips — we manage your entire travel journey from start to finish.</h3>
            <p class="mt-3 fs-4">Let us turn your travel dreams into reality <i class="fas fa-plane"></i> <i
                    class="fas fa-globe"></i></p>
        </div>
    </section>
    <!-- Contact Section (IMPROVED) -->
    <section class="contact-section" id="contact-section">
        <div class="container">
            <div class="contact-wrapper" data-aos="fade-up">
                <div class="row g-0">
                    <!-- Left Side - Contact Info (IMPROVED) -->
                    <div class="col-lg-5">
                        <div class="contact-info-side">
                            <h2><i class="fas fa-address-card"></i> Connect With Us</h2>

                            <div class="contact-detail-card" data-aos="fade-right" data-aos-delay="100">
                                <div class="icon-wrapper">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="content">
                                    <h3>Address</h3>
                                    <p>214, Oberon, Opp. Mercedes-Benz Showroom,<br>New City Light Road, Surat – 395017
                                    </p>
                                </div>
                            </div>

                            <div class="contact-detail-card" data-aos="fade-right" data-aos-delay="200">
                                <div class="icon-wrapper">
                                    <i class="fas fa-phone-alt"></i>
                                </div>
                                <div class="content">
                                    <h3>Phone</h3>
                                    <p><a href="tel:+919033186905">+91-90331 86905</a></p>
                                </div>
                            </div>

                            <div class="contact-detail-card" data-aos="fade-right" data-aos-delay="300">
                                <div class="icon-wrapper">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="content">
                                    <h3>Email</h3>
                                    <p><a href="mailto:travelcation.co.in">travelcation.co.in</a></p>
                                </div>
                            </div>

                            <div class="contact-detail-card" data-aos="fade-right" data-aos-delay="400">
                                <div class="icon-wrapper">
                                    <i class="fas fa-globe"></i>
                                </div>
                                <div class="content">
                                    <h3>Website</h3>
                                    <p><a href="https://travelcation.co.in" target="_blank">travelcation.co.in</a></p>
                                </div>
                            </div>

                            <!-- Social Media Links -->
                            <div class="d-flex gap-3 mt-4 align-items-center justify-content-center" data-aos="fade-up"
                                data-aos-delay="500">
                                <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                                <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                                <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                                <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side - Contact Form (IMPROVED) -->
                    <div class="col-lg-7">
                        <div class="contact-form-side">
                            <h2 data-aos="fade-left">Send Us a Message</h2>
                            <p data-aos="fade-left" data-aos-delay="100">Have questions? We'd love to hear from you.
                                Send us a message and we'll respond as soon as possible.</p>

                            <?php if ($contact_success): ?>
                                <div class="alert-custom alert-success-custom" data-aos="fade-up">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <?= htmlspecialchars($contact_message) ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($contact_error): ?>
                                <div class="alert-custom alert-error-custom" data-aos="fade-up">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <?= $contact_error ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="" class="contact-form" data-aos="fade-left"
                                data-aos-delay="200">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <i class="fas fa-user"></i>
                                            <input type="text" class="form-control" name="full_name"
                                                placeholder="Your Full Name *" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <i class="fas fa-envelope"></i>
                                            <input type="email" class="form-control" name="email"
                                                placeholder="Your Email *" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <i class="fas fa-phone-alt"></i>
                                            <input type="tel" class="form-control" name="phone"
                                                placeholder="Your Phone Number">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <i class="fas fa-tag"></i>
                                            <input type="text" class="form-control" name="subject"
                                                placeholder="Subject">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group textarea-group">
                                            <i class="fas fa-comment"></i>
                                            <textarea class="form-control" name="message" rows="5"
                                                placeholder="Your Message *" required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" name="submit_contact" class="btn-submit">
                                            <i class="fas fa-paper-plane"></i> Send Message
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="container">
            <div class="map-container" data-aos="zoom-in">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3536.986712233326!2d72.79851049999999!3d21.1482659!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be0535482a6d31d%3A0x168767862d155ac5!2sOberon%20Business%20Complex!5e1!3m2!1sen!2sin!4v1771697007199!5m2!1sen!2sin"
                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;"
                    allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                    title="Travelcation Office at Oberon Business Complex, Surat">
                </iframe>
                <div class="map-overlay">
                    <i class="fas fa-map-pin"></i> 214, Oberon, Surat - 395017
                </div>
            </div>
        </div>
    </section>


    <script>
        let packagesSlider;

        function initPackagesSlider() {
            if (window.innerWidth <= 768 && !packagesSlider) {
                packagesSlider = new Swiper(".packagesSwiper", {
                    slidesPerView: 1.2,
                    spaceBetween: 16,
                    freeMode: true,
                    grabCursor: true,
                });
            }

            if (window.innerWidth > 768 && packagesSlider) {
                packagesSlider.destroy(true, true);
                packagesSlider = null;
            }
        }

        // Function to open enquiry form
        function openEnquiryModal(serviceType) {
            // Hide all enquiry forms first
            document.querySelectorAll('.enquiry-form').forEach(form => {
                form.style.display = 'none';
            });

            // Show the selected form
            const form = document.getElementById(serviceType + '-enquiry');
            if (form) {
                form.style.display = 'block';
                // Smooth scroll to form
                form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            initPackagesSlider();

            // Auto-show form if there was an error with travel enquiry
            <?php if (isset($_POST['service_type']) && $_POST['service_type'] == 'travel' && !$enquiry_success): ?>
                document.getElementById('travel-enquiry').style.display = 'block';
            <?php endif; ?>

            // Auto-show passport form if there was an error
            <?php if (isset($_POST['service_type']) && $_POST['service_type'] == 'passport' && !$enquiry_success): ?>
                document.getElementById('passport-enquiry').style.display = 'block';
            <?php endif; ?>

            // Auto-show visa form if there was an error
            <?php if (isset($_POST['service_type']) && $_POST['service_type'] == 'visa' && !$enquiry_success): ?>
                document.getElementById('visa-enquiry').style.display = 'block';
            <?php endif; ?>

            // Auto-hide alerts after 5 seconds
            setTimeout(function () {
                document.querySelectorAll('.alert').forEach(function (alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);

            // Optional: Add click outside to close forms
            document.addEventListener('click', function (event) {
                if (!event.target.closest('.enquiry-form') && !event.target.closest('.btn')) {
                    document.querySelectorAll('.enquiry-form').forEach(form => {
                        form.style.display = 'none';
                    });
                }
            });
        });

        window.addEventListener("resize", initPackagesSlider);
    </script>


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
                        <input type="email"
                            class="rounded-end-8 rounded-end-sm-0 form-control bg-dark text-white border-secondary"
                            placeholder="Your email address">
                        <button class="btn btn-primary mt-sm-0 mt-2 rounded-start-8 rounded-start-sm-0" type="button">
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

    <!-- Enquiry Modal -->
    <div class="modal fade enquiry-modal" id="enquiryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-paper-plane me-2"></i> Travel Enquiry Form</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <?php if ($modal_enquiry_success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= htmlspecialchars($modal_enquiry_message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!$modal_enquiry_success && $modal_enquiry_error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= $modal_enquiry_error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="modal_full_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="modal_full_name" name="modal_full_name"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label for="modal_email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="modal_email" name="modal_email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="modal_phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="modal_phone" name="modal_phone" required>
                            </div>
                            <div class="col-md-6">
                                <label for="modal_package" class="form-label">Package Interested In</label>
                                <input type="text" class="form-control" id="modal_package" name="modal_package"
                                    placeholder="Enter package name (optional)">
                            </div>
                            <div class="col-md-6">
                                <label for="modal_travel_date" class="form-label">Travel Date *</label>
                                <input type="date" class="form-control" id="modal_travel_date" name="modal_travel_date"
                                    min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="modal_travelers" class="form-label">Number of Travelers *</label>
                                <select class="form-select" id="modal_travelers" name="modal_travelers" required>
                                    <option value="">Select</option>
                                    <option value="1">1 Traveler</option>
                                    <option value="2" selected>2 Travelers</option>
                                    <option value="3">3 Travelers</option>
                                    <option value="4">4 Travelers</option>
                                    <option value="5+">5+ Travelers</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="modal_message" class="form-label">Additional Requirements</label>
                                <textarea class="form-control" id="modal_message" name="modal_message" rows="4"
                                    placeholder="Please share any specific requirements or questions..."></textarea>
                            </div>
                        </div>

                        <input type="hidden" name="source" id="enquiry_source" value="navbar">

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

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Handle navbar enquiry button
        document.querySelector('.btn-primary[data-bs-target="#enquiryModal"]').addEventListener('click', function () {
            document.getElementById('enquiry_source').value = 'navbar';
            document.getElementById('modal_package').value = ''; // Clear the field for manual entry
            document.getElementById('modal_package').removeAttribute('readonly'); // Ensure it's editable
        });

        // Handle package enquiry buttons
        document.querySelectorAll('.enquire-package-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const packageTitle = this.getAttribute('data-package-title');
                document.getElementById('modal_package').value = packageTitle;
                document.getElementById('modal_package').setAttribute('readonly', true); // Make readonly when auto-filled
                document.getElementById('enquiry_source').value = 'packages';
            });
        });

        // Handle hotel enquiry buttons
        document.querySelectorAll('.enquire-hotel-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const hotelName = this.getAttribute('data-hotel-name');
                document.getElementById('modal_package').value = 'Hotel: ' + hotelName;
                document.getElementById('modal_package').setAttribute('readonly', true); // Make readonly when auto-filled
                document.getElementById('enquiry_source').value = 'hotels';
            });
        });
        // Professional JavaScript for enhanced functionality
        document.addEventListener('DOMContentLoaded', function () {
            // Navbar scroll effect
            window.addEventListener('scroll', function () {
                const navbar = document.querySelector('.navbar');
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });

            // Carousel functionality
            const slides = document.querySelectorAll('.bg-slide');
            const thumbnails = document.querySelectorAll('.thumbnail');
            let currentSlide = 0;

            function showSlide(index) {
                slides.forEach(slide => slide.classList.remove('active'));
                thumbnails.forEach(thumb => thumb.classList.remove('active'));

                if (slides[index]) {
                    slides[index].classList.add('active');
                }
                if (thumbnails[index]) {
                    thumbnails[index].classList.add('active');
                }

                currentSlide = index;
            }

            thumbnails.forEach((thumb, index) => {
                thumb.addEventListener('click', () => {
                    showSlide(index);
                });
            });

            // Auto slide every 5 seconds
            if (slides.length > 1) {
                setInterval(() => {
                    let nextSlide = (currentSlide + 1) % slides.length;
                    showSlide(nextSlide);
                }, 5000);
            }

            // Set minimum date for travel date
            const today = new Date().toISOString().split('T')[0];
            const travelDateInputs = document.querySelectorAll('input[type="date"]');
            travelDateInputs.forEach(input => {
                if (!input.value) {
                    input.min = today;
                }
            });

            // Add smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    const href = this.getAttribute('href');
                    if (href === '#') return;

                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        window.scrollTo({
                            top: target.offsetTop - 80,
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
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
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
                    }, 300);
                });
            });
        });
    </script>

    <?php if ($enquiry_success && isset($_POST['service_type']) && $_POST['service_type'] == 'travel'): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.getElementById('travel-enquiry').style.display = 'block';
                setTimeout(function () {
                    document.querySelectorAll('.alert').forEach(function (alert) {
                        var bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    });
                }, 5000);
            });
        </script>
    <?php endif; ?>
    <script>
        window.addEventListener("pageshow", function (event) {
            // Fix for back/forward cache
            document.body.classList.remove("page-exit");
            document.body.classList.add("page-loaded");
        });
    </script>
    <script>
        window.addEventListener("pageshow", function (event) {
            // Fix for back/forward cache
            document.body.classList.remove("page-exit");
            document.body.classList.add("page-loaded");
        });
        // Preloader functionality
        window.addEventListener('load', function () {
            // Wait for everything to load
            setTimeout(function () {
                const preloader = document.getElementById('preloader');
                if (preloader) {
                    preloader.classList.add('hidden');

                    // Optional: Remove preloader from DOM after animation
                    setTimeout(function () {
                        preloader.style.display = 'none';
                    }, 800);
                }
            }, 2500); // Adjust time as needed (2500ms = 2.5 seconds)
        });

        // Alternative: Hide preloader when page is fully loaded
        // This will hide immediately when everything is loaded
        document.addEventListener('DOMContentLoaded', function () {
            // You can use this if you don't want a minimum display time
            // But better to use the load event above
        });
    </script>

    <!-- Replace jQuery slim with full version -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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