<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

try {
    $stats = [
        'total_rooms' => $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn(),
        'available_rooms' => $pdo->query("SELECT COUNT(*) FROM rooms WHERE availability = 1")->fetchColumn(),
        'occupied_rooms' => $pdo->query("SELECT COUNT(*) FROM rooms WHERE availability = 0")->fetchColumn()
    ];
    
    echo json_encode($stats);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
} 