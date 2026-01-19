<?php
/**
 * FoodFlow - Kitchen Display System
 * Real-time order display for kitchen staff
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAuth();

$storeName = getSetting('store_name', 'FoodFlow');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Display -
        <?= htmlspecialchars($storeName) ?>
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Karla:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Karla', sans-serif;
        }

        .order-card {
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
        }

        .status-pending {
            border-left: 6px solid #EAB308;
        }

        .status-confirmed {
            border-left: 6px solid #3B82F6;
        }

        .status-preparing {
            border-left: 6px solid #F97316;
        }

        .status-ready {
            border-left: 6px solid #22C55E;
        }

        .blink {
            animation: blink 1s ease-in-out infinite;
        }

        @keyframes blink {
            50% {
                opacity: 0.5;
            }
        }

        .timer-warning {
            color: #F97316;
        }

        .timer-critical {
            color: #EF4444;
            animation: blink 0.5s ease-in-out infinite;
        }
    </style>
</head>

<body class="bg-gray-900 text-white min-h-screen">
    <!-- Header -->
    <header class="bg-gray-800 px-6 py-4 flex items-center justify-between sticky top-0 z-10">
        <div class="flex items-center gap-4">
            <a href="index.php" class="text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-orange-600 rounded-lg flex items-center justify-center">
                    <span class="text-xl">👨‍🍳</span>
                </div>
                <div>
                    <div class="font-bold">Kitchen Display</div>
                    <div class="text-sm text-gray-400" id="currentTime"></div>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-right">
                <div class="text-2xl font-bold" id="activeCount">0</div>
                <div class="text-sm text-gray-400">Active Orders</div>
            </div>
            <a href="pos.php" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg font-medium">
                POS
            </a>
        </div>
    </header>

    <!-- Status Legend -->
    <div class="bg-gray-800 px-6 py-3 flex gap-6 border-b border-gray-700">
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
            <span class="text-sm">Pending</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-blue-500"></span>
            <span class="text-sm">Confirmed</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-orange-500"></span>
            <span class="text-sm">Preparing</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-green-500"></span>
            <span class="text-sm">Ready</span>
        </div>
    </div>

    <!-- Orders Grid -->
    <main class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="ordersGrid">
            <!-- Orders will be loaded here -->
        </div>

        <div id="noOrders" class="hidden text-center py-20">
            <div class="text-6xl mb-4">🎉</div>
            <div class="text-xl text-gray-400">All orders completed!</div>
            <div class="text-gray-500">Waiting for new orders...</div>
        </div>
    </main>

    <!-- Audio notification -->
    <audio id="newOrderSound" preload="auto">
        <source
            src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdH2Onp+YgG1lb3yLmZ+YiHdoaXiCipOUjYJ1bmhxfouWl4+FdWxqdHuIkpSNhHdyb3J6hI+TkYl7c29tc3yGjo+MhXlybHF3g42QjYR6dHBxdoKLj42Fenl0cnR7g4qMiYF5dXJzdnuEioyJgXl0cXR4foaKioN9eHRzdnqBho2LhH15dXN3fIOJjYqDfHh1c3Z9g4qNi4R9eHR0dn6EioyKg316dXN3fYOKjYqDfXl1c3d9g4qMioN9eXRzd32DioqKg315dXN3fYOKi4qDfXl1c3d9hIqLioN9eXV0d32EiouJg315dHN3fYOKi4mDfHl1c3h9hIqLiYN8eHVzeH2EiYqJg3x5dXN4fYSJiomDfHl1c3h9hImKiYN8eXVzeH2EiYqJg3x5dHN4fYSJiomDfHh1c3h9hImKiIN8eHV0eH2EiYqIg3x4dXR4fYSJioiDfHh1dHh9hImKiIN8eXV0eH2EiYqIg3x5dXR4fYSJioiDfHl1dHh9hImKiIN8eXV0eH2EiImHg3x5dXR5fYSIiYeDfHl1dHl9hIiJh4N8eXV0eX2EiImHg3x5dXR5fYSIiYeDfHl1dHl9hIiJh4N8eXV0eX2EiImHg3x5dXR5fYSIiYeDfHl1dHl9hIiJh4N8eXV0eX2EiImHg3x5dXR5fYSIiYeDfHl1dHl9hA=="
            type="audio/wav">
    </audio>

    <script>
        let orders = [];
        let lastOrderCount = 0;

        // Update time
        function updateTime() {
            const now = new Date();
            document.getElementById('currentTime').textContent = now.toLocaleTimeString();
        }
        setInterval(updateTime, 1000);
        updateTime();

        // Calculate time elapsed
        function getElapsedTime(createdAt) {
            const created = new Date(createdAt);
            const now = new Date();
            const diff = Math.floor((now - created) / 1000 / 60); // minutes
            return diff;
        }

        function getTimerClass(minutes) {
            if (minutes >= 20) return 'timer-critical';
            if (minutes >= 10) return 'timer-warning';
            return '';
        }

        // Render orders
        function renderOrders() {
            const grid = document.getElementById('ordersGrid');
            const noOrders = document.getElementById('noOrders');

            if (orders.length === 0) {
                grid.innerHTML = '';
                noOrders.classList.remove('hidden');
                document.getElementById('activeCount').textContent = '0';
                return;
            }

            noOrders.classList.add('hidden');
            document.getElementById('activeCount').textContent = orders.length;

            grid.innerHTML = orders.map(order => {
                const elapsed = getElapsedTime(order.created_at);
                const timerClass = getTimerClass(elapsed);

                return `
                    <div class="order-card bg-gray-800 rounded-xl overflow-hidden status-${order.order_status}">
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <div class="text-xl font-bold">#${order.order_number}</div>
                                    <div class="text-sm text-gray-400">${order.customer_name}</div>
                                </div>
                                <div class="text-right">
                                    <div class="${timerClass} text-lg font-bold">${elapsed}m</div>
                                    <div class="text-xs px-2 py-1 rounded ${getStatusBg(order.order_status)}">
                                        ${order.order_status.toUpperCase()}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-2 text-sm">
                                <span class="${order.order_type === 'delivery' ? 'text-blue-400' : 'text-green-400'}">
                                    ${order.order_type === 'delivery' ? '🚗 Delivery' : '📦 Pickup'}
                                </span>
                            </div>
                            
                            <div class="border-t border-gray-700 pt-3 space-y-1">
                                ${(order.items || []).map(item => `
                                    <div class="flex justify-between">
                                        <span class="font-bold text-lg">${item.quantity}x</span>
                                        <span class="flex-1 ml-2">${item.item_name}</span>
                                    </div>
                                `).join('')}
                            </div>
                            
                            ${order.special_instructions ? `
                                <div class="mt-3 p-2 bg-yellow-900/50 rounded text-sm">
                                    ⚠️ ${order.special_instructions}
                                </div>
                            ` : ''}
                        </div>
                        
                        <div class="grid grid-cols-2 border-t border-gray-700">
                            ${getActionButtons(order)}
                        </div>
                    </div>
                `;
            }).join('');
        }

        function getStatusBg(status) {
            switch (status) {
                case 'pending': return 'bg-yellow-600';
                case 'confirmed': return 'bg-blue-600';
                case 'preparing': return 'bg-orange-600';
                case 'ready': return 'bg-green-600';
                default: return 'bg-gray-600';
            }
        }

        function getActionButtons(order) {
            switch (order.order_status) {
                case 'pending':
                    return `
                        <button onclick="updateStatus(${order.id}, 'confirmed')" class="py-3 bg-blue-600 hover:bg-blue-700 font-medium">✓ Confirm</button>
                        <button onclick="updateStatus(${order.id}, 'cancelled')" class="py-3 bg-gray-700 hover:bg-gray-600 font-medium">✕ Cancel</button>
                    `;
                case 'confirmed':
                    return `
                        <button onclick="updateStatus(${order.id}, 'preparing')" class="py-3 bg-orange-600 hover:bg-orange-700 font-medium col-span-2">🍳 Start Preparing</button>
                    `;
                case 'preparing':
                    return `
                        <button onclick="updateStatus(${order.id}, 'ready')" class="py-3 bg-green-600 hover:bg-green-700 font-medium col-span-2">✓ Mark Ready</button>
                    `;
                case 'ready':
                    return `
                        <button onclick="updateStatus(${order.id}, 'delivered')" class="py-3 bg-gray-600 hover:bg-gray-700 font-medium col-span-2">✓ Complete</button>
                    `;
                default:
                    return '';
            }
        }

        async function updateStatus(orderId, status) {
            try {
                await fetch('../api/orders.php?action=update_status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_id: orderId, status })
                });
                loadOrders();
            } catch (err) {
                console.error(err);
            }
        }

        async function loadOrders() {
            try {
                const response = await fetch('../api/orders.php?action=pending');
                const data = await response.json();

                if (data.success) {
                    const newCount = data.orders.length;

                    // Play sound for new orders
                    if (newCount > lastOrderCount) {
                        document.getElementById('newOrderSound').play().catch(() => { });

                        // Show notification
                        if (Notification.permission === 'granted') {
                            new Notification('New Order!', { body: 'A new order has arrived' });
                        }
                    }

                    lastOrderCount = newCount;
                    orders = data.orders;
                    renderOrders();
                }
            } catch (err) {
                console.error('Failed to load orders:', err);
            }
        }

        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        // Initial load and polling
        loadOrders();
        setInterval(loadOrders, 5000); // Poll every 5 seconds
    </script>
</body>

</html>