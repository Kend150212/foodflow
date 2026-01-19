<?php
/**
 * FoodFlow - Helper Functions
 */

require_once __DIR__ . '/db.php';

/**
 * Get setting value from database
 */
function getSetting($key, $default = null)
{
    static $settings = null;

    if ($settings === null) {
        $rows = db()->fetchAll("SELECT setting_key, setting_value, setting_type FROM settings");
        $settings = [];
        foreach ($rows as $row) {
            $value = $row['setting_value'];
            if ($row['setting_type'] === 'boolean') {
                $value = (bool) $value;
            } elseif ($row['setting_type'] === 'number') {
                $value = floatval($value);
            } elseif ($row['setting_type'] === 'json') {
                $value = json_decode($value, true);
            }
            $settings[$row['setting_key']] = $value;
        }
    }

    return $settings[$key] ?? $default;
}

/**
 * Update or insert setting
 */
function setSetting($key, $value, $type = 'text')
{
    if ($type === 'json' && is_array($value)) {
        $value = json_encode($value);
    }

    $existing = db()->fetch("SELECT id FROM settings WHERE setting_key = ?", [$key]);

    if ($existing) {
        db()->update('settings', ['setting_value' => $value], 'setting_key = :key', ['key' => $key]);
    } else {
        db()->insert('settings', [
            'setting_key' => $key,
            'setting_value' => $value,
            'setting_type' => $type
        ]);
    }
}

/**
 * Get landing page content
 */
function getLandingContent($section, $key = null)
{
    if ($key) {
        $row = db()->fetch(
            "SELECT content_value, content_type FROM landing_content WHERE section = ? AND content_key = ?",
            [$section, $key]
        );
        if (!$row)
            return null;

        if ($row['content_type'] === 'json') {
            return json_decode($row['content_value'], true);
        }
        return $row['content_value'];
    }

    $rows = db()->fetchAll(
        "SELECT content_key, content_value, content_type FROM landing_content WHERE section = ? ORDER BY sort_order",
        [$section]
    );

    $content = [];
    foreach ($rows as $row) {
        $value = $row['content_value'];
        if ($row['content_type'] === 'json') {
            $value = json_decode($value, true);
        }
        $content[$row['content_key']] = $value;
    }
    return $content;
}

/**
 * Format price with currency
 */
function formatPrice($price)
{
    $symbol = getSetting('currency_symbol', '$');
    return $symbol . number_format((float) $price, 2);
}

/**
 * Generate unique order number
 */
function generateOrderNumber()
{
    $prefix = 'FF';
    $date = date('ymd');
    $random = strtoupper(substr(uniqid(), -4));
    return $prefix . $date . $random;
}

/**
 * Check if store is currently open
 */
function isStoreOpen()
{
    $hours = getSetting('operating_hours', []);
    $dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    $today = strtolower(date('l'));

    if (!isset($hours[$today]))
        return false;

    $dayHours = $hours[$today];
    if ($dayHours['closed'] ?? false)
        return false;

    $now = date('H:i');
    return $now >= $dayHours['open'] && $now <= $dayHours['close'];
}

/**
 * Check if menu item is available now (time-based)
 */
function isMenuItemAvailable($itemId)
{
    $schedules = db()->fetchAll(
        "SELECT * FROM menu_schedules WHERE menu_item_id = ? AND is_active = 1",
        [$itemId]
    );

    if (empty($schedules))
        return true; // No schedule = always available

    $now = new DateTime();
    $currentDay = (int) $now->format('w'); // 0-6, Sunday = 0
    $currentTime = $now->format('H:i:s');

    foreach ($schedules as $schedule) {
        if ($schedule['schedule_type'] === 'always') {
            return true;
        }

        // Check day match (NULL = all days)
        $dayMatch = $schedule['day_of_week'] === null || (int) $schedule['day_of_week'] === $currentDay;

        // Check time match
        $timeMatch = true;
        if ($schedule['start_time'] && $schedule['end_time']) {
            $timeMatch = $currentTime >= $schedule['start_time'] && $currentTime <= $schedule['end_time'];
        }

        if ($dayMatch && $timeMatch) {
            return true;
        }
    }

    return false;
}

/**
 * Get available menu items
 */
