<?php
/**
 * FoodFlow - Shared Admin Sidebar Component
 * Include this in all admin pages for consistent navigation
 */

$currentPage = basename($_SERVER['PHP_SELF']);
$storeName = getSetting('store_name', 'FoodFlow');

$menuItems = [
    ['url' => 'index.php', 'name' => 'Dashboard', 'icon' => 'home'],
    ['url' => 'orders.php', 'name' => 'Orders', 'icon' => 'clipboard'],
    ['url' => 'reports.php', 'name' => 'Reports', 'icon' => 'chart'],
    ['url' => 'menu.php', 'name' => 'Menu Items', 'icon' => 'book'],
    ['url' => 'categories.php', 'name' => 'Categories', 'icon' => 'grid'],
    ['url' => 'content.php', 'name' => 'Landing Page', 'icon' => 'edit'],
    ['url' => 'settings.php', 'name' => 'Settings', 'icon' => 'cog'],
];

$icons = [
    'home' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
    'clipboard' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>',
    'chart' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
    'book' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',
    'grid' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>',
    'edit' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
    'cog' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
];
?>

<!-- Sidebar -->
<aside class="fixed inset-y-0 left-0 w-64 bg-gray-900 text-white p-6 hidden lg:block z-20">
    <div class="flex items-center gap-3 mb-8">
        <div class="w-10 h-10 bg-red-600 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
        </div>
        <span class="font-bold text-lg">
            <?= htmlspecialchars($storeName) ?>
        </span>
    </div>

    <nav class="space-y-1">
        <?php foreach ($menuItems as $item): ?>
            <a href="<?= $item['url'] ?>"
                class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?= $currentPage === $item['url'] ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' ?>">
                <?= $icons[$item['icon']] ?>
                <?= $item['name'] ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="absolute bottom-6 left-6 right-6">
        <div class="border-t border-gray-700 pt-4 space-y-2">
            <a href="pos.php"
                class="flex items-center gap-3 px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white transition text-sm font-medium">
                <span>💵</span> POS System
            </a>
            <a href="kitchen.php"
                class="flex items-center gap-3 px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-700 text-white transition text-sm font-medium">
                <span>👨‍🍳</span> Kitchen Display
            </a>
        </div>
    </div>
</aside>

<!-- Mobile Header -->
<header class="lg:hidden fixed top-0 left-0 right-0 bg-gray-900 text-white p-4 z-20 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
        </div>
        <span class="font-bold">
            <?= htmlspecialchars($storeName) ?>
        </span>
    </div>
    <button onclick="document.getElementById('mobileMenu').classList.toggle('hidden')" class="p-2">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>
</header>

<!-- Mobile Menu -->
<div id="mobileMenu" class="lg:hidden fixed inset-0 bg-gray-900/95 z-30 hidden pt-16">
    <nav class="p-4 space-y-2">
        <?php foreach ($menuItems as $item): ?>
            <a href="<?= $item['url'] ?>"
                class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $currentPage === $item['url'] ? 'bg-gray-800 text-white' : 'text-gray-300' ?>">
                <?= $icons[$item['icon']] ?>
                <?= $item['name'] ?>
            </a>
        <?php endforeach; ?>
        <hr class="border-gray-700 my-4">
        <a href="pos.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-red-600 text-white">
            <span>💵</span> POS System
        </a>
        <a href="kitchen.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-orange-600 text-white">
            <span>👨‍🍳</span> Kitchen Display
        </a>
    </nav>
</div>