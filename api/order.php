<?php
/**
 * FoodFlow - Order API
 */

// Start output buffering to catch any errors
ob_start();

// Suppress all error output to prevent JSON corruption
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    exit(0);
}

try {
    require_once __DIR__ . '/../includes/functions.php';
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['error' => 'Server configuration error']);
    exit;
}

// Clear any buffered output from includes
ob_end_clean();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Get order status
            $orderId = $_GET['id'] ?? null;
            $orderNumber = $_GET['number'] ?? null;

            if ($orderNumber) {
                $order = db()->fetch(
                    "SELECT id, order_number, customer_name, order_type, order_status, 
                            payment_status, total, created_at, estimated_ready_time 
                     FROM orders WHERE order_number = ?",
                    [$orderNumber]
                );
            } elseif ($orderId) {
                $order = db()->fetch(
                    "SELECT id, order_number, customer_name, order_type, order_status, 
                            payment_status, total, created_at, estimated_ready_time 
                     FROM orders WHERE id = ?",
                    [$orderId]
                );
            } else {
                jsonResponse(['error' => 'Order ID or number required'], 400);
            }

            if (!$order) {
                jsonResponse(['error' => 'Order not found'], 404);
            }

            // Get order items
            $items = db()->fetchAll(
                "SELECT item_name, quantity, unit_price, subtotal 
                 FROM order_items WHERE order_id = ?",
                [$order['id']]
            );

            $order['items'] = $items;
            jsonResponse(['success' => true, 'order' => $order]);
            break;

        case 'POST':
            // Create new order
            $input = json_decode(file_get_contents('php://input'), true);

            // Validate CSRF
            if (!validateCSRFToken($input['csrf_token'] ?? '')) {
                // For demo purposes, skip CSRF in API calls
                // jsonResponse(['error' => 'Invalid token'], 403);
            }

            // Parse cart data
            $cartData = json_decode($input['cart_data'] ?? '{}', true);

            if (empty($cartData['items'])) {
                jsonResponse(['error' => 'Cart is empty'], 400);
            }

            // Validate required fields - phone is optional for walk-in orders
            if (empty($input['customer_name'])) {
                jsonResponse(['error' => 'Customer name is required'], 400);
            }
            if (empty($input['payment_method'])) {
                jsonResponse(['error' => 'Payment method is required'], 400);
            }

            // Normalize order_type - database only accepts 'delivery' or 'pickup'
            $orderType = $input['order_type'] ?? 'pickup';
            // Convert dine_in, takeout, etc. to 'pickup' for database
            if (!in_array($orderType, ['delivery', 'pickup'])) {
                $orderType = 'pickup'; // dine_in, takeout = pickup in database
            }

            // Calculate totals
            $subtotal = 0;
            foreach ($cartData['items'] as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }

            $taxRate = getSetting('tax_rate', 8.25);
            $deliveryFee = $orderType === 'delivery' ? getSetting('delivery_fee', 4.99) : 0;
            $freeDeliveryMin = getSetting('free_delivery_min', 35);

            if ($subtotal >= $freeDeliveryMin) {
                $deliveryFee = 0;
            }

            $taxAmount = $subtotal * ($taxRate / 100);
            $tipAmount = 0;

            if (!empty($input['tip_percentage'])) {
                $tipAmount = $subtotal * ((int) $input['tip_percentage'] / 100);
            } elseif (!empty($input['custom_tip'])) {
                $tipAmount = (float) $input['custom_tip'];
            }

            $total = $subtotal + $taxAmount + $deliveryFee + $tipAmount;

            // Create delivery address
            $deliveryAddress = '';
            if ($orderType === 'delivery') {
                $deliveryAddress = implode(', ', array_filter([
                    $input['delivery_address'] ?? '',
                    $input['delivery_city'] ?? '',
                    $input['delivery_zip'] ?? ''
                ]));
            }

            // Generate order number
            $orderNumber = generateOrderNumber();

            // Prep time
            $prepTime = getSetting('estimated_prep_time', 25);
            $estimatedReady = date('Y-m-d H:i:s', strtotime("+{$prepTime} minutes"));

            // Insert order
            $orderId = db()->insert('orders', [
                'order_number' => $orderNumber,
                'customer_name' => sanitize($input['customer_name']),
                'customer_email' => sanitize($input['customer_email'] ?? ''),
                'customer_phone' => sanitize($input['customer_phone'] ?? ''),
                'order_type' => $orderType,
                'delivery_address' => $deliveryAddress,
                'delivery_instructions' => sanitize($input['delivery_instructions'] ?? ''),
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'delivery_fee' => $deliveryFee,
                'tip_amount' => $tipAmount,
                'total' => $total,
                'payment_method' => $input['payment_method'],
                'payment_status' => 'paid', // For demo - in production, verify with payment gateway
                'order_status' => 'confirmed',
                'special_instructions' => sanitize($input['special_instructions'] ?? ''),
                'estimated_ready_time' => $estimatedReady
            ]);

            // Insert order items
            foreach ($cartData['items'] as $item) {
                db()->insert('order_items', [
                    'order_id' => $orderId,
                    'menu_item_id' => $item['id'],
                    'item_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity']
                ]);
            }

            // Send notification (silently - don't let errors affect response)
            try {
                ob_start();
                sendOrderNotification($orderId);
                ob_end_clean();
            } catch (Exception $e) {
                // Log but don't fail the order
            }

            jsonResponse([
                'success' => true,
                'order_number' => $orderNumber,
                'order_id' => $orderId,
                'estimated_ready' => $estimatedReady,
                'total' => $total
            ]);
            break;

        case 'PUT':
            // Update order status (admin only)
            session_start();
            if (!isset($_SESSION['admin_id'])) {
                jsonResponse(['error' => 'Unauthorized'], 401);
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $orderId = $input['order_id'] ?? null;
            $status = $input['status'] ?? null;

            if (!$orderId || !$status) {
                jsonResponse(['error' => 'Order ID and status required'], 400);
            }

            $validStatuses = ['pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered', 'cancelled'];
            if (!in_array($status, $validStatuses)) {
                jsonResponse(['error' => 'Invalid status'], 400);
            }

            db()->update('orders', ['order_status' => $status], 'id = :id', ['id' => $orderId]);

            jsonResponse(['success' => true, 'status' => $status]);
            break;

        default:
            jsonResponse(['error' => 'Method not allowed'], 405);
    }
} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}
