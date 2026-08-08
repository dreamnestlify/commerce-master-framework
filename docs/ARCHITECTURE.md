# Architecture — Commerce Master

## Overview

Single master codebase, multi-site independent deployment. Not WordPress Multisite.

Each site is a full WordPress installation cloned from the master template. New sites are configured via:
- Brand configuration (name, logo, tagline)
- `theme.json` + Style Variations (visual identity)
- Content & product data
- Third-party account credentials (env vars)

## Layering

```
┌──────────────────────────────────────────────────────────────┐
│                     Site Layer                                │
│  Brand config · theme.json · content · product data · creds  │
├──────────────────────────────────────────────────────────────┤
│              commerce-block-theme                              │
│  Templates · Template Parts · Block Patterns · theme.json      │
├──────────────────────────────────────────────────────────────┤
│              commerce-core (plugin)                             │
│  Modules · Config · Adapter Interfaces · Security · REST API   │
├──────────────────────────────────────────────────────────────┤
│         WordPress + WooCommerce (core)                         │
│  Unmodified · Updated via official channels                     │
├──────────────────────────────────────────────────────────────┤
│              Infrastructure (Docker)                           │
│  PHP-FPM · MariaDB · phpMyAdmin · WP-CLI · Node               │
└──────────────────────────────────────────────────────────────┘
```

## commerce-core Plugin

The plugin encapsulates all business logic that is not visual. It is **not** coupled to the theme.

### Module System

```
Plugin (lifecycle)
  └─ ModuleRegistry
       ├─ SettingsModule    (brand, market, currency, support config)
       ├─ SecurityModule     (nonce, capability, escaping)
       ├─ AdapterModule      (payment, ERP, email, support, analytics interfaces)
       └─ ... (future modules)
```

- Each module implements `ModuleInterface` with `register()` and `boot()` methods.
- `ModuleRegistry` discovers and boots modules in dependency order.
- No single mega-file entry point — modules are self-contained.

### Configuration Model

Configuration is structured, typed, and stored in WordPress options (or env vars for secrets).

```php
$config = [
    'brand' => [
        'name'     => 'Commerce Master',
        'tagline'  => 'Fashion for the modern world',
        'logo_id'  => null,  // attachment ID
    ],
    'market' => [
        'default_locale'    => 'en_US',
        'base_currency'     => 'USD',
        'enabled_currencies' => ['USD', 'EUR', 'GBP'],
        'default_market'    => 'EU',
    ],
    'support' => [
        'email' => 'support@example.com',
        'phone' => '',
    ],
    'analytics' => [
        'ga4_measurement_id' => '',
        'meta_pixel_id'      => '',
        'tiktok_pixel_id'    => '',
        'google_ads_id'      => '',
    ],
];
```

Secrets (Stripe keys, PayPal credentials, ERP API keys) are read from environment variables or WordPress constants defined in `wp-config.php`, never stored in options or committed to Git.

### Adapter Interfaces

Each integration domain defines a PHP interface. In Phase 0, only interfaces exist — no implementations.

```php
interface PaymentAdapterInterface {
    public function process_payment(int $order_id, array $payment_data): PaymentResult;
    public function process_refund(int $order_id, float $amount, string $reason = ''): RefundResult;
}

interface ErpAdapterInterface {
    public function sync_inventory(array $product_skus): SyncResult;
    public function sync_order(int $order_id): SyncResult;
}

interface EmailAdapterInterface {
    public function send(string $to, string $subject, string $template, array $data): bool;
}

interface SupportAdapterInterface {
    public function inject_widget(): void;
    public function get_config(): array;
}

interface AnalyticsAdapterInterface {
    public function track_event(string $event_name, array $params = []): void;
    public function inject_tracking(): void;
}
```

### Security

- All settings saves: nonce verification (`check_admin_referer`) + capability check (`manage_options` or custom).
- All settings inputs: sanitized (`sanitize_text_field`, `sanitize_email`, `sanitize_textarea_field`).
- All outputs: escaped (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`).
- All DB queries: prepared statements (`$wpdb->prepare`).
- REST API endpoints: permission callbacks + nonce.

## commerce-block-theme

A full-site editing (FSE) block theme built on Gutenberg.

### theme.json

Defines design tokens:
- Color palette (neutral base: black/white/gray + brand accent slots)
- Typography (font families, sizes, line heights)
- Spacing scale
- Border radius, shadows
- Layout (content width, wide width)
- Custom properties (CSS variables)

### Style Variations

JSON files in `styles/` directory allow swapping the entire visual identity:
- `styles/default.json` — neutral dark-on-light
- `styles/light.json` — minimal white
- `styles/dark.json` — dark mode
- Future: per-brand style packs

### Templates

| Template | Purpose |
|---|---|
| `index.html` | Fallback |
| `front-page.html` | Homepage with block patterns |
| `page.html` | Static pages |
| `search.html` | Search results |
| `404.html` | Not found |
| `archive-product.html` | WooCommerce product archive |
| `single-product.html` | Single product page |
| `page-cart.html` | Cart page |
| `page-checkout.html` | Checkout page |
| `page-my-account.html` | Account page |

### Template Parts

- `parts/header.html` — Site header (announcement bar + nav + mini-cart)
- `parts/footer.html` — Site footer (links + newsletter + social)
- `parts/checkout-header.html` — Simplified checkout header

### Block Patterns

Homepage block patterns:
- Announcement bar
- Fashion header (logo + mega nav + search + account + mini-cart)
- Hero (full-bleed image + headline + CTA)
- Category grid (shop by category)
- New arrivals (product collection)
- Editorial campaign (lookbook / editorial block)
- Product collection (curated grid)
- Benefits strip (shipping, returns, secure payment, etc.)
- Newsletter signup
- Footer (link columns + social + legal)

## Docker Environment

```
┌─ docker-compose.yml ─────────────────────────────────┐
│                                                      │
│  wordpress (PHP 8.3 + Apache)  ←── :8080             │
│    └─ volumes: commerce-core (ro), commerce-block-theme (ro)
│                                                      │
│  db (MariaDB 11.8 LTS)          ←── :3307            │
│                                                      │
│  phpmyadmin                     ←── :8090            │
│                                                      │
│  wpcli (on-demand)             ←── profile: cli      │
│                                                      │
│  node (on-demand)              ←── profile: build     │
│                                                      │
└──────────────────────────────────────────────────────┘
```

- Plugin and theme are mounted read-only into the WordPress container for live editing.
- DB and WordPress core data use named volumes.
- WP-CLI and Node run on-demand via profiles to keep `up -d` lightweight.
