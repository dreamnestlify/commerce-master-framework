#!/usr/bin/env bash
# ════════════════════════════════════════════════════════════════
# smoke-check.sh
# Post-initialization smoke checks for the Commerce Master framework.
# Verifies that WordPress + WooCommerce + theme + pages + products
# are properly set up after running init.sh.
#
# Must be run inside the wpcli Docker container:
#   docker compose --profile cli run --rm wpcli bash /scripts/smoke-check.sh
#
# Exit codes:
#   0 — All checks passed
#   1 — One or more checks failed
# ════════════════════════════════════════════════════════════════
set -euo pipefail

PASS=0
FAIL=0
WARN=0

ok() {
    echo "  ✅ $1"
    PASS=$((PASS + 1))
}

fail() {
    echo "  ❌ $1"
    FAIL=$((FAIL + 1))
}

warn() {
    echo "  ⚠️  $1"
    WARN=$((WARN + 1))
}

echo "═══════════════════════════════════════════════════════════"
echo "  Commerce Master — Post-Init Smoke Checks"
echo "═══════════════════════════════════════════════════════════"
echo ""

# ── 1. WordPress Core ─────────────────────────────────────────────
echo "── WordPress Core ────────────────────────────────────────"

WP_INSTALLED=$(wp core is-installed 2>/dev/null && echo "yes" || echo "no")
if [ "$WP_INSTALLED" = "yes" ]; then
    ok "WordPress is installed"
else
    fail "WordPress is NOT installed — run scripts/init.sh first"
    exit 1
fi

WP_VERSION=$(wp core version 2>/dev/null || echo "unknown")
ok "WordPress version: $WP_VERSION"

# ── 2. WooCommerce ───────────────────────────────────────────────
echo ""
echo "── WooCommerce ──────────────────────────────────────────"

WC_ACTIVE=$(wp plugin is-active woocommerce 2>/dev/null && echo "yes" || echo "no")
if [ "$WC_ACTIVE" = "yes" ]; then
    ok "WooCommerce plugin is active"
else
    WC_INSTALLED=$(wp plugin is-installed woocommerce 2>/dev/null && echo "yes" || echo "no")
    if [ "$WC_INSTALLED" = "yes" ]; then
        fail "WooCommerce is installed but NOT active"
    else
        fail "WooCommerce is NOT installed"
    fi
fi

WC_VERSION=$(wp plugin get woocommerce --field=version 2>/dev/null || echo "unknown")
ok "WooCommerce version: $WC_VERSION"

# ── 3. commerce-core Plugin ──────────────────────────────────────
echo ""
echo "── commerce-core Plugin ──────────────────────────────────"

CC_ACTIVE=$(wp plugin is-active commerce-core 2>/dev/null && echo "yes" || echo "no")
if [ "$CC_ACTIVE" = "yes" ]; then
    ok "commerce-core plugin is active"
else
    fail "commerce-core plugin is NOT active"
fi

# ── 4. Theme ─────────────────────────────────────────────────────
echo ""
echo "── Theme ────────────────────────────────────────────────"

ACTIVE_THEME=$(wp option get stylesheet 2>/dev/null || echo "")
if [ "$ACTIVE_THEME" = "commerce-block-theme" ]; then
    ok "commerce-block-theme is the active theme"
else
    fail "Active theme is '$ACTIVE_THEME' (expected 'commerce-block-theme')"
fi

# ── 5. Required Pages ─────────────────────────────────────────────
echo ""
echo "── Required Pages ────────────────────────────────────────"

check_page() {
    local slug="$1"
    local label="$2"
    local page_id
    page_id=$(wp option get "woocommerce_$(echo "$slug" | tr '-' '_')_page_id" 2>/dev/null || echo "")

    if [ -z "$page_id" ] || [ "$page_id" = "0" ]; then
        fail "$label page is not configured"
        return
    fi

    local post_status
    post_status=$(wp post get "$page_id" --field=post_status 2>/dev/null || echo "not_found")
    if [ "$post_status" = "publish" ]; then
        ok "$label page exists (ID: $page_id)"
    else
        fail "$label page status: $post_status (expected 'publish')"
    fi
}

check_page "shop" "Shop"
check_page "cart" "Cart"
check_page "checkout" "Checkout"
check_page "myaccount" "My Account"

# ── 6. Cart/Checkout Page Content (Block vs Shortcode) ──────────
echo ""
echo "── Cart/Checkout Content (Block vs Shortcode) ───────────"

CART_PAGE_ID=$(wp option get woocommerce_cart_page_id 2>/dev/null || echo "")
if [ -n "$CART_PAGE_ID" ] && [ "$CART_PAGE_ID" != "0" ]; then
    CART_CONTENT=$(wp post get "$CART_PAGE_ID" --field=post_content 2>/dev/null || echo "")
    if echo "$CART_CONTENT" | grep -q "wp:woocommerce/cart"; then
        ok "Cart page uses Cart Block"
    elif echo "$CART_CONTENT" | grep -q "\[woocommerce_cart\]"; then
        fail "Cart page still uses [woocommerce_cart] shortcode (should use Cart Block)"
    else
        warn "Cart page content is empty or unrecognized"
    fi
