<?php
require_once __DIR__ . '/config/config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ========================
   GET PACKAGE ID
======================== */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid package');
}

$packageId = (int)$_GET['id'];

/* ========================
   FETCH PACKAGE DETAILS
======================== */
$stmt = $pdo->prepare("
    SELECT * FROM tour_packages 
    WHERE id = ? AND status = 'Active'
    LIMIT 1
");
$stmt->execute([$packageId]);
$pkg = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pkg) {
    die('Package not found');
}

/* ========================
   FEATURES HANDLING (WITH NULL CHECK)
======================== */
$features = [];
if (isset($pkg['features']) && !empty($pkg['features'])) {
    $featuresRaw = $pkg['features'];
    if (strpos($featuresRaw, '[') === 0) {
        $features = json_decode($featuresRaw, true) ?? [];
    } else {
        $features = array_map('trim', explode(',', $featuresRaw));
    }
}

/* ========================
   LOCATIONS COVERED HANDLING
======================== */
$locations = [];
if (isset($pkg['locations_covered']) && !empty($pkg['locations_covered'])) {
    $locationsRaw = $pkg['locations_covered'];
    if (strpos($locationsRaw, '[') === 0) {
        $locations = json_decode($locationsRaw, true) ?? [];
    } else {
        $locations = array_map('trim', explode(',', $locationsRaw));
    }
}

/* ========================
   INCLUSIONS HANDLING
======================== */
$inclusions = [];
if (isset($pkg['inclusions']) && !empty($pkg['inclusions'])) {
    $inclusionsRaw = $pkg['inclusions'];
    if (strpos($inclusionsRaw, '[') === 0) {
        $inclusions = json_decode($inclusionsRaw, true) ?? [];
    } else {
        $inclusions = array_map('trim', explode(',', $inclusionsRaw));
    }
}

/* ========================
   FETCH OTHER PACKAGES
======================== */
$otherStmt = $pdo->prepare("
    SELECT id, title, image, price, description, features, duration, days, nights 
    FROM tour_packages 
    WHERE status = 'Active' AND id != ?
    ORDER BY id DESC
    LIMIT 4
");
$otherStmt->execute([$packageId]);
$otherPackages = $otherStmt->fetchAll(PDO::FETCH_ASSOC);

/* ========================
   HANDLE ENQUIRY SUBMISSION TO enquiries TABLE
======================== */
$enquiry_success = false;
$enquiry_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_enquiry'])) {
    
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $package_name = trim($_POST['package_name'] ?? $pkg['title']);
    $travel_date = trim($_POST['travel_date'] ?? '');
    $travelers = trim($_POST['travelers'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $source = 'package_details';
    
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
                $package_name,
                !empty($travel_date) ? $travel_date : null,
                $travelers,
                !empty($message) ? $message : null,
                $source
            ]);
            
            // Set success flag
            $enquiry_success = true;
            
        } catch (PDOException $e) {
            $enquiry_error = 'Failed to submit enquiry. Please try again.';
        }
    } else {
        $enquiry_error = implode('<br>', $errors);
    }
}

