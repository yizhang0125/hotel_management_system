<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query user from database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: index.php'); // Redirect to homepage or dashboard
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Luxury Hotel</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4F46E5;
            --secondary: #6366F1;
            --accent: #818CF8;
            --white: #FFFFFF;
            --text-dark: #1E293B;
            --text-gray: #64748B;
        }

        body {
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(-45deg, #EEF2FF, #E0E7FF, #F0F9FF, #F0FDFA);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }

        /* Update Login Container */
        .login-container {
            width: 100%;
            max-width: 1200px;
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            background: rgba(255, 255, 255, 0.98);
            border-radius: 2rem;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        /* Update Form Controls */
        .form-control {
            width: 100%;
            height: 3.5rem;
            padding: 0 1.25rem 0 3rem;
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-radius: 1rem;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        /* Update Button */
        .btn-login {
            width: 100%;
            height: 3.5rem;
            background: linear-gradient(135deg, var(--accent), var(--primary));
            color: var(--white);
            border: none;
            border-radius: 1rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.15);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(99, 102, 241, 0.25);
        }

        /* New Animation Styles */
        
        .animated-bg {
            position: fixed;
            inset: 0;
            z-index: -1;
            overflow: hidden;
        }

        /* Floating Orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, 
                rgba(99, 102, 241, 0.4),
                rgba(147, 51, 234, 0.1)
            );
            filter: blur(20px);
            animation: floatOrb 15s infinite ease-in-out;
        }

        .orb-1 { width: 300px; height: 300px; top: 10%; right: 10%; }
        .orb-2 { width: 200px; height: 200px; bottom: 20%; left: 15%; animation-delay: -5s; }
        .orb-3 { width: 250px; height: 250px; top: 40%; left: 30%; animation-delay: -10s; }

        /* Glowing Stars */
        .star {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(99, 102, 241, 0.8);
            border-radius: 50%;
            filter: blur(1px);
            animation: twinkle 4s infinite ease-in-out;
        }

        .star-1 { top: 20%; left: 20%; }
        .star-2 { top: 40%; right: 25%; animation-delay: -1s; }
        .star-3 { top: 60%; left: 40%; animation-delay: -2s; }
        .star-4 { top: 30%; right: 40%; animation-delay: -3s; }
        .star-5 { top: 70%; right: 30%; animation-delay: -4s; }

        /* Floating Bubbles */
        .bubble {
            position: absolute;
            border-radius: 50%;
            background: rgba(99, 102, 241, 0.1);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: floatBubble 20s infinite ease-in-out;
        }

        .bubble-1 { width: 80px; height: 80px; top: 15%; left: 25%; }
        .bubble-2 { width: 120px; height: 120px; top: 45%; right: 20%; animation-delay: -6s; }
        .bubble-3 { width: 100px; height: 100px; bottom: 25%; left: 35%; animation-delay: -12s; }
        .bubble-4 { width: 60px; height: 60px; top: 35%; right: 35%; animation-delay: -18s; }

        /* Mist Effect */
        .mist {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.03);
            filter: blur(40px);
            animation: mistFlow 20s infinite ease-in-out;
        }

        .mist-1 { width: 500px; height: 500px; top: -250px; right: -100px; }
        .mist-2 { width: 400px; height: 400px; bottom: -200px; left: -100px; animation-delay: -10s; }

        /* Animation Keyframes */
        @keyframes floatOrb {
            0%, 100% {
                transform: translate(0, 0) scale(1);
                opacity: 0.3;
            }
            50% {
                transform: translate(-30px, -30px) scale(1.1);
                opacity: 0.6;
            }
        }

        @keyframes twinkle {
            0%, 100% {
                transform: scale(1);
                opacity: 0.3;
            }
            50% {
                transform: scale(3);
                opacity: 1;
            }
        }

        @keyframes floatBubble {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
                opacity: 0.5;
            }
            25% {
                transform: translate(20px, -20px) rotate(90deg);
                opacity: 0.8;
            }
            50% {
                transform: translate(40px, 0px) rotate(180deg);
                opacity: 0.5;
            }
            75% {
                transform: translate(20px, 20px) rotate(270deg);
                opacity: 0.8;
            }
        }

        @keyframes mistFlow {
            0%, 100% {
                transform: translate(0, 0) scale(1);
                opacity: 0.5;
            }
            50% {
                transform: translate(-50px, 50px) scale(1.2);
                opacity: 0.8;
            }
        }

        /* Update Brand Section */
        .brand-section {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: clamp(2rem, 5vw, 4rem);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
        }

        .brand-logo {
            font-size: clamp(3rem, 5vw, 4.5rem);
            color: var(--white);
            margin-bottom: 2rem;
            filter: drop-shadow(0 4px 15px rgba(0, 0, 0, 0.2));
        }

        .brand-title {
            font-size: clamp(2rem, 4vw, 2.75rem);
            color: var(--white);
            margin-bottom: 1.25rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .brand-subtitle {
            font-size: clamp(0.875rem, 2vw, 1rem);
            color: rgba(255, 255, 255, 0.9);
            max-width: 35ch;
            line-height: 1.6;
        }

        /* Login Section */
        .login-section {
            padding: clamp(2rem, 5vw, 4rem);
            background: var(--white);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .login-title {
            font-size: clamp(1.5rem, 3vw, 2rem);
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .login-subtitle {
            color: var(--text-gray);
            font-size: clamp(0.875rem, 2vw, 1rem);
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-gray);
            font-size: 1.25rem;
        }

        /* Alert Styling */
        .alert {
            background: rgba(239, 68, 68, 0.1);
            color: #EF4444;
            padding: 1rem 1.25rem;
            border-radius: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .login-container {
                max-width: 1000px;
            }
        }

        @media (max-width: 992px) {
            .login-container {
                grid-template-columns: 1fr;
                max-width: 600px;
            }
        }

        @media (max-width: 640px) {
            .login-container {
                min-height: 100vh;
                border-radius: 0;
            }

            .brand-section {
                padding: 2rem 1.5rem;
            }

            .login-section {
                padding: 2rem 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .brand-section {
                padding: 1.5rem 1rem;
            }

            .login-section {
                padding: 1.5rem 1rem;
            }

            .form-control {
                height: 3rem;
                font-size: 0.875rem;
            }

            .btn-login {
                height: 3rem;
                font-size: 0.875rem;
            }
        }

        /* Handle Landscape Mode */
        @media (max-height: 600px) and (orientation: landscape) {
            .login-container {
                margin: 1rem auto;
            }
        }

        /* Update the register-link styles */
        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-gray);
        }

        .register-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;  /* Add space between text and link */
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="animated-bg">
        <div class="mist mist-1"></div>
        <div class="mist mist-2"></div>
        
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
        
        <div class="star star-1"></div>
        <div class="star star-2"></div>
        <div class="star star-3"></div>
        <div class="star star-4"></div>
        <div class="star star-5"></div>
        
        <div class="bubble bubble-1"></div>
        <div class="bubble bubble-2"></div>
        <div class="bubble bubble-3"></div>
        <div class="bubble bubble-4"></div>
    </div>
    <div class="page-wrapper">
        <div class="login-container">
            <div class="brand-section">
                <div class="brand-logo">
                    <i class="fas fa-hotel"></i>
                </div>
                <h1 class="brand-title">Luxury Hotel & Resort</h1>
                <p class="brand-subtitle">Experience unparalleled luxury and comfort in our world-class accommodations</p>
            </div>

            <div class="login-section">
                <div class="login-header">
                    <h2 class="login-title">Welcome Back</h2>
                    <p class="login-subtitle">Please sign in to continue</p>
                </div>

                <form method="POST" action="">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" class="form-control" name="username" placeholder="Enter your username" required>
                    </div>

                    <div class="form-group">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-control" name="password" placeholder="Enter your password" required>
                    </div>

                    <button type="submit" class="btn btn-login">
                        Sign In
                    </button>

                    <div class="register-link">
                        Don't have an account? <a href="register.php" style="margin-left: 5px;">Create an account</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

