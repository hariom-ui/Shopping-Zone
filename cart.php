<?php 
require_once 'includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$cartItems = getCartItems();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Shopping Cart</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <h1>Shopping Cart</h1>
        
        <?php if (!empty($cartItems)): ?>
            <div class="cart-items">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td>
                                    <img src="assets/images/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" width="50">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </td>
                                <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <input type="number" class="quantity-input" data-product-id="<?php echo $item['id']; ?>" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>">
                                </td>
                                <td>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                <td>
                                    <button class="btn remove-from-cart" data-product-id="<?php echo $item['id']; ?>">Remove</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total:</strong></td>
                            <td colspan="2">₹<?php echo number_format(getCartTotal(), 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
                
                <div class="cart-actions">
                    <a href="products.php" class="btn">Continue Shopping</a>
                    <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
                </div>
            </div>
        <?php else: ?>
            <p>Your cart is empty. <a href="products.php">Browse our products</a> to add items to your cart.</p>
        <?php endif; ?>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/script.js"></script>
    <script src="assets/js/cart.js"></script>
</body>
</html>