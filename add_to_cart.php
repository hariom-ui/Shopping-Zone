<?php
require_once 'includes/config.php';

// Enable detailed error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log the incoming request
file_put_contents('cart_debug.log', print_r($_POST, true), FILE_APPEND);

header('Content-Type: application/json');

if (!isLoggedIn()) {
    file_put_contents('cart_debug.log', "User not logged in\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;
    
    file_put_contents('cart_debug.log', "Product ID: $product_id, Quantity: $quantity\n", FILE_APPEND);
    
    if ($product_id) {
        // Verify product exists
        $product = getProductById($product_id);
        if (!$product) {
            file_put_contents('cart_debug.log', "Invalid product ID: $product_id\n", FILE_APPEND);
            echo json_encode(['success' => false, 'message' => 'Invalid product.']);
            exit();
        }
        
        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Add/update product in cart
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
        
        file_put_contents('cart_debug.log', "Cart contents: ".print_r($_SESSION['cart'], true)."\n", FILE_APPEND);
        
        // Count total items in cart
        $cartCount = array_sum($_SESSION['cart']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Product added to cart.',
            'cartCount' => $cartCount
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid product.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}