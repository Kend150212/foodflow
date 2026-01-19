<?php
/**
 * FoodFlow - Kitchen Display / Order Print
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAuth();

$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$order = null;
$items = [];

if ($orderId) {
    $order = db()->fetch("SELECT * FROM orders WHERE id = ?", [$orderId]);
    if ($order) {
        $items = db()->fetchAll("SELECT * FROM order_items WHERE order_id = ?", [$orderId]);
    }
}

$storeName = getSetting('store_name', 'FoodFlow');
$storePhone = getSetting('store_phone', '');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Ticket #
        <?= $order['order_number'] ?? '' ?>
    </title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.4;
            padding: 10px;
            max-width: 80mm;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .store-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .order-type {
            display: inline-block;
            background: #000;
            color: #fff;
            padding: 5px 15px;
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
            text-transform: uppercase;
        }

        .order-number {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .order-time {
            font-size: 12px;
            margin-top: 5px;
        }

        .customer-info {
            border-bottom: 1px dashed #000;
            padding: 10px 0;
            margin-bottom: 10px;
        }

        .customer-name {
            font-size: 16px;
            font-weight: bold;
        }

        .items-section {
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px dotted #ccc;
        }

        .item:last-child {
            border-bottom: none;
        }

        .item-qty {
            font-weight: bold;
            font-size: 16px;
            min-width: 30px;
        }

        .item-name {
            flex: 1;
            font-weight: bold;
            font-size: 14px;
        }

        .item-options {
            font-size: 12px;
            color: #666;
            padding-left: 30px;
        }

        .special-instructions {
            background: #f5f5f5;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #000;
        }

        .special-instructions-title {
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .footer {
            text-align: center;
            padding-top: 10px;
            font-size: 12px;
        }

        .print-btn {
            display: block;
            width: 100%;
            padding: 15px;
            background: #000;
            color: #fff;
            border: none;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
        }

        @media print {

            .print-btn,
            .no-print {
                display: none !important;
            }

            body {
                padding: 0;
            }
        }
    </style>
</head>

<body>
    <?php if (!$order): ?>
        <div style="text-align: center; padding: 50px;">
            <h2>Order not found</h2>
            <a href="orders.php">← Back to Orders</a>
        </div>
    <?php else: ?>
        <div class="header">
            <div class="store-name">
                <?= htmlspecialchars($storeName) ?>
            </div>
            <div class="order-type">
                <?= strtoupper($order['order_type']) ?>
            </div>
            <div class="order-number">#
                <?= htmlspecialchars($order['order_number']) ?>
            </div>
            <div class="order-time">
                <?= date('M j, Y - g:i A', strtotime($order['created_at'])) ?>
            </div>
        </div>

        <div class="customer-info">
            <div class="customer-name">
                <?= htmlspecialchars($order['customer_name']) ?>
            </div>
            <div>
                <?= htmlspecialchars($order['customer_phone']) ?>
            </div>
            <?php if ($order['order_type'] === 'delivery' && $order['delivery_address']): ?>
                <div style="margin-top: 5px; font-size: 12px;">
                    📍
                    <?= htmlspecialchars($order['delivery_address']) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="items-section">
            <div
                style="font-weight: bold; margin-bottom: 10px; text-transform: uppercase; border-bottom: 2px solid #000; padding-bottom: 5px;">
                Order Items
            </div>
            <?php foreach ($items as $item): ?>
                <div class="item">
                    <span class="item-qty">
                        <?= $item['quantity'] ?>x
                    </span>
                    <span class="item-name">
                        <?= htmlspecialchars($item['item_name']) ?>
                    </span>
                </div>
                <?php if (!empty($item['options'])): ?>
                    <div class="item-options">
                        <?= htmlspecialchars($item['options']) ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($order['special_instructions'])): ?>
            <div class="special-instructions">
                <div class="special-instructions-title">⚠️ Special Instructions:</div>
                <?= htmlspecialchars($order['special_instructions']) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($order['delivery_instructions'])): ?>
            <div class="special-instructions">
                <div class="special-instructions-title">🚗 Delivery Notes:</div>
                <?= htmlspecialchars($order['delivery_instructions']) ?>
            </div>
        <?php endif; ?>

        <div class="footer">
            <div style="font-weight: bold; font-size: 14px;">
                TOTAL: $
                <?= number_format($order['total'], 2) ?>
            </div>
            <?php if ($order['tip_amount'] > 0): ?>
                <div style="font-size: 12px;">(includes $
                    <?= number_format($order['tip_amount'], 2) ?> tip)
                </div>
            <?php endif; ?>
            <div style="margin-top: 10px;">
                <?= $storePhone ?>
            </div>
        </div>

        <button class="print-btn no-print" onclick="window.print()">
            🖨️ PRINT TICKET
        </button>

        <div class="no-print" style="text-align: center; margin-top: 10px;">
            <a href="orders.php" style="color: #666;">← Back to Orders</a>
        </div>
    <?php endif; ?>

    <?php if ($order && isset($_GET['autoprint'])): ?>
        <script>
            window.onload = function () {
                window.print();
            };
        </script>
    <?php endif; ?>
</body>

</html>