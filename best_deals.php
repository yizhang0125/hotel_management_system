<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

include 'db_connect.php';

// Create deals table if it doesn't exist
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS deals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        discount INT NOT NULL,
        image_path VARCHAR(255),
        valid_until DATE,
        status ENUM('active', 'expired') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    // Handle error silently
}

// Fetch all deals with error handling
try {
    $current_date = date('Y-m-d');

    // Modify your SQL query to check expiration
    $stmt = $pdo->prepare("
        SELECT 
            id,
            deal_name,
            description,
            discount,
            image_path,    /* This will be stored without 'uploads/' prefix */
            price,
            start_date,
            end_date,
            is_best,
            CASE 
                WHEN end_date < ? THEN 'expired'
                WHEN start_date > ? THEN 'upcoming'
                ELSE 'active'
            END as deal_status
        FROM deals 
        WHERE end_date >= ? OR end_date IS NULL
        ORDER BY end_date DESC
    ");
    $stmt->execute([$current_date, $current_date, $current_date]);
    $deals = $stmt->fetchAll();
} catch (PDOException $e) {
    $deals = [];
}

// Calculate statistics
$stats = [
    'total_deals' => count($deals),
    'active_deals' => count(array_filter($deals, fn($d) => $d['deal_status'] === 'active')),
    'upcoming_deals' => count(array_filter($deals, fn($d) => $d['deal_status'] === 'upcoming'))
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Best Deals | Admin</title>
    <!-- Load jQuery first -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Then load Bootstrap's JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
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

        /* Deal Image Styles */
        .deal-image {
            width: 120px;
            height: 90px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .deal-image:hover {
            transform: scale(1.05);
        }

        /* Enhanced Table Styles */
        .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .table th {
            border: none;
            font-weight: 600;
            color: #1e3c72;
            background: #f8f9fa;
            padding: 15px 20px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            padding: 15px 20px;
            vertical-align: middle;
            background: white;
            border-top: 1px solid #edf2f9;
            border-bottom: 1px solid #edf2f9;
        }

        .table tr td:first-child {
            border-left: 1px solid #edf2f9;
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        .table tr td:last-child {
            border-right: 1px solid #edf2f9;
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        /* Enhanced Badge Styles */
        .badge {
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .badge-success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .badge-warning {
            background-color: #fff3e0;
            color: #ef6c00;
        }

        /* Enhanced Button Styles */
        .btn-action {
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30,60,114,0.2);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
        }

        /* Price and Discount Styles */
        .discount-badge {
            background: #ffe0e0;
            color: #d32f2f;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .valid-until {
            color: #6c757d;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }

        .valid-until i {
            margin-right: 5px;
            font-size: 0.8rem;
        }

        /* Card Header Styles */
        .card-header {
            background: white;
            border-bottom: 1px solid #edf2f9;
            padding: 20px 25px;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e3c72;
            margin: 0;
        }

        /* Action Buttons Container */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-buttons .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            padding: 0;
            border-radius: 6px;
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

            .deal-image {
                width: 100px;
                height: 75px;
            }

            .table td:first-child {
                width: 120px;
            }
        }

        /* Add these styles */
        .deal-expired {
            opacity: 0.7;
            position: relative;
        }

        .deal-expired::after {
            content: 'EXPIRED';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            font-size: 2rem;
            font-weight: bold;
            color: #dc3545;
            border: 3px solid #dc3545;
            padding: 10px 20px;
            border-radius: 8px;
            pointer-events: none;
            z-index: 1;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge-danger {
            background: #ffe5e5;
            color: #dc3545;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .deal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<?php include 'admin/navbar.php'; ?>
<?php include 'admin/sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Best Deals</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Best Deals</li>
                </ol>
            </nav>
        </div>

        <!-- Stats Section -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #e3f2fd; color: #1e88e5;">
                    <i class="fas fa-tag"></i>
                </div>
                <div class="stat-value"><?= $stats['total_deals'] ?></div>
                <div class="stat-label">Total Deals</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #e8f5e9; color: #43a047;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value"><?= $stats['active_deals'] ?></div>
                <div class="stat-label">Active Deals</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #fff3e0; color: #fb8c00;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value"><?= $stats['upcoming_deals'] ?></div>
                <div class="stat-label">Upcoming Deals</div>
            </div>
        </div>

        <!-- Deals Table -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title">
                        <i class="fas fa-tags mr-2"></i> All Deals
                    </h5>
                    <a href="add_deal.php" class="btn btn-action btn-primary">
                        <i class="fas fa-plus mr-2"></i> Add New Deal
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Deal Name</th>
                                <th>Discount</th>
                                <th>Valid Until</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($deals as $deal): ?>
                            <tr>
                                <td>
                                    <?php
                                    // Check if image exists and add uploads/ directory
                                    $imagePath = !empty($deal['image_path']) ? 
                                        'uploads/' . $deal['image_path'] : 
                                        'images/default-deal.jpg';
                                    ?>
                                    <img src="<?= htmlspecialchars($imagePath) ?>" 
                                         alt="<?= htmlspecialchars($deal['deal_name']) ?>"
                                         class="deal-image">
                                </td>
                                <td>
                                    <div class="font-weight-bold"><?= htmlspecialchars($deal['deal_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars(substr($deal['description'] ?? '', 0, 50)) ?>...</small>
                                    <?php if ($deal['is_best']): ?>
                                        <span class="badge badge-primary ml-2">Best Deal</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="price-info">
                                        <span class="discount-badge">
                                            <?= htmlspecialchars($deal['discount']) ?>% OFF
                                        </span>
                                        <div class="original-price">
                                            $<?= number_format($deal['price'], 2) ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="valid-until">
                                        <i class="far fa-calendar-alt"></i>
                                        <?= date('M d, Y', strtotime($deal['end_date'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $status_badge = match($deal['deal_status']) {
                                        'upcoming' => '<span class="badge badge-warning">Upcoming</span>',
                                        'active' => '<span class="badge badge-success">Active</span>',
                                        default => ''
                                    };
                                    echo $status_badge;
                                    ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit_deal.php?id=<?= $deal['id'] ?>" 
                                           class="btn btn-primary btn-sm" 
                                           title="Edit Deal">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete_deal.php?id=<?= $deal['id'] ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this deal?')"
                                           title="Delete Deal">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
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
