<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

$booking_id = $_GET['id'];

// Fetch booking details with room and user information
$stmt = $pdo->prepare("
    SELECT 
        b.*,
        r.room_type,
        r.price,
        DATEDIFF(b.check_out, b.check_in) as nights,
        (DATEDIFF(b.check_out, b.check_in) * r.price) as total_price
    FROM bookings b
    INNER JOIN rooms r ON b.room_id = r.id
    WHERE b.id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    header('Location: admin_manage_bookings.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details | Admin</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #1e3c72;
            --secondary-color: #2a5298;
        }

        body {
            background: #f8f9fa;
            padding-top: 80px;
        }

        .details-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .page-header {
            background: white;
            padding: 25px 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .details-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .details-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .details-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .section-title {
            color: var(--primary-color);
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .detail-label {
            color: #6c757d;
            font-weight: 500;
        }

        .detail-value {
            color: #2c3e50;
            font-weight: 600;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
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

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
        }
    </style>
</head>
<body>
    <?php include 'admin/navbar.php'; ?>
    <?php include 'admin/sidebar.php'; ?>

    <div class="details-container">
        <div class="page-header">
            <h1 class="page-title">Booking Details #<?= $booking_id ?></h1>
            <a href="admin_manage_bookings.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Bookings
            </a>
        </div>

        <div class="details-card">
            <!-- Guest Information -->
            <div class="details-section">
                <h2 class="section-title">
                    <i class="fas fa-user"></i> Guest Information
                </h2>
                <div class="detail-row">
                    <span class="detail-label">Name</span>
                    <span class="detail-value"><?= htmlspecialchars($booking['username']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email</span>
                    <span class="detail-value"><?= htmlspecialchars($booking['email_address']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Phone</span>
                    <span class="detail-value"><?= htmlspecialchars($booking['phone_number']) ?></span>
                </div>
            </div>

            <!-- Booking Details -->
            <div class="details-section">
                <h2 class="section-title">
                    <i class="fas fa-calendar-alt"></i> Booking Details
                </h2>
                <div class="detail-row">
                    <span class="detail-label">Room Type</span>
                    <span class="detail-value"><?= htmlspecialchars($booking['room_type']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Check-in Date</span>
                    <span class="detail-value"><?= date('F d, Y', strtotime($booking['check_in'])) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Check-out Date</span>
                    <span class="detail-value"><?= date('F d, Y', strtotime($booking['check_out'])) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Number of Nights</span>
                    <span class="detail-value"><?= $booking['nights'] ?></span>
                </div>
            </div>

            <!-- Guest Count -->
            <div class="details-section">
                <h2 class="section-title">
                    <i class="fas fa-users"></i> Guest Count
                </h2>
                <div class="detail-row">
                    <span class="detail-label">Adults</span>
                    <span class="detail-value"><?= $booking['adults'] ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Children</span>
                    <span class="detail-value"><?= $booking['children'] ?></span>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="details-section">
                <h2 class="section-title">
                    <i class="fas fa-credit-card"></i> Payment Information
                </h2>
                <div class="detail-row">
                    <span class="detail-label">Price per Night</span>
                    <span class="detail-value">$<?= number_format($booking['price'], 2) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Amount</span>
                    <span class="detail-value">$<?= number_format($booking['total_price'], 2) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status</span>
                    <span class="status-badge status-<?= strtolower($booking['status']) ?>">
                        <?= ucfirst($booking['status']) ?>
                    </span>
                </div>
            </div>

            <div class="action-buttons">
                <a href="edit_booking.php?id=<?= $booking_id ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Booking
                </a>
                <a href="delete_booking.php?id=<?= $booking_id ?>" 
                   class="btn btn-danger"
                   onclick="return confirm('Are you sure you want to delete this booking?')">
                    <i class="fas fa-trash"></i> Delete Booking
                </a>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 