<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

include 'db_connect.php';

// Fetch all rooms
$stmt = $pdo->query("SELECT * FROM rooms ORDER BY id DESC");
$rooms = $stmt->fetchAll();

// Count total rooms and stats - with safer queries
$stats = [
    'total_rooms' => count($rooms),
    'available_rooms' => $pdo->query("SELECT COUNT(*) FROM rooms WHERE availability = 1")->fetchColumn(),
    'occupied_rooms' => $pdo->query("SELECT COUNT(*) FROM rooms WHERE availability = 0")->fetchColumn(),
    'maintenance_rooms' => 0  // Since we don't have a maintenance status
];

// Check if status column exists
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM rooms LIKE 'status'");
    if ($stmt->rowCount() > 0) {
        // If status column exists, get the real counts
        $stats['available_rooms'] = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'available'")->fetchColumn();
        $stats['occupied_rooms'] = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'occupied'")->fetchColumn();
        $stats['maintenance_rooms'] = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'maintenance'")->fetchColumn();
    }
} catch (PDOException $e) {
    // If there's an error, we'll keep the default values
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms | Admin</title>
    <!-- Load jQuery first -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Then load Bootstrap's JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
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

        body {
            background: var(--bg-primary);
            font-family: 'Poppins', sans-serif;
            padding-top: 65px;
            color: var(--text-primary);
        }

        /* Update Dashboard Header Styles */
        .dashboard-header {
            margin-bottom: 40px;
            padding: 40px 0 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .dashboard-title {
            font-size: 32px;
            font-weight: 700;
            color: #1e3c72;
            margin-top: 20px;
            margin-bottom: 10px;
            position: relative;
            padding-left: 20px;
        }

        .dashboard-title::after {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 24px;
            background: linear-gradient(to bottom, #1e3c72, #2a5298);
            border-radius: 2px;
        }

        .breadcrumb {
            background: transparent;
            padding: 0 20px;
            margin: 0;
            font-size: 0.9rem;
        }

        .breadcrumb-item a {
            color: #1e3c72;
            font-weight: 500;
        }

        .breadcrumb-item.active {
            color: #6c757d;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: "›";
            color: #6c757d;
        }

        /* Container padding for better spacing */
        .container-fluid {
            padding: 0 30px;
        }

        @media (max-width: 768px) {
            .dashboard-header {
                padding: 30px 0 20px;
            }

            .dashboard-title {
                margin-top: 15px;
                font-size: 24px;
                padding-left: 15px;
            }

            .dashboard-title::after {
                height: 20px;
                width: 3px;
            }

            .container-fluid {
                padding: 0 15px;
            }
        }

        /* Enhanced Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--bg-secondary);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
            line-height: 1;
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 14px;
            font-weight: 500;
        }

        /* Enhanced Filters Section */
        .filters-section {
            background: var(--bg-secondary);
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
        }

        .form-control {
            height: 48px;
            border-radius: 10px;
            border: 1px solid rgba(0,0,0,0.1);
            font-size: 15px;
            padding: 10px 15px;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(30,60,114,0.15);
        }

        /* Enhanced Room Cards */
        .room-card {
            background: var(--bg-secondary);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            height: 100%;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }

        .room-image {
            height: 220px;
            position: relative;
            overflow: hidden;
        }

        .room-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .room-card:hover .room-image img {
            transform: scale(1.1);
        }

        .room-status {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 8px 15px;
            border-radius: 25px;
            color: white;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .room-status.available {
            background: var(--accent-color);
        }

        .room-status.occupied {
            background: var(--danger-color);
        }

        .room-status.maintenance {
            background: var(--warning-color);
        }

        .room-details {
            padding: 25px;
        }

        .room-type {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .room-number {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 15px;
        }

        .room-price {
            color: var(--accent-color);
            font-weight: 700;
            font-size: 20px;
            margin-bottom: 20px;
        }

        .room-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .btn-room-action {
            padding: 12px;
            border-radius: 10px;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-room-action:hover {
            transform: translateY(-2px);
        }

        /* Enhanced Modal Styles */
        .modal-content {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            padding: 20px 25px;
        }

        .modal-body {
            padding: 25px;
        }

        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid rgba(0,0,0,0.05);
        }

        /* Custom Button Styles */
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

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .room-card {
                margin-bottom: 20px;
            }

            .dashboard-header {
                padding: 20px;
                margin: -20px -20px 20px -20px;
            }
        }

        /* Navbar Styles */
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

        /* Custom Scrollbar */
        .admin-sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .admin-sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .admin-sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        .admin-sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
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

        /* Room Cards Grid */
        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .add-room-card {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 350px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none !important;
        }

        .add-room-card:hover {
            border-color: #1e3c72;
            background: #fff;
        }

        .add-room-icon {
            width: 60px;
            height: 60px;
            background: #e3f2fd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #1e3c72;
            margin-bottom: 15px;
        }

        .add-room-text {
            font-size: 16px;
            font-weight: 500;
            color: #2c3e50;
        }
    </style>
</head>
<body>

<?php include 'admin/navbar.php'; ?>
<?php include 'admin/sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Manage Rooms</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Rooms</li>
                </ol>
            </nav>
        </div>

        <!-- Stats Section -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #e3f2fd; color: #1e88e5;">
                    <i class="fas fa-bed"></i>
                        </div>
                <div class="stat-value"><?= $stats['total_rooms'] ?></div>
                <div class="stat-label">Total Rooms</div>
                    </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #e8f5e9; color: #43a047;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value"><?= $stats['available_rooms'] ?></div>
                <div class="stat-label">Available Rooms</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #fce4ec; color: #e91e63;">
                    <i class="fas fa-door-closed"></i>
        </div>
                <div class="stat-value"><?= $stats['occupied_rooms'] ?></div>
                <div class="stat-label">Occupied Rooms</div>
</div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #fff3e0; color: #fb8c00;">
                    <i class="fas fa-tools"></i>
                </div>
                <div class="stat-value"><?= $stats['maintenance_rooms'] ?></div>
                <div class="stat-label">Under Maintenance</div>
            </div>
                    </div>

        <!-- Filters Section -->
        <div class="filters-section mb-4">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <select class="form-control" id="roomTypeFilter">
                        <option value="">All Room Types</option>
                            <option value="Standard">Standard</option>
                            <option value="Deluxe">Deluxe</option>
                            <option value="Suite">Suite</option>
                        </select>
                    </div>
                <div class="col-md-3">
                    <select class="form-control" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="available">Available</option>
                        <option value="occupied">Occupied</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="search-box">
                        <input type="text" class="form-control search-input" placeholder="Search rooms...">
                    </div>
                </div>
                <div class="col-md-2">
                    <a href="add_room.php" class="btn btn-custom-primary w-100">
                        <i class="fas fa-plus"></i> Add Room
                    </a>
                </div>
            </div>
        </div>

        <div class="rooms-grid">
            <!-- Add New Room Card -->
            <a href="add_room.php" class="add-room-card">
                <div class="add-room-icon">
                    <i class="fas fa-plus"></i>
                </div>
                <div class="add-room-text">Add New Room</div>
            </a>

            <?php foreach ($rooms as $room): ?>
            <div class="room-card">
                <div class="room-image">
                    <?php
                    // Check if image exists in uploads directory
                    $imagePath = $room['image_path'] ? 'uploads/' . basename($room['image_path']) : 'images/default-room.jpg';
                    ?>
                    <img src="<?= htmlspecialchars($imagePath) ?>" 
                         alt="<?= htmlspecialchars($room['room_type']) ?>">
                </div>
                <div class="room-details">
                    <h3 class="room-type"><?= htmlspecialchars($room['room_type']) ?></h3>
                    <div class="room-price">$<?= number_format($room['price'], 2) ?> / night</div>
                    <div class="room-info">
                        <i class="fas fa-bed"></i> 
                        <?= isset($room['max_adults']) ? htmlspecialchars($room['max_adults']) : '2' ?> Adults
                        • 
                        <?= isset($room['max_children']) ? htmlspecialchars($room['max_children']) : '2' ?> Children
                        • Room <?= htmlspecialchars($room['room_number']) ?>
                    </div>
                    <div class="room-actions">
                        <a href="edit_room.php?id=<?= $room['id'] ?>" class="btn btn-primary btn-room">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="delete_room.php?id=<?= $room['id'] ?>" 
                           class="btn btn-danger btn-room"
                           onclick="return confirm('Are you sure you want to delete this room?')">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
            </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
function editRoom(id) {
    window.location.href = 'edit_room.php?id=' + id;
}

function deleteRoom(id) {
    if(confirm('Are you sure you want to delete this room?')) {
        // Add your delete room logic
    }
}

function saveRoom() {
    // Add your save room logic
}
</script>

<!-- Add this script at the bottom of your file -->
<script>
$(document).ready(function() {
    // Toggle sidebar on mobile
    $('.navbar-toggler').click(function() {
        $('body').toggleClass('show-sidebar');
    });

    // Close sidebar when clicking outside on mobile
    $(document).click(function(event) {
        if (!$(event.target).closest('.admin-sidebar, .navbar-toggler').length) {
            $('body').removeClass('show-sidebar');
        }
    });
});
</script>

</body>
</html>
