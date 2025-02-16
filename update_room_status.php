<?php
include 'db_connect.php';

// Get the current date
$current_date = date('Y-m-d');

// Fetch all bookings that are currently active
$stmt = $pdo->prepare("
    SELECT room_id FROM bookings 
    WHERE check_in <= ? AND check_out >= ? AND status = 'confirmed'
");
$stmt->execute([$current_date, $current_date]);
$occupied_rooms = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Set all rooms to available first
$pdo->exec("UPDATE rooms SET availability = 1");

// Set occupied rooms to unavailable
if (!empty($occupied_rooms)) {
    // Prepare placeholders for the IN clause
    $placeholders = implode(',', array_fill(0, count($occupied_rooms), '?'));
    $stmt = $pdo->prepare("UPDATE rooms SET availability = 0 WHERE id IN ($placeholders)");
    $stmt->execute($occupied_rooms);
}

echo "Room statuses updated based on current bookings.";
?> 