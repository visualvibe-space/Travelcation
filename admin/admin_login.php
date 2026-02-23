<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_pannel.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        try {
            // Fetch user from database by email
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ? AND status = 'Active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_name'] = $user['full_name'];
                $_SESSION['admin_role'] = $user['role'];
                
                // Update last login
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
                $updateStmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW(), last_login_ip = ? WHERE id = ?");
                $updateStmt->execute([$ip_address, $user['id']]);
                
                // Redirect to admin panel
                header('Location: admin_pannel.php');
                exit;
            } else {
                $error = 'Invalid email or password';
            }
        } catch (PDOException $e) {
            $error = 'Database error occurred. Please try again.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - ExploreWorld Travel</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            --danger-color: #E53E3E;
            --white: #FFFFFF;
            
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.07);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --shadow-xl: 0 20px 25px rgba(0,0,0,0.15);
            --shadow-2xl: 0 25px 50px -12px rgba(0,0,0,0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Animated Background with Floating Elements */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            animation: rotate 40s linear infinite;
            z-index: 0;
        }

        body::after {
            content: '';
            position: absolute;
            bottom: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(242,140,40,0.15) 0%, transparent 70%);
            animation: rotate 50s linear infinite reverse;
            z-index: 0;
        }

        /* Floating decorative elements */
        .floating-element {
            position: absolute;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(5px);
            border-radius: 0;
            z-index: 1;
        }

        .element-1 {
            top: 10%;
            left: 5%;
            width: 150px;
            height: 150px;
            background: rgba(242,140,40,0.1);
            transform: rotate(45deg);
            animation: float 8s ease-in-out infinite;
        }

        .element-2 {
            bottom: 15%;
            right: 8%;
            width: 200px;
            height: 200px;
            background: rgba(0,166,200,0.1);
            transform: rotate(15deg);
            animation: float 10s ease-in-out infinite reverse;
        }

        .element-3 {
            top: 20%;
            right: 15%;
            width: 100px;
            height: 100px;
            background: rgba(255,255,255,0.1);
            transform: rotate(75deg);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(45deg); }
            50% { transform: translateY(-30px) rotate(45deg); }
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .login-container {
            width: 100%;
            max-width: 550px;
            position: relative;
            z-index: 10;
            animation: fadeInUp 0.8s ease;
        }

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

        /* LARGER SQUARE CARD */
        .login-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 0;
            box-shadow: 0 30px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.3);
            width: 100%;
          
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }
        .login-card {
    width: 100%;
    max-width: 550px;
    min-height: 750px;
    display: flex;
    flex-direction: column;
}

        .login-card:hover {
            transform: scale(1.02);
            box-shadow: 0 40px 80px rgba(0,0,0,0.4);
        }

        /* Header with Gradient Animation */
        .login-header {
            background: linear-gradient(135deg, var(--primary-color), var(--dark-color), var(--accent-color));
            background-size: 200% 200%;
            animation: gradientShift 8s ease infinite;
            padding: 2.5rem 2rem 1.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            flex: 0 0 auto;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        .login-header::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 10%;
            width: 80%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--secondary-color), var(--accent-color), transparent);
        }

        .login-header i {
            font-size: 3.5rem;
            color: var(--secondary-color);
            margin-bottom: 0.8rem;
            position: relative;
            z-index: 2;
            filter: drop-shadow(0 10px 15px rgba(0,0,0,0.3));
            animation: pulse 2s infinite, floatIcon 3s ease-in-out infinite;
        }

        @keyframes floatIcon {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.9;
            }
        }

        .login-header h2 {
            color: var(--white);
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 0.25rem;
            font-family: 'Playfair Display', serif;
            position: relative;
            z-index: 2;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            letter-spacing: 1px;
        }

        .login-header p {
            color: rgba(255,255,255,0.9);
            font-size: 1rem;
            position: relative;
            z-index: 2;
            font-weight: 500;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .login-body {
            padding: 2.5rem 2.5rem 2rem;
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            background: rgba(255,255,255,0.95);
        }

        /* Alert Messages */
        .alert-custom {
            border-radius: 0;
            padding: 1rem;
            margin-bottom: 1.5rem;
            animation: slideInDown 0.5s ease;
            border-left: 5px solid;
            font-weight: 500;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: linear-gradient(135deg, #FEE, #FDD);
            color: var(--danger-color);
            border-left-color: var(--danger-color);
        }

        .alert-success {
            background: linear-gradient(135deg, #DFD, #CFC);
            color: var(--success-color);
            border-left-color: var(--success-color);
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 2rem;
            position: relative;
        }

        .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 1.2rem;
            transition: all 0.3s ease;
            z-index: 10;
        }

        .form-control {
            width: 100%;
            padding: 1.2rem 1.2rem 1.2rem 3rem;
            border: 2px solid var(--border-color);
            border-radius: 0;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--white);
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 5px rgba(242,140,40,0.1);
            outline: none;
            transform: translateY(-2px);
        }

        .form-control:focus + i {
            color: var(--secondary-color);
            transform: translateY(-50%) scale(1.1);
        }

        .form-control::placeholder {
            color: var(--text-light);
            opacity: 0.7;
        }

        /* Password Toggle */
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
            font-size: 1.2rem;
        }

        .password-toggle:hover {
            color: var(--secondary-color);
            transform: translateY(-50%) scale(1.1);
        }

        /* Remember Me & Forgot Password */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            cursor: pointer;
            position: relative;
        }

        .remember-me input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: var(--secondary-color);
        }

        .remember-me span {
            color: var(--text-color);
            font-size: 0.95rem;
            font-weight: 500;
        }

        .forgot-password {
            color: var(--secondary-color);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }

        .forgot-password::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent-color);
            transition: width 0.3s ease;
        }

        .forgot-password:hover {
            color: var(--accent-color);
        }

        .forgot-password:hover::after {
            width: 100%;
        }

        /* ENHANCED SIGN IN BUTTON - Made more prominent */
        .btn-login {
            background: linear-gradient(135deg, var(--secondary-color), #f39c12, var(--accent-color));
            background-size: 200% 200%;
            animation: gradientShift 3s ease infinite;
            color: white;
            border: none;
            padding: 1.3rem;
            border-radius: 0;
            font-weight: 800;
            font-size: 1.3rem;
            cursor: pointer;
            transition: all 0.4s ease;
            width: 100%;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 1.5rem;
            border: 2px solid rgba(255,255,255,0.3);
            box-shadow: 0 10px 20px rgba(242,140,40,0.3);
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-5px) scale(1.03);
            box-shadow: 0 20px 30px rgba(242,140,40,0.5);
            border-color: var(--white);
        }

        .btn-login i {
            margin-right: 1rem;
            font-size: 1.3rem;
            transition: transform 0.3s ease;
        }

        .btn-login:hover i {
            transform: translateX(10px) rotate(360deg);
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: auto;
        }

        .login-footer p {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .login-footer a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .login-footer a:hover {
            color: var(--accent-color);
            text-decoration: underline;
        }

        /* Back to Home Link */
        .back-home {
            position: absolute;
            top: 30px;
            left: 30px;
            z-index: 20;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 0.8rem 1.5rem;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 0;
            border: 1px solid rgba(255,255,255,0.3);
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .back-home:hover {
            background: rgba(255,255,255,0.25);
            transform: translateX(-10px);
            color: white;
            border-color: var(--secondary-color);
        }

        .back-home i {
            transition: transform 0.3s ease;
        }

        .back-home:hover i {
            transform: translateX(-5px);
        }

        /* Loading State */
        .loading {
            display: none;
            text-align: center;
            margin-top: 1.5rem;
        }

        .loading.active {
            display: block;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--border-color);
            border-top-color: var(--secondary-color);
            border-right-color: var(--accent-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Welcome Text */
        .welcome-text {
            text-align: center;
            margin-bottom: 2rem;
        }

        .welcome-text h3 {
            font-size: 1.3rem;
            color: var(--primary-color);
            font-weight: 600;
        }

        .welcome-text p {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-container {
                max-width: 500px;
            }

            .login-header h2 {
                font-size: 2rem;
            }

            .login-body {
                padding: 2rem;
            }
            
            .btn-login {
                padding: 1.1rem;
                font-size: 1.1rem;
            }
        }

        @media (max-width: 576px) {
            .login-card {
                aspect-ratio: auto;
                min-height: auto;
            }
            
            .login-body {
                padding: 1.5rem;
            }

            .login-header {
                padding: 1.8rem 1.5rem 1.2rem;
            }

            .login-header h2 {
                font-size: 1.8rem;
            }

            .form-options {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .back-home {
                top: 15px;
                left: 15px;
                font-size: 0.9rem;
                padding: 0.5rem 1rem;
            }

            .floating-element {
                display: none;
            }
            
            .btn-login {
                padding: 1rem;
                font-size: 1rem;
                letter-spacing: 2px;
            }
        }

        /* For very small screens */
        @media (max-height: 700px) {
            .login-card {
                aspect-ratio: auto;
            }
        }
    </style>
</head>
<body>
    <!-- Floating Decorative Elements -->
    <div class="floating-element element-1"></div>
    <div class="floating-element element-2"></div>
    <div class="floating-element element-3"></div>

 

    <div class="login-container">
        <!-- LARGER SQUARE LOGIN CARD -->
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-cogs"></i>
                <h2>Welcome Back</h2>
                <p>Admin Portal</p>
            </div>

            <div class="login-body">
                <!-- Welcome Text -->
                <div class="welcome-text">
                    <h3>Sign in to your account</h3>
                    <p>Access the admin dashboard to manage your travel business</p>
                </div>

                <!-- Error Message -->
                <?php if ($error): ?>
                    <div class="alert-custom alert-error">
                        <i class="fas fa-exclamation-circle fa-lg"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- Success Message -->
                <?php if (isset($_GET['reset']) && $_GET['reset'] == 'success'): ?>
                    <div class="alert-custom alert-success">
                        <i class="fas fa-check-circle fa-lg"></i>
                        Password reset successful! Please login with your new password.
                    </div>
                <?php endif; ?>

                <!-- Login Form - Only Email and Password -->
                <form method="POST" action="" id="loginForm">
                    <div class="form-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" class="form-control" name="email" placeholder="Email Address" required autofocus>
                    </div>

                    <div class="form-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                        <span class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </span>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <!-- <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember"> 
                            <span>Remember me</span>
                        </label> -->
                        <!-- <a href="forgot-password.php" class="forgot-password">Forgot Password?</a> -->
                    </div>

                    <!-- PROMINENT SIGN IN BUTTON -->
                    <button type="submit" class="btn-login" id="loginBtn">
                        <i class="fas fa-sign-in-alt"></i> SIGN IN
                    </button>

                    <!-- Loading Spinner -->
                    <div class="loading" id="loading">
                        <div class="loading-spinner"></div>
                        <p class="mt-2">Authenticating...</p>
                    </div>
                </form>

                <!-- Footer -->
                <div class="login-footer">
                    <p>&copy; <?= date('Y') ?> Travelcation. All rights reserved.</p>
                    <!-- <p><a href="#">Privacy Policy</a> | <a href="#">Terms of Use</a></p> -->
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Show loading spinner on form submit
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const loginBtn = document.getElementById('loginBtn');
            const loading = document.getElementById('loading');
            
            loginBtn.style.display = 'none';
            loading.classList.add('active');
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            document.querySelectorAll('.alert-custom').forEach(function(alert) {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        // Adjust square card on window resize
        function adjustSquareCard() {
            const loginCard = document.querySelector('.login-card');
            
            if (window.innerWidth > 576 && window.innerHeight > 700) {
                loginCard.style.aspectRatio = '1 / 1';
            } else {
                loginCard.style.aspectRatio = 'auto';
            }
        }

        window.addEventListener('resize', adjustSquareCard);
        window.addEventListener('load', adjustSquareCard);

        // Add floating animation to form elements
        const formGroups = document.querySelectorAll('.form-group');
        formGroups.forEach((group, index) => {
            group.style.animation = `fadeInUp 0.5s ease ${index * 0.1 + 0.3}s both`;
        });

        document.querySelector('.form-options').style.animation = 'fadeInUp 0.5s ease 0.5s both';
        document.querySelector('.btn-login').style.animation = 'fadeInUp 0.5s ease 0.6s both';
    </script>
</body>
</html>