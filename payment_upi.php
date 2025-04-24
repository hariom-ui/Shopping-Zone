<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$orderId = $_GET['order_id'] ?? 0;
$order = getOrderById($orderId, $_SESSION['user_id']);

if (!$order) {
    header('Location: checkout.php');
    exit();
}

// Generate QR Code (using PHP QR Code library)
require_once 'lib/phpqrcode/qrlib.php';

$upiId = 'your-merchant@upi'; // Replace with your UPI ID
$amount = $order['total'];
$note = "Payment for Order #$orderId";
$transactionId = 'TXN'.time().rand(1000,9999);

// UPI Payment URL
$upiUrl = "upi://pay?pa=$upiId&pn=Your%20Store&am=$amount&tn=$note&tid=$transactionId";

// Generate QR Code
$qrPath = "assets/qrcodes/order_$orderId.png";
QRcode::png($upiUrl, $qrPath);

// Save transaction record
$stmt = $conn->prepare("INSERT INTO transactions (order_id, payment_method_id, amount, status, upi_transaction_id, qr_code_path) VALUES (?, 1, ?, 'pending', ?, ?)");
$stmt->bind_param("idss", $orderId, $amount, $transactionId, $qrPath);
$stmt->execute();
?>

<div class="payment-container">
    <h1>Complete Your Payment</h1>
    
    <div class="qr-code">
        <img src="<?= $qrPath ?>" alt="Scan to Pay">
        <p>Scan this QR code with any UPI app</p>
    </div>
    
    <div class="upi-details">
        <p><strong>Amount:</strong> ₹<?= number_format($order['total'], 2) ?></p>
        <p><strong>UPI ID:</strong> <?= $upiId ?></p>
        <p><strong>Order ID:</strong> #<?= $orderId ?></p>
    </div>
    
    <div class="payment-status">
        <p>After payment, click below to verify:</p>
        <button id="checkPayment" class="btn">I've Paid</button>
        <div id="paymentStatus"></div>
    </div>
</div>

<script>
document.getElementById('checkPayment').addEventListener('click', function() {
    fetch('check_payment.php?order_id=<?= $orderId ?>')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'completed') {
                document.getElementById('paymentStatus').innerHTML = 
                    '<div class="alert alert-success">Payment verified! Your order is confirmed.</div>';
                window.location.href = 'order_success.php?id=<?= $orderId ?>';
            } else {
                document.getElementById('paymentStatus').innerHTML = 
                    '<div class="alert alert-danger">Payment not received yet. Please try again.</div>';
            }
        });
});
</script>