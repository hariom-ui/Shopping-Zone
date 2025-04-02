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
$user_id = $_SESSION['user_id'];

$order = getOrderById($order_id, $user_id);

if (!$order) {
    header('Location: orders.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Details</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="order-details">
            <h1>Order #<?= $order['id'] ?></h1>
            
            <div class="order-meta">
                <p><strong>Date:</strong> <?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></p>
                <p><strong>Status:</strong> <span class="status-<?= strtolower($order['status']) ?>">
                    <?= ucfirst($order['status']) ?>
                </span></p>
                <p><strong>Total:</strong> ₹<?= number_format($order['total'], 2) ?></p>
            </div>
            
            <div class="order-items">
                <h2>Items</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order['items'] as $item): ?>
                        <tr>
                            <td>
                                <img src="assets/images/<?= htmlspecialchars($item['image']) ?>" width="50" alt="<?= htmlspecialchars($item['name']) ?>">
                                <?= htmlspecialchars($item['name']) ?>
                            </td>
                            <td>₹<?= number_format($item['price'], 2) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td>₹<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Order Total:</strong></td>
                            <td>₹<?= number_format($order['total'], 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="order-actions">
                <?php if ($order['status'] == 'pending'): ?>
                    <a href="cancel_order.php?id=<?= $order['id'] ?>" class="btn btn-danger">Cancel Order</a>
                <?php endif; ?>
                <a href="orders.php" class="btn">Back to Orders</a>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>