function getAvailableMenuItems($categoryId = null, $featuredOnly = false)
{
    $sql = "SELECT m.*, c.name as category_name 
            FROM menu_items m 
            JOIN categories c ON m.category_id = c.id 
            WHERE m.is_active = 1 AND c.is_active = 1";
    $params = [];

    if ($categoryId) {
        $sql .= " AND m.category_id = ?";
        $params[] = $categoryId;
    }

    if ($featuredOnly) {
        $sql .= " AND m.is_featured = 1";
    }

    $sql .= " ORDER BY c.sort_order, m.sort_order";

    $items = db()->fetchAll($sql, $params);

    // Add schedule info and availability to each item
    foreach ($items as &$item) {
        $item['is_available_now'] = isMenuItemAvailable($item['id']);
        $item['schedule_info'] = getMenuItemScheduleInfo($item['id']);
    }

    return $items;
}

/**
 * Get schedule info for a menu item
 */
function getMenuItemScheduleInfo($itemId)
{
    $schedule = db()->fetch(
        "SELECT MAX(schedule_type) as schedule_type, 
                TIME_FORMAT(MIN(start_time), '%h:%i %p') as start_time, 
                TIME_FORMAT(MAX(end_time), '%h:%i %p') as end_time,
                GROUP_CONCAT(DISTINCT day_of_week ORDER BY day_of_week) as days 
         FROM menu_schedules 
         WHERE menu_item_id = ? AND is_active = 1",
        [$itemId]
    );

    if (empty($schedule['schedule_type']) || $schedule['schedule_type'] === 'always') {
        return null; // Always available
    }

    $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    $dayList = '';

    if (!empty($schedule['days'])) {
        $dayNums = explode(',', $schedule['days']);
        $dayNames = array_map(function ($d) use ($days) {
            return $days[(int) $d] ?? '';
        }, $dayNums);
        $dayList = implode(', ', array_filter($dayNames));
    }

    return [
        'type' => $schedule['schedule_type'],
        'time' => $schedule['start_time'] . ' - ' . $schedule['end_time'],
        'days' => $dayList ?: 'Every day'
    ];
}

/**
 * Get active categories
 */
function getCategories()
{
    return db()->fetchAll(
        "SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order"
    );
}

/**
 * Sanitize input
 */
function sanitize($input)
{
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * JSON response helper
 */
function jsonResponse($data, $statusCode = 200)
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * CSRF token generation and validation
 */
function generateCSRFToken()
{
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function validateCSRFToken($token)
{
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Upload and optionally compress an image
 */
function uploadImage($file, $folder = '')
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload failed'];
    }

    $maxSize = 2 * 1024 * 1024; // 2MB

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }

    $uploadPath = UPLOAD_DIR . ($folder ? $folder . '/' : '');
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }

    $filename = uniqid() . '_' . time() . '.jpg';
    $destination = $uploadPath . $filename;

    // If file is larger than 2MB, compress it
    if ($file['size'] > $maxSize) {
        $compressed = compressImage($file['tmp_name'], $destination, $mimeType, $maxSize);
        if (!$compressed) {
            return ['success' => false, 'error' => 'Failed to compress image'];
        }
    } else {
        // Just move the file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'error' => 'Failed to save file'];
        }
    }

    $relativePath = 'assets/uploads/' . ($folder ? $folder . '/' : '') . $filename;
    return ['success' => true, 'path' => $relativePath];
}

/**
 * Compress image to target size
 */
function compressImage($source, $destination, $mimeType, $maxSize)
{
    $image = null;
    switch ($mimeType) {
        case 'image/jpeg':
        case 'image/jpg':
            $image = @imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = @imagecreatefrompng($source);
            break;
        case 'image/gif':
            $image = @imagecreatefromgif($source);
            break;
        case 'image/webp':
            $image = @imagecreatefromwebp($source);
            break;
    }

    if (!$image) {
        return false;
    }

    // Get original dimensions
    $width = imagesx($image);
    $height = imagesy($image);

    // Resize if too large (max 1920px on longest side)
    $maxDimension = 1920;
    if ($width > $maxDimension || $height > $maxDimension) {
        if ($width > $height) {
            $newWidth = $maxDimension;
            $newHeight = (int) ($height * ($maxDimension / $width));
        } else {
            $newHeight = $maxDimension;
            $newWidth = (int) ($width * ($maxDimension / $height));
        }
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);
        $image = $resized;
    }

    // Save with decreasing quality until under max size
    $quality = 85;
    do {
        imagejpeg($image, $destination, $quality);
        $quality -= 10;
    } while (filesize($destination) > $maxSize && $quality > 20);

    imagedestroy($image);
    return true;
}

