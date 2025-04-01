<?php 
require_once 'includes/config.php';

// In checkout.php after order creation
//error_log("Order created: ID $order_id for user {$_SESSION['user_id']}");

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$cartItems = getCartItems();

if (empty($cartItems)) {
    header('Location: cart.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = createOrder($_SESSION['user_id'], $cartItems);
    
    if ($order_id) {
        header('Location: order_success.php?id=' . $order_id);
        exit();
    } else {
        $error = "There was an error processing your order. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Checkout</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <h1>Checkout</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="checkout-container">
            <div class="order-summary">
                <h2>Order Summary</h2>
                <ul>
                    <?php foreach ($cartItems as $item): ?>
                        <li>
                            <?php echo htmlspecialchars($item['name']); ?> 
                            (<?php echo $item['quantity']; ?> x $<?php echo number_format($item['price'], 2); ?>)
                            <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="order-total">
                    <strong>Total:</strong>
                    <span>$<?php echo number_format(getCartTotal(), 2); ?></span>
                </div>
            </div>
            
            <div class="checkout-form">
                <h2>Shipping Information</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Shipping Address</label>
                        <textarea id="address" name="address" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="zip">ZIP Code</label>
                        <input type="text" id="zip" name="zip" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="country">Country</label>
                        <select id="country" name="country" required>
                            <option value="">Select Country</option>
                            <option value="US">United States</option>
                            <option value="CA">Canada</option>
                            <option value="UK">United Kingdom</option>
                            <!-- Add more countries as needed -->
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment">Payment Method</label>
                        <select id="payment" name="payment" required>
                            <option value="">Select Payment Method</option>
                            <option value="credit">Credit Card</option>
                            <option value="paypal">PayPal</option>
                            <option value="bank">Bank Transfer</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Place Order</button>
                </form>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/script.js"></script>
</body>
</html>