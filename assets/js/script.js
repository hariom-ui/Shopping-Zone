/**
 * E-Commerce Website - Main JavaScript File
 * Contains all frontend interactivity for the shopping website
 */

document.addEventListener('DOMContentLoaded', function() {
    // ======================
    // 1. MOBILE MENU TOGGLE
    // ======================
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navMenu = document.querySelector('nav ul');
    
    if (mobileMenuBtn && navMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            this.classList.toggle('open');
        });
    }

    // ======================
    // 2. SHOPPING CART SYSTEM
    // ======================
    const cart = {
        items: JSON.parse(localStorage.getItem('cart')) || [],
        
        // Add item to cart
        addItem: function(productId, quantity = 1) {
            const existingItem = this.items.find(item => item.id === productId);
            
            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                this.items.push({
                    id: productId,
                    quantity: quantity
                });
            }
            
            this.save();
            this.updateUI();
        },
        
        // Remove item from cart
        removeItem: function(productId) {
            this.items = this.items.filter(item => item.id !== productId);
            this.save();
            this.updateUI();
        },
        
        // Update item quantity
        updateQuantity: function(productId, newQuantity) {
            const item = this.items.find(item => item.id === productId);
            if (item) {
                item.quantity = parseInt(newQuantity);
                this.save();
                this.updateUI();
            }
        },
        
        // Save cart to localStorage
        save: function() {
            localStorage.setItem('cart', JSON.stringify(this.items));
        },
        
        // Update cart UI elements
        updateUI: function() {
            // Update cart count in header
            const totalItems = this.items.reduce((sum, item) => sum + item.quantity, 0);
            document.querySelectorAll('.cart-count').forEach(el => {
                el.textContent = totalItems;
                el.style.display = totalItems > 0 ? 'inline-block' : 'none';
            });
            
            // If on cart page, update the cart table
            if (document.querySelector('.cart-table')) {
                this.updateCartPage();
            }
        },
        
        // Update cart page specifically
        updateCartPage: function() {
            // This would be replaced with actual API calls in production
            const cartTable = document.querySelector('.cart-table tbody');
            if (cartTable) {
                // Clear existing rows
                cartTable.innerHTML = '';
                
                // Add new rows
                this.items.forEach(item => {
                    // In a real app, you would fetch product details from your backend
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>
                            <img src="assets/images/product-${item.id}.jpg" alt="Product" width="50">
                            <span>Product ${item.id}</span>
                        </td>
                        <td>$19.99</td>
                        <td>
                            <input type="number" class="cart-quantity" 
                                   value="${item.quantity}" min="1" 
                                   data-product-id="${item.id}">
                        </td>
                        <td>$${(19.99 * item.quantity).toFixed(2)}</td>
                        <td>
                            <button class="btn btn-danger remove-from-cart" 
                                    data-product-id="${item.id}">Remove</button>
                        </td>
                    `;
                    cartTable.appendChild(row);
                });
                
                // Add event listeners to new elements
                this.addCartEventListeners();
            }
        },
        
        // Add event listeners for cart interactions
        addCartEventListeners: function() {
            // Quantity changes
            document.querySelectorAll('.cart-quantity').forEach(input => {
                input.addEventListener('change', (e) => {
                    const productId = e.target.dataset.productId;
                    const newQuantity = e.target.value;
                    this.updateQuantity(productId, newQuantity);
                });
            });
            
            // Remove buttons
            document.querySelectorAll('.remove-from-cart').forEach(button => {
                button.addEventListener('click', (e) => {
                    const productId = e.target.dataset.productId;
                    if (confirm('Remove this item from your cart?')) {
                        this.removeItem(productId);
                    }
                });
            });
        }
    };
    
    // Initialize cart UI
    cart.updateUI();
    
    // ======================
    // 3. PRODUCT INTERACTIONS
    // ======================
    
    // Add to cart buttons
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const quantity = document.querySelector('#quantity-${productId}')?.value || 1;
            
            cart.addItem(productId, parseInt(quantity));
            
            // Show feedback
            const feedback = document.createElement('div');
            feedback.className = 'cart-feedback';
            feedback.textContent = 'Added to cart!';
            document.body.appendChild(feedback);
            
            setTimeout(() => {
                feedback.classList.add('show');
                setTimeout(() => {
                    feedback.classList.remove('show');
                    setTimeout(() => feedback.remove(), 300);
                }, 2000);
            }, 10);
        });
    });
    
    // Quantity selectors
    document.querySelectorAll('.quantity-selector input').forEach(input => {
        input.addEventListener('change', function() {
            if (this.value < 1) this.value = 1;
        });
    });
    
    // ======================
    // 4. FORM VALIDATION
    // ======================
    const validateForms = () => {
        // Login form
        const loginForm = document.querySelector('#login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                const email = this.querySelector('input[type="email"]').value;
                const password = this.querySelector('input[type="password"]').value;
                
                if (!email || !password) {
                    e.preventDefault();
                    alert('Please fill in all fields');
                }
            });
        }
        
        // Registration form
        const registerForm = document.querySelector('#register-form');
        if (registerForm) {
            registerForm.addEventListener('submit', function(e) {
                const password = this.querySelector('#password').value;
                const confirmPassword = this.querySelector('#confirm-password').value;
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                }
            });
        }
    };
    
    validateForms();
    
    // ======================
    // 5. UI ENHANCEMENTS
    // ======================
    
    // Product image zoom
    document.querySelectorAll('.product-image').forEach(img => {
        img.addEventListener('click', function() {
            this.classList.toggle('zoomed');
        });
    });
    
    // Tab system for product details
    const tabs = document.querySelectorAll('.product-tabs li');
    const tabContents = document.querySelectorAll('.tab-content');
    
    if (tabs.length && tabContents.length) {
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs and contents
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                // Add active class to current tab and corresponding content
                this.classList.add('active');
                const tabId = this.dataset.tab;
                document.getElementById(tabId).classList.add('active');
            });
        });
    }
    
    // ======================
    // 6. RESPONSIVE HELPERS
    // ======================
    function handleResponsiveElements() {
        // Example: Change product grid columns based on screen size
        const productGrid = document.querySelector('.product-grid');
        if (productGrid) {
            if (window.innerWidth < 768) {
                productGrid.style.gridTemplateColumns = 'repeat(2, 1fr)';
            } else if (window.innerWidth < 1024) {
                productGrid.style.gridTemplateColumns = 'repeat(3, 1fr)';
            } else {
                productGrid.style.gridTemplateColumns = 'repeat(4, 1fr)';
            }
        }
    }
    
    // Run on load and resize
    handleResponsiveElements();
    window.addEventListener('resize', handleResponsiveElements);
});

// ======================
// 7. UTILITY FUNCTIONS
// ======================

/**
 * Debounce function for performance optimization
 * @param {Function} func - Function to debounce
 * @param {number} wait - Delay in milliseconds
 * @returns {Function}
 */
function debounce(func, wait) {
    let timeout;
    return function() {
        const context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), wait);
    };
}

/**
 * Format price with currency symbol
 * @param {number} amount 
 * @returns {string}
 */
function formatPrice(amount) {
    return '$' + amount.toFixed(2);
}