/**
 * Upload image from URL
 */
function uploadImageFromUrl($url, $folder = '')
{
    $url = trim($url);
    if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
        return ['success' => false, 'error' => 'Invalid URL'];
    }

    $uploadPath = UPLOAD_DIR . ($folder ? $folder . '/' : '');
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }

    // Download image
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if ($httpCode !== 200 || empty($imageData)) {
        return ['success' => false, 'error' => 'Failed to download image'];
    }

    // Check mime type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $mimeType = explode(';', $contentType)[0];
    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid image type'];
    }

    $filename = uniqid() . '_' . time() . '.jpg';
    $tempFile = sys_get_temp_dir() . '/' . $filename;
    $destination = $uploadPath . $filename;

    file_put_contents($tempFile, $imageData);

    $maxSize = 2 * 1024 * 1024; // 2MB
    if (filesize($tempFile) > $maxSize) {
        $compressed = compressImage($tempFile, $destination, $mimeType, $maxSize);
        unlink($tempFile);
        if (!$compressed) {
            return ['success' => false, 'error' => 'Failed to compress image'];
        }
    } else {
        rename($tempFile, $destination);
    }

    $relativePath = 'assets/uploads/' . ($folder ? $folder . '/' : '') . $filename;
    return ['success' => true, 'path' => $relativePath];
}


/**
 * Send notification email (basic)
 */
function sendOrderNotification($orderId)
{
    $order = db()->fetch("SELECT * FROM orders WHERE id = ?", [$orderId]);
    if (!$order)
        return false;

    $storeEmail = getSetting('store_email');
    $storeName = getSetting('store_name');

    // Email to store
    $subject = "New Order #{$order['order_number']}";
    $message = "New order received!\n\n";
    $message .= "Order #: {$order['order_number']}\n";
    $message .= "Customer: {$order['customer_name']}\n";
    $message .= "Phone: {$order['customer_phone']}\n";
    $message .= "Type: {$order['order_type']}\n";
    $message .= "Total: " . formatPrice($order['total']) . "\n";

    $headers = "From: {$storeName} <noreply@{$_SERVER['HTTP_HOST']}>";

    return mail($storeEmail, $subject, $message, $headers);
}

/**
 * Get add-ons for a menu item
 */
function getMenuItemAddons($menuItemId)
{
    return db()->fetchAll(
        "SELECT a.*, ac.name as category_name, ac.icon as category_icon, mia.max_quantity
         FROM menu_item_addons mia
         JOIN addons a ON mia.addon_id = a.id
         JOIN addon_categories ac ON a.category_id = ac.id
         WHERE mia.menu_item_id = ? AND a.is_active = 1 AND ac.is_active = 1
         ORDER BY ac.sort_order, a.sort_order",
        [$menuItemId]
    );
}

/**
 * Get all add-ons grouped by menu item
 */
function getAllMenuItemAddons()
{
    $data = db()->fetchAll(
        "SELECT mia.menu_item_id, a.*, ac.name as category_name, ac.icon as category_icon, mia.max_quantity
         FROM menu_item_addons mia
         JOIN addons a ON mia.addon_id = a.id
         JOIN addon_categories ac ON a.category_id = ac.id
         WHERE a.is_active = 1 AND ac.is_active = 1
         ORDER BY mia.menu_item_id, ac.sort_order, a.sort_order"
    );

    $result = [];
    foreach ($data as $row) {
        $menuItemId = $row['menu_item_id'];
        if (!isset($result[$menuItemId])) {
            $result[$menuItemId] = [];
        }
        $result[$menuItemId][] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'price' => (float) $row['price'],
            'unit' => $row['unit'],
            'unit_value' => $row['unit_value'],
            'category' => $row['category_name'],
            'icon' => $row['category_icon'],
            'max_qty' => (int) $row['max_quantity']
        ];
    }
    return $result;
}
