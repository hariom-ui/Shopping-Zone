<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: orders.php');
    exit();
}

$order_id = (int)$_POST['order_id'];
$status = $_POST['status'];

// Validate status
$allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
if (!in_array($status, $allowed_statuses)) {
    $_SESSION['error'] = "Invalid order status.";
    header("Location: order_detail.php?id=$order_id");
    exit();
}

// Update status
$stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $order_id);

if ($stmt->execute()) {
    $_SESSION['message'] = "Order status updated successfully.";
} else {
    $_SESSION['error'] = "Failed to update order status.";
}

header("Location: order_detail.php?id=$order_id");
exit();
?>