<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

include 'db_connect.php';

// Fetch summary statistics
$stats = [
    'total_bookings' => $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'total_rooms' => $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn(),
    'active_bookings' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed'")->fetchColumn()
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Luxury Hotel</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Update Dashboard Header Styles */
        .dashboard-header {
            margin-bottom: 40px;
            padding: 40px 0 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .dashboard-title {
            font-size: 32px;
            font-weight: 700;
            color: #1e3c72;
            margin-top: 20px;
            margin-bottom: 10px;
            position: relative;
            padding-left: 20px;
        }

        .dashboard-title::after {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 24px;
            background: linear-gradient(to bottom, #1e3c72, #2a5298);
            border-radius: 2px;
        }

        .breadcrumb {
            background: transparent;
            padding: 0 20px;
            margin: 0;
            font-size: 0.9rem;
        }

        .breadcrumb-item a {
            color: #1e3c72;
            font-weight: 500;
        }

        .breadcrumb-item.active {
            color: #6c757d;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: "›";
            color: #6c757d;
        }

        /* Container padding for better spacing */
        .container-fluid {
            padding: 0 30px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #1e3c72;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6c757d;
            font-size: 14px;
        }

        /* Action Cards */
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .action-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .action-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin: 0 auto 20px;
            color: #1e3c72;
        }

        .action-title {
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .action-description {
            color: #6c757d;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .btn-action {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30,60,114,0.2);
            color: white;
        }

        @media (max-width: 768px) {
            .dashboard-header {
                padding: 30px 0 20px;
            }

            .dashboard-title {
                margin-top: 15px;
                font-size: 24px;
                padding-left: 15px;
            }

            .dashboard-title::after {
                height: 20px;
                width: 3px;
            }

            .container-fluid {
                padding: 0 15px;
            }

            .stat-card {
                padding: 20px;
            }

            .stat-value {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

<?php include 'admin/navbar.php'; ?>
<?php include 'admin/sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Admin Dashboard</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </div>

        <!-- Stats Section -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #e3f2fd; color: #1e88e5;">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-value"><?= $stats['total_bookings'] ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #e8f5e9; color: #43a047;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?= $stats['total_users'] ?></div>
                <div class="stat-label">Registered Users</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #fff3e0; color: #fb8c00;">
                    <i class="fas fa-door-open"></i>
                </div>
                <div class="stat-value"><?= $stats['total_rooms'] ?></div>
                <div class="stat-label">Available Rooms</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #fce4ec; color: #e91e63;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value"><?= $stats['active_bookings'] ?></div>
                <div class="stat-label">Active Bookings</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="actions-grid">
            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-door-open"></i>
                </div>
                <h3 class="action-title">Manage Rooms</h3>
                <p class="action-description">Add, edit, or remove rooms from your hotel inventory</p>
                <a href="rooms.php" class="btn btn-action">Go to Rooms</a>
            </div>

            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3 class="action-title">Manage Bookings</h3>
                <p class="action-description">View and manage all customer bookings</p>
                <a href="admin_manage_bookings.php" class="btn btn-action">Go to Bookings</a>
            </div>

            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-cogs"></i>
                </div>
                <h3 class="action-title">Manage Facilities</h3>
                <p class="action-description">Add or update hotel facilities</p>
                <a href="admin_facilities.php" class="btn btn-action">Go to Facilities</a>
            </div>

            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-star"></i>
                </div>
                <h3 class="action-title">Best Deals</h3>
                <p class="action-description">Manage special offers and discounts</p>
                <a href="best_deals.php" class="btn btn-action">View Deals</a>
            </div>

            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <h3 class="action-title">View Payments</h3>
                <p class="action-description">Monitor and manage payments</p>
                <a href="admin_view_payments.php" class="btn btn-action">View Payments</a>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
