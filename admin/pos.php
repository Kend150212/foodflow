<?php
/**
 * FoodFlow - POS System
 * Quick order taking and management for in-store
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAuth();

$categories = getCategories();
$menuItems = getAvailableMenuItems(); // This includes schedule info and availability status

$storeName = getSetting('store_name', 'FoodFlow');
$taxRate = getSetting('tax_rate', 8.25);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS -
        <?= htmlspecialchars($storeName) ?>
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Karla:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Karla', sans-serif;
        }

        .category-btn.active {
            background: #DC2626;
            color: white;
        }

        .menu-item:active {
            transform: scale(0.98);
        }

        .cart-item {
            animation: slideIn 0.2s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
        }

        .pulse {
            animation: pulse 0.3s ease;
        }

        @keyframes pulse {
            50% {
                transform: scale(1.05);
            }
        }
    </style>
</head>

<body class="bg-gray-900 text-white h-screen overflow-hidden">
    <div class="flex h-full">
        <!-- Left Side - Menu -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <header class="bg-gray-800 px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="index.php" class="text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center">
                        <span class="font-bold text-sm">POS</span>
                    </div>
                    <span class="font-bold">
                        <?= htmlspecialchars($storeName) ?>
                    </span>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-gray-400 text-sm" id="currentTime"></span>
                    <a href="kitchen.php"
                        class="bg-orange-600 hover:bg-orange-700 px-3 py-1.5 rounded-lg text-sm font-medium">
                        👨‍🍳 Kitchen
                    </a>
                </div>
            </header>

            <!-- Categories -->
            <div class="bg-gray-800 px-4 py-2 flex gap-2 overflow-x-auto">
                <button
                    class="category-btn active px-4 py-2 bg-gray-700 rounded-lg font-medium whitespace-nowrap transition"
                    data-category="all">
                    All Items
                </button>
                <?php foreach ($categories as $cat): ?>
                    <button
                        class="category-btn px-4 py-2 bg-gray-700 rounded-lg font-medium whitespace-nowrap transition hover:bg-gray-600"
                        data-category="<?= $cat['id'] ?>">
                        <?= htmlspecialchars($cat['name']) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Menu Grid -->
            <div class="flex-1 overflow-y-auto p-4">
                <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3" id="menuGrid">
                    <?php foreach ($menuItems as $item): ?>
                        <button class="menu-item bg-gray-800 rounded-xl p-3 text-left hover:bg-gray-700 transition relative"
                            data-id="<?= $item['id'] ?? 0 ?>" data-name="<?= htmlspecialchars($item['name'] ?? '') ?>"
                            data-price="<?= $item['price'] ?? 0 ?>" data-category="<?= $item['category_id'] ?? 0 ?>">
                            <?php if (!empty($item['image'])): ?>
                                <img src="../<?= htmlspecialchars($item['image']) ?>" alt=""
                                    class="w-full h-20 object-cover rounded-lg mb-2">
                            <?php else: ?>
                                <div class="w-full h-20 bg-gray-700 rounded-lg mb-2 flex items-center justify-center">
                                    <span class="text-2xl">🍽️</span>
                                </div>
                            <?php endif; ?>
                            <?php if (empty($item['is_available_now']) && !empty($item['schedule_info'])): ?>
                                <span
                                    class="absolute top-1 right-1 bg-yellow-500 text-black text-xs px-1.5 py-0.5 rounded font-medium"
                                    title="<?= $item['schedule_info']['days'] ?? 'Every day' ?>">
                                    ⏰ <?= $item['schedule_info']['time'] ?? '' ?>
                                </span>
                            <?php endif; ?>
                            <div class="font-medium text-sm truncate">
                                <?= htmlspecialchars($item['name'] ?? '') ?>
                            </div>
                            <div class="text-red-400 font-bold">$
                                <?= number_format($item['price'] ?? 0, 2) ?>
                            </div>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Right Side - Cart & Checkout -->
        <div class="w-96 bg-gray-800 flex flex-col">
            <!-- Order Type Toggle -->
            <div class="p-4 border-b border-gray-700">
                <div class="flex bg-gray-700 rounded-lg p-1">
                    <button id="btnDineIn" class="flex-1 py-2 rounded-lg font-medium transition bg-red-600">
                        🍽️ Dine In
                    </button>
                    <button id="btnTakeout" class="flex-1 py-2 rounded-lg font-medium transition">
                        📦 Takeout
                    </button>
                    <button id="btnDelivery" class="flex-1 py-2 rounded-lg font-medium transition">
                        🚗 Delivery
                    </button>
                </div>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-4" id="cartItems">
                <div id="emptyCart" class="text-center text-gray-500 py-10">
                    <div class="text-4xl mb-2">🛒</div>
                    <div>Tap items to add</div>
                </div>
            </div>

            <!-- Customer Info (for delivery) -->
            <div id="customerInfo" class="hidden p-4 border-t border-gray-700 space-y-2">
                <input type="text" id="customerName" placeholder="Customer Name"
                    class="w-full px-3 py-2 bg-gray-700 rounded-lg text-white placeholder-gray-400">
                <input type="tel" id="customerPhone" placeholder="Phone Number"
                    class="w-full px-3 py-2 bg-gray-700 rounded-lg text-white placeholder-gray-400">
                <input type="text" id="customerAddress" placeholder="Delivery Address"
                    class="w-full px-3 py-2 bg-gray-700 rounded-lg text-white placeholder-gray-400 hidden">
            </div>

            <!-- Totals & Actions -->
            <div class="p-4 border-t border-gray-700 space-y-3">
                <div class="flex justify-between text-gray-400">
                    <span>Subtotal</span>
                    <span id="subtotal">$0.00</span>
                </div>
                <div class="flex justify-between text-gray-400">
                    <span>Tax (
                        <?= $taxRate ?>%)
                    </span>
                    <span id="tax">$0.00</span>
                </div>
                <div class="flex justify-between text-xl font-bold">
                    <span>Total</span>
                    <span id="total" class="text-green-400">$0.00</span>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-2">
                    <button id="btnClear" class="py-3 bg-gray-700 hover:bg-gray-600 rounded-lg font-medium transition">
                        Clear
                    </button>
                    <button id="btnPay" class="py-3 bg-green-600 hover:bg-green-700 rounded-lg font-medium transition">
                        💵 Pay & Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 hidden">
        <div class="bg-gray-800 rounded-2xl p-6 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Payment</h2>

            <div class="text-center mb-6">
                <div class="text-4xl font-bold text-green-400" id="modalTotal">$0.00</div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-6">
                <button class="payment-btn py-4 bg-gray-700 hover:bg-gray-600 rounded-xl font-medium transition"
                    data-method="cash">
                    💵 Cash
                </button>
                <button class="payment-btn py-4 bg-gray-700 hover:bg-gray-600 rounded-xl font-medium transition"
                    data-method="card">
                    💳 Card
                </button>
                <button class="payment-btn py-4 bg-gray-700 hover:bg-gray-600 rounded-xl font-medium transition"
                    data-method="venmo">
                    📱 Venmo
                </button>
                <button class="payment-btn py-4 bg-gray-700 hover:bg-gray-600 rounded-xl font-medium transition"
                    data-method="cashapp">
                    💚 CashApp
                </button>
            </div>

            <div id="cashInput" class="hidden mb-4">
                <label class="block text-sm text-gray-400 mb-1">Cash Received</label>
                <input type="number" id="cashReceived" step="0.01"
                    class="w-full px-4 py-3 bg-gray-700 rounded-lg text-2xl text-center font-bold">

                <!-- Quick Cash Buttons -->
                <div class="grid grid-cols-6 gap-2 mt-3">
                    <button type="button" class="quick-cash py-2 bg-gray-700 hover:bg-gray-600 rounded-lg font-medium"
                        data-amount="5">$5</button>
                    <button type="button" class="quick-cash py-2 bg-gray-700 hover:bg-gray-600 rounded-lg font-medium"
                        data-amount="10">$10</button>
                    <button type="button" class="quick-cash py-2 bg-gray-700 hover:bg-gray-600 rounded-lg font-medium"
                        data-amount="20">$20</button>
                    <button type="button" class="quick-cash py-2 bg-gray-700 hover:bg-gray-600 rounded-lg font-medium"
                        data-amount="50">$50</button>
                    <button type="button" class="quick-cash py-2 bg-gray-700 hover:bg-gray-600 rounded-lg font-medium"
                        data-amount="100">$100</button>
                    <button type="button"
                        class="quick-cash py-2 bg-green-600 hover:bg-green-700 rounded-lg font-medium text-sm"
                        data-amount="exact">Exact</button>
                </div>

                <div class="mt-3 text-center text-lg">
                    Change: <span id="changeAmount" class="text-green-400 font-bold text-xl">$0.00</span>
                </div>
            </div>

            <div class="flex gap-3">
                <button id="btnCancelPayment"
                    class="flex-1 py-3 bg-gray-700 hover:bg-gray-600 rounded-lg font-medium transition">
                    Cancel
                </button>
                <button id="btnConfirmPayment"
                    class="flex-1 py-3 bg-green-600 hover:bg-green-700 rounded-lg font-medium transition">
                    ✓ Complete Order
                </button>
            </div>
        </div>
    </div>

    <!-- Audio for new order notification -->
    <audio id="notifySound" preload="auto">
        <source
            src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdH2Onp+YgG1lb3yLmZ+YiHdoaXiCipOUjYJ1bmhxfouWl4+FdWxqdHuIkpSNhHdyb3J6hI+TkYl7c29tc3yGjo+MhXlybHF3g42QjYR6dHBxdoKLj42Fenl0cnR7g4qMiYF5dXJzdnuEioyJgXl0cXR4foaKioN9eHRzdnqBho2LhH15dXN3fIOJjYqDfHh1c3Z9g4qNi4R9eHR0dn6EioyKg316dXN3fYOKjYqDfXl1c3d9g4qMioN9eXRzd32DioqKg315dXN3fYOKi4qDfXl1c3d9hIqLioN9eXV0d32EiouJg315dHN3fYOKi4mDfHl1c3h9hIqLiYN8eHVzeH2EiYqJg3x5dXN4fYSJiomDfHl1c3h9hImKiYN8eXVzeH2EiYqJg3x5dHN4fYSJiomDfHh1c3h9hImKiIN8eHV0eH2EiYqIg3x4dXR4fYSJioiDfHh1dHh9hImKiIN8eXV0eH2EiYqIg3x5dXR4fYSJioiDfHl1dHh9hImKiIN8eXV0eH2EiYqIg3x5dXR4fYSIiYeDfHl1dHl9hIiJh4N8eXV0eX2EiImHg3x5dXR5fYSIiYeDfHl1dHl9hIiJh4N8eXV0eX2EiImHg3x5dXR5fYSIiYeDfHl1dHl9hIiJh4N8eXV0eX2EiImHg3x5dXR5fYSIiYeDfHl1dHl9hA=="
            type="audio/wav">
    </audio>

    <script>
        const TAX_RATE = <?= $taxRate ?>;
        let cart = [];
        let orderType = 'dine_in';
        let selectedPayment = '';

        // Update time
        function updateTime() {
            const now = new Date();
            document.getElementById('currentTime').textContent = now.toLocaleTimeString();
        }
        setInterval(updateTime, 1000);
        updateTime();

        // Category filter
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const category = btn.dataset.category;
                document.querySelectorAll('.menu-item').forEach(item => {
                    if (category === 'all' || item.dataset.category === category) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });

        // Add to cart
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', () => {
                const id = item.dataset.id;
                const name = item.dataset.name;
                const price = parseFloat(item.dataset.price);

                const existing = cart.find(i => i.id === id);
                if (existing) {
                    existing.qty++;
                } else {
                    cart.push({ id, name, price, qty: 1 });
                }

                item.classList.add('pulse');
                setTimeout(() => item.classList.remove('pulse'), 300);

                renderCart();
            });
        });

        // Render cart
        function renderCart() {
            const container = document.getElementById('cartItems');
            const empty = document.getElementById('emptyCart');

            if (cart.length === 0) {
                empty.classList.remove('hidden');
                container.innerHTML = '';
                container.appendChild(empty);
                updateTotals();
                return;
            }

            empty.classList.add('hidden');
            container.innerHTML = cart.map((item, idx) => `
                <div class="cart-item flex items-center gap-3 py-3 border-b border-gray-700">
                    <div class="flex-1">
                        <div class="font-medium">${item.name}</div>
                        <div class="text-gray-400 text-sm">$${item.price.toFixed(2)} each</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="updateQty(${idx}, -1)" class="w-8 h-8 bg-gray-700 rounded-lg hover:bg-gray-600">-</button>
                        <span class="w-8 text-center font-bold">${item.qty}</span>
                        <button onclick="updateQty(${idx}, 1)" class="w-8 h-8 bg-gray-700 rounded-lg hover:bg-gray-600">+</button>
                    </div>
                    <div class="font-bold min-w-[60px] text-right">$${(item.price * item.qty).toFixed(2)}</div>
                </div>
            `).join('');

            updateTotals();
        }

        function updateQty(idx, delta) {
            cart[idx].qty += delta;
            if (cart[idx].qty <= 0) {
                cart.splice(idx, 1);
            }
            renderCart();
        }

        function updateTotals() {
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
            const tax = subtotal * (TAX_RATE / 100);
            const total = subtotal + tax;

            document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
            document.getElementById('tax').textContent = '$' + tax.toFixed(2);
            document.getElementById('total').textContent = '$' + total.toFixed(2);
            document.getElementById('modalTotal').textContent = '$' + total.toFixed(2);
        }

        // Order type
        ['DineIn', 'Takeout', 'Delivery'].forEach(type => {
            const btn = document.getElementById('btn' + type);
            if (btn) {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('#btnDineIn, #btnTakeout, #btnDelivery').forEach(b => b && b.classList.remove('bg-red-600'));
                    this.classList.add('bg-red-600');
                    orderType = type.toLowerCase();

                    const customerInfo = document.getElementById('customerInfo');
                    const addressField = document.getElementById('customerAddress');

                    if (type === 'DineIn') {
                        customerInfo && customerInfo.classList.add('hidden');
                    } else {
                        customerInfo && customerInfo.classList.remove('hidden');
                        addressField && addressField.classList.toggle('hidden', type !== 'Delivery');
                    }
                });
            }
        });

        // Clear cart
        document.getElementById('btnClear').addEventListener('click', () => {
            if (confirm('Clear entire order?')) {
                cart = [];
                renderCart();
            }
        });

        // Pay
        document.getElementById('btnPay').addEventListener('click', () => {
            if (cart.length === 0) {
                alert('Add items to order first!');
                return;
            }
            document.getElementById('paymentModal').classList.remove('hidden');
        });

        document.getElementById('btnCancelPayment').addEventListener('click', () => {
            document.getElementById('paymentModal').classList.add('hidden');
        });

        // Payment method selection
        document.querySelectorAll('.payment-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.payment-btn').forEach(b => b.classList.remove('bg-green-600'));
                this.classList.add('bg-green-600');
                selectedPayment = this.dataset.method;

                document.getElementById('cashInput').classList.toggle('hidden', selectedPayment !== 'cash');
            });
        });

        // Cash calculation
        document.getElementById('cashReceived').addEventListener('input', function () {
            calculateChange();
        });

        function calculateChange() {
            const received = parseFloat(document.getElementById('cashReceived').value) || 0;
            const total = cart.reduce((sum, item) => sum + (item.price * item.qty), 0) * (1 + TAX_RATE / 100);
            const change = Math.max(0, received - total);
            document.getElementById('changeAmount').textContent = '$' + change.toFixed(2);
        }

        function getOrderTotal() {
            return cart.reduce((sum, item) => sum + (item.price * item.qty), 0) * (1 + TAX_RATE / 100);
        }

        // Quick cash buttons
        document.querySelectorAll('.quick-cash').forEach(btn => {
            btn.addEventListener('click', function () {
                const amount = this.dataset.amount;
                const input = document.getElementById('cashReceived');

                if (amount === 'exact') {
                    input.value = getOrderTotal().toFixed(2);
                } else {
                    input.value = parseFloat(amount);
                }
                calculateChange();
            });
        });

        // Complete order
        document.getElementById('btnConfirmPayment').addEventListener('click', async () => {
            if (!selectedPayment) {
                alert('Select a payment method');
                return;
            }

            const customerName = document.getElementById('customerName').value || 'Walk-in';
            const customerPhone = document.getElementById('customerPhone').value || '';
            const customerAddress = document.getElementById('customerAddress').value || '';

            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
            const tax = subtotal * (TAX_RATE / 100);
            const total = subtotal + tax;

            // Create order via API
            try {
                const response = await fetch('../api/order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        customer_name: customerName,
                        customer_phone: customerPhone,
                        customer_email: '',
                        order_type: orderType === 'dine_in' ? 'pickup' : orderType,
                        delivery_address: customerAddress,
                        payment_method: selectedPayment,
                        cart_data: JSON.stringify({ items: cart.map(i => ({ id: i.id, name: i.name, price: i.price, quantity: i.qty })) })
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Open print window
                    window.open('print.php?id=' + data.order_id + '&autoprint=1', '_blank', 'width=400,height=600');

                    // Reset
                    cart = [];
                    renderCart();
                    document.getElementById('customerName').value = '';
                    document.getElementById('customerPhone').value = '';
                    document.getElementById('customerAddress').value = '';
                    document.getElementById('paymentModal').classList.add('hidden');
                    selectedPayment = '';
                    document.querySelectorAll('.payment-btn').forEach(b => b.classList.remove('bg-green-600'));

                    alert('Order #' + data.order_number + ' created!');
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (err) {
                alert('Error: ' + err.message);
            }
        });
    </script>
</body>

</html>