<?php
/**
 * FoodFlow - Order Confirmation Page
 */

require_once __DIR__ . '/includes/functions.php';

$storeName = getSetting('store_name', 'FoodFlow');
$storePhone = getSetting('store_phone', '');
$prepTime = getSetting('estimated_prep_time', 25);
$deliveryTime = getSetting('estimated_delivery_time', 35);

// In production, get order from database
$orderNumber = 'FF' . date('ymd') . strtoupper(substr(uniqid(), -4));
$isDemo = isset($_GET['demo']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed -
        <?= htmlspecialchars($storeName) ?>
    </title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Karla:wght@400;500;600;700&family=Playfair+Display+SC:wght@400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .confirmation-container {
            max-width: 600px;
            margin: 0 auto;
            padding: var(--space-2xl) var(--space-md);
            text-align: center;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #16A34A 0%, #22C55E 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto var(--space-xl);
            animation: scaleIn 0.5s ease;
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        .order-number {
            background: var(--color-bg);
            padding: var(--space-lg);
            border-radius: var(--radius-xl);
            margin: var(--space-xl) 0;
        }

        .order-number-label {
            font-size: 0.875rem;
            color: var(--color-text-muted);
            margin-bottom: var(--space-xs);
        }

        .order-number-value {
            font-size: 2rem;
            font-weight: 700;
            font-family: var(--font-heading);
            color: var(--color-primary);
            letter-spacing: 2px;
        }

        .estimated-time {
            display: flex;
            gap: var(--space-lg);
            justify-content: center;
            margin: var(--space-xl) 0;
        }

        .time-box {
            background: white;
            padding: var(--space-lg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            min-width: 120px;
        }

        .time-box-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--color-primary);
        }

        .time-box-label {
            font-size: 0.875rem;
            color: var(--color-text-muted);
        }

        .order-timeline {
            margin: var(--space-2xl) 0;
            text-align: left;
        }

        .timeline-step {
            display: flex;
            gap: var(--space-md);
            padding: var(--space-md) 0;
        }

        .timeline-dot {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--color-bg);
            border: 3px solid var(--color-primary);
            flex-shrink: 0;
        }

        .timeline-step.active .timeline-dot {
            background: var(--color-primary);
        }

        .timeline-step.active .timeline-dot::after {
            content: '✓';
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            font-size: 12px;
        }

        .timeline-content h4 {
            font-weight: 600;
            margin-bottom: 2px;
            font-family: var(--font-body);
        }

        .timeline-content p {
            font-size: 0.875rem;
            color: var(--color-text-muted);
            margin: 0;
        }

        .timeline-line {
            position: absolute;
            left: 11px;
            top: 24px;
            bottom: 0;
            width: 2px;
            background: #e5e5e5;
        }
    </style>
</head>

<body style="background: var(--color-bg);">
    <main class="confirmation-container">
        <div class="success-icon">
            <svg width="48" height="48" fill="none" stroke="white" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h1 style="margin-bottom: var(--space-sm);">Order Confirmed!</h1>
        <p style="color: var(--color-text-muted);">Thank you for your order. We're preparing it now!</p>

        <div class="order-number">
            <div class="order-number-label">Order Number</div>
            <div class="order-number-value">
                <?= $orderNumber ?>
            </div>
        </div>

        <div class="estimated-time">
            <div class="time-box">
                <div class="time-box-value">
                    <?= $prepTime ?>m
                </div>
                <div class="time-box-label">Prep Time</div>
            </div>
            <div class="time-box">
                <div class="time-box-value">
                    <?= $deliveryTime ?>m
                </div>
                <div class="time-box-label">Est. Delivery</div>
            </div>
        </div>

        <div class="order-timeline">
            <div class="timeline-step active" style="position: relative;">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <h4>Order Received</h4>
                    <p>We've received your order</p>
                </div>
            </div>
            <div class="timeline-step" style="position: relative;">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <h4>Preparing</h4>
                    <p>Our kitchen is working on it</p>
                </div>
            </div>
            <div class="timeline-step">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <h4>Ready for Pickup/Delivery</h4>
                    <p>Your order is ready!</p>
                </div>
            </div>
            <div class="timeline-step">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <h4>Delivered</h4>
                    <p>Enjoy your meal!</p>
                </div>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: var(--space-md);">
            <a href="track-order.php?order=<?= $orderNumber ?>" class="btn btn-primary btn-lg">
                Track Order
            </a>
            <a href="index.php" class="btn btn-outline">Back to Home</a>
        </div>

        <?php if ($storePhone): ?>
            <p style="margin-top: var(--space-xl); color: var(--color-text-muted); font-size: 0.875rem;">
                Questions? Call us at <a href="tel:<?= preg_replace('/[^0-9]/', '', $storePhone) ?>">
                    <?= htmlspecialchars($storePhone) ?>
                </a>
            </p>
        <?php endif; ?>
    </main>
</body>

</html>