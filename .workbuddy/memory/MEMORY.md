# Project Memory — Commerce Master

## Project Type
WordPress + WooCommerce fashion e-commerce framework (master template for multi-site deployment).

## Tech Stack
- WordPress 6.7 + PHP 8.3 + WooCommerce 9.x
- MariaDB 11.6 (Docker)
- Gutenberg FSE block theme (commerce-block-theme)
- Self-built plugin (commerce-core) with module registry, adapter interfaces
- Docker Compose for local dev
- Git initialized in workspace root

## Architecture
- 6-layer: commerce-core plugin → commerce-block-theme → UI style packs → site config → product data → third-party adapters
- Single master codebase, multi-site independent deployment (NOT Multisite)
- Business logic in plugin only, never in theme
- Adapter pattern for payment/ERP/email/support/analytics (Phase 0: interfaces only)

## Phase 0 Status (completed 2026-08-08)
- 86 files, 6,623 lines of code
- 5 git commits
- Docker Compose, plugin skeleton, theme skeleton, 10 demo products, 15 unit tests
- ESLint ✅ Stylelint ✅ YAML ✅ JSON ✅ all pass
- PHP/PHPCS/PHPStan/PHPUnit NOT run (PHP not installed on this machine)
- Docker NOT available for runtime verification

## Missing Dependencies
- Docker (not installed) — needed to run WP/WC
- PHP + Composer (not installed) — needed for PHP lint/test

## Next Steps for User
1. Install Docker: `brew install --cask docker`
2. Install PHP: `brew install php composer`
3. `cp .env.example .env` (fill in passwords + salts)
4. `docker compose up -d`
5. `docker compose --profile cli run --rm wpcli bash /scripts/init.sh`
6. Run `composer check-all` in plugin dir
