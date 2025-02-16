<?php
include 'db_connect.php';

header('Content-Type: application/json');

$room_id = $_GET['room_id'] ?? null;
if (!$room_id) {
    echo json_encode(['error' => 'Room ID is required']);
    exit;
}

// Get all booked dates for this room where status is confirmed or pending
$stmt = $pdo->prepare("
    SELECT check_in, check_out 
    FROM bookings 
    WHERE room_id = ? 
    AND (status = 'confirmed' OR status = 'pending')
    AND (
        check_in >= CURRENT_DATE 
        OR check_out >= CURRENT_DATE
    )
");

$stmt->execute([$room_id]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format the dates for the frontend
$bookedDates = [];
foreach ($bookings as $booking) {
    $start = new DateTime($booking['check_in']);
    $end = new DateTime($booking['check_out']);
    
    // Include all dates between check-in and check-out
    while ($start <= $end) {
        $bookedDates[] = $start->format('Y-m-d');
        $start->modify('+1 day');
    }
}

echo json_encode([
    'booked_dates' => array_values(array_unique($bookedDates))
]); 