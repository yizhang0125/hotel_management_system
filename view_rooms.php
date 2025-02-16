<?php
session_start();

include 'db_connect.php';
include 'functions.php';  // Include shared functions

include 'user/navbar.php';

// Fetch all rooms
$stmt = $pdo->query("SELECT * FROM rooms ORDER BY room_type ASC");
$rooms = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Rooms | Luxury Hotel</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            padding-top: 120px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1a1a1a;
        }

        .navbar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 45px 0;
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

        .page-header {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.05);
            margin-bottom: 50px;
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h1 {
            font-size: 32px;
            font-weight: 700;
            margin: 0;
            color: #1e3c72;
        }

        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 40px;
            margin-bottom: 50px;
        }

        .room-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 30px rgba(0,0,0,0.05);
            transition: all 0.4s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .room-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(30,60,114,0.15);
        }

        .room-image {
            position: relative;
            height: 300px;
            overflow: hidden;
        }

        .room-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .room-card:hover .room-image img {
            transform: scale(1.1);
        }

        .room-details {
            padding: 30px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .room-type {
            font-size: 24px;
            font-weight: 700;
            color: #1e3c72;
            margin-bottom: 15px;
        }

        .room-description {
            color: #666;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .room-features {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .feature-badge {
            background: rgba(30,60,114,0.05);
            color: #1e3c72;
            padding: 8px 15px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .feature-badge i {
            color: #2a5298;
        }

        .room-price {
            font-size: 28px;
            font-weight: 700;
            color: #1e3c72;
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid rgba(0,0,0,0.05);
        }

        .room-price small {
            font-size: 16px;
            font-weight: 500;
            color: #666;
        }

        .room-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .btn-book {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            flex: 1;
            text-align: center;
            text-decoration: none;
        }

        .btn-book:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30,60,114,0.3);
            color: white;
        }

        .btn-details {
            background: rgba(30,60,114,0.05);
            color: #1e3c72;
            padding: 12px 25px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            flex: 1;
            text-align: center;
            text-decoration: none;
        }

        .btn-details:hover {
            background: rgba(30,60,114,0.1);
            transform: translateY(-2px);
            color: #1e3c72;
        }

        @media (max-width: 992px) {
            .rooms-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .rooms-grid {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                padding: 30px;
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }

            .room-image {
                height: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-bed"></i> Our Luxury Rooms</h1>
        </div>

        <div class="rooms-grid">
            <?php foreach ($rooms as $room): ?>
                <div class="room-card">
                    <div class="room-image">
                        <img src="<?= htmlspecialchars($room['image_path']) ?>" 
                             alt="<?= htmlspecialchars($room['room_type']) ?>">
                    </div>
                    <div class="room-details">
                        <h3 class="room-type"><?= htmlspecialchars($room['room_type']) ?></h3>
                        <div class="room-description">
                            <?= htmlspecialchars($room['description']) ?>
                        </div>
                        <div class="room-features">
                            <span class="feature-badge">
                                <i class="fas fa-user-friends"></i> <?= htmlspecialchars($room['max_adults']) ?> Adults
                            </span>
                            <span class="feature-badge">
                                <i class="fas fa-child"></i> <?= htmlspecialchars($room['max_children']) ?> Children
                            </span>
                            <?php if (isset($room['room_size']) && $room['room_size'] > 0): ?>
                                <span class="feature-badge">
                                    <i class="fas fa-expand-arrows-alt"></i> <?= htmlspecialchars($room['room_size']) ?> m²
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="room-price">
                            $<?= number_format($room['price'], 2) ?> <small>per night</small>
                        </div>
                        <div class="room-actions">
                            <a href="book_room.php?id=<?= $room['id'] ?>" class="btn-book">
                                <i class="fas fa-calendar-check"></i> Book Now
                            </a>
                            <a href="room_details.php?id=<?= $room['id'] ?>" class="btn-details">
                                <i class="fas fa-info-circle"></i> Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
