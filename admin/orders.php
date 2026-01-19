<?php
/**
 * FoodFlow - Orders Management
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAuth();

$message = '';
$filter = $_GET['filter'] ?? 'all';

// Handle status update
if (isset($_POST['update_status'])) {
    $orderId = (int) $_POST['order_id'];
    $newStatus = $_POST['new_status'];

    $validStatuses = ['pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered', 'cancelled'];
    if (in_array($newStatus, $validStatuses)) {
        db()->update('orders', ['order_status' => $newStatus], 'id = :id', ['id' => $orderId]);
        $message = 'Order status updated!';
    }
}

// Filter queries
$whereClause = "1=1";
$params = [];

switch ($filter) {
    case 'pending':
        $whereClause = "order_status IN ('pending', 'confirmed')";
        break;
    case 'preparing':
        $whereClause = "order_status = 'preparing'";
        break;
    case 'ready':
        $whereClause = "order_status IN ('ready', 'out_for_delivery')";
        break;
    case 'completed':
        $whereClause = "order_status = 'delivered'";
        break;
    case 'cancelled':
        $whereClause = "order_status = 'cancelled'";
        break;
    case 'today':
        $whereClause = "DATE(created_at) = CURDATE()";
        break;
}

$orders = db()->fetchAll(
    "SELECT * FROM orders WHERE {$whereClause} ORDER BY created_at DESC LIMIT 100",
    $params
);

// Get order counts
$counts = db()->fetch(
    "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN order_status IN ('pending', 'confirmed') THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN order_status = 'preparing' THEN 1 ELSE 0 END) as preparing,
        SUM(CASE WHEN order_status IN ('ready', 'out_for_delivery') THEN 1 ELSE 0 END) as ready,
        SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as completed
     FROM orders WHERE DATE(created_at) = CURDATE()"
);

$storeName = getSetting('store_name', 'FoodFlow');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Karla:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Karla', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>


    <main class="lg:ml-64 min-h-screen">
        <header class="bg-white border-b px-6 py-4 sticky top-0 z-10">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Orders</h1>
                    <p class="text-gray-500 text-sm">Manage incoming orders</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="pos.php"
                        class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition">
                        💵 POS
                    </a>
                    <a href="kitchen.php"
                        class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium transition">
                        👨‍🍳 Kitchen
                    </a>
                    <button onclick="location.reload()" class="text-gray-500 hover:text-gray-700 p-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        <div class="p-6">
            <?php if ($message): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-6">
                    ✅
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <a href="orders.php?filter=pending"
                    class="bg-white rounded-xl p-4 border <?= $filter === 'pending' ? 'border-red-500 ring-2 ring-red-100' : '' ?>">
                    <div class="text-2xl font-bold text-yellow-600">
                        <?= $counts['pending'] ?? 0 ?>
                    </div>
                    <div class="text-sm text-gray-500">Pending</div>
                </a>
                <a href="orders.php?filter=preparing"
                    class="bg-white rounded-xl p-4 border <?= $filter === 'preparing' ? 'border-red-500 ring-2 ring-red-100' : '' ?>">
                    <div class="text-2xl font-bold text-orange-600">
                        <?= $counts['preparing'] ?? 0 ?>
                    </div>
                    <div class="text-sm text-gray-500">Preparing</div>
                </a>
                <a href="orders.php?filter=ready"
                    class="bg-white rounded-xl p-4 border <?= $filter === 'ready' ? 'border-red-500 ring-2 ring-red-100' : '' ?>">
                    <div class="text-2xl font-bold text-green-600">
                        <?= $counts['ready'] ?? 0 ?>
                    </div>
                    <div class="text-sm text-gray-500">Ready</div>
                </a>
                <a href="orders.php?filter=completed"
                    class="bg-white rounded-xl p-4 border <?= $filter === 'completed' ? 'border-red-500 ring-2 ring-red-100' : '' ?>">
                    <div class="text-2xl font-bold text-gray-600">
                        <?= $counts['completed'] ?? 0 ?>
                    </div>
                    <div class="text-sm text-gray-500">Completed</div>
                </a>
                <a href="orders.php?filter=all"
                    class="bg-white rounded-xl p-4 border <?= $filter === 'all' ? 'border-red-500 ring-2 ring-red-100' : '' ?>">
                    <div class="text-2xl font-bold text-blue-600">
                        <?= $counts['total'] ?? 0 ?>
                    </div>
                    <div class="text-sm text-gray-500">Today Total</div>
                </a>
            </div>

            <!-- Orders Table -->
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                        No orders found. New orders will appear here!
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-gray-900">
                                                <?= htmlspecialchars($order['order_number']) ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-gray-900">
                                                <?= htmlspecialchars($order['customer_name']) ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?= htmlspecialchars($order['customer_phone']) ?>
                                            </div>
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
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="update_status" value="1">
                                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                <select name="new_status" onchange="this.form.submit()"
                                                    class="text-sm border rounded-lg px-2 py-1 focus:ring-2 focus:ring-red-500">
                                                    <option value="pending" <?= $order['order_status'] === 'pending' ? 'selected' : '' ?>>⏳ Pending</option>
                                                    <option value="confirmed" <?= $order['order_status'] === 'confirmed' ? 'selected' : '' ?>>✅ Confirmed</option>
                                                    <option value="preparing" <?= $order['order_status'] === 'preparing' ? 'selected' : '' ?>>🍳 Preparing</option>
                                                    <option value="ready" <?= $order['order_status'] === 'ready' ? 'selected' : '' ?>>📦 Ready</option>
                                                    <?php if ($order['order_type'] === 'delivery'): ?>
                                                        <option value="out_for_delivery"
                                                            <?= $order['order_status'] === 'out_for_delivery' ? 'selected' : '' ?>>🚗
                                                            Out for Delivery</option>
                                                    <?php endif; ?>
                                                    <option value="delivered" <?= $order['order_status'] === 'delivered' ? 'selected' : '' ?>>✓ Delivered</option>
                                                    <option value="cancelled" <?= $order['order_status'] === 'cancelled' ? 'selected' : '' ?>>❌ Cancelled</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 text-sm">
                                            <?= date('M j, g:i A', strtotime($order['created_at'])) ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <button onclick="viewOrder(<?= $order['id'] ?>)"
                                                class="text-blue-600 hover:text-blue-800">View</button>
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

    <!-- Order Details Modal -->
    <div id="orderModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-2xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold">Order Details</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <div id="orderDetails">Loading...</div>
            </div>
        </div>
    </div>

    <script>
        function viewOrder(orderId) {
            document.getElementById('orderModal').classList.remove('hidden');
            fetch('../api/order.php?id=' + orderId)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const o = data.order;
                        let html = `
                            <div class="space-y-4">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Order #</span>
                                    <span class="font-medium">${o.order_number}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Customer</span>
                                    <span class="font-medium">${o.customer_name}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Type</span>
                                    <span class="font-medium">${o.order_type}</span>
                                </div>
                                <hr>
                                <div class="font-medium">Items</div>
                                ${o.items.map(i => `
                                    <div class="flex justify-between text-sm">
                                        <span>${i.quantity}x ${i.item_name}</span>
                                        <span>$${parseFloat(i.subtotal).toFixed(2)}</span>
                                    </div>
                                `).join('')}
                                <hr>
                                <div class="flex justify-between font-bold text-lg">
                                    <span>Total</span>
                                    <span>$${parseFloat(o.total).toFixed(2)}</span>
                                </div>
                            </div>
                        `;
                        document.getElementById('orderDetails').innerHTML = html;
                    }
                });
        }

        function closeModal() {
            document.getElementById('orderModal').classList.add('hidden');
        }

        // Auto-refresh every 30 seconds
        setInterval(() => location.reload(), 30000);
    </script>
</body>

</html>