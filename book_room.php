<?php
session_start();
include 'db_connect.php';
include 'functions.php';  // Include shared functions
include 'user/navbar.php';  // Add the shared navbar

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get parameters from search results
$room_id = $_GET['id'] ?? null;
$check_in = $_GET['check_in'] ?? '';
$check_out = $_GET['check_out'] ?? '';
$adults = $_GET['adults'] ?? 1;
$children = $_GET['children'] ?? 0;

// If form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $pdo->beginTransaction();

        // Insert booking
        $stmt = $pdo->prepare("
            INSERT INTO bookings (
                user_id, room_id, check_in, check_out, 
                adults, children, username, email_address, 
                phone_number, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");

        $stmt->execute([
            $_SESSION['user_id'],
            $_POST['room_id'],
            $_POST['check_in'],
            $_POST['check_out'],
            $_POST['adults'],
            $_POST['children'],
            $_POST['username'],
            $_POST['email'],
            $_POST['phone']
        ]);

        $booking_id = $pdo->lastInsertId();

        // Commit transaction
        $pdo->commit();

        // Redirect to payment page with booking_id
        header("Location: payment.php?booking_id=" . $booking_id);
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $_SESSION['error'] = $e->getMessage();
        header('Location: search_results.php');
        exit();
    }
}

// Check if room exists and get its details
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->execute([$room_id]);
$room = $stmt->fetch();

if (!$room) {
    header('Location: view_rooms.php');
    exit();
}

