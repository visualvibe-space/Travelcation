<?php
require_once __DIR__ . '/config/config.php';
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ============================
   HANDLE FEEDBACK SUBMISSION
============================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $rating = intval($_POST['rating'] ?? 0);
    $message = trim($_POST['message'] ?? '');
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $page_url = $_SERVER['REQUEST_URI'] ?? '';
    
    $errors = [];
    $success = false;
    
    // Validation
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($message)) {
        $errors[] = "Feedback message is required";
    }
    
    if ($rating < 1 || $rating > 5) {
        $errors[] = "Please select a valid rating";
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO feedback (
                    name, email, phone, subject, message, rating, status,
                    ip_address, user_agent, page_url, created_at, updated_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, 'New',
                    ?, ?, ?, NOW(), NOW()
                )
            ");
            
            $result = $stmt->execute([
                $name, $email, $phone, $subject, $message, $rating,
                $ip_address, $user_agent, $page_url
            ]);
            
            if ($result) {
                $success = true;
                $success_message = "Thank you for your valuable feedback! Your input helps us improve our services.";
            } else {
                $errors[] = "Failed to submit feedback. Please try again.";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error occurred. Please try again.";
            error_log($e->getMessage());
        }
    }
}

/* ============================
   HANDLE MODAL ENQUIRY SUBMISSION
============================ */
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

