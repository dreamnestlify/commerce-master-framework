# AGENTS.md — Development Agent Guidelines

This file provides context and rules for AI agents and human developers working on this repository.

## Project Context

A reusable WordPress + WooCommerce master template for European/American fashion e-commerce. The master is cloned per site; new sites mainly swap brand config, `theme.json`/UI style, content, product data, and third-party credentials.

- **First product line:** apparel, footwear, accessories
- **First language:** English
- **Transaction currencies:** USD, EUR, GBP
- **Payments:** Stripe, PayPal (Phase 2; sandbox only)
- **Editor:** Gutenberg native block editor
- **UX reference:** Zalando information architecture (no copying of trademarks, images, copy, or source code)

## Architecture Rules

1. Business logic **never** lives in the theme.
2. **Never** modify WordPress or WooCommerce core files.
3. Prefer WooCommerce Blocks and Gutenberg native capabilities over custom implementations.
4. All self-authored user-visible strings **must** be internationalized (`__()`, `_x()`, `esc_html__()`, etc.).
5. Multi-currency must keep orders, refunds, discounts, taxes, and payment gateways in the same transaction currency.
6. Credentials are injected via environment variables or WordPress secure config — **never** committed to Git.
7. Accessibility target: **WCAG 2.2 AA**. Mobile-first design.
8. Product variations use color and size as core attributes; shoe size, material, fit are extensible.

## Code Quality

### PHP
- Follow WordPress Coding Standards (WPCS).
- Use prepared statements for all database queries.
- Escape all output (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`).
- Sanitize all input (`sanitize_text_field`, `sanitize_email`, etc.).
- Verify nonces on all form submissions and AJAX requests.
- Check capabilities before privileged operations.
- Use namespaces and autoloading (PSR-4 where possible).
- Never log passwords, payment info, or personal data.

### JavaScript / CSS
- ESLint + Stylelint for linting.
- Load assets conditionally (`wp_enqueue_scripts`, not inline).
- Respect `prefers-reduced-motion`.

### Testing
- Write meaningful tests for critical plugin logic.
- Do not write empty tests just to inflate count.
- Tests must be runnable without Docker (mock WP functions where needed).

## Git Workflow

- Commit messages: conventional style (`feat:`, `fix:`, `docs:`, `refactor:`, `test:`, `chore:`).
- **Never** commit `.env`, `*.sql`, `wp-content/uploads/`, database volumes, or secrets.
- Run `git diff` self-check before committing.

## File Organization

```
commerce-core/
├── commerce-core.php          # Main plugin entry (bootstrap)
├── composer.json
├── src/
│   ├── Plugin.php              # Plugin lifecycle (activation/deactivation)
│   ├── Module/
│   │   ├── ModuleInterface.php # Module contract
│   │   ├── ModuleRegistry.php  # Module registration & bootstrapping
│   │   ├── SettingsModule.php  # Configuration management
│   │   └── SecurityModule.php  # Nonce, capability, escaping helpers
│   ├── Config/
│   │   ├── BrandConfig.php     # Brand name, tagline, logo
│   │   ├── MarketConfig.php    # Market, locale, currencies
│   │   └── SupportConfig.php   # Support contact info
│   ├── Adapter/
│   │   ├── PaymentAdapterInterface.php
│   │   ├── ErpAdapterInterface.php
│   │   ├── EmailAdapterInterface.php
│   │   ├── SupportAdapterInterface.php
│   │   └── AnalyticsAdapterInterface.php
│   ├── Admin/
│   │   ├── SettingsPage.php    # Admin settings page
│   │   └── SettingsSection.php
│   ├── Rest/
│   │   └── SettingsController.php
│   └── Util/
│       ├── Idempotency.php     # Idempotent operations helper
│       └── Logger.php
├── tests/
│   ├── phpstan.neon
│   └── phpunit/
│       ├── Unit/
│       └── bootstrap.php
├── languages/
│   └── commerce-core.pot
└── uninstall.php
```

## Environment Dependencies

| Tool | Required for | Status on this machine |
|---|---|---|
| Git | Version control | Available |
| Node 22 | Theme build, lint | Available |
| PHP 8.3 | Plugin code, lint, tests | **Not installed** |
| Composer | PHP deps, PHPCS, PHPStan | **Not installed** |
| Docker | Local WP environment | **Not installed** |
| WP-CLI 2.12.0 | Demo data init | Not installed (via Docker image) |

When dependencies are missing:
- Complete all reviewable engineering files.
- Run all checks that can run (JS/CSS lint).
- Do not fake success — accurately report what was not run and why.
- Provide the user with exact install commands for the next step.
