<?php
// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fix the include path - use absolute path from document root
include_once $_SERVER['DOCUMENT_ROOT'] . '/hotel_new2/db_connect.php';

// Fetch admin name
$admin_name = '';
if (isset($_SESSION['admin_id'])) {
    $stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch();
    $admin_name = $admin['username'] ?? 'Admin';
}
?>

<style>
    /* Admin Navbar Styles */
    .admin-navbar {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        padding: 15px 0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .navbar-brand {
        font-size: 1.8rem;
        font-weight: 700;
        color: #fff !important;
        display: flex;
        align-items: center;
    }

    .navbar-brand i {
        font-size: 2rem;
        margin-right: 12px;
    }

    .navbar-nav .nav-link {
        color: rgba(255,255,255,0.9) !important;
        font-size: 1rem;
        font-weight: 500;
        padding: 8px 20px !important;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
    }

    .navbar-nav .nav-link:hover {
        color: #fff !important;
        background: rgba(255,255,255,0.1);
        border-radius: 5px;
    }

    .navbar-nav .nav-link i {
        margin-right: 8px;
        font-size: 1.2rem;
    }

    .navbar-toggler {
        border: none;
        padding: 0.5rem 0.75rem;
    }

    .navbar-toggler:focus {
        box-shadow: none;
        outline: none;
    }

    @media (max-width: 768px) {
        .navbar-brand {
            font-size: 1.5rem;
        }
        
        .navbar-brand i {
            font-size: 1.8rem;
        }

        .navbar-nav .nav-link {
            padding: 10px 15px !important;
        }
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark admin-navbar fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="admin_dashboard.php">
            <i class="fas fa-hotel"></i> Admin Dashboard
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="admin_profile.php">
                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($admin_name) ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav> 