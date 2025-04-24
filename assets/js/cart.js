document.addEventListener('DOMContentLoaded', function() {
    // Add to cart functionality
    document.querySelectorAll('.add-to-cart-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('add_to_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Product added to cart!');
                    // Update cart count in header if you have one
                    const cartCountElements = document.querySelectorAll('.cart-count');
                    cartCountElements.forEach(el => {
                        el.textContent = data.cartCount;
                    });
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('There was a problem adding the item to your cart.');
            });
        });
    });
});

// Remove from cart functionality
document.querySelectorAll('.remove-from-cart').forEach(button => {
    button.addEventListener('click', function() {
        const productId = this.dataset.productId;
        
        if (confirm('Are you sure you want to remove this item from your cart?')) {
            const formData = new FormData();
            formData.append('product_id', productId);
            
            fetch('remove_from_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Remove the item from the DOM or refresh the page
                    this.closest('tr').remove(); // For table rows
                    // OR location.reload(); // Full page reload
                    
                    // Update cart total if displayed
                    updateCartTotals();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('There was a problem removing the item from your cart.');
            });
        }
    });
});

function updateCartTotals() {
    // Implement your cart total update logic here
    // Example: Refresh the cart totals section
    fetch('get_cart_totals.php')
        .then(response => response.json())
        .then(data => {
            document.querySelector('.cart-total').textContent = '₹' + data.total.toFixed(2);
            document.querySelector('.cart-count').textContent = data.count;
        });
}