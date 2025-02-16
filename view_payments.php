<?php
session_start();
include 'db_connect.php';
include 'user/navbar.php';  // Add navbar include

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch user payments with total price, room type, and number of days between check_in and check_out
$stmtPayments = $pdo->prepare("
    SELECT payments.*, 
           SUM(DATEDIFF(bookings.check_out, bookings.check_in) * rooms.price) AS total_price, 
           rooms.room_type, 
           DATEDIFF(bookings.check_out, bookings.check_in) AS number_of_days 
    FROM payments 
    INNER JOIN bookings ON payments.booking_id = bookings.id 
    INNER JOIN rooms ON bookings.room_id = rooms.id 
    WHERE bookings.user_id = ?
    GROUP BY payments.id
    ORDER BY payments.id DESC
");
$stmtPayments->execute([$_SESSION['user_id']]);
$payments = $stmtPayments->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Payments</title>
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

        .page-header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 28px;
            font-weight: 600;
        }

        .payments-table {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            color: #2c3e50;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            padding: 15px;
        }

        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            color: #2c3e50;
            border-bottom: 1px solid #eee;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-success {
            background: #e3fcef;
            color: #00a650;
        }

        .status-pending {
            background: #fff4e6;
            color: #f76707;
        }

        .card-number {
            font-family: monospace;
            letter-spacing: 1px;
        }

        .table-responsive {
            border-radius: 15px;
            overflow: hidden;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .table thead {
                display: none;
            }
            
            .table tbody td {
                display: block;
                text-align: right;
                padding: 10px 15px;
                position: relative;
            }
            
            .table tbody td::before {
                content: attr(data-label);
                position: absolute;
                left: 15px;
                font-weight: 600;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-credit-card"></i> Payment History</h1>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Print Statement
            </button>
        </div>
    </div>

    <div class="payments-table">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Room Type</th>
                        <th>Duration</th>
                        <th>Amount</th>
                        <th>Card Info</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td data-label="Payment ID">#<?= str_pad($payment['id'], 5, '0', STR_PAD_LEFT) ?></td>
                            <td data-label="Room Type"><?= htmlspecialchars($payment['room_type']) ?></td>
                            <td data-label="Duration"><?= htmlspecialchars($payment['number_of_days']) ?> nights</td>
                            <td data-label="Amount">$<?= number_format($payment['total_price'], 2) ?></td>
                            <td data-label="Card Info">
                                <span class="card-number">
                                    <?= str_repeat('•', strlen($payment['card_number']) - 4) . substr($payment['card_number'], -4) ?>
                                </span>
                                <br>
                                <small class="text-muted"><?= htmlspecialchars($payment['cardholder_name']) ?></small>
                            </td>
                            <td data-label="Status">
                                <span class="status-badge <?= $payment['status'] === 'completed' ? 'status-success' : 'status-pending' ?>">
                                    <?= ucfirst(htmlspecialchars($payment['status'])) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