/* ============================
   FETCH PUBLISHED FEEDBACKS
============================ */
$stmt = $pdo->query("
    SELECT * FROM feedback 
    WHERE status = 'Published' 
    ORDER BY created_at DESC 
    LIMIT 9
");
$published_feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get rating statistics
$rating_stats = $pdo->query("
    SELECT 
        COUNT(*) as total,
        AVG(rating) as avg_rating,
        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
        SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
    FROM feedback 
    WHERE status = 'Published'
")->fetch(PDO::FETCH_ASSOC);

$total_reviews = $rating_stats['total'] ?? 0;
$avg_rating = $rating_stats['avg_rating'] ? number_format($rating_stats['avg_rating'], 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Travelcation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Share your travel experience with us - your feedback helps us serve you better">
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
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
            padding-top:15%;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('uploads/feedback-bg.jpg') center/cover no-repeat;
            opacity: 0.1;
            z-index: 0;
        }

        .page-header .container {
            position: relative;
            z-index: 1;
        }
        
        #enquiryModal .modal-dialog {
            margin-top: 10%;
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

        /* Feedback Stats Section */
        .stats-section {
            padding: 4rem 0;
            background: var(--light-color);
        }

        .stat-card {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--secondary-color), #f39c12);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: var(--white);
            font-size: 1.8rem;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-light);
            font-weight: 500;
        }

        /* Rating Progress Bars */
        .rating-breakdown {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: var(--shadow-md);
        }

        .rating-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .rating-label {
            min-width: 60px;
            font-weight: 600;
        }

        .rating-bar {
            flex: 1;
            height: 8px;
            background: var(--border-color);
            border-radius: 4px;
            overflow: hidden;
        }

        .rating-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--secondary-color), #f39c12);
            border-radius: 4px;
        }

        .rating-percent {
            min-width: 50px;
            text-align: right;
            font-weight: 600;
            color: var(--text-color);
        }

        /* Feedback Form */
        .feedback-section {
            padding: 5rem 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            position: relative;
            overflow: hidden;
        }

        .feedback-section::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: var(--secondary-color);
            opacity: 0.05;
            border-radius: 50%;
        }

        .feedback-section::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: -50px;
            width: 300px;
            height: 300px;
            background: var(--primary-color);
            opacity: 0.05;
            border-radius: 50%;
        }

        .feedback-card {
            background: var(--white);
            border-radius: 20px;
            box-shadow: var(--shadow-xl);
            overflow: hidden;
        }

        .feedback-header {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: var(--white);
            padding: 2rem;
            text-align: center;
        }

        .feedback-header h3 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .feedback-body {
            padding: 2.5rem;
        }

        /* Rating Stars */
        .rating-stars {
            font-size: 2.5rem;
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 0.5rem;
        }

        .rating-input {
            display: none;
        }

        .rating-label {
            cursor: pointer;
            color: #ddd;
            transition: all 0.2s ease;
        }

        .rating-label:hover,
        .rating-label:hover ~ .rating-label,
        .rating-input:checked ~ .rating-label {
            color: #ffc107;
        }

        /* Input Groups */
        .input-group-text {
            border: none;
            background: var(--light-color);
            border-radius: 8px 0 0 8px;
            color: var(--secondary-color);
        }

        .form-control, .form-select {
            border: 1px solid var(--border-color);
            border-left: none;
            border-radius: 0 8px 8px 0;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(0, 166, 200, 0.1);
            outline: none;
        }

        .is-invalid {
            border-color: #dc3545 !important;
        }
        .is-invalid:focus {
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
        }

        /* Testimonial Cards */
        .testimonials-section {
            padding: 5rem 0;
            background: var(--white);
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1rem;
            text-align: center;
        }

        .section-subtitle {
            color: var(--text-light);
            font-size: 1.1rem;
            text-align: center;
            margin-bottom: 3rem;
        }

        .testimonial-card {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid var(--border-color);
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--secondary-color);
        }

        .testimonial-rating {
            color: #ffc107;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .testimonial-text {
            font-style: italic;
            color: var(--text-color);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.2rem;
            font-weight: 600;
        }

        .author-info h6 {
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.2rem;
        }

        .author-info small {
            color: var(--text-light);
            font-size: 0.85rem;
        }

        /* Alerts */
        .alert {
            border-radius: 10px;
            border: none;
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Footer */
        footer {
            background: linear-gradient(135deg, var(--dark-color), #0F172A);
            color: var(--white);
            padding: 4rem 0 2rem;
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
            transition: all 0.3s ease;
        }

        .btn-back-to-top:hover {
            background: var(--primary-color);
            transform: translateY(-3px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header {
                padding: 100px 0 40px;
            }
            
            .page-title {
                font-size: 2.2rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .feedback-body {
                padding: 1.5rem;
            }
            
            .rating-stars {
                font-size: 2rem;
            }
        }

        @media (max-width: 576px) {
            .stat-card {
                margin-bottom: 1rem;
            }
            
            .rating-stars {
                font-size: 1.8rem;
                gap: 0.2rem;
            }
        }
    </style>
</head>

<body>

<!-- Top CTA Header -->
<div class="top-cta" id="topCta">
    <div class="container d-flex justify-content-between align-items-center">
        <span>
            <i class="fas fa-heart me-2"></i>Share Your Experience & Help Us Improve!
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
                <h1 class="page-title animate__animated animate__fadeInDown">Share Your Feedback</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb page-breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Submit Feedback</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-number"><?= $avg_rating ?></div>
                    <div class="stat-label">Average Rating</div>
                    <div class="mt-2 text-warning">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <?php if($i <= round($avg_rating)): ?>
                                <i class="fas fa-star"></i>
                            <?php else: ?>
                                <i class="far fa-star"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number"><?= $total_reviews ?></div>
                    <div class="stat-label">Total Reviews</div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-smile"></i>
                    </div>
                    <div class="stat-number"><?= $rating_stats['five_star'] ?? 0 ?></div>
                    <div class="stat-label">5-Star Reviews</div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-number">100%</div>
                    <div class="stat-label">Satisfaction Rate</div>
                </div>
            </div>
        </div>
        
        <?php if($total_reviews > 0): ?>
        <div class="row mt-5">
            <div class="col-lg-8 mx-auto">
                <div class="rating-breakdown">
                    <h5 class="text-center mb-4">Rating Breakdown</h5>
                    
                    <?php 
                    $ratings = [
                        5 => $rating_stats['five_star'] ?? 0,
                        4 => $rating_stats['four_star'] ?? 0,
                        3 => $rating_stats['three_star'] ?? 0,
                        2 => $rating_stats['two_star'] ?? 0,
                        1 => $rating_stats['one_star'] ?? 0
                    ];
                    
                    foreach($ratings as $stars => $count):
                        $percentage = $total_reviews > 0 ? round(($count / $total_reviews) * 100) : 0;
                    ?>
                    <div class="rating-row">
                        <div class="rating-label"><?= $stars ?> Star</div>
                        <div class="rating-bar">
                            <div class="rating-fill" style="width: <?= $percentage ?>%"></div>
                        </div>
                        <div class="rating-percent"><?= $percentage ?>%</div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Feedback Form Section -->
<section class="feedback-section" id="feedback">
    <div class="container">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php foreach($errors as $error): ?>
                    <?= $error ?><br>
                <?php endforeach; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success) && $success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?= $success_message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="feedback-card">
                    <div class="feedback-header">
                        <h3><i class="fas fa-pen-fancy me-2"></i>We Value Your Opinion</h3>
                        <p class="mb-0">Your feedback helps us create better travel experiences</p>
                    </div>
                    
                    <div class="feedback-body">
                        <form method="POST" action="" id="feedbackForm">
                            <!-- Rating -->
                            <div class="mb-4 text-center">
                                <label class="form-label fw-bold mb-3">How would you rate your experience? *</label>
                                <div class="rating-stars">
                                    <input type="radio" name="rating" value="5" id="star5" class="rating-input" required>
                                    <label for="star5" class="rating-label"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" name="rating" value="4" id="star4" class="rating-input">
                                    <label for="star4" class="rating-label"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" name="rating" value="3" id="star3" class="rating-input">
                                    <label for="star3" class="rating-label"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" name="rating" value="2" id="star2" class="rating-input">
                                    <label for="star2" class="rating-label"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" name="rating" value="1" id="star1" class="rating-input">
                                    <label for="star1" class="rating-label"><i class="fas fa-star"></i></label>
                                </div>
                                <div id="rating-error" class="text-danger small mt-2" style="display: none;">Please select a rating</div>
                            </div>
                            
                            <div class="row g-4">
                                <!-- Name -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Your Full Name *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="feedback-name" name="name" 
                                               placeholder="Enter your full name" required value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                                    </div>
                                </div>
                                
                                <!-- Email -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Email Address *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" id="feedback-email" name="email" 
                                               placeholder="Enter your email" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                                    </div>
                                </div>
                                
                                <!-- Phone (Optional) -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Phone Number <span class="text-muted">(Optional)</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        <input type="tel" class="form-control" id="feedback-phone" name="phone" 
                                               placeholder="Enter your phone number" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                                    </div>
                                </div>
                                
                                <!-- Subject -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Subject <span class="text-muted">(Optional)</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                        <input type="text" class="form-control" id="feedback-subject" name="subject" 
                                               placeholder="e.g., Tour Experience, Hotel Stay" value="<?= isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : '' ?>">
                                    </div>
                                </div>
                                
                                <!-- Message -->
                                <div class="col-12">
                                    <label class="form-label fw-bold">Your Feedback *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-comment"></i></span>
                                        <textarea class="form-control" id="feedback-message" name="message" 
                                                  rows="5" placeholder="Please share your detailed experience with us..." required><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
                                    </div>
                                </div>
                                
                                <!-- Submit Button -->
                                <div class="col-12 text-center mt-4">
                                    <button type="submit" name="submit_feedback" class="btn btn-primary btn-lg px-5">
                                        <i class="fas fa-paper-plane me-2"></i>Submit Feedback
                                    </button>
                                </div>
                                
                                <div class="col-12">
                                    <p class="text-muted small text-center mb-0">
                                        <i class="fas fa-lock me-1"></i>
                                        Your feedback is valuable and helps us improve our services
                                    </p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<?php if (!empty($published_feedbacks)): ?>
<section class="testimonials-section" id="testimonials-section">
    <div class="container">
        <h2 class="section-title">What Our Travelers Say</h2>
        <p class="section-subtitle">Real experiences from our happy customers around the world</p>
        
        <div class="row g-4">
            <?php foreach($published_feedbacks as $feedback): ?>
            <div class="col-lg-4 col-md-6">
                <div class="testimonial-card">
                    <div class="testimonial-rating">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <?php if($i <= $feedback['rating']): ?>
                                <i class="fas fa-star"></i>
                            <?php else: ?>
                                <i class="far fa-star"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    
                    <p class="testimonial-text">"<?= htmlspecialchars(substr($feedback['message'], 0, 150)) ?>..."</p>
                    
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <?= strtoupper(substr($feedback['name'], 0, 1)) ?>
                        </div>
                        <div class="author-info">
                            <h6><?= htmlspecialchars($feedback['name']) ?></h6>
                            <small><i class="far fa-calendar-alt me-1"></i> <?= date('M d, Y', strtotime($feedback['created_at'])) ?></small>
                        </div>
                    </div>
                    
                    <?php if (!empty($feedback['subject'])): ?>
                    <div class="mt-3">
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-tag me-1"></i> <?= htmlspecialchars($feedback['subject']) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (count($published_feedbacks) >= 9): ?>
        <div class="text-center mt-5">
            <a href="all-testimonials.php" class="btn btn-outline-primary">
                <i class="fas fa-star me-2"></i>View All Testimonials
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

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
                            <input type="text" class="form-control" id="modal_full_name" name="modal_full_name" required>
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
                            <input type="text" class="form-control" id="modal_package" name="modal_package" placeholder="Enter package name (optional)">
                        </div>
                        <div class="col-md-6">
                            <label for="modal_travel_date" class="form-label">Travel Date *</label>
                            <input type="date" class="form-control" id="modal_travel_date" name="modal_travel_date" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
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
                            <textarea class="form-control" id="modal_message" name="modal_message" rows="4" placeholder="Please share any specific requirements or questions..."></textarea>
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
    
    if (header) {
        window.addEventListener("scroll", () => {
            const headerHeight = header.offsetHeight;
            const hidePoint = headerHeight * 0.3;
            
            if (window.scrollY > hidePoint) {
                cta.classList.add("hidden");
                navbar.classList.add("sticky");
            } else {
                cta.classList.remove("hidden");
                navbar.classList.remove("sticky");
            }
        });
    }

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

    // Set min date for travel date
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    const travelDate = document.getElementById('travelDate');
    if (travelDate) {
        travelDate.min = tomorrow.toISOString().split('T')[0];
    }

    // Feedback form validation
    document.getElementById('feedbackForm')?.addEventListener('submit', function(e) {
        const rating = document.querySelector('input[name="rating"]:checked');
        const name = document.getElementById('feedback-name').value.trim();
        const email = document.getElementById('feedback-email').value.trim();
        const message = document.getElementById('feedback-message').value.trim();
        
        let isValid = true;
        
        // Validate rating
        if (!rating) {
            document.getElementById('rating-error').style.display = 'block';
            isValid = false;
        } else {
            document.getElementById('rating-error').style.display = 'none';
        }
        
        // Validate name
        if (!name) {
            document.getElementById('feedback-name').classList.add('is-invalid');
            isValid = false;
        } else {
            document.getElementById('feedback-name').classList.remove('is-invalid');
        }
        
        // Validate email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email || !emailRegex.test(email)) {
            document.getElementById('feedback-email').classList.add('is-invalid');
            isValid = false;
        } else {
            document.getElementById('feedback-email').classList.remove('is-invalid');
        }
        
        // Validate message
        if (!message) {
            document.getElementById('feedback-message').classList.add('is-invalid');
            isValid = false;
        } else {
            document.getElementById('feedback-message').classList.remove('is-invalid');
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });

    // Real-time rating display
    document.querySelectorAll('.rating-label').forEach(label => {
        label.addEventListener('mouseover', function() {
            // Optional: Show rating text on hover
        });
        
        label.addEventListener('mouseout', function() {
            // Optional: Remove hover effect
        });
    });

    // Modal enquiry form handling
    document.querySelector('.btn-primary[data-bs-target="#enquiryModal"]').addEventListener('click', function() {
        document.getElementById('enquiry_source').value = 'navbar';
        document.getElementById('modal_package').value = '';
        document.getElementById('modal_package').removeAttribute('readonly');
    });

    // Page transition for links
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

    // Fix for back/forward cache
    window.addEventListener("pageshow", function (event) {
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