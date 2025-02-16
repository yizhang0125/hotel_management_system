<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

include 'db_connect.php';

// Fetch user payments
$stmtPayments = $pdo->prepare("
    SELECT 
        p.*, 
        b.check_in,
        b.check_out,
        r.room_type,
        r.price,
        u.username,
        p.cardholder_name as name,
        p.expiry_date,
        DATEDIFF(b.check_out, b.check_in) AS number_of_days,
        (DATEDIFF(b.check_out, b.check_in) * r.price) as total_price
    FROM payments p
    JOIN bookings b ON p.booking_id = b.id
    JOIN rooms r ON b.room_id = r.id
    JOIN users u ON b.user_id = u.id
    ORDER BY p.id DESC
");

$stmtPayments->execute();
$payments = $stmtPayments->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$stats = [
    'total_payments' => count($payments),
    'total_amount' => array_sum(array_column($payments, 'total_price')),
    'paid_payments' => count(array_filter($payments, fn($p) => ($p['status'] ?? '') === 'paid')),
    'unpaid_payments' => count(array_filter($payments, fn($p) => ($p['status'] ?? '') === 'unpaid'))
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Payments | Admin</title>
    <!-- Load jQuery first -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Then load Bootstrap's JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e3c72;
            --secondary-color: #2a5298;
            --accent-color: #4CAF50;
            --danger-color: #dc3545;
            --warning-color: #ff9800;
            --text-primary: #2c3e50;
            --text-secondary: #6c757d;
            --bg-primary: #f8f9fa;
            --bg-secondary: #ffffff;
            --border-radius: 15px;
            --card-shadow: 0 5px 20px rgba(0,0,0,0.08);
            --transition: all 0.3s ease;
        }

        body {
            background: var(--bg-primary);
            font-family: 'Poppins', sans-serif;
            padding-top: 65px;
        }

        /* Navbar Styles */
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-size: 24px;
            font-weight: 700;
            color: white;
            padding: 0 20px;
            display: flex;
            align-items: center;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            padding: 8px 15px !important;
            transition: all 0.3s;
        }

        /* Sidebar Styles */
        .admin-sidebar {
            position: fixed;
            top: 65px;
            left: 0;
            bottom: 0;
            width: 250px;
            background: #284B8C;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: all 0.3s ease;
            height: calc(100vh - 65px);
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-category {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            padding: 1.5rem 2rem 0.75rem;
        }

        .menu-item {
            padding: 12px 25px;
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
            margin: 4px 0;
        }

        .menu-item:hover, .menu-item.active {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            text-decoration: none;
            border-left-color: #ffffff;
        }

        .menu-item i {
            width: 20px;
            margin-right: 10px;
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }

        /* Dashboard Header */
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

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
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
        }

        /* Table Styles */
        .table-responsive {
            background: var(--bg-primary);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 20px;
        }

        .table {
            background: transparent;
        }

        .table thead th {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 15px;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .table tbody tr {
            background: transparent;
            transition: var(--transition);
        }

        .table tbody tr:hover {
            background: rgba(0,0,0,0.02);
        }

        .table td {
            border-color: rgba(0,0,0,0.05);
            padding: 15px;
            vertical-align: middle;
        }

        /* Status Badges */
        .status-badge {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-success {
            background: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
        }

        .status-pending {
            background: rgba(255, 152, 0, 0.1);
            color: #FF9800;
        }

        .status-failed {
            background: rgba(244, 67, 54, 0.1);
            color: #F44336;
        }

        /* Action Buttons */
        .btn-action {
            padding: 8px 15px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-danger {
            background: rgba(244, 67, 54, 0.1);
            color: #F44336;
            border: none;
        }

        .btn-danger:hover {
            background: #F44336;
            color: white;
            transform: translateY(-2px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
            }

            body.show-sidebar .admin-sidebar {
                transform: translateX(0);
            }
        }

        /* Enhanced Table Styles */
        .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .table th {
            border: none;
            font-weight: 600;
            color: #1e3c72;
            background: #f8f9fa;
            padding: 15px 20px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            padding: 15px 20px;
            vertical-align: middle;
            background: white;
            border-top: 1px solid #edf2f9;
            border-bottom: 1px solid #edf2f9;
        }

        .table tr td:first-child {
            border-left: 1px solid #edf2f9;
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        .table tr td:last-child {
            border-right: 1px solid #edf2f9;
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        /* Avatar Circle */
        .avatar-circle {
            width: 35px;
            height: 35px;
            background: #1e3c72;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        /* Amount Styling */
        .amount {
            font-weight: 600;
            color: #2e7d32;
            font-size: 1.1rem;
        }

        /* Badge Styles */
        .badge {
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .badge-success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .badge-warning {
            background-color: #fff3e0;
            color: #ef6c00;
        }

        .badge-danger {
            background-color: #ffebee;
            color: #c62828;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-buttons .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
        }

        /* Room Type Badge */
        .room-type {
            background: #e3f2fd;
            color: #1e88e5;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Date Style */
        .date {
            color: #6c757d;
            font-size: 0.9rem;
        }

        /* Card Header */
        .card-header {
            background: white;
            border-bottom: 1px solid #edf2f9;
            padding: 20px 25px;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e3c72;
            margin: 0;
        }

        /* Enhanced Table Header Styles */
        .table thead tr.bg-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border-radius: 8px 8px 0 0;
        }

        .table thead th {
            border: none;
            padding: 15px 20px;
            color: white;
            font-weight: 500;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            vertical-align: middle;
        }

        .th-content {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .th-content i {
            font-size: 1rem;
            opacity: 0.8;
        }

        .th-content span {
            font-size: 0.85rem;
        }

        /* Hover effect for header */
        .table thead tr.bg-header th:hover {
            background: rgba(255, 255, 255, 0.1);
            cursor: pointer;
        }

        /* First and last th border radius */
        .table thead tr.bg-header th:first-child {
            border-top-left-radius: 8px;
        }

        .table thead tr.bg-header th:last-child {
            border-top-right-radius: 8px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .th-content span {
                font-size: 0.8rem;
            }
            
            .th-content i {
                font-size: 0.9rem;
            }

            .table thead th {
                padding: 12px 15px;
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
            <h1 class="dashboard-title">Payment History</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Payments</li>
                </ol>
            </nav>
        </div>

        <!-- Add this after dashboard-header div -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #e3f2fd; color: #1e88e5;">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="stat-value"><?= $stats['total_payments'] ?></div>
                <div class="stat-label">Total Transactions</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #e8f5e9; color: #43a047;">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value">$<?= number_format($stats['total_amount'], 2) ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #e8f5e9; color: #43a047;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value"><?= $stats['paid_payments'] ?></div>
                <div class="stat-label">Paid Payments</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #fff3e0; color: #fb8c00;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value"><?= $stats['unpaid_payments'] ?></div>
                <div class="stat-label">Unpaid Payments</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title">
                        <i class="fas fa-credit-card mr-2"></i> Payment Transactions
                    </h5>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr class="bg-header">
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-hashtag"></i>
                                        <span>Transaction ID</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-user"></i>
                                        <span>Guest Details</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-bed"></i>
                                        <span>Room Info</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-dollar-sign"></i>
                                        <span>Amount</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-info-circle"></i>
                                        <span>Status</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-cog"></i>
                                        <span>Actions</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="text-primary font-weight-bold">#<?= htmlspecialchars($payment['id']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle">
                                            <?= strtoupper(substr($payment['name'], 0, 1)) ?>
                                        </div>
                                        <div class="ml-2">
                                            <div class="font-weight-bold"><?= htmlspecialchars($payment['name']) ?></div>
                                            <small class="text-muted">
                                                <?= str_repeat('*', strlen($payment['card_number']) - 4) . substr($payment['card_number'], -4) ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="room-type"><?= htmlspecialchars($payment['room_type']) ?></span>
                                    <div class="small text-muted"><?= htmlspecialchars($payment['number_of_days']) ?> days</div>
                                </td>
                                <td>
                                    <div class="amount">$<?= number_format($payment['total_price'], 2) ?></div>
                                </td>
                                <td>
                                    <?php 
                                    $statusClass = match($payment['status']) {
                                        'paid' => 'success',
                                        'unpaid' => 'warning',
                                        default => 'danger'
                                    };
                                    $statusIcon = match($payment['status']) {
                                        'paid' => 'check-circle',
                                        'unpaid' => 'clock',
                                        default => 'times-circle'
                                    };
                                    ?>
                                    <span class="badge badge-<?= $statusClass ?>">
                                        <i class="fas fa-<?= $statusIcon ?> mr-1"></i>
                                        <?= ucfirst($payment['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="view_payment_details.php?id=<?= $payment['id'] ?>" 
                                           class="btn btn-info btn-sm" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="delete_payment.php?id=<?= $payment['id'] ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Are you sure you want to delete this payment?')"
                                           title="Delete Payment">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Toggle sidebar on mobile
    $('.navbar-toggler').click(function() {
        $('body').toggleClass('show-sidebar');
    });

    // Close sidebar when clicking outside on mobile
    $(document).click(function(event) {
        if (!$(event.target).closest('.admin-sidebar, .navbar-toggler').length) {
            $('body').removeClass('show-sidebar');
        }
    });
});
</script>

</body>
</html>
