<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

include 'db_connect.php';

// Ensure uploads directory exists
$uploads_dir = 'uploads/';
if (!is_dir($uploads_dir)) {
    mkdir($uploads_dir, 0777, true);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_number = $_POST['room_number'];
    $room_type = $_POST['room_type'];
    $price = $_POST['price'];
    $availability = $_POST['availability'];
    $description = $_POST['description'];
    $max_adults = $_POST['max_adults'];
    $max_children = $_POST['max_children'];

    // Check if the room number already exists
    $stmt_check = $pdo->prepare("SELECT * FROM rooms WHERE room_number = ?");
    $stmt_check->execute([$room_number]);
    $existing_room = $stmt_check->fetch();

    if ($existing_room) {
        $error_message = "Room number already exists.";
    } else {
        // Handling image upload
        if ($_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $target_file = $uploads_dir . basename($_FILES["image"]["name"]);

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = $target_file;
            } else {
                $image_path = null; // Handle error or set default image
            }
        } else {
            $image_path = null;
        }

        // Insert into database
        $stmt = $pdo->prepare("INSERT INTO rooms (room_number, room_type, price, availability, description, image_path, max_adults, max_children) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$room_number, $room_type, $price, $availability, $description, $image_path, $max_adults, $max_children]);

        header('Location: admin_dashboard.php'); // Redirect to dashboard after adding
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Room</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <a class="navbar-brand" href="#"><i class="fas fa-hotel"></i> Admin Dashboard</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="admin_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container-fluid" style="margin-top: 60px;">
    <div class="row">
        <main class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <h1><i class="fas fa-plus-circle"></i> Add New Room</h1>

            <form method="POST" action="" enctype="multipart/form-data">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?= $error_message ?></div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="room_number"><i class="fas fa-hashtag"></i> Room Number</label>
                    <input type="text" id="room_number" name="room_number" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="room_type"><i class="fas fa-bed"></i> Room Type</label>
                    <select id="room_type" name="room_type" class="form-control">
                        <option value="Standard Room">Standard Room</option>
                        <option value="Double Room">Double Room</option>
                        <option value="Twin Room">Twin Room</option>
                        <option value="Suite">Suite</option>
                        <option value="Deluxe Room">Deluxe Room</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="price"><i class="fas fa-dollar-sign"></i> Price</label>
                    <input type="number" id="price" name="price" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="availability"><i class="fas fa-calendar-check"></i> Availability</label>
                    <select id="availability" name="availability" class="form-control">
                        <option value="1">Available</option>
                        <option value="0">Not Available</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="description"><i class="fas fa-info-circle"></i> Room Description</label>
                    <textarea id="description" name="description" class="form-control" required></textarea>
                </div>
                <div class="form-group">
                    <label for="image"><i class="fas fa-image"></i> Upload Room Image</label>
                    <input type="file" id="image" name="image" class="form-control-file">
                </div>
                <div class="form-group">
                    <label for="max_adults"><i class="fas fa-users"></i> Max Adults</label>
                    <input type="number" id="max_adults" name="max_adults" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="max_children"><i class="fas fa-child"></i> Max Children</label>
                    <input type="number" id="max_children" name="max_children" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add Room</button>
            </form>
        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
