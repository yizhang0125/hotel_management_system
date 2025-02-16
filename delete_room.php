<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

include 'db_connect.php';

// Get room ID from URL
$room_id = $_GET['id'];

try {
    // Prepare and execute delete statement
    $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);

    // Redirect to admin dashboard after deletion
    header('Location: rooms.php');
    exit();
} catch (PDOException $e) {
    // Handle errors (optional)
    echo "Error: " . $e->getMessage();
}
?>
