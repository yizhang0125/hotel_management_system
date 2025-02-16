<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
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
    <title><?= htmlspecialchars($room['room_type']) ?> | Luxury Hotel</title>
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

        .room-details-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .room-gallery {
            position: relative;
            height: 400px;
            overflow: hidden;
        }

        .room-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .room-info {
            padding: 30px;
        }

        .room-type {
            font-size: 32px;
            font-weight: 700;
            color: #1e3c72;
            margin-bottom: 20px;
        }

        .room-description {
            color: #6c757d;
            font-size: 16px;
            line-height: 1.8;
            margin-bottom: 30px;
        }

        .room-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .feature {
            display: flex;
            align-items: center;
            color: #2c3e50;
        }

        .feature i {
            width: 30px;
            height: 30px;
            background: #e3f2fd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            color: #1e3c72;
        }

        .price-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .price {
            font-size: 36px;
            font-weight: 700;
            color: #1e3c72;
        }

        .price small {
            font-size: 16px;
            color: #6c757d;
            font-weight: normal;
        }

        .btn-book {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 18px;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-book:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30,60,114,0.2);
            color: white;
        }

        @media (max-width: 768px) {
            .room-gallery {
                height: 300px;
            }
            
            .room-type {
                font-size: 24px;
            }
            
            .price {
                font-size: 28px;
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
                <li class="nav-item active">
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
    <?php if ($room): ?>
        <div class="room-details-card">
            <div class="room-gallery">
                <img src="<?= htmlspecialchars($room['image_path']) ?>" alt="<?= htmlspecialchars($room['room_type']) ?>" class="room-image">
            </div>
            <div class="room-info">
                <h1 class="room-type"><?= htmlspecialchars($room['room_type']) ?></h1>
                <p class="room-description"><?= htmlspecialchars($room['description']) ?></p>
                
                <div class="room-features">
                    <div class="feature">
                        <i class="fas fa-users"></i>
                        <span>Max Occupancy: 4 persons</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-wifi"></i>
                        <span>Free High-speed WiFi</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-tv"></i>
                        <span>50" Smart TV</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-snowflake"></i>
                        <span>Air Conditioning</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-coffee"></i>
                        <span>Coffee Maker</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-bath"></i>
                        <span>Private Bathroom</span>
                    </div>
                </div>

                <div class="price-section">
                    <div class="price">
                        $<?= number_format($room['price'], 2) ?> <small>per night</small>
                    </div>
                </div>

                <?php if ($room['availability']): ?>
                    <a href="book_room.php?id=<?= $room['id'] ?>" class="btn btn-book">
                        <i class="fas fa-calendar-check"></i> Book Now
                    </a>
                <?php else: ?>
                    <button class="btn btn-book" disabled>
                        <i class="fas fa-ban"></i> Not Available
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Room details not found.
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
