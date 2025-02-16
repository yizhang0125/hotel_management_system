<?php
session_start();
include 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Check if booking ID is provided
if (!isset($_GET['id'])) {
    header('Location: admin_manage_bookings.php');
    exit();
}

try {
    // Get the booking ID
    $booking_id = $_GET['id'];

    // First check if booking exists
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();

    if (!$booking) {
        $_SESSION['error'] = "Booking not found.";
        header('Location: admin_manage_bookings.php');
        exit();
    }

    // Delete the booking
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->execute([$booking_id]);

    // Set success message
    $_SESSION['success'] = "Booking deleted successfully.";
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Error deleting booking: " . $e->getMessage();
}

// Redirect back to bookings page
header('Location: admin_manage_bookings.php');
exit(); 