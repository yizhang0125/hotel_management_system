<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: admin_view_payments.php');
    exit();
}

$payment_id = $_GET['id'];

// Fetch payment details with booking and room information
$stmt = $pdo->prepare("
    SELECT 
        p.*,
        b.check_in,
        b.check_out,
        r.room_type,
        r.price,
        u.username,
        DATEDIFF(b.check_out, b.check_in) AS nights,
        (DATEDIFF(b.check_out, b.check_in) * r.price) AS total_amount
    FROM payments p
    INNER JOIN bookings b ON p.booking_id = b.id
    INNER JOIN rooms r ON b.room_id = r.id
    INNER JOIN users u ON b.user_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$payment_id]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    header('Location: admin_view_payments.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details | Admin</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Add your existing admin styles */
        .payment-details {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .detail-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .section-title {
            color: #1e3c72;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <?php include 'admin/navbar.php'; ?>
    <?php include 'admin/sidebar.php'; ?>

    <div class="main-content">
        <div class="payment-details">
            <h2 class="section-title">
                <i class="fas fa-file-invoice-dollar"></i> Payment Details
            </h2>

            <div class="detail-section">
                <h4>Payment Information</h4>
                <div class="detail-row">
                    <span>Payment ID:</span>
                    <span>#<?= str_pad($payment['id'], 5, '0', STR_PAD_LEFT) ?></span>
                </div>
                <div class="detail-row">
                    <span>Amount:</span>
                    <span>$<?= number_format($payment['total_amount'], 2) ?></span>
                </div>
                <div class="detail-row">
                    <span>Status:</span>
                    <span class="badge badge-<?= $payment['status'] === 'paid' ? 'success' : 'warning' ?>">
                        <?= ucfirst($payment['status']) ?>
                    </span>
                </div>
                <div class="detail-row">
                    <span>Card Number:</span>
                    <span><?= str_repeat('*', strlen($payment['card_number']) - 4) . substr($payment['card_number'], -4) ?></span>
                </div>
                <div class="detail-row">
                    <span>Card Holder:</span>
                    <span><?= htmlspecialchars($payment['cardholder_name']) ?></span>
                </div>
                <div class="detail-row">
                    <span>Expiry Date:</span>
                    <span><?= htmlspecialchars($payment['expiry_date']) ?></span>
                </div>
            </div>

            <div class="detail-section">
                <h4>Booking Details</h4>
                <div class="detail-row">
                    <span>Room Type:</span>
                    <span><?= htmlspecialchars($payment['room_type']) ?></span>
                </div>
                <div class="detail-row">
                    <span>Check-in:</span>
                    <span><?= date('F d, Y', strtotime($payment['check_in'])) ?></span>
                </div>
                <div class="detail-row">
                    <span>Check-out:</span>
                    <span><?= date('F d, Y', strtotime($payment['check_out'])) ?></span>
                </div>
                <div class="detail-row">
                    <span>Number of Nights:</span>
                    <span><?= $payment['nights'] ?></span>
                </div>
            </div>

            <div class="detail-section">
                <h4>Guest Information</h4>
                <div class="detail-row">
                    <span>Guest Name:</span>
                    <span><?= htmlspecialchars($payment['username']) ?></span>
                </div>
                <div class="detail-row">
                    <span>Card Holder:</span>
                    <span><?= htmlspecialchars($payment['cardholder_name']) ?></span>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="admin_view_payments.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Payments
                </a>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 