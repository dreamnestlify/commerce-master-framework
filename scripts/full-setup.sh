#!/bin/bash
# ═══════════════════════════════════════════════════════════════
# Zalandy Full Site Setup Script
# Run on server: bash scripts/full-setup.sh
# ═══════════════════════════════════════════════════════════════
set -e

cd /opt/commerce-master
COMPOSE="docker compose -f docker-compose.yml -f docker-compose.prod.yml"
WP="exec wordpress /usr/local/bin/wp --allow-root"

echo "========================================"
echo "  Zalandy Full Site Setup"
echo "========================================"
echo ""

# 1. Fix wp-config.php memory limit
echo "1/5 — Fixing PHP memory limit..."
$COMPOSE exec wordpress bash -c "grep -q 'WP_MEMORY_LIMIT' /var/www/html/wp-config.php || sed -i '/\/\* Add any custom values/i define(\"WP_MEMORY_LIMIT\", \"512M\");\ndefine(\"WP_MAX_MEMORY_LIMIT\", \"512M\");' /var/www/html/wp-config.php 2>/dev/null || sed -i '/table_prefix/i define(\"WP_MEMORY_LIMIT\", \"512M\");\ndefine(\"WP_MAX_MEMORY_LIMIT\", \"512M\");' /var/www/html/wp-config.php"
echo "  ✅ Memory limit set to 512M"

# 2. Deactivate PayPal plugin (causes memory issues with WP-CLI)
echo ""
echo "2/5 — Deactivating WooCommerce PayPal Payments..."
$COMPOSE $WP plugin deactivate woocommerce-paypal-payments 2>/dev/null || echo "  (already deactivated or not installed)"

# 3. Run jewelry product setup
echo ""
echo "3/5 — Creating jewelry products..."
$COMPOSE exec wordpress bash -c "php -d memory_limit=512M /usr/local/bin/wp eval-file scripts/jewelry-product-setup.php --allow-root"

# 4. Run site design setup
echo ""
echo "4/5 — Configuring homepage, menu & theme..."
$COMPOSE exec wordpress bash -c "php -d memory_limit=512M /usr/local/bin/wp eval-file scripts/site-design.php --allow-root"

# 5. Reactivate PayPal plugin & flush cache
echo ""
echo "5/5 — Reactivating PayPal plugin & flushing cache..."
$COMPOSE $WP plugin activate woocommerce-paypal-payments 2>/dev/null || echo "  (PayPal plugin not found, skipping)"
$COMPOSE $WP cache flush 2>/dev/null || true

echo ""
echo "========================================"
echo "  ✅ Setup Complete!"
echo "========================================"
echo ""
echo "  Homepage: https://zalandy.top"
echo "  Shop:     https://zalandy.top/shop/"
echo "  Admin:    https://zalandy.top/wp-admin"
echo ""
echo "========================================"
