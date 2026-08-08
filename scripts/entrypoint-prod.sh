#!/bin/bash
# ──────────────────────────────────────────────
# Production Entrypoint for Commerce Master
# Runs composer install for plugin dependencies,
# then starts the default WordPress Apache process.
# ──────────────────────────────────────────────
set -e

PLUGIN_DIR="/var/www/html/wp-content/plugins/commerce-core"

# Install Composer if not already available in the WordPress image.
if ! command -v composer &>/dev/null; then
    echo "[entrypoint-prod] Installing Composer..."
    apt-get update -qq
    apt-get install -y -qq curl unzip
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

# Install plugin dependencies (e.g. stripe/stripe-php) on every start.
# This ensures vendor/ matches the committed composer.lock after deployments.
if [ -f "${PLUGIN_DIR}/composer.json" ] && [ -f "${PLUGIN_DIR}/composer.lock" ]; then
    echo "[entrypoint-prod] Installing plugin dependencies with Composer..."
    cd "${PLUGIN_DIR}"
    composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
else
    echo "[entrypoint-prod] Warning: composer.json or composer.lock not found in ${PLUGIN_DIR}"
fi

# Delegate to the official WordPress entrypoint to finish setup and start Apache.
echo "[entrypoint-prod] Starting WordPress..."
exec docker-entrypoint.sh apache2-foreground
