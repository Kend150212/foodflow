<?php
/**
 * FoodFlow - Menu Management
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAuth();

$message = '';
$error = '';
$action = $_GET['action'] ?? 'list';
$editId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isPopular = isset($_POST['is_popular']) ? 1 : 0;
    $isSpicy = isset($_POST['is_spicy']) ? 1 : 0;
    $isVegetarian = isset($_POST['is_vegetarian']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    // Handle image upload
    $imagePath = $_POST['existing_image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadImage($_FILES['image'], 'menu');
        if ($upload['success']) {
            $imagePath = $upload['path'];
        } else {
            $error = $upload['error'];
        }
    }
    
    if (empty($name) || $categoryId <= 0 || $price <= 0) {
        $error = 'Please fill in all required fields';
    } elseif (!$error) {
        $data = [
            'name' => $name,
            'category_id' => $categoryId,
            'price' => $price,
            'description' => $description,
            'image' => $imagePath,
            'is_featured' => $isFeatured,
            'is_popular' => $isPopular,
            'is_spicy' => $isSpicy,
            'is_vegetarian' => $isVegetarian,
            'is_active' => $isActive
        ];
        
        if ($editId) {
            db()->update('menu_items', $data, 'id = :id', ['id' => $editId]);
            $itemId = $editId;
            $message = 'Menu item updated successfully!';
        } else {
            $itemId = db()->insert('menu_items', $data);
            $message = 'Menu item added successfully!';
        }
        
        // Save schedule
        $scheduleType = $_POST['schedule_type'] ?? 'always';
        
        // Delete existing schedules for this item
        db()->delete('menu_schedules', 'menu_item_id = ?', [$itemId]);
        
        // Create new schedules if not "always"
        if ($scheduleType !== 'always') {
            $startTime = $_POST['start_time'] ?? '10:00';
            $endTime = $_POST['end_time'] ?? '21:00';
            
            if ($scheduleType === 'specific_hours') {
                // All days, same hours
                db()->insert('menu_schedules', [
                    'menu_item_id' => $itemId,
                    'schedule_type' => $scheduleType,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'is_active' => 1
                ]);
            } elseif ($scheduleType === 'specific_days') {
                // Specific days
                $days = $_POST['schedule_days'] ?? [0,1,2,3,4,5,6];
                foreach ($days as $day) {
                    db()->insert('menu_schedules', [
                        'menu_item_id' => $itemId,
                        'schedule_type' => $scheduleType,
                        'day_of_week' => (int)$day,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'is_active' => 1
                    ]);
                }
            }
        }
        
        header('Location: menu.php?success=1');
        exit;
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    db()->delete('menu_items', 'id = ?', [$deleteId]);
    header('Location: menu.php?deleted=1');
    exit;
}

// Get categories for dropdown
$categories = getCategories();

// Get menu items
$menuItems = db()->fetchAll(
    "SELECT m.*, c.name as category_name 
     FROM menu_items m 
     LEFT JOIN categories c ON m.category_id = c.id 
     ORDER BY c.sort_order, m.sort_order"
);

// Get item for editing
$editItem = null;
$itemSchedule = null;
if ($editId) {
    $editItem = db()->fetch("SELECT * FROM menu_items WHERE id = ?", [$editId]);
    // Get schedule if exists - use aggregate functions to avoid GROUP BY error
    $itemSchedule = db()->fetch(
        "SELECT MAX(schedule_type) as schedule_type, 
                MIN(start_time) as start_time, 
                MAX(end_time) as end_time, 
                GROUP_CONCAT(day_of_week) as days 
         FROM menu_schedules 
         WHERE menu_item_id = ? AND is_active = 1",
        [$editId]
    );
    // If no schedule, set to null
    if (empty($itemSchedule['schedule_type'])) {
        $itemSchedule = null;
    }
}

$storeName = getSetting('store_name', 'FoodFlow');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Karla:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Karla', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Include sidebar from index.php pattern -->
    <aside class="fixed inset-y-0 left-0 w-64 bg-gray-900 text-white p-6 hidden lg:block">
        <div class="flex items-center gap-3 mb-8">
            <div class="w-10 h-10 bg-red-600 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
            </div>
            <span class="font-bold text-lg"><?= htmlspecialchars($storeName) ?></span>
        </div>
        
        <nav class="space-y-1">
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>
            <a href="orders.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Orders
            </a>
            <a href="menu.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gray-800 text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                Menu Items
            </a>
            <a href="categories.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/>
                </svg>
                Categories
            </a>
            <a href="content.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Landing Page
            </a>
            <a href="settings.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Settings
            </a>
        </nav>
    </aside>

    <main class="lg:ml-64 min-h-screen">
        <header class="bg-white border-b px-6 py-4 flex items-center justify-between sticky top-0 z-10">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Menu Items</h1>
                <p class="text-gray-500 text-sm"><?= count($menuItems) ?> items total</p>
            </div>
            <a href="menu.php?action=add" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Item
            </a>
        </header>
        
        <div class="p-6">
            <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-6">
                    ✅ Menu item saved successfully!
                </div>
            <?php endif; ?>
            
            <?php if ($action === 'add' || $editItem): ?>
                <!-- Add/Edit Form -->
                <div class="bg-white rounded-xl shadow-sm border p-6 max-w-2xl">
                    <h2 class="text-xl font-bold mb-6"><?= $editItem ? 'Edit Menu Item' : 'Add New Menu Item' ?></h2>
                    
                    <?php if ($error): ?>
                        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-6">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Item Name *</label>
                                <input type="text" name="name" required
                                       value="<?= htmlspecialchars($editItem['name'] ?? '') ?>"
                                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                                <select name="category_id" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                    <option value="">Select category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= ($editItem['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Price ($) *</label>
                            <input type="number" name="price" required step="0.01" min="0"
                                   value="<?= htmlspecialchars($editItem['price'] ?? '') ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="3"
                                      class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"><?= htmlspecialchars($editItem['description'] ?? '') ?></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                            <?php if (!empty($editItem['image'])): ?>
                                <div class="mb-2">
                                    <img src="../<?= htmlspecialchars($editItem['image']) ?>" alt="Current image" class="w-24 h-24 object-cover rounded-lg">
                                    <input type="hidden" name="existing_image" value="<?= htmlspecialchars($editItem['image']) ?>">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="image" accept="image/*"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_featured" value="1" <?= ($editItem['is_featured'] ?? 0) ? 'checked' : '' ?>
                                       class="w-4 h-4 text-red-600 rounded">
                                <span class="text-sm">Featured</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_popular" value="1" <?= ($editItem['is_popular'] ?? 0) ? 'checked' : '' ?>
                                       class="w-4 h-4 text-red-600 rounded">
                                <span class="text-sm">Popular</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_spicy" value="1" <?= ($editItem['is_spicy'] ?? 0) ? 'checked' : '' ?>
                                       class="w-4 h-4 text-red-600 rounded">
                                <span class="text-sm">🌶️ Spicy</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_vegetarian" value="1" <?= ($editItem['is_vegetarian'] ?? 0) ? 'checked' : '' ?>
                                       class="w-4 h-4 text-red-600 rounded">
                                <span class="text-sm">🌱 Vegetarian</span>
                            </label>
                        </div>
                        
                        <!-- Time Scheduling Section -->
                        <div class="border-t pt-4 mt-4">
                            <h3 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                                ⏰ Availability Schedule
                                <span class="text-sm font-normal text-gray-500">(Optional)</span>
                            </h3>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Schedule Type</label>
                                <select name="schedule_type" id="scheduleType" onchange="toggleScheduleOptions()"
                                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                                    <option value="always" <?= ($itemSchedule['schedule_type'] ?? 'always') === 'always' ? 'selected' : '' ?>>Always Available</option>
                                    <option value="specific_hours" <?= ($itemSchedule['schedule_type'] ?? '') === 'specific_hours' ? 'selected' : '' ?>>Specific Hours</option>
                                    <option value="specific_days" <?= ($itemSchedule['schedule_type'] ?? '') === 'specific_days' ? 'selected' : '' ?>>Specific Days & Hours</option>
                                </select>
                            </div>
                            
                            <div id="timeOptions" class="<?= ($itemSchedule['schedule_type'] ?? 'always') === 'always' ? 'hidden' : '' ?>">
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                                        <input type="time" name="start_time" 
                                               value="<?= $itemSchedule['start_time'] ?? '10:00' ?>"
                                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                                        <input type="time" name="end_time" 
                                               value="<?= $itemSchedule['end_time'] ?? '21:00' ?>"
                                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                                    </div>
                                </div>
                            </div>
                            
                            <div id="dayOptions" class="<?= ($itemSchedule['schedule_type'] ?? 'always') !== 'specific_days' ? 'hidden' : '' ?>">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Available Days</label>
                                <div class="flex flex-wrap gap-2">
                                    <?php 
                                    $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                                    $selectedDays = explode(',', $itemSchedule['days'] ?? '0,1,2,3,4,5,6');
                                    foreach ($days as $i => $day): 
                                    ?>
                                        <label class="flex items-center gap-1 cursor-pointer border px-3 py-2 rounded-lg hover:bg-gray-50 has-[:checked]:bg-red-50 has-[:checked]:border-red-300">
                                            <input type="checkbox" name="schedule_days[]" value="<?= $i ?>" 
                                                   <?= in_array($i, $selectedDays) ? 'checked' : '' ?>
                                                   class="w-4 h-4 text-red-600 rounded">
                                            <span class="text-sm"><?= $day ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <p class="text-xs text-gray-500 mt-3">
                                💡 Use this to set items only available at certain times (e.g., Breakfast: 7am-11am, Lunch Special: 11am-2pm)
                            </p>
                        </div>
                        
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" <?= ($editItem['is_active'] ?? 1) ? 'checked' : '' ?>
                                   class="w-4 h-4 text-red-600 rounded">
                            <span class="text-sm font-medium">Active (visible to customers)</span>
                        </label>
                        
                        <div class="flex gap-4 pt-4">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition">
                                <?= $editItem ? 'Update Item' : 'Add Item' ?>
                            </button>
                            <a href="menu.php" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-medium transition">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <!-- Items List -->
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <?php if (empty($menuItems)): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                            No menu items yet. <a href="menu.php?action=add" class="text-red-600 hover:underline">Add your first item</a>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($menuItems as $item): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <?php if ($item['image']): ?>
                                                        <img src="../<?= htmlspecialchars($item['image']) ?>" alt="" class="w-12 h-12 rounded-lg object-cover">
                                                    <?php else: ?>
                                                        <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center">
                                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                            </svg>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <div class="font-medium text-gray-900"><?= htmlspecialchars($item['name']) ?></div>
                                                        <div class="text-sm text-gray-500 flex gap-1">
                                                            <?php if ($item['is_featured']): ?><span class="bg-yellow-100 text-yellow-800 px-1.5 py-0.5 rounded text-xs">Featured</span><?php endif; ?>
                                                            <?php if ($item['is_spicy']): ?><span>🌶️</span><?php endif; ?>
                                                            <?php if ($item['is_vegetarian']): ?><span>🌱</span><?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($item['category_name'] ?? 'Uncategorized') ?></td>
                                            <td class="px-6 py-4 font-medium">$<?= number_format($item['price'], 2) ?></td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $item['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                                    <?= $item['is_active'] ? 'Active' : 'Inactive' ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-2">
                                                    <a href="menu.php?id=<?= $item['id'] ?>" class="text-blue-600 hover:text-blue-800">Edit</a>
                                                    <a href="menu.php?delete=<?= $item['id'] ?>" onclick="return confirm('Delete this item?')" class="text-red-600 hover:text-red-800">Delete</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <script>
        function toggleScheduleOptions() {
            const type = document.getElementById('scheduleType').value;
            const timeOptions = document.getElementById('timeOptions');
            const dayOptions = document.getElementById('dayOptions');
            
            timeOptions.classList.toggle('hidden', type === 'always');
            dayOptions.classList.toggle('hidden', type !== 'specific_days');
        }
    </script>
</body>
</html>
