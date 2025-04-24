<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

$orderId = $_GET['order_id'] ?? 0;
$order = getOrderById($orderId, $_SESSION['user_id']);

if (!$order) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid order']);
    exit();
}

// In a real implementation, you would check with your payment gateway API
// This is a simulation - in production use Razorpay/PhonePe/PayTM APIs

$stmt = $conn->prepare("SELECT * FROM transactions WHERE order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$transaction = $stmt->get_result()->fetch_assoc();

// Simulate 70% chance of successful payment
if (rand(1, 10) > 3) {
    // Mark as paid
    $conn->query("UPDATE transactions SET status='completed' WHERE id = {$transaction['id']}");
    $conn->query("UPDATE orders SET status='processing' WHERE id = $orderId");
    
    // Clear cart
    unset($_SESSION['cart']);
    
    echo json_encode(['status' => 'completed']);
} else {
    echo json_encode(['status' => 'pending']);
}