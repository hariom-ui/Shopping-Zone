<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get available payment methods
$paymentMethods = $conn->query("SELECT * FROM payment_methods WHERE is_active = TRUE");

$cartItems = getCartItems();
$total = getCartTotal();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethodId = (int)$_POST['payment_method'];
    
    // Validate payment method exists
    $stmt = $conn->prepare("SELECT id FROM payment_methods WHERE id = ? AND is_active = TRUE");
    $stmt->bind_param("i", $paymentMethodId);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        die("Invalid payment method selected");
    }

    // Create order
    $orderId = createOrder($_SESSION['user_id'], $cartItems);
    
    if ($orderId) {
        // Redirect based on payment method
        if ($paymentMethodId == 1) { // UPI
            header("Location: payment_upi.php?order_id=$orderId");
        } else {
            // Handle other payment methods
            header("Location: order_success.php?id=$orderId");
        }
        exit();
    }
}
?>

<h2>Select Payment Method</h2>
<form method="POST">
    <?php while ($method = $paymentMethods->fetch_assoc()): ?>
    <div class="payment-option">
        <input type="radio" id="method<?= $method['id'] ?>" 
               name="payment_method" value="<?= $method['id'] ?>"
               <?= $method['id'] == 1 ? 'checked' : '' ?>>
        <label for="method<?= $method['id'] ?>">
            <?= htmlspecialchars($method['name']) ?>
            <small><?= htmlspecialchars($method['description']) ?></small>
        </label>
    </div>
    <?php endwhile; ?>
    
    <button type="submit" class="btn">Proceed to Payment</button>
</form>