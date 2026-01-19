-- =====================================================
-- FoodFlow Database Schema
-- Food Ordering System for US Market
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- =====================================================
-- Settings Table - Store configuration
-- =====================================================
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `setting_key` VARCHAR(100) UNIQUE NOT NULL,
    `setting_value` TEXT,
    `setting_type` ENUM('text', 'textarea', 'number', 'boolean', 'json') DEFAULT 'text',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`) VALUES
('store_name', 'FoodFlow', 'text'),
('store_tagline', 'Delicious Food, Delivered Fast', 'text'),
('store_description', 'Experience the best local cuisine delivered right to your doorstep', 'textarea'),
('store_logo', '', 'text'),
('store_address', '123 Main Street, City, State 12345', 'text'),
('store_phone', '(555) 123-4567', 'text'),
('store_email', 'hello@foodflow.com', 'text'),
('currency', 'USD', 'text'),
('currency_symbol', '$', 'text'),
('tax_rate', '8.25', 'number'),
('min_order_amount', '15.00', 'number'),
('delivery_fee', '4.99', 'number'),
('free_delivery_min', '35.00', 'number'),
('estimated_prep_time', '25', 'number'),
('estimated_delivery_time', '35', 'number'),
-- Operating hours (JSON: {day: {open: "HH:MM", close: "HH:MM", closed: bool}})
('operating_hours', '{"monday":{"open":"10:00","close":"21:00","closed":false},"tuesday":{"open":"10:00","close":"21:00","closed":false},"wednesday":{"open":"10:00","close":"21:00","closed":false},"thursday":{"open":"10:00","close":"21:00","closed":false},"friday":{"open":"10:00","close":"22:00","closed":false},"saturday":{"open":"11:00","close":"22:00","closed":false},"sunday":{"open":"11:00","close":"20:00","closed":false}}', 'json'),
-- Payment gateways
('stripe_enabled', '1', 'boolean'),
('stripe_public_key', '', 'text'),
('stripe_secret_key', '', 'text'),
('paypal_enabled', '1', 'boolean'),
('paypal_client_id', '', 'text'),
('paypal_secret', '', 'text'),
('venmo_enabled', '1', 'boolean'),
('cashapp_enabled', '0', 'boolean'),
('square_app_id', '', 'text'),
('square_access_token', '', 'text'),
-- Social links
('facebook_url', '', 'text'),
('instagram_url', '', 'text'),
('twitter_url', '', 'text');

-- =====================================================
-- Admins Table
-- =====================================================
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `username` VARCHAR(50) UNIQUE NOT NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `name` VARCHAR(100),
    `role` ENUM('super_admin', 'admin', 'staff') DEFAULT 'admin',
    `last_login` TIMESTAMP NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Categories Table
-- =====================================================
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `image` VARCHAR(255),
    `icon` VARCHAR(50),
    `sort_order` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample categories
INSERT INTO `categories` (`name`, `description`, `icon`, `sort_order`) VALUES
('Appetizers', 'Start your meal right', 'utensils', 1),
('Main Dishes', 'Signature entrees', 'fire', 2),
('Noodles & Rice', 'Asian favorites', 'bowl-food', 3),
('Drinks', 'Refreshing beverages', 'glass-water', 4),
('Desserts', 'Sweet endings', 'ice-cream', 5);

-- =====================================================
-- Menu Items Table
-- =====================================================
CREATE TABLE IF NOT EXISTS `menu_items` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `category_id` INT NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(10,2) NOT NULL,
    `compare_price` DECIMAL(10,2) NULL COMMENT 'Original price for sale items',
    `image` VARCHAR(255),
    `is_featured` TINYINT(1) DEFAULT 0,
    `is_popular` TINYINT(1) DEFAULT 0,
    `is_spicy` TINYINT(1) DEFAULT 0,
    `is_vegetarian` TINYINT(1) DEFAULT 0,
    `is_gluten_free` TINYINT(1) DEFAULT 0,
    `calories` INT NULL,
    `prep_time` INT DEFAULT 15 COMMENT 'Prep time in minutes',
    `stock_status` ENUM('in_stock', 'low_stock', 'out_of_stock') DEFAULT 'in_stock',
    `sort_order` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Menu Item Schedules - Time-based availability
-- =====================================================
CREATE TABLE IF NOT EXISTS `menu_schedules` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `menu_item_id` INT NOT NULL,
    `schedule_type` ENUM('always', 'specific_hours', 'specific_days') DEFAULT 'always',
    `day_of_week` TINYINT NULL COMMENT '0=Sunday, 6=Saturday, NULL=all days',
    `start_time` TIME NULL,
    `end_time` TIME NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Menu Item Options (Add-ons, Sizes, etc.)
-- =====================================================
CREATE TABLE IF NOT EXISTS `menu_options` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `menu_item_id` INT NOT NULL,
    `option_group` VARCHAR(50) NOT NULL COMMENT 'e.g., Size, Spice Level, Add-ons',
    `option_name` VARCHAR(100) NOT NULL,
    `price_modifier` DECIMAL(10,2) DEFAULT 0.00,
    `is_default` TINYINT(1) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Orders Table
-- =====================================================
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `order_number` VARCHAR(20) UNIQUE NOT NULL,
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_email` VARCHAR(100),
    `customer_phone` VARCHAR(20) NOT NULL,
    `order_type` ENUM('delivery', 'pickup') NOT NULL,
    `delivery_address` TEXT,
    `delivery_instructions` TEXT,
    `scheduled_time` DATETIME NULL COMMENT 'NULL = ASAP',
    `subtotal` DECIMAL(10,2) NOT NULL,
    `tax_amount` DECIMAL(10,2) NOT NULL,
    `delivery_fee` DECIMAL(10,2) DEFAULT 0.00,
    `tip_amount` DECIMAL(10,2) DEFAULT 0.00,
    `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
    `total` DECIMAL(10,2) NOT NULL,
    `payment_method` ENUM('stripe', 'paypal', 'venmo', 'cashapp', 'cash') NOT NULL,
    `payment_status` ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    `payment_id` VARCHAR(100) NULL COMMENT 'Transaction ID from payment gateway',
    `order_status` ENUM('pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'pending',
    `special_instructions` TEXT,
    `estimated_ready_time` DATETIME NULL,
    `actual_ready_time` DATETIME NULL,
    `delivered_at` DATETIME NULL,
    `cancelled_at` DATETIME NULL,
    `cancel_reason` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Order Items Table
-- =====================================================
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `order_id` INT NOT NULL,
    `menu_item_id` INT NOT NULL,
    `item_name` VARCHAR(150) NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(10,2) NOT NULL,
    `options_json` JSON NULL COMMENT 'Selected options and modifiers',
    `special_instructions` TEXT,
    `subtotal` DECIMAL(10,2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Landing Page Content - Editable from admin
-- =====================================================
CREATE TABLE IF NOT EXISTS `landing_content` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `section` VARCHAR(50) NOT NULL COMMENT 'hero, about, features, testimonials, cta',
    `content_key` VARCHAR(100) NOT NULL,
    `content_value` TEXT,
    `content_type` ENUM('text', 'textarea', 'image', 'json') DEFAULT 'text',
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `section_key` (`section`, `content_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default landing content
INSERT INTO `landing_content` (`section`, `content_key`, `content_value`, `content_type`) VALUES
-- Hero section
('hero', 'title', 'Authentic Flavors, Delivered Fresh', 'text'),
('hero', 'subtitle', 'Experience restaurant-quality meals from your favorite local spots, delivered right to your door', 'textarea'),
('hero', 'cta_text', 'Order Now', 'text'),
('hero', 'cta_link', '/menu.php', 'text'),
('hero', 'image', '', 'image'),
-- About section
('about', 'title', 'Our Story', 'text'),
('about', 'description', 'We started with a simple mission: bring the best local food directly to your table. Every dish is prepared fresh with quality ingredients and delivered with care.', 'textarea'),
('about', 'image', '', 'image'),
-- Features
('features', 'items', '[{"icon":"clock","title":"Fast Delivery","desc":"Hot food at your door in 30-45 minutes"},{"icon":"leaf","title":"Fresh Ingredients","desc":"Quality ingredients, made fresh daily"},{"icon":"credit-card","title":"Easy Payment","desc":"Pay your way - cards, PayPal, Venmo & more"}]', 'json'),
-- Testimonials
('testimonials', 'items', '[{"name":"Sarah M.","text":"Best pho in town! Fast delivery and always hot.","rating":5,"image":""},{"name":"James K.","text":"Love the banh mi. My go-to lunch spot!","rating":5,"image":""},{"name":"Lisa T.","text":"Great portions and authentic flavors. Highly recommend!","rating":5,"image":""}]', 'json'),
-- CTA section
('cta', 'title', 'Hungry? Order Now!', 'text'),
('cta', 'subtitle', 'Free delivery on orders over $35', 'text'),
('cta', 'button_text', 'View Menu', 'text'),
('cta', 'button_link', '/menu.php', 'text');

-- =====================================================
-- Coupons/Promo Codes
-- =====================================================
CREATE TABLE IF NOT EXISTS `coupons` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `code` VARCHAR(50) UNIQUE NOT NULL,
    `description` VARCHAR(255),
    `discount_type` ENUM('percentage', 'fixed') NOT NULL,
    `discount_value` DECIMAL(10,2) NOT NULL,
    `min_order_amount` DECIMAL(10,2) DEFAULT 0.00,
    `max_discount` DECIMAL(10,2) NULL COMMENT 'Max discount for percentage type',
    `usage_limit` INT NULL,
    `used_count` INT DEFAULT 0,
    `valid_from` DATETIME NOT NULL,
    `valid_until` DATETIME NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Indexes for performance
-- =====================================================
CREATE INDEX idx_orders_status ON orders(order_status);
CREATE INDEX idx_orders_date ON orders(created_at);
CREATE INDEX idx_orders_customer ON orders(customer_phone);
CREATE INDEX idx_menu_category ON menu_items(category_id);
CREATE INDEX idx_menu_active ON menu_items(is_active);
CREATE INDEX idx_schedule_item ON menu_schedules(menu_item_id);
