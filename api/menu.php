<?php
/**
 * FoodFlow - Menu API
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../includes/functions.php';

$categoryId = isset($_GET['category']) ? (int) $_GET['category'] : null;
$featured = isset($_GET['featured']);

try {
    $items = getAvailableMenuItems($categoryId, $featured);
    $categories = getCategories();

    jsonResponse([
        'success' => true,
        'categories' => $categories,
        'items' => array_values($items),
        'store' => [
            'name' => getSetting('store_name'),
            'is_open' => isStoreOpen(),
            'min_order' => getSetting('min_order_amount'),
            'delivery_fee' => getSetting('delivery_fee'),
            'free_delivery_min' => getSetting('free_delivery_min'),
            'tax_rate' => getSetting('tax_rate')
        ]
    ]);
} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}
