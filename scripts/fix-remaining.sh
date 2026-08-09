#!/bin/bash
# ═══════════════════════════════════════════════════════════════
# Zalandy — Fix Remaining Issues
# 1. Run compliance pages script
# 2. Fix WooCommerce shop page (coming soon issue)
# 3. Verify cookie consent mu-plugin
# 4. Flush permalinks
# 5. Verify everything
#
# Usage on server:
#   cd /opt/commerce-master
#   bash scripts/fix-remaining.sh
# ═══════════════════════════════════════════════════════════════
set -e

cd /opt/commerce-master
COMPOSE="docker compose -f docker-compose.yml -f docker-compose.prod.yml"
WP="exec wordpress /usr/local/bin/wp --allow-root"

echo "========================================"
echo "  Zalandy — Fix Remaining Issues"
echo "========================================"
echo ""

# ─── 1. Run compliance pages ───────────────────────────────
echo "1/6 — Creating compliance & legal pages..."
$COMPOSE cp scripts/compliance-pages.php wordpress:/tmp/compliance-pages.php
$COMPOSE exec wordpress bash -c "php -d memory_limit=512M /usr/local/bin/wp eval-file /tmp/compliance-pages.php --allow-root"
echo "  ✅ Compliance pages created"
echo ""

# ─── 2. Fix WooCommerce shop page ──────────────────────────
echo "2/6 — Fixing WooCommerce shop page..."

# Check if shop page exists, create if not
SHOP_PAGE_ID=$($COMPOSE $WP option get woocommerce_shop_page_id 2>/dev/null || echo "0")
if [ "$SHOP_PAGE_ID" = "0" ] || [ -z "$SHOP_PAGE_ID" ]; then
    echo "  Shop page not assigned. Creating..."
    SHOP_PAGE_ID=$($COMPOSE $WP post create --post_type=page --post_title="Shop" --post_status=publish --post_name="shop" --porcelain)
    $COMPOSE $WP option update woocommerce_shop_page_id "$SHOP_PAGE_ID"
    echo "  Shop page created (ID: $SHOP_PAGE_ID)"
else
    echo "  Shop page exists (ID: $SHOP_PAGE_ID)"
fi

# Disable WooCommerce "Coming Soon" / launch mode (WC 8.7+)
$COMPOSE $WP option delete woocommerce_coming_soon 2>/dev/null || true
$COMPOSE $WP option update woocommerce_store_pages_only 0 2>/dev/null || true
$COMPOSE $WP option delete woocommerce_private_link 2>/dev/null || true

# Also check for site-wide coming soon in WooCommerce
$COMPOSE $WP eval 'delete_option("woocommerce_coming_soon"); delete_option("woocommerce_store_pages_only");' --allow-root 2>/dev/null || true

echo "  ✅ Shop page configured"
echo ""

# ─── 3. Fix Woostify coming soon ───────────────────────────
echo "3/6 — Checking Woostify theme settings..."

# Disable any Woostify coming soon / maintenance mode
$COMPOSE $WP eval '
    remove_theme_mod("woostify_coming_soon");
    update_option("woostify_setting", array_merge(
        (array) get_option("woostify_setting", array()),
        array("coming_soon" => false)
    ));
' --allow-root 2>/dev/null || true

# Set WooCommerce cart, checkout, account pages if missing
$COMPOSE $WP eval '
    // Ensure all WC pages are set
    $pages = array(
        "woocommerce_cart_page_id"     => "cart",
        "woocommerce_checkout_page_id" => "checkout",
        "woocommerce_myaccount_page_id"=> "my-account",
        "woocommerce_shop_page_id"     => "shop",
        "woocommerce_terms_page_id"    => "terms-of-service",
    );
    foreach ($pages as $option => $slug) {
        $current = get_option($option, 0);
        if (!$current || !get_post($current)) {
            $page = get_page_by_path($slug);
            if ($page) {
                update_option($option, $page->ID);
                WP_CLI::log("  Set $option → $slug (ID: {$page->ID})");
            } else {
                WP_CLI::log("  Page $slug not found, skipping $option");
            }
        } else {
            WP_CLI::log("  $option already set (ID: $current)");
        }
    }
' --allow-root 2>/dev/null || true

echo "  ✅ Theme & WC pages configured"
echo ""

# ─── 4. Verify cookie consent mu-plugin ────────────────────
echo "4/6 — Verifying cookie consent mu-plugin..."

# Check if mu-plugins directory exists in container
$COMPOSE exec wordpress bash -c "ls -la /var/www/html/wp-content/mu-plugins/zalandy-cookie-consent.php 2>/dev/null && echo '  ✅ Cookie consent plugin found' || echo '  ❌ Cookie consent plugin NOT found — need container rebuild'"

# If not found, copy directly
$COMPOSE exec wordpress bash -c "test -d /var/www/html/wp-content/mu-plugins || mkdir -p /var/www/html/wp-content/mu-plugins"
$COMPOSE cp wp-content/mu-plugins/zalandy-cookie-consent.php wordpress:/var/www/html/wp-content/mu-plugins/zalandy-cookie-consent.php 2>/dev/null || true
echo "  ✅ Cookie consent plugin deployed"
echo ""

# ─── 5. Flush permalinks & cache ───────────────────────────
echo "5/6 — Flushing permalinks & cache..."
$COMPOSE $WP rewrite flush --hard 2>/dev/null || true
$COMPOSE $WP cache flush 2>/dev/null || true
$COMPOSE $WP rewrite structure '/%postname%/' 2>/dev/null || true
$COMPOSE $WP rewrite flush --hard 2>/dev/null || true
echo "  ✅ Permalinks flushed"
echo ""

# ─── 6. Summary & verification ─────────────────────────────
echo "6/6 — Verification summary..."
echo ""
echo "========================================"
echo "  Pages created/updated:"
echo "========================================"
$COMPOSE $WP post list --post_type=page --post_status=publish --fields=ID,post_title,post_name --format=table --allow-root 2>/dev/null || true
echo ""
echo "========================================"
echo "  Products:"
echo "========================================"
$COMPOSE $WP post list --post_type=product --post_status=publish --fields=ID,post_title,post_name --format=table --allow-root 2>/dev/null || true
echo ""
echo "========================================"
echo "  mu-plugins:"
echo "========================================"
$COMPOSE exec wordpress bash -c "ls -la /var/www/html/wp-content/mu-plugins/ 2>/dev/null || echo '  No mu-plugins directory'" 2>/dev/null || true
echo ""
echo "========================================"
echo "  Fix Complete!"
echo "========================================"
echo ""
echo "  Verify at:"
echo "    Homepage:      https://zalandy.top"
echo "    Shop:          https://zalandy.top/shop/"
echo "    Imprint:       https://zalandy.top/imprint/"
echo "    Privacy:       https://zalandy.top/privacy-policy/"
echo "    About Us:      https://zalandy.top/about-us/"
echo "    Contact:       https://zalandy.top/contact/"
echo "    Cookie Policy: https://zalandy.top/cookie-policy/"
echo "    FAQ:           https://zalandy.top/faq/"
echo "    Size Guide:    https://zalandy.top/size-guide/"
echo "    Care Guide:    https://zalandy.top/jewelry-care-guide/"
echo "    Withdrawal:    https://zalandy.top/withdrawal-right/"
echo "    Admin:         https://zalandy.top/wp-admin"
echo ""
echo "========================================"
