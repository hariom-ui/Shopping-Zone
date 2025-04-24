<?php
// admin/add_product.php
error_reporting(0); // Disable error display - log them instead
ini_set('display_errors', 0);

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Security check
if (!isAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

// Initialize variables
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate input
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        
        if (empty($name) || empty($description) || $price <= 0) {
            throw new Exception('All fields are required and price must be positive');
        }

        // Handle file upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/images/products/';
            
            // Create directory if not exists
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Validate image
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = mime_content_type($_FILES['image']['tmp_name']);
            
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception('Only JPG, PNG, and GIF images are allowed');
            }
            
            // Generate unique filename
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            $destination = $uploadDir . $filename;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                throw new Exception('Failed to upload image');
            }
            
            $image = $filename;
        } else {
            throw new Exception('Product image is required');
        }

        // Insert into database
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, image, stock) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsi", $name, $description, $price, $image, $stock);
        
        if (!$stmt->execute()) {
            // Delete the uploaded image if DB insert failed
            if ($image && file_exists($uploadDir . $image)) {
                unlink($uploadDir . $image);
            }
            throw new Exception('Database error: ' . $stmt->error);
        }
        
        $success = 'Product added successfully!';
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        error_log('Add Product Error: ' . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Product</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>
    
    <div class="admin-container">
        <h1>Add New Product</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="admin-form">
            <div class="form-group">
                <label>Product Name *</label>
                <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label>Description *</label>
                <textarea name="description" rows="5" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Price *</label>
                <input type="number" step="0.01" min="0.01" name="price" required value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label>Stock Quantity *</label>
                <input type="number" min="0" name="stock" required value="<?= htmlspecialchars($_POST['stock'] ?? 0) ?>">
            </div>
            
            <div class="form-group">
                <label>Product Image *</label>
                <input type="file" name="image" accept="image/*" required>
                <small>Allowed formats: JPG, PNG, GIF (Max 10MB)</small>
            </div>
            
            <button type="submit" class="btn btn-primary">Add Product</button>
            <a href="products.php" class="btn">Cancel</a>
        </form>
    </div>
    
    <?php include 'includes/admin_footer.php'; ?>
</body>
</html>