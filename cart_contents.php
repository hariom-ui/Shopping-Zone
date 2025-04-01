<?php
require_once 'includes/config.php';

$cartItems = getCartItems();
?>
<table class="cart-items">
    <!-- Your cart table HTML here -->
    <?php foreach ($cartItems as $item): ?>
    <tr data-product-id="<?= $item['id'] ?>">
        <!-- Item details -->
    </tr>
    <?php endforeach; ?>
</table>