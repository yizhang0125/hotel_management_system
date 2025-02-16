<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

include 'db_connect.php';

// Fetch facility details
$facilityId = $_GET['id'];
$stmtFacility = $pdo->prepare("SELECT * FROM facilities WHERE id = ?");
$stmtFacility->execute([$facilityId]);
$facility = $stmtFacility->fetch();

// Fetch additional images for the facility
$stmtImages = $pdo->prepare("SELECT * FROM facility_images WHERE facility_id = ?");
$stmtImages->execute([$facilityId]);
$images = $stmtImages->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($facility['name']) ?> - Details</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container" style="margin-top: 60px;">
    <h1><?= htmlspecialchars($facility['name']) ?></h1>
    <p><?= htmlspecialchars($facility['description']) ?></p>

    <h3>Images</h3>
    <div class="row">
        <?php foreach ($images as $image): ?>
            <div class="col-md-4 mb-3">
                <img src="<?= htmlspecialchars($image['image_path']) ?>" alt="Facility Image" class="img-fluid">
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
