<?php 
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'db_connect.php';

$booking_id = $_POST['booking_id'];
$status = 'paid';
$cardholder_name = $_POST['cardHolder'];
$card_number = preg_replace('/\D/', '', $_POST['cardNumber']); // Remove non-digit characters
$expiry_date = $_POST['cardExpiry'];
$total_price = $_POST['total_price'];

// Ensure expiry date is exactly 5 characters (MM/YY)
if (strlen($expiry_date) !== 5 || !preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $expiry_date)) {
    die('Invalid expiry date format. Please use MM/YY.');
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Insert payment into the deals_payment table
    $stmt = $pdo->prepare("
        INSERT INTO deals_payment (
            booking_id, 
            status, 
            cardholder_name, 
            card_number, 
            expiry_date,
            amount
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $booking_id, 
        $status, 
        $cardholder_name, 
        $card_number, 
        $expiry_date,
        $total_price
    ]);

    // Update booking status to 'confirmed'
    $stmt = $pdo->prepare("
        UPDATE deals_booking 
        SET status = 'confirmed' 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);

    // Get booking details for email
    $stmt = $pdo->prepare("
        SELECT b.*, d.deal_name, d.price, d.discount 
        FROM deals_booking b 
        JOIN deals d ON b.deal_id = d.id 
        WHERE b.id = ?
    ");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();

    // Commit the transaction
    $pdo->commit();

    // Send confirmation email
    $to = $_SESSION['user_email'];
    $subject = "Payment Confirmed - Booking #" . $booking_id;
    $message = "
    Dear " . $_SESSION['username'] . ",

    Thank you for your payment. Your booking has been confirmed.

    Booking Details:
    - Booking ID: #" . $booking_id . "
    - Deal: " . $booking['deal_name'] . "
    - Check-in: " . $booking['check_in'] . "
    - Check-out: " . $booking['check_out'] . "
    - Number of Guests: " . $booking['adults'] . " Adults, " . $booking['children'] . " Children
    - Total Amount Paid: $" . number_format($total_price, 2) . "

    Your booking is now confirmed. We look forward to welcoming you!

    Best regards,
    Luxury Hotel Team";

    $headers = "From: noreply@luxuryhotel.com";
    mail($to, $subject, $message, $headers);

    // Store success message in session
    $_SESSION['success_message'] = "Payment successful! Your booking is confirmed.";

    // Redirect to success page
    header('Location: booking_success.php?booking_id=' . $booking_id);
    exit();

} catch (PDOException $e) {
    // Rollback the transaction if something went wrong
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Handle errors
    $_SESSION['error_message'] = 'Payment processing failed: ' . $e->getMessage();
    header('Location: payment1.php?booking_id=' . $booking_id);
    exit();
}
?> 