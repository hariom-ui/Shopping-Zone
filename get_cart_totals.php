<?php
require_once 'includes/config.php';

header('Content-Type: application/json');

$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
$cartTotal = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $product = getProductById($product_id);
        if ($product) {
            $cartTotal += $product['price'] * $quantity;
        }
    }
}

echo json_encode([
    'count' => $cartCount,
    'total' => $cartTotal
]);