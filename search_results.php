<?php
include 'db_connect.php';

// Get search parameters
$checkin = $_GET['checkin'] ?? null;
$checkout = $_GET['checkout'] ?? null;
$adults = $_GET['adults'] ?? 1;
$children = $_GET['children'] ?? 0;

try {
    // Query to get available rooms that match the criteria
    $query = "
        SELECT DISTINCT r.* 
        FROM rooms r 
        WHERE r.max_adults >= :adults 
        AND r.max_children >= :children
        AND r.id NOT IN (
            SELECT b.room_id 
            FROM bookings b 
            WHERE b.status IN ('confirmed', 'pending')
            AND (
                (b.check_in <= :checkout AND b.check_out >= :checkin)
                OR (b.check_in BETWEEN :checkin AND :checkout)
                OR (b.check_out BETWEEN :checkin AND :checkout)
            )
        )
        ORDER BY r.room_type ASC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':adults' => $adults,
        ':children' => $children,
        ':checkin' => $checkin,
        ':checkout' => $checkout
    ]);
    
    $available_rooms = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results | Luxury Hotel</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1e3c72;
            --secondary: #2a5298;
            --accent: #ffc107;
            --text-dark: #2c3e50;
            --text-light: #666;
            --bg-light: #f8f9fa;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-light);
            color: var(--text-dark);
            padding-top: 80px;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }

        .search-summary {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }

        .search-summary::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 150px;
            height: 100%;
            background: linear-gradient(135deg, transparent 0%, rgba(30,60,114,0.05) 100%);
        }

        .search-summary h4 {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .search-details {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .detail {
            background: rgba(30,60,114,0.05);
            padding: 12px 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
        }

        .detail:hover {
            transform: translateY(-2px);
            background: rgba(30,60,114,0.1);
        }

        .detail i {
            color: var(--primary);
            font-size: 1.2rem;
        }

        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .room-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: var(--transition);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .room-image {
            height: 250px;
            position: relative;
            overflow: hidden;
        }

        .room-image::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50%;
            background: linear-gradient(to top, rgba(0,0,0,0.4), transparent);
        }

        .room-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .room-card:hover .room-image img {
            transform: scale(1.1);
        }

        .room-details {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .room-type {
            font-size: 24px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .room-description {
            color: var(--text-light);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .room-features {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 20px;
        }

        .feature-badge {
            background: rgba(30,60,114,0.1);
            color: var(--primary);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: var(--transition);
        }

        .feature-badge:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        .room-price {
            font-size: 26px;
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: baseline;
            gap: 5px;
        }

        .price-period {
            font-size: 14px;
            color: var(--text-light);
            font-weight: 400;
        }

        .btn-book {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: var(--transition);
            text-decoration: none;
            margin-top: auto;
        }

        .btn-book:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30,60,114,0.2);
            color: white;
        }

        .no-rooms-message {
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .no-rooms-message i {
            font-size: 48px;
            color: #dc3545;
            margin-bottom: 20px;
        }

        .no-rooms-message h3 {
            color: var(--primary);
            margin-bottom: 15px;
        }

        .btn-new-search {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            margin-top: 20px;
        }

        .btn-new-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30,60,114,0.2);
            color: white;
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
            
            .search-summary {
                padding: 20px;
            }

            .detail {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .room-image {
                height: 200px;
            }

            .room-type {
                font-size: 20px;
            }

            .room-price {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="search-summary">
        <h4>Search Results</h4>
        <div class="search-details">
            <div class="detail">
                <i class="fas fa-calendar-check"></i>
                <span>Check-in: <?= date('F j, Y', strtotime($checkin)) ?></span>
            </div>
            <div class="detail">
                <i class="fas fa-calendar-times"></i>
                <span>Check-out: <?= date('F j, Y', strtotime($checkout)) ?></span>
            </div>
            <div class="detail">
                <i class="fas fa-user-friends"></i>
                <span><?= $adults ?> Adult<?= $adults > 1 ? 's' : '' ?></span>
            </div>
            <div class="detail">
                <i class="fas fa-child"></i>
                <span><?= $children ?> Child<?= $children > 1 ? 'ren' : '' ?></span>
            </div>
        </div>
    </div>

    <?php if (!empty($available_rooms)): ?>
        <div class="rooms-grid">
            <?php foreach ($available_rooms as $room): ?>
                <div class="room-card">
                    <!-- Your existing room card HTML -->
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
                                <i class="fas fa-user-friends"></i> Max <?= htmlspecialchars($room['max_adults']) ?> Adults
                            </span>
                            <span class="feature-badge">
                                <i class="fas fa-child"></i> Max <?= htmlspecialchars($room['max_children']) ?> Children
                            </span>
                            <!-- Other features -->
                        </div>
                        <div class="room-price">
                            $<?= number_format($room['price'], 2) ?> <span class="price-period">/night</span>
                        </div>
                        <a href="book_room.php?id=<?= $room['id'] ?>&check_in=<?= urlencode($checkin) ?>&check_out=<?= urlencode($checkout) ?>&adults=<?= $adults ?>&children=<?= $children ?>" 
                           class="btn-book">
                            <i class="fas fa-calendar-check"></i> Book Now
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-rooms-message">
            <i class="fas fa-exclamation-circle"></i>
            <h3>No Available Rooms Found</h3>
            <p>We couldn't find any rooms matching your criteria for the selected dates.</p>
            <p>Please try different dates or adjust your guest numbers.</p>
            <a href="index.php" class="btn-new-search">
                <i class="fas fa-search"></i> Start New Search
            </a>
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
<?php
} catch (PDOException $e) {
    // Handle database errors
    echo "Error: " . $e->getMessage();
}
?>