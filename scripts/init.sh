#!/usr/bin/env bash
# ════════════════════════════════════════════════
# Commerce Master — WordPress Initialization Script
# Runs via WP-CLI inside Docker container.
# Convergently idempotent: safe to run multiple times;
# each run corrects toward the target state.
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
ADMIN_PASSWORD="${ADMIN_PASSWORD:-}"
ADMIN_EMAIL="${ADMIN_EMAIL:-admin@example.com}"

# ── Validate admin password (CRITICAL — must not be weak or placeholder) ──
# List of forbidden placeholder / weak passwords
FORBIDDEN_PASSWORDS="admin password change_me change_me_to_a_strong_password 123456 abc123 letmein welcome"

check_password() {
    local pw="$1"
    if [ -z "$pw" ]; then
        echo "❌ FATAL: ADMIN_PASSWORD is not set."
        echo "   Set ADMIN_PASSWORD in your .env file to a strong password (min 12 chars)."
        exit 1
    fi
    for forbidden in $FORBIDDEN_PASSWORDS; do
        if [ "$pw" = "$forbidden" ]; then
            echo "❌ FATAL: ADMIN_PASSWORD is set to a known weak/placeholder value ('$forbidden')."
            echo "   Change it in your .env file to a strong password (min 12 chars)."
            exit 1
        fi
    done
    if [ "${#pw}" -lt 12 ]; then
        echo "❌ FATAL: ADMIN_PASSWORD is too short (${#pw} chars). Minimum 12 characters required."
        echo "   Change it in your .env file."
        exit 1
    fi
}

check_password "$ADMIN_PASSWORD"
echo "✅ Admin password validated."

# ── Validate DB password (CRITICAL — must not be placeholder) ──
DB_PASSWORD="${DB_PASSWORD:-}"
if [ -z "$DB_PASSWORD" ] || [ "$DB_PASSWORD" = "change_me" ] || [ "$DB_PASSWORD" = "change_me_in_production" ]; then
    echo "❌ FATAL: DB_PASSWORD is not set or is still at the placeholder value."
    echo "   Set DB_PASSWORD in your .env file."
    exit 1
fi

# ── Validate WordPress salts (CRITICAL — must not be placeholder) ──
PLACEHOLDER_SALTS=(
    AUTH_KEY SECURE_AUTH_KEY LOGGED_IN_KEY NONCE_KEY
    AUTH_SALT SECURE_AUTH_SALT LOGGED_IN_SALT NONCE_SALT
)
for salt_var in "${PLACEHOLDER_SALTS[@]}"; do
    salt_val="${!salt_var:-}"
    if [ -z "$salt_val" ] || echo "$salt_val" | grep -qiE "^(generate_me|change_me|put_your_unique)" ; then
        echo "❌ FATAL: $salt_var is not set or is still at the placeholder value."
        echo "   Generate real salts at https://api.wordpress.org/secret-key/1.1/salt/"
        echo "   and set them in your .env file."
        exit 1
    fi
done
echo "✅ Database password and WordPress salts validated."

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
        --skip-email; then
        echo "✅ WordPress installed."
        break
    fi
    echo "   Attempt $i/30 — waiting..."
    sleep 5
done

if ! wp core is-installed 2>/dev/null; then
    echo "❌ FATAL: WordPress is not installed after 30 attempts."
    echo "   Check that the database is running and credentials are correct."
    exit 1
fi

# ── Install & activate WooCommerce ──
echo "📦 Checking WooCommerce..."
if ! wp plugin is-installed woocommerce 2>/dev/null; then
    echo "   Installing WooCommerce 11.0.0..."
    wp plugin install woocommerce --version=11.0.0 --activate
    echo "   ✅ WooCommerce 11.0.0 installed and activated."
else
    INSTALLED_VERSION=$(wp plugin get woocommerce --field=version 2>/dev/null || echo "unknown")
    echo "   WooCommerce already installed (v${INSTALLED_VERSION})."
    if ! wp plugin is-active woocommerce 2>/dev/null; then
        wp plugin activate woocommerce
        echo "   ✅ WooCommerce activated."
    fi
fi

# ── Activate self-built plugins & theme (CRITICAL — must succeed) ──
echo "🔌 Activating commerce-core plugin..."
wp plugin activate commerce-core
echo "   ✅ commerce-core activated."

