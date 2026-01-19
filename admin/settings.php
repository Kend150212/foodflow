<?php
/**
 * FoodFlow - Settings Page
 * Organized with tabs for better management
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAuth();

$message = '';
$activeTab = $_GET['tab'] ?? 'store';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? '';
    
    switch ($section) {
        case 'store':
            setSetting('store_name', $_POST['store_name'] ?? '');
            setSetting('store_tagline', $_POST['store_tagline'] ?? '');
            setSetting('store_phone', $_POST['store_phone'] ?? '');
            setSetting('store_email', $_POST['store_email'] ?? '');
            setSetting('store_address', $_POST['store_address'] ?? '');
            setSetting('store_timezone', $_POST['store_timezone'] ?? 'America/New_York');
            break;
            
        case 'pricing':
            setSetting('tax_rate', $_POST['tax_rate'] ?? '8.25', 'number');
            setSetting('min_order_amount', $_POST['min_order_amount'] ?? '15', 'number');
            setSetting('delivery_fee', $_POST['delivery_fee'] ?? '4.99', 'number');
            setSetting('free_delivery_min', $_POST['free_delivery_min'] ?? '35', 'number');
            setSetting('estimated_prep_time', $_POST['estimated_prep_time'] ?? '25', 'number');
            setSetting('estimated_delivery_time', $_POST['estimated_delivery_time'] ?? '35', 'number');
            break;
            
        case 'payments':
            setSetting('stripe_enabled', isset($_POST['stripe_enabled']) ? '1' : '0', 'boolean');
            if (!empty($_POST['stripe_public_key'])) setSetting('stripe_public_key', $_POST['stripe_public_key']);
            if (!empty($_POST['stripe_secret_key'])) setSetting('stripe_secret_key', $_POST['stripe_secret_key']);
            
            setSetting('paypal_enabled', isset($_POST['paypal_enabled']) ? '1' : '0', 'boolean');
            if (!empty($_POST['paypal_client_id'])) setSetting('paypal_client_id', $_POST['paypal_client_id']);
            if (!empty($_POST['paypal_secret'])) setSetting('paypal_secret', $_POST['paypal_secret']);
            
            setSetting('venmo_enabled', isset($_POST['venmo_enabled']) ? '1' : '0', 'boolean');
            
            setSetting('cashapp_enabled', isset($_POST['cashapp_enabled']) ? '1' : '0', 'boolean');
            if (!empty($_POST['square_app_id'])) setSetting('square_app_id', $_POST['square_app_id']);
            if (!empty($_POST['square_access_token'])) setSetting('square_access_token', $_POST['square_access_token']);
            break;
            
        case 'ai':
            if (!empty($_POST['gemini_api_key'])) setSetting('gemini_api_key', $_POST['gemini_api_key']);
            if (!empty($_POST['openai_api_key'])) setSetting('openai_api_key', $_POST['openai_api_key']);
            setSetting('ai_provider', $_POST['ai_provider'] ?? 'gemini');
            break;
            
        case 'social':
            setSetting('facebook_url', $_POST['facebook_url'] ?? '');
            setSetting('instagram_url', $_POST['instagram_url'] ?? '');
            setSetting('twitter_url', $_POST['twitter_url'] ?? '');
            setSetting('tiktok_url', $_POST['tiktok_url'] ?? '');
            setSetting('yelp_url', $_POST['yelp_url'] ?? '');
            break;
    }
    
    $message = 'Settings saved successfully!';
    $activeTab = $section;
}

$storeName = getSetting('store_name', 'FoodFlow');

$tabs = [
    'store' => ['icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'label' => 'Store Info'],
    'pricing' => ['icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => 'Pricing & Fees'],
    'payments' => ['icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 'label' => 'Payments'],
    'ai' => ['icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', 'label' => 'AI Settings'],
    'social' => ['icon' => 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1', 'label' => 'Social Links']
];
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
        body { font-family: 'Karla', sans-serif; }
        .tab-active { border-color: #DC2626; color: #DC2626; background: #FEF2F2; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 w-64 bg-gray-900 text-white p-6 hidden lg:block">
        <div class="flex items-center gap-3 mb-8">
            <div class="w-10 h-10 bg-red-600 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
            </div>
            <span class="font-bold text-lg"><?= htmlspecialchars($storeName) ?></span>
        </div>
        
        <nav class="space-y-1">
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="orders.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Orders
            </a>
            <a href="menu.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253"/></svg>
                Menu Items
            </a>
            <a href="categories.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/></svg>
                Categories
            </a>
            <a href="content.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Landing Page
            </a>
            <a href="settings.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gray-800 text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Settings
            </a>
        </nav>
    </aside>

    <main class="lg:ml-64 min-h-screen">
        <header class="bg-white border-b px-6 py-4 sticky top-0 z-10">
            <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
            <p class="text-gray-500 text-sm">Configure your store</p>
        </header>
        
        <div class="p-6">
            <?php if ($message): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-6">
                    ✅ <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <!-- Tabs Navigation -->
            <div class="flex flex-wrap gap-2 mb-6 border-b pb-4">
                <?php foreach ($tabs as $key => $tab): ?>
                    <a href="?tab=<?= $key ?>" 
                       class="flex items-center gap-2 px-4 py-2 rounded-lg border-2 transition font-medium text-sm
                              <?= $activeTab === $key ? 'tab-active border-red-600' : 'border-gray-200 text-gray-600 hover:border-gray-300' ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $tab['icon'] ?>"/>
                        </svg>
                        <?= $tab['label'] ?>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <!-- Tab Content -->
            <div class="max-w-3xl">
                
                <!-- Store Info Tab -->
                <?php if ($activeTab === 'store'): ?>
                <form method="POST" class="bg-white rounded-xl shadow-sm border p-6 space-y-4">
                    <input type="hidden" name="section" value="store">
                    <h2 class="text-lg font-bold mb-4">🏪 Store Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Store Name</label>
                            <input type="text" name="store_name" value="<?= htmlspecialchars(getSetting('store_name', '')) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500" placeholder="FoodFlow">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tagline</label>
                            <input type="text" name="store_tagline" value="<?= htmlspecialchars(getSetting('store_tagline', '')) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500" placeholder="Delicious Food, Delivered Fast">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input type="tel" name="store_phone" value="<?= htmlspecialchars(getSetting('store_phone', '')) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500" placeholder="(555) 123-4567">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="store_email" value="<?= htmlspecialchars(getSetting('store_email', '')) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500" placeholder="hello@foodflow.com">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <input type="text" name="store_address" value="<?= htmlspecialchars(getSetting('store_address', '')) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500" placeholder="123 Main St, New York, NY 10001">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                            <select name="store_timezone" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                                <?php
                                $timezones = ['America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles', 'America/Phoenix'];
                                foreach ($timezones as $tz):
                                ?>
                                    <option value="<?= $tz ?>" <?= getSetting('store_timezone') === $tz ? 'selected' : '' ?>><?= $tz ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition mt-4">
                        Save Store Info
                    </button>
                </form>
                <?php endif; ?>
                
                <!-- Pricing Tab -->
                <?php if ($activeTab === 'pricing'): ?>
                <form method="POST" class="bg-white rounded-xl shadow-sm border p-6 space-y-4">
                    <input type="hidden" name="section" value="pricing">
                    <h2 class="text-lg font-bold mb-4">💰 Pricing & Fees</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tax Rate (%)</label>
                            <input type="number" step="0.01" name="tax_rate" value="<?= getSetting('tax_rate', 8.25) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Order ($)</label>
                            <input type="number" step="0.01" name="min_order_amount" value="<?= getSetting('min_order_amount', 15) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Fee ($)</label>
                            <input type="number" step="0.01" name="delivery_fee" value="<?= getSetting('delivery_fee', 4.99) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Free Delivery Min ($)</label>
                            <input type="number" step="0.01" name="free_delivery_min" value="<?= getSetting('free_delivery_min', 35) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prep Time (minutes)</label>
                            <input type="number" name="estimated_prep_time" value="<?= getSetting('estimated_prep_time', 25) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Time (minutes)</label>
                            <input type="number" name="estimated_delivery_time" value="<?= getSetting('estimated_delivery_time', 35) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                    
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition mt-4">
                        Save Pricing
                    </button>
                </form>
                <?php endif; ?>
                
                <!-- Payments Tab -->
                <?php if ($activeTab === 'payments'): ?>
                <form method="POST" class="bg-white rounded-xl shadow-sm border p-6 space-y-6">
                    <input type="hidden" name="section" value="payments">
                    <h2 class="text-lg font-bold mb-4">💳 Payment Methods</h2>
                    
                    <!-- Stripe -->
                    <div class="border-b pb-4">
                        <label class="flex items-center gap-3 cursor-pointer mb-3">
                            <input type="checkbox" name="stripe_enabled" value="1" <?= getSetting('stripe_enabled') ? 'checked' : '' ?>
                                   class="w-5 h-5 text-red-600 rounded">
                            <span class="font-medium">💳 Stripe (Credit/Debit Cards)</span>
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
                    <div class="border-b pb-4">
                        <label class="flex items-center gap-3 cursor-pointer mb-3">
                            <input type="checkbox" name="paypal_enabled" value="1" <?= getSetting('paypal_enabled') ? 'checked' : '' ?>
                                   class="w-5 h-5 text-red-600 rounded">
                            <span class="font-medium">🅿️ PayPal</span>
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
                    <div class="border-b pb-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="venmo_enabled" value="1" <?= getSetting('venmo_enabled') ? 'checked' : '' ?>
                                   class="w-5 h-5 text-red-600 rounded">
                            <span class="font-medium">📱 Venmo</span>
                            <span class="text-sm text-gray-500">(Requires PayPal)</span>
                        </label>
                    </div>
                    
                    <!-- CashApp -->
                    <div>
                        <label class="flex items-center gap-3 cursor-pointer mb-3">
                            <input type="checkbox" name="cashapp_enabled" value="1" <?= getSetting('cashapp_enabled') ? 'checked' : '' ?>
                                   class="w-5 h-5 text-red-600 rounded">
                            <span class="font-medium">💵 Cash App Pay</span>
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
                    
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition">
                        Save Payment Settings
                    </button>
                </form>
                <?php endif; ?>
                
                <!-- AI Settings Tab -->
                <?php if ($activeTab === 'ai'): ?>
                <form method="POST" class="bg-white rounded-xl shadow-sm border p-6 space-y-4">
                    <input type="hidden" name="section" value="ai">
                    <h2 class="text-lg font-bold mb-2">🤖 AI Content Generation</h2>
                    <p class="text-gray-500 text-sm mb-4">Use AI to automatically generate landing page content based on your store description.</p>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">AI Provider</label>
                        <select name="ai_provider" id="aiProvider" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                            <option value="gemini" <?= getSetting('ai_provider') === 'gemini' ? 'selected' : '' ?>>Google Gemini (Recommended)</option>
                            <option value="openai" <?= getSetting('ai_provider') === 'openai' ? 'selected' : '' ?>>OpenAI GPT-4</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Google Gemini API Key
                            <a href="https://aistudio.google.com/apikey" target="_blank" class="text-blue-600 text-xs ml-2">Get API Key →</a>
                        </label>
                        <div class="flex gap-2">
                            <input type="text" name="gemini_api_key" id="geminiKey"
                                   placeholder="AIza..." 
                                   value="<?= htmlspecialchars(getSetting('gemini_api_key', '')) ?>"
                                   class="flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                            <button type="button" onclick="testApiKey('gemini')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                                Test
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Free tier: 60 requests/minute</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            OpenAI API Key
                            <a href="https://platform.openai.com/api-keys" target="_blank" class="text-blue-600 text-xs ml-2">Get API Key →</a>
                        </label>
                        <div class="flex gap-2">
                            <input type="text" name="openai_api_key" id="openaiKey"
                                   placeholder="sk-..." 
                                   value="<?= htmlspecialchars(getSetting('openai_api_key', '')) ?>"
                                   class="flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                            <button type="button" onclick="testApiKey('openai')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                                Test
                            </button>
                        </div>
                    </div>
                    
                    <div id="testResult" class="hidden rounded-lg p-4 mt-4"></div>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                        <h4 class="font-medium text-blue-800 mb-2">💡 How it works</h4>
                        <ol class="text-sm text-blue-700 space-y-1">
                            <li>1. Add your API key above and click "Test" to verify</li>
                            <li>2. Go to <strong>Landing Page</strong> section</li>
                            <li>3. Describe your restaurant/store</li>
                            <li>4. Click <strong>"Generate with AI"</strong></li>
                        </ol>
                    </div>
                    
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition mt-4">
                        Save AI Settings
                    </button>
                </form>
                <?php endif; ?>
                
                <!-- Social Links Tab -->
                <?php if ($activeTab === 'social'): ?>
                <form method="POST" class="bg-white rounded-xl shadow-sm border p-6 space-y-4">
                    <input type="hidden" name="section" value="social">
                    <h2 class="text-lg font-bold mb-4">🔗 Social Media Links</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">📘 Facebook</label>
                            <input type="url" name="facebook_url" value="<?= htmlspecialchars(getSetting('facebook_url', '')) ?>"
                                   placeholder="https://facebook.com/yourpage"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">📸 Instagram</label>
                            <input type="url" name="instagram_url" value="<?= htmlspecialchars(getSetting('instagram_url', '')) ?>"
                                   placeholder="https://instagram.com/yourhandle"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">🐦 Twitter/X</label>
                            <input type="url" name="twitter_url" value="<?= htmlspecialchars(getSetting('twitter_url', '')) ?>"
                                   placeholder="https://twitter.com/yourhandle"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">🎵 TikTok</label>
                            <input type="url" name="tiktok_url" value="<?= htmlspecialchars(getSetting('tiktok_url', '')) ?>"
                                   placeholder="https://tiktok.com/@yourhandle"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">⭐ Yelp</label>
                            <input type="url" name="yelp_url" value="<?= htmlspecialchars(getSetting('yelp_url', '')) ?>"
                                   placeholder="https://yelp.com/biz/yourstore"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                    
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition mt-4">
                        Save Social Links
                    </button>
                </form>
                <?php endif; ?>
                
            </div>
        </div>
    </main>
    
    <script>
        async function testApiKey(provider) {
            const keyInput = provider === 'gemini' ? document.getElementById('geminiKey') : document.getElementById('openaiKey');
            const apiKey = keyInput.value.trim();
            
            if (!apiKey) {
                showTestResult(false, 'Please enter an API key first');
                return;
            }
            
            const resultDiv = document.getElementById('testResult');
            resultDiv.classList.remove('hidden', 'bg-green-50', 'bg-red-50', 'border-green-200', 'border-red-200');
            resultDiv.innerHTML = '<span class="text-gray-600">Testing...</span>';
            resultDiv.classList.add('bg-gray-50', 'border', 'border-gray-200');
            
            try {
                const response = await fetch('../api/test-key.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ provider, api_key: apiKey })
                });
                
                const data = await response.json();
                showTestResult(data.success, data.message || data.error);
            } catch (err) {
                showTestResult(false, 'Connection error: ' + err.message);
            }
        }
        
        function showTestResult(success, message) {
            const resultDiv = document.getElementById('testResult');
            resultDiv.classList.remove('hidden', 'bg-gray-50', 'border-gray-200');
            
            if (success) {
                resultDiv.className = 'rounded-lg p-4 mt-4 bg-green-50 border border-green-200 text-green-700';
                resultDiv.innerHTML = '✅ ' + message;
            } else {
                resultDiv.className = 'rounded-lg p-4 mt-4 bg-red-50 border border-red-200 text-red-700';
                resultDiv.innerHTML = '❌ ' + message;
            }
        }
    </script>
</body>
</html>