fi

CHECKOUT_PAGE_ID=$(wp option get woocommerce_checkout_page_id 2>/dev/null || echo "")
if [ -n "$CHECKOUT_PAGE_ID" ] && [ "$CHECKOUT_PAGE_ID" != "0" ]; then
    CO_CONTENT=$(wp post get "$CHECKOUT_PAGE_ID" --field=post_content 2>/dev/null || echo "")
    if echo "$CO_CONTENT" | grep -q "wp:woocommerce/checkout"; then
        ok "Checkout page uses Checkout Block"
    elif echo "$CO_CONTENT" | grep -q "\[woocommerce_checkout\]"; then
        fail "Checkout page still uses [woocommerce_checkout] shortcode (should use Checkout Block)"
    else
        warn "Checkout page content is empty or unrecognized"
    fi
fi

# ── 7. Permalink Structure ───────────────────────────────────────
echo ""
echo "── Permalink Structure ──────────────────────────────────"

PERMALINK=$(wp option get permalink_structure 2>/dev/null || echo "")
if [ "$PERMALINK" = "/%postname%/" ]; then
    ok "Permalink structure: /%postname%/"
else
    warn "Permalink structure: '$PERMALINK' (expected '/%postname%/')"
fi

# ── 8. Demo Products ──────────────────────────────────────────────
echo ""
echo "── Demo Products ────────────────────────────────────────"

PRODUCT_COUNT=$(wp post list --post_type=product --post_status=publish --format=count 2>/dev/null || echo "0")
if [ "$PRODUCT_COUNT" -ge 10 ]; then
    ok "Published products: $PRODUCT_COUNT (≥ 10 expected)"
else
    fail "Published products: $PRODUCT_COUNT (expected ≥ 10)"
fi

# Check specific products exist
for title in "Classic White Tee" "Slim Fit Denim Jacket" "Pleated Midi Skirt"; do
    exists=$(wp post list --post_type=product --name="$(echo "$title" | tr '[:upper:] ' '[:lower:]-')" --format=count 2>/dev/null || echo "0")
    if [ "$exists" -ge 1 ]; then
        ok "Product found: $title"
    else
        fail "Product NOT found: $title"
    fi
done

# ── 9. Categories ─────────────────────────────────────────────────
echo ""
echo "── Product Categories ───────────────────────────────────"

CAT_COUNT=$(wp term list product_cat --format=count 2>/dev/null || echo "0")
if [ "$CAT_COUNT" -ge 3 ]; then
    ok "Product categories: $CAT_COUNT (≥ 3 expected)"
else
    fail "Product categories: $CAT_COUNT (expected ≥ 3)"
fi

for cat in "Tops" "Bottoms" "Dresses"; do
    exists=$(wp term get product_cat "$cat" --by=slug --field=term_id 2>/dev/null || echo "")
    if [ -n "$exists" ]; then
        ok "Category found: $cat (ID: $exists)"
    else
        fail "Category NOT found: $cat"
    fi
done

# ── 10. Product Attributes ───────────────────────────────────────
echo ""
echo "── Product Attributes ────────────────────────────────────"

ATTR_COUNT=$(wp wc product_attribute list --format=count 2>/dev/null || echo "0")
if [ "$ATTR_COUNT" -ge 2 ]; then
    ok "Product attributes: $ATTR_COUNT (≥ 2 expected)"
else
    # Try alternative method
    COLOR_EXISTS=$(wp term list pa_color --format=count 2>/dev/null || echo "0")
    SIZE_EXISTS=$(wp term list pa_size --format=count 2>/dev/null || echo "0")
    if [ "$COLOR_EXISTS" -ge 1 ] && [ "$SIZE_EXISTS" -ge 1 ]; then
        ok "Attributes verified via terms (color: $COLOR_EXISTS, size: $SIZE_EXISTS terms)"
    else
        fail "Product attributes incomplete (color terms: $COLOR_EXISTS, size terms: $SIZE_EXISTS)"
    fi
fi

# ── 11. Site URL ──────────────────────────────────────────────────
echo ""
echo "── Site Configuration ──────────────────────────────────"

SITEURL=$(wp option get siteurl 2>/dev/null || echo "")
HOME_URL=$(wp option get home 2>/dev/null || echo "")
ok "Site URL: $SITEURL"
ok "Home URL: $HOME_URL"

# ── Summary ───────────────────────────────────────────────────────
echo ""
echo "═══════════════════════════════════════════════════════════"
echo "  Summary: $PASS passed, $FAIL failed, $WARN warnings"
echo "═══════════════════════════════════════════════════════════"

if [ "$FAIL" -gt 0 ]; then
    exit 1
fi
exit 0
