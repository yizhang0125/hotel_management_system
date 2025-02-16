<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'db_connect.php';

$booking_id = $_GET['booking_id'];

// Fetch booking details with deal information
$stmt = $pdo->prepare("
    SELECT b.*, d.deal_name, d.price, d.discount, d.image_path 
    FROM deals_booking b 
    JOIN deals d ON b.deal_id = d.id 
    WHERE b.id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking) {
    die('Booking not found!');
}

// Calculate total price based on the stay duration and deal discount
$check_in = new DateTime($booking['check_in']);
$check_out = new DateTime($booking['check_out']);
$interval = $check_in->diff($check_out);
$days = $interval->days;
$discounted_price = $booking['price'] * (1 - $booking['discount']/100);
$total_price = $booking['total_price']; // Use the stored total price
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visa Card Payment - <?= htmlspecialchars($booking['deal_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        .container {
            text-align: center;
        }

        .bank-card {
            width: 450px;
            height: 250px;
            background: linear-gradient(135deg, #007bff, #00d4ff);
            border-radius: 15px;
            color: white;
            padding: 20px;
            position: relative;
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .bank-card-chip img,
        .bank-card-logo img {
            width: 45px;
            height: 40px;
            position: absolute;
            top: 20px;
        }

        .bank-card-chip img {
            left: 20px;
        }

        .bank-card-logo img {
            width: 20%;
            height: auto;
            right: 20px;
        }

        .bank-card-number input {
            background: transparent;
            border: none;
            color: white;
            font-size: 2.0rem;
            font-weight: bold;
            text-align: center;
            letter-spacing: 2px;
            width: 100%;
            margin-bottom: 45px;
            outline: none;
        }

        .bank-card-footer input {
            background: transparent;
            border: none;
            color: white;
            font-size: 1.1rem;
            font-weight: bold;
            outline: none;
            text-align: left;
            width: 48%;
            position: absolute;
            bottom: 10px;
        }

        .bank-card-footer input:first-child {
            left: 5%;
        }

        .bank-card-footer input:last-child {
            right: -33%;
        }

        .btn-success {
            font-size: 1.1rem;
            padding: 10px 25px;
            border-radius: 8px;
        }

        .booking-summary {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .price-breakdown {
            text-align: left;
            margin: 20px 0;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }

        @media (max-width: 768px) {
            .payment-container {
                padding: 10px;
            }

            .container {
                padding: 10px;
            }

            .bank-card {
                width: 100%;
                margin: 10px auto;
            }

            .price-details {
                padding: 15px;
            }

            .price-row {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 5px;
            }

            .price-row:last-child {
                flex-direction: row;
                justify-content: space-between;
            }

            .bank-card-number {
                font-size: 1.2rem;
            }

            .bank-card-footer {
                flex-direction: column;
                gap: 10px;
            }

            .bank-card-chip img {
                width: 35px;
                height: 30px;
            }

            .bank-card-logo img {
                width: 15%;
            }

            .submit-btn {
                width: 100%;
                padding: 12px;
            }

            .total-price-details {
                text-align: center;
            }

            .nights-info {
                margin-bottom: 10px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 5px;
            }

            .bank-card {
                padding: 15px;
            }

            .bank-card-number input {
                font-size: 1rem;
            }

            .bank-card-footer input {
                font-size: 0.9rem;
            }

            .price-breakdown {
                padding: 10px;
            }

            .total-amount {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Payment for <?= htmlspecialchars($booking['deal_name']) ?></h1>

    <div class="booking-summary">
        <div class="price-breakdown">
            <div class="price-row">
                <span>Original Price:</span>
                <span>$<?= number_format($booking['price'], 2) ?></span>
            </div>
            <div class="price-row">
                <span>Discount:</span>
                <span><?= $booking['discount'] ?>% OFF</span>
            </div>
            <div class="price-row">
                <span>Number of Nights:</span>
                <span><?= $days ?></span>
            </div>
            <div class="price-row">
                <strong>Total Amount:</strong>
                <strong>$<?= number_format($total_price, 2) ?></strong>
            </div>
        </div>
    </div>

    <div class="bank-card mx-auto mt-4">
        <div class="bank-card-chip">
            <img src="https://raw.githubusercontent.com/muhammederdem/credit-card-form/master/src/assets/images/chip.png" alt="Chip">
        </div>
        <div class="bank-card-logo">
            <img src="https://upload.wikimedia.org/wikipedia/commons/4/41/Visa_Logo.png" alt="Visa Logo">
        </div>
        <div class="bank-card-number">
            <input type="text" id="cardNumberInput" value="1234 5678 9012 3456" maxlength="19" oninput="updateHiddenInput()">
        </div>
        <div class="bank-card-footer">
            <input type="text" id="cardHolderInput" value="John Doe" oninput="updateCardHolder()">
            <input type="text" id="cardExpiryInput" value="02/24" maxlength="5" oninput="updateExpiry()">
        </div>
    </div>

    <form id="paymentForm" method="POST" action="process_payment1.php?booking_id=<?= htmlspecialchars($booking_id) ?>" class="mt-3">
        <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking_id) ?>">
        <input type="hidden" name="cardNumber" id="hiddenCardNumber" value="1234 5678 9012 3456">
        <input type="hidden" name="cardHolder" id="hiddenCardHolder" value="John Doe">
        <input type="hidden" name="cardExpiry" id="hiddenCardExpiry" value="02/24">
        <input type="hidden" name="total_price" value="<?= $total_price ?>">
        <button type="submit" class="btn btn-success">Pay Now</button>
    </form>
</div>

<script>
function updateHiddenInput() {
    const cardNumberInput = document.getElementById('cardNumberInput');
    const hiddenCardNumber = document.getElementById('hiddenCardNumber');

    // Allow only digits and spaces
    cardNumberInput.value = cardNumberInput.value.replace(/[^0-9 ]/g, '');

    // Update hidden input value
    hiddenCardNumber.value = cardNumberInput.value;

    // Auto-format as 4-digit blocks
    const rawValue = cardNumberInput.value.replace(/\s+/g, ''); 
    let formattedValue = '';
    for (let i = 0; i < rawValue.length; i++) {
        if (i > 0 && i % 4 === 0) formattedValue += ' ';
        formattedValue += rawValue[i];
    }
    cardNumberInput.value = formattedValue;
}

function updateExpiry() {
    const cardExpiryInput = document.getElementById('cardExpiryInput');
    const hiddenCardExpiry = document.getElementById('hiddenCardExpiry');

    // Allow only digits and "/"
    cardExpiryInput.value = cardExpiryInput.value.replace(/[^0-9\/]/g, '');

    // Auto-format as MM/YY
    const rawValue = cardExpiryInput.value.replace(/\//g, '');
    let formattedValue = '';
    for (let i = 0; i < rawValue.length; i++) {
        if (i === 2) formattedValue += '/';
        formattedValue += rawValue[i];
    }
    cardExpiryInput.value = formattedValue.substring(0, 5);
    hiddenCardExpiry.value = cardExpiryInput.value;
}

function updateCardHolder() {
    const cardHolderInput = document.getElementById('cardHolderInput');
    const hiddenCardHolder = document.getElementById('hiddenCardHolder');
    hiddenCardHolder.value = cardHolderInput.value;
}
</script>
</body>
</html> 