// Get hero carousel images
$carousel_images = $pdo->query("SELECT * FROM hero_carousel WHERE is_active = 1 ORDER BY display_order, created_at ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pkg['title']) ?> | Travelcation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars(substr($pkg['description'], 0, 150)) ?>">
    
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
        /* Package Hero Banner - FIXED */
        .package-hero-banner {
            position: relative;
            min-height: 70vh;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7));
            overflow: hidden;
            margin-top: 40px;
            padding-top: 72px;
            display: flex;
            align-items: center;
        }

        .package-hero-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('uploads/<?= htmlspecialchars($pkg['image']) ?>') center/cover no-repeat;
            z-index: -1;
            animation: zoomEffect 20s infinite alternate;
        }

        @keyframes zoomEffect {
            0% { transform: scale(1); }
            100% { transform: scale(1.1); }
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(42, 67, 101, 0.9) 0%, rgba(26, 32, 44, 0.9) 100%);
            display: flex;
            align-items: center;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            color: var(--white);
            max-width: 800px;
            padding: 2rem 0;
        }

        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 1.5rem;
        }

        .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .breadcrumb-item a:hover {
            color: var(--secondary-color);
        }

        .breadcrumb-item.active {
            color: var(--secondary-color);
            font-weight: 600;
        }

        .package-title {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.2;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            margin-top:5%;
        }

        @media (max-width: 768px) {
            .package-title {
                font-size: 2.5rem;
            }
        }

        .package-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .badge-custom {
            padding: 0.5rem 1.25rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
        }

        .badge-custom i {
            margin-right: 0.5rem;
        }

        .package-price-tag {
            font-size: 2rem;
            font-weight: 700;
            color: var(--secondary-color);
            background: rgba(255, 255, 255, 0.15);
            padding: 0.75rem 2rem;
            border-radius: 50px;
            display: inline-block;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 2rem;
        }

        /* Package Details Container */
        .package-container {
            position: relative;
            margin-top: -50px;
            z-index: 10;
        }

        .package-main-card {
            background: var(--white);
            border-radius: 20px;
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            margin-bottom: 4rem;
        }

        .package-image-main {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }

        @media (max-width: 768px) {
            .package-image-main {
                height: 250px;
            }
        }

        .package-content-wrapper {
            padding: 2.5rem;
        }

        @media (max-width: 768px) {
            .package-content-wrapper {
                padding: 1.5rem;
            }
        }

        /* Section Titles */
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.75rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--secondary-color), var(--accent-color));
            border-radius: 2px;
        }

        .section-title.center {
            text-align: center;
        }

        .section-title.center::after {
            left: 50%;
            transform: translateX(-50%);
        }

        /* Description */
        .description-content {
            font-size: 1rem;
            line-height: 1.7;
            color: var(--text-color);
            margin-bottom: 2rem;
        }

        /* Package Stats Bar */
        .package-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            background: var(--light-color);
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            background: var(--white);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-color);
            font-size: 1.25rem;
            box-shadow: var(--shadow-sm);
        }

        .stat-content h6 {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-content p {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
        }

        /* Locations Grid */
        .locations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 0.75rem;
            margin: 1.5rem 0 2rem;
        }

        .location-item {
            background: var(--light-color);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
        }

        .location-item:hover {
            border-color: var(--secondary-color);
            transform: translateX(3px);
        }

        .location-item i {
            color: var(--secondary-color);
            font-size: 1rem;
        }

        .location-item span {
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text-color);
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0 2rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: var(--white);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .feature-item:hover {
            border-color: var(--accent-color);
            box-shadow: var(--shadow-md);
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            background: rgba(0, 166, 200, 0.1);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-color);
        }

        .feature-content h6 {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0;
        }

        /* Inclusions Grid */
        .inclusions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .inclusion-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            background: var(--white);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .inclusion-item:hover {
            border-color: var(--success-color);
            background: rgba(56, 161, 105, 0.05);
        }

        .inclusion-item i {
            color: var(--success-color);
            font-size: 1rem;
        }

        .inclusion-item span {
            font-size: 0.95rem;
            color: var(--text-color);
        }

        /* Sidebar */
        .sidebar-wrapper {
            position: sticky;
            top: 120px;
        }

        .sidebar-card {
            background: var(--white);
            border-radius: 15px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
        }

        .sidebar-card .card-body {
            padding: 1.5rem;
        }

        /* Price Box */
        .price-box {
            text-align: center;
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 12px;
            color: var(--white);
            margin-bottom: 1.5rem;
        }

        .price-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
        }

        .price-amount {
            font-size: 2.25rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .price-type {
            font-size: 0.85rem;
            opacity: 0.9;
        }

        /* Quick Info */
        .quick-info {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem;
            background: var(--light-color);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .info-row i {
            width: 35px;
            height: 35px;
            background: var(--white);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-color);
            font-size: 1rem;
        }

        .info-row .info-text {
            flex: 1;
        }

        .info-row .info-text small {
            font-size: 0.8rem;
            color: var(--text-light);
            display: block;
        }

        .info-row .info-text span {
            font-weight: 600;
            color: var(--primary-color);
        }

        /* Trust Badges */
        .trust-badges {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .trust-badge {
            background: var(--light-color);
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            border: 1px solid var(--border-color);
        }

        .trust-badge i {
            font-size: 1.5rem;
            color: var(--accent-color);
            margin-bottom: 0.5rem;
        }

        .trust-badge p {
            font-size: 0.8rem;
            font-weight: 600;
            margin: 0;
            color: var(--primary-color);
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

        /* Package Cards */
        .package-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            box-shadow: var(--shadow-md);
            background: var(--white);
            border: 1px solid var(--border-color);
        }

        .package-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
            border-color: var(--accent-color);
        }

        .card-img-container {
            position: relative;
            overflow: hidden;
            height: 200px;
        }

        .card-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .package-card:hover .card-img-container img {
            transform: scale(1.05);
        }

        .package-price-small {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: var(--white);
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 1rem;
            font-weight: 700;
            color: var(--accent-color);
            box-shadow: var(--shadow-sm);
        }

        .card-body {
            padding: 1.25rem;
        }

        .card-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .card-duration {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-bottom: 0.75rem;
        }

        .card-duration i {
            color: var(--accent-color);
            margin-right: 0.3rem;
        }

        .card-text {
            color: var(--text-light);
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .feature-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.3rem;
            margin-bottom: 1rem;
        }

        .feature-badge {
            background: var(--light-color);
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }

        .feature-badge i {
            color: var(--success-color);
            margin-right: 0.2rem;
            font-size: 0.7rem;
        }

        /* Footer */
        footer {
            background: linear-gradient(135deg, var(--dark-color), #0F172A);
            color: var(--white);
            padding: 4rem 0 2rem;
            margin-top: 4rem;
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

        /* Responsive */
        @media (max-width: 768px) {
            .package-hero-banner {
                min-height: 60vh;
                padding-top: 64px;
            }
            .top-cta{
                display:none;
            }
            .package-title {
                font-size: 2.5rem;
            }
            
            .package-price-tag {
                font-size: 1.5rem;
                padding: 0.5rem 1.5rem;
            }
            
            .section-title {
                font-size: 1.5rem;
            }
            
            .package-stats {
                flex-direction: column;
                gap: 1rem;
            }
            
            .sidebar-wrapper {
                position: static;
                margin-top: 2rem;
            }
            
            .locations-grid,
            .features-grid,
            .inclusions-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .package-title {
                font-size: 2rem;
            }
            
            .package-content-wrapper {
                padding: 1.25rem;
            }
            
            .stat-item {
                width: 100%;
            }
            
            .trust-badges {
                grid-template-columns: 1fr;
            }
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
            .hero-overlay{
                margin-top:10%;
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
                        <a class="dropdown-item active" href="home.php#packages">Packages</a>
                        <a class="dropdown-item" href="home.php#hotels">Hotels</a>
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
                  <button class="btn btn-primary ms-lg-3 mt-3 mt-lg-0" data-bs-toggle="modal" data-bs-target="#enquiryModal">
                      <i class="fas fa-paper-plane me-2"></i>Quick Enquiry
                  </button>
              </div>
          </div>
      </nav>

<!-- Package Hero Banner -->
<section class="package-hero-banner">
    <div class="hero-overlay">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="hero-content fade-in">
                        
                        <h1 class="package-title"><?= htmlspecialchars($pkg['title']) ?></h1>
                        
                        <div class="package-badges">
                            <span class="badge-custom">
                                <i class="fas fa-globe"></i> <?= htmlspecialchars($pkg['package_type']) ?>
                            </span>
                            <span class="badge-custom">
                                <i class="fas fa-clock"></i> <?= htmlspecialchars($pkg['duration']) ?>
                            </span>
                        </div>
                        
                        <div class="package-price-tag">
                            <i class="fas fa-tag me-2"></i> 
                            <?php if ($pkg['price_type'] == 'starting_from'): ?>
                                From ₹<?= number_format($pkg['price']) ?>
                            <?php else: ?>
                                ₹<?= number_format($pkg['price']) ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-flex flex-wrap gap-3">
                            <button class="btn btn-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#enquiryModal">
                                <i class="fas fa-calendar-check me-2"></i> Book Now
                            </button>
                            <a href="#details" class="btn btn-outline-primary">
                                <i class="fas fa-info-circle me-2"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Package Details -->
<section id="details" class="package-container">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <div class="package-main-card">
                    <!-- Main Image -->
                    <img src="uploads/<?= htmlspecialchars($pkg['image']) ?>" 
                         alt="<?= htmlspecialchars($pkg['title']) ?>" 
                         class="package-image-main">
                    
                    <!-- Package Content -->
                    <div class="package-content-wrapper">
                        <h2 class="section-title">Overview</h2>
                        <div class="description-content">
                            <?= nl2br(htmlspecialchars($pkg['description'])) ?>
                        </div>

                        <!-- Package Stats -->
                        <div class="package-stats">
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stat-content">
                                    <h6>Duration</h6>
                                    <p><?= htmlspecialchars($pkg['duration']) ?>
                                    <?php if ($pkg['days'] && $pkg['nights']): ?>
                                        <small style="font-size: 0.85rem; color: var(--text-light);">(<?= $pkg['days'] ?>D/<?= $pkg['nights'] ?>N)</small>
                                    <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-content">
                                    <h6>Min. People</h6>
                                    <p><?= $pkg['min_people'] ?? '1' ?>+ Travelers</p>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-tag"></i>
                                </div>
                                <div class="stat-content">
                                    <h6>Price Type</h6>
                                    <p><?= $pkg['price_type'] == 'starting_from' ? 'Starting From' : 'Fixed Price' ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Locations Covered -->
                        <?php if (!empty($locations)): ?>
                        <h2 class="section-title">Locations Covered</h2>
                        <div class="locations-grid">
                            <?php foreach ($locations as $location): ?>
                                <div class="location-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?= htmlspecialchars($location) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Features/Highlights -->
                        <?php if (!empty($features)): ?>
                        <h2 class="section-title">Highlights</h2>
                        <div class="features-grid">
                            <?php foreach ($features as $feature): ?>
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <div class="feature-content">
                                        <h6><?= htmlspecialchars($feature) ?></h6>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Inclusions -->
                        <?php if (!empty($inclusions)): ?>
                        <h2 class="section-title">Inclusions</h2>
                        <div class="inclusions-grid">
                            <?php foreach ($inclusions as $inclusion): ?>
                                <div class="inclusion-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span><?= htmlspecialchars($inclusion) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="sidebar-wrapper">
                    <div class="sidebar-card">
                        <div class="card-body">
                            <div class="price-box">
                                <div class="price-label">Package Price</div>
                                <div class="price-amount">
                                    <?php if ($pkg['price_type'] == 'starting_from'): ?>
                                        ₹<?= number_format($pkg['price']) ?>+
                                    <?php else: ?>
                                        ₹<?= number_format($pkg['price']) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="price-type">
                                    <?= $pkg['price_type'] == 'starting_from' ? 'Starting from per person' : 'Fixed price per person' ?>
                                </div>
                            </div>
                            
                            <button class="btn btn-primary w-100 mb-3"
                                    data-bs-toggle="modal"
                                    data-bs-target="#enquiryModal">
                                <i class="fas fa-paper-plane me-2"></i> Enquire Now
                            </button>
                            
                            <a href="tel:+919033186905" class="btn btn-outline-primary w-100">
                                <i class="fas fa-phone-alt me-2"></i> Call for Details
                            </a>
                            
                            <div class="trust-badges">
                                <div class="trust-badge">
                                    <i class="fas fa-shield-alt"></i>
                                    <p>Secure Booking</p>
                                </div>
                                <div class="trust-badge">
                                    <i class="fas fa-headset"></i>
                                    <p>24/7 Support</p>
                                </div>
                                <div class="trust-badge">
                                    <i class="fas fa-undo-alt"></i>
                                    <p>Free Cancellation</p>
                                </div>
                                <div class="trust-badge">
                                    <i class="fas fa-wallet"></i>
                                    <p>Best Price</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Video Section -->
<section class="video-section">
    <video autoplay muted loop playsinline class="bg-video">
        <source src="uploads/package.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <div class="video-overlay">
        <h2>Explore The World With Us</h2>
        <p>Discover beautiful destinations & unforgettable experiences</p>
        <a href="packages.php" class="btn-warning">Explore Packages</a>
    </div>
</section>

<!-- Other Packages Section -->
<?php if (!empty($otherPackages)): ?>
<section class="py-5 bg-light" id="otherpackage">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="section-title center">You May Also Like</h2>
            <p class="text-muted">Discover more amazing travel experiences</p>
        </div>

        <div class="row g-4">
            <?php foreach ($otherPackages as $op): 
                $otherFeatures = [];
                if (isset($op['features']) && !empty($op['features'])) {
                    $otherFeaturesRaw = $op['features'];
                    if (strpos($otherFeaturesRaw, '[') === 0) {
                        $otherFeatures = json_decode($otherFeaturesRaw, true) ?? [];
                    } else {
                        $otherFeatures = array_map('trim', explode(',', $otherFeaturesRaw));
                    }
                }
                $otherFeatures = array_slice($otherFeatures, 0, 3);
            ?>
                <div class="col-md-6 col-lg-3">
                    <div class="package-card">
                        <div class="card-img-container">
                            <img src="uploads/<?= htmlspecialchars($op['image']) ?>"
                                 alt="<?= htmlspecialchars($op['title']) ?>">
                            <div class="package-price-small">₹<?= number_format($op['price']) ?></div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($op['title']) ?></h5>
                            <?php if (!empty($op['duration'])): ?>
                            <div class="card-duration">
                                <i class="fas fa-clock"></i> <?= htmlspecialchars($op['duration']) ?>
                            </div>
                            <?php endif; ?>
                            <p class="card-text">
                                <?= htmlspecialchars(substr($op['description'], 0, 70)) ?>...
                            </p>
                            
                            <?php if (!empty($otherFeatures)): ?>
                            <div class="feature-badges">
                                <?php foreach ($otherFeatures as $feature): ?>
                                    <span class="feature-badge">
                                        <i class="fas fa-check-circle"></i>
                                        <?= htmlspecialchars($feature) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <a href="package_details.php?id=<?= $op['id'] ?>" 
                               class="btn btn-outline-primary btn-sm w-100">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="all_packages.php" class="btn btn-primary px-4">
                <i class="fas fa-compass me-2"></i> View All Packages
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Enquiry Modal -->
<div class="modal fade" id="enquiryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-paper-plane me-2"></i> Travel Enquiry</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                
                <?php if ($enquiry_success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    Thank you for your enquiry! Our team will contact you within 24 hours.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($enquiry_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= $enquiry_error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" name="full_name" 
                                   value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone *</label>
                            <input type="tel" class="form-control" name="phone" 
                                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Package</label>
                            <input type="text" class="form-control" name="package_name" id="package_name" 
                                   value="<?= htmlspecialchars($pkg['title']) ?>" placeholder="Enter package name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Travel Date *</label>
                            <input type="date" class="form-control" name="travel_date" 
                                   value="<?= htmlspecialchars($_POST['travel_date'] ?? '') ?>" 
                                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Travelers *</label>
                            <select class="form-select" name="travelers" required>
                                <option value="">Select</option>
                                <?php 
                                $traveler_options = ['1', '2', '3', '4', '5+'];
                                $selected_travelers = $_POST['travelers'] ?? '';
                                foreach ($traveler_options as $option): 
                                ?>
                                    <option value="<?= $option ?>" <?= $selected_travelers == $option ? 'selected' : '' ?>>
                                        <?= $option ?> <?= $option == '1' ? 'Traveler' : 'Travelers' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Message</label>
                            <textarea class="form-control" name="message" rows="3" 
                                      placeholder="Any specific requirements or questions..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" name="submit_enquiry" class="btn btn-primary">Submit Enquiry</button>
                    </div>
                </form>
                
                <div class="mt-3 text-muted small">
                    <i class="fas fa-lock me-1"></i> Your information is secure and will not be shared with third parties.
                </div>
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

<!-- Back to Top Button -->
<button class="btn-back-to-top" id="backToTop">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // CTA and Navbar scroll effect
    const cta = document.getElementById("topCta");
    const navbar = document.querySelector(".navbar");
    const heroSection = document.querySelector(".package-hero-banner");
    
    window.addEventListener("scroll", () => {
        if (heroSection) {
            const heroHeight = heroSection.offsetHeight;
            const hidePoint = heroHeight * 0.3;
            
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

    // Handle modal button clicks for package field behavior
    document.querySelectorAll('[data-bs-target="#enquiryModal"]').forEach(btn => {
        btn.addEventListener('click', function() {
            const packageField = document.getElementById('package_name');
            // If it's the navbar button, make it editable and set to current package
            if (this.closest('.navbar')) {
                packageField.removeAttribute('readonly');
                packageField.value = '<?= htmlspecialchars($pkg['title']) ?>';
                packageField.placeholder = 'Enter package name';
            } else {
                // For package-specific buttons, set the value and make it readonly
                packageField.setAttribute('readonly', true);
                packageField.value = '<?= htmlspecialchars($pkg['title']) ?>';
            }
        });
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
</script>

<?php if ($enquiry_success): ?>
<script>
    // Auto-show modal on success
    document.addEventListener('DOMContentLoaded', function() {
        var enquiryModal = new bootstrap.Modal(document.getElementById('enquiryModal'));
        enquiryModal.show();
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
 <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>