<?php 
require_once 'includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = $_GET['id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Order Confirmation</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="order-confirmation">
            <h1>Order Confirmation</h1>
            <div class="confirmation-message">
                <p>Thank you for your order!</p>
                <p>Your order number is: <strong>#<?php echo $order_id; ?></strong></p>
                <p>We've sent a confirmation email to your registered email address.</p>
            </div>
            
            <div class="order-actions">
                <a href="orders.php" class="btn">View Your Orders</a>
                <a href="products.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/script.js"></script>
</body>
</html>