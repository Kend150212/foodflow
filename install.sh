#!/bin/bash
# =====================================================
# FoodFlow - Installation Script
# Run: chmod +x install.sh && ./install.sh
# =====================================================

echo "=========================================="
echo "   FoodFlow - Food Ordering System"
echo "   Installation Script"
echo "=========================================="
echo ""

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;" 2>/dev/null)
if [ -z "$PHP_VERSION" ]; then
    echo "❌ PHP is not installed!"
    echo "Please install PHP 8.0 or higher"
    exit 1
fi
echo "✅ PHP Version: $PHP_VERSION"

# Check if MySQL/MariaDB is available
if command -v mysql &> /dev/null; then
    echo "✅ MySQL/MariaDB is available"
else
    echo "⚠️  MySQL client not found (optional for CLI check)"
fi

# Create necessary directories
echo ""
echo "📁 Creating directories..."
mkdir -p assets/uploads/menu
mkdir -p assets/uploads/content
chmod -R 755 assets/uploads

# Set file permissions
echo "🔐 Setting permissions..."
chmod 644 *.php 2>/dev/null
chmod 644 admin/*.php 2>/dev/null
chmod 644 api/*.php 2>/dev/null
chmod 644 includes/*.php 2>/dev/null
chmod 755 install.sh

# Create .htaccess for security
echo "🔒 Creating .htaccess..."
cat > .htaccess << 'EOF'
# FoodFlow - Apache Configuration

# Enable URL rewriting
RewriteEngine On

# Prevent directory listing
Options -Indexes

# Protect sensitive files
<FilesMatch "^(config\.php|\.env|\.git|composer\.json|composer\.lock)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Protect includes directory
<FilesMatch "^includes/">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Cache static assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/webp "access plus 1 month"
    ExpiresByType text/css "access plus 1 week"
    ExpiresByType application/javascript "access plus 1 week"
</IfModule>

# Enable GZIP compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/css application/json application/javascript
</IfModule>
EOF

# Create config from sample if not exists
if [ ! -f "includes/config.php" ]; then
    echo "📝 Creating config.php from sample..."
    cp includes/config.sample.php includes/config.php
    echo "⚠️  Please edit includes/config.php with your database credentials"
fi

echo ""
echo "=========================================="
echo "✅ Installation preparation complete!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Edit includes/config.php with your database credentials"
echo "2. Open your browser and go to: http://your-domain/install.php"
echo "3. Follow the web installer to complete setup"
echo ""
echo "Or import database manually:"
echo "   mysql -u username -p database_name < database.sql"
echo ""
