<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

include 'db_connect.php';

$deal_id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM deals WHERE id = ?");
$stmt->execute([$deal_id]);
$deal = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deal_name = $_POST['deal_name'];
    $discount = $_POST['discount'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $price = $_POST['price'];
    $is_best = isset($_POST['is_best']) ? 1 : 0;
    
    // Handle image upload only if new image is selected
    $image_path = $deal['image_path']; // Keep existing image path by default
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate new filename
        $image_name = time() . '_' . $_FILES['image']['name'];
        $target_path = $upload_dir . $image_name;
        
        // Upload new image
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            // Delete old image if exists
            if (!empty($deal['image_path']) && file_exists($upload_dir . $deal['image_path'])) {
                unlink($upload_dir . $deal['image_path']);
            }
            $image_path = $image_name;
        }
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE deals 
            SET deal_name = ?, 
                discount = ?, 
                description = ?, 
                image_path = ?, 
                price = ?,
                start_date = ?, 
                end_date = ?, 
                is_best = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $deal_name, 
            $discount, 
            $description, 
            $image_path,
            $price,
            $start_date, 
            $end_date, 
            $is_best, 
            $deal_id
        ]);
        
        header('Location: best_deals.php?success=updated');
        exit();
    } catch (PDOException $e) {
        $error = "Error updating deal: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Deal | Admin Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #34495e;
            --accent: #3498db;
            --success: #2ecc71;
            --warning: #f1c40f;
            --danger: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --transition: all 0.3s ease;
            --primary-color: #1e3c72;
            --secondary-color: #2a5298;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light);
            padding-top: 80px;
        }

        .edit-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .page-header {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .form-group label {
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 12px 15px;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.2rem rgba(52,152,219,0.25);
        }

        .image-preview {
            margin-top: 15px;
            position: relative;
            width: 200px;
            height: 200px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .date-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .custom-checkbox {
            margin-top: 20px;
        }

        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }

        .btn-primary {
            background: var(--accent);
            border: none;
        }

        .btn-secondary {
            background: #95a5a6;
            border: none;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        @media (max-width: 768px) {
            .edit-container {
                padding: 0 15px;
            }

            .page-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            .date-inputs {
                grid-template-columns: 1fr;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            color: #ffffff !important;
            font-weight: 600;
        }

        .navbar-brand i {
            margin-right: 8px;
        }

        .nav-link {
            color: #ffffff !important;
            padding: 0.8rem 1rem !important;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        .nav-item.active .nav-link {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        .current-image {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .current-image img {
            display: block;
            max-width: 200px;
            height: auto;
        }
        .current-image p {
            margin-bottom: 10px;
            font-weight: 500;
            color: #2c3e50;
        }

        .input-group-text {
            background: #1e3c72;
            color: white;
            border: none;
        }

        input[type="number"] {
            padding-left: 10px;
        }
    </style>
</head>
<body>
    <?php include 'admin/navbar.php'; ?>
    <?php include 'admin/sidebar.php'; ?>
    
    <div class="edit-container">
        <div class="page-header">
            <h1 class="page-title">Edit Deal</h1>
            <a href="best_deals.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Deals
            </a>
        </div>

        <div class="form-container">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form action="edit_deal.php?id=<?= $deal_id ?>" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>
                        <i class="fas fa-tag"></i> Deal Name
                    </label>
                    <input type="text" class="form-control" name="deal_name" 
                           value="<?= htmlspecialchars($deal['deal_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-dollar-sign"></i> Original Price
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="number" 
                               class="form-control" 
                               name="price" 
                               step="0.01" 
                               min="0" 
                               required 
                               value="<?= htmlspecialchars($deal['price']) ?>"
                               placeholder="Enter original price">
                    </div>
                    <small class="form-text text-muted">Enter the original price before discount</small>
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-percent"></i> Discount Percentage
                    </label>
                    <input type="number" class="form-control" name="discount" 
                           value="<?= htmlspecialchars($deal['discount']) ?>" 
                           min="1" max="99" required>
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-align-left"></i> Description
                    </label>
                    <textarea class="form-control" name="description" rows="4" required><?= htmlspecialchars($deal['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-image"></i> Deal Image
                    </label>
                    
                    <?php if (!empty($deal['image_path'])): ?>
                        <div class="current-image mb-3">
                            <p>Current Image:</p>
                            <img src="uploads/<?php echo htmlspecialchars($deal['image_path']); ?>" 
                                 alt="Current Deal Image"
                                 style="max-width: 200px; height: auto; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                        </div>
                    <?php endif; ?>
                    
                    <input type="file" class="form-control" name="image" accept="image/*">
                    <small class="form-text text-muted">Leave empty to keep current image</small>
                </div>

                <div class="date-inputs">
                    <div class="form-group">
                        <label>
                            <i class="fas fa-calendar-plus"></i> Start Date
                        </label>
                        <input type="date" class="form-control" name="start_date" 
                               value="<?= $deal['start_date'] ?>" required>
                    </div>

                    <div class="form-group">
                        <label>
                            <i class="fas fa-calendar-minus"></i> End Date
                        </label>
                        <input type="date" class="form-control" name="end_date" 
                               value="<?= $deal['end_date'] ?>" required>
                    </div>
                </div>

                <div class="custom-checkbox">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_best" 
                               name="is_best" <?= $deal['is_best'] ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="is_best">
                            <i class="fas fa-star"></i> Mark as Best Deal
                        </label>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="best_deals.php" class="btn btn-secondary">
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
    // Validate end date is after start date
    document.querySelector('input[name="end_date"]').addEventListener('change', function() {
        const startDate = document.querySelector('input[name="start_date"]').value;
        if (startDate && this.value < startDate) {
            alert('End date must be after start date');
            this.value = '';
        }
    });

    // Preview image before upload
    document.querySelector('input[name="image_path"]').addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.querySelector('.image-preview img');
                if (preview) {
                    preview.src = e.target.result;
                } else {
                    const newPreview = document.createElement('div');
                    newPreview.className = 'image-preview';
                    newPreview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    document.querySelector('.form-group').appendChild(newPreview);
                }
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    document.querySelector('input[name="price"]').addEventListener('change', function() {
        if (this.value < 0) {
            this.value = 0;
        }
    });
    </script>

</body>
</html>
