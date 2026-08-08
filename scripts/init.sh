#!/usr/bin/env bash
# ════════════════════════════════════════════════
# Commerce Master — WordPress Initialization Script
# Runs via WP-CLI inside Docker container.
# Idempotent: safe to run multiple times.
#
# Usage:
#   docker compose --profile cli run --rm wpcli bash /scripts/init.sh
# ════════════════════════════════════════════════
set -euo pipefail

echo "╔══════════════════════════════════════════╗"
echo "║  Commerce Master — Site Initialization     ║"
echo "╚══════════════════════════════════════════╝"

# ── Configuration ──
SITE_TITLE="${BRAND_NAME:-Commerce Master}"
SITE_URL="${WP_HOME:-http://localhost:8080}"
ADMIN_USER="${ADMIN_USER:-admin}"
ADMIN_PASSWORD="${ADMIN_PASSWORD:-admin}"
ADMIN_EMAIL="${SUPPORT_EMAIL:-admin@example.com}"

# ── Wait for WordPress to be ready ──
echo "⏳ Waiting for WordPress to be ready..."
for i in $(seq 1 30); do
    if wp core is-installed 2>/dev/null; then
        echo "✅ WordPress is already installed."
        break
    fi
    if wp core install \
        --url="$SITE_URL" \
        --title="$SITE_TITLE" \
        --admin_user="$ADMIN_USER" \
        --admin_password="$ADMIN_PASSWORD" \
        --admin_email="$ADMIN_EMAIL" \
        --skip-email \
        2>/dev/null; then
        echo "✅ WordPress installed."
        break
    fi
    echo "   Attempt $i/30 — waiting..."
    sleep 5
done

if ! wp core is-installed 2>/dev/null; then
    echo "❌ WordPress is not installed after 30 attempts."
    exit 1
fi

# ── Install & activate WooCommerce ──
echo "📦 Checking WooCommerce..."
if ! wp plugin is-installed woocommerce 2>/dev/null; then
    echo "   Installing WooCommerce..."
    wp plugin install woocommerce --version=11.0.0 --activate
else
    echo "   WooCommerce already installed."
    wp plugin activate woocommerce 2>/dev/null || true
fi

# ── Activate self-built plugins & theme ──
echo "🔌 Activating commerce-core plugin..."
wp plugin activate commerce-core 2>/dev/null || true

echo "🎨 Activating commerce-block-theme..."
wp theme activate commerce-block-theme 2>/dev/null || true

# ── Set permalink structure ──
echo "🔗 Setting permalink structure..."
wp rewrite structure '/%postname%/' --hard 2>/dev/null || true
wp rewrite flush 2>/dev/null || true

# ── Create essential pages (idempotent) ──
echo "📄 Ensuring essential pages exist..."

# Cart page
CART_PAGE_ID=$(wp option get woocommerce_cart_page_id 2>/dev/null || echo "0")
if [ "$CART_PAGE_ID" = "0" ] || ! wp post exists "$CART_PAGE_ID" 2>/dev/null; then
    CART_PAGE_ID=$(wp post create \
        --post_type=page \
        --post_title="Cart" \
        --post_status=publish \
        --post_content='[woocommerce_cart]' \
        --porcelain)
    wp option update woocommerce_cart_page_id "$CART_PAGE_ID"
    echo "   ✅ Cart page created (ID: $CART_PAGE_ID)"
else
    echo "   ✅ Cart page exists (ID: $CART_PAGE_ID)"
fi

# Checkout page
CHECKOUT_PAGE_ID=$(wp option get woocommerce_checkout_page_id 2>/dev/null || echo "0")
if [ "$CHECKOUT_PAGE_ID" = "0" ] || ! wp post exists "$CHECKOUT_PAGE_ID" 2>/dev/null; then
    CHECKOUT_PAGE_ID=$(wp post create \
        --post_type=page \
        --post_title="Checkout" \
        --post_status=publish \
        --post_content='[woocommerce_checkout]' \
        --porcelain)
    wp option update woocommerce_checkout_page_id "$CHECKOUT_PAGE_ID"
    echo "   ✅ Checkout page created (ID: $CHECKOUT_PAGE_ID)"
else
    echo "   ✅ Checkout page exists (ID: $CHECKOUT_PAGE_ID)"
fi

