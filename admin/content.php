<?php
/**
 * FoodFlow - Landing Page Content Editor
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAuth();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? '';

    foreach ($_POST as $key => $value) {
        if ($key === 'section')
            continue;

        // Check if it's a JSON field
        $contentType = 'text';
        if (in_array($key, ['items'])) {
            $contentType = 'json';
        } elseif (strpos($key, 'image') !== false) {
            $contentType = 'image';
        } elseif (strpos($key, 'description') !== false || strpos($key, 'subtitle') !== false || strpos($key, 'text') !== false) {
            $contentType = 'textarea';
        }

        // Update or insert
        $existing = db()->fetch(
            "SELECT id FROM landing_content WHERE section = ? AND content_key = ?",
            [$section, $key]
        );

        if ($existing) {
            db()->update(
                'landing_content',
                ['content_value' => $value, 'content_type' => $contentType],
                'section = :section AND content_key = :key',
                ['section' => $section, 'key' => $key]
            );
        } else {
            db()->insert('landing_content', [
                'section' => $section,
                'content_key' => $key,
                'content_value' => $value,
                'content_type' => $contentType
            ]);
        }
    }

    $message = 'Content saved successfully!';
}

// Get current content
$hero = getLandingContent('hero');
$about = getLandingContent('about');
$features = getLandingContent('features');
$testimonials = getLandingContent('testimonials');
$cta = getLandingContent('cta');

$storeName = getSetting('store_name', 'FoodFlow');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Landing Page - Admin</title>
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
            <a href="content.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gray-800 text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Landing Page
            </a>
            <a href="menu.php"
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253" />
                </svg>
                Menu Items
            </a>
            <a href="settings.php"
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0" />
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
                <h1 class="text-2xl font-bold text-gray-900">Landing Page Content</h1>
                <p class="text-gray-500 text-sm">Edit your homepage content</p>
            </div>
            <a href="../index.php" target="_blank" class="text-red-600 hover:text-red-700 font-medium">Preview →</a>
        </header>

        <div class="p-6">
            <?php if ($message): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-6">
                    ✅
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="space-y-6 max-w-3xl">
                <!-- Hero Section -->
                <form method="POST" class="bg-white rounded-xl shadow-sm border p-6">
                    <input type="hidden" name="section" value="hero">
                    <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <span
                            class="w-8 h-8 bg-red-100 text-red-600 rounded-lg flex items-center justify-center text-sm">1</span>
                        Hero Section
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                            <input type="text" name="title" value="<?= htmlspecialchars($hero['title'] ?? '') ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500"
                                placeholder="Authentic Flavors, Delivered Fresh">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                            <textarea name="subtitle" rows="2"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500"
                                placeholder="Experience restaurant-quality meals..."><?= htmlspecialchars($hero['subtitle'] ?? '') ?></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CTA Button Text</label>
                                <input type="text" name="cta_text"
                                    value="<?= htmlspecialchars($hero['cta_text'] ?? 'Order Now') ?>"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CTA Link</label>
                                <input type="text" name="cta_link"
                                    value="<?= htmlspecialchars($hero['cta_link'] ?? 'menu.php') ?>"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                            </div>
                        </div>
                    </div>
                    <button type="submit"
                        class="mt-4 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition">
                        Save Hero Section
                    </button>
                </form>

                <!-- About Section -->
                <form method="POST" class="bg-white rounded-xl shadow-sm border p-6">
                    <input type="hidden" name="section" value="about">
                    <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <span
                            class="w-8 h-8 bg-red-100 text-red-600 rounded-lg flex items-center justify-center text-sm">2</span>
                        About Section
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                            <input type="text" name="title"
                                value="<?= htmlspecialchars($about['title'] ?? 'Our Story') ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="4"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500"><?= htmlspecialchars($about['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <button type="submit"
                        class="mt-4 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition">
                        Save About Section
                    </button>
                </form>

                <!-- CTA Section -->
                <form method="POST" class="bg-white rounded-xl shadow-sm border p-6">
                    <input type="hidden" name="section" value="cta">
                    <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <span
                            class="w-8 h-8 bg-red-100 text-red-600 rounded-lg flex items-center justify-center text-sm">3</span>
                        Call to Action Section
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                            <input type="text" name="title"
                                value="<?= htmlspecialchars($cta['title'] ?? 'Hungry? Order Now!') ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                            <input type="text" name="subtitle" value="<?= htmlspecialchars($cta['subtitle'] ?? '') ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500"
                                placeholder="Free delivery on orders over $35">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Button Text</label>
                                <input type="text" name="button_text"
                                    value="<?= htmlspecialchars($cta['button_text'] ?? 'View Menu') ?>"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Button Link</label>
                                <input type="text" name="button_link"
                                    value="<?= htmlspecialchars($cta['button_link'] ?? 'menu.php') ?>"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                            </div>
                        </div>
                    </div>
                    <button type="submit"
                        class="mt-4 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition">
                        Save CTA Section
                    </button>
                </form>
            </div>
        </div>
    </main>
</body>

</html>