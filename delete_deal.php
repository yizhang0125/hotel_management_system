<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

include 'db_connect.php';

$id = $_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM deals WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: best_deals.php');
    exit();
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
