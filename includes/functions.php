<?php
// Get all products
function getProducts($limit = null) {
    global $conn;
    
    $sql = "SELECT * FROM products";
    if ($limit) {
        $sql .= " LIMIT $limit";
    }
    $result = $conn->query($sql);
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    return $products;
}

// Get product by ID
function getProductById($id) {
    global $conn;
    
    $id = $conn->real_escape_string($id);
    $sql = "SELECT * FROM products WHERE id = '$id'";
    $result = $conn->query($sql);
    
    return $result->fetch_assoc();
}

// Add product to cart
function addToCart($product_id, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

// Remove product from cart
function removeFromCart($product_id) {
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
}

// Get cart items with product details
function getCartItems() {
    $cartItems = [];
    
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $product = getProductById($product_id);
            if ($product) {
                $product['quantity'] = $quantity;
                $cartItems[] = $product;
            }
        }
    }
    
    return $cartItems;
}

// Calculate cart total
function getCartTotal() {
    $total = 0;
    $cartItems = getCartItems();
    
    foreach ($cartItems as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    return $total;
}

// Create order
function createOrder($user_id, $cartItems) {
    global $conn;
    
    $total = getCartTotal();
    $conn->begin_transaction();
    
    try {
        // Insert order
        $sql = "INSERT INTO orders (user_id, total) VALUES ('$user_id', '$total')";
        $conn->query($sql);
        $order_id = $conn->insert_id;
        
        // Insert order items
        foreach ($cartItems as $item) {
            $product_id = $item['id'];
            $quantity = $item['quantity'];
            $price = $item['price'];
            
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                    VALUES ('$order_id', '$product_id', '$quantity', '$price')";
            $conn->query($sql);
            
            // Update product stock
            $sql = "UPDATE products SET stock = stock - $quantity WHERE id = $product_id";
            $conn->query($sql);
        }
        
        $conn->commit();
        unset($_SESSION['cart']);
        return $order_id;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

// Get user orders
function getUserOrders($user_id) {
    global $conn;
    
    $sql = "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY created_at DESC";
    $result = $conn->query($sql);
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    return $orders;
}

// Cancel order
function cancelOrder($order_id, $user_id) {
    global $conn;
    
    $sql = "UPDATE orders SET status = 'cancelled' WHERE id = '$order_id' AND user_id = '$user_id'";
    return $conn->query($sql);
}

function getOrderById($order_id, $user_id) {
    global $conn;
    
    try {
        // Validate parameters
        if (!is_numeric($order_id)) {
            throw new InvalidArgumentException("Invalid order ID");
        }
        
        if (!is_numeric($user_id)) {
            throw new InvalidArgumentException("Invalid user ID");
        }
        
        // Get order
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $stmt->bind_param("ii", $order_id, $user_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute query");
        }
        
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return false;
        }
        
        $order = $result->fetch_assoc();
        
        // Get order items
        $stmt = $conn->prepare("
            SELECT oi.*, p.name, p.image, p.description
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $stmt->bind_param("i", $order_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute items query");
        }
        
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $order['items'] = $items;
        
        return $order;
        
    } catch (Exception $e) {
        error_log("Error in getOrderById(): " . $e->getMessage());
        return false;
    }
}
?>