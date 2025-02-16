<?php
session_start();

// Database connection
include 'db_connect.php';

if (isset($_GET['id'])) {
    $facility_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM facilities WHERE id = ?");
    $stmt->execute([$facility_id]);
    $facility = $stmt->fetch();

    if (!$facility) {
        header('Location: facilities.php'); // Redirect to facilities page if facility not found
        exit();
    }
} else {
    header('Location: facilities.php'); // Redirect to facilities page if no ID is provided
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($facility['name']) ?> | Luxury Hotel</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
            padding-top: 80px;
            font-family: 'Poppins', sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-size: 24px;
            font-weight: 700;
        }

        .nav-link {
            font-weight: 500;
            padding: 10px 15px !important;
            transition: all 0.3s;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            border-radius: 5px;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .facility-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .facility-gallery {
            position: relative;
            height: 500px;
            overflow: hidden;
        }

        .carousel-item img {
            width: 100%;
            height: 500px;
            object-fit: cover;
        }

        .carousel-control-prev,
        .carousel-control-next {
            width: 50px;
            height: 50px;
            background: rgba(0,0,0,0.5);
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.7;
        }

        .carousel-control-prev {
            left: 20px;
        }

        .carousel-control-next {
            right: 20px;
        }

        .facility-info {
            padding: 40px;
        }

        .facility-name {
            font-size: 32px;
            font-weight: 700;
            color: #1e3c72;
            margin-bottom: 20px;
        }

        .facility-description {
            color: #6c757d;
            font-size: 16px;
            line-height: 1.8;
            margin-bottom: 30px;
        }

        .facility-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            color: #2c3e50;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .detail-item i {
            width: 40px;
            height: 40px;
            background: #e3f2fd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #1e3c72;
            font-size: 18px;
        }

        .btn-back {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30,60,114,0.2);
            color: white;
        }

        @media (max-width: 768px) {
            .facility-gallery {
                height: 300px;
            }

            .carousel-item img {
                height: 300px;
            }

            .facility-name {
                font-size: 24px;
            }

            .facility-info {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fas fa-hotel"></i> Luxury Hotel
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="view_bookings.php">
                        <i class="fas fa-book-open"></i> Bookings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="view_rooms.php">
                        <i class="fas fa-bed"></i> Rooms
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="view_payments.php">
                        <i class="fas fa-credit-card"></i> Payments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-user-circle"></i> <?php echo $_SESSION['username']; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <?php if ($facility): ?>
        <div class="facility-card">
            <div class="facility-gallery">
                <div id="facilitySlideshow" class="carousel slide" data-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img src="<?= htmlspecialchars($facility['image_path']) ?>" 
                                 alt="<?= htmlspecialchars($facility['name']) ?>">
                        </div>
                        <!-- Add more carousel items for additional images -->
                    </div>
                    <a class="carousel-control-prev" href="#facilitySlideshow" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#facilitySlideshow" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                    </a>
                </div>
            </div>
            <div class="facility-info">
                <h1 class="facility-name"><?= htmlspecialchars($facility['name']) ?></h1>
                <p class="facility-description"><?= htmlspecialchars($facility['description']) ?></p>
                
                <div class="facility-details">
                    <?php if (!empty($facility['location'])): ?>
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?= htmlspecialchars($facility['location']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($facility['contact'])): ?>
                        <div class="detail-item">
                            <i class="fas fa-phone-alt"></i>
                            <span><?= htmlspecialchars($facility['contact']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <a href="index.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i>
                    Back to Home
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Facility details not found.
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
