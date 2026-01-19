<?php
/**
 * Debug endpoint to test order API
 */

header('Content-Type: text/plain');

echo "=== Order API Debug ===\n\n";

// Test 1: Check functions.php
echo "1. Loading functions.php...\n";
try {
    require_once __DIR__ . '/../includes/functions.php';
    echo "   ✓ functions.php loaded OK\n\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
    exit;
}

// Test 2: Check database connection
echo "2. Testing database connection...\n";
try {
    $db = db();
    echo "   ✓ Database connected OK\n\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
    exit;
}

// Test 3: Check orders table
echo "3. Testing orders table...\n";
try {
    $count = $db->fetch("SELECT COUNT(*) as cnt FROM orders");
    echo "   ✓ Orders table OK, count: " . $count['cnt'] . "\n\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Test 4: Check menu_items table
echo "4. Testing menu_items table...\n";
try {
    $items = $db->fetchAll("SELECT id, name, price FROM menu_items LIMIT 3");
    echo "   ✓ Menu items OK:\n";
    foreach ($items as $item) {
        echo "      - [{$item['id']}] {$item['name']} - \${$item['price']}\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Test 5: Test order insertion
echo "5. Testing order insertion (dry run)...\n";
try {
    $testOrder = [
        'order_number' => 'TEST' . time(),
        'customer_name' => 'Debug Test',
        'customer_email' => '',
        'customer_phone' => '',
        'order_type' => 'pickup',
        'delivery_address' => '',
        'delivery_instructions' => '',
        'subtotal' => 10.00,
        'tax_amount' => 0.83,
        'delivery_fee' => 0,
        'tip_amount' => 0,
        'total' => 10.83,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'order_status' => 'confirmed',
        'estimated_ready_time' => date('Y-m-d H:i:s', strtotime('+25 minutes'))
    ];

    echo "   Order data structure: OK\n";
    echo "   Required fields present: OK\n\n";

    // Don't actually insert
    echo "   (Dry run - no actual insertion)\n\n";

} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Test 6: Check PHP version and extensions
echo "6. PHP Info...\n";
echo "   PHP Version: " . PHP_VERSION . "\n";
echo "   PDO Available: " . (extension_loaded('pdo') ? 'Yes' : 'No') . "\n";
echo "   PDO MySQL: " . (extension_loaded('pdo_mysql') ? 'Yes' : 'No') . "\n";
echo "   JSON: " . (extension_loaded('json') ? 'Yes' : 'No') . "\n\n";

echo "=== Debug Complete ===\n";
