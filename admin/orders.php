<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Pagination setup
$per_page = 10;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $per_page;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = '';
$params = [];
$types = '';

if (!empty($search)) {
    $where = "WHERE (o.id LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
    $search_term = "%$search%";
    $params = array_fill(0, 3, $search_term);
    $types = 'sss';
}

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM orders o JOIN users u ON o.user_id = u.id $where";
$stmt = $conn->prepare($count_sql);
if (!empty($where)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_orders = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_orders / $per_page);

// Get orders with pagination
$sql = "SELECT o.*, u.username, u.email 
        FROM orders o
        JOIN users u ON o.user_id = u.id
        $where
        ORDER BY o.created_at DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
if (!empty($where)) {
    $params[] = $per_page;
    $params[] = $offset;
    $types .= 'ii';
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param('ii', $per_page, $offset);
}
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Manage Orders</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>
    
    <main class="admin-container">
        <h1>Order Management</h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="admin-actions">
            <form method="get" class="search-form">
                <input type="text" name="search" placeholder="Search orders..." 
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="orders.php" class="btn">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="order-filters">
            <a href="orders.php?status=all" class="btn <?= !isset($_GET['status']) ? 'active' : '' ?>">All</a>
            <a href="orders.php?status=pending" class="btn <?= ($_GET['status'] ?? '') == 'pending' ? 'active' : '' ?>">Pending</a>
            <a href="orders.php?status=processing" class="btn <?= ($_GET['status'] ?? '') == 'processing' ? 'active' : '' ?>">Processing</a>
            <a href="orders.php?status=shipped" class="btn <?= ($_GET['status'] ?? '') == 'shipped' ? 'active' : '' ?>">Shipped</a>
            <a href="orders.php?status=delivered" class="btn <?= ($_GET['status'] ?? '') == 'delivered' ? 'active' : '' ?>">Delivered</a>
            <a href="orders.php?status=cancelled" class="btn <?= ($_GET['status'] ?? '') == 'cancelled' ? 'active' : '' ?>">Cancelled</a>
        </div>
        
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="6" class="text-center">No orders found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><a href="order_detail.php?id=<?= $order['id'] ?>">#<?= $order['id'] ?></a></td>
                        <td>
                            <?= htmlspecialchars($order['username']) ?>
                            <small><?= htmlspecialchars($order['email']) ?></small>
                        </td>
                        <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                        <td>₹<?= number_format($order['total'], 2) ?></td>
                        <td>
                            <span class="status-<?= strtolower($order['status']) ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($current_page > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" class="btn">First</a>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>" class="btn">Previous</a>
            <?php endif; ?>
            
            <span>Page <?= $current_page ?> of <?= $total_pages ?></span>
            
            <?php if ($current_page < $total_pages): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>" class="btn">Next</a>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>" class="btn">Last</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </main>
    
    <?php include 'includes/admin_footer.php'; ?>
</body>
</html>