# My Account page
ACCOUNT_PAGE_ID=$(wp option get woocommerce_myaccount_page_id 2>/dev/null || echo "0")
if [ "$ACCOUNT_PAGE_ID" = "0" ] || ! wp post exists "$ACCOUNT_PAGE_ID" 2>/dev/null; then
    ACCOUNT_PAGE_ID=$(wp post create \
        --post_type=page \
        --post_title="My Account" \
        --post_status=publish \
        --post_content='[woocommerce_my_account]' \
        --porcelain)
    wp option update woocommerce_myaccount_page_id "$ACCOUNT_PAGE_ID"
    echo "   ✅ My Account page created (ID: $ACCOUNT_PAGE_ID)"
else
    echo "   ✅ My Account page exists (ID: $ACCOUNT_PAGE_ID)"
fi

# Shop page
SHOP_PAGE_ID=$(wp option get woocommerce_shop_page_id 2>/dev/null || echo "0")
if [ "$SHOP_PAGE_ID" = "0" ] || ! wp post exists "$SHOP_PAGE_ID" 2>/dev/null; then
    SHOP_PAGE_ID=$(wp post create \
        --post_type=page \
        --post_title="Shop" \
        --post_status=publish \
        --post_content='' \
        --porcelain)
    wp option update woocommerce_shop_page_id "$SHOP_PAGE_ID"
    echo "   ✅ Shop page created (ID: $SHOP_PAGE_ID)"
else
    echo "   ✅ Shop page exists (ID: $SHOP_PAGE_ID)"
fi

# Terms & Conditions page
if ! wp post exists --post_title="Terms and Conditions" 2>/dev/null; then
    wp post create \
        --post_type=page \
        --post_title="Terms and Conditions" \
        --post_status=publish \
        --post_content='[Coming soon]' \
        --porcelain > /dev/null
    echo "   ✅ Terms page created"
else
    echo "   ✅ Terms page exists"
fi

# Privacy Policy page
if ! wp post exists --post_title="Privacy Policy" 2>/dev/null; then
    wp post create \
        --post_type=page \
        --post_title="Privacy Policy" \
        --post_status=publish \
        --post_content='[Coming soon]' \
        --porcelain > /dev/null
    echo "   ✅ Privacy Policy page created"
else
    echo "   ✅ Privacy Policy page exists"
fi

# ── WooCommerce settings ──
echo "⚙️  Configuring WooCommerce..."
wp option update woocommerce_default_country "US" 2>/dev/null || true
wp option update woocommerce_currency "USD" 2>/dev/null || true
wp option update woocommerce_enable_ajax_add_to_cart "yes" 2>/dev/null || true
wp option update woocommerce_cart_redirect_after_add "no" 2>/dev/null || true
wp option update woocommerce_enable_checkout_login_reminder "yes" 2>/dev/null || true
wp option update woocommerce_enable_guest_checkout "yes" 2>/dev/null || true

# ── Set front page to show the block theme homepage ──
FRONT_PAGE_ID=$(wp post list --post_type=page --field=ID --title="Home" 2>/dev/null | head -1)
if [ -n "$FRONT_PAGE_ID" ]; then
    wp option update show_on_front "page" 2>/dev/null || true
    wp option update page_on_front "$FRONT_PAGE_ID" 2>/dev/null || true
    echo "   ✅ Front page set"
fi

# ── Delete default WordPress boilerplate ──
echo "🧹 Cleaning up default content..."
wp post delete 1 2>/dev/null || true  # "Hello World" post
wp post delete 2 2>/dev/null || true  # "Sample Page"
wp comment delete 1 2>/dev/null || true  # Default comment

# ── Run demo data import (idempotent) ──
if [ -f /scripts/demo-data.php ]; then
    echo "👗 Importing demo product data..."
    wp eval-file /scripts/demo-data.php 2>/dev/null || echo "   ⚠️  Demo data import skipped (may need WooCommerce active first)"
fi

echo ""
echo "╔══════════════════════════════════════════╗"
echo "║  ✅ Initialization complete!              ║"
echo "╠══════════════════════════════════════════╣"
echo "║  Site:  $SITE_URL"
echo "║  Admin: $SITE_URL/wp-admin"
echo "║  User:  $ADMIN_USER"
echo "║  Pass:  $ADMIN_PASSWORD"
echo "╚══════════════════════════════════════════╝"
