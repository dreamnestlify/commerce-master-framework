#!/usr/bin/env bash
# ════════════════════════════════════════════════════════════════
# block-name-scanner.sh
# Scans theme templates, parts, and patterns for deprecated/invalid
# WooCommerce block names per WooCommerce 11.0.
#
# Usage:
#   bash scripts/block-name-scanner.sh [--theme-dir PATH]
#
# Exit codes:
#   0 — All block names valid (or no deprecated names found)
#   1 — Deprecated block names found (details printed)
#   2 — Theme directory not found
# ════════════════════════════════════════════════════════════════
set -euo pipefail

THEME_DIR="${1:-$(cd "$(dirname "$0")/.." && pwd)/wp-content/themes/commerce-block-theme}"

if [ ! -d "$THEME_DIR" ]; then
    echo "ERROR: Theme directory not found: $THEME_DIR"
    exit 2
fi

# ── Deprecated block names and their WC 11 replacements ───────────
# Format: "old_name|new_name"
DEPRECATED_BLOCKS=(
    "woocommerce/breadcrumb|woocommerce/breadcrumbs"
    "woocommerce/result-count|woocommerce/product-results-count"
    "woocommerce/catalog-ordering|woocommerce/catalog-sorting"
    "woocommerce/product-short-description|woocommerce/product-summary"
    "woocommerce/add-to-cart|woocommerce/add-to-cart-form"
    "woocommerce/product-tabs|woocommerce/product-details"
    "woocommerce-customer-account|woocommerce/customer-account"
    "woocommerce-mini-cart|woocommerce/mini-cart"
    "woocommerce/cart-fragment|woocommerce/mini-cart-contents"
)

# Deprecated shortcodes that should be replaced with blocks
DEPRECATED_SHORTCODES=(
    "[woocommerce_cart]"
    "[woocommerce_checkout]"
    "[woocommerce_my_account]"
)

echo "═══════════════════════════════════════════════════════════"
echo "  WooCommerce Block Name Scanner (WC 11.0)"
echo "  Theme: $THEME_DIR"
echo "═══════════════════════════════════════════════════════════"
echo ""

# Collect files to scan
FILES=()
for dir in templates parts patterns; do
    if [ -d "$THEME_DIR/$dir" ]; then
        while IFS= read -r -d '' f; do
            FILES+=("$f")
        done < <(find "$THEME_DIR/$dir" \( -name '*.html' -o -name '*.php' \) -print0)
    fi
done

if [ ${#FILES[@]} -eq 0 ]; then
    echo "WARNING: No template/part/pattern files found to scan."
    exit 0
fi

echo "Scanning ${#FILES[@]} files..."
echo ""

ERRORS_FOUND=0

for file in "${FILES[@]}"; do
    rel_path="${file#$THEME_DIR/}"

    # Check deprecated block names
    for mapping in "${DEPRECATED_BLOCKS[@]}"; do
        old_name="${mapping%%|*}"
        new_name="${mapping##*|}"

        # Match the deprecated block name as an EXACT token (not a substring).
        # Uses -E extended regex with a terminator character class to prevent
        # matching "breadcrumb" inside "breadcrumbs" or "add-to-cart" inside
        # "add-to-cart-form".
        # The pattern: wp:OLD_NAME followed by a non-continuation character
        # (space, slash, quote, brace, closing comment, or end of line).
        matches=$(grep -nE "wp:${old_name}([^a-zA-Z0-9_-]|$)" "$file" 2>/dev/null || true)

        if [ -n "$matches" ]; then
            while IFS= read -r line; do
                line_num="${line%%:*}"
                line_content="${line#*:}"
                echo "  ❌ DEPRECATED: $rel_path:$line_num"
                echo "     Found:     $old_name"
                echo "     Should be: $new_name"
                echo "     Line:      ${line_content#"${line_content%%[![:space:]]*}"}"
                echo ""
                ERRORS_FOUND=$((ERRORS_FOUND + 1))
            done <<< "$matches"
        fi
    done

    # Check deprecated shortcodes
    for shortcode in "${DEPRECATED_SHORTCODES[@]}"; do
        matches=$(grep -nF "$shortcode" "$file" 2>/dev/null || true)

        if [ -n "$matches" ]; then
            while IFS= read -r line; do
                line_num="${line%%:*}"
                line_content="${line#*:}"
                echo "  ❌ SHORTCODE: $rel_path:$line_num"
                echo "     Found:     $shortcode (should use WooCommerce Block)"
                echo "     Line:      ${line_content#"${line_content%%[![:space:]]*}"}"
                echo ""
                ERRORS_FOUND=$((ERRORS_FOUND + 1))
            done <<< "$matches"
        fi
    done
done

# ── Summary ───────────────────────────────────────────────────────
echo "───────────────────────────────────────────────────────────"
if [ "$ERRORS_FOUND" -eq 0 ]; then
    echo "  ✅ All block names are valid for WooCommerce 11.0."
    echo "  Scanned ${#FILES[@]} files, found 0 deprecated names."
    exit 0
else
    echo "  ❌ Found $ERRORS_FOUND deprecated block name(s) or shortcode(s)."
    echo "  Fix the above issues before proceeding."
    exit 1
fi
