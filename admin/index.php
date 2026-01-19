<?php
/**
 * FoodFlow - Admin Dashboard
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAuth();

// Get statistics
$todayOrders = db()->fetch(
    "SELECT COUNT(*) as count, COALESCE(SUM(total), 0) as revenue 
     FROM orders 
     WHERE DATE(created_at) = CURDATE()"
) ?? ['count' => 0, 'revenue' => 0];

$pendingOrders = db()->fetch(
    "SELECT COUNT(*) as count FROM orders WHERE order_status IN ('pending', 'confirmed', 'preparing')"
) ?? ['count' => 0];

$totalOrders = db()->fetch(
    "SELECT COUNT(*) as count, COALESCE(SUM(total), 0) as revenue FROM orders WHERE payment_status = 'paid'"
) ?? ['count' => 0, 'revenue' => 0];

$menuItemsCount = db()->fetch("SELECT COUNT(*) as count FROM menu_items WHERE is_active = 1") ?? ['count' => 0];

// Recent orders
$recentOrders = db()->fetchAll(
    "SELECT * FROM orders ORDER BY created_at DESC LIMIT 10"
);

$storeName = getSetting('store_name', 'FoodFlow');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Karla:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Karla', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Sidebar -->
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
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gray-800 text-white">
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
                <?php if ($pendingOrders['count'] > 0): ?>
                    <span class="ml-auto bg-red-600 text-white text-xs px-2 py-1 rounded-full">
                        <?= $pendingOrders['count'] ?>
                    </span>
                <?php endif; ?>
            </a>
            <a href="menu.php"
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                Menu Items
            </a>
            <a href="categories.php"
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
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
            <a href="settings.php"
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Settings
            </a>
        </nav>

        <div class="absolute bottom-6 left-6 right-6">
            <a href="../index.php" target="_blank"
                class="flex items-center gap-2 text-gray-400 hover:text-white text-sm mb-4">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                View Store
            </a>
            <a href="?logout=1" class="flex items-center gap-2 text-gray-400 hover:text-red-400 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Logout
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen">
        <!-- Header -->
        <header class="bg-white border-b px-6 py-4 flex items-center justify-between sticky top-0 z-10">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                <p class="text-gray-500 text-sm">Welcome back,
                    <?= htmlspecialchars(getAdminName()) ?>
                </p>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-500">
                    <?= date('l, F j, Y') ?>
                </span>
            </div>
        </header>

        <div class="p-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl p-6 shadow-sm border">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-gray-500 text-sm">Today's Orders</span>
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-gray-900">
                        <?= $todayOrders['count'] ?>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-gray-500 text-sm">Today's Revenue</span>
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-gray-900">$
                        <?= number_format($todayOrders['revenue'], 2) ?>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-gray-500 text-sm">Pending Orders</span>
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-gray-900">
                        <?= $pendingOrders['count'] ?>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-gray-500 text-sm">Menu Items</span>
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-gray-900">
                        <?= $menuItemsCount['count'] ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <a href="pos.php"
                    class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white rounded-xl p-6 flex items-center gap-4 transition shadow-lg">
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <span class="text-2xl">💵</span>
                    </div>
                    <div>
                        <div class="font-semibold text-lg">POS</div>
                        <div class="text-red-200 text-sm">Quick order taking</div>
                    </div>
                </a>

                <a href="kitchen.php"
                    class="bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-xl p-6 flex items-center gap-4 transition shadow-lg">
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <span class="text-2xl">👨‍🍳</span>
                    </div>
                    <div>
                        <div class="font-semibold text-lg">Kitchen</div>
                        <div class="text-orange-200 text-sm">Order display</div>
                    </div>
                </a>

                <a href="orders.php"
                    class="bg-white hover:bg-gray-50 border rounded-xl p-6 flex items-center gap-4 transition">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">View Orders</div>
                        <div class="text-gray-500 text-sm">Manage orders</div>
                    </div>
                </a>

                <a href="menu.php?action=add"
                    class="bg-white hover:bg-gray-50 border rounded-xl p-6 flex items-center gap-4 transition">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">Add Item</div>
                        <div class="text-gray-500 text-sm">New menu item</div>
                    </div>
                </a>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white rounded-xl shadow-sm border">
                <div class="px-6 py-4 border-b flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Recent Orders</h2>
                    <a href="orders.php" class="text-red-600 hover:text-red-700 text-sm font-medium">View All →</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php if (empty($recentOrders)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                        No orders yet. They'll appear here when customers start ordering!
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 font-medium text-gray-900">
                                            <?= htmlspecialchars($order['order_number']) ?>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600">
                                            <?= htmlspecialchars($order['customer_name']) ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $order['order_type'] === 'delivery' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' ?>">
                                                <?= ucfirst($order['order_type']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 font-medium">$
                                            <?= number_format($order['total'], 2) ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php
                                            $statusColors = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'confirmed' => 'bg-blue-100 text-blue-800',
                                                'preparing' => 'bg-orange-100 text-orange-800',
                                                'ready' => 'bg-green-100 text-green-800',
                                                'delivered' => 'bg-gray-100 text-gray-800',
                                                'cancelled' => 'bg-red-100 text-red-800'
                                            ];
                                            $color = $statusColors[$order['order_status']] ?? 'bg-gray-100 text-gray-800';
                                            ?>
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $color ?>">
                                                <?= ucfirst($order['order_status']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 text-sm">
                                            <?= date('M j, g:i A', strtotime($order['created_at'])) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <?php if (isset($_GET['logout'])): ?>
        <script>
            window.location.href = 'login.php';
        </script>
        <?php logout(); ?>
    <?php endif; ?>

    <!-- Real-time notification -->
    <div id="newOrderNotif"
        class="fixed top-4 right-4 bg-green-600 text-white px-6 py-4 rounded-xl shadow-2xl z-50 hidden transform transition-all duration-300 translate-x-full">
        <div class="flex items-center gap-3">
            <span class="text-2xl">🔔</span>
            <div>
                <div class="font-bold">New Order!</div>
                <div class="text-sm" id="notifText"></div>
            </div>
        </div>
    </div>

    <audio id="notifySound" preload="auto">
        <source
            src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdH2Onp+YgG1lb3yLmZ+YiHdoaXiCipOUjYJ1bmhxfouWl4+FdWxqdHuIkpSNhHdyb3J6hI+TkYl7c29tc3yGjo+MhXlybHF3g42QjYR6dHBxdoKLj42Fenl0cnR7g4qMiYF5dXJzdnuEioyJgXl0cXR4foaKioN9eHRzdnqBho2LhH15dXN3fIOJjYqDfHh1c3Z9g4qNi4R9eHR0dn6EioyKg316dXN3fYOKjYqDfXl1c3d9g4qMioN9eXRzd32DioqKg315dXN3fYOKi4qDfXl1c3d9hIqLioN9eXV0d32EiouJg315dHN3fYOKi4mDfHl1c3h9hIqLiYN8eHVzeH2EiYqJg3x5dXN4fYSJiomDfHl1c3h9hImKiYN8eXVzeH2EiYqJg3x5dHN4fYSJiomDfHh1c3h9hImKiIN8eHV0eH2EiYqIg3x4dXR4fYSJioiDfHh1dHh9hImKiIN8eXV0eH2EiYqIg3x5dXR4fYSJioiDfHl1dHh9hImKiIN8eXV0eH2EiImHg3x5dXR5fYSIiYeDfHl1dHl9hIiJh4N8eXV0eX2EiImHg3x5dXR5fYSIiYeDfHl1dHl9hIiJh4N8eXV0eX2EiImHg3x5dXR5fYSIiYeDfHl1dHl9hIiJh4N8eXV0eX2EiImHg3x5dXR5fYSIiYeDfHl1dHl9hA=="
            type="audio/wav">
    </audio>

    <script>
        let lastOrderCount = <?= $pendingOrders['count'] ?>;

        async function checkNewOrders() {
            try {
                const response = await fetch('../api/orders.php?action=stats');
                const data = await response.json();

                if (data.success) {
                    const newPending = data.stats.pending + data.stats.confirmed + data.stats.preparing;

                    if (newPending > lastOrderCount) {
                        // New order arrived!
                        showNotification(newPending - lastOrderCount);
                    }

                    lastOrderCount = newPending;

                    // Update pending badge in sidebar
                    const badge = document.querySelector('.sidebar-pending-badge');
                    if (badge && newPending > 0) {
                        badge.textContent = newPending;
                        badge.classList.remove('hidden');
                    }
                }
            } catch (err) {
                console.log('Polling error:', err);
            }
        }

        function showNotification(count) {
            // Play sound
            document.getElementById('notifySound').play().catch(() => { });

            // Show in-page notification
            const notif = document.getElementById('newOrderNotif');
            document.getElementById('notifText').textContent = count + ' new order' + (count > 1 ? 's' : '') + '!';
            notif.classList.remove('hidden', 'translate-x-full');

            setTimeout(() => {
                notif.classList.add('translate-x-full');
                setTimeout(() => notif.classList.add('hidden'), 300);
            }, 5000);

            // Browser notification
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('New Order!', {
                    body: count + ' new order' + (count > 1 ? 's' : '') + ' received',
                    icon: '/favicon.ico'
                });
            }
        }

        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        // Poll every 10 seconds
        setInterval(checkNewOrders, 10000);
    </script>
</body>

</html>