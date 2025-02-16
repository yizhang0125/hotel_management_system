<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

include 'db_connect.php';

$booking_id = $_GET['booking_id'];

// Fetch booking details with room type and price
$stmt = $pdo->prepare("
    SELECT b.*, r.room_type, r.price 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    WHERE b.id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking) {
    die('Booking not found!');
}

// Calculate total price based on the stay duration
$check_in = new DateTime($booking['check_in']);
$check_out = new DateTime($booking['check_out']);
$interval = $check_in->diff($check_out);
$days = $interval->days;
$total_price = $days * $booking['price'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visa Bank Card Payment</title>
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
    margin-bottom: 45px; /* Moves card number upwards */
    outline: none; /* Removes focus box */
}

.bank-card-footer input {
    background: transparent;
    border: none;
    color: white;
    font-size: 1.1rem;
    font-weight: bold;
    outline: none; /* Removes focus box */
    text-align: left;
    width: 48%;
    position: absolute;
    bottom: 10px;
}

.bank-card-footer input:first-child {
    left: 5%;
}

.bank-card-footer input:last-child {
    right: -33%; /* Slight adjustment for more right alignment */
}

.btn-success {
    font-size: 1.1rem;
    padding: 10px 25px;
    border-radius: 8px;
}


    </style>
</head>
<body>
<div class="container">
    <h1>Payment for <?= htmlspecialchars($booking['room_type'] ?? 'Room') ?></h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <p>Total Price: <?= htmlspecialchars($total_price) ?> USD</p>

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

    <form id="paymentForm" method="POST" action="process_payment.php?booking_id=<?= htmlspecialchars($booking_id) ?>" class="mt-3">
        <input type="hidden" name="order_id" value="<?= htmlspecialchars($booking_id) ?>">
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
    cardExpiryInput.value = formattedValue.substring(0, 5); // Limit to MM/YY
    hiddenCardExpiry.value = cardExpiryInput.value;
}

function updateCardHolder() {
    const cardHolderInput = document.getElementById('cardHolderInput');
    const hiddenCardHolder = document.getElementById('hiddenCardHolder');

    // Update hidden input value
    hiddenCardHolder.value = cardHolderInput.value;
}

</script>
</body>
</html>
