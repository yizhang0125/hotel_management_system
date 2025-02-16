<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

include 'db_connect.php';

// Get room ID from URL
$room_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch room data
try {
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();

    if (!$room) {
        header('Location: rooms.php');
        exit();
    }
} catch (PDOException $e) {
    $error = "Error fetching room data: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Process image upload if new image is selected
        $image_path = $room['image_path']; // Keep existing image by default
        if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/rooms/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['room_image']['name'], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $image_path = $upload_dir . $new_filename;
            
            move_uploaded_file($_FILES['room_image']['tmp_name'], $image_path);

            // Delete old image if exists
            if ($room['image_path'] && file_exists($room['image_path'])) {
                unlink($room['image_path']);
            }
        }

        // Convert status to availability (1 for available, 0 for unavailable)
        $availability = ($_POST['status'] === 'available') ? 1 : 0;

        // Update room data
        $stmt = $pdo->prepare("UPDATE rooms SET 
            room_number = ?, 
            room_type = ?, 
            price = ?, 
            description = ?, 
            image_path = ?,
            availability = ?
            WHERE id = ?");
        
        $stmt->execute([
            $_POST['room_number'],
            $_POST['room_type'],
            $_POST['price'],
            $_POST['description'],
            $image_path,
            $availability,
            $room_id
        ]);

        header('Location: rooms.php?success=2');
        exit();
    } catch (PDOException $e) {
        $error = "Error updating room: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Room | Luxury Hotel</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Copy all the root variables and base styles from rooms.php */
        :root {
            --primary-color: #1e3c72;
            --secondary-color: #2a5298;
            --accent-color: #4CAF50;
            --danger-color: #dc3545;
            --warning-color: #ff9800;
            --text-primary: #2c3e50;
            --text-secondary: #6c757d;
            --bg-primary: #f8f9fa;
            --bg-secondary: #ffffff;
            --border-radius: 15px;
            --card-shadow: 0 5px 20px rgba(0,0,0,0.08);
            --transition: all 0.3s ease;
        }

        /* ... Copy other base styles from rooms.php ... */

        /* Enhanced Form Styles */
        .form-container {
            background: var(--bg-secondary);
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--card-shadow);
            margin-top: 20px;
        }

        .form-section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(30,60,114,0.1);
        }

        .form-group label {
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .form-control {
            height: 48px;
            border-radius: 10px;
            border: 1px solid rgba(0,0,0,0.1);
            padding: 10px 15px;
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(30,60,114,0.15);
        }

        textarea.form-control {
            height: auto;
            min-height: 120px;
        }

        /* Image Preview Styles */
        .image-preview-container {
            background: var(--bg-primary);
            border-radius: var(--border-radius);
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }

        .current-image {
            max-width: 300px;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .image-upload-placeholder {
            border: 2px dashed rgba(0,0,0,0.1);
            border-radius: var(--border-radius);
            padding: 30px;
            cursor: pointer;
            transition: var(--transition);
        }

        .image-upload-placeholder:hover {
            border-color: var(--primary-color);
            background: rgba(30,60,114,0.02);
        }

        .preview-image {
            max-width: 300px;
            max-height: 300px;
            border-radius: 10px;
            display: none;
            margin-top: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        /* Action Buttons */
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(0,0,0,0.05);
        }

        .btn-custom-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-custom-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30,60,114,0.2);
        }

        .btn-custom-secondary {
            background: var(--bg-primary);
            color: var(--text-primary);
            border: 1px solid rgba(0,0,0,0.1);
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-custom-secondary:hover {
            background: var(--bg-secondary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        /* Navbar and Sidebar Styles */
        .admin-navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-size: 24px;
            font-weight: 700;
        }

        /* Sidebar Styles */
        .admin-sidebar {
            position: fixed;
            top: 65px;
            left: 0;
            bottom: 0;
            width: 250px;
            background: #284B8C;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-category {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            padding: 1.5rem 2rem 0.75rem;
        }

        .menu-item {
            padding: 12px 25px;
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
            margin: 4px 0;
        }

        .menu-item:hover, .menu-item.active {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            text-decoration: none;
            border-left-color: #ffffff;
        }

        .menu-item i {
            width: 20px;
            margin-right: 10px;
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s;
        }

        .menu-item:hover i,
        .menu-item.active i {
            color: #ffffff;
        }

        .menu-item span {
            font-size: 0.9rem;
            font-weight: 400;
        }

        /* Update main content margin for sidebar */
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
            }

            .show-sidebar .admin-sidebar {
                transform: translateX(0);
            }
        }

        /* Enhanced Select Styling */
        .form-control {
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
            padding-right: 40px;
        }

        select.form-control:focus {
            border-color: #1e3c72;
            box-shadow: 0 0 0 0.2rem rgba(30, 60, 114, 0.15);
        }

        /* Room Type Badge Styles */
        .room-type-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-top: 5px;
        }

        .room-type-standard {
            background: #e3f2fd;
            color: #1e88e5;
        }

        .room-type-deluxe {
            background: #e8f5e9;
            color: #43a047;
        }

        .room-type-suite {
            background: #fff3e0;
            color: #ef6c00;
        }

        .room-type-twin {
            background: #f3e5f5;
            color: #8e24aa;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark admin-navbar fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <i class="fas fa-hotel"></i> Admin Dashboard
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="admin_profile.php">
                        <i class="fas fa-user-circle"></i> Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Sidebar -->
<div class="admin-sidebar">
    <div class="sidebar-menu">
        <div class="menu-category">Main</div>
        <a href="admin_dashboard.php" class="menu-item">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>

        <div class="menu-category">Management</div>
        <a href="admin_manage_bookings.php" class="menu-item">
            <i class="fas fa-calendar-check"></i>
            <span>Bookings</span>
        </a>
        <a href="rooms.php" class="menu-item active">
            <i class="fas fa-door-open"></i>
            <span>Rooms</span>
        </a>
        <a href="admin_manage_users.php" class="menu-item">
            <i class="fas fa-users"></i>
            <span>Users</span>
        </a>
        <a href="admin_facilities.php" class="menu-item">
            <i class="fas fa-spa"></i>
            <span>Facilities</span>
        </a>

        <div class="menu-category">Marketing</div>
        <a href="best_deals.php" class="menu-item">
            <i class="fas fa-tag"></i>
            <span>Best Deals</span>
        </a>

        <div class="menu-category">Finance</div>
        <a href="admin_view_payments.php" class="menu-item">
            <i class="fas fa-credit-card"></i>
            <span>Payments</span>
        </a>

        <div class="menu-category">Settings</div>
        <a href="admin_settings.php" class="menu-item">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
    </div>
</div>

<div class="main-content">
    <div class="dashboard-header">
        <h1 class="dashboard-title">Edit Room</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="rooms.php">Rooms</a></li>
                <li class="breadcrumb-item active">Edit Room</li>
            </ol>
        </nav>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-section-title">
                <i class="fas fa-info-circle"></i> Basic Information
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-door-open"></i> Room Number</label>
                        <input type="text" class="form-control" name="room_number" 
                               value="<?= htmlspecialchars($room['room_number']) ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="room_type">Room Type</label>
                        <select class="form-control" id="room_type" name="room_type" required>
                            <option value="">Select Room Type</option>
                            <option value="Standard Room" <?= $room['room_type'] === 'Standard Room' ? 'selected' : '' ?>>Standard Room</option>
                            <option value="Deluxe Room" <?= $room['room_type'] === 'Deluxe Room' ? 'selected' : '' ?>>Deluxe Room</option>
                            <option value="Suite" <?= $room['room_type'] === 'Suite' ? 'selected' : '' ?>>Suite</option>
                            <option value="Twin Room" <?= $room['room_type'] === 'Twin Room' ? 'selected' : '' ?>>Twin Room</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Price per Night ($)</label>
                        <input type="number" class="form-control" name="price" 
                               value="<?= htmlspecialchars($room['price']) ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-check-circle"></i> Status</label>
                        <select class="form-control" name="status" required>
                            <option value="available" <?= $room['availability'] == 1 ? 'selected' : '' ?>>Available</option>
                            <option value="occupied" <?= $room['availability'] == 0 ? 'selected' : '' ?>>Occupied</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section-title mt-4">
                <i class="fas fa-align-left"></i> Room Description
            </div>

            <div class="form-group">
                <textarea class="form-control" name="description" rows="4" 
                          placeholder="Enter detailed room description..."><?= htmlspecialchars($room['description']) ?></textarea>
            </div>

            <div class="form-section-title mt-4">
                <i class="fas fa-image"></i> Room Image
            </div>

            <div class="image-preview-container">
                <?php if ($room['image_path']): ?>
                    <img src="<?= htmlspecialchars($room['image_path']) ?>" alt="Current Room Image" class="current-image">
                <?php endif; ?>
                
                <div class="image-upload-placeholder">
                    <input type="file" class="form-control-file" name="room_image" id="room_image" accept="image/*">
                    <p class="text-muted mb-0">Click to upload new image</p>
                </div>
                <img id="image_preview" class="preview-image" src="#" alt="New Image Preview">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-custom-primary">
                    <i class="fas fa-save"></i> Update Room
                </button>
                <a href="rooms.php" class="btn btn-custom-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
// Image preview
document.getElementById('room_image').onchange = function(e) {
    const preview = document.getElementById('image_preview');
    const file = e.target.files[0];
    if (file) {
        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
    }
};

// Copy the sidebar toggle scripts from rooms.php

const roomDescriptions = {
    'Standard Room': 'Comfortable room with essential amenities, perfect for solo travelers or couples.',
    'Deluxe Room': 'Spacious room with premium furnishings and city views.',
    'Suite': 'Luxurious suite with separate living area and premium amenities.',
    'Twin Room': 'Room with two single beds, ideal for friends or business travelers.'
};

// Update description when room type changes
$('#room_type').change(function() {
    const selectedType = $(this).val();
    const description = roomDescriptions[selectedType] || '';
    $('#room_description').val(description);
});
</script>

</body>
</html>
