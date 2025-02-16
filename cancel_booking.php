<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

if (isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];
    
    try {
        // Update booking status to cancelled
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$booking_id]);
        
        $_SESSION['success'] = "Booking cancelled successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error cancelling booking: " . $e->getMessage();
    }
}

header('Location: admin_manage_bookings.php');
exit(); 