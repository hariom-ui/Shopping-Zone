<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Products</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <h1>Our Products</h1>
        
        <div class="product-filters">
            <form method="GET">
                <input type="text" name="search" placeholder="Search products..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit">Search</button>
            </form>
        </div>
        
        <div class="product-grid">
            <?php
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            $sql = "SELECT * FROM products";
            
            if (!empty($search)) {
                $search = $conn->real_escape_string($search);
                $sql .= " WHERE name LIKE '%$search%' OR description LIKE '%$search%'";
            }
            
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                while ($product = $result->fetch_assoc()) {
                    echo '<div class="product-card">';
                    echo '<img src="assets/images/' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['name']) . '">';
                    echo '<h3 id="p_title">' . htmlspecialchars($product['name']) . '</h3>';
                    echo '<p>₹' . number_format($product['price'], 2) . '</p>';
                    echo '<a href="product_detail.php?id=' . $product['id'] . '" class="btn">View Details</a>';
                    echo '</div>';
                }
            } else {
                echo '<p>No products found.</p>';
            }
            ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/script.js"></script>
</body>
</html>