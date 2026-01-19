<?php
/**
 * FoodFlow - Database Configuration
 * Copy this file to config.php and update with your database credentials
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'foodflow');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_CHARSET', 'utf8mb4');

// Application Settings
define('APP_NAME', 'FoodFlow');
define('APP_URL', 'https://your-domain.com'); // No trailing slash
define('APP_TIMEZONE', 'America/New_York');

// Security
define('SESSION_NAME', 'foodflow_session');
define('CSRF_TOKEN_NAME', 'csrf_token');

// File Upload
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Error reporting (disable in production)
define('DEBUG_MODE', true);
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
