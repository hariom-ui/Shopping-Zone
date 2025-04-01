<?php 
require_once 'includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$orders = getUserOrders($_SESSION['user_id']);
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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                <td>$<?php echo number_format($order['total'], 2); ?></td>
                                <td>
                                    <span class="status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn">View</a>
                                    <?php if ($order['status'] == 'pending'): ?>
                                        <a href="cancel_order.php?id=<?php echo $order['id']; ?>" class="btn btn-danger">Cancel</a>
                                    <?php endif; ?>
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