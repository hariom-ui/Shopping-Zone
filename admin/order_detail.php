<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = (int)$_GET['id'];

// Get order with payment info
function getAdminOrderWithPayment($order_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT o.*, u.username, u.email, 
               t.status as payment_status, t.upi_transaction_id, t.qr_code_path,
               pm.name as payment_method
        FROM orders o
        JOIN users u ON o.user_id = u.id
        LEFT JOIN transactions t ON t.order_id = o.id
        LEFT JOIN payment_methods pm ON pm.id = t.payment_method_id
        WHERE o.id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    
    if (!$order) return false;
    
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

$order = getAdminOrderWithPayment($order_id);

if (!$order) {
    $_SESSION['error'] = "Order not found.";
    header('Location: orders.php');
    exit();
}

// Update payment status if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment'])) {
    $new_status = $_POST['payment_status'];
    $transaction_id = $_POST['transaction_id'];
    
    $stmt = $conn->prepare("UPDATE transactions SET status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Payment status updated successfully!";
        header("Location: order_detail.php?id=$order_id");
        exit();
    } else {
        $_SESSION['error'] = "Failed to update payment status.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Order #<?= $order['id'] ?></title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>
    
    <main class="admin-container">
        <h1>Order #<?= $order['id'] ?></h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="order-meta">
            <div class="meta-section">
                    <h3>Customer Information</h3>
                    <p><strong>Name:</strong> <?= htmlspecialchars($order['username']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                </div>
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
                    <p><strong>Method:</strong> <?= $order['payment_method'] ?? 'N/A' ?></p>
                    <p><strong>Status:</strong> 
                        <span class="payment-status-<?= strtolower($order['payment_status'] ?? 'pending') ?>">
                            <?= ucfirst($order['payment_status'] ?? 'Pending') ?>
                        </span>
                    </p>
                    <?php if (!empty($order['upi_transaction_id'])): ?>
                        <p><strong>Transaction ID:</strong> <?= $order['upi_transaction_id'] ?></p>
                    <?php endif; ?>
                </div>
        
        
        <!-- Payment Status Update Form -->
        <?php if (isset($order['payment_status'])): ?>
        <div class="meta-section">
            <h3>Update Payment Status</h3>
            <form method="POST">
                <input type="hidden" name="update_payment" value="1">
                <input type="hidden" name="transaction_id" value="<?= $order['upi_transaction_id'] ?? '' ?>">
         
                <div class="admin-form-group">
                    <label class="admin-form-label">Payment Status</label>
                    <select name="payment_status" class="admin-form-control">
                        <option value="pending" <?= ($order['payment_status'] ?? '') == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="completed" <?= ($order['payment_status'] ?? '') == 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="failed" <?= ($order['payment_status'] ?? '') == 'failed' ? 'selected' : '' ?>>Failed</option>
                    </select>
                </div>
                
                <button type="submit" class="admin-btn">Update Payment Status</button>
            </form>
        </div>
        </div>
        <?php endif; ?>
        
        <!-- QR Code Display -->
        <?php if (!empty($order['qr_code_path'])): ?>
        <div class="meta-section">
            <h3>Payment QR Code</h3>
            <div style="text-align: center;">
                <img src="../<?= $order['qr_code_path'] ?>" alt="Payment QR Code" style="max-width: 300px;">
                <p>Scanned on: <?= date('M j, Y g:i a', filemtime("../".$order['qr_code_path'])) ?></p>
            </div>
        </div>
        </div>
        <?php endif; ?>
        
        <!-- Rest of your order details (items, etc.) -->
        <div class="order-items">
            <h2>Order Items</h2>
            <table class="admin-table">
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
                                <img src="../assets/images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" width="50">
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
                        <td colspan="3" class="text-right"><strong>Tax:</strong></td>
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
            <form method="post" action="update_order_status.php" class="status-form">
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                <label for="status"><strong>Update Status:</strong></label>
                <select name="status" id="status">
                    <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                    <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
                <button type="submit" class="btn">Update</button>
            </form>
            
            <a href="orders.php" class="btn">Back to Orders</a>
        </div>
    </main>
    
    <?php include 'includes/admin_footer.php'; ?>
    
    <script>
    // Add confirmation for status changes to cancelled
    document.querySelector('.status-form').addEventListener('submit', function(e) {
        const status = document.getElementById('status').value;
        if (status === 'cancelled') {
            if (!confirm('Are you sure you want to cancel this order?')) {
                e.preventDefault();
            }
        }
    });
    </script>
    
</body>
</html>