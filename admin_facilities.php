<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}   
include 'db_connect.php';

// Fetch all facilities
$facilities = $pdo->query("SELECT * FROM facilities ORDER BY id DESC")->fetchAll();

// Calculate statistics
$stats = [
    'total_facilities' => count($facilities),
    'active_facilities' => count(array_filter($facilities, fn($f) => $f['status'] === 'active')),
    'maintenance' => count(array_filter($facilities, fn($f) => $f['status'] === 'maintenance'))
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Facilities | Admin</title>
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
            padding-top: 70px;
            color: var(--text-primary);
        }

        /* Enhanced Navbar */
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            padding: 0.8rem 1rem;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
            color: white !important;
        }

        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            padding: 0.5rem 1rem;
            transition: var(--transition);
        }

        .nav-link:hover {
            color: white !important;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
        }

        /* Enhanced Card Container */
        .card-container {
            background: var(--bg-secondary);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            margin: 15px;
            width: 300px;
            border: none;
        }

        .card-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .card-container img {
            height: 200px;
            object-fit: cover;
            width: 100%;
            transition: var(--transition);
        }

        .card-container:hover img {
            transform: scale(1.05);
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-title {
            color: var(--text-primary);
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .card-text {
            color: var(--text-secondary);
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        /* Enhanced Buttons */
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-warning {
            background: #fff3e0;
            color: #f57c00;
            border: none;
        }

        .btn-warning:hover {
            background: #f57c00;
            color: white;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #ffebee;
            color: #c62828;
            border: none;
        }

        .btn-danger:hover {
            background: #c62828;
            color: white;
            transform: translateY(-2px);
        }

        /* Container Layout */
        .container {
            padding: 2rem;
            max-width: 1400px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        /* Flex Container for Cards */
        .d-flex.flex-wrap {
            margin: -15px;
            justify-content: center;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .card-container {
                width: calc(33.333% - 30px);
            }
        }

        @media (max-width: 992px) {
            .card-container {
                width: calc(50% - 30px);
            }
        }

        @media (max-width: 768px) {
            .card-container {
                width: calc(100% - 30px);
                margin: 10px;
            }

            .container {
                padding: 1rem;
            }

            .page-header {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }
        }

        /* Loading Animation */
        .card-container {
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Navbar and Sidebar Styles */
        .admin-navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .admin-navbar .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
            color: white !important;
        }

        .admin-navbar .nav-link {
            color: rgba(255,255,255,0.9) !important;
            padding: 0.5rem 1rem;
            transition: var(--transition);
        }

        .admin-navbar .nav-link:hover {
            color: white !important;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
        }

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
            height: calc(100vh - 65px);
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
        .container {
            margin-left: 250px;
            padding: 0;
            width: calc(100% - 250px);
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }

            .container {
                margin-left: 0;
                width: 100%;
            }

            body.show-sidebar .admin-sidebar {
                transform: translateX(0);
            }
        }

        /* Dashboard Header Styles */
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

        /* Card Styles */
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            border: none;
        }

        .card-body {
            padding: 25px;
        }

        /* Table Styles */
        .table {
            margin-bottom: 0;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: #2c3e50;
            background: #f8f9fa;
            padding: 15px;
        }

        .table td {
            padding: 15px;
            vertical-align: middle;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #1e3c72;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6c757d;
            font-size: 14px;
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

            .card-body {
                padding: 15px;
            }
        }

        /* Update Facility Image Styles */
        .facility-image {
            width: 120px;  /* Increased from 80px */
            height: 90px;  /* Increased from 60px */
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .facility-image:hover {
            transform: scale(1.05);
        }

        /* Make table cell wider for image */
        .table td:first-child {
            width: 140px;  /* Give enough space for the larger image */
            padding: 10px;
        }

        @media (max-width: 768px) {
            .facility-image {
                width: 100px;
                height: 75px;
            }
            
            .table td:first-child {
                width: 120px;
            }
        }

        /* Status Badge Styles */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .status-active {
            background: #e8f5e9;
            color: #43a047;
        }

        .status-maintenance {
            background: #fff3e0;
            color: #fb8c00;
        }

        /* Action Buttons */
        .facility-actions .btn {
            padding: 5px 10px;
            margin: 0 3px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<?php include 'admin/navbar.php'; ?>
<?php include 'admin/sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Manage Facilities</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Facilities</li>
                </ol>
            </nav>
        </div>

        <!-- Stats Section -->
        <div class="stats-grid">
            <!-- Your stats cards here -->
    </div>

        <!-- Facilities Table -->
        <div class="card">
                <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">All Facilities</h5>
                    <a href="add_facility.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Facility
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Facility Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($facilities as $facility): ?>
                            <tr>
                                <td>
                                    <?php
                                    // Check if image exists and display it, otherwise show default
                                    $imagePath = $facility['image_path'] ? 'uploads/' . basename($facility['image_path']) : 'images/default-facility.jpg';
                                    if (!file_exists($imagePath)) {
                                        $imagePath = 'images/default-facility.jpg';
                                    }
                                    ?>
                                    <img src="<?= htmlspecialchars($imagePath) ?>" 
                                         alt="<?= htmlspecialchars($facility['name']) ?>"
                                         class="facility-image">
                                </td>
                                <td><?= htmlspecialchars($facility['name']) ?></td>
                                <td><?= htmlspecialchars($facility['description']) ?></td>
                                <td>
                                    <span class="status-badge status-<?= strtolower($facility['status']) ?>">
                                        <?= ucfirst(htmlspecialchars($facility['status'])) ?>
                                    </span>
                                </td>
                                <td class="facility-actions">
                                    <a href="edit_facility.php?id=<?= $facility['id'] ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_facility.php?id=<?= $facility['id'] ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Are you sure you want to delete this facility?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
            