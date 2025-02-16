<?php
if (!isset($_GET['booking_id'])) {
    die("Booking ID not provided.");
}

$booking_id = $_GET['booking_id'];

// Database connection
include 'db_connect.php';

// Fetch booking details from the database with more information
$stmt = $pdo->prepare("SELECT b.*, r.room_type, r.room_number, r.price AS room_price, 
                             b.check_in, b.check_out, b.status, u.username, u.email
                      FROM bookings b 
                      JOIN rooms r ON b.room_id = r.id 
                      JOIN users u ON b.user_id = u.id 
                      WHERE b.id = ?");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking) {
    die("Booking not found.");
}

// Calculate total price
$check_in_date = new DateTime($booking['check_in']);
$check_out_date = new DateTime($booking['check_out']);
$interval = $check_in_date->diff($check_out_date);
$total_days = $interval->days;

$total_price = $total_days * $booking['room_price'];

// Include Dompdf library
require 'vendor/autoload.php';
use Dompdf\Dompdf;

// Create new Dompdf instance
$dompdf = new Dompdf();

// Build HTML content
$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            width: 90%;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .booking-info {
            margin-top: 20px;
        }
        .booking-details {
            margin-bottom: 30px;
        }
        .detail-row {
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .label {
            font-weight: bold;
            color: #333;
            width: 150px;
            display: inline-block;
        }
        .value {
            color: #555;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-style: italic;
        }
        .status-confirmed {
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Booking Receipt</h1>
        
        <div class="booking-details">
            <div class="detail-row">
                <span class="label">Booking ID:</span>
                <span class="value">#' . htmlspecialchars($booking_id) . '</span>
            </div>
            <div class="detail-row">
                <span class="label">Room Type:</span>
                <span class="value">' . htmlspecialchars($booking['room_type']) . '</span>
            </div>
            <div class="detail-row">
                <span class="label">Room Number:</span>
                <span class="value">' . htmlspecialchars($booking['room_number']) . '</span>
            </div>
            <div class="detail-row">
                <span class="label">Check-in:</span>
                <span class="value">' . date('M d, Y', strtotime($booking['check_in'])) . '</span>
            </div>
            <div class="detail-row">
                <span class="label">Check-out:</span>
                <span class="value">' . date('M d, Y', strtotime($booking['check_out'])) . '</span>
            </div>
            <div class="detail-row">
                <span class="label">Duration:</span>
                <span class="value">' . $total_days . ' nights</span>
            </div>
            <div class="detail-row">
                <span class="label">Price per Night:</span>
                <span class="value">$' . number_format($booking['room_price'], 2) . '</span>
            </div>
            <div class="detail-row">
                <span class="label">Total Amount:</span>
                <span class="value">$' . number_format($total_price, 2) . '</span>
            </div>
            <div class="detail-row">
                <span class="label">Status:</span>
                <span class="value status-confirmed">' . htmlspecialchars($booking['status']) . '</span>
            </div>
            <div class="detail-row">
                <span class="label">Guest Name:</span>
                <span class="value">' . htmlspecialchars($booking['username']) . '</span>
            </div>
            <div class="detail-row">
                <span class="label">Email:</span>
                <span class="value">' . htmlspecialchars($booking['email']) . '</span>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for choosing our service!</p>
            <p>For any inquiries, please contact our support team.</p>
        </div>
    </div>
</body>
</html>
';

// Load HTML into Dompdf
$dompdf->loadHtml($html);

// Render the PDF
$dompdf->render();

// Stream the PDF to the browser with automatic download
$dompdf->stream("booking_receipt_$booking_id.pdf", array("Attachment" => 1));
exit();
?>
