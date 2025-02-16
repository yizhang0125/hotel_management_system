<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

include 'db_connect.php';

$facility_id = $_GET['id'];
$stmt = $pdo->prepare("DELETE FROM facilities WHERE id = ?");
$stmt->execute([$facility_id]);

header('Location: admin_facilities.php');
exit();
?>
