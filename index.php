<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/svgs.php'; ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travelcation - Your Gateway to Amazing Journeys</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="32x32" href="uploads/lg-tra (1).png">

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
            --shadow-xl: 0 20px 25px rgba(0, 0, 0, 0.15);
            --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);

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

        /* Navbar dropdown background */
        .navbar .dropdown-menu {
            background-color: #ffffff !important;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-lg);
            padding: 0.5rem 0;
            z-index: 1055;
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

        .navbar .dropdown-menu {
            background-color: #ffffff !important;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-lg);
            padding: 0.5rem 0;
            z-index: 1055;
            border-radius: 8px;
            margin-top: 0.5rem;
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


        .navbar .dropdown-item {
            color: var(--text-color);
            font-weight: 500;
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

        /* ================= HERO SECTION ================= */
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
            animation: fadeInUp 1s ease;
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
            animation: fadeInUp 1s ease 0.2s both;
            font-weight: 400;
        }

        .hero-buttons {
            animation: fadeInUp 1s ease 0.4s both;
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
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

        .footer-newsletter input {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--white);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .footer-newsletter input:focus {
            outline: none;
            border-color: var(--secondary-color);
            background: rgba(255, 255, 255, 0.12);
        }

        .footer-newsletter input::placeholder {
            color: #64748b;
        }

        .footer-newsletter .btn-primary {
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
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

        @media (max-width: 768px) {
            footer {
                padding: 3rem 0 1.5rem;
            }

            .footer-title {
                font-size: 1.1rem;
                margin-bottom: 1rem;
            }

            .footer-links a {
                font-size: 0.9rem;
            }

            .contact-info li {
                font-size: 0.85rem;
            }

            .social-links {
                justify-content: flex-start;
            }

            .footer-bottom {
                margin-top: 2rem;
                padding-top: 1rem;
            }
        }

        /* ================= PRELOADER STYLES ================= */
        #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 99999;
            background-color: white;
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

        /* ================= DECORATIVE AEROPLANES ================= */
        .deco-plane-wrap {
            position: absolute;
            z-index: 2;
            pointer-events: none;
            opacity: 0.13;
        }

        .deco-plane-left {
            bottom: 10%;
            left: -70px;
            animation: planeDrift 8s ease-in-out infinite alternate;
        }

        .deco-plane-right {
            top: 15%;
            right: -70px;
            animation: planeDriftReverse 9s ease-in-out infinite alternate-reverse;
        }

        .deco-plane-wrap svg {
            width: 400px;
            height: auto;
        }

        .has-plane-deco {
            position: relative;
            overflow: hidden;
        }

        @keyframes planeDrift {
            0% {
                transform: translateY(0px) translateX(0px);
            }

            50% {
                transform: translateY(-18px) translateX(8px);
            }

            100% {
                transform: translateY(-8px) translateX(-4px);
            }
        }

        @keyframes planeDriftReverse {
            0% {
                transform: scaleX(-1) rotate(-5deg) translateY(0px);
            }

            50% {
                transform: scaleX(-1) rotate(-5deg) translateY(16px);
            }

            100% {
                transform: scaleX(-1) rotate(-5deg) translateY(-8px);
            }
        }

        @media (max-width: 992px) {
            .deco-plane-wrap svg {
                width: 260px;
            }
        }

        @media (max-width: 576px) {
            .deco-plane-wrap {
                display: none;
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
            <a class="navbar-brand" href="index.php">
                <img src="uploads/lg-tra (1).png" alt="Travelcation" class="img-fluid">
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
                            <a class="dropdown-item" href="offers.php">Exclusive Offers</a>
                            <a class="dropdown-item" href="alldestinations.php">Destinations</a>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="home.php#contact-section">Contact</a>
                    </li>
                    <li class="nav-item">

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
    <section class="hero-section has-plane-deco" id="home">
        <!-- Decorative airplane wireframes (SVGRepo) -->
        <div class="deco-plane-wrap deco-plane-left" style="filter:invert(1); opacity:0.25;">
            <?= $svg_airplane ?>
        </div>
        <div class="deco-plane-wrap deco-plane-right" style="filter:invert(1); opacity:0.22;">
            <?= $svg_airplane ?>
        </div>
        <div class="hero-background">
            <div class="bg-slide active"
                style="background-image: url('https://images.unsplash.com/photo-1469474968028-56623f02e42e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1474&q=80');">
            </div>
            <div class="bg-slide"
                style="background-image: url('https://images.unsplash.com/photo-1441974231531-c6227db76b6e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80');">
            </div>
            <div class="bg-slide"
                style="background-image: url('https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?ixlib=rb-4.0.3&auto=format&fit=crop&w=1574&q=80');">
            </div>
        </div>

        <div class="hero-overlay"></div>

        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Discover Your Next <span>Adventure</span></h1>
                <p class="hero-description">Your dream vacation is just a click away! Explore breathtaking locations,
                    book luxurious stays, and create memories that last a lifetime.</p>
                <div class="hero-buttons">
                    <a href="home.php" class="btn btn-primary">Explore Packages & Hotels</a>
                    <a href="#contact" class="btn btn-outline"><i class="fas fa-headset me-2"></i>Contact Us</a>
                </div>
            </div>
        </div>
    </section>


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
                    <p class="mb-3" style="color: #94a3b8;">Subscribe to receive exclusive travel deals and updates.</p>
                    <div class="footer-newsletter">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Your email address">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2026 Travelcation. All rights reserved.</p>
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

    <!-- Optional Bootstrap JS for dropdowns -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
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