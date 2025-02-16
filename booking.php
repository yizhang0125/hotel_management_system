<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get deal ID
$deal_id = isset($_GET['deal_id']) ? $_GET['deal_id'] : null;

if (!$deal_id) {
    header('Location: index.php');
    exit;
}

// Fetch deal details
$stmt = $pdo->prepare("SELECT * FROM deals WHERE id = ? AND end_date >= CURRENT_DATE");
$stmt->execute([$deal_id]);
$deal = $stmt->fetch();

if (!$deal) {
    header('Location: index.php?error=deal_expired');
    exit;
}

// Add these lines after fetching deal details
$start_date = new DateTime($deal['start_date']);
$end_date = new DateTime($deal['end_date']);
$formatted_start = $start_date->format('Y-m-d');
$formatted_end = $end_date->format('Y-m-d');

// Calculate total nights and price right away
$nights = $start_date->diff($end_date)->days;
$price_per_night = $deal['price'];
$discount = $deal['discount'];
$discounted_price = $price_per_night * (1 - $discount/100);
$total_price = $discounted_price * $nights;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Insert booking
        $stmt = $pdo->prepare("
            INSERT INTO deals_booking (
                user_id, 
                deal_id,
                check_in,
                check_out,
                adults,
                children,
                special_requests,
                total_price,
                status,
                booking_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', CURRENT_TIMESTAMP)
        ");
        
        $stmt->execute([
            $_SESSION['user_id'],
            $deal_id,
            $_POST['check_in'],
            $_POST['check_out'],
            $_POST['adults'],
            $_POST['children'],
            $_POST['special_requests'],
            $total_price
        ]);

        $booking_id = $pdo->lastInsertId();
        header("Location: payment1.php?booking_id=" . $booking_id);
        exit;
    } catch (PDOException $e) {
        $error = "Booking failed. Please try again.";
    }
}

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Deal - <?php echo htmlspecialchars($deal['deal_name']); ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        body {
            background: #f8f9fa;
            padding-top: 80px;
        }
        .booking-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .booking-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .deal-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .price-info {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 15px 0;
        }
        .price-row {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .original-price, .discount-info, .discounted-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
        }
        .price-label {
            color: #666;
            font-weight: 500;
        }
        .original-price .price-value {
            text-decoration: line-through;
            color: #999;
        }
        .discounted-price .price-value {
            color: #1e3c72;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .total-price {
            background: #e8f4ff;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        .total-price-details {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .nights-info {
            color: #666;
            font-size: 0.9rem;
        }
        .total-amount {
            font-size: 1.4rem;
            font-weight: bold;
            color: #2c3e50;
        }
        .badge-danger {
            background: #e74c3c;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
        }
        .submit-btn {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            width: 100%;
            margin-top: 20px;
            transition: transform 0.3s ease;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            color: white;
        }
        .deal-image {
            position: relative;
            margin: -20px -20px 20px -20px;
            border-radius: 10px 10px 0 0;
            overflow: hidden;
        }
        .deal-preview-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            display: block;
        }
        .discount-tag {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #e74c3c;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 1;
        }
        .deal-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .deal-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(0deg, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0) 50%);
            pointer-events: none;
        }
        @media (max-width: 768px) {
            .deal-preview-image {
                height: 200px;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-hotel"></i> Luxury Hotel
        </a>
    </div>
</nav>

<div class="booking-container">
    <div class="booking-card">
        <h2 class="mb-4">Book Your Stay</h2>

        <div class="deal-summary">
            <div class="deal-image">
                <?php if (!empty($deal['image_path'])): ?>
                    <img src="uploads/<?= htmlspecialchars($deal['image_path']) ?>" 
                         alt="<?= htmlspecialchars($deal['deal_name']) ?>"
                         class="deal-preview-image">
                <?php else: ?>
                    <img src="images/default-deal.jpg" 
                         alt="Default Deal Image"
                         class="deal-preview-image">
                <?php endif; ?>
                <div class="discount-tag">
                    <?= $deal['discount'] ?>% OFF
                </div>
            </div>
            <h4><?php echo htmlspecialchars($deal['deal_name']); ?></h4>
            <p><?php echo htmlspecialchars($deal['description']); ?></p>
            <div class="price-info">
                <div class="price-row">
                    <div class="original-price">
                        <span class="price-label">Original Price:</span>
                        <span class="price-value">$<?php echo number_format($deal['price'], 2); ?> per night</span>
                    </div>
                    <div class="discount-info">
                        <span class="price-label">Discount:</span>
                        <span class="badge badge-danger">
                            <?php echo $deal['discount']; ?>% OFF
                        </span>
                    </div>
                    <div class="discounted-price">
                        <span class="price-label">Discounted Price:</span>
                        <span class="price-value">$<?php echo number_format($deal['price'] * (1 - $deal['discount']/100), 2); ?> per night</span>
                    </div>
                </div>
            </div>
            <div class="total-price mt-3" id="totalPrice">
                <div class="total-price-details">
                    <div class="nights-info">
                        <?php echo $nights; ?> night<?php echo $nights > 1 ? 's' : ''; ?> × $<?php echo number_format($discounted_price, 2); ?>
                    </div>
                    <div class="total-amount">
                        Total: $<?php echo number_format($total_price, 2); ?>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt"></i> Check In Date</label>
                        <input type="text" class="form-control" name="check_in" 
                               value="<?php echo $formatted_start; ?>" readonly disabled>
                        <input type="hidden" name="check_in" value="<?php echo $formatted_start; ?>">
                        <small class="text-muted">Deal starts on <?php echo date('M d, Y', strtotime($deal['start_date'])); ?></small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt"></i> Check Out Date</label>
                        <input type="text" class="form-control" name="check_out" 
                               value="<?php echo $formatted_end; ?>" readonly disabled>
                        <input type="hidden" name="check_out" value="<?php echo $formatted_end; ?>">
                        <small class="text-muted">Deal ends on <?php echo date('M d, Y', strtotime($deal['end_date'])); ?></small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Adults</label>
                        <select class="form-control" id="adults" name="adults" required>
                            <?php for($i = 1; $i <= 4; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-child"></i> Children</label>
                        <select class="form-control" id="children" name="children">
                            <?php for($i = 0; $i <= 3; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-comment"></i> Special Requests</label>
                <textarea class="form-control" id="special_requests" name="special_requests" rows="3"></textarea>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-check-circle"></i> Proceed to Payment
            </button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // No need for date picker initialization since dates are fixed
    const basePrice = <?php echo $deal['price']; ?>;
    const discount = <?php echo $deal['discount']; ?>;
    const discountedPrice = basePrice * (1 - discount/100);
    const totalNights = <?php echo $nights; ?>;
    const totalPrice = discountedPrice * totalNights;

    // Display is now handled by PHP, no need for calculateTotal function
</script>

</body>
</html> 