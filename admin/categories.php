<?php
/**
 * FoodFlow - Categories Management
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAuth();

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $icon = $_POST['icon'] ?? 'utensils';
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        if ($name) {
            db()->insert('categories', [
                'name' => $name,
                'icon' => $icon,
                'sort_order' => $sortOrder,
                'is_active' => 1
            ]);
            $message = 'Category added successfully!';
        }
    } elseif ($action === 'update') {
        $id = (int) $_POST['id'];
        $name = trim($_POST['name'] ?? '');
        $icon = $_POST['icon'] ?? 'utensils';
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($name && $id) {
            db()->update('categories', [
                'name' => $name,
                'icon' => $icon,
                'sort_order' => $sortOrder,
                'is_active' => $isActive
            ], 'id = :id', ['id' => $id]);
            $message = 'Category updated!';
        }
    } elseif ($action === 'delete') {
        $id = (int) $_POST['id'];
        // Check if category has items
        $itemCount = db()->fetch("SELECT COUNT(*) as count FROM menu_items WHERE category_id = ?", [$id]);
        if ($itemCount['count'] > 0) {
            $error = "Cannot delete: This category has {$itemCount['count']} menu items.";
        } else {
            db()->delete('categories', 'id = ?', [$id]);
            $message = 'Category deleted!';
        }
    }
}

$categories = db()->fetchAll("SELECT c.*, COUNT(m.id) as item_count FROM categories c LEFT JOIN menu_items m ON c.id = m.category_id GROUP BY c.id ORDER BY c.sort_order");

$icons = ['utensils', 'fire', 'bowl-food', 'glass-water', 'ice-cream', 'pizza', 'burger', 'coffee'];

$storeName = getSetting('store_name', 'FoodFlow');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Karla:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Karla', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <aside class="fixed inset-y-0 left-0 w-64 bg-gray-900 text-white p-6 hidden lg:block">
        <div class="flex items-center gap-3 mb-8">
            <div class="w-10 h-10 bg-red-600 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            </div>
            <span class="font-bold text-lg">
                <?= htmlspecialchars($storeName) ?>
            </span>
        </div>

        <nav class="space-y-1">
            <a href="index.php"
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>
            <a href="orders.php"
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Orders
            </a>
            <a href="menu.php"
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253" />
                </svg>
                Menu Items
            </a>
            <a href="categories.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gray-800 text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                Categories
            </a>
            <a href="content.php"
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Landing Page
            </a>
            <a href="settings.php"
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Settings
            </a>
        </nav>
    </aside>

    <main class="lg:ml-64 min-h-screen">
        <header class="bg-white border-b px-6 py-4 sticky top-0 z-10 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Categories</h1>
                <p class="text-gray-500 text-sm">
                    <?= count($categories) ?> categories
                </p>
            </div>
        </header>

        <div class="p-6">
            <?php if ($message): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-6">✅
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-6">❌
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Add New Category -->
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h2 class="text-lg font-bold mb-4">Add Category</h2>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="add">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                            <input type="text" name="name" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500"
                                placeholder="e.g., Main Dishes">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Icon</label>
                            <select name="icon"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                                <?php foreach ($icons as $icon): ?>
                                    <option value="<?= $icon ?>">
                                        <?= ucfirst($icon) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                            <input type="number" name="sort_order" value="0"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <button type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition">
                            Add Category
                        </button>
                    </form>
                </div>

                <!-- Existing Categories -->
                <div class="bg-white rounded-xl shadow-sm border">
                    <div class="p-4 border-b">
                        <h2 class="font-bold">Existing Categories</h2>
                    </div>
                    <div class="divide-y">
                        <?php if (empty($categories)): ?>
                            <div class="p-6 text-center text-gray-500">No categories yet</div>
                        <?php else: ?>
                            <?php foreach ($categories as $cat): ?>
                                <div class="p-4 flex items-center justify-between hover:bg-gray-50">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center text-red-600">
                                            <?= $cat['sort_order'] ?>
                                        </div>
                                        <div>
                                            <div class="font-medium <?= $cat['is_active'] ? '' : 'text-gray-400' ?>">
                                                <?= htmlspecialchars($cat['name']) ?>
                                                <?php if (!$cat['is_active']): ?>
                                                    <span class="text-xs text-gray-400">(inactive)</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?= $cat['item_count'] ?> items
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button onclick="editCategory(<?= htmlspecialchars(json_encode($cat)) ?>)"
                                            class="text-blue-600 hover:text-blue-800 text-sm">Edit</button>
                                        <form method="POST" class="inline" onsubmit="return confirm('Delete this category?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-2xl max-w-md w-full mx-4 p-6">
            <h3 class="text-xl font-bold mb-4">Edit Category</h3>
            <form method="POST" id="editForm" class="space-y-4">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="editId">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" id="editName" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Icon</label>
                    <select name="icon" id="editIcon"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        <?php foreach ($icons as $icon): ?>
                            <option value="<?= $icon ?>">
                                <?= ucfirst($icon) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                    <input type="number" name="sort_order" id="editSort"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" id="editActive" value="1"
                        class="w-4 h-4 text-red-600 rounded">
                    <span class="text-sm">Active</span>
                </label>
                <div class="flex gap-2">
                    <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium">Save</button>
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editCategory(cat) {
            document.getElementById('editId').value = cat.id;
            document.getElementById('editName').value = cat.name;
            document.getElementById('editIcon').value = cat.icon;
            document.getElementById('editSort').value = cat.sort_order;
            document.getElementById('editActive').checked = cat.is_active == 1;
            document.getElementById('editModal').classList.remove('hidden');
        }
    </script>
</body>

</html>