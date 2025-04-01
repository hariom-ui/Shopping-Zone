<?php
require_once 'includes/config.php';

// Enable detailed error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log the incoming request
file_put_contents('cart_remove_debug.log', print_r($_POST, true), FILE_APPEND);

header('Content-Type: application/json');

if (!isLoggedIn()) {
    file_put_contents('cart_remove_debug.log', "User not logged in\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Please login to modify your cart.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? null;
    
    file_put_contents('cart_remove_debug.log', "Attempting to remove product ID: $product_id\n", FILE_APPEND);
    
    if ($product_id) {
        // Check if product exists in cart
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            file_put_contents('cart_remove_debug.log', "Product removed. Cart contents: ".print_r($_SESSION['cart'], true)."\n", FILE_APPEND);
            echo json_encode(['success' => true]);
        } else {
            file_put_contents('cart_remove_debug.log', "Product not found in cart\n", FILE_APPEND);
            echo json_encode(['success' => false, 'message' => 'Product not in cart.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}