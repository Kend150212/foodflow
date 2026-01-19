<?php
/**
 * FoodFlow - Kitchen Display System with Tabs
 * Real-time order display for kitchen staff
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAuth();

// Handle status updates FIRST before any output
if (isset($_GET['action']) && $_GET['action'] === 'status') {
    $orderId = (int) $_GET['id'];
    $status = $_GET['status'];
    $validStatuses = ['pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered', 'cancelled'];

    if ($orderId && in_array($status, $validStatuses)) {
        db()->update('orders', ['order_status' => $status], 'id = :id', ['id' => $orderId]);
    }

    header('Location: kitchen.php?tab=' . ($_GET['tab'] ?? 'pending'));
    exit;
}

$storeName = getSetting('store_name', 'FoodFlow');
$activeTab = $_GET['tab'] ?? 'pending';

// Get orders based on tab
$tabs = [
    'pending' => ['title' => '⏳ Pending', 'statuses' => ['pending', 'confirmed']],
    'preparing' => ['title' => '🍳 Preparing', 'statuses' => ['preparing']],
    'ready' => ['title' => '✅ Ready', 'statuses' => ['ready']],
    'all' => ['title' => '📋 All Active', 'statuses' => ['pending', 'confirmed', 'preparing', 'ready']],
    'upcoming' => ['title' => '📅 Scheduled', 'statuses' => []]
];

// Build query based on tab
if ($activeTab === 'upcoming') {
    $orders = db()->fetchAll(
        "SELECT * FROM orders 
         WHERE scheduled_time IS NOT NULL 
         AND scheduled_time > NOW()
         AND order_status NOT IN ('delivered', 'cancelled')
         ORDER BY scheduled_time ASC"
    );
} else {
    $statuses = $tabs[$activeTab]['statuses'] ?? ['pending'];
    $placeholders = implode(',', array_fill(0, count($statuses), '?'));
    $orders = db()->fetchAll(
        "SELECT * FROM orders 
         WHERE order_status IN ({$placeholders})
         AND DATE(created_at) >= CURDATE() - INTERVAL 1 DAY
         ORDER BY 
            FIELD(order_status, 'pending', 'confirmed', 'preparing', 'ready'),
            created_at ASC",
        $statuses
    );
}

// Get items for each order
foreach ($orders as &$order) {
    $order['items'] = db()->fetchAll(
        "SELECT * FROM order_items WHERE order_id = ?",
        [$order['id']]
    );
}

// Count for badges
$counts = db()->fetch(
    "SELECT 
        SUM(CASE WHEN order_status IN ('pending', 'confirmed') THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN order_status = 'preparing' THEN 1 ELSE 0 END) as preparing,
        SUM(CASE WHEN order_status = 'ready' THEN 1 ELSE 0 END) as ready
     FROM orders 
     WHERE DATE(created_at) >= CURDATE() - INTERVAL 1 DAY
     AND order_status NOT IN ('delivered', 'cancelled')"
) ?? ['pending' => 0, 'preparing' => 0, 'ready' => 0];

function getStatusBg($status)
{
    switch ($status) {
        case 'pending':
            return 'bg-yellow-600';
        case 'confirmed':
            return 'bg-blue-600';
        case 'preparing':
            return 'bg-orange-600';
        case 'ready':
            return 'bg-green-600';
        default:
            return 'bg-gray-600';
    }
}

function getActionButtons($order)
{
    $tab = $_GET['tab'] ?? 'pending';
    switch ($order['order_status']) {
        case 'pending':
            return '<a href="?action=status&id=' . $order['id'] . '&status=confirmed&tab=' . $tab . '" class="py-3 bg-blue-600 hover:bg-blue-700 font-medium text-center">✓ Confirm</a>
                    <a href="?action=status&id=' . $order['id'] . '&status=cancelled&tab=' . $tab . '" class="py-3 bg-gray-700 hover:bg-gray-600 font-medium text-center">✕ Cancel</a>';
        case 'confirmed':
            return '<a href="?action=status&id=' . $order['id'] . '&status=preparing&tab=' . $tab . '" class="py-3 bg-orange-600 hover:bg-orange-700 font-medium text-center col-span-2">🍳 Start Preparing</a>';
        case 'preparing':
            return '<a href="?action=status&id=' . $order['id'] . '&status=ready&tab=' . $tab . '" class="py-3 bg-green-600 hover:bg-green-700 font-medium text-center col-span-2">✓ Mark Ready</a>';
        case 'ready':
            return '<a href="print.php?id=' . $order['id'] . '" target="_blank" class="py-3 bg-gray-600 hover:bg-gray-700 font-medium text-center">🖨️ Print</a>
                    <a href="?action=status&id=' . $order['id'] . '&status=delivered&tab=' . $tab . '" class="py-3 bg-green-600 hover:bg-green-700 font-medium text-center">✓ Complete</a>';
        default:
            return '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Display - <?= htmlspecialchars($storeName) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Karla:wght@400;500;600;700&display=swap" rel="stylesheet">
    <meta http-equiv="refresh" content="30">
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

        .tab-active {
            background: #DC2626;
            color: white;
        }

        .timer-warning {
            color: #F97316;
        }

        .timer-critical {
            color: #EF4444;
            font-weight: bold;
        }
    </style>
</head>

<body class="bg-gray-900 text-white min-h-screen">
    <!-- Header -->
    <header class="bg-gray-800 px-4 py-3 flex items-center justify-between sticky top-0 z-10">
        <div class="flex items-center gap-3">
            <a href="index.php" class="text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div class="w-8 h-8 bg-orange-600 rounded-lg flex items-center justify-center">
                <span class="text-lg">👨‍🍳</span>
            </div>
            <span class="font-bold">Kitchen Display</span>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-gray-400 text-sm" id="clock"></span>
            <a href="pos.php" class="bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded-lg text-sm font-medium">POS</a>
        </div>
    </header>

    <!-- Tabs -->
    <div class="bg-gray-800 px-4 py-2 flex gap-2 overflow-x-auto border-b border-gray-700">
        <?php foreach ($tabs as $key => $tab): ?>
            <a href="?tab=<?= $key ?>"
                class="px-4 py-2 rounded-lg font-medium whitespace-nowrap transition <?= $activeTab === $key ? 'tab-active' : 'bg-gray-700 hover:bg-gray-600' ?>">
                <?= $tab['title'] ?>
                <?php if (isset($counts[$key]) && $counts[$key] > 0): ?>
                    <span class="ml-1 bg-white/20 px-2 py-0.5 rounded-full text-xs"><?= $counts[$key] ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Orders Grid -->
    <main class="p-4">
        <?php if (empty($orders)): ?>
            <div class="text-center py-20">
                <div class="text-6xl mb-4"><?= $activeTab === 'upcoming' ? '📅' : '🎉' ?></div>
                <div class="text-xl text-gray-400">
                    <?= $activeTab === 'upcoming' ? 'No scheduled orders' : 'No orders in this queue!' ?>
                </div>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <?php foreach ($orders as $order):
                    $created = strtotime($order['created_at']);
                    $elapsed = floor((time() - $created) / 60);
                    $timerClass = $elapsed >= 20 ? 'timer-critical' : ($elapsed >= 10 ? 'timer-warning' : '');
                    ?>
                    <div class="order-card bg-gray-800 rounded-xl overflow-hidden status-<?= $order['order_status'] ?>">
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <div class="text-xl font-bold">#<?= htmlspecialchars($order['order_number']) ?></div>
                                    <div class="text-sm text-gray-400"><?= htmlspecialchars($order['customer_name']) ?></div>
                                </div>
                                <div class="text-right">
                                    <div class="<?= $timerClass ?> text-lg font-bold"><?= $elapsed ?>m</div>
                                    <div class="text-xs px-2 py-1 rounded <?= getStatusBg($order['order_status']) ?>">
                                        <?= strtoupper($order['order_status']) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-2 text-sm">
                                <span class="<?= $order['order_type'] === 'delivery' ? 'text-blue-400' : 'text-green-400' ?>">
                                    <?= $order['order_type'] === 'delivery' ? '🚗 Delivery' : '📦 Pickup' ?>
                                </span>
                                <?php if ($order['payment_method'] === 'cash'): ?>
                                    <span class="ml-2 text-yellow-400">💵 Cash - $<?= number_format($order['total'], 2) ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="border-t border-gray-700 pt-3 space-y-1 max-h-40 overflow-y-auto">
                                <?php foreach ($order['items'] as $item): ?>
                                    <div class="flex gap-2">
                                        <span class="font-bold text-lg"><?= $item['quantity'] ?>x</span>
                                        <span><?= htmlspecialchars($item['item_name']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if (!empty($order['special_instructions'])): ?>
                                <div class="mt-3 p-2 bg-yellow-900/50 rounded text-sm">
                                    ⚠️ <?= htmlspecialchars($order['special_instructions']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="grid grid-cols-2 border-t border-gray-700">
                            <?= getActionButtons($order) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script>
        function updateClock() {
            document.getElementById('clock').textContent = new Date().toLocaleTimeString();
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>

</html>