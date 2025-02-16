<?php 
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

include 'db_connect.php';

$order_id = $_POST['order_id'];
$status = 'paid'; // Keep payment status as 'paid'
$cardholder_name = $_POST['cardHolder'];
$card_number = preg_replace('/\D/', '', $_POST['cardNumber']); // Remove non-digit characters
$expiry_date = $_POST['cardExpiry']; // Already in MM/YY format

// Ensure expiry date is exactly 5 characters (MM/YY)
if (strlen($expiry_date) !== 5 || !preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $expiry_date)) {
    // Handle invalid expiry date format
    die('Invalid expiry date format. Please use MM/YY.');
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Insert payment into the payments table as 'paid'
    $stmt = $pdo->prepare("INSERT INTO payments (booking_id, status, cardholder_name, card_number, expiry_date) 
                          VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$order_id, $status, $cardholder_name, $card_number, $expiry_date]);

    // Update booking status to 'pending' instead of 'confirmed'
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'pending' WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);

    // Get booking details for email
    $stmt = $pdo->prepare("SELECT b.*, r.room_type, r.price 
                          FROM bookings b 
                          JOIN rooms r ON b.room_id = r.id 
                          WHERE b.id = ?");
    $stmt->execute([$order_id]);
    $booking = $stmt->fetch();

    // Calculate total amount
    $nights = (strtotime($booking['check_out']) - strtotime($booking['check_in'])) / (60 * 60 * 24);
    $total_amount = $booking['price'] * $nights;

    // Commit the transaction
    $pdo->commit();

    // Send email notification
    $to = $_SESSION['user_email'];
    $subject = "Payment Received - Booking #" . $order_id;
    $message = "
    Dear " . $_SESSION['username'] . ",

    Thank you for your payment. Your booking is currently being processed.

    Booking Details:
    - Booking ID: #" . $order_id . "
    - Room Type: " . $booking['room_type'] . "
    - Check-in: " . $booking['check_in'] . "
    - Check-out: " . $booking['check_out'] . "
    - Total Amount: $" . number_format($total_amount, 2) . "

    Your payment has been received and your booking is pending confirmation.
    Our team will review and confirm your booking shortly.
    You will receive another email once your booking is confirmed.

    If you have any questions, please contact our support team.

    Best regards,
    Luxury Hotel Team";

    $headers = "From: noreply@luxuryhotel.com";
    mail($to, $subject, $message, $headers);

    // Store success message in session
    $_SESSION['success_message'] = "Payment successful! Your booking is pending confirmation. We will notify you once it's confirmed.";

    // Redirect to success page
    header('Location: success.php?booking_id=' . $order_id);
    exit();

} catch (PDOException $e) {
    // Rollback the transaction if something went wrong
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Handle errors
    $_SESSION['error_message'] = 'Payment processing failed: ' . $e->getMessage();
    header('Location: payment.php?booking_id=' . $order_id);
    exit();
}
?>
