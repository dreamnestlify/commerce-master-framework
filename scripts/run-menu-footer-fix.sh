#!/bin/bash
# ═══════════════════════════════════════════════════════════════
# Zalandy — Apply Footer & Header Menu Fix
#
# Usage on server:
#   cd /opt/commerce-master
#   bash scripts/run-menu-footer-fix.sh
# ═══════════════════════════════════════════════════════════════
set -e

cd /opt/commerce-master
COMPOSE="docker compose -f docker-compose.yml -f docker-compose.prod.yml"
WP="exec wordpress /usr/local/bin/wp --allow-root"

echo "========================================"
echo "  Zalandy — Apply Footer & Menu Fix"
echo "========================================"
echo ""

echo "1/3 — Copying fix script into container..."
$COMPOSE cp scripts/fix-menu-and-footer.php wordpress:/tmp/fix-menu-and-footer.php
echo "  ✅ Copied"
echo ""

echo "2/3 — Running fix script..."
$COMPOSE exec wordpress bash -c "php -d memory_limit=512M /usr/local/bin/wp eval-file /tmp/fix-menu-and-footer.php --allow-root"
echo "  ✅ Script executed"
echo ""

echo "3/3 — Flushing caches..."
$COMPOSE $WP cache flush 2>/dev/null || true
$COMPOSE $WP rewrite flush --hard 2>/dev/null || true
echo "  ✅ Done"
echo ""

echo "========================================"
echo "  Fix Applied"
echo "========================================"
echo ""
echo "Verify at:"
echo "  Homepage: https://zalandy.top"
echo "  Shop:     https://zalandy.top/shop/"
echo ""
echo "If the footer still looks wrong, hard-refresh the browser:"
echo "  Ctrl+Shift+R (Windows/Linux) or Cmd+Shift+R (Mac)"
echo ""
