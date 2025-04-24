<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = (int)$_GET['id'];
$user_id = (int)$_SESSION['user_id'];

// Get order details with payment info
function getOrderWithPayment($order_id, $user_id) {
    global $conn;
    
    // Verify order belongs to user (or admin/manager)
    $access_check = isAdmin() ? "" : "AND o.user_id = ?";
    $params = isAdmin()  ? [$order_id] : [$order_id, $user_id];
    
    $stmt = $conn->prepare("
        SELECT o.*, u.username, u.email, 
               t.status as payment_status, t.upi_transaction_id, t.qr_code_path,
               t.created_at as payment_initiated, pm.name as payment_method
        FROM orders o
        JOIN users u ON o.user_id = u.id
        LEFT JOIN transactions t ON t.order_id = o.id
        LEFT JOIN payment_methods pm ON t.payment_method_id = pm.id
        WHERE o.id = ? $access_check
    ");
    
    $stmt->bind_param(str_repeat("i", count($params)), ...$params);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    
    if (!$order) {
        return false;
    }
    
    // Get order items
    $stmt = $conn->prepare("
        SELECT oi.*, p.name, p.image, p.description
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $order['items'] = $items;
    return $order;
}

$order = getOrderWithPayment($order_id, $user_id);

if (!$order) {
    $_SESSION['error'] = "Order not found or you don't have permission to view it.";
    header('Location: orders.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order #<?= $order['id'] ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="order-detail-container">
            <h1>Order #<?= $order['id'] ?></h1>
            
            <div class="order-meta">
                <div class="meta-section">
                    <h3>Order Information</h3>
                    <p><strong>Date:</strong> <?= date('F j, Y g:i a', strtotime($order['created_at'])) ?></p>
                    <p><strong>Status:</strong> 
                        <span class="status-<?= strtolower($order['status']) ?>">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </p>
                    <p><strong>Total:</strong> ₹<?= number_format($order['total'], 2) ?></p>
                </div>
                
                <div class="meta-section">
                    <h3>Payment Information</h3>
                    <?php if ($order['payment_method']): ?>
                        <p><strong>Method:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
                        <p><strong>Status:</strong> 
                            <span class="payment-status-<?= strtolower($order['payment_status'] ?? 'pending') ?>">
                                <?= ucfirst($order['payment_status'] ?? 'Pending') ?>
                            </span>
                        </p>
                        <?php if ($order['upi_transaction_id']): ?>
                            <p><strong>Transaction ID:</strong> <?= $order['upi_transaction_id'] ?></p>
                        <?php endif; ?>
                        <?php if ($order['payment_initiated']): ?>
                            <p><strong>Initiated:</strong> <?= date('F j, Y g:i a', strtotime($order['payment_initiated'])) ?></p>
                        <?php endif; ?>
                        <?php if ($order['payment_status'] == 'pending' && $order['qr_code_path']): ?>
                            <a href="<?= $order['qr_code_path'] ?>" class="btn" download>Download QR Code</a>
                            <a href="payment_upi.php?order_id=<?= $order['id'] ?>" class="btn">Retry Payment</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>Payment method not selected</p>
                    <?php endif; ?>
                </div>
                
                <div class="meta-section">
                    <h3>Customer Information</h3>
                    <p><strong>Name:</strong> <?= htmlspecialchars($order['username']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                </div>
            </div>
            
            <div class="order-items">
                <h2>Order Items</h2>
                <table class="order-items-table">
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
                                <div class="product-info">
                                    <?php if ($item['image']): ?>
                                    <img src="assets/images/<?= htmlspecialchars($item['image']) ?>" 
                                         alt="<?= htmlspecialchars($item['name']) ?>" width="50">
                                    <?php endif; ?>
                                    <div>
                                        <h4><?= htmlspecialchars($item['name']) ?></h4>
                                        <?php if (!empty($item['description'])): ?>
                                        <p class="product-description">
                                            <?= nl2br(htmlspecialchars(substr($item['description'], 0, 100))) ?>...
                                        </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>₹<?= number_format($item['price'], 2) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td>₹<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                            <td>₹<?= number_format($order['total'], 2) ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Shipping:</strong></td>
                            <td>₹0.00</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total:</strong></td>
                            <td>₹<?= number_format($order['total'], 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="order-actions">
                <a href="orders.php" class="btn">Back to Orders</a>
                <?php if ($order['status'] == 'pending' && (isLoggedIn())): ?>
                    <a href="cancel_order.php?id=<?= $order['id'] ?>" class="btn btn-danger">Cancel Order</a>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>