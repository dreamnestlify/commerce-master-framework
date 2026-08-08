#!/usr/bin/env bash
# ════════════════════════════════════════════════════════════════
# smoke-check.sh
# Post-initialization smoke checks for the Commerce Master framework.
# Verifies WordPress + WooCommerce + theme + pages + products
# are properly set up after running init.sh.
#
# Uses stable SKUs and actual category slugs from demo-data.php.
# Does NOT depend on product titles (which may change).
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

ok() {
    echo "  ✅ $1"
    PASS=$((PASS + 1))
}

fail() {
    echo "  ❌ $1"
    FAIL=$((FAIL + 1))
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

check_page_by_option() {
    local option_name="$1"
    local label="$2"

    local page_id
    page_id=$(wp option get "$option_name" 2>/dev/null || echo "")

    if [ -z "$page_id" ] || [ "$page_id" = "0" ]; then
        fail "$label page is not configured (option: $option_name)"
        return
    fi

    local post_status
    post_status=$(wp post get "$page_id" --field=post_status 2>/dev/null || echo "not_found")
    if [ "$post_status" = "publish" ]; then
        ok "$label page exists and is published (ID: $page_id)"
    else
        fail "$label page status: $post_status (expected 'publish')"
    fi
}

check_page_by_option "woocommerce_shop_page_id" "Shop"
check_page_by_option "woocommerce_cart_page_id" "Cart"
check_page_by_option "woocommerce_checkout_page_id" "Checkout"
check_page_by_option "woocommerce_myaccount_page_id" "My Account"

# Check Home page exists and is set as front page
echo ""
echo "── Home / Front Page ────────────────────────────────────"
HOME_PAGE_ID=$(wp post list --post_type=page --field=ID --title="Home" 2>/dev/null | head -1)
if [ -n "$HOME_PAGE_ID" ]; then
    ok "Home page exists (ID: $HOME_PAGE_ID)"
else
    fail "Home page does NOT exist"
fi

SHOW_ON_FRONT=$(wp option get show_on_front 2>/dev/null || echo "")
if [ "$SHOW_ON_FRONT" = "page" ]; then
    ok "Front page displays as static page"
else
    fail "Front page is '$SHOW_ON_FRONT' (expected 'page')"
fi

PAGE_ON_FRONT=$(wp option get page_on_front 2>/dev/null || echo "0")
if [ "$PAGE_ON_FRONT" != "0" ] && [ "$PAGE_ON_FRONT" = "$HOME_PAGE_ID" ]; then
    ok "Front page is set to Home page (ID: $PAGE_ON_FRONT)"
else
    fail "Front page ID is $PAGE_ON_FRONT (expected $HOME_PAGE_ID)"
fi

# ── 6. Cart/Checkout Page Content (Block vs Shortcode) ──────────
echo ""
echo "── Cart/Checkout Content (Block vs Shortcode) ───────────"

check_page_content() {
    local option_name="$1"
    local label="$2"
    local block_pattern="$3"
    local shortcode_pattern="$4"

    local page_id
    page_id=$(wp option get "$option_name" 2>/dev/null || echo "0")
    if [ "$page_id" = "0" ]; then
        fail "$label page not configured"
        return
    fi

    local content
    content=$(wp post get "$page_id" --field=post_content 2>/dev/null || echo "")

    if [ -z "$content" ]; then
        fail "$label page has EMPTY content"
    elif echo "$content" | grep -q "$block_pattern"; then
        ok "$label page uses correct block"
    elif echo "$content" | grep -q "$shortcode_pattern"; then
        fail "$label page still uses shortcode (should use block)"
    else
        fail "$label page content is unrecognized (not block, not shortcode)"
    fi
}

check_page_content "woocommerce_cart_page_id" "Cart" "wp:woocommerce/cart" "\[woocommerce_cart\]"
check_page_content "woocommerce_checkout_page_id" "Checkout" "wp:woocommerce/checkout" "\[woocommerce_checkout\]"

# ── 7. Permalink Structure ───────────────────────────────────────
echo ""
echo "── Permalink Structure ──────────────────────────────────"

PERMALINK=$(wp option get permalink_structure 2>/dev/null || echo "")
if [ "$PERMALINK" = "/%postname%/" ]; then
    ok "Permalink structure: /%postname%/"
else
    fail "Permalink structure: '$PERMALINK' (expected '/%postname%/')"
fi

# ── 8. Demo Products — Exact Count ───────────────────────────────
echo ""
echo "── Demo Products — Count & SKU ─────────────────────────"

EXPECTED_PRODUCT_COUNT=16
PRODUCT_COUNT=$(wp post list --post_type=product --post_status=publish --format=count 2>/dev/null || echo "0")
if [ "$PRODUCT_COUNT" = "$EXPECTED_PRODUCT_COUNT" ]; then
    ok "Published products: $PRODUCT_COUNT (expected exactly $EXPECTED_PRODUCT_COUNT)"
else
    fail "Published products: $PRODUCT_COUNT (expected exactly $EXPECTED_PRODUCT_COUNT)"
fi

# Verify each product by SKU
EXPECTED_SKUS=(
    "ACC-TOTE-001"
    "ACC-BELT-001"
    "ACC-SCARF-001"
    "ACC-SUN-001"
    "WOM-TS-001"
    "WOM-DR-001"
    "WOM-KC-001"
    "WOM-SK-001"
    "MEN-JK-001"
    "MEN-KN-001"
    "MEN-SH-001"
    "SHO-SN-001"
    "SHO-BT-001"
    "SHO-LF-001"
    "ACC-BEANIE-001"
    "ACC-CH-001"
)

for sku in "${EXPECTED_SKUS[@]}"; do
    product_id=$(wp post list --post_type=product --meta_key=_sku --meta_value="$sku" --field=ID 2>/dev/null | head -1)
    if [ -n "$product_id" ]; then
        ok "Product SKU found: $sku (ID: $product_id)"
    else
        fail "Product SKU NOT found: $sku"
    fi
done

# ── 9. Product Types ─────────────────────────────────────────────
echo ""
echo "── Product Types ────────────────────────────────────────"

check_product_type() {
    local sku="$1"
    local expected_type="$2"

    local product_id
    product_id=$(wp post list --post_type=product --meta_key=_sku --meta_value="$sku" --field=ID 2>/dev/null | head -1)
    if [ -z "$product_id" ]; then
        fail "Cannot check type for SKU $sku — product not found"
        return
    fi

    # Get product type via WC_Product::get_type() (most reliable method).
    # WooCommerce stores product type in product_type taxonomy, not post meta.
    local product_type
    product_type=$(wp eval "echo wc_get_product($product_id)->get_type();" 2>/dev/null || echo "unknown")

    if [ "$product_type" = "$expected_type" ]; then
        ok "SKU $sku: type=$expected_type"
    else
        fail "SKU $sku: type=$product_type (expected $expected_type)"
    fi
}

# Simple products
check_product_type "ACC-TOTE-001" "simple"
check_product_type "ACC-BELT-001" "simple"
check_product_type "ACC-SCARF-001" "simple"
check_product_type "ACC-SUN-001" "simple"
check_product_type "ACC-BEANIE-001" "simple"
check_product_type "ACC-CH-001" "simple"

# Variable products
check_product_type "WOM-TS-001" "variable"
check_product_type "WOM-DR-001" "variable"
check_product_type "WOM-KC-001" "variable"
check_product_type "WOM-SK-001" "variable"
check_product_type "MEN-JK-001" "variable"
check_product_type "MEN-KN-001" "variable"
check_product_type "MEN-SH-001" "variable"
check_product_type "SHO-SN-001" "variable"
check_product_type "SHO-BT-001" "variable"
check_product_type "SHO-LF-001" "variable"

# ── 10. Variation Counts ─────────────────────────────────────────
echo ""
echo "── Variation Counts (exact) ─────────────────────────────"

check_variation_count() {
    local sku="$1"
    local expected_count="$2"

    local product_id
    product_id=$(wp post list --post_type=product --meta_key=_sku --meta_value="$sku" --field=ID 2>/dev/null | head -1)
    if [ -z "$product_id" ]; then
        fail "Cannot check variations for SKU $sku — product not found"
        return
    fi

    local var_count
    var_count=$(wp post list --post_type=product_variation --post_parent="$product_id" --format=count 2>/dev/null || echo "0")

    if [ "$var_count" = "$expected_count" ]; then
        ok "SKU $sku: $var_count variations (expected $expected_count)"
    else
        fail "SKU $sku: $var_count variations (expected $expected_count)"
    fi
}

# WOM-TS-001: 4 colors × 5 sizes = 20
check_variation_count "WOM-TS-001" 20
# WOM-DR-001: 3 colors × 4 sizes = 12
check_variation_count "WOM-DR-001" 12
# WOM-KC-001: 3 colors × 4 sizes = 12
check_variation_count "WOM-KC-001" 12
# WOM-SK-001: 3 colors × 4 sizes = 12
check_variation_count "WOM-SK-001" 12
# MEN-JK-001: 3 colors × 5 sizes = 15
check_variation_count "MEN-JK-001" 15
# MEN-KN-001: 3 colors × 4 sizes = 12
check_variation_count "MEN-KN-001" 12
# MEN-SH-001: 3 colors × 5 sizes = 15
check_variation_count "MEN-SH-001" 15
# SHO-SN-001: 3 colors × 8 shoe sizes = 24
check_variation_count "SHO-SN-001" 24
# SHO-BT-001: 2 colors × 6 shoe sizes = 12
check_variation_count "SHO-BT-001" 12
# SHO-LF-001: 3 colors × 5 shoe sizes = 15
check_variation_count "SHO-LF-001" 15

# ── 11. Categories ─────────────────────────────────────────────────
echo ""
echo "── Product Categories ───────────────────────────────────"

# Top-level categories
EXPECTED_CATS=("women" "men" "shoes" "accessories")
# Sub-categories
EXPECTED_SUBCATS=("tops" "dresses" "outerwear" "knitwear" "sneakers" "boots" "bags" "belts" "scarves" "sunglasses")

for cat_slug in "${EXPECTED_CATS[@]}" "${EXPECTED_SUBCATS[@]}"; do
    term_id=$(wp term get product_cat "$cat_slug" --by=slug --field=term_id 2>/dev/null || echo "")
    if [ -n "$term_id" ]; then
        ok "Category found: $cat_slug (ID: $term_id)"
    else
        fail "Category NOT found: $cat_slug"
    fi
done

# Verify product tags
echo ""
echo "── Product Tags ─────────────────────────────────────────"

EXPECTED_TAGS=("new-arrival" "sale" "featured")
for tag_slug in "${EXPECTED_TAGS[@]}"; do
    term_id=$(wp term get product_tag "$tag_slug" --by=slug --field=term_id 2>/dev/null || echo "")
    if [ -n "$term_id" ]; then
        ok "Tag found: $tag_slug (ID: $term_id)"
    else
        fail "Tag NOT found: $tag_slug"
    fi
done

# ── 12. Product Attributes ───────────────────────────────────────
echo ""
echo "── Product Attributes ───────────────────────────────────"

# Check pa_color terms (expected: 15)
COLOR_TERMS=$(wp term list pa_color --format=count 2>/dev/null || echo "0")
if [ "$COLOR_TERMS" -ge 15 ]; then
    ok "pa_color terms: $COLOR_TERMS (≥ 15 expected)"
else
    fail "pa_color terms: $COLOR_TERMS (expected ≥ 15)"
fi

# Check pa_size terms (expected: 6)
SIZE_TERMS=$(wp term list pa_size --format=count 2>/dev/null || echo "0")
if [ "$SIZE_TERMS" -ge 6 ]; then
    ok "pa_size terms: $SIZE_TERMS (≥ 6 expected)"
else
    fail "pa_size terms: $SIZE_TERMS (expected ≥ 6)"
fi

# Check pa_shoe_size terms (expected: 8)
SHOE_SIZE_TERMS=$(wp term list pa_shoe_size --format=count 2>/dev/null || echo "0")
if [ "$SHOE_SIZE_TERMS" -ge 8 ]; then
    ok "pa_shoe_size terms: $SHOE_SIZE_TERMS (≥ 8 expected)"
else
    fail "pa_shoe_size terms: $SHOE_SIZE_TERMS (expected ≥ 8)"
fi

# ── 13. Site Configuration ───────────────────────────────────────
echo ""
echo "── Site Configuration ──────────────────────────────────"

SITEURL=$(wp option get siteurl 2>/dev/null || echo "")
HOME_URL=$(wp option get home 2>/dev/null || echo "")
ok "Site URL: $SITEURL"
ok "Home URL: $HOME_URL"

# ── Summary ───────────────────────────────────────────────────────
echo ""
echo "═══════════════════════════════════════════════════════════"
echo "  Summary: $PASS passed, $FAIL failed"
echo "═══════════════════════════════════════════════════════════"

if [ "$FAIL" -gt 0 ]; then
    exit 1
fi
exit 0
