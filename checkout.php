<?php
/**
 * FoodFlow - Checkout Page
 * Multi-payment support: Stripe, PayPal, Venmo, CashApp
 */

require_once __DIR__ . '/includes/functions.php';

$storeName = getSetting('store_name', 'FoodFlow');
$taxRate = getSetting('tax_rate', 8.25);
$deliveryFee = getSetting('delivery_fee', 4.99);
$minOrder = getSetting('min_order_amount', 15);
$freeDeliveryMin = getSetting('free_delivery_min', 35);

// Payment settings
$stripeEnabled = getSetting('stripe_enabled', true);
$stripePublicKey = getSetting('stripe_public_key', '');
$paypalEnabled = getSetting('paypal_enabled', true);
$paypalClientId = getSetting('paypal_client_id', '');
$venmoEnabled = getSetting('venmo_enabled', true);
$cashappEnabled = getSetting('cashapp_enabled', false);

// Store hours check
$isOpen = isStoreOpen();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout -
        <?= htmlspecialchars($storeName) ?>
    </title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Karla:wght@400;500;600;700&family=Playfair+Display+SC:wght@400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .checkout-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: var(--space-xl) var(--space-md);
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--space-xl);
        }

        @media (min-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr 380px;
            }
        }

        .checkout-form {
            background: white;
            border-radius: var(--radius-xl);
            padding: var(--space-xl);
            box-shadow: var(--shadow-md);
        }

        .checkout-section {
            margin-bottom: var(--space-xl);
            padding-bottom: var(--space-xl);
            border-bottom: 1px solid #f1f1f1;
        }

        .checkout-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .checkout-section-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: var(--space-md);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }

        .checkout-section-title .step {
            width: 28px;
            height: 28px;
            background: var(--color-primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-md);
        }

        @media (max-width: 640px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .order-type-toggle {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-sm);
            margin-bottom: var(--space-lg);
        }

        .order-type-btn {
            padding: var(--space-md);
            border: 2px solid #e5e5e5;
            border-radius: var(--radius-lg);
            background: white;
            cursor: pointer;
            text-align: center;
            transition: all var(--transition-fast);
        }

        .order-type-btn:hover {
            border-color: var(--color-secondary);
        }

        .order-type-btn.active {
            border-color: var(--color-primary);
            background: var(--color-bg);
        }

        .order-type-btn svg {
            display: block;
            margin: 0 auto var(--space-xs);
        }

        .tip-options {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: var(--space-sm);
        }

        .tip-btn {
            padding: var(--space-sm) var(--space-md);
            border: 2px solid #e5e5e5;
            border-radius: var(--radius-md);
            background: white;
            cursor: pointer;
            text-align: center;
            font-weight: 500;
            transition: all var(--transition-fast);
        }

        .tip-btn:hover {
            border-color: var(--color-cta);
        }

        .tip-btn.active {
            border-color: var(--color-cta);
            background: var(--color-bg-warm);
            color: var(--color-cta-dark);
        }

        .payment-methods {
            display: grid;
            gap: var(--space-sm);
        }

        .payment-method {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            padding: var(--space-md);
            border: 2px solid #e5e5e5;
            border-radius: var(--radius-lg);
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .payment-method:hover {
            border-color: var(--color-secondary);
        }

        .payment-method.active {
            border-color: var(--color-primary);
            background: var(--color-bg);
        }

        .payment-method input[type="radio"] {
            display: none;
        }

        .payment-method-icon {
            width: 48px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .payment-method-name {
            font-weight: 500;
        }

        .order-summary {
            background: white;
            border-radius: var(--radius-xl);
            padding: var(--space-xl);
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 80px;
            height: fit-content;
        }

        .order-summary-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: var(--space-lg);
        }

        .order-items {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: var(--space-lg);
        }

        .order-item {
            display: flex;
            gap: var(--space-md);
            margin-bottom: var(--space-md);
            padding-bottom: var(--space-md);
            border-bottom: 1px solid #f1f1f1;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item-image {
            width: 60px;
            height: 60px;
            border-radius: var(--radius-md);
            object-fit: cover;
            background: var(--color-bg);
        }

        .order-item-info {
            flex: 1;
        }

        .order-item-name {
            font-weight: 500;
            font-size: 0.9375rem;
        }

        .order-item-qty {
            color: var(--color-text-muted);
            font-size: 0.875rem;
        }

        .order-item-price {
            font-weight: 600;
            color: var(--color-primary);
        }

        .order-totals {
            border-top: 1px solid #f1f1f1;
            padding-top: var(--space-md);
        }

        .order-total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: var(--space-sm);
        }

        .order-total-row.total {
            font-size: 1.25rem;
            font-weight: 700;
            margin-top: var(--space-md);
            padding-top: var(--space-md);
            border-top: 2px solid var(--color-text);
        }

        .empty-cart-message {
            text-align: center;
            padding: var(--space-2xl);
        }

        .empty-cart-message svg {
            margin-bottom: var(--space-md);
            color: #ccc;
        }

        .store-closed-banner {
            background: var(--color-warning);
            color: var(--color-text);
            padding: var(--space-md);
            border-radius: var(--radius-lg);
            margin-bottom: var(--space-lg);
            text-align: center;
        }
    </style>
