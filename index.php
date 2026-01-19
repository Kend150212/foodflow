<?php
/**
 * FoodFlow - Landing Page
 * All content is loaded from database (editable from admin)
 */

require_once __DIR__ . '/includes/functions.php';

// Get landing content from database
$hero = getLandingContent('hero');
$about = getLandingContent('about');
$features = getLandingContent('features');
$testimonials = getLandingContent('testimonials');
$cta = getLandingContent('cta');

// Get store settings
$storeName = getSetting('store_name', 'FoodFlow');
$storeTagline = getSetting('store_tagline', 'Delicious Food, Delivered Fast');
$storePhone = getSetting('store_phone', '');
$storeAddress = getSetting('store_address', '');
$storeEmail = getSetting('store_email', '');
$freeDeliveryMin = getSetting('free_delivery_min', 35);

// Get featured items
$featuredItems = getAvailableMenuItems(null, true);
$categories = getCategories();

// Social links
$facebookUrl = getSetting('facebook_url', '');
$instagramUrl = getSetting('instagram_url', '');
$twitterUrl = getSetting('twitter_url', '');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($hero['subtitle'] ?? $storeTagline) ?>">
    <title>
        <?= htmlspecialchars($storeName) ?> -
        <?= htmlspecialchars($storeTagline) ?>
    </title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Karla:wght@300;400;500;600;700&family=Playfair+Display+SC:wght@400;700&display=swap"
        rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-logo">
                <?php if (getSetting('store_logo')): ?>
                    <img src="<?= getSetting('store_logo') ?>" alt="<?= htmlspecialchars($storeName) ?>">
                <?php endif; ?>
                <span>
                    <?= htmlspecialchars($storeName) ?>
                </span>
            </a>

            <ul class="navbar-menu">
                <li><a href="#home" class="active">Home</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>

            <div class="navbar-actions">
                <button class="cart-btn" aria-label="Shopping cart">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span class="cart-badge" style="display: none;">0</span>
                </button>

                <a href="menu.php" class="btn btn-primary btn-sm md:hidden">Order</a>

                <button class="mobile-menu-btn" aria-label="Toggle menu">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div class="mobile-menu">
        <ul class="mobile-menu-list">
            <li><a href="#home">Home</a></li>
            <li><a href="menu.php">Menu</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#contact">Contact</a></li>
            <li><a href="track-order.php">Track Order</a></li>
        </ul>
    </div>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-title">
                    <?= htmlspecialchars($hero['title'] ?? 'Authentic Flavors, Delivered Fresh') ?>
                </h1>
                <p class="hero-subtitle">
                    <?= htmlspecialchars($hero['subtitle'] ?? 'Experience restaurant-quality meals delivered right to your door') ?>
                </p>
                <div class="hero-buttons">
                    <a href="<?= htmlspecialchars($hero['cta_link'] ?? 'menu.php') ?>" class="btn btn-primary btn-lg">
                        <?= htmlspecialchars($hero['cta_text'] ?? 'Order Now') ?>
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                    <a href="#menu-preview" class="btn btn-outline btn-lg">View Menu</a>
                </div>
            </div>
            <div class="hero-image">
                <?php if (!empty($hero['image'])): ?>
                    <img src="<?= htmlspecialchars($hero['image']) ?>" alt="Delicious food">
                <?php else: ?>
                    <div
                        style="aspect-ratio: 1; background: linear-gradient(135deg, #FEF2F2 0%, #FECACA 100%); border-radius: var(--radius-2xl); display: flex; align-items: center; justify-content: center;">
                        <svg width="120" height="120" fill="none" stroke="#DC2626" viewBox="0 0 24 24"
                            style="opacity: 0.5;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                <?php endif; ?>

                <div class="hero-badge hero-badge-delivery">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        style="color: var(--color-primary);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <strong style="font-size: 0.875rem;">Fast Delivery</strong>
                        <p style="font-size: 0.75rem; color: #888; margin: 0;">30-45 min</p>
                    </div>
                </div>

                <div class="hero-badge hero-badge-rating">
                    <span style="font-size: 1.25rem;">⭐</span>
                    <div>
                        <strong style="font-size: 0.875rem;">4.9 Rating</strong>
                        <p style="font-size: 0.75rem; color: #888; margin: 0;">500+ reviews</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories">
        <div class="section-container">
            <div class="section-header">
                <h2 class="section-title">Browse Categories</h2>
                <p class="section-subtitle">Explore our delicious menu categories</p>
            </div>

            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                    <a href="menu.php?category=<?= $category['id'] ?>" class="category-card">
                        <div class="category-icon">
                            <?php
                            $icons = [
                                'utensils' => '<svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',
                                'fire' => '<svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>',
                                'bowl-food' => '<svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
                                'glass-water' => '<svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                                'ice-cream' => '<svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>'
                            ];
                            echo $icons[$category['icon']] ?? $icons['utensils'];
                            ?>
                        </div>
                        <span class="category-name">
                            <?= htmlspecialchars($category['name']) ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Menu Items -->
    <section id="menu-preview" class="featured-menu" style="padding: var(--space-3xl) 0; background: white;">
        <div class="section-container">
            <div class="section-header">
                <h2 class="section-title">Popular Dishes</h2>
                <p class="section-subtitle">Our customers' favorites</p>
            </div>

            <div class="menu-grid">
                <?php if (empty($featuredItems)): ?>
                    <p style="text-align: center; grid-column: 1/-1; color: #888;">No featured items yet. Add some from the
                        admin panel!</p>
                <?php else: ?>
                    <?php foreach (array_slice($featuredItems, 0, 6) as $item): ?>
                        <div class="card food-card" onclick="window.location='menu.php?item=<?= $item['id'] ?>'">
                            <div class="food-card-image">
                                <?php if ($item['image']): ?>
                                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"
                                        loading="lazy">
                                <?php else: ?>
                                    <div
                                        style="width: 100%; height: 100%; background: linear-gradient(135deg, #FEF2F2 0%, #FECACA 100%); display: flex; align-items: center; justify-content: center;">
                                        <svg width="48" height="48" fill="none" stroke="#DC2626" viewBox="0 0 24 24"
                                            style="opacity: 0.3;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
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
                                <h3 class="food-card-title">
                                    <?= htmlspecialchars($item['name']) ?>
                                </h3>
                                <p class="food-card-desc">
                                    <?= htmlspecialchars($item['description'] ?? '') ?>
                                </p>
                                <div class="food-card-footer">
                                    <span class="food-card-price">
                                        <?= formatPrice($item['price']) ?>
                                        <?php if ($item['compare_price']): ?>
                                            <span class="original">
                                                <?= formatPrice($item['compare_price']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </span>
                                    <button class="add-to-cart-btn" data-id="<?= $item['id'] ?>"
                                        data-name="<?= htmlspecialchars($item['name']) ?>" data-price="<?= $item['price'] ?>"
                                        data-image="<?= htmlspecialchars($item['image'] ?? '') ?>" aria-label="Add to cart">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="text-center mt-8">
                <a href="menu.php" class="btn btn-outline btn-lg">View Full Menu</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="section-container">
            <div class="section-header">
                <h2 class="section-title">Why Choose Us?</h2>
                <p class="section-subtitle">We deliver more than just food</p>
            </div>

            <div class="features-grid">
                <?php
                $featureItems = $features['items'] ?? [
                    ['icon' => 'clock', 'title' => 'Fast Delivery', 'desc' => 'Hot food at your door in 30-45 minutes'],
                    ['icon' => 'leaf', 'title' => 'Fresh Ingredients', 'desc' => 'Quality ingredients, made fresh daily'],
                    ['icon' => 'credit-card', 'title' => 'Easy Payment', 'desc' => 'Pay your way - cards, PayPal, Venmo & more']
                ];

                $featureIcons = [
                    'clock' => '<svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                    'leaf' => '<svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>',
                    'credit-card' => '<svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>'
                ];

                foreach ($featureItems as $feature):
                    ?>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <?= $featureIcons[$feature['icon']] ?? $featureIcons['clock'] ?>
                        </div>
                        <h3 class="feature-title">
                            <?= htmlspecialchars($feature['title']) ?>
                        </h3>
                        <p class="feature-desc">
                            <?= htmlspecialchars($feature['desc']) ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" style="padding: var(--space-3xl) 0; background: var(--color-bg);">
        <div class="section-container">
            <div style="display: grid; grid-template-columns: 1fr; gap: var(--space-2xl); align-items: center;">
                <div style="text-align: center;">
                    <h2 class="section-title" style="margin-bottom: var(--space-md);">
                        <?= htmlspecialchars($about['title'] ?? 'Our Story') ?>
                    </h2>
                    <p style="color: var(--color-text-muted); max-width: 600px; margin: 0 auto; line-height: 1.8;">
                        <?= htmlspecialchars($about['description'] ?? 'We started with a simple mission: bring the best local food directly to your table. Every dish is prepared fresh with quality ingredients and delivered with care.') ?>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="section-container">
            <div class="section-header">
                <h2 class="section-title">What Our Customers Say</h2>
                <p class="section-subtitle">Real reviews from real food lovers</p>
            </div>

            <div class="testimonials-grid">
                <?php
                $testimonialItems = $testimonials['items'] ?? [
                    ['name' => 'Sarah M.', 'text' => 'Best food in town! Fast delivery and always hot.', 'rating' => 5],
                    ['name' => 'James K.', 'text' => 'Love the flavors. My go-to lunch spot!', 'rating' => 5],
                    ['name' => 'Lisa T.', 'text' => 'Great portions and authentic taste. Highly recommend!', 'rating' => 5]
                ];

                foreach ($testimonialItems as $testimonial):
                    ?>
                    <div class="testimonial-card">
                        <div class="testimonial-rating">
                            <?php for ($i = 0; $i < ($testimonial['rating'] ?? 5); $i++): ?>
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                </svg>
                            <?php endfor; ?>
                        </div>
                        <p class="testimonial-text">"
                            <?= htmlspecialchars($testimonial['text']) ?>"
                        </p>
                        <div class="testimonial-author">
                            <div class="testimonial-avatar">
                                <?= strtoupper(substr($testimonial['name'], 0, 1)) ?>
                            </div>
                            <span class="testimonial-name">
                                <?= htmlspecialchars($testimonial['name']) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="section-container">
            <h2 class="cta-title">
                <?= htmlspecialchars($cta['title'] ?? 'Hungry? Order Now!') ?>
            </h2>
            <p class="cta-subtitle">
                <?= htmlspecialchars($cta['subtitle'] ?? 'Free delivery on orders over $' . $freeDeliveryMin) ?>
            </p>
            <a href="<?= htmlspecialchars($cta['button_link'] ?? 'menu.php') ?>" class="btn btn-white btn-lg">
                <?= htmlspecialchars($cta['button_text'] ?? 'View Menu') ?>
            </a>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" style="padding: var(--space-3xl) 0; background: white;">
        <div class="section-container">
            <div class="section-header">
                <h2 class="section-title">Visit Us</h2>
                <p class="section-subtitle">Find us or reach out</p>
            </div>

            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: var(--space-xl); text-align: center;">
                <div>
                    <div
                        style="width: 56px; height: 56px; background: var(--color-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-md);">
                        <svg width="24" height="24" fill="none" stroke="var(--color-primary)" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h4 style="margin-bottom: var(--space-xs);">Address</h4>
                    <p style="color: var(--color-text-muted);">
                        <?= htmlspecialchars($storeAddress ?: 'Address coming soon') ?>
                    </p>
                </div>

                <div>
                    <div
                        style="width: 56px; height: 56px; background: var(--color-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-md);">
                        <svg width="24" height="24" fill="none" stroke="var(--color-primary)" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </div>
                    <h4 style="margin-bottom: var(--space-xs);">Phone</h4>
                    <p style="color: var(--color-text-muted);">
                        <a href="tel:<?= preg_replace('/[^0-9]/', '', $storePhone) ?>">
                            <?= htmlspecialchars($storePhone ?: 'Phone coming soon') ?>
                        </a>
                    </p>
                </div>

                <div>
                    <div
                        style="width: 56px; height: 56px; background: var(--color-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-md);">
                        <svg width="24" height="24" fill="none" stroke="var(--color-primary)" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h4 style="margin-bottom: var(--space-xs);">Email</h4>
                    <p style="color: var(--color-text-muted);">
                        <a href="mailto:<?= htmlspecialchars($storeEmail) ?>">
                            <?= htmlspecialchars($storeEmail ?: 'Email coming soon') ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="section-container">
            <div class="footer-grid">
                <div>
                    <div class="footer-logo">
                        <?= htmlspecialchars($storeName) ?>
                    </div>
                    <p class="footer-desc">
                        <?= htmlspecialchars($storeTagline) ?>
                    </p>
                    <div class="footer-social" style="margin-top: var(--space-md);">
                        <?php if ($facebookUrl): ?>
                            <a href="<?= htmlspecialchars($facebookUrl) ?>" target="_blank" rel="noopener"
                                aria-label="Facebook">
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z" />
                                </svg>
                            </a>
                        <?php endif; ?>
                        <?php if ($instagramUrl): ?>
                            <a href="<?= htmlspecialchars($instagramUrl) ?>" target="_blank" rel="noopener"
                                aria-label="Instagram">
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                                    <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
                                    <path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z" fill="none" stroke="currentColor"
                                        stroke-width="2" />
                                    <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" stroke="currentColor" stroke-width="2" />
                                </svg>
                            </a>
                        <?php endif; ?>
                        <?php if ($twitterUrl): ?>
                            <a href="<?= htmlspecialchars($twitterUrl) ?>" target="_blank" rel="noopener"
                                aria-label="Twitter">
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z" />
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <h4 class="footer-title">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="menu.php">Menu</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#contact">Contact</a></li>
                        <li><a href="track-order.php">Track Order</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-title">Legal</h4>
                    <ul class="footer-links">
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Refund Policy</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-title">Hours</h4>
                    <ul class="footer-links">
                        <li>Mon-Thu: 10AM - 9PM</li>
                        <li>Fri-Sat: 10AM - 10PM</li>
                        <li>Sunday: 11AM - 8PM</li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy;
                    <?= date('Y') ?>
                    <?= htmlspecialchars($storeName) ?>. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <!-- Mobile Sticky Order Button -->
    <div class="mobile-order-btn">
        <a href="menu.php" class="btn btn-primary btn-lg">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            Order Now
        </a>
    </div>

    <!-- Cart Sidebar -->
    <div class="cart-overlay"></div>
    <div class="cart-sidebar">
        <div class="cart-header">
            <h3>Your Cart</h3>
            <button class="cart-close" aria-label="Close cart">&times;</button>
        </div>
        <div class="cart-items">
            <!-- Cart items will be rendered here by JS -->
        </div>
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

    <!-- Scripts -->
    <script src="assets/js/app.js"></script>
    <script src="assets/js/cart.js"></script>
</body>

</html>