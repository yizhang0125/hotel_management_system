<?php
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Logout | Luxury Hotel</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary: #1e3c72;
            --secondary: #2a5298;
            --accent: #ffc107;
        }

        body {
            background: linear-gradient(135deg, rgba(30,60,114,0.95), rgba(42,82,152,0.95)), 
                        url('uploads/background1.jpg') center/cover no-repeat fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
            padding: 20px;
        }

        .logout-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
            padding: 40px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            backdrop-filter: blur(10px);
        }

        .logout-icon {
            font-size: 3.5rem;
            color: var(--primary);
            margin-bottom: 20px;
            animation: fadeInDown 0.5s ease;
        }

        .logout-message {
            color: var(--primary);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 15px;
            animation: fadeIn 0.5s ease;
        }

        .redirect-text {
            color: #666;
            margin-bottom: 25px;
            animation: fadeIn 0.5s ease 0.2s both;
        }

        .spinner-border {
            color: var(--primary);
            margin-bottom: 20px;
            animation: fadeIn 0.5s ease 0.4s both;
        }

        .btn-return {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 12px 30px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-block;
            margin-top: 20px;
            animation: fadeIn 0.5s ease 0.6s both;
        }

        .btn-return:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30,60,114,0.3);
            color: white;
            text-decoration: none;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 576px) {
            .logout-container {
                padding: 30px;
            }

            .logout-icon {
                font-size: 3rem;
            }

            .logout-message {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <i class="fas fa-sign-out-alt logout-icon"></i>
        <h2 class="logout-message">Logging Out...</h2>
        <p class="redirect-text">Please wait while we securely log you out.</p>
        <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
        </div>
        <div>
            <a href="admin_login.php" class="btn-return">
                <i class="fas fa-arrow-left mr-2"></i>Return to Login
            </a>
        </div>
    </div>

    <script>
        // Redirect after a short delay
        setTimeout(function() {
            window.location.href = 'admin_login.php';
        }, 2000);
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 