</head>

<body style="padding-top: 64px; background: var(--color-bg);">
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-logo">
                <span>
                    <?= htmlspecialchars($storeName) ?>
                </span>
            </a>

            <div class="navbar-actions">
                <a href="menu.php" class="btn btn-outline btn-sm">Back to Menu</a>
            </div>
        </div>
    </nav>

    <main>
        <div class="checkout-container">
            <!-- Checkout Form -->
            <div class="checkout-form">
                <h1 style="margin-bottom: var(--space-xl);">Checkout</h1>

                <?php if (!$isOpen): ?>
                    <div class="store-closed-banner">
                        ⚠️ We're currently closed. You can still place an order for later!
                    </div>
                <?php endif; ?>

                <form id="checkoutForm" method="POST" action="api/order.php">
                    <!-- Order Type -->
                    <div class="checkout-section">
                        <h3 class="checkout-section-title">
                            <span class="step">1</span>
                            Order Type
                        </h3>
                        <div class="order-type-toggle">
                            <label class="order-type-btn active" data-type="delivery">
                                <input type="radio" name="order_type" value="delivery" checked>
                                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                </svg>
                                <div>Delivery</div>
                            </label>
                            <label class="order-type-btn" data-type="pickup">
                                <input type="radio" name="order_type" value="pickup">
                                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <div>Pickup</div>
                            </label>
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div class="checkout-section">
                        <h3 class="checkout-section-title">
                            <span class="step">2</span>
                            Contact Information
                        </h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="customer_name" class="form-input" required
                                    placeholder="John Doe">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone Number *</label>
                                <input type="tel" name="customer_phone" class="form-input" required
                                    placeholder="(555) 123-4567" inputmode="tel">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email (for receipt)</label>
                            <input type="email" name="customer_email" class="form-input" placeholder="john@example.com"
                                inputmode="email">
                        </div>
                    </div>

                    <!-- Delivery Address (shown for delivery only) -->
                    <div class="checkout-section" id="deliverySection">
                        <h3 class="checkout-section-title">
                            <span class="step">3</span>
                            Delivery Address
                        </h3>
                        <div class="form-group">
                            <label class="form-label">Street Address *</label>
                            <input type="text" name="delivery_address" class="form-input"
                                placeholder="123 Main Street, Apt 4B">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">City *</label>
                                <input type="text" name="delivery_city" class="form-input" placeholder="New York">
                            </div>
                            <div class="form-group">
                                <label class="form-label">ZIP Code *</label>
                                <input type="text" name="delivery_zip" class="form-input" placeholder="10001"
                                    inputmode="numeric">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Delivery Instructions</label>
                            <textarea name="delivery_instructions" class="form-textarea" rows="2"
                                placeholder="Gate code, building entrance, etc."></textarea>
                        </div>
                    </div>

                    <!-- Schedule -->
                    <div class="checkout-section">
                        <h3 class="checkout-section-title">
                            <span class="step" id="scheduleStep">4</span>
                            When do you want it?
                        </h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Date</label>
                                <select name="schedule_date" class="form-select">
                                    <option value="today">Today (ASAP)</option>
                                    <option value="<?= date('Y-m-d', strtotime('+1 day')) ?>">Tomorrow</option>
                                    <option value="<?= date('Y-m-d', strtotime('+2 days')) ?>">
                                        <?= date('l, M j', strtotime('+2 days')) ?>
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Time</label>
                                <select name="schedule_time" class="form-select">
                                    <option value="asap">As Soon As Possible</option>
                                    <option value="11:00">11:00 AM</option>
                                    <option value="11:30">11:30 AM</option>
                                    <option value="12:00">12:00 PM</option>
                                    <option value="12:30">12:30 PM</option>
                                    <option value="13:00">1:00 PM</option>
                                    <option value="17:00">5:00 PM</option>
                                    <option value="17:30">5:30 PM</option>
                                    <option value="18:00">6:00 PM</option>
                                    <option value="18:30">6:30 PM</option>
                                    <option value="19:00">7:00 PM</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Tip -->
                    <div class="checkout-section">
                        <h3 class="checkout-section-title">
                            <span class="step" id="tipStep">5</span>
                            Add a Tip
                        </h3>
                        <p
                            style="color: var(--color-text-muted); margin-bottom: var(--space-md); font-size: 0.9375rem;">
                            100% of your tip goes to the team</p>
                        <div class="tip-options">
                            <button type="button" class="tip-btn" data-tip="15">15%</button>
                            <button type="button" class="tip-btn active" data-tip="18">18%</button>
                            <button type="button" class="tip-btn" data-tip="20">20%</button>
                            <button type="button" class="tip-btn" data-tip="custom">Custom</button>
                        </div>
                        <input type="hidden" name="tip_percentage" id="tipPercentage" value="18">
                        <div id="customTipInput" class="form-group" style="display: none; margin-top: var(--space-md);">
                            <label class="form-label">Custom Tip Amount ($)</label>
                            <input type="number" name="custom_tip" class="form-input" min="0" step="0.01"
                                placeholder="0.00">
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="checkout-section">
                        <h3 class="checkout-section-title">
                            <span class="step" id="paymentStep">6</span>
                            Payment Method
                        </h3>
                        <div class="payment-methods">
                            <?php if ($stripeEnabled): ?>
                                <label class="payment-method active">
                                    <input type="radio" name="payment_method" value="stripe" checked>
                                    <div class="payment-method-icon">💳</div>
                                    <div>
                                        <div class="payment-method-name">Credit/Debit Card</div>
                                        <div style="font-size: 0.75rem; color: #888;">Visa, Mastercard, Amex</div>
                                    </div>
                                </label>
                            <?php endif; ?>

                            <?php if ($paypalEnabled): ?>
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="paypal">
                                    <div class="payment-method-icon" style="color: #003087;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                            <path
                                                d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944 3.384a.77.77 0 0 1 .76-.646h6.4c2.133 0 3.67.43 4.57 1.28.86.81 1.2 2.03 1.01 3.62-.02.18-.05.37-.09.56-.43 2.27-1.57 3.85-3.37 4.69-1.71.8-3.88.8-5.51.8H7.63c-.35 0-.66.25-.72.6l-1.3 6.78a.508.508 0 0 1-.53.45z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="payment-method-name">PayPal</div>
                                        <div style="font-size: 0.75rem; color: #888;">Pay with your PayPal account</div>
                                    </div>
                                </label>
                            <?php endif; ?>

                            <?php if ($venmoEnabled): ?>
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="venmo">
                                    <div class="payment-method-icon" style="color: #3D95CE;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                            <path
                                                d="M19.25 2c.62.87 1 1.77 1 2.91 0 3.62-3.1 8.33-5.61 11.64H8.53L6 2.88l5.37-.51.91 7.31c1.15-1.87 2.57-4.81 2.57-6.83 0-1.07-.18-1.8-.44-2.38L19.25 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="payment-method-name">Venmo</div>
                                        <div style="font-size: 0.75rem; color: #888;">Fast and easy</div>
                                    </div>
                                </label>
                            <?php endif; ?>

                            <?php if ($cashappEnabled): ?>
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="cashapp">
                                    <div class="payment-method-icon" style="color: #00C853;">$</div>
                                    <div>
                                        <div class="payment-method-name">Cash App</div>
                                        <div style="font-size: 0.75rem; color: #888;">Pay with Cash App</div>
                                    </div>
                                </label>
                            <?php endif; ?>

                            <!-- Cash Payment - Always available for pickup -->
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="cash">
                                <div class="payment-method-icon" style="color: #4CAF50;">💵</div>
                                <div>
                                    <div class="payment-method-name">Cash</div>
                                    <div style="font-size: 0.75rem; color: #888;">Pay on pickup/delivery</div>
                                </div>
                            </label>
                        </div>

                        <!-- Card input for Stripe -->
                        <div id="cardElement"
                            style="margin-top: var(--space-lg); padding: var(--space-md); border: 2px solid #e5e5e5; border-radius: var(--radius-lg);">
                            <!-- Stripe Elements will be mounted here -->
                            <p style="color: #888; text-align: center; font-size: 0.875rem;">Card payment form will load
                                here</p>
                        </div>
                    </div>

                    <!-- Special Instructions -->
                    <div class="checkout-section">
                        <h3 class="checkout-section-title">
                            <span class="step">7</span>
                            Special Instructions
                        </h3>
                        <div class="form-group" style="margin-bottom: 0;">
                            <textarea name="special_instructions" class="form-textarea" rows="2"
                                placeholder="Any allergies, preferences, or special requests?"></textarea>
                        </div>
                    </div>

                    <!-- Hidden cart data -->
                    <input type="hidden" name="cart_data" id="cartData">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                </form>
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <h2 class="order-summary-title">Order Summary</h2>

                <div id="orderItems" class="order-items">
                    <!-- Items will be rendered here by JS -->
                </div>

                <div class="order-totals">
                    <div class="order-total-row">
                        <span>Subtotal</span>
                        <span id="summarySubtotal">$0.00</span>
                    </div>
                    <div class="order-total-row" id="deliveryFeeRow">
                        <span>Delivery Fee</span>
                        <span id="summaryDelivery">$
                            <?= number_format($deliveryFee, 2) ?>
                        </span>
                    </div>
                    <div class="order-total-row">
                        <span>Tax (
                            <?= $taxRate ?>%)
                        </span>
                        <span id="summaryTax">$0.00</span>
                    </div>
                    <div class="order-total-row">
                        <span>Tip</span>
                        <span id="summaryTip">$0.00</span>
                    </div>
                    <div class="order-total-row total">
                        <span>Total</span>
                        <span id="summaryTotal">$0.00</span>
                    </div>
                </div>

                <button type="submit" form="checkoutForm" class="btn btn-primary btn-block btn-lg" id="placeOrderBtn"
                    style="margin-top: var(--space-lg);">
                    Place Order
                </button>

                <p style="text-align: center; margin-top: var(--space-md); font-size: 0.75rem; color: #888;">
                    By placing your order, you agree to our Terms of Service
                </p>
            </div>
        </div>
    </main>

    <script src="assets/js/app.js"></script>
    <script src="assets/js/cart.js"></script>
    <script>
        const TAX_RATE = <?= $taxRate ?>;
        const DELIVERY_FEE = <?= $deliveryFee ?>;
        const FREE_DELIVERY_MIN = <?= $freeDeliveryMin ?>;

        let tipPercentage = 18;
        let customTip = 0;
        let isDelivery = true;

        document.addEventListener('DOMContentLoaded', function () {
            renderOrderSummary();

            // Order type toggle
            document.querySelectorAll('.order-type-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.order-type-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    isDelivery = this.dataset.type === 'delivery';

                    // Update step numbers
                    const deliverySection = document.getElementById('deliverySection');
                    deliverySection.style.display = isDelivery ? 'block' : 'none';

                    updateStepNumbers();
                    renderOrderSummary();
                });
            });

            // Tip buttons
            document.querySelectorAll('.tip-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.tip-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    const customInput = document.getElementById('customTipInput');
                    if (this.dataset.tip === 'custom') {
                        customInput.style.display = 'block';
                        tipPercentage = 0;
                    } else {
                        customInput.style.display = 'none';
                        tipPercentage = parseInt(this.dataset.tip);
                        customTip = 0;
                    }

                    document.getElementById('tipPercentage').value = tipPercentage;
                    renderOrderSummary();
                });
            });

            // Custom tip input
            document.querySelector('input[name="custom_tip"]')?.addEventListener('input', function () {
                customTip = parseFloat(this.value) || 0;
                renderOrderSummary();
            });

            // Payment method toggle
            document.querySelectorAll('.payment-method').forEach(method => {
                method.addEventListener('click', function () {
                    document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('active'));
                    this.classList.add('active');

                    const cardElement = document.getElementById('cardElement');
                    cardElement.style.display = this.querySelector('input').value === 'stripe' ? 'block' : 'none';
                });
            });

            // Form submission
            document.getElementById('checkoutForm').addEventListener('submit', async function (e) {
                e.preventDefault();

                if (Cart.items.length === 0) {
                    showToast('Your cart is empty!', 'error');
                    return;
                }

                // Set cart data
                document.getElementById('cartData').value = JSON.stringify(Cart.getCheckoutData());

                // Show loading
                const btn = document.getElementById('placeOrderBtn');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner" style="width:20px;height:20px;border-width:2px;"></span> Processing...';

                // Submit via API
                const formData = new FormData(this);
                const data = {
                    customer_name: formData.get('customer_name'),
                    customer_email: formData.get('customer_email'),
                    customer_phone: formData.get('customer_phone'),
                    order_type: isDelivery ? 'delivery' : 'pickup',
                    delivery_address: formData.get('delivery_address') || '',
                    delivery_city: formData.get('delivery_city') || '',
                    delivery_zip: formData.get('delivery_zip') || '',
                    delivery_instructions: formData.get('delivery_instructions') || '',
                    payment_method: formData.get('payment_method') || 'card',
                    tip_percentage: tipPercentage,
                    custom_tip: customTip,
                    special_instructions: formData.get('special_instructions') || '',
                    cart_data: document.getElementById('cartData').value,
                    csrf_token: formData.get('csrf_token')
                };

                try {
                    const response = await fetch('api/order.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (result.success) {
                        showToast('Order placed successfully!', 'success');
                        Cart.clear();
                        window.location.href = 'order-confirmation.php?order=' + result.order_number;
                    } else {
                        showToast('Error: ' + result.error, 'error');
                        btn.disabled = false;
                        btn.innerHTML = 'Place Order';
                    }
                } catch (err) {
                    showToast('Error: ' + err.message, 'error');
                    btn.disabled = false;
                    btn.innerHTML = 'Place Order';
                }
            });
        });

        function updateStepNumbers() {
            const steps = ['scheduleStep', 'tipStep', 'paymentStep'];
            const offset = isDelivery ? 4 : 3;

            steps.forEach((id, index) => {
                const el = document.getElementById(id);
                if (el) el.textContent = offset + index;
            });
        }

        function renderOrderSummary() {
            const itemsContainer = document.getElementById('orderItems');
            const items = Cart.items;

            if (items.length === 0) {
                itemsContainer.innerHTML = `
                    <div class="empty-cart-message">
                        <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <p style="color: #888;">Your cart is empty</p>
                        <a href="menu.php" class="btn btn-outline btn-sm" style="margin-top: var(--space-md);">Add Items</a>
                    </div>
                `;
                document.getElementById('placeOrderBtn').disabled = true;
                return;
            }

            document.getElementById('placeOrderBtn').disabled = false;

            itemsContainer.innerHTML = items.map(item => `
                <div class="order-item">
                    ${item.image ? `<img src="${item.image}" alt="${item.name}" class="order-item-image">` : '<div class="order-item-image"></div>'}
                    <div class="order-item-info">
                        <div class="order-item-name">${item.name}</div>
                        <div class="order-item-qty">Qty: ${item.quantity}</div>
                    </div>
                    <div class="order-item-price">${formatPrice(item.price * item.quantity)}</div>
                </div>
            `).join('');

            // Calculate totals
            const subtotal = Cart.getSubtotal();
            const deliveryFee = isDelivery ? (subtotal >= FREE_DELIVERY_MIN ? 0 : DELIVERY_FEE) : 0;
            const tax = subtotal * (TAX_RATE / 100);
            const tip = tipPercentage > 0 ? subtotal * (tipPercentage / 100) : customTip;
            const total = subtotal + deliveryFee + tax + tip;

            document.getElementById('summarySubtotal').textContent = formatPrice(subtotal);
            document.getElementById('summaryDelivery').textContent = deliveryFee === 0 ? 'FREE' : formatPrice(deliveryFee);
            document.getElementById('deliveryFeeRow').style.display = isDelivery ? 'flex' : 'none';
            document.getElementById('summaryTax').textContent = formatPrice(tax);
            document.getElementById('summaryTip').textContent = formatPrice(tip);
            document.getElementById('summaryTotal').textContent = formatPrice(total);
        }
    </script>
</body>

</html>