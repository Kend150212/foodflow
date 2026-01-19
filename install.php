<?php
/**
 * FoodFlow - Web Installer
 * Delete this file after installation!
 */

session_start();

$errors = [];
$success = false;
$step = isset($_GET['step']) ? (int) $_GET['step'] : 1;

// Check if already installed
if (file_exists(__DIR__ . '/includes/config.php') && $step === 1) {
    $config = file_get_contents(__DIR__ . '/includes/config.php');
    if (strpos($config, 'your_username') === false && strpos($config, 'your_password') === false) {
        // Config seems to be set, check if DB works
        try {
            require_once __DIR__ . '/includes/db.php';
            $test = db()->fetch("SELECT 1");
            if ($test) {
                header('Location: index.php');
                exit;
            }
        } catch (Exception $e) {
            // Continue with installation
        }
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 1) {
        // Database setup
        $dbHost = trim($_POST['db_host'] ?? 'localhost');
        $dbName = trim($_POST['db_name'] ?? '');
        $dbUser = trim($_POST['db_user'] ?? '');
        $dbPass = $_POST['db_pass'] ?? '';
        $appUrl = trim($_POST['app_url'] ?? '');

        if (empty($dbName) || empty($dbUser)) {
            $errors[] = 'Database name and username are required';
        } else {
            // Test connection
            try {
                $pdo = new PDO(
                    "mysql:host={$dbHost};charset=utf8mb4",
                    $dbUser,
                    $dbPass,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );

                // Create database if not exists
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `{$dbName}`");

                // Import schema
                $sql = file_get_contents(__DIR__ . '/database.sql');
                $pdo->exec($sql);

                // Update config file
                $config = file_get_contents(__DIR__ . '/includes/config.sample.php');
                $config = str_replace("'localhost'", "'{$dbHost}'", $config);
                $config = str_replace("'foodflow'", "'{$dbName}'", $config);
                $config = str_replace("'your_username'", "'{$dbUser}'", $config);
                $config = str_replace("'your_password'", "'{$dbPass}'", $config);

                if (!empty($appUrl)) {
                    $config = str_replace("'https://your-domain.com'", "'{$appUrl}'", $config);
                }

                file_put_contents(__DIR__ . '/includes/config.php', $config);

                $_SESSION['install_step'] = 2;
                header('Location: install.php?step=2');
                exit;

            } catch (PDOException $e) {
                $errors[] = 'Database connection failed: ' . $e->getMessage();
            }
        }
    } elseif ($step === 2) {
        // Admin account setup
        require_once __DIR__ . '/includes/db.php';

        $adminUser = trim($_POST['admin_user'] ?? '');
        $adminEmail = trim($_POST['admin_email'] ?? '');
        $adminPass = $_POST['admin_pass'] ?? '';
        $adminPassConfirm = $_POST['admin_pass_confirm'] ?? '';
        $storeName = trim($_POST['store_name'] ?? 'FoodFlow');

        if (empty($adminUser) || empty($adminEmail) || empty($adminPass)) {
            $errors[] = 'All fields are required';
        } elseif ($adminPass !== $adminPassConfirm) {
            $errors[] = 'Passwords do not match';
        } elseif (strlen($adminPass) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        } elseif (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address';
        } else {
            try {
                // Create admin user
                $hashedPass = password_hash($adminPass, PASSWORD_DEFAULT);
                db()->insert('admins', [
                    'username' => $adminUser,
                    'email' => $adminEmail,
                    'password' => $hashedPass,
                    'name' => $adminUser,
                    'role' => 'super_admin'
                ]);

                // Update store name
                db()->update(
                    'settings',
                    ['setting_value' => $storeName],
                    'setting_key = :key',
                    ['key' => 'store_name']
                );

                $success = true;

            } catch (Exception $e) {
                $errors[] = 'Failed to create admin: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodFlow - Installation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Karla:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Karla', sans-serif;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-red-50 to-orange-50 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-8">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-red-600 rounded-2xl mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">FoodFlow Installation</h1>
            <p class="text-gray-500 mt-1">Step
                <?= $step ?> of 2
            </p>
        </div>

        <!-- Progress -->
        <div class="flex items-center justify-center mb-8">
            <div class="flex items-center">
                <div
                    class="w-8 h-8 rounded-full flex items-center justify-center <?= $step >= 1 ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-500' ?>">
                    1</div>
                <div class="w-16 h-1 <?= $step >= 2 ? 'bg-red-600' : 'bg-gray-200' ?>"></div>
                <div
                    class="w-8 h-8 rounded-full flex items-center justify-center <?= $step >= 2 ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-500' ?>">
                    2</div>
            </div>
        </div>

        <?php if ($success): ?>
            <!-- Success -->
            <div class="text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">Installation Complete!</h2>
                <p class="text-gray-600 mb-6">Your FoodFlow store is ready to go.</p>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6 text-left">
                    <p class="text-yellow-800 text-sm font-medium">⚠️ Security Reminder</p>
                    <p class="text-yellow-700 text-sm mt-1">Delete this install.php file from your server!</p>
                </div>

                <div class="space-y-3">
                    <a href="admin/index.php"
                        class="block w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-4 rounded-lg transition">
                        Go to Admin Panel
                    </a>
                    <a href="index.php"
                        class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-4 rounded-lg transition">
                        View Store
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Errors -->
            <?php if (!empty($errors)): ?>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <?php foreach ($errors as $error): ?>
                        <p class="text-red-700 text-sm">
                            <?= htmlspecialchars($error) ?>
                        </p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($step === 1): ?>
                <!-- Step 1: Database -->
                <form method="POST" class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Database Configuration</h2>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Database Host</label>
                        <input type="text" name="db_host" value="localhost" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Database Name</label>
                        <input type="text" name="db_name" value="foodflow" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Database Username</label>
                        <input type="text" name="db_user" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Database Password</label>
                        <input type="password" name="db_pass"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Site URL (optional)</label>
                        <input type="url" name="app_url" placeholder="https://your-domain.com"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <button type="submit"
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-4 rounded-lg transition mt-6">
                        Connect & Continue
                    </button>
                </form>
            <?php elseif ($step === 2): ?>
                <!-- Step 2: Admin Account -->
                <form method="POST" class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Create Admin Account</h2>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Store Name</label>
                        <input type="text" name="store_name" value="FoodFlow" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Admin Username</label>
                        <input type="text" name="admin_user" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Admin Email</label>
                        <input type="email" name="admin_email" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" name="admin_pass" required minlength="8"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input type="password" name="admin_pass_confirm" required minlength="8"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <button type="submit"
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-4 rounded-lg transition mt-6">
                        Complete Installation
                    </button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>

</html>