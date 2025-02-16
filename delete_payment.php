<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

include 'db_connect.php';

if (!isset($_GET['id'])) {
    header('Location: admin_view_payments.php');
    exit();
}

$payment_id = $_GET['id'];

try {
    // Start transaction
    $pdo->beginTransaction();

    // Get booking_id before deleting payment
    $stmt = $pdo->prepare("SELECT booking_id FROM payments WHERE id = ?");
    $stmt->execute([$payment_id]);
    $payment = $stmt->fetch();

    if ($payment) {
        // Delete the payment
        $stmt = $pdo->prepare("DELETE FROM payments WHERE id = ?");
        $stmt->execute([$payment_id]);

        // Update booking status to pending
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'pending' WHERE id = ?");
        $stmt->execute([$payment['booking_id']]);

        $pdo->commit();
        $_SESSION['success'] = "Payment deleted successfully.";
    }
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Error deleting payment: " . $e->getMessage();
}

header('Location: admin_view_payments.php');
exit();
?>
