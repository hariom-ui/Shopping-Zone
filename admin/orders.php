<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Pagination setup
$per_page = 10;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $per_page;

// Get total count of orders
$count_sql = "SELECT COUNT(*) as total FROM orders";
$total_orders = $conn->query($count_sql)->fetch_assoc()['total'];
$total_pages = ceil($total_orders / $per_page);

// Get orders with pagination and payment status
$sql = "SELECT o.*, 
               u.username as customer_name,
               t.status as payment_status,
               t.upi_transaction_id
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        LEFT JOIN transactions t ON o.id = t.order_id
        ORDER BY o.created_at DESC
        LIMIT $per_page OFFSET $offset";

$orders = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Orders</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        .payment-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }
        .payment-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .payment-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .payment-failed {
            background-color: #f8d7da;
            color: #721c24;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .pagination a {
            padding: 8px 16px;
            margin: 0 4px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #3498db;
            border-radius: 4px;
        }
        .pagination a.active {
            background-color: #3498db;
            color: white;
            border-color: #3498db;
        }
        .pagination a:hover:not(.active) {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>
    
    <main class="admin-container">
        <h1>Order Management</h1>
        
        <div class="admin-actions">
            <form method="get" class="search-form">
                <input type="text" name="search" placeholder="Search orders..." 
                       value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <button type="submit" class="btn">Search</button>
            </form>
        </div>
        
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Order Status</th>
                    <th>Payment Status</th>
                    <th>Transaction ID</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders->num_rows > 0): ?>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $order['id'] ?></td>
                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td><?= date('M j, Y H:i', strtotime($order['created_at'])) ?></td>
                        <td>₹<?= number_format($order['total'], 2) ?></td>
                        <td>
                            <span class="status-<?= strtolower($order['status']) ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($order['payment_status']): ?>
                                <span class="payment-status payment-<?= strtolower($order['payment_status']) ?>">
                                    <?= ucfirst($order['payment_status']) ?>
                                </span>
                            <?php else: ?>
                                <span class="payment-status">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $order['upi_transaction_id'] ?? 'N/A' ?></td>
                        <td>
                            <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn btn-sm">View</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No orders found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="pagination">
            <?php if ($current_page > 1): ?>
                <a href="?page=1">&laquo; First</a>
                <a href="?page=<?= $current_page - 1 ?>">&lsaquo; Previous</a>
            <?php endif; ?>
            
            <?php
            // Show page numbers
            $start = max(1, $current_page - 2);
            $end = min($total_pages, $current_page + 2);
            
            for ($i = $start; $i <= $end; $i++):
            ?>
                <a href="?page=<?= $i ?>" <?= $i == $current_page ? 'class="active"' : '' ?>>
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($current_page < $total_pages): ?>
                <a href="?page=<?= $current_page + 1 ?>">Next &rsaquo;</a>
                <a href="?page=<?= $total_pages ?>">Last &raquo;</a>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/admin_footer.php'; ?>
</body>
</html>