<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

include 'db_connect.php';

$booking_id = $_GET['booking_id'] ?? null;

// Fetch booking details with room information
$stmt = $pdo->prepare("
    SELECT b.*, r.room_type, r.price, r.room_number 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    WHERE b.id = ? AND b.user_id = ? AND b.status = 'confirmed'
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: index.php');
    exit();
}

// Calculate total price
$check_in = new DateTime($booking['check_in']);
$check_out = new DateTime($booking['check_out']);
$duration = $check_in->diff($check_out)->days;
$total_price = $duration * $booking['price'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .confirmation-container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .success-icon {
            color: #28a745;
            font-size: 4rem;
            margin-bottom: 20px;
        }
        .booking-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container confirmation-container">
        <div class="text-center">
            <i class="fas fa-check-circle success-icon"></i>
            <h2>Booking Confirmed!</h2>
            <p class="lead">Thank you for your booking. Your reservation has been confirmed.</p>
        </div>

        <div class="booking-details">
            <h4>Booking Details</h4>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Booking ID:</strong> #<?= $booking['id'] ?></p>
                    <p><strong>Room Type:</strong> <?= htmlspecialchars($booking['room_type']) ?></p>
                    <p><strong>Room Number:</strong> <?= htmlspecialchars($booking['room_number']) ?></p>
                    <p><strong>Check-in:</strong> <?= date('M d, Y', strtotime($booking['check_in'])) ?></p>
                    <p><strong>Check-out:</strong> <?= date('M d, Y', strtotime($booking['check_out'])) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Adults:</strong> <?= $booking['adults'] ?></p>
                    <p><strong>Children:</strong> <?= $booking['children'] ?></p>
                    <p><strong>Duration:</strong> <?= $duration ?> nights</p>
                    <p><strong>Total Amount:</strong> $<?= number_format($total_price, 2) ?></p>
                    <p><strong>Status:</strong> <span class="badge bg-success">Confirmed</span></p>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="view_bookings.php" class="btn btn-primary">
                <i class="fas fa-list"></i> View All Bookings
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Return to Home
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 