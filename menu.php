<?php
/**
 * FoodFlow - Menu Page
 */

require_once __DIR__ . '/includes/functions.php';

$storeName = getSetting('store_name', 'FoodFlow');
$categories = getCategories();
$selectedCategory = isset($_GET['category']) ? (int)$_GET['category'] : null;
$menuItems = getAvailableMenuItems($selectedCategory);

// Group items by category if no filter
$itemsByCategory = [];
if (!$selectedCategory) {
    foreach ($menuItems as $item) {
        $catId = $item['category_id'];
        if (!isset($itemsByCategory[$catId])) {
            $itemsByCategory[$catId] = [
                'name' => $item['category_name'],
                'items' => []
            ];
        }
        $itemsByCategory[$catId]['items'][] = $item;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - <?= htmlspecialchars($storeName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Karla:wght@400;500;600;700&family=Playfair+Display+SC:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="padding-top: 64px;">
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-logo">
                <span><?= htmlspecialchars($storeName) ?></span>
            </a>
            
            <ul class="navbar-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="menu.php" class="active">Menu</a></li>
                <li><a href="index.php#about">About</a></li>
                <li><a href="index.php#contact">Contact</a></li>
            </ul>
            
            <div class="navbar-actions">
                <button class="cart-btn" aria-label="Shopping cart">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span class="cart-badge" style="display: none;">0</span>
                </button>
                
                <button class="mobile-menu-btn" aria-label="Toggle menu">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div class="mobile-menu">
        <ul class="mobile-menu-list">
            <li><a href="index.php">Home</a></li>
            <li><a href="menu.php">Menu</a></li>
            <li><a href="index.php#about">About</a></li>
            <li><a href="index.php#contact">Contact</a></li>
            <li><a href="track-order.php">Track Order</a></li>
        </ul>
    </div>

    <main style="min-height: calc(100vh - 64px);">
        <!-- Page Header -->
        <section style="padding: var(--space-xl) 0; background: linear-gradient(135deg, var(--color-bg) 0%, var(--color-bg-warm) 100%);">
            <div class="section-container">
                <h1 style="text-align: center; margin-bottom: var(--space-sm);">Our Menu</h1>
                <p style="text-align: center; color: var(--color-text-muted);">Fresh, delicious, and made with love</p>
            </div>
        </section>

        <!-- Category Filter -->
        <section style="padding: var(--space-lg) 0; background: white; position: sticky; top: 64px; z-index: 50; border-bottom: 1px solid #f1f1f1;">
            <div class="section-container">
                <div class="categories-grid" style="padding-bottom: 0;">
                    <a href="menu.php" class="category-card <?= !$selectedCategory ? 'active' : '' ?>">
                        <div class="category-icon">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </div>
                        <span class="category-name">All</span>
                    </a>
                    <?php foreach ($categories as $category): ?>
                        <a href="menu.php?category=<?= $category['id'] ?>" class="category-card <?= $selectedCategory == $category['id'] ? 'active' : '' ?>">
                            <div class="category-icon">
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <span class="category-name"><?= htmlspecialchars($category['name']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Menu Items -->
        <section style="padding: var(--space-2xl) 0;">
            <div class="section-container">
                <?php if ($selectedCategory): ?>
                    <!-- Single category view -->
                    <?php if (empty($menuItems)): ?>
                        <p style="text-align: center; color: #888;">No items available in this category right now.</p>
                    <?php else: ?>
                        <div class="menu-grid">
                            <?php foreach ($menuItems as $item): ?>
                                <div class="card food-card">
                                    <div class="food-card-image">
                                        <?php if ($item['image']): ?>
                                            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy">
                                        <?php else: ?>
                                            <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #FEF2F2 0%, #FECACA 100%); display: flex; align-items: center; justify-content: center;">
                                                <svg width="48" height="48" fill="none" stroke="#DC2626" viewBox="0 0 24 24" style="opacity: 0.3;">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="food-card-badges">
                                            <?php if ($item['is_popular']): ?>
                                                <span class="badge badge-popular">Popular</span>
                                            <?php endif; ?>
                                            <?php if ($item['is_spicy']): ?>
                                                <span class="badge badge-spicy">🌶️ Spicy</span>
                                            <?php endif; ?>
                                            <?php if ($item['is_vegetarian']): ?>
                                                <span class="badge badge-vegetarian">🌱 Veg</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="food-card-content">
                                        <h3 class="food-card-title"><?= htmlspecialchars($item['name']) ?></h3>
                                        <p class="food-card-desc"><?= htmlspecialchars($item['description'] ?? '') ?></p>
                                        <div class="food-card-footer">
                                            <span class="food-card-price">
                                                <?= formatPrice($item['price']) ?>
                                                <?php if ($item['compare_price']): ?>
                                                    <span class="original"><?= formatPrice($item['compare_price']) ?></span>
                                                <?php endif; ?>
                                            </span>
                                            <button class="add-to-cart-btn" 
                                                    data-id="<?= $item['id'] ?>"
                                                    data-name="<?= htmlspecialchars($item['name']) ?>"
                                                    data-price="<?= $item['price'] ?>"
                                                    data-image="<?= htmlspecialchars($item['image'] ?? '') ?>"
                                                    aria-label="Add to cart">
                                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- All categories view -->
                    <?php if (empty($itemsByCategory)): ?>
                        <p style="text-align: center; color: #888;">No menu items available right now. Check back soon!</p>
                    <?php else: ?>
                        <?php foreach ($itemsByCategory as $catId => $categoryData): ?>
                            <div style="margin-bottom: var(--space-3xl);">
                                <h2 style="margin-bottom: var(--space-lg); padding-bottom: var(--space-sm); border-bottom: 2px solid var(--color-primary);">
                                    <?= htmlspecialchars($categoryData['name']) ?>
                                </h2>
                                <div class="menu-grid">
                                    <?php foreach ($categoryData['items'] as $item): ?>
                                        <div class="card food-card">
                                            <div class="food-card-image">
                                                <?php if ($item['image']): ?>
                                                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy">
                                                <?php else: ?>
                                                    <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #FEF2F2 0%, #FECACA 100%); display: flex; align-items: center; justify-content: center;">
                                                        <svg width="48" height="48" fill="none" stroke="#DC2626" viewBox="0 0 24 24" style="opacity: 0.3;">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="food-card-badges">
                                                    <?php if ($item['is_popular']): ?>
                                                        <span class="badge badge-popular">Popular</span>
                                                    <?php endif; ?>
                                                    <?php if ($item['is_spicy']): ?>
                                                        <span class="badge badge-spicy">🌶️</span>
                                                    <?php endif; ?>
                                                    <?php if ($item['is_vegetarian']): ?>
                                                        <span class="badge badge-vegetarian">🌱</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="food-card-content">
                                                <h3 class="food-card-title"><?= htmlspecialchars($item['name']) ?></h3>
                                                <p class="food-card-desc"><?= htmlspecialchars($item['description'] ?? '') ?></p>
                                                <div class="food-card-footer">
                                                    <span class="food-card-price"><?= formatPrice($item['price']) ?></span>
                                                    <button class="add-to-cart-btn" 
                                                            data-id="<?= $item['id'] ?>"
                                                            data-name="<?= htmlspecialchars($item['name']) ?>"
                                                            data-price="<?= $item['price'] ?>"
                                                            data-image="<?= htmlspecialchars($item['image'] ?? '') ?>"
                                                            aria-label="Add to cart">
                                                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Mobile Sticky Checkout Button -->
    <div class="mobile-order-btn" id="mobileCheckout" style="display: none;">
        <a href="checkout.php" class="btn btn-primary btn-lg">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            Checkout (<span id="mobileCartCount">0</span>)
        </a>
    </div>

    <!-- Cart Sidebar -->
    <div class="cart-overlay"></div>
    <div class="cart-sidebar">
        <div class="cart-header">
            <h3>Your Cart</h3>
            <button class="cart-close" aria-label="Close cart">&times;</button>
        </div>
        <div class="cart-items"></div>
        <div class="cart-footer">
            <div class="cart-subtotal">
                <span>Subtotal:</span>
                <strong class="cart-subtotal-amount">$0.00</strong>
            </div>
            <a href="checkout.php" class="btn btn-primary btn-block cart-checkout-btn">
                Proceed to Checkout
            </a>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
    <script src="assets/js/cart.js"></script>
    <script>
        // Show mobile checkout button when cart has items
        document.addEventListener('DOMContentLoaded', function() {
            const updateMobileBtn = () => {
                const count = Cart.getTotalItems();
                const btn = document.getElementById('mobileCheckout');
                const countEl = document.getElementById('mobileCartCount');
                if (count > 0) {
                    btn.style.display = 'block';
                    countEl.textContent = count;
                } else {
                    btn.style.display = 'none';
                }
            };
            
            // Update on cart changes
            const originalUpdate = Cart.updateUI.bind(Cart);
            Cart.updateUI = function() {
                originalUpdate();
                updateMobileBtn();
            };
            
            updateMobileBtn();
        });
    </script>
</body>
</html>
