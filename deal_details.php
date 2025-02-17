<?php
session_start();
include 'db_connect.php';

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get deal ID from URL
$deal_id = $_GET['id'] ?? null;

if (!$deal_id) {
    header('Location: index.php');
    exit();
}

// Fetch deal details
$stmt = $pdo->prepare("
    SELECT * FROM deals 
    WHERE id = ? AND end_date >= CURRENT_DATE
");
$stmt->execute([$deal_id]);
$deal = $stmt->fetch();

if (!$deal) {
    header('Location: index.php');
    exit();
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
    <title><?= htmlspecialchars($deal['deal_name']) ?> | Deal Details</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        /* Add Plus Jakarta Sans font */
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

        body {
            padding-top: 120px; /* Increased to match taller navbar */
            background: #f8f9fa;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .deal-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px;
        }
        .deal-image-wrapper {
            position: sticky;
            top: 100px;
        }
        .deal-image {
            width: 100%;
            height: 500px;
            object-fit: cover;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .deal-info {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .deal-header {
            margin-bottom: 30px;
        }
        .deal-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e3c72;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        .price-info {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .price-label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        .validity-dates {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .date-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .date-item i {
            color: #1e3c72;
            font-size: 1.2rem;
        }
        .date-label {
            display: block;
            color: #666;
            font-size: 0.9rem;
        }
        .date-value {
            display: block;
            color: #1e3c72;
            font-weight: 600;
            font-size: 1.1rem;
        }
        .discount-badge {
            background: #ff4757;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 15px;
        }
        .original-price {
            text-decoration: line-through;
            color: #777;
            font-size: 1.2rem;
        }
        .discounted-price {
            font-size: 2rem;
            color: #2ecc71;
            font-weight: bold;
        }
        .validity {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .navbar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 45px 0; /* Increased padding to match index.php */
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-size: 24px;
            font-weight: 700;
        }

        .navbar .container {
            max-width: 1400px;
            padding: 0 30px;
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

        @media (max-width: 991px) {
            .deal-container {
                padding: 20px;
            }
            
            .deal-image-wrapper {
                position: relative;
                top: 0;
                margin-bottom: 30px;
            }
            
            .deal-image {
                height: 300px;
            }
            
            .deal-info {
                padding: 25px;
            }
            
            .deal-header h1 {
                font-size: 2rem;
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

        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 40px;
        }

        .btn-lg {
            padding: 18px 35px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border: none;
            box-shadow: 0 8px 20px rgba(30,60,114,0.2);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(30,60,114,0.3);
        }

        @media (max-width: 991px) {
            .action-buttons {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Update the navbar structure -->
    <?php include 'user/navbar.php'; ?>

    <div class="deal-container">
        <div class="row">
            <!-- Left Column - Image -->
            <div class="col-lg-6">
                <div class="deal-image-wrapper">
                    <img src="uploads/<?= htmlspecialchars($deal['image_path']) ?>" 
                         alt="<?= htmlspecialchars($deal['deal_name']) ?>" 
                         class="deal-image">
                </div>
            </div>
            
            <!-- Right Column - Deal Info -->
            <div class="col-lg-6">
                <div class="deal-info">
                    <div class="deal-header">
                        <h1><?= htmlspecialchars($deal['deal_name']) ?></h1>
                        <div class="discount-badge">
                            <i class="fas fa-tag"></i> <?= htmlspecialchars($deal['discount']) ?>% OFF
                        </div>
                    </div>

                    <div class="price-info">
                        <div class="price-label">Regular Price:</div>
                        <div class="original-price">
                            $<?= number_format($deal['price'], 2) ?>
                        </div>
                        <div class="price-label">Deal Price:</div>
                        <div class="discounted-price">
                            $<?= number_format($deal['price'] * (1 - $deal['discount']/100), 2) ?>
                        </div>
                    </div>

                    <div class="description">
                        <?= nl2br(htmlspecialchars($deal['description'])) ?>
                    </div>

                    <div class="validity">
                        <h5><i class="far fa-calendar-alt"></i> Validity Period</h5>
                        <div class="validity-dates">
                            <div class="date-item">
                                <i class="fas fa-play"></i>
                                <div>
                                    <span class="date-label">From:</span>
                                    <span class="date-value"><?= date('F d, Y', strtotime($deal['start_date'])) ?></span>
                                </div>
                            </div>
                            <div class="date-item">
                                <i class="fas fa-stop"></i>
                                <div>
                                    <span class="date-label">Until:</span>
                                    <span class="date-value"><?= date('F d, Y', strtotime($deal['end_date'])) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <?php if (isLoggedIn()): ?>
                            <a href="booking.php?deal_id=<?= $deal['id'] ?>" class="btn btn-primary btn-lg">
                                <i class="fas fa-bookmark"></i> Book Now
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Login to Book
                            </a>
                        <?php endif; ?>
                        
                        <a href="index.php" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-arrow-left"></i> Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 