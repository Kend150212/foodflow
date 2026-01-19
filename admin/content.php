<?php
/**
 * FoodFlow - Landing Page Content Editor with AI Generation
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAuth();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['section'])) {
    $section = $_POST['section'] ?? '';

    foreach ($_POST as $key => $value) {
        if ($key === 'section')
            continue;

        $contentType = 'text';
        if (in_array($key, ['items', 'features', 'testimonials'])) {
            $contentType = 'json';
        } elseif (strpos($key, 'image') !== false) {
            $contentType = 'image';
        } elseif (strpos($key, 'description') !== false || strpos($key, 'subtitle') !== false) {
            $contentType = 'textarea';
        }

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

// Handle AI-generated content save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ai_content'])) {
    $content = json_decode($_POST['ai_content'], true);
    if ($content) {
        // Save hero
        if (isset($content['hero'])) {
            foreach ($content['hero'] as $key => $value) {
                $existing = db()->fetch("SELECT id FROM landing_content WHERE section = 'hero' AND content_key = ?", [$key]);
                if ($existing) {
                    db()->update('landing_content', ['content_value' => $value], 'section = :s AND content_key = :k', ['s' => 'hero', 'k' => $key]);
                } else {
                    db()->insert('landing_content', ['section' => 'hero', 'content_key' => $key, 'content_value' => $value, 'content_type' => 'text']);
                }
            }
        }
        // Save about
        if (isset($content['about'])) {
            foreach ($content['about'] as $key => $value) {
                $existing = db()->fetch("SELECT id FROM landing_content WHERE section = 'about' AND content_key = ?", [$key]);
                if ($existing) {
                    db()->update('landing_content', ['content_value' => $value], 'section = :s AND content_key = :k', ['s' => 'about', 'k' => $key]);
                } else {
                    db()->insert('landing_content', ['section' => 'about', 'content_key' => $key, 'content_value' => $value, 'content_type' => 'text']);
                }
            }
        }
        // Save CTA
        if (isset($content['cta'])) {
            foreach ($content['cta'] as $key => $value) {
                $existing = db()->fetch("SELECT id FROM landing_content WHERE section = 'cta' AND content_key = ?", [$key]);
                if ($existing) {
                    db()->update('landing_content', ['content_value' => $value], 'section = :s AND content_key = :k', ['s' => 'cta', 'k' => $key]);
                } else {
                    db()->insert('landing_content', ['section' => 'cta', 'content_key' => $key, 'content_value' => $value, 'content_type' => 'text']);
                }
            }
        }
        $message = '✨ AI-generated content saved successfully!';
    }
}

$hero = getLandingContent('hero');
$about = getLandingContent('about');
$cta = getLandingContent('cta');
$hasApiKey = !empty(getSetting('gemini_api_key')) || !empty(getSetting('openai_api_key'));

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

        .pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }

        @keyframes pulse-glow {

            0%,
            100% {
                box-shadow: 0 0 5px rgba(139, 92, 246, 0.5);
            }

            50% {
                box-shadow: 0 0 20px rgba(139, 92, 246, 0.8);
            }
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
            <span class="font-bold text-lg"><?= htmlspecialchars($storeName) ?></span>
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
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0" />
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
            <a href="../index.php" target="_blank"
                class="text-red-600 hover:text-red-700 font-medium flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                Preview Site
            </a>
        </header>

        <div class="p-6">
            <?php if ($message): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-6">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- AI Generation Section -->
            <div
                class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl p-6 mb-6 text-white <?= $hasApiKey ? 'pulse-glow' : '' ?>">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-xl font-bold mb-2">🤖 AI Content Generator</h2>
                        <p class="text-purple-200 mb-4">Describe your restaurant and let AI create compelling landing
                            page content for you!</p>

                        <?php if (!$hasApiKey): ?>
                            <div class="bg-white/10 rounded-lg p-4 mb-4">
                                <p class="text-sm">⚠️ Please configure your AI API key first:</p>
                                <a href="settings.php?tab=ai"
                                    class="inline-block mt-2 bg-white text-purple-600 px-4 py-2 rounded-lg font-medium hover:bg-purple-50 transition">
                                    Configure AI Settings →
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Describe your restaurant:</label>
                                    <textarea id="aiDescription" rows="3"
                                        class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/20 text-white placeholder-purple-200 focus:bg-white/20 focus:outline-none"
                                        placeholder="Example: We are a family-owned Vietnamese pho restaurant in Houston, TX. We've been serving authentic pho and banh mi for 15 years. Our specialty is slow-cooked beef pho with homemade noodles. We focus on fresh ingredients and traditional recipes passed down from grandmother."><?= htmlspecialchars($_POST['ai_description'] ?? '') ?></textarea>
                                </div>
                                <button id="generateBtn" onclick="generateWithAI()"
                                    class="bg-white text-purple-600 px-6 py-2 rounded-lg font-medium hover:bg-purple-50 transition flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    Generate with AI
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- AI Generated Preview -->
                <div id="aiPreview" class="hidden mt-6 bg-white/10 rounded-lg p-4">
                    <h3 class="font-bold mb-3">📝 Generated Content Preview</h3>
                    <div id="aiPreviewContent" class="space-y-3 text-sm"></div>
                    <form method="POST" class="mt-4">
                        <input type="hidden" name="ai_content" id="aiContentInput">
                        <div class="flex gap-2">
                            <button type="submit"
                                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-medium transition">
                                ✅ Apply Content
                            </button>
                            <button type="button" onclick="regenerate()"
                                class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg font-medium transition">
                                🔄 Regenerate
                            </button>
                        </div>
                    </form>
                </div>
            </div>

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
                            <input type="text" name="title" id="heroTitle"
                                value="<?= htmlspecialchars($hero['title'] ?? '') ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500"
                                placeholder="Authentic Flavors, Delivered Fresh">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                            <textarea name="subtitle" id="heroSubtitle" rows="2"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500"
                                placeholder="Experience restaurant-quality meals..."><?= htmlspecialchars($hero['subtitle'] ?? '') ?></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CTA Button Text</label>
                                <input type="text" name="cta_text" id="heroCta"
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
                            <input type="text" name="title" id="aboutTitle"
                                value="<?= htmlspecialchars($about['title'] ?? 'Our Story') ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" id="aboutDesc" rows="4"
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
                            <input type="text" name="title" id="ctaTitle"
                                value="<?= htmlspecialchars($cta['title'] ?? 'Hungry? Order Now!') ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                            <input type="text" name="subtitle" id="ctaSubtitle"
                                value="<?= htmlspecialchars($cta['subtitle'] ?? '') ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Button Text</label>
                                <input type="text" name="button_text" id="ctaBtn"
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

    <script>
        let generatedContent = null;

        async function generateWithAI() {
            const description = document.getElementById('aiDescription').value.trim();
            if (!description) {
                alert('Please describe your restaurant first!');
                return;
            }

            const btn = document.getElementById('generateBtn');
            btn.disabled = true;
            btn.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Generating...';

            try {
                const response = await fetch('../api/ai-generate.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ description })
                });

                const data = await response.json();

                if (data.success) {
                    generatedContent = data.content;
                    showPreview(data.content);
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (err) {
                alert('Error: ' + err.message);
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> Generate with AI';
            }
        }

        function showPreview(content) {
            const preview = document.getElementById('aiPreview');
            const previewContent = document.getElementById('aiPreviewContent');

            let html = '';
            if (content.hero) {
                html += `<div class="bg-white/10 rounded p-3"><strong>Hero:</strong><br>"${content.hero.title}"<br><span class="text-purple-200">${content.hero.subtitle}</span></div>`;
            }
            if (content.about) {
                html += `<div class="bg-white/10 rounded p-3"><strong>About:</strong><br>${content.about.title}<br><span class="text-purple-200">${content.about.description}</span></div>`;
            }
            if (content.cta) {
                html += `<div class="bg-white/10 rounded p-3"><strong>CTA:</strong><br>${content.cta.title}<br><span class="text-purple-200">${content.cta.subtitle}</span></div>`;
            }

            previewContent.innerHTML = html;
            document.getElementById('aiContentInput').value = JSON.stringify(content);
            preview.classList.remove('hidden');

            // Also fill the forms
            if (content.hero) {
                document.getElementById('heroTitle').value = content.hero.title || '';
                document.getElementById('heroSubtitle').value = content.hero.subtitle || '';
                document.getElementById('heroCta').value = content.hero.cta_text || 'Order Now';
            }
            if (content.about) {
                document.getElementById('aboutTitle').value = content.about.title || '';
                document.getElementById('aboutDesc').value = content.about.description || '';
            }
            if (content.cta) {
                document.getElementById('ctaTitle').value = content.cta.title || '';
                document.getElementById('ctaSubtitle').value = content.cta.subtitle || '';
                document.getElementById('ctaBtn').value = content.cta.button_text || 'View Menu';
            }
        }

        function regenerate() {
            generateWithAI();
        }
    </script>
</body>

</html>