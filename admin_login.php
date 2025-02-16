<?php
include 'db_connect.php'; // Including database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login_admin'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        session_start();
        $_SESSION['admin_id'] = $admin['id'];
        header('Location: admin_dashboard.php');
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary: #4a90e2;
            --secondary: #82b1ff;
            --accent: #c5a47e;
            --text: #2c3e50;
            --light: #ffffff;
            --gradient: linear-gradient(135deg, #4a90e2, #82b1ff);
            --shadow: 0 10px 30px rgba(74, 144, 226, 0.1);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa, #e4e8f0);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
            padding: 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 450px;
            animation: slideUp 0.8s ease;
        }

        .login-container {
            background: var(--light);
            border-radius: 20px;
            box-shadow: var(--shadow);
            overflow: hidden;
            position: relative;
        }

        .login-header {
            padding: 40px;
            text-align: center;
            background: var(--light);
            position: relative;
        }

        .login-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: var(--gradient);
            border-radius: 3px;
        }

        .hotel-logo {
            width: 110px;
            height: 110px;
            margin: 0 auto 20px;
            position: relative;
            animation: pulse 2s infinite;
        }

        .logo-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 2px solid var(--primary);
            border-radius: 50%;
            animation: rotate 20s linear infinite;
        }

        .logo-ring::before {
            content: '';
            position: absolute;
            top: -4px;
            left: 50%;
            width: 8px;
            height: 8px;
            background: var(--primary);
            border-radius: 50%;
            transform: translateX(-50%);
        }

        .brand-logo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90px;
            height: 90px;
            background: var(--gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-logo i {
            font-size: 35px;
            color: var(--light);
        }

        .login-header h2 {
            color: var(--text);
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            font-family: 'Playfair Display', serif;
        }

        .login-header p {
            color: var(--primary);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 5px;
        }

        .login-form {
            padding: 40px;
            background: var(--light);
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label {
            color: var(--text);
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            height: 55px;
            padding: 12px 25px 12px 50px;
            border: 2px solid #e6eef9;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            background: #f8fafd;
            color: var(--text);
        }

        .form-control:focus {
            border-color: var(--primary);
            background: var(--light);
            box-shadow: 0 0 0 4px rgba(74, 144, 226, 0.1);
        }

        .form-group i {
            position: absolute;
            left: 20px;
            top: 45px;
            color: var(--primary);
            font-size: 18px;
            transition: all 0.3s ease;
        }

        .btn-login {
            width: 100%;
            height: 55px;
            background: var(--gradient);
            border: none;
            border-radius: 12px;
            color: var(--light);
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(74, 144, 226, 0.2);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(74, 144, 226, 0.3);
        }

        .back-link {
            text-align: center;
            margin-top: 25px;
        }

        .back-link a {
            color: var(--text);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 30px;
            border: 2px solid var(--primary);
            transition: all 0.3s ease;
        }

        .back-link a:hover {
            background: var(--primary);
            color: var(--light);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @media (max-width: 576px) {
            .login-wrapper {
                width: 90%;
            }

            .login-header {
                padding: 30px 20px;
            }

            .hotel-logo {
                width: 90px;
                height: 90px;
            }

            .brand-logo {
                width: 75px;
                height: 75px;
            }

            .brand-logo i {
                font-size: 30px;
            }

            .login-form {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-container">
        <div class="login-header">
            <div class="hotel-logo">
                <div class="logo-ring"></div>
                <div class="brand-logo">
                    <i class="fas fa-hotel"></i>
                </div>
            </div>
            <h2>Luxury Hotel</h2>
            <p>Management Portal</p>
        </div>

        <div class="login-form">
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" name="login_admin" class="btn-login">
                    Access Dashboard
                </button>
            </form>
            <?php if(isset($error)) { echo '<p class="text-danger mt-3">'.$error.'</p>'; } ?>
        </div>
    </div>

    <div class="back-link">
        <a href="index.php">
            <i class="fas fa-arrow-left mr-2"></i> Return to Main Site
        </a>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
