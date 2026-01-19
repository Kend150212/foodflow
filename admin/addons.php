<?php
/**
 * FoodFlow - Add-ons Management
 * Manage add-on categories and items
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAuth();

$message = '';
$error = '';
$action = $_GET['action'] ?? 'list';
$editId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$type = $_GET['type'] ?? 'addon'; // 'addon' or 'category'

// Unit options
$unitOptions = ['Pcs', 'g', 'kg', 'oz', 'lb', 'ml', 'L'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postType = $_POST['type'] ?? 'addon';
    
    if ($postType === 'category') {
        // Category operations
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? '🔹');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($name)) {
            $error = 'Category name is required';
        } else {
            $data = [
                'name' => $name,
                'description' => $description,
                'icon' => $icon,
                'sort_order' => $sortOrder,
                'is_active' => $isActive
            ];
            
            if (isset($_POST['category_id']) && $_POST['category_id']) {
                db()->update('addon_categories', $data, 'id = :id', ['id' => $_POST['category_id']]);
                $message = 'Category updated successfully!';
            } else {
                db()->insert('addon_categories', $data);
                $message = 'Category created successfully!';
            }
            header('Location: addons.php?msg=' . urlencode($message));
            exit;
        }
    } elseif ($postType === 'addon') {
        // Add-on operations
        $name = trim($_POST['name'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $unit = $_POST['unit'] ?? 'Pcs';
        $unitValue = (float)($_POST['unit_value'] ?? 1);
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($name) || $categoryId <= 0) {
            $error = 'Name and category are required';
        } else {
            $data = [
                'name' => $name,
                'category_id' => $categoryId,
                'price' => $price,
                'unit' => $unit,
                'unit_value' => $unitValue,
                'sort_order' => $sortOrder,
                'is_active' => $isActive
            ];
            
            if (isset($_POST['addon_id']) && $_POST['addon_id']) {
                db()->update('addons', $data, 'id = :id', ['id' => $_POST['addon_id']]);
                $message = 'Add-on updated successfully!';
            } else {
                db()->insert('addons', $data);
                $message = 'Add-on created successfully!';
            }
            header('Location: addons.php?msg=' . urlencode($message));
            exit;
        }
    } elseif ($postType === 'bulk_apply') {
        // Bulk apply add-ons to menu items
        $addonIds = $_POST['addon_ids'] ?? [];
        $menuItemIds = $_POST['menu_item_ids'] ?? [];
        
        if (empty($addonIds) || empty($menuItemIds)) {
            $error = 'Select at least one add-on and one menu item';
        } else {
            $count = 0;
            foreach ($menuItemIds as $menuItemId) {
                foreach ($addonIds as $addonId) {
                    // Check if mapping already exists
                    $exists = db()->fetch(
                        "SELECT id FROM menu_item_addons WHERE menu_item_id = ? AND addon_id = ?",
                        [(int)$menuItemId, (int)$addonId]
                    );
                    if (!$exists) {
                        db()->insert('menu_item_addons', [
                            'menu_item_id' => (int)$menuItemId,
                            'addon_id' => (int)$addonId,
                            'max_quantity' => 5
                        ]);
                        $count++;
                    }
                }
            }
            $message = "Applied {$count} add-on mappings successfully!";
            header('Location: addons.php?msg=' . urlencode($message));
            exit;
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $deleteType = $_GET['type'] ?? 'addon';
    
    if ($deleteType === 'category') {
        db()->delete('addon_categories', 'id = ?', [$deleteId]);
    } else {
        db()->delete('addons', 'id = ?', [$deleteId]);
    }
    header('Location: addons.php?msg=' . urlencode('Deleted successfully'));
    exit;
}

// Get message from URL
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
}

// Get all categories with their add-ons
$categories = db()->fetchAll("SELECT * FROM addon_categories ORDER BY sort_order, name");
$addons = db()->fetchAll(
    "SELECT a.*, c.name as category_name 
     FROM addons a 
     JOIN addon_categories c ON a.category_id = c.id 
     ORDER BY c.sort_order, a.sort_order, a.name"
);

// Get menu items for bulk apply
$menuItems = db()->fetchAll(
    "SELECT id, name, category_id FROM menu_items WHERE is_active = 1 ORDER BY name"
);

// Get edit item if editing
$editItem = null;
$editCategory = null;
if ($editId) {
    if ($type === 'category') {
        $editCategory = db()->fetch("SELECT * FROM addon_categories WHERE id = ?", [$editId]);
    } else {
        $editItem = db()->fetch("SELECT * FROM addons WHERE id = ?", [$editId]);
    }
}

$storeName = getSetting('store_name', 'FoodFlow');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add-ons - <?= htmlspecialchars($storeName) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Karla:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Karla', sans-serif; }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="lg:ml-64 min-h-screen pt-16 lg:pt-0">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Add-ons Management</h1>
                    <p class="text-gray-600">Manage add-on categories and items</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="document.getElementById('categoryModal').classList.remove('hidden')"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition flex items-center gap-2">
                        <span>📁</span> New Category
                    </button>
                    <button onclick="document.getElementById('addonModal').classList.remove('hidden')"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition flex items-center gap-2">
                        <span>➕</span> New Add-on
                    </button>
                    <button onclick="document.getElementById('bulkModal').classList.remove('hidden')"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition flex items-center gap-2">
                        <span>🔗</span> Bulk Apply
                    </button>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-6">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-6">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Categories and Add-ons Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Categories Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-4 border-b border-gray-200">
                        <h2 class="font-bold text-lg">📁 Categories</h2>
                    </div>
                    <div class="p-4 space-y-2">
                        <?php if (empty($categories)): ?>
                            <p class="text-gray-500 text-center py-4">No categories yet</p>
                        <?php else: ?>
                            <!-- All category button -->
                            <button onclick="filterByCategory('all')" 
                                class="category-filter w-full flex items-center justify-between p-3 bg-red-50 border-2 border-red-500 rounded-lg" data-cat="all">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">📋</span>
                                    <div>
                                        <div class="font-medium">All Add-ons</div>
                                        <div class="text-sm text-gray-500"><?= count($addons) ?> items</div>
                                    </div>
                                </div>
                            </button>
                            <?php foreach ($categories as $cat): ?>
                                <button onclick="filterByCategory(<?= $cat['id'] ?>)" 
                                    class="category-filter w-full flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition" data-cat="<?= $cat['id'] ?>">
                                    <div class="flex items-center gap-3">
                                        <span class="text-2xl"><?= htmlspecialchars($cat['icon']) ?></span>
                                        <div>
                                            <div class="font-medium"><?= htmlspecialchars($cat['name']) ?></div>
                                            <div class="text-sm text-gray-500">
                                                <?= count(array_filter($addons, fn($a) => $a['category_id'] == $cat['id'])) ?> items
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-1 rounded text-xs <?= $cat['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' ?>">
                                            <?= $cat['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </div>
                                </button>
                                <div class="flex gap-2 pl-12 -mt-1 mb-2">
                                    <a href="addons.php?id=<?= $cat['id'] ?>&type=category" class="text-blue-600 hover:underline text-xs">Edit</a>
                                    <a href="addons.php?delete=<?= $cat['id'] ?>&type=category" 
                                       onclick="return confirm('Delete this category and all its add-ons?')"
                                       class="text-red-600 hover:underline text-xs">Delete</a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Add-ons Card (spans 2 columns) -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="font-bold text-lg">🔹 Add-ons</h2>
                        <span id="addonCount" class="text-gray-500 text-sm"><?= count($addons) ?> items</span>
                    </div>
                    <div class="p-4">
                        <?php if (empty($addons)): ?>
                            <p class="text-gray-500 text-center py-8">No add-ons yet. Create your first add-on!</p>
                        <?php else: ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3" id="addonsGrid">
                                <?php foreach ($addons as $addon): ?>
                                    <div class="addon-item flex items-center justify-between p-3 bg-gray-50 rounded-lg" data-category="<?= $addon['category_id'] ?>">
                                        <div class="flex-1">
                                            <div class="font-medium"><?= htmlspecialchars($addon['name']) ?></div>
                                            <div class="text-sm text-gray-500">
                                                <?= htmlspecialchars($addon['category_name']) ?> • 
                                                <span class="text-green-600 font-medium">+$<?= number_format($addon['price'], 2) ?></span> / <?= $addon['unit_value'] ?><?= $addon['unit'] ?>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-1 rounded text-xs <?= $addon['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' ?>">
                                                <?= $addon['is_active'] ? 'Active' : 'Off' ?>
                                            </span>
                                            <a href="addons.php?id=<?= $addon['id'] ?>&type=addon" class="text-blue-600 hover:underline text-sm">Edit</a>
                                            <a href="addons.php?delete=<?= $addon['id'] ?>&type=addon" 
                                               onclick="return confirm('Delete this add-on?')"
                                               class="text-red-600 hover:underline text-sm">Delete</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Category Modal -->
    <div id="categoryModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center <?= $editCategory ? '' : 'hidden' ?>">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md mx-4">
            <h3 class="text-xl font-bold mb-4"><?= $editCategory ? 'Edit' : 'New' ?> Category</h3>
            <form method="POST">
                <input type="hidden" name="type" value="category">
                <?php if ($editCategory): ?>
                    <input type="hidden" name="category_id" value="<?= $editCategory['id'] ?>">
                <?php endif; ?>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Icon</label>
                        <input type="text" name="icon" value="<?= htmlspecialchars($editCategory['icon'] ?? '🔹') ?>"
                            class="w-full px-4 py-2 border rounded-lg text-2xl text-center" maxlength="10">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($editCategory['name'] ?? '') ?>" required
                            class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="2" class="w-full px-4 py-2 border rounded-lg"><?= htmlspecialchars($editCategory['description'] ?? '') ?></textarea>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                            <input type="number" name="sort_order" value="<?= $editCategory['sort_order'] ?? 0 ?>"
                                class="w-full px-4 py-2 border rounded-lg">
                        </div>
                        <div class="flex items-center gap-2 pt-6">
                            <input type="checkbox" name="is_active" id="catActive" <?= ($editCategory['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <label for="catActive" class="text-sm">Active</label>
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="window.location='addons.php'" 
                        class="flex-1 px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <?= $editCategory ? 'Update' : 'Create' ?> Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add-on Modal -->
    <div id="addonModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center <?= $editItem ? '' : 'hidden' ?>">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md mx-4">
            <h3 class="text-xl font-bold mb-4"><?= $editItem ? 'Edit' : 'New' ?> Add-on</h3>
            <form method="POST">
                <input type="hidden" name="type" value="addon">
                <?php if ($editItem): ?>
                    <input type="hidden" name="addon_id" value="<?= $editItem['id'] ?>">
                <?php endif; ?>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($editItem['name'] ?? '') ?>" required
                            class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <select name="category_id" required class="w-full px-4 py-2 border rounded-lg">
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($editItem['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['icon'] . ' ' . $cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Price ($)</label>
                            <input type="number" step="0.01" name="price" value="<?= $editItem['price'] ?? '0.00' ?>"
                                class="w-full px-4 py-2 border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Qty</label>
                            <input type="number" step="0.01" name="unit_value" value="<?= $editItem['unit_value'] ?? '1' ?>"
                                class="w-full px-4 py-2 border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                            <select name="unit" class="w-full px-4 py-2 border rounded-lg">
                                <?php foreach ($unitOptions as $unit): ?>
                                    <option value="<?= $unit ?>" <?= ($editItem['unit'] ?? 'Pcs') === $unit ? 'selected' : '' ?>>
                                        <?= $unit ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                            <input type="number" name="sort_order" value="<?= $editItem['sort_order'] ?? 0 ?>"
                                class="w-full px-4 py-2 border rounded-lg">
                        </div>
                        <div class="flex items-center gap-2 pt-6">
                            <input type="checkbox" name="is_active" id="addonActive" <?= ($editItem['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <label for="addonActive" class="text-sm">Active</label>
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="window.location='addons.php'" 
                        class="flex-1 px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        <?= $editItem ? 'Update' : 'Create' ?> Add-on
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Apply Modal -->
    <div id="bulkModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-2xl w-full max-w-5xl mx-4 max-h-[95vh] overflow-hidden flex flex-col">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-2xl font-bold">🔗 Bulk Apply Add-ons to Menu Items</h3>
                <p class="text-gray-600 mt-1">Select add-ons and menu items to create mappings</p>
            </div>
            
            <form method="POST" class="flex-1 overflow-hidden flex flex-col">
                <input type="hidden" name="type" value="bulk_apply">
                
                <div class="flex-1 grid grid-cols-2 gap-6 p-6 overflow-hidden">
                    <!-- Add-ons Selection -->
                    <div class="flex flex-col h-full">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-bold text-lg">📦 Select Add-ons</h4>
                            <label class="flex items-center gap-2 text-blue-600 cursor-pointer">
                                <input type="checkbox" onclick="toggleAllAddons(this)">
                                <span class="text-sm">Select All</span>
                            </label>
                        </div>
                        <div class="border rounded-xl p-4 flex-1 overflow-y-auto bg-gray-50 space-y-4">
                            <?php foreach ($categories as $cat): ?>
                                <div class="bg-white rounded-lg p-3 shadow-sm">
                                    <div class="font-medium text-gray-800 border-b pb-2 mb-2 flex items-center gap-2">
                                        <span class="text-xl"><?= htmlspecialchars($cat['icon']) ?></span>
                                        <?= htmlspecialchars($cat['name']) ?>
                                        <span class="text-xs text-gray-400">(<?= count(array_filter($addons, fn($a) => $a['category_id'] == $cat['id'])) ?>)</span>
                                    </div>
                                    <div class="space-y-1">
                                        <?php foreach ($addons as $addon): ?>
                                            <?php if ($addon['category_id'] == $cat['id']): ?>
                                                <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                                    <input type="checkbox" name="addon_ids[]" value="<?= $addon['id'] ?>" class="addon-cb">
                                                    <span class="flex-1"><?= htmlspecialchars($addon['name']) ?></span>
                                                    <span class="text-green-600 font-medium text-sm">+$<?= number_format($addon['price'], 2) ?></span>
                                                </label>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Menu Items Selection -->
                    <div class="flex flex-col h-full">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-bold text-lg">🍽️ Select Menu Items</h4>
                            <label class="flex items-center gap-2 text-blue-600 cursor-pointer">
                                <input type="checkbox" id="selectAllItems" onclick="toggleAllItems(this)">
                                <span class="text-sm">Select All</span>
                            </label>
                        </div>
                        <div class="border rounded-xl p-4 flex-1 overflow-y-auto bg-gray-50">
                            <div class="grid grid-cols-2 gap-2">
                                <?php foreach ($menuItems as $item): ?>
                                    <label class="flex items-center gap-2 p-2 bg-white rounded-lg hover:bg-blue-50 cursor-pointer shadow-sm">
                                        <input type="checkbox" name="menu_item_ids[]" value="<?= $item['id'] ?>" class="menu-item-cb">
                                        <span class="truncate"><?= htmlspecialchars($item['name']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="p-6 border-t border-gray-200 bg-gray-50 flex gap-4">
                    <button type="button" onclick="document.getElementById('bulkModal').classList.add('hidden')" 
                        class="flex-1 px-6 py-3 border-2 border-gray-300 rounded-xl hover:bg-gray-100 font-medium">Cancel</button>
                    <button type="submit" class="flex-1 px-6 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 font-medium">
                        ✅ Apply Selected Add-ons
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Category filtering
        function filterByCategory(categoryId) {
            const items = document.querySelectorAll('.addon-item');
            const buttons = document.querySelectorAll('.category-filter');
            let visibleCount = 0;
            
            // Update button styles
            buttons.forEach(btn => {
                const cat = btn.dataset.cat;
                if (cat == categoryId || (categoryId === 'all' && cat === 'all')) {
                    btn.classList.remove('bg-gray-50', 'hover:bg-gray-100');
                    btn.classList.add('bg-red-50', 'border-2', 'border-red-500');
                } else {
                    btn.classList.remove('bg-red-50', 'border-2', 'border-red-500');
                    btn.classList.add('bg-gray-50', 'hover:bg-gray-100');
                }
            });
            
            // Filter items
            items.forEach(item => {
                if (categoryId === 'all' || item.dataset.category == categoryId) {
                    item.style.display = 'flex';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Update count
            document.getElementById('addonCount').textContent = visibleCount + ' items';
        }

        function toggleAllItems(checkbox) {
            document.querySelectorAll('.menu-item-cb').forEach(cb => cb.checked = checkbox.checked);
        }
        
        function toggleAllAddons(checkbox) {
            document.querySelectorAll('.addon-cb').forEach(cb => cb.checked = checkbox.checked);
        }
    </script>
</body>
</html>
