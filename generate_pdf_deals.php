<?php
if (!isset($_GET['booking_id'])) {
    die("Booking ID not provided.");
}

$booking_id = $_GET['booking_id'];

// Database connection
include 'db_connect.php';

// Fetch booking details with deal information
$stmt = $pdo->prepare("
    SELECT b.*, d.deal_name, d.price, d.discount, d.image_path,
           p.cardholder_name, p.card_number, p.payment_date, u.username, u.email
    FROM deals_booking b 
    JOIN deals d ON b.deal_id = d.id
    JOIN deals_payment p ON b.id = p.booking_id
    JOIN users u ON b.user_id = u.id 
    WHERE b.id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking) {
    die("Booking not found.");
}

// Calculate total days and discounted price
$check_in_date = new DateTime($booking['check_in']);
$check_out_date = new DateTime($booking['check_out']);
$interval = $check_in_date->diff($check_out_date);
$total_days = $interval->days;

$original_price = $booking['price'];
$discounted_price = $original_price * (1 - $booking['discount']/100);
$total_price = $booking['total_price'];

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
            color: #333;
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .hotel-header {
            text-align: center;
            border-bottom: 2px solid #1e3c72;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .hotel-name {
            font-size: 28px;
            color: #1e3c72;
            margin: 0;
            font-weight: bold;
        }
        .hotel-info {
            color: #666;
            font-size: 14px;
            margin: 5px 0;
        }
        .receipt-title {
            text-align: center;
            font-size: 22px;
            margin: 20px 0;
            color: #1e3c72;
        }
        .receipt-number {
            text-align: right;
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }
        .booking-details {
            margin: 30px 0;
            border: 1px solid #eee;
            padding: 20px;
            background: #f9f9f9;
        }
        .section-title {
            font-size: 18px;
            color: #1e3c72;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            font-size: 14px;
        }
        .label {
            color: #666;
            font-weight: bold;
            width: 150px;
        }
        .value {
            color: #333;
            flex: 1;
        }
        .price-breakdown {
            margin: 30px 0;
            border: 1px solid #eee;
            padding: 20px;
        }
        .price-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            font-size: 14px;
        }
        .price-row.total {
            border-top: 2px solid #ddd;
            margin-top: 15px;
            padding-top: 15px;
            font-size: 16px;
            font-weight: bold;
            color: #1e3c72;
        }
        .discount {
            color: #e74c3c;
        }
        .payment-info {
            margin: 30px 0;
            padding: 20px;
            background: #f9f9f9;
            border: 1px solid #eee;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="hotel-header">
            <h1 class="hotel-name">LUXURY HOTEL</h1>
            <p class="hotel-info">123 Luxury Avenue, Paradise City</p>
            <p class="hotel-info">Tel: +1 234 567 8900 | Email: info@luxuryhotel.com</p>
        </div>

        <h2 class="receipt-title">BOOKING CONFIRMATION</h2>
        <div class="receipt-number">Receipt #' . str_pad($booking_id, 6, '0', STR_PAD_LEFT) . '</div>

        <div class="booking-details">
            <h3 class="section-title">Guest Information</h3>
            <div class="detail-row">
                <span class="label">Guest Name:</span>
                <span class="value">' . htmlspecialchars($booking['username']) . '</span>
            </div>
            <div class="detail-row">
                <span class="label">Email:</span>
                <span class="value">' . htmlspecialchars($booking['email']) . '</span>
            </div>
            <div class="detail-row">
                <span class="label">Booking Date:</span>
                <span class="value">' . date('F d, Y', strtotime($booking['booking_date'])) . '</span>
            </div>
        </div>

        <div class="booking-details">
            <h3 class="section-title">Stay Details</h3>
            <div class="detail-row">
                <span class="label">Package:</span>
                <span class="value">' . htmlspecialchars($booking['deal_name']) . '</span>
            </div>
            <div class="detail-row">
                <span class="label">Check In:</span>
                <span class="value">' . date('F d, Y', strtotime($booking['check_in'])) . '</span>
            </div>
            <div class="detail-row">
                <span class="label">Check Out:</span>
                <span class="value">' . date('F d, Y', strtotime($booking['check_out'])) . '</span>
            </div>
            <div class="detail-row">
                <span class="label">Duration:</span>
                <span class="value">' . $total_days . ' night(s)</span>
            </div>
            <div class="detail-row">
                <span class="label">Guests:</span>
                <span class="value">' . $booking['adults'] . ' Adult(s), ' . $booking['children'] . ' Children</span>
            </div>
        </div>

        <div class="price-breakdown">
            <h3 class="section-title">Price Details</h3>
            <div class="price-row">
                <span>Original Price (' . $total_days . ' nights × $' . number_format($original_price, 2) . ')</span>
                <span>$' . number_format($original_price * $total_days, 2) . '</span>
            </div>
            <div class="price-row discount">
                <span>Special Discount (' . $booking['discount'] . '% OFF)</span>
                <span>-$' . number_format(($original_price * $total_days) - $total_price, 2) . '</span>
            </div>
            <div class="price-row total">
                <span>Total Amount</span>
                <span>$' . number_format($total_price, 2) . '</span>
            </div>
        </div>

        <div class="payment-info">
            <h3 class="section-title">Payment Information</h3>
            <div class="detail-row">
                <span class="label">Payment Method:</span>
                <span class="value">Visa Card ending in ' . substr($booking['card_number'], -4) . '</span>
            </div>
            <div class="detail-row">
                <span class="label">Payment Date:</span>
                <span class="value">' . date('F d, Y H:i', strtotime($booking['payment_date'])) . '</span>
            </div>
            <div class="detail-row">
                <span class="label">Status:</span>
                <span class="value">Confirmed</span>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for choosing Luxury Hotel. We look forward to welcoming you!</p>
            <p>For any inquiries, please contact our support team.</p>
            <p>This is a computer-generated receipt. No signature required.</p>
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
$dompdf->stream("deal_booking_receipt_$booking_id.pdf", array("Attachment" => 1));
exit();
?> 