<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

include 'db_connect.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM payments WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: admin_view_payments.php');
    exit();
} else {
    header('Location: admin_view_payments.php');
    exit();
}
?>
