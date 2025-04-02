<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Home</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <section class="hero">
            <h1>Welcome to <?php echo SITE_NAME; ?></h1>
            <p>Discover amazing products at great prices</p>
            <a href="products.php" class="btn">Shop Now</a>
        </section>
        
        <section class="featured-products">
            <h2>Featured Products</h2>
            <div class="product-grid">
                <?php
                $products = getProducts(4);
                foreach ($products as $product) {
                    echo '<div class="product-card">';
                    echo '<img src="assets/images/' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['name']) . '">';
                    echo '<h3>' . htmlspecialchars($product['name']) . '</h3>';
                    echo '<p>₹' . number_format($product['price'], 2) . '</p>';
                    echo '<a href="product_detail.php?id=' . $product['id'] . '" class="btn">View Details</a>';
                    echo '</div>';
                }
                ?>
            </div>
        </section>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/script.js"></script>
</body>
</html>