// Calculate total price
$check_in_date = new DateTime($check_in);
$check_out_date = new DateTime($check_out);
$duration = $check_in_date->diff($check_out_date)->days;
$total_price = $room['price'] * $duration;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Room | Luxury Hotel</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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

        .booking-container {
            max-width: 1200px;
            margin: 80px auto;
            padding: 0 20px;
        }
        .booking-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 0 30px rgba(0,0,0,0.1);
        }
        .room-preview {
            padding: 30px;
            background: #f8f9fa;
        }
        .room-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .booking-form {
            padding: 30px;
        }
        .form-group label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        .form-control {
            border: 2px solid #eee;
            border-radius: 8px;
            padding: 12px;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #3498db;
            box-shadow: none;
        }
        .price-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .price-summary h4 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
        .price-detail {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #666;
        }
        .total-price {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #eee;
            font-weight: bold;
            color: #2c3e50;
        }
        .btn-book {
            background: #3498db;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            border: none;
            width: 100%;
            margin-top: 20px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-book:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        .room-features {
            margin: 20px 0;
            padding: 20px;
            background: white;
            border-radius: 10px;
        }
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            color: #666;
        }
        .feature-item i {
            margin-right: 10px;
            color: #3498db;
        }
        @media (max-width: 768px) {
            .booking-grid {
                grid-template-columns: 1fr;
            }
            .booking-container {
                margin: 60px auto;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="booking-container">
        <div class="booking-grid">
            <div class="room-preview">
                <img src="<?= htmlspecialchars($room['image_path']) ?>" alt="<?= htmlspecialchars($room['room_type']) ?>" class="room-image">
                <h3><?= htmlspecialchars($room['room_type']) ?></h3>
                <div class="room-features">
                    <div class="feature-item">
                        <i class="fas fa-bed"></i> King Size Bed
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-wifi"></i> Free WiFi
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-tv"></i> Smart TV
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-snowflake"></i> Air Conditioning
                    </div>
                </div>
                <div class="price-summary">
                    <h4>Booking Summary</h4>
                    <div class="price-detail">
                        <span>Room Rate</span>
                        <span>$<?= number_format($room['price'], 2) ?> per night</span>
                    </div>
                    <div class="price-detail">
                        <span>Check-in</span>
                        <span id="check-in-display"><?= date('F d, Y', strtotime($check_in)) ?></span>
                    </div>
                    <div class="price-detail">
                        <span>Check-out</span>
                        <span id="check-out-display"><?= date('F d, Y', strtotime($check_out)) ?></span>
                    </div>
                    <div class="price-detail">
                        <span>Duration</span>
                        <span><span id="duration"><?= $duration ?></span> nights</span>
                    </div>
                    <div class="price-detail">
                        <span>Guests</span>
                        <span id="guests-display"><?= $adults ?> Adult<?= $adults > 1 ? 's' : '' ?>, <?= $children ?> Child<?= $children > 1 ? 'ren' : '' ?></span>
                    </div>
                    <div class="total-price">
                        <span>Total Amount</span>
                        <span id="total-price">$<?= number_format($total_price, 2) ?></span>
                    </div>
                </div>
            </div>

            <div class="booking-form">
                <h2 class="mb-4">Complete Your Booking</h2>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?= $error_message ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <input type="hidden" name="room_id" value="<?= $room_id ?>">
                    
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" value="<?= $_SESSION['username'] ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-calendar-alt"></i> Check In Date</label>
                                <input type="text" class="form-control datepicker" id="check_in" name="check_in" 
                                       value="<?= htmlspecialchars($check_in) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-calendar-alt"></i> Check Out Date</label>
                                <input type="text" class="form-control datepicker" id="check_out" name="check_out" 
                                       value="<?= htmlspecialchars($check_out) ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Adults</label>
                                <input type="number" name="adults" class="form-control" 
                                       value="<?= $adults ?>" min="1" max="4" 
                                       onchange="updateBookingSummary()">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Children</label>
                                <input type="number" name="children" class="form-control" 
                                       value="<?= $children ?>" min="0" max="4" 
                                       onchange="updateBookingSummary()">
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="total_price" id="hidden-total-price" value="<?= $total_price ?>">

                    <button type="submit" class="btn-book">
                        <i class="fas fa-check-circle"></i> Proceed to Payment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
function calculatePrice() {
    var checkIn = new Date(document.querySelector('input[name="check_in"]').value);
    var checkOut = new Date(document.querySelector('input[name="check_out"]').value);
    var pricePerNight = <?= $room['price'] ?>;
    var adults = parseInt(document.querySelector('input[name="adults"]').value);
    var children = parseInt(document.querySelector('input[name="children"]').value);
    
    if (checkIn && checkOut && checkOut > checkIn) {
        var duration = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
        var totalPrice = duration * pricePerNight;
        
        // Update booking summary
        document.getElementById('duration').textContent = duration;
        document.getElementById('total-price').textContent = '$' + totalPrice.toFixed(2);
        
        // Format dates for display
        var options = { month: 'long', day: 'numeric', year: 'numeric' };
        document.getElementById('check-in-display').textContent = checkIn.toLocaleDateString('en-US', options);
        document.getElementById('check-out-display').textContent = checkOut.toLocaleDateString('en-US', options);
        
        // Update guests display
        var guestText = adults + ' Adult' + (adults > 1 ? 's' : '') + 
                       ', ' + children + ' Child' + (children > 1 ? 'ren' : '');
        document.getElementById('guests-display').textContent = guestText;
    }
}

// Add event listeners
document.querySelector('input[name="check_in"]').addEventListener('change', calculatePrice);
document.querySelector('input[name="check_out"]').addEventListener('change', calculatePrice);
document.querySelector('input[name="adults"]').addEventListener('change', calculatePrice);
document.querySelector('input[name="children"]').addEventListener('change', calculatePrice);

// Add date validation
document.querySelector('input[name="check_in"]').addEventListener('change', function() {
    var checkOut = document.querySelector('input[name="check_out"]');
    checkOut.min = this.value;
    if (checkOut.value && checkOut.value < this.value) {
        checkOut.value = this.value;
    }
    calculatePrice();
});

// Initialize on page load
calculatePrice();

let bookedDates = [];
const room_id = <?= json_encode($room_id) ?>;

// Fetch booked dates from server
fetch(`check_room_availability.php?room_id=${room_id}`)
    .then(response => response.json())
    .then(data => {
        bookedDates = data.booked_dates;
        initializeDatePickers();
    });

function initializeDatePickers() {
    const checkInPicker = flatpickr("#check_in", {
        minDate: "today",
        disable: bookedDates,
        onChange: function(selectedDates) {
            // Update check-out minimum date
            checkOutPicker.set('minDate', selectedDates[0]);
            
            // Find the next booked date after selected check-in
            const nextBookedDate = bookedDates.find(date => 
                new Date(date) > selectedDates[0]
            );
            
            // If there's a next booked date, set it as the maximum date for check-out
            if (nextBookedDate) {
                checkOutPicker.set('maxDate', new Date(nextBookedDate));
            } else {
                checkOutPicker.set('maxDate', null);
            }
        }
    });

    const checkOutPicker = flatpickr("#check_out", {
        minDate: "today",
        disable: bookedDates,
        onChange: function(selectedDates) {
            // Update check-in maximum date
            checkInPicker.set('maxDate', selectedDates[0]);
        }
    });
}

// Add validation before form submission
document.querySelector('form').addEventListener('submit', function(e) {
    const checkIn = new Date(document.getElementById('check_in').value);
    const checkOut = new Date(document.getElementById('check_out').value);
    
    if (checkIn >= checkOut) {
        e.preventDefault();
        alert('Check-out date must be after check-in date');
        return;
    }

    // Check if any date in range is booked
    let currentDate = new Date(checkIn);
    while (currentDate <= checkOut) {
        if (bookedDates.includes(currentDate.toISOString().split('T')[0])) {
            e.preventDefault();
            alert('Selected dates include already booked dates');
            return;
        }
        currentDate.setDate(currentDate.getDate() + 1);
    }
});
</script>

</body>
</html>
