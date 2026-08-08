# Technical Debt — Phase 0

Non-blocking issues identified during Phase 0 review. These do not prevent
Phase 1 development and are tracked here for future resolution.

## Deferred to Phase 1

### 1. Product Filter Attribute Block
- **Issue**: `wp:woocommerce/product-filter-attribute` requires a stable
  `attributeId` that is not known at template creation time.
- **Current state**: Removed from `archive-product.html`. The block can be
  added dynamically in Phase 1 after WooCommerce attributes are registered.
- **Priority**: Medium

### 2. Single-Product Template — WC 11 Alignment
- **Issue**: `templates/single-product.html` should be verified against the
  official WooCommerce 11 default block template for completeness.
- **Current state**: Basic template exists and renders correctly. Full
  block-by-block comparison with WC 11 defaults is pending.
- **Priority**: Low

### 3. Automated Page Screenshots in CI
- **Issue**: CI verifies HTTP status codes for all pages but does not capture
  visual screenshots for regression testing.
- **Current state**: HTTP 200 checks for Home, Shop, Cart, Checkout,
  My-Account, and Product detail pages are in place.
- **Priority**: Low — consider Playwright or similar in Phase 2+.

### 4. PHPStan Level
- **Issue**: PHPStan is configured at level 6. Levels 7-9 could catch
  additional type safety issues.
- **Current state**: Level 6 passes. Can be raised incrementally in Phase 1+.
- **Priority**: Low

### 5. Payment Toggle Sanitization Logic
- **Issue**: `SettingsModule::sanitize_settings()` uses `isset()` for
  `stripe_enabled` and `paypal_enabled`. When the value is explicitly `false`,
  `isset()` returns `true`, so the sanitized value becomes `true` instead of
  `false`.
- **Current state**: Works for form checkbox semantics (absent = false,
  present = true) but is incorrect for REST API JSON input where `false` is
  explicitly passed.
- **Fix**: Use `filter_var($input['payment']['stripe_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN)`.
- **Priority**: Medium — should fix before Phase 1 settings UI.
