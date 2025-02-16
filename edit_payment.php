<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $cardholder_name = $_POST['cardholder_name'];
    $expiry_date = $_POST['expiry_date'];

    // Update the payment record
    $stmt = $pdo->prepare("UPDATE payments SET status = ?, cardholder_name = ?, expiry_date = ? WHERE id = ?");
    $stmt->execute([$status, $cardholder_name, $expiry_date, $id]);

    header('Location: admin_view_payments.php');
    exit();
} elseif (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = ?");
    $stmt->execute([$id]);
    $payment = $stmt->fetch();
} else {
    header('Location: admin_view_payments.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Payment</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <a class="navbar-brand" href="#">Admin Dashboard</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="admin_logout.php">Logout</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-5">
    <h1>Edit Payment</h1>
    <form action="edit_payment.php" method="POST">
        <input type="hidden" name="id" value="<?= $payment['id'] ?>">
        <div class="form-group">
            <label>Status</label>
            <input type="text" name="status" class="form-control" value="<?= htmlspecialchars($payment['status']) ?>" required>
        </div>
        <div class="form-group">
            <label>Cardholder Name</label>
            <input type="text" name="cardholder_name" class="form-control" value="<?= htmlspecialchars($payment['cardholder_name']) ?>" required>
        </div>
        <div class="form-group">
            <label>Expiry Date</label>
            <input type="text" name="expiry_date" class="form-control" value="<?= htmlspecialchars($payment['expiry_date']) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Payment</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
