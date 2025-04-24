<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>
    
    <main class="admin-container">
        <h1>Admin Dashboard</h1>
        
        <div class="admin-stats">
            <div class="stat-card">
                <h3>Total Products</h3>
                <p><?php 
                    $result = $conn->query("SELECT COUNT(*) as count FROM products");
                    echo $result->fetch_assoc()['count'];
                ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Total Orders</h3>
                <p><?php 
                    $result = $conn->query("SELECT COUNT(*) as count FROM orders");
                    echo $result->fetch_assoc()['count'];
                ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Total Users</h3>
                <p><?php 
                    $result = $conn->query("SELECT COUNT(*) as count FROM users");
                    echo $result->fetch_assoc()['count'];
                ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Revenue</h3>
                <p>₹<?php 
                    $result = $conn->query("SELECT SUM(total) as total FROM orders WHERE status != 'cancelled'");
                    echo number_format($result->fetch_assoc()['total'] ?? 0, 2);
                ?></p>
            </div>

            <div class="stat-card">
                <h3>View All Data</h3>
                <p><a href="database_viewer.php" class = "btn">View</a></p>
            </div>

        </div>
        
        <div class="recent-orders">
            <h2>Recent Orders</h2>
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5";
                    $result = $conn->query($sql);
                    
                    while ($order = $result->fetch_assoc()):
                    ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                            <td>₹<?php echo number_format($order['total'], 2); ?></td>
                            <td>
                                <span class="status-<?php echo strtolower($order['status']); ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn">View</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
    
    <?php include 'includes/admin_footer.php'; ?>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>