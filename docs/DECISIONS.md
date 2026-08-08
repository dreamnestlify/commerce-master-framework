# Key Technical Decisions — Commerce Master

## D-001: Single Master, Multi-Site Deployment (not Multisite)

**Date:** 2026-08-08  
**Status:** Decided

**Context:** The project needs to create multiple independent websites that share core e-commerce capabilities but differ in branding, design, products, and integrations.

**Decision:** Use "single master codebase, multi-site independent deployment." Each site is a full WordPress installation cloned from the master template. Not WordPress Multisite.

**Rationale:**
- Multisite couples sites at the database level, making per-site customization harder.
- Independent deployments allow per-site hosting, SSL, CDN, and scaling.
- Config swapping (brand, theme.json, data, credentials) is sufficient for differentiation.
- Avoids Multisite's known complexity with plugins, domains, and security.

**Consequences:** Each site needs its own DB and hosting. Upgrades must be applied per site (mitigated by CI/CD in Phase 4).

---

## D-002: Gutenberg FSE Block Theme (not classic theme)

**Date:** 2026-08-08  
**Status:** Decided

**Context:** The theme must support brand visual swapping via JSON configuration and provide modern editing experience.

**Decision:** Build a full-site editing (FSE) block theme with `theme.json` and Style Variations.

**Rationale:**
- Block themes support `theme.json` design tokens for systematic visual changes.
- Style Variations allow per-brand visual identity via JSON files.
- Gutenberg is the future of WordPress theming; ensures long-term compatibility.
- Business logic stays in the plugin, theme is purely presentation.

---

## D-003: Plugin-First Business Logic

**Date:** 2026-08-08  
**Status:** Decided

**Context:** E-commerce logic (payments, currencies, taxes, ERP) must be reusable and brand-agnostic.

**Decision:** All business logic lives in `commerce-core` plugin. Theme contains only presentation.

**Rationale:**
- Plugins survive theme switches; logic is portable.
- Clear separation of concerns.
- Future modules (marketing, ERP, COD) can be added to the plugin without touching the theme.

---

## D-004: Adapter Pattern for Integrations

**Date:** 2026-08-08  
**Status:** Decided

**Context:** Payment, ERP, email, support, and analytics vendors are not yet decided. The system must be ready to swap implementations.

**Decision:** Define PHP interfaces for each integration domain. Phase 0 ships interfaces only — no implementations.

**Rationale:**
- Vendor-agnostic architecture; swap Stripe→Adyen or Mailchimp→Klaviyo without touching business logic.
- Clear contracts for future development.
- Prevents premature coupling to specific vendors.

---

## D-005: Module Registry (not mega-file)

**Date:** 2026-08-08  
**Status:** Decided

**Context:** WordPress plugins commonly start as single large files that become unmaintainable.

**Decision:** Use a lightweight module registration system. Each module implements `ModuleInterface` and is registered with `ModuleRegistry`.

**Rationale:**
- Each module is self-contained (own namespace, own tests).
- Modules boot in dependency order.
- New features are added as new modules, not appended to a mega-file.

---

## D-006: Docker Compose for Local Dev

**Date:** 2026-08-08  
**Status:** Decided

**Context:** Developers need a consistent, reproducible local WordPress environment.

**Decision:** Use Docker Compose with named images (WordPress 7.0.2 + PHP 8.3, MariaDB 11.8 LTS, phpMyAdmin, WP-CLI 2.12.0, Node 22).

**Rationale:**
- Consistent environment across team members.
- No host PHP/MySQL installation needed.
- Plugin and theme mounted as read-only volumes for live editing.
- WP-CLI and Node run on-demand via profiles.

---

## D-007: No Core Modifications, WC Hooks/Blocks Only

**Date:** 2026-08-08  
**Status:** Decided

**Context:** Modifying WordPress or WooCommerce core files creates upgrade hazards.

**Decision:** Never modify WP/WC core files. Extend via hooks, REST API, and WooCommerce Blocks only.

**Rationale:**
- Clean upgrades when WP/WC release new versions.
- No merge conflicts during updates.
- Leverage community-tested code paths.

---

## D-008: WCAG 2.2 AA Target

**Date:** 2026-08-08  
**Status:** Decided

**Context:** European accessibility regulations (EAA) take effect in 2025, requiring WCAG 2.2 AA compliance for e-commerce.

**Decision:** Design and build to WCAG 2.2 AA from the start. Mobile-first.

**Rationale:**
- Legal compliance for EU market.
- Better UX for all users.
- Retrofitting accessibility is 10x harder than building it in.
