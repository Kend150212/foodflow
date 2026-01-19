# FoodFlow - Food Ordering Webapp

A full-featured, mobile-first food ordering web application built with PHP/MySQL for the US market.

![FoodFlow](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat&logo=php) ![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat&logo=mysql&logoColor=white) ![TailwindCSS](https://img.shields.io/badge/Tailwind-CSS-38B2AC?style=flat&logo=tailwind-css)

## ✨ Features

- 📱 **Mobile-First Design** - Responsive, touch-friendly interface
- 💳 **Multi-Payment Support** - Stripe, PayPal, Venmo, Cash App
- ⏰ **Time-Based Menu** - Schedule items by day/time
- 🛒 **Smart Cart** - Persistent cart with localStorage
- 📦 **Order Tracking** - Real-time order status updates
- 🎨 **CMS Admin Panel** - Full content management

## 🚀 Quick Start

### Prerequisites
- PHP 8.0+
- MySQL 5.7+
- Web server (Apache/Nginx) or PHP built-in server

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/YOUR_USERNAME/foodflow.git
   cd foodflow
   ```

2. **For VPS deployment, run the install script:**
   ```bash
   chmod +x install.sh
   ./install.sh
   ```

3. **Open the web installer:**
   ```
   http://yourdomain.com/install.php
   ```

4. **For local development:**
   ```bash
   php -S localhost:8000
   # Open http://localhost:8000/install.php
   ```

## 📁 Project Structure

```
foodflow/
├── index.php            # Landing page
├── menu.php             # Menu browsing
├── checkout.php         # Multi-payment checkout
├── track-order.php      # Order tracking
├── admin/               # Admin panel (8 files)
├── api/                 # REST API endpoints
├── includes/            # Core PHP classes
├── assets/              # CSS, JS, images
├── database.sql         # Database schema
└── install.php          # Web installer
```

## 💳 Payment Configuration

Configure payments in **Admin → Settings**:

| Provider | Integration |
|----------|------------|
| Stripe | Direct API (Cards) |
| PayPal | PayPal SDK |
| Venmo | Via PayPal |
| Cash App | Via Square |

## 🎨 Design System

- **Primary:** #DC2626 (Red)
- **CTA:** #CA8A04 (Gold)
- **Fonts:** Playfair Display SC + Karla

## 📝 Admin Panel

Access at `/admin/login.php`:
- Dashboard with real-time stats
- Order management with status updates
- Menu item CRUD with images
- Category management
- Landing page CMS
- Payment & store settings

## 🔒 Security

- PDO prepared statements
- Password hashing (bcrypt)
- CSRF protection
- XSS prevention
- Session-based authentication

## 📄 License

MIT License - feel free to use for personal or commercial projects.

---

Made with ❤️ using PHP, MySQL, and TailwindCSS
