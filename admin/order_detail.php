<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = (int)$_GET['id'];

// Get order details (admin version can view any order)
function getAdminOrderById($order_id) {
    global $conn;
    
    // Get order header
    $stmt = $conn->prepare("
        SELECT o.*, u.username, u.email 
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ");
    $stmt->bind_param("i", $order_id);
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

$order = getAdminOrderById($order_id);

if (!$order) {
    $_SESSION['error'] = "Order not found.";
    header('Location: orders.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Order #<?= $order['id'] ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>
    
    <main class="admin-container">
        <h1>Order #<?= $order['id'] ?></h1>
        
        <div class="order-meta">
            <div class="meta-row">
                <div>
                    <h3>Customer Information</h3>
                    <p><strong>Name:</strong> <?= htmlspecialchars($order['username']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                </div>
                <div>
                    <h3>Order Information</h3>
                    <p><strong>Date:</strong> <?= date('F j, Y g:i a', strtotime($order['created_at'])) ?></p>
                    <p><strong>Status:</strong> 
                        <span class="status-<?= strtolower($order['status']) ?>">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </p>
                    <p><strong>Total:</strong> ₹<?= number_format($order['total'], 2) ?></p>
                </div>
            </div>
        </div>
        
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