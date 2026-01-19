<?php
/**
 * FoodFlow - Orders API (for real-time polling)
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache');

session_start();
require_once __DIR__ . '/../includes/functions.php';

// Check admin auth for some endpoints
$isAdmin = isset($_SESSION['admin_id']);

$action = $_GET['action'] ?? 'list';

try {
    switch ($action) {
        case 'new':
            // Get orders newer than a timestamp
            if (!$isAdmin)
                jsonResponse(['error' => 'Unauthorized'], 401);

            $since = $_GET['since'] ?? date('Y-m-d H:i:s', strtotime('-5 minutes'));
            $orders = db()->fetchAll(
                "SELECT id, order_number, customer_name, order_type, total, order_status, created_at 
                 FROM orders 
                 WHERE created_at > ? 
                 ORDER BY created_at DESC",
                [$since]
            );

            $count = db()->fetch("SELECT COUNT(*) as cnt FROM orders WHERE order_status IN ('pending', 'confirmed')")['cnt'] ?? 0;

            jsonResponse([
                'success' => true,
                'orders' => $orders,
                'pending_count' => (int) $count,
                'server_time' => date('Y-m-d H:i:s')
            ]);
            break;

        case 'pending':
            // Get all pending/active orders
            if (!$isAdmin)
                jsonResponse(['error' => 'Unauthorized'], 401);

            $orders = db()->fetchAll(
                "SELECT * FROM orders 
                 WHERE order_status IN ('pending', 'confirmed', 'preparing', 'ready') 
                 ORDER BY 
                    FIELD(order_status, 'pending', 'confirmed', 'preparing', 'ready'),
                    created_at ASC"
            );

            // Get items for each order
            foreach ($orders as &$order) {
                $order['items'] = db()->fetchAll(
                    "SELECT * FROM order_items WHERE order_id = ?",
                    [$order['id']]
                );
            }

            jsonResponse(['success' => true, 'orders' => $orders]);
            break;

        case 'stats':
            // Get today's stats
            if (!$isAdmin)
                jsonResponse(['error' => 'Unauthorized'], 401);

            $stats = db()->fetch(
                "SELECT 
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN order_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                    SUM(CASE WHEN order_status = 'preparing' THEN 1 ELSE 0 END) as preparing,
                    SUM(CASE WHEN order_status = 'ready' THEN 1 ELSE 0 END) as ready,
                    SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as completed,
                    COALESCE(SUM(total), 0) as revenue
                 FROM orders 
                 WHERE DATE(created_at) = CURDATE()"
            );

            jsonResponse(['success' => true, 'stats' => $stats]);
            break;

        case 'update_status':
            // Update order status
            if (!$isAdmin)
                jsonResponse(['error' => 'Unauthorized'], 401);
            if ($_SERVER['REQUEST_METHOD'] !== 'POST')
                jsonResponse(['error' => 'POST required'], 405);

            $input = json_decode(file_get_contents('php://input'), true);
            $orderId = (int) ($input['order_id'] ?? 0);
            $status = $input['status'] ?? '';

            $validStatuses = ['pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered', 'cancelled'];
            if (!$orderId || !in_array($status, $validStatuses)) {
                jsonResponse(['error' => 'Invalid parameters'], 400);
            }

            db()->update('orders', ['order_status' => $status], 'id = :id', ['id' => $orderId]);

            jsonResponse(['success' => true, 'status' => $status]);
            break;

        default:
            jsonResponse(['error' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}
