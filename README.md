# Commerce Master — WordPress Fashion E-Commerce Framework

A reusable WordPress + WooCommerce master template for European/American fashion e-commerce independent sites.

## Quick Start

### Prerequisites

- Docker Desktop (or Docker Engine + Docker Compose)
- Git

### Setup

```bash
# 1. Clone the repository
git clone <repo-url> commerce-master
cd commerce-master

# 2. Copy environment config and fill in values
cp .env.example .env
# Edit .env — set DB passwords, generate WordPress salts
# Salts: https://api.wordpress.org/secret-key/1.1/salt/

# 3. Start the development environment
docker compose up -d

# 4. Install WordPress + WooCommerce + theme + demo data
docker compose --profile cli run --rm wpcli bash /scripts/init.sh

# 5. Access the site
open http://localhost:8080
# Admin: http://localhost:8080/wp-admin
# phpMyAdmin: http://localhost:8090
```

## Tech Stack

| Component | Version / Tool |
|---|---|
| WordPress | 7.0.2 |
| PHP | 8.3 |
| WooCommerce | 11.0.0 |
| Database | MariaDB 11.8 LTS |
| Runtime | Docker Compose |
| Node (build/lint) | 22.x |
| Frontend | Gutenberg Blocks + native WC Blocks |

## Project Structure

```
.
├── docker-compose.yml          # Local development environment
├── .env.example                 # Environment config template
├── .gitignore
├── AGENTS.md                   # Agent / developer guidelines
├── README.md                   # This file
├── docs/
│   ├── ARCHITECTURE.md         # Architecture decisions and layering
│   ├── ROADMAP.md              # Phase 0–4 roadmap
│   └── DECISIONS.md            # Key technical decisions log
├── scripts/
│   └── init.sh                 # WP-CLI site initialization (idempotent)
├── wp-content/
│   ├── plugins/
│   │   └── commerce-core/      # Self-built core plugin (business logic)
│   └── themes/
│       └── commerce-block-theme/  # Self-built Gutenberg block theme
├── composer.json               # PHP dependencies & tooling
├── package.json                # Node dependencies & build scripts
└── .github/
    └── workflows/
        └── ci.yml              # CI pipeline
```

## Architecture

Single master codebase, multi-site deployment (not WordPress Multisite). See [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) for details.

### Layering

1. **commerce-core plugin** — Business logic, configuration, adapter interfaces, extension points
2. **commerce-block-theme** — Gutenberg FSE block theme, templates, block patterns, responsive UX
3. **UI style packs** — `theme.json` + Style Variations for brand visual changes
4. **Site config** — Brand, logo, market, currency, language, support, tracking IDs
5. **Product data** — WooCommerce native product model + CSV/CLI import
6. **Third-party adapters** — Payment, ERP, email, support, analytics (decoupled)

### Key Principles

- Business logic never lives in the theme.
- WordPress and WooCommerce core files are never modified.
- WooCommerce Blocks and Gutenberg native capabilities are preferred.
- All self-authored user-visible strings are internationalized (English first).
- Multi-currency must keep orders, refunds, discounts, taxes, and payment gateways in the same transaction currency.
- Accessibility target: WCAG 2.2 AA. Mobile-first.
- Design references large European/American fashion e-commerce information architecture without copying any third-party protected materials.

## Development

### PHP (Lint / Static Analysis / Tests)

```bash
composer install
composer lint          # PHP lint
composer phpcs         # WordPress Coding Standards
composer phpstan       # Static analysis
composer test          # PHPUnit tests
```

### Frontend (Lint / Build)

```bash
cd wp-content/themes/commerce-block-theme
npm install
npm run lint           # ESLint + Stylelint
npm run build          # Build theme assets
```

### Re-run Demo Data Initialization

The init script is idempotent — safe to run multiple times:

```bash
docker compose --profile cli run --rm wpcli bash /scripts/init.sh
```

## Phases

| Phase | Goal | Status |
|---|---|---|
| 0 | Engineering master template | In progress |
| 1 | Fashion e-commerce shopping experience | Planned |
| 2 | Transactions & localization | Planned |
| 3 | Growth & operations | Planned |
| 4 | ERP & multi-site replication | Planned |

See [docs/ROADMAP.md](docs/ROADMAP.md) for details.

## License

Proprietary. All rights reserved.
