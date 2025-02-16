<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

$booking_id = $_GET['id'];

// Fetch booking details with room and user information
$stmt = $pdo->prepare("
    SELECT 
        b.*,
        r.room_type,
        r.price,
        u.username,
        u.email,
        DATEDIFF(b.check_out, b.check_in) as nights,
        (DATEDIFF(b.check_out, b.check_in) * r.price) as total_price
    FROM bookings b
    INNER JOIN rooms r ON b.room_id = r.id
    INNER JOIN users u ON b.user_id = u.id
    WHERE b.id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch all available rooms for dropdown
$stmt = $pdo->query("SELECT * FROM rooms ORDER BY room_type");
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = $_POST['room_id'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $status = $_POST['status'];

    try {
        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET room_id = ?,
                check_in = ?,
                check_out = ?,
                status = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $room_id,
            $check_in,
            $check_out,
            $status,
            $booking_id
        ]);

        header('Location: admin_manage_bookings.php?success=updated');
        exit();
    } catch (PDOException $e) {
        $error = "Error updating booking: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking | Admin</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #1e3c72;
            --secondary-color: #2a5298;
        }

        body {
            background: #f8f9fa;
            padding-top: 80px;
        }

        .edit-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .page-header {
            background: white;
            padding: 25px 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .form-group label {
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            padding: 12px 15px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(30, 60, 114, 0.15);
        }

        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
        }

        .booking-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .booking-info p {
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
        }

        .booking-info strong {
            color: var(--primary-color);
        }

        /* Fix dropdown styling */
        select.form-control {
            width: 100%;
            height: 45px;
            padding: 8px 15px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            background-color: #fff;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%232c3e50' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 12px;
            cursor: pointer;
        }

        select.form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(30, 60, 114, 0.15);
            outline: none;
        }

        select.form-control:hover {
            border-color: var(--primary-color);
        }

        /* Add container for better control */
        .select-container {
            position: relative;
            width: 100%;
        }
    </style>
</head>
<body>
    <?php include 'admin/navbar.php'; ?>
    <?php include 'admin/sidebar.php'; ?>

    <div class="edit-container">
        <div class="page-header">
            <h1 class="page-title">Edit Booking #<?= $booking_id ?></h1>
            <a href="admin_manage_bookings.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Bookings
            </a>
        </div>

        <div class="form-container">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <div class="booking-info">
                <p>
                    <span>Guest Name:</span>
                    <strong><?= htmlspecialchars($booking['username']) ?></strong>
                </p>
                <p>
                    <span>Email:</span>
                    <strong><?= htmlspecialchars($booking['email_address']) ?></strong>
                </p>
                <p>
                    <span>Phone:</span>
                    <strong><?= htmlspecialchars($booking['phone_number']) ?></strong>
                </p>
                <p>
                    <span>Current Room:</span>
                    <strong><?= htmlspecialchars($booking['room_type']) ?></strong>
                </p>
                <p>
                    <span>Guests:</span>
                    <strong>Adults: <?= $booking['adults'] ?>, Children: <?= $booking['children'] ?></strong>
                </p>
                <p>
                    <span>Total Price:</span>
                    <strong>$<?= number_format($booking['total_price'], 2) ?></strong>
                </p>
            </div>

            <form action="edit_booking.php?id=<?= $booking_id ?>" method="POST">
                <div class="form-group">
                    <label>
                        <i class="fas fa-door-open"></i> Room Type
                    </label>
                    <div class="select-container">
                        <select class="form-control" name="room_id" required>
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?= $room['id'] ?>" 
                                    <?= $room['id'] == $booking['room_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($room['room_type']) ?> - $<?= $room['price'] ?>/night
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-calendar-alt"></i> Check-in Date
                    </label>
                    <input type="date" class="form-control" name="check_in" 
                           value="<?= $booking['check_in'] ?>" required>
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-calendar-alt"></i> Check-out Date
                    </label>
                    <input type="date" class="form-control" name="check_out" 
                           value="<?= $booking['check_out'] ?>" required>
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-info-circle"></i> Status
                    </label>
                    <div class="select-container">
                        <select class="form-control" name="status" required>
                            <option value="pending" <?= $booking['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="confirmed" <?= $booking['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                            <option value="cancelled" <?= $booking['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="admin_manage_bookings.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
    // Validate check-out date is after check-in date
    document.querySelector('input[name="check_out"]').addEventListener('change', function() {
        const checkIn = document.querySelector('input[name="check_in"]').value;
        if (checkIn && this.value <= checkIn) {
            alert('Check-out date must be after check-in date');
            this.value = '';
        }
    });
    </script>
</body>
</html> 