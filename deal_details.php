<?php
session_start();
include 'db_connect.php';

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get deal ID from URL
$deal_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$deal_id) {
    header('Location: index.php');
    exit;
}

// Fetch deal details
$stmt = $pdo->prepare("SELECT * FROM deals WHERE id = ?");
$stmt->execute([$deal_id]);
$deal = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$deal) {
    header('Location: index.php');
    exit;
}

// Debug information
echo "<!-- Debug: Image path = " . $deal['image_path'] . " -->";
echo "<!-- Debug: Full path = uploads/" . $deal['image_path'] . " -->";
echo "<!-- Debug: File exists = " . (file_exists('uploads/' . $deal['image_path']) ? 'Yes' : 'No') . " -->";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($deal['deal_name']); ?> - Special Deal</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        .navbar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 15px 0;
        }

        .deal-banner {
            position: relative;
            height: 400px;
            background-size: cover;
            background-position: center;
            margin-top: 76px;
            background-color: #f8f9fa; /* Fallback color */
        }

        /* Add a default background if image fails to load */
        .deal-banner:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.4);
        }

        .banner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: white;
            text-align: center;
        }

        .deal-details {
            padding: 40px 0;
        }

        .price-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .discount-badge {
            background: #e74c3c;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9rem;
            margin-left: 10px;
        }

        .book-now-btn {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            width: 100%;
            margin-top: 20px;
            transition: transform 0.3s ease;
        }

        .book-now-btn:hover {
            transform: translateY(-2px);
            color: white;
        }

        @media (max-width: 768px) {
            .deal-banner {
                height: 300px;
                margin-top: 66px;
            }

            .deal-details {
                padding: 20px 0;
            }
        }

        .pricing-details {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 15px;
        }

        .pricing-details h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .price-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .original-price, .discount, .final-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .original-price .amount {
            text-decoration: line-through;
            color: #999;
        }

        .discount .amount {
            color: #e74c3c;
            font-weight: bold;
        }

        .final-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: #2c3e50;
            background: #e8f4ff;
            padding: 10px;
            border-radius: 8px;
        }

        .price-breakdown {
            text-align: center;
            margin: 20px 0;
        }

        .price-breakdown .original {
            color: #999;
            text-decoration: line-through;
            font-size: 1.1rem;
        }

        .price-breakdown .final {
            color: #2c3e50;
            font-size: 1.8rem;
            font-weight: bold;
        }

        .discount-badge {
            background: #e74c3c;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-hotel"></i> Luxury Hotel
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="view_bookings.php">
                            <i class="fas fa-book-open"></i> My Bookings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Deal Banner -->
<div class="deal-banner" style="background-image: url('uploads/<?php echo htmlspecialchars($deal['image_path']); ?>');">
    <div class="banner-overlay">
        <h1><?php echo htmlspecialchars($deal['deal_name']); ?></h1>
        <p class="lead">Limited Time Special Offer</p>
    </div>
</div>

<!-- Deal Details -->
<div class="deal-details">
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <h2>Deal Description</h2>
                <p class="lead"><?php echo nl2br(htmlspecialchars($deal['description'])); ?></p>
                
                <!-- Add pricing details -->
                <div class="pricing-details">
                    <h3><i class="fas fa-tag"></i> Pricing Information</h3>
                    <div class="price-card">
                        <div class="original-price">
                            <span class="label">Original Price:</span>
                            <span class="amount">$<?= number_format($deal['price'], 2) ?></span>
                        </div>
                        <div class="discount">
                            <span class="label">Discount:</span>
                            <span class="amount"><?= $deal['discount'] ?>% OFF</span>
                        </div>
                        <div class="final-price">
                            <span class="label">Final Price:</span>
                            <span class="amount">$<?= number_format($deal['price'] * (1 - $deal['discount']/100), 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="price-card">
                    <h4>Special Deal</h4>
                    <div class="discounted-price">
                        <span class="discount-badge"><?php echo $deal['discount']; ?>% OFF</span>
                        <div class="price-breakdown">
                            <div class="original">
                                <s>$<?= number_format($deal['price'], 2) ?></s>
                            </div>
                            <div class="final">
                                $<?= number_format($deal['price'] * (1 - $deal['discount']/100), 2) ?>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="deal-info">
                        <p><i class="fas fa-calendar-alt"></i> Available until: <?php echo date('F d, Y', strtotime($deal['end_date'])); ?></p>
                        <p><i class="fas fa-clock"></i> Valid from: <?php echo date('F d, Y', strtotime($deal['start_date'])); ?></p>
                    </div>
                    
                    <?php if (isLoggedIn()): ?>
                        <a href="booking.php?deal_id=<?php echo $deal_id; ?>" class="btn book-now-btn">
                            Book Now
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn book-now-btn">
                            Login to Book
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html> 