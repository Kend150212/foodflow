<?php
/**
 * FoodFlow - Track Order Page
 */

require_once __DIR__ . '/includes/functions.php';

$storeName = getSetting('store_name', 'FoodFlow');
$orderNumber = $_GET['order'] ?? '';
$order = null;
$items = [];

if ($orderNumber) {
    $order = db()->fetch(
        "SELECT * FROM orders WHERE order_number = ?",
        [$orderNumber]
    );

    if ($order) {
        $items = db()->fetchAll(
            "SELECT * FROM order_items WHERE order_id = ?",
            [$order['id']]
        );
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order -
        <?= htmlspecialchars($storeName) ?>
    </title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Karla:wght@400;500;600;700&family=Playfair+Display+SC:wght@400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .track-container {
            max-width: 600px;
            margin: 0 auto;
            padding: var(--space-xl) var(--space-md);
        }

        .track-card {
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            padding: var(--space-xl);
        }

        .status-timeline {
            position: relative;
            padding-left: 40px;
        }

        .status-timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e5e5e5;
        }

        .timeline-item {
            position: relative;
            padding-bottom: var(--space-lg);
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-dot {
            position: absolute;
            left: -33px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #e5e5e5;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #e5e5e5;
        }

        .timeline-item.active .timeline-dot {
            background: var(--color-primary);
            box-shadow: 0 0 0 2px var(--color-primary);
        }

        .timeline-item.completed .timeline-dot {
            background: var(--color-success);
            box-shadow: 0 0 0 2px var(--color-success);
        }

        .timeline-item.completed .timeline-dot::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 10px;
        }

        .timeline-title {
            font-weight: 600;
            margin-bottom: 2px;
        }

        .timeline-time {
            font-size: 0.875rem;
            color: var(--color-text-muted);
        }
    </style>
</head>

<body style="padding-top: 64px; background: var(--color-bg);">
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-logo">
                <span>
                    <?= htmlspecialchars($storeName) ?>
                </span>
            </a>
        </div>
    </nav>

    <main class="track-container">
        <?php if (!$orderNumber): ?>
            <!-- Search Form -->
            <div class="track-card text-center">
                <h1 style="margin-bottom: var(--space-md);">Track Your Order</h1>
                <p style="color: var(--color-text-muted); margin-bottom: var(--space-xl);">Enter your order number to check
                    the status</p>

                <form method="GET" style="max-width: 300px; margin: 0 auto;">
                    <div class="form-group">
                        <input type="text" name="order" class="form-input" placeholder="Order # (e.g., FF240118ABCD)"
                            required style="text-align: center; font-size: 1.125rem; letter-spacing: 1px;">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Track Order</button>
                </form>

                <p style="margin-top: var(--space-xl); font-size: 0.875rem; color: var(--color-text-muted);">
                    Can't find your order number? Check your email or call us at
                    <?= htmlspecialchars(getSetting('store_phone', '')) ?>
                </p>
            </div>
        <?php elseif (!$order): ?>
            <!-- Not Found -->
            <div class="track-card text-center">
                <div
                    style="width: 64px; height: 64px; background: var(--color-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-md);">
                    <svg width="32" height="32" fill="none" stroke="var(--color-text-muted)" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <h2>Order Not Found</h2>
                <p style="color: var(--color-text-muted);">We couldn't find an order with number <strong>
                        <?= htmlspecialchars($orderNumber) ?>
                    </strong></p>
                <a href="track-order.php" class="btn btn-outline" style="margin-top: var(--space-lg);">Try Again</a>
            </div>
        <?php else: ?>
            <!-- Order Found -->
            <div class="track-card">
                <div class="text-center" style="margin-bottom: var(--space-xl);">
                    <h1 style="margin-bottom: var(--space-xs);">Order
                        <?= htmlspecialchars($order['order_number']) ?>
                    </h1>
                    <p style="color: var(--color-text-muted);">
                        <?= ucfirst($order['order_type']) ?> •
                        <?= date('M j, g:i A', strtotime($order['created_at'])) ?>
                    </p>
                </div>

                <?php
                $statuses = [
                    'pending' => ['label' => 'Order Received', 'desc' => 'We received your order'],
                    'confirmed' => ['label' => 'Confirmed', 'desc' => 'Order has been confirmed'],
                    'preparing' => ['label' => 'Preparing', 'desc' => 'Our kitchen is working on it'],
                    'ready' => ['label' => 'Ready', 'desc' => $order['order_type'] === 'delivery' ? 'Ready for pickup by driver' : 'Ready for pickup!'],
                    'out_for_delivery' => ['label' => 'On the Way', 'desc' => 'Driver is heading to you'],
                    'delivered' => ['label' => 'Delivered', 'desc' => 'Enjoy your meal!'],
                    'cancelled' => ['label' => 'Cancelled', 'desc' => $order['cancel_reason'] ?? 'Order was cancelled']
                ];

                $currentStatus = $order['order_status'];
                $statusOrder = array_keys($statuses);
                $currentIndex = array_search($currentStatus, $statusOrder);
                ?>

                <div class="status-timeline">
                    <?php foreach ($statuses as $key => $status):
                        $index = array_search($key, $statusOrder);
                        $isCompleted = $index < $currentIndex;
                        $isActive = $key === $currentStatus;
                        if ($key === 'out_for_delivery' && $order['order_type'] === 'pickup')
                            continue; // Skip delivery step for pickup
                        if ($key === 'cancelled' && $currentStatus !== 'cancelled')
                            continue; // Only show cancelled if cancelled
                        ?>
                        <div class="timeline-item <?= $isCompleted ? 'completed' : '' ?> <?= $isActive ? 'active' : '' ?>">
                            <div class="timeline-dot"></div>
                            <div class="timeline-title">
                                <?= $status['label'] ?>
                            </div>
                            <div class="timeline-time">
                                <?= $status['desc'] ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($order['estimated_ready_time'] && !in_array($currentStatus, ['delivered', 'cancelled'])): ?>
                    <div
                        style="background: var(--color-bg); padding: var(--space-md); border-radius: var(--radius-lg); margin-top: var(--space-xl); text-align: center;">
                        <strong>Estimated Ready:</strong>
                        <?= date('g:i A', strtotime($order['estimated_ready_time'])) ?>
                    </div>
                <?php endif; ?>

                <!-- Order Details -->
                <div style="margin-top: var(--space-xl); padding-top: var(--space-lg); border-top: 1px solid #f1f1f1;">
                    <h3 style="margin-bottom: var(--space-md);">Order Details</h3>

                    <?php foreach ($items as $item): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-sm);">
                            <span>
                                <?= $item['quantity'] ?>x
                                <?= htmlspecialchars($item['item_name']) ?>
                            </span>
                            <span>
                                <?= formatPrice($item['subtotal']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>

                    <div style="border-top: 1px solid #f1f1f1; margin-top: var(--space-md); padding-top: var(--space-md);">
                        <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 1.125rem;">
                            <span>Total</span>
                            <span>
                                <?= formatPrice($order['total']) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="text-center" style="margin-top: var(--space-xl);">
                    <a href="index.php" class="btn btn-outline">Back to Home</a>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>

</html>