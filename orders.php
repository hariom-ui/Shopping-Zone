<?php 
require_once 'includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get user orders with payment status
function getUserOrdersWithPayment($user_id) {
    global $conn;
    
    $sql = "SELECT o.*, t.status as payment_status 
            FROM orders o
            LEFT JOIN transactions t ON o.id = t.order_id
            WHERE o.user_id = ?
            ORDER BY o.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$orders = getUserOrdersWithPayment($_SESSION['user_id']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - My Orders</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <h1>My Orders</h1>
        
        <?php if (!empty($orders)): ?>
            <div class="orders-list">
                <table>
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payments</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                        <td>#<?= $order['id'] ?></td>
                        <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                        <td>₹<?= number_format($order['total'], 2) ?></td>
                        <td>
                            <span class="status-<?= strtolower($order['status']) ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($order['payment_status']): ?>
                                <span class="payment-status-<?= strtolower($order['payment_status']) ?>">
                                    <?= ucfirst($order['payment_status']) ?>
                                </span>
                            <?php else: ?>
                                <span class="payment-status-pending">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn">View</a>
                        </td>
                    </tr>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>You haven't placed any orders yet. <a href="products.php">Browse our products</a> to get started.</p>
        <?php endif; ?>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/script.js"></script>
</body>
</html>