<?php
/**
 * FoodFlow - Settings Page
 * Multi-payment configuration
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAuth();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Store info
    setSetting('store_name', $_POST['store_name'] ?? '');
    setSetting('store_tagline', $_POST['store_tagline'] ?? '');
    setSetting('store_phone', $_POST['store_phone'] ?? '');
    setSetting('store_email', $_POST['store_email'] ?? '');
    setSetting('store_address', $_POST['store_address'] ?? '');

    // Pricing
    setSetting('tax_rate', $_POST['tax_rate'] ?? '8.25', 'number');
    setSetting('min_order_amount', $_POST['min_order_amount'] ?? '15', 'number');
    setSetting('delivery_fee', $_POST['delivery_fee'] ?? '4.99', 'number');
    setSetting('free_delivery_min', $_POST['free_delivery_min'] ?? '35', 'number');

    // Payment - Stripe
    setSetting('stripe_enabled', isset($_POST['stripe_enabled']) ? '1' : '0', 'boolean');
    if (!empty($_POST['stripe_public_key'])) {
        setSetting('stripe_public_key', $_POST['stripe_public_key']);
    }
    if (!empty($_POST['stripe_secret_key'])) {
        setSetting('stripe_secret_key', $_POST['stripe_secret_key']);
    }

    // Payment - PayPal
    setSetting('paypal_enabled', isset($_POST['paypal_enabled']) ? '1' : '0', 'boolean');
    if (!empty($_POST['paypal_client_id'])) {
        setSetting('paypal_client_id', $_POST['paypal_client_id']);
    }
    if (!empty($_POST['paypal_secret'])) {
        setSetting('paypal_secret', $_POST['paypal_secret']);
    }

    // Payment - Venmo (via PayPal)
    setSetting('venmo_enabled', isset($_POST['venmo_enabled']) ? '1' : '0', 'boolean');

    // Payment - CashApp (via Square)
    setSetting('cashapp_enabled', isset($_POST['cashapp_enabled']) ? '1' : '0', 'boolean');
    if (!empty($_POST['square_app_id'])) {
        setSetting('square_app_id', $_POST['square_app_id']);
    }
    if (!empty($_POST['square_access_token'])) {
        setSetting('square_access_token', $_POST['square_access_token']);
    }

    // Social
    setSetting('facebook_url', $_POST['facebook_url'] ?? '');
    setSetting('instagram_url', $_POST['instagram_url'] ?? '');
    setSetting('twitter_url', $_POST['twitter_url'] ?? '');

    $message = 'Settings saved successfully!';
}

$storeName = getSetting('store_name', 'FoodFlow');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Karla:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Karla', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <aside class="fixed inset-y-0 left-0 w-64 bg-gray-900 text-white p-6 hidden lg:block">
        <div class="flex items-center gap-3 mb-8">
            <div class="w-10 h-10 bg-red-600 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            </div>
            <span class="font-bold text-lg">
                <?= htmlspecialchars($storeName) ?>
            </span>
        </div>

        <nav class="space-y-1">
            <a href="index.php"
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>
            <a href="orders.php"
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Orders
            </a>
            <a href="menu.php"
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253" />
                </svg>
                Menu Items
            </a>
            <a href="categories.php"
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z" />
                </svg>
                Categories
            </a>
            <a href="content.php"
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Landing Page
            </a>
            <a href="settings.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gray-800 text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Settings
            </a>
        </nav>
    </aside>

    <main class="lg:ml-64 min-h-screen">
        <header class="bg-white border-b px-6 py-4 sticky top-0 z-10">
            <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
            <p class="text-gray-500 text-sm">Configure your store and payment methods</p>
        </header>

        <div class="p-6">
            <?php if ($message): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-6">
                    ✅
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6 max-w-3xl">
                <!-- Store Info -->
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h2 class="text-lg font-bold mb-4">Store Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Store Name</label>
                            <input type="text" name="store_name"
                                value="<?= htmlspecialchars(getSetting('store_name', '')) ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tagline</label>
                            <input type="text" name="store_tagline"
                                value="<?= htmlspecialchars(getSetting('store_tagline', '')) ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input type="tel" name="store_phone"
                                value="<?= htmlspecialchars(getSetting('store_phone', '')) ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="store_email"
                                value="<?= htmlspecialchars(getSetting('store_email', '')) ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <input type="text" name="store_address"
                                value="<?= htmlspecialchars(getSetting('store_address', '')) ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                </div>

                <!-- Pricing -->
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h2 class="text-lg font-bold mb-4">Pricing & Fees</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tax Rate (%)</label>
                            <input type="number" step="0.01" name="tax_rate" value="<?= getSetting('tax_rate', 8.25) ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Order ($)</label>
                            <input type="number" step="0.01" name="min_order_amount"
                                value="<?= getSetting('min_order_amount', 15) ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Fee ($)</label>
                            <input type="number" step="0.01" name="delivery_fee"
                                value="<?= getSetting('delivery_fee', 4.99) ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Free Delivery Minimum
                                ($)</label>
                            <input type="number" step="0.01" name="free_delivery_min"
                                value="<?= getSetting('free_delivery_min', 35) ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h2 class="text-lg font-bold mb-4">💳 Payment Methods</h2>

                    <!-- Stripe -->
                    <div class="border-b pb-4 mb-4">
                        <label class="flex items-center gap-3 cursor-pointer mb-3">
                            <input type="checkbox" name="stripe_enabled" value="1" <?= getSetting('stripe_enabled') ? 'checked' : '' ?>
                            class="w-5 h-5 text-red-600 rounded">
                            <span class="font-medium">Stripe (Credit/Debit Cards)</span>
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 ml-8">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Publishable Key</label>
                                <input type="text" name="stripe_public_key" placeholder="pk_live_..."
                                    value="<?= getSetting('stripe_public_key') ? '••••••••' : '' ?>"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Secret Key</label>
                                <input type="password" name="stripe_secret_key" placeholder="sk_live_..."
                                    value="<?= getSetting('stripe_secret_key') ? '••••••••' : '' ?>"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- PayPal -->
                    <div class="border-b pb-4 mb-4">
                        <label class="flex items-center gap-3 cursor-pointer mb-3">
                            <input type="checkbox" name="paypal_enabled" value="1" <?= getSetting('paypal_enabled') ? 'checked' : '' ?>
                            class="w-5 h-5 text-red-600 rounded">
                            <span class="font-medium">PayPal</span>
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 ml-8">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Client ID</label>
                                <input type="text" name="paypal_client_id" placeholder="Client ID"
                                    value="<?= getSetting('paypal_client_id') ? '••••••••' : '' ?>"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Secret</label>
                                <input type="password" name="paypal_secret" placeholder="Secret"
                                    value="<?= getSetting('paypal_secret') ? '••••••••' : '' ?>"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Venmo -->
                    <div class="border-b pb-4 mb-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="venmo_enabled" value="1" <?= getSetting('venmo_enabled') ? 'checked' : '' ?>
                            class="w-5 h-5 text-red-600 rounded">
                            <span class="font-medium">Venmo</span>
                            <span class="text-sm text-gray-500">(Requires PayPal integration)</span>
                        </label>
                    </div>

                    <!-- CashApp -->
                    <div>
                        <label class="flex items-center gap-3 cursor-pointer mb-3">
                            <input type="checkbox" name="cashapp_enabled" value="1" <?= getSetting('cashapp_enabled') ? 'checked' : '' ?>
                            class="w-5 h-5 text-red-600 rounded">
                            <span class="font-medium">Cash App Pay</span>
                            <span class="text-sm text-gray-500">(via Square)</span>
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 ml-8">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Square App ID</label>
                                <input type="text" name="square_app_id" placeholder="App ID"
                                    value="<?= getSetting('square_app_id') ? '••••••••' : '' ?>"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Access Token</label>
                                <input type="password" name="square_access_token" placeholder="Access Token"
                                    value="<?= getSetting('square_access_token') ? '••••••••' : '' ?>"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Links -->
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h2 class="text-lg font-bold mb-4">Social Links</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Facebook URL</label>
                            <input type="url" name="facebook_url"
                                value="<?= htmlspecialchars(getSetting('facebook_url', '')) ?>"
                                placeholder="https://facebook.com/..."
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Instagram URL</label>
                            <input type="url" name="instagram_url"
                                value="<?= htmlspecialchars(getSetting('instagram_url', '')) ?>"
                                placeholder="https://instagram.com/..."
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Twitter URL</label>
                            <input type="url" name="twitter_url"
                                value="<?= htmlspecialchars(getSetting('twitter_url', '')) ?>"
                                placeholder="https://twitter.com/..."
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                </div>

                <button type="submit"
                    class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg font-medium transition">
                    Save Settings
                </button>
            </form>
        </div>
    </main>
</body>

</html>