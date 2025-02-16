<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

include 'db_connect.php';

$room_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->execute([$room_id]);
$room = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Details</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .container {
            margin-top: 60px;
        }
        .room-details {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .room-image {
            max-height: 400px;
            object-fit: cover;
            border-radius: 10px;
        }
        .room-title {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #333;
        }
        .room-info {
            font-size: 1.2rem;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <a class="navbar-brand" href="#"><i class="fas fa-home"></i> Admin Dashboard</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="admin_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container">
    <h1 class="text-center mb-4">Room Details</h1>

    <?php if ($room): ?>
        <div class="room-details">
            <div class="row">
                <div class="col-md-6">
                    <img src="<?= htmlspecialchars($room['image_path']) ?>" alt="<?= htmlspecialchars($room['room_type']) ?>" class="img-fluid room-image">
                </div>
                <div class="col-md-6">
                    <h2 class="room-title"><?= htmlspecialchars($room['room_type']) ?></h2>
                    <p class="room-info"><strong>Description:</strong> <?= htmlspecialchars($room['description']) ?></p>
                    <p class="room-info"><strong>Price:</strong> $<?= number_format($room['price'], 2) ?></p>
                    <p class="room-info"><strong>Availability:</strong> <?= $room['availability'] ? 'Available' : 'Not Available' ?></p>
                    <p class="room-info"><strong>Max Adults:</strong> <?= htmlspecialchars($room['max_adults']) ?></p>
                    <p class="room-info"><strong>Max Children:</strong> <?= htmlspecialchars($room['max_children']) ?></p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <p class="text-center">No details found for the selected room.</p>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