echo "🎨 Activating commerce-block-theme..."
wp theme activate commerce-block-theme
echo "   ✅ commerce-block-theme activated."

# ── Set permalink structure ──
echo "🔗 Setting permalink structure..."
wp rewrite structure '/%postname%/' --hard
wp rewrite flush
echo "   ✅ Permalinks set."

# ── Create essential pages ──
echo "📄 Ensuring essential pages exist..."

# Let WooCommerce create its default pages (Cart, Checkout, Shop, My Account, Terms)
# This uses WC's official page creation mechanism with proper block content.
echo "   Running WooCommerce page creation..."
wp eval 'WC_Install::create_pages();' 2>/dev/null || echo "   (WC page creation skipped — may already exist)"

# ── Cart page: verify correct block content ──
# Official WC 11 Cart page content (self-closing block with lock attributes)
CART_BLOCK_CONTENT='<!-- wp:woocommerce/cart {"lock":{"remove":true,"move":false}} /-->'
CART_PAGE_ID=$(wp option get woocommerce_cart_page_id 2>/dev/null || echo "0")

if [ "$CART_PAGE_ID" = "0" ] || ! wp post exists "$CART_PAGE_ID" 2>/dev/null; then
    CART_PAGE_ID=$(wp post create \
        --post_type=page \
        --post_title="Cart" \
        --post_status=publish \
        --post_content="$CART_BLOCK_CONTENT" \
        --porcelain)
    wp option update woocommerce_cart_page_id "$CART_PAGE_ID"
    echo "   ✅ Cart page created with Cart Block (ID: $CART_PAGE_ID)"
else
    EXISTING_CONTENT=$(wp post get "$CART_PAGE_ID" --field=post_content 2>/dev/null || echo "")
    if echo "$EXISTING_CONTENT" | grep -q 'wp:woocommerce/cart'; then
        echo "   ✅ Cart page already uses Cart Block (ID: $CART_PAGE_ID)"
    else
        # Fix: empty content, shortcode, or unrecognized content
        wp post update "$CART_PAGE_ID" --post_content="$CART_BLOCK_CONTENT"
        echo "   ✅ Cart page fixed: content → Cart Block (ID: $CART_PAGE_ID)"
    fi
fi

# ── Checkout page: verify correct block content ──
# Official WC 11 Checkout page content (self-closing block with lock attributes)
CHECKOUT_BLOCK_CONTENT='<!-- wp:woocommerce/checkout {"lock":{"remove":true,"move":false}} /-->'
CHECKOUT_PAGE_ID=$(wp option get woocommerce_checkout_page_id 2>/dev/null || echo "0")

if [ "$CHECKOUT_PAGE_ID" = "0" ] || ! wp post exists "$CHECKOUT_PAGE_ID" 2>/dev/null; then
    CHECKOUT_PAGE_ID=$(wp post create \
        --post_type=page \
        --post_title="Checkout" \
        --post_status=publish \
        --post_content="$CHECKOUT_BLOCK_CONTENT" \
        --porcelain)
    wp option update woocommerce_checkout_page_id "$CHECKOUT_PAGE_ID"
    echo "   ✅ Checkout page created with Checkout Block (ID: $CHECKOUT_PAGE_ID)"
else
    EXISTING_CONTENT=$(wp post get "$CHECKOUT_PAGE_ID" --field=post_content 2>/dev/null || echo "")
    if echo "$EXISTING_CONTENT" | grep -q 'wp:woocommerce/checkout'; then
        echo "   ✅ Checkout page already uses Checkout Block (ID: $CHECKOUT_PAGE_ID)"
    else
        wp post update "$CHECKOUT_PAGE_ID" --post_content="$CHECKOUT_BLOCK_CONTENT"
        echo "   ✅ Checkout page fixed: content → Checkout Block (ID: $CHECKOUT_PAGE_ID)"
    fi
fi

# My Account page — still uses shortcode (no block available in WC 11)
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

# Wishlist page (idempotent)
WISHLIST_PAGE_SLUG="wishlist"
WISHLIST_PAGE_ID=$(wp post list --post_type=page --field=ID --name="$WISHLIST_PAGE_SLUG" 2>/dev/null | head -1)
if [ -z "$WISHLIST_PAGE_ID" ]; then
    WISHLIST_PAGE_ID=$(wp post create \
        --post_type=page \
        --post_title="Wishlist" \
        --post_status=publish \
        --post_name="$WISHLIST_PAGE_SLUG" \
        --post_content='[commerce_wishlist]' \
        --porcelain)
    echo "   ✅ Wishlist page created (ID: $WISHLIST_PAGE_ID, slug: $WISHLIST_PAGE_SLUG)"
else
    echo "   ✅ Wishlist page exists (ID: $WISHLIST_PAGE_ID)"
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

# Terms & Conditions page (idempotent)
TERMS_PAGE_ID=$(wp post list --post_type=page --field=ID --title="Terms and Conditions" 2>/dev/null | head -1)
if [ -z "$TERMS_PAGE_ID" ]; then
    wp post create \
        --post_type=page \
        --post_title="Terms and Conditions" \
        --post_status=publish \
        --post_content='<!-- wp:paragraph --><p>Terms and conditions content coming soon.</p><!-- /wp:paragraph -->' \
        --porcelain > /dev/null
    echo "   ✅ Terms page created"
else
    echo "   ✅ Terms page exists"
fi

# Privacy Policy page (idempotent)
PRIVACY_PAGE_ID=$(wp post list --post_type=page --field=ID --title="Privacy Policy" 2>/dev/null | head -1)
if [ -z "$PRIVACY_PAGE_ID" ]; then
    wp post create \
        --post_type=page \
        --post_title="Privacy Policy" \
        --post_status=publish \
        --post_content='<!-- wp:paragraph --><p>Privacy policy content coming soon.</p><!-- /wp:paragraph -->' \
        --porcelain > /dev/null
    echo "   ✅ Privacy Policy page created"
else
    echo "   ✅ Privacy Policy page exists"
fi

# ── WooCommerce settings ──
echo "⚙️  Configuring WooCommerce..."
wp option update woocommerce_default_country "US"
wp option update woocommerce_currency "${BASE_CURRENCY:-USD}"
wp option update woocommerce_enable_ajax_add_to_cart "yes"
wp option update woocommerce_cart_redirect_after_add "no"
wp option update woocommerce_enable_checkout_login_reminder "yes"
wp option update woocommerce_enable_guest_checkout "yes"
echo "   ✅ WooCommerce settings configured."

# ── Create and set Home page as front page ──
echo "🏠 Creating Home page..."
HOME_PAGE_ID=$(wp post list --post_type=page --field=ID --title="Home" 2>/dev/null | head -1)
if [ -z "$HOME_PAGE_ID" ]; then
    HOME_PAGE_ID=$(wp post create \
        --post_type=page \
        --post_title="Home" \
        --post_status=publish \
        --post_content='<!-- wp:paragraph --><p>Welcome to our store. Browse our latest collection.</p><!-- /wp:paragraph -->' \
        --porcelain)
    echo "   ✅ Home page created (ID: $HOME_PAGE_ID)"
else
    echo "   ✅ Home page exists (ID: $HOME_PAGE_ID)"
fi
wp option update show_on_front "page"
wp option update page_on_front "$HOME_PAGE_ID"
echo "   ✅ Front page set to Home"

# ── Run demo data import (idempotent, must succeed) ──
if [ -f /scripts/demo-data.php ]; then
    echo "👗 Importing demo product data..."
    wp eval-file /scripts/demo-data.php
    echo "   ✅ Demo data import complete."
else
    echo "   ⚠️  /scripts/demo-data.php not found — skipping demo data."
fi

# ── Run smoke checks ──
if [ -f /scripts/smoke-check.sh ]; then
    echo "🔍 Running smoke checks..."
    bash /scripts/smoke-check.sh
fi

# ── Non-critical cleanup (tolerant of failures) ──
echo "🧹 Cleaning up default content..."
wp post delete 1 2>/dev/null || true  # "Hello World" post
wp post delete 2 2>/dev/null || true  # "Sample Page"
wp comment delete 1 2>/dev/null || true  # Default comment

echo ""
echo "╔══════════════════════════════════════════╗"
echo "║  ✅ Initialization complete!              ║"
echo "╠══════════════════════════════════════════╣"
echo "║  Site:  $SITE_URL"
echo "║  Admin: $SITE_URL/wp-admin"
echo "║  User:  $ADMIN_USER"
echo "╚══════════════════════════════════════════╝"
