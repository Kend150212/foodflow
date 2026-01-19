/**
 * FoodFlow - Cart System
 */

const Cart = {
    items: [],
    storageKey: 'foodflow_cart',

    /**
     * Initialize cart from localStorage
     */
    init() {
        const saved = localStorage.getItem(this.storageKey);
        if (saved) {
            try {
                this.items = JSON.parse(saved);
            } catch (e) {
                this.items = [];
            }
        }
        this.updateUI();
        this.bindEvents();
    },

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Cart button
        const cartBtn = document.querySelector('.cart-btn');
        if (cartBtn) {
            cartBtn.addEventListener('click', () => this.openSidebar());
        }

        // Cart overlay
        const overlay = document.querySelector('.cart-overlay');
        if (overlay) {
            overlay.addEventListener('click', () => this.closeSidebar());
        }

        // Cart close button
        const closeBtn = document.querySelector('.cart-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.closeSidebar());
        }

        // Add to cart buttons
        document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const itemId = btn.dataset.id;
                const itemName = btn.dataset.name;
                const itemPrice = parseFloat(btn.dataset.price);
                const itemImage = btn.dataset.image || '';

                this.addItem({
                    id: itemId,
                    name: itemName,
                    price: itemPrice,
                    image: itemImage,
                    quantity: 1
                });
            });
        });
    },

    /**
     * Add item to cart
     */
    addItem(item) {
        const existingIndex = this.items.findIndex(i => i.id === item.id);

        if (existingIndex > -1) {
            this.items[existingIndex].quantity += item.quantity || 1;
        } else {
            this.items.push({
                ...item,
                quantity: item.quantity || 1
            });
        }

        this.save();
        this.updateUI();
        this.animateCartButton();
        showToast(`${item.name} added to cart!`);
    },

    /**
     * Remove item from cart
     */
    removeItem(itemId) {
        this.items = this.items.filter(item => item.id !== itemId);
        this.save();
        this.updateUI();
    },

    /**
     * Update item quantity
     */
    updateQuantity(itemId, quantity) {
        const item = this.items.find(i => i.id === itemId);
        if (item) {
            if (quantity <= 0) {
                this.removeItem(itemId);
            } else {
                item.quantity = quantity;
                this.save();
                this.updateUI();
            }
        }
    },

    /**
     * Get total items count
     */
    getTotalItems() {
        return this.items.reduce((sum, item) => sum + item.quantity, 0);
    },

    /**
     * Get subtotal
     */
    getSubtotal() {
        return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    },

    /**
     * Save to localStorage
     */
    save() {
        localStorage.setItem(this.storageKey, JSON.stringify(this.items));
    },

    /**
     * Clear cart
     */
    clear() {
        this.items = [];
        this.save();
        this.updateUI();
    },

    /**
     * Update all UI elements
     */
    updateUI() {
        this.updateBadge();
        this.updateSidebar();
    },

    /**
     * Update cart badge
     */
    updateBadge() {
        const badge = document.querySelector('.cart-badge');
        const count = this.getTotalItems();

        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        }
    },

    /**
     * Update cart sidebar
     */
    updateSidebar() {
        const itemsContainer = document.querySelector('.cart-items');
        const subtotalEl = document.querySelector('.cart-subtotal-amount');
        const checkoutBtn = document.querySelector('.cart-checkout-btn');

        if (!itemsContainer) return;

        if (this.items.length === 0) {
            itemsContainer.innerHTML = `
                <div class="cart-empty">
                    <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #ccc; margin: 2rem auto; display: block;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <p style="text-align: center; color: #888;">Your cart is empty</p>
                    <p style="text-align: center; color: #aaa; font-size: 0.875rem;">Add some delicious items!</p>
                </div>
            `;
            if (checkoutBtn) checkoutBtn.disabled = true;
        } else {
            itemsContainer.innerHTML = this.items.map(item => `
                <div class="cart-item" data-id="${item.id}">
                    ${item.image ? `<img src="${item.image}" alt="${item.name}" class="cart-item-image">` : ''}
                    <div class="cart-item-info">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-price">${formatPrice(item.price)}</div>
                        <div class="cart-item-qty">
                            <button class="qty-btn" onclick="Cart.updateQuantity('${item.id}', ${item.quantity - 1})">−</button>
                            <span>${item.quantity}</span>
                            <button class="qty-btn" onclick="Cart.updateQuantity('${item.id}', ${item.quantity + 1})">+</button>
                        </div>
                    </div>
                    <button class="cart-item-remove" onclick="Cart.removeItem('${item.id}')" style="background: none; border: none; cursor: pointer; padding: 8px;">
                        <svg width="20" height="20" fill="none" stroke="#888" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            `).join('');
            if (checkoutBtn) checkoutBtn.disabled = false;
        }

        if (subtotalEl) {
            subtotalEl.textContent = formatPrice(this.getSubtotal());
        }
    },

    /**
     * Open cart sidebar
     */
    openSidebar() {
        const overlay = document.querySelector('.cart-overlay');
        const sidebar = document.querySelector('.cart-sidebar');

        if (overlay) overlay.classList.add('open');
        if (sidebar) sidebar.classList.add('open');
        document.body.style.overflow = 'hidden';
    },

    /**
     * Close cart sidebar
     */
    closeSidebar() {
        const overlay = document.querySelector('.cart-overlay');
        const sidebar = document.querySelector('.cart-sidebar');

        if (overlay) overlay.classList.remove('open');
        if (sidebar) sidebar.classList.remove('open');
        document.body.style.overflow = '';
    },

    /**
     * Animate cart button on add
     */
    animateCartButton() {
        const cartBtn = document.querySelector('.cart-btn');
        if (cartBtn) {
            cartBtn.classList.add('animate');
            setTimeout(() => cartBtn.classList.remove('animate'), 300);
        }
    },

    /**
     * Get cart data for checkout
     */
    getCheckoutData() {
        return {
            items: this.items,
            subtotal: this.getSubtotal(),
            itemCount: this.getTotalItems()
        };
    }
};

// Initialize cart when DOM is ready
document.addEventListener('DOMContentLoaded', () => Cart.init());
