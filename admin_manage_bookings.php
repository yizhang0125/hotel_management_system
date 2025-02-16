<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Update the SQL query to order by booking ID DESC
$stmt = $pdo->prepare("
    SELECT 
        b.*,
        r.room_type,
        r.price,
        u.username,
        u.email,
          DATEDIFF(b.check_out, b.check_in) as nights,
        (DATEDIFF(b.check_out, b.check_in) * r.price) as total_price
          FROM bookings b 
    INNER JOIN rooms r ON b.room_id = r.id
    INNER JOIN users u ON b.user_id = u.id
    ORDER BY b.id DESC  /* Changed to order by ID DESC */
");
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$stats = [
    'total' => count($bookings),
    'confirmed' => count(array_filter($bookings, fn($b) => $b['status'] === 'confirmed')),
    'pending' => count(array_filter($bookings, fn($b) => $b['status'] === 'pending')),
    'cancelled' => count(array_filter($bookings, fn($b) => $b['status'] === 'cancelled'))
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings | Admin</title>
    <!-- Load jQuery first -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Then load Bootstrap's JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
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

        @media (max-width: 768px) {
            .dashboard-header {
                padding: 30px 0 20px;
            }

            .dashboard-title {
                margin-top: 15px;
            }

            .dashboard-title {
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

        /* Booking Table Card */
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .card-body {
            padding: 25px;
        }

        /* Table Styles */
        .table {
            margin-bottom: 0;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: #2c3e50;
            background: #f8f9fa;
            padding: 15px;
        }

        .table td {
            padding: 15px;
            vertical-align: middle;
        }

        /* Status Badge */
        .booking-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }

        .status-pending {
            background: #fff3e0;
            color: #fb8c00;
        }

        .status-confirmed {
            background: #e8f5e9;
            color: #43a047;
        }

        .status-cancelled {
            background: #ffebee;
            color: #e53935;
        }

        .status-completed {
            background: #e3f2fd;
            color: #1e88e5;
        }

        /* Action Buttons */
        .booking-actions {
            display: flex;
            gap: 5px;
            align-items: center;
        }

        .booking-actions .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .booking-actions .btn:hover {
            transform: translateY(-2px);
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
            color: white;
        }

        /* Add success message styling */
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .card-body {
                padding: 15px;
            }

            .table-responsive {
                font-size: 0.9rem;
            }
            
            .booking-status {
                padding: 4px 8px;
                font-size: 0.8rem;
            }

            .booking-actions .btn {
                width: 28px;
                height: 28px;
                line-height: 28px;
            }
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
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .stat-card {
                padding: 20px;
            }

            .stat-value {
                font-size: 24px;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        .btn-group {
            display: flex;
            gap: 5px;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
    </style>
</head>
<body>

<?php include 'admin/navbar.php'; ?>
<?php include 'admin/sidebar.php'; ?>

<div class="main-content">
<div class="container-fluid">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Manage Bookings</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Bookings</li>
                </ol>
            </nav>
        </div>

        <!-- Add Stats Cards -->
        <div class="stats-grid">
        <div class="stat-card">
                <div class="stat-icon" style="background: #e3f2fd; color: #1e88e5;">
                    <i class="fas fa-calendar-check"></i>
                </div>
            <div class="stat-value"><?= $stats['total'] ?></div>
            <div class="stat-label">Total Bookings</div>
        </div>

        <div class="stat-card">
                <div class="stat-icon" style="background: #e8f5e9; color: #43a047;">
                    <i class="fas fa-check-circle"></i>
                </div>
            <div class="stat-value"><?= $stats['confirmed'] ?></div>
                <div class="stat-label">Confirmed</div>
        </div>

        <div class="stat-card">
                <div class="stat-icon" style="background: #fff3e0; color: #fb8c00;">
                    <i class="fas fa-clock"></i>
                </div>
            <div class="stat-value"><?= $stats['pending'] ?></div>
                <div class="stat-label">Pending</div>
        </div>

        <div class="stat-card">
                <div class="stat-icon" style="background: #ffebee; color: #e53935;">
                    <i class="fas fa-times-circle"></i>
                </div>
            <div class="stat-value"><?= $stats['cancelled'] ?></div>
                <div class="stat-label">Cancelled</div>
    </div>
        </div>

        <!-- Add this below the dashboard header to show success message -->
        <?php if (isset($_GET['success']) && $_GET['success'] === 'marked_complete'): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Booking has been marked as complete successfully!
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
    <div class="table-responsive">
                    <table class="table">
            <thead>
                <tr>
                        <th>Booking ID</th>
                                <th>User</th>
                                <th>Room Type</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                                    <td>#<?= htmlspecialchars($booking['id']) ?></td>
                        <td><?= htmlspecialchars($booking['username']) ?></td>
                        <td><?= htmlspecialchars($booking['room_type']) ?></td>
                        <td><?= date('M d, Y', strtotime($booking['check_in'])) ?></td>
                        <td><?= date('M d, Y', strtotime($booking['check_out'])) ?></td>
                        <td>
                                        <span class="booking-status status-<?= strtolower($booking['status']) ?>">
                                            <?= ucfirst(htmlspecialchars($booking['status'])) ?>
                            </span>
                        </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="view_booking_details.php?id=<?= $booking['id'] ?>" 
                                               class="btn btn-info btn-sm" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <?php if ($booking['status'] === 'pending'): ?>
                                                <form method="POST" action="mark_booking_complete.php" style="display: inline;">
                                                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                                    <button type="submit" class="btn btn-success btn-sm" 
                                                            onclick="return confirm('Confirm this booking?')"
                                                            title="Confirm Booking">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if ($booking['status'] !== 'cancelled'): ?>
                                                <form method="POST" action="cancel_booking.php" style="display: inline;">
                                                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                                    <button type="submit" class="btn btn-warning btn-sm" 
                                                            onclick="return confirm('Are you sure you want to cancel this booking?')"
                                                            title="Cancel Booking">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <a href="edit_booking.php?id=<?= $booking['id'] ?>" 
                                               class="btn btn-primary btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <a href="delete_booking.php?id=<?= $booking['id'] ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Are you sure you want to delete this booking?')"
                                               title="Delete">
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

<!-- Your custom scripts should come last -->
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
