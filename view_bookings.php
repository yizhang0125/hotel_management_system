<?php
session_start();
include 'db_connect.php';
include 'user/navbar.php';  // Add navbar include

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch bookings for the logged-in user
$stmt = $pdo->prepare("
    SELECT 
        b.*,
        r.room_type,
        r.price,
        DATEDIFF(b.check_out, b.check_in) as nights,
        (DATEDIFF(b.check_out, b.check_in) * r.price) as total_price
    FROM bookings b
    INNER JOIN rooms r ON b.room_id = r.id
    WHERE b.user_id = ?
    ORDER BY b.id DESC
");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Bookings | Luxury Hotel</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
            padding-top: 80px;
            font-family: 'Poppins', sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-size: 24px;
            font-weight: 700;
        }

        .nav-link {
            font-weight: 500;
            padding: 10px 15px !important;
            transition: all 0.3s;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            border-radius: 5px;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .page-header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 28px;
            font-weight: 600;
        }

        .bookings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
        }

        .booking-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            overflow: hidden;
            transition: transform 0.3s;
        }

        .booking-card:hover {
            transform: translateY(-5px);
        }

        .booking-details {
            padding: 20px;
        }

        .booking-id {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 10px;
        }

        .room-type {
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .dates {
            margin-bottom: 20px;
        }

        .date-badge {
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            color: #2c3e50;
            display: block;
            margin-bottom: 8px;
        }

        .date-badge i {
            color: #1e3c72;
            width: 16px;
            margin-right: 5px;
        }

        .contact-info {
            margin-bottom: 20px;
        }

        .contact-detail {
            color: #6c757d;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .contact-detail i {
            color: #1e3c72;
            width: 16px;
            margin-right: 5px;
        }

        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-confirmed {
            background: #e3fcef;
            color: #00a650;
        }

        .status-pending {
            background: #fff4e6;
            color: #f76707;
        }

        .status-cancelled {
            background: #ffe3e3;
            color: #e03131;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-book-open"></i> Your Bookings</h1>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Print Bookings
            </button>
        </div>
    </div>

    <div class="bookings-grid">
        <?php foreach ($bookings as $booking): ?>
            <div class="booking-card">
                <div class="booking-details">
                    <div class="booking-id">
                        <i class="fas fa-hashtag"></i> 
                        Booking #<?= str_pad($booking['id'], 5, '0', STR_PAD_LEFT) ?>
                    </div>
                    <div class="room-type">
                        <?= htmlspecialchars($booking['room_type']) ?>
                    </div>
                    <div class="dates">
                        <div class="date-badge">
                            <i class="fas fa-calendar-check"></i>
                            Check-in: <?= date('M d, Y', strtotime($booking['check_in'])) ?>
                        </div>
                        <div class="date-badge">
                            <i class="fas fa-calendar-times"></i>
                            Check-out: <?= date('M d, Y', strtotime($booking['check_out'])) ?>
                        </div>
                    </div>
                    <div class="contact-info">
                        <div class="contact-detail">
                            <i class="fas fa-phone"></i>
                            <?= htmlspecialchars($booking['phone_number']) ?>
                        </div>
                        <div class="contact-detail">
                            <i class="fas fa-envelope"></i>
                            <?= htmlspecialchars($booking['email_address']) ?>
                        </div>
                    </div>
                    <div class="status-badge status-<?= strtolower($booking['status']) ?>">
                        <?= ucfirst(htmlspecialchars($booking['status'])) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
