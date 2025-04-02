<?php 
require_once 'includes/config.php';

if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$product = getProductById($_GET['id']);
if (!$product) {
    header('Location: products.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo htmlspecialchars($product['name']); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container product-detail">
        <div class="product-images">
            <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        
        <div class="product-info">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <p class="price">₹<?php echo number_format($product['price'], 2); ?></p>
            <p class="stock"><?php echo $product['stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?></p>
            
            <div class="product-description">
                <h3>Description</h3>
                <p><?php echo htmlspecialchars($product['description']); ?></p>
            </div>
            
            <?php if ($product['stock'] > 0): ?>
                <form class="add-to-cart-form" onsubmit="return false;">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <div class="quantity">
                        <label for="quantity">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                    </div>
                    <button type="submit" class="btn add-to-cart-btn">Add to Cart</button>
                </form>
            <?php else: ?>
                <p class="out-of-stock">This product is currently out of stock.</p>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/script.js"></script>
    <script src="assets/js/cart.js"></script>
</body>
</html>