-- FoodFlow Add-ons Feature Migration
-- Run this script on the production database

-- Add-on Categories Table
CREATE TABLE IF NOT EXISTS addon_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50) DEFAULT '🔹',
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add-ons Table
CREATE TABLE IF NOT EXISTS addons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) DEFAULT 0.00,
    unit VARCHAR(20) DEFAULT 'Pcs',
    unit_value DECIMAL(10,2) DEFAULT 1.00,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES addon_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Menu Item Add-ons Mapping (which add-ons apply to which menu items)
CREATE TABLE IF NOT EXISTS menu_item_addons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_item_id INT NOT NULL,
    addon_id INT NOT NULL,
    is_default TINYINT(1) DEFAULT 0,
    max_quantity INT DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE,
    FOREIGN KEY (addon_id) REFERENCES addons(id) ON DELETE CASCADE,
    UNIQUE KEY unique_item_addon (menu_item_id, addon_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order Item Add-ons (snapshot of add-ons for each order item)
CREATE TABLE IF NOT EXISTS order_item_addons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_item_id INT NOT NULL,
    addon_id INT NOT NULL,
    addon_name VARCHAR(100) NOT NULL,
    quantity INT DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert some default add-on categories
INSERT INTO addon_categories (name, description, icon, sort_order) VALUES 
('Toppings', 'Extra toppings for your dish', '🧀', 1),
('Sauces', 'Delicious sauces', '🍯', 2),
('Sides', 'Side dishes and extras', '🍟', 3),
('Proteins', 'Extra protein options', '🍗', 4);

-- Insert sample add-ons
INSERT INTO addons (category_id, name, price, unit, unit_value, sort_order) VALUES
(1, 'Extra Cheese', 1.50, 'Pcs', 1, 1),
(1, 'Bacon Bits', 2.00, 'oz', 2, 2),
(1, 'Jalapenos', 0.75, 'Pcs', 1, 3),
(1, 'Mushrooms', 1.25, 'oz', 2, 4),
(2, 'Ranch Dressing', 0.50, 'oz', 2, 1),
(2, 'BBQ Sauce', 0.50, 'oz', 2, 2),
(2, 'Sriracha Mayo', 0.75, 'oz', 2, 3),
(3, 'French Fries', 3.99, 'Pcs', 1, 1),
(3, 'Onion Rings', 4.49, 'Pcs', 1, 2),
(3, 'Coleslaw', 2.49, 'oz', 4, 3),
(4, 'Grilled Chicken', 3.99, 'oz', 4, 1),
(4, 'Shrimp', 4.99, 'Pcs', 5, 2),
(4, 'Tofu', 2.49, 'oz', 4, 3);
