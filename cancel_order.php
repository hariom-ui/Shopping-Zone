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

if (cancelOrder($order_id, $user_id)) {
    $_SESSION['message'] = "Order #$order_id has been cancelled successfully.";
} else {
    $_SESSION['error'] = "Failed to cancel order #$order_id.";
}

header('Location: orders.php');
exit();
?>