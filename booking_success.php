<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['booking_id'])) {
    die("Booking ID not provided.");
}

$booking_id = $_GET['booking_id'];

include 'db_connect.php';

// Fetch booking details with deal information
$stmt = $pdo->prepare("
    SELECT b.*, d.deal_name, d.price, d.discount, d.image_path,
           p.cardholder_name, p.card_number, p.payment_date
    FROM deals_booking b 
    JOIN deals d ON b.deal_id = d.id
    JOIN deals_payment p ON b.id = p.booking_id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    die("Booking not found.");
}

// Calculate nights
$check_in = new DateTime($booking['check_in']);
$check_out = new DateTime($booking['check_out']);
$nights = $check_in->diff($check_out)->days;

// Get success message if exists
$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']); // Clear the message after use

// Generate the PDF link
$pdf_link = "generate_pdf_deals.php?booking_id=" . urlencode($booking_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .success-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .success-icon {
            color: #28a745;
            font-size: 4rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container success-container">
        <div class="text-center">
            <i class="fas fa-check-circle success-icon"></i>
            <h1 class="mb-4">Payment Successful!</h1>
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Booking Details</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Booking ID:</strong> #<?= htmlspecialchars($booking_id) ?></p>
                        <p><strong>Deal:</strong> <?= htmlspecialchars($booking['deal_name']) ?></p>
                        <p><strong>Check-in:</strong> <?= date('M d, Y', strtotime($booking['check_in'])) ?></p>
                        <p><strong>Check-out:</strong> <?= date('M d, Y', strtotime($booking['check_out'])) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Duration:</strong> <?= $nights ?> nights</p>
                        <p><strong>Guests:</strong> <?= $booking['adults'] ?> Adults, <?= $booking['children'] ?> Children</p>
                        <p><strong>Total Amount:</strong> $<?= number_format($booking['total_price'], 2) ?></p>
                        <p><strong>Status:</strong> <span class="badge badge-success">Confirmed</span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden PDF Download Button -->
        <a href="<?= $pdf_link ?>" id="pdfDownloadBtn" style="display: none;">Download Receipt</a>

        <div class="text-center">
            <a href="view_bookings.php" class="btn btn-info mr-2">
                <i class="fas fa-list"></i> View All Bookings
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Return to Home
            </a>
        </div>
    </div>

    <script>
        // Auto-trigger PDF download when page loads
        window.onload = function() {
            // Small delay to ensure the page is loaded
            setTimeout(function() {
                window.location.href = "<?= $pdf_link ?>";
            }, 1000);
        };
    </script>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 