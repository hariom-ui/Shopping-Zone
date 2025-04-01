<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$product_id = $_GET['id'];
$product = getProductById($product_id);

if (!$product) {
    header('Location: products.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    
    // Handle file upload if a new image is provided
    $image = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/images/';
        $uploadFile = $uploadDir . basename($_FILES['image']['name']);
        
        // Generate unique filename
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $uploadFile = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            // Delete old image if it exists
            if (!empty($product['image']) && file_exists($uploadDir . $product['image'])) {
                unlink($uploadDir . $product['image']);
            }
            $image = $filename;
        }
    }
    
    // Update product
    $sql = "UPDATE products SET name = ?, description = ?, price = ?, image = ?, stock = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdsii", $name, $description, $price, $image, $stock, $product_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Product updated successfully.";
        header('Location: products.php');
        exit();
    } else {
        $error = "Failed to update product.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Edit Product</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>
    
    <main class="admin-container">
        <h1>Edit Product</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="admin-form">
            <div class="form-group">
                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo $product['price']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="stock">Stock Quantity</label>
                <input type="number" id="stock" name="stock" min="0" value="<?php echo $product['stock']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="image">Product Image</label>
                <input type="file" id="image" name="image">
                <?php if (!empty($product['image'])): ?>
                    <p>Current image: <?php echo $product['image']; ?></p>
                    <img src="../assets/images/<?php echo $product['image']; ?>" alt="Current product image" width="100">
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn btn-primary">Update Product</button>
            <a href="products.php" class="btn">Cancel</a>
        </form>
    </main>
    
    <?php include 'includes/admin_footer.php'; ?>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>