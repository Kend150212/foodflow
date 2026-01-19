<?php
/**
 * FoodFlow - Professional Reports & Analytics Dashboard
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAuth();

$storeName = getSetting('store_name', 'FoodFlow');

// Date range filter
$dateRange = $_GET['range'] ?? 'today';
$customFrom = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
$customTo = $_GET['to'] ?? date('Y-m-d');

switch ($dateRange) {
    case 'today':
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
        $compareStart = date('Y-m-d', strtotime('-1 day'));
        $compareEnd = date('Y-m-d', strtotime('-1 day'));
        break;
    case 'yesterday':
        $startDate = date('Y-m-d', strtotime('-1 day'));
        $endDate = date('Y-m-d', strtotime('-1 day'));
        $compareStart = date('Y-m-d', strtotime('-2 days'));
        $compareEnd = date('Y-m-d', strtotime('-2 days'));
        break;
    case 'week':
        $startDate = date('Y-m-d', strtotime('-7 days'));
        $endDate = date('Y-m-d');
        $compareStart = date('Y-m-d', strtotime('-14 days'));
        $compareEnd = date('Y-m-d', strtotime('-7 days'));
        break;
    case 'month':
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-d');
        $compareStart = date('Y-m-01', strtotime('-1 month'));
        $compareEnd = date('Y-m-t', strtotime('-1 month'));
        break;
    case 'year':
        $startDate = date('Y-01-01');
        $endDate = date('Y-m-d');
        $compareStart = date('Y-01-01', strtotime('-1 year'));
        $compareEnd = date('Y-m-d', strtotime('-1 year'));
        break;
    case 'custom':
        $startDate = $customFrom;
        $endDate = $customTo;
        $diff = (strtotime($endDate) - strtotime($startDate)) / 86400;
        $compareStart = date('Y-m-d', strtotime("-{$diff} days", strtotime($startDate)));
        $compareEnd = date('Y-m-d', strtotime('-1 day', strtotime($startDate)));
        break;
    default:
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
}

// Main metrics
$mainStats = db()->fetch(
    "SELECT 
        COUNT(*) as total_orders,
        COALESCE(SUM(total), 0) as total_revenue,
        COALESCE(AVG(total), 0) as avg_order_value,
        COALESCE(SUM(tip_amount), 0) as total_tips,
        COALESCE(SUM(CASE WHEN order_status = 'cancelled' THEN 1 ELSE 0 END), 0) as cancelled_orders
     FROM orders 
     WHERE DATE(created_at) BETWEEN ? AND ?
     AND order_status != 'cancelled'",
    [$startDate, $endDate]
);

// Compare period stats
$compareStats = db()->fetch(
    "SELECT COUNT(*) as total_orders, COALESCE(SUM(total), 0) as total_revenue
     FROM orders 
     WHERE DATE(created_at) BETWEEN ? AND ?
     AND order_status != 'cancelled'",
    [$compareStart, $compareEnd]
);

// Revenue change percentage
$revenueChange = $compareStats['total_revenue'] > 0
    ? (($mainStats['total_revenue'] - $compareStats['total_revenue']) / $compareStats['total_revenue']) * 100
    : 0;
$orderChange = $compareStats['total_orders'] > 0
    ? (($mainStats['total_orders'] - $compareStats['total_orders']) / $compareStats['total_orders']) * 100
    : 0;

// Payment methods breakdown
$paymentStats = db()->fetchAll(
    "SELECT payment_method, COUNT(*) as count, SUM(total) as revenue
     FROM orders 
     WHERE DATE(created_at) BETWEEN ? AND ?
     AND order_status != 'cancelled'
     GROUP BY payment_method
     ORDER BY revenue DESC",
    [$startDate, $endDate]
);

// Order types breakdown
$orderTypeStats = db()->fetchAll(
    "SELECT order_type, COUNT(*) as count, SUM(total) as revenue
     FROM orders 
     WHERE DATE(created_at) BETWEEN ? AND ?
     AND order_status != 'cancelled'
     GROUP BY order_type
     ORDER BY count DESC",
    [$startDate, $endDate]
);

// Order status breakdown
$statusStats = db()->fetchAll(
    "SELECT order_status, COUNT(*) as count
     FROM orders 
     WHERE DATE(created_at) BETWEEN ? AND ?
     GROUP BY order_status
     ORDER BY FIELD(order_status, 'pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled')",
    [$startDate, $endDate]
);

// Daily revenue chart data (last 7 days or custom range)
$dailyRevenue = db()->fetchAll(
    "SELECT DATE(created_at) as date, 
            COUNT(*) as orders, 
            SUM(total) as revenue
     FROM orders 
     WHERE DATE(created_at) BETWEEN ? AND ?
     AND order_status != 'cancelled'
     GROUP BY DATE(created_at)
     ORDER BY date ASC",
    [$startDate, $endDate]
);

// Top selling items
$topItems = db()->fetchAll(
    "SELECT oi.item_name, 
            SUM(oi.quantity) as total_qty, 
            SUM(oi.subtotal) as total_revenue
     FROM order_items oi
     JOIN orders o ON oi.order_id = o.id
     WHERE DATE(o.created_at) BETWEEN ? AND ?
     AND o.order_status != 'cancelled'
     GROUP BY oi.item_name
     ORDER BY total_qty DESC
     LIMIT 10",
    [$startDate, $endDate]
);

// Peak hours
$peakHours = db()->fetchAll(
    "SELECT HOUR(created_at) as hour, COUNT(*) as orders, SUM(total) as revenue
     FROM orders 
     WHERE DATE(created_at) BETWEEN ? AND ?
     AND order_status != 'cancelled'
     GROUP BY HOUR(created_at)
     ORDER BY hour",
    [$startDate, $endDate]
);

// Recent orders
$recentOrders = db()->fetchAll(
    "SELECT * FROM orders 
     WHERE DATE(created_at) BETWEEN ? AND ?
     ORDER BY created_at DESC
     LIMIT 10",
    [$startDate, $endDate]
);

$paymentLabels = ['stripe' => '💳 Card', 'cash' => '💵 Cash', 'paypal' => 'PayPal', 'venmo' => 'Venmo', 'cashapp' => 'CashApp'];
$orderTypeLabels = ['delivery' => '🚗 Delivery', 'pickup' => '📦 Pickup'];
$statusColors = [
    'pending' => 'bg-yellow-100 text-yellow-800',
    'confirmed' => 'bg-blue-100 text-blue-800',
    'preparing' => 'bg-orange-100 text-orange-800',
    'ready' => 'bg-green-100 text-green-800',
    'delivered' => 'bg-gray-100 text-gray-800',
    'cancelled' => 'bg-red-100 text-red-800'
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics -
        <?= htmlspecialchars($storeName) ?>
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <!-- Header -->
        <header class="bg-white border-b px-6 py-4 sticky top-0 z-10">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">📊 Reports & Analytics</h1>
                    <p class="text-gray-500 text-sm">
                        <?= date('M j', strtotime($startDate)) ?> -
                        <?= date('M j, Y', strtotime($endDate)) ?>
                    </p>
                </div>

                <!-- Date Range Filter -->
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="?range=today"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium <?= $dateRange === 'today' ? 'bg-red-600 text-white' : 'bg-gray-100 hover:bg-gray-200' ?>">Today</a>
                    <a href="?range=yesterday"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium <?= $dateRange === 'yesterday' ? 'bg-red-600 text-white' : 'bg-gray-100 hover:bg-gray-200' ?>">Yesterday</a>
                    <a href="?range=week"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium <?= $dateRange === 'week' ? 'bg-red-600 text-white' : 'bg-gray-100 hover:bg-gray-200' ?>">7
                        Days</a>
                    <a href="?range=month"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium <?= $dateRange === 'month' ? 'bg-red-600 text-white' : 'bg-gray-100 hover:bg-gray-200' ?>">This
                        Month</a>
                    <a href="?range=year"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium <?= $dateRange === 'year' ? 'bg-red-600 text-white' : 'bg-gray-100 hover:bg-gray-200' ?>">This
                        Year</a>
                </div>
            </div>
        </header>

        <div class="p-6 space-y-6">
            <!-- Main Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Revenue Card -->
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Revenue</p>
                            <p class="text-3xl font-bold text-gray-900">$
                                <?= number_format($mainStats['total_revenue'], 2) ?>
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                            <span class="text-2xl">💰</span>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span class="<?= $revenueChange >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $revenueChange >= 0 ? '↑' : '↓' ?>
                            <?= abs(round($revenueChange, 1)) ?>%
                        </span>
                        <span class="text-gray-500 ml-2">vs previous period</span>
                    </div>
                </div>

                <!-- Orders Card -->
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Orders</p>
                            <p class="text-3xl font-bold text-gray-900">
                                <?= number_format($mainStats['total_orders']) ?>
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <span class="text-2xl">📦</span>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span class="<?= $orderChange >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $orderChange >= 0 ? '↑' : '↓' ?>
                            <?= abs(round($orderChange, 1)) ?>%
                        </span>
                        <span class="text-gray-500 ml-2">vs previous period</span>
                    </div>
                </div>

                <!-- Avg Order Value -->
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Avg Order Value</p>
                            <p class="text-3xl font-bold text-gray-900">$
                                <?= number_format($mainStats['avg_order_value'], 2) ?>
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                            <span class="text-2xl">📊</span>
                        </div>
                    </div>
                    <div class="mt-4 text-sm text-gray-500">
                        Total tips: $
                        <?= number_format($mainStats['total_tips'], 2) ?>
                    </div>
                </div>

                <!-- Cancelled Orders -->
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Cancelled</p>
                            <p class="text-3xl font-bold text-gray-900">
                                <?= $mainStats['cancelled_orders'] ?>
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                            <span class="text-2xl">❌</span>
                        </div>
                    </div>
                    <div class="mt-4 text-sm text-gray-500">
                        <?php
                        $cancelRate = $mainStats['total_orders'] > 0
                            ? round(($mainStats['cancelled_orders'] / ($mainStats['total_orders'] + $mainStats['cancelled_orders'])) * 100, 1)
                            : 0;
                        ?>
                        Cancel rate:
                        <?= $cancelRate ?>%
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Revenue Chart -->
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h3 class="font-bold text-gray-900 mb-4">📈 Revenue Trend</h3>
                    <canvas id="revenueChart" height="200"></canvas>
                </div>

                <!-- Payment Methods Pie -->
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h3 class="font-bold text-gray-900 mb-4">💳 Payment Methods</h3>
                    <div class="flex items-center justify-center">
                        <canvas id="paymentChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Order Details Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Order Types -->
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h3 class="font-bold text-gray-900 mb-4">🚗 Order Types</h3>
                    <div class="space-y-3">
                        <?php foreach ($orderTypeStats as $stat): ?>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span>
                                        <?= $orderTypeLabels[$stat['order_type']] ?? ucfirst($stat['order_type']) ?>
                                    </span>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold">
                                        <?= $stat['count'] ?> orders
                                    </div>
                                    <div class="text-sm text-gray-500">$
                                        <?= number_format($stat['revenue'], 2) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($orderTypeStats)): ?>
                            <p class="text-gray-500 text-center py-4">No orders in this period</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Order Status -->
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h3 class="font-bold text-gray-900 mb-4">📋 Order Status</h3>
                    <div class="space-y-3">
                        <?php foreach ($statusStats as $stat): ?>
                            <div class="flex items-center justify-between">
                                <span
                                    class="px-2 py-1 rounded text-xs font-medium <?= $statusColors[$stat['order_status']] ?>">
                                    <?= ucfirst($stat['order_status']) ?>
                                </span>
                                <span class="font-bold">
                                    <?= $stat['count'] ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Peak Hours -->
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h3 class="font-bold text-gray-900 mb-4">⏰ Peak Hours</h3>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        <?php
                        usort($peakHours, function ($a, $b) {
                            return $b['orders'] - $a['orders'];
                        });
                        $topPeakHours = array_slice($peakHours, 0, 6);
                        foreach ($topPeakHours as $stat):
                            $hour = (int) $stat['hour'];
                            $ampm = $hour >= 12 ? 'PM' : 'AM';
                            $displayHour = $hour % 12 ?: 12;
                            ?>
                            <div class="flex items-center justify-between">
                                <span class="text-sm">
                                    <?= $displayHour ?>:00
                                    <?= $ampm ?>
                                </span>
                                <div class="text-right">
                                    <span class="font-bold">
                                        <?= $stat['orders'] ?> orders
                                    </span>
                                    <span class="text-gray-500 text-xs ml-2">$
                                        <?= number_format($stat['revenue'], 0) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Top Items & Recent Orders -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Selling Items -->
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h3 class="font-bold text-gray-900 mb-4">🔥 Top Selling Items</h3>
                    <div class="space-y-3">
                        <?php foreach ($topItems as $i => $item): ?>
                            <div class="flex items-center gap-3">
                                <span
                                    class="w-6 h-6 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-xs font-bold">
                                    <?= $i + 1 ?>
                                </span>
                                <div class="flex-1">
                                    <div class="font-medium">
                                        <?= htmlspecialchars($item['item_name']) ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?= $item['total_qty'] ?> sold
                                    </div>
                                </div>
                                <div class="font-bold text-green-600">$
                                    <?= number_format($item['total_revenue'], 2) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($topItems)): ?>
                            <p class="text-gray-500 text-center py-4">No items sold in this period</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h3 class="font-bold text-gray-900 mb-4">🕐 Recent Orders</h3>
                    <div class="space-y-3 max-h-80 overflow-y-auto">
                        <?php foreach ($recentOrders as $order): ?>
                            <div class="flex items-center justify-between py-2 border-b last:border-0">
                                <div>
                                    <div class="font-medium">#
                                        <?= htmlspecialchars($order['order_number']) ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?= date('M j, g:i A', strtotime($order['created_at'])) ?>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold">$
                                        <?= number_format($order['total'], 2) ?>
                                    </div>
                                    <span class="text-xs px-2 py-0.5 rounded <?= $statusColors[$order['order_status']] ?>">
                                        <?= ucfirst($order['order_status']) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_map(function ($d) {
                    return date('M j', strtotime($d['date']));
                }, $dailyRevenue)) ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?= json_encode(array_column($dailyRevenue, 'revenue')) ?>,
                    borderColor: '#DC2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.1)',
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Orders',
                    data: <?= json_encode(array_column($dailyRevenue, 'orders')) ?>,
                    borderColor: '#3B82F6',
                    backgroundColor: 'transparent',
                    yAxisID: 'y1',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                interaction: { intersect: false },
                scales: {
                    y: { beginAtZero: true, position: 'left' },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } }
                }
            }
        });

        // Payment Methods Chart
        const paymentCtx = document.getElementById('paymentChart').getContext('2d');
        new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_map(function ($p) use ($paymentLabels) {
                    return $paymentLabels[$p['payment_method']] ?? ucfirst($p['payment_method']);
                }, $paymentStats)) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($paymentStats, 'revenue')) ?>,
                    backgroundColor: ['#DC2626', '#22C55E', '#3B82F6', '#8B5CF6', '#F59E0B']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    </script>
</body>

</html>