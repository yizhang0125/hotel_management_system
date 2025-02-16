<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

include 'db_connect.php';

$facility_id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM facilities WHERE id = ?");
$stmt->execute([$facility_id]);
$facility = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    
    // Handle image upload only if new image is selected
    $image_path = $facility['image_path']; // Keep existing image path by default
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate new filename with sanitization
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $image_name = time() . '_' . preg_replace("/[^a-zA-Z0-9]/", "", pathinfo($_FILES['image']['name'], PATHINFO_FILENAME)) . '.' . $file_extension;
        
        // Include uploads/ in the database path
        $image_path = $upload_dir . $image_name;
        $target_path = $image_path;
        
        // Upload new image
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            // Delete old image if exists
            if (!empty($facility['image_path']) && file_exists($facility['image_path'])) {
                unlink($facility['image_path']);
            }
        }
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE facilities 
            SET name = ?, 
                description = ?, 
                image_path = ?, 
                status = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $name, 
            $description, 
            $image_path,
            $status, 
            $facility_id
        ]);
        
        header('Location: admin_facilities.php?success=updated');
        exit();
    } catch (PDOException $e) {
        $error = "Error updating facility: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Facility | Admin Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e3c72;
            --secondary-color: #2a5298;
            --accent: #3498db;
            --success: #2ecc71;
            --warning: #f1c40f;
            --danger: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Poppins', sans-serif;
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

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group label i {
            color: var(--primary-color);
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            padding: 12px 15px;
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(30, 60, 114, 0.15);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .current-image {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .current-image p {
            color: var(--primary-color);
            font-weight: 500;
            margin-bottom: 10px;
        }

        .current-image img {
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .current-image img:hover {
            transform: scale(1.02);
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
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
        }

        .btn-secondary {
            background: #6c757d;
            border: none;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .form-text {
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: 5px;
        }

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

        .select-container {
            position: relative;
            width: 100%;
        }

        .alert {
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 25px;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
                padding: 20px;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        .placeholder-image {
            width: 200px;
            height: 150px;
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            color: #6c757d;
            border: 2px dashed #dee2e6;
            gap: 10px;
        }

        .placeholder-image i {
            font-size: 2rem;
            opacity: 0.5;
        }

        .placeholder-image span {
            font-size: 0.9rem;
        }

        .image-upload-container {
            margin: 15px 0;
        }

        input[type="file"] {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }
    </style>
</head>
<body>
    <?php include 'admin/navbar.php'; ?>
    <?php include 'admin/sidebar.php'; ?>
    
    <div class="edit-container">
        <div class="page-header">
            <h1 class="page-title">Edit Facility</h1>
            <a href="admin_facilities.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Facilities
            </a>
        </div>

        <div class="form-container">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form action="edit_facility.php?id=<?= $facility_id ?>" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>
                        <i class="fas fa-spa"></i> Facility Name
                    </label>
                    <input type="text" class="form-control" name="name" 
                           value="<?= htmlspecialchars($facility['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-align-left"></i> Description
                    </label>
                    <textarea class="form-control" name="description" rows="4" required><?= htmlspecialchars($facility['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-image"></i> Facility Image
                    </label>
                    
                    <div class="current-image mb-3">
                        <p>Current Image:</p>
                        <?php 
                        // Remove the extra 'uploads/' since it's already in the database path
                        if (!empty($facility['image_path']) && file_exists($facility['image_path'])): ?>
                            <img src="<?= htmlspecialchars($facility['image_path']) ?>" 
                                 alt="Current Facility Image"
                                 style="max-width: 200px; height: auto; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                        <?php else: ?>
                            <div class="placeholder-image">
                                <i class="fas fa-image"></i>
                                <span>No image available</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <input type="file" class="form-control" name="image" accept="image/*">
                    <small class="form-text text-muted">Leave empty to keep current image</small>
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-toggle-on"></i> Status
                    </label>
                    <div class="select-container">
                        <select class="form-control" name="status" required>
                            <option value="1" <?= $facility['status'] == 1 ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= $facility['status'] == 0 ? 'selected' : '' ?>>Maintenance</option>
                        </select>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="admin_facilities.php" class="btn btn-secondary">
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
    // Preview image before upload
    document.querySelector('input[name="image"]').addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Find the current image container
                const currentImage = document.querySelector('.current-image img');
                if (currentImage) {
                    // Update existing image
                    currentImage.src = e.target.result;
                } else {
                    // Create new preview if no image exists
                    const previewContainer = document.querySelector('.current-image');
                    if (previewContainer) {
                        // Replace placeholder with new image
                        previewContainer.innerHTML = `
                            <p>Preview:</p>
                            <img src="${e.target.result}" alt="Preview Image" 
                                 style="max-width: 200px; height: auto; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                        `;
                    }
                }
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
    </script>

</body>
</html>
