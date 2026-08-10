<?php
/**
 * Append CSS overrides to force Zalandy orange brand color
 * Woostify generated CSS may still contain old blue/gold colors
 */

$override_css = '

/* ========================================
   Zalandy — Brand Color Overrides
   ======================================== */

/* Override Woostify default primary blue (#1346af) */
.onsale,
.pagination li .page-numbers.current,
.woocommerce-pagination li .page-numbers.current,
.tagcloud a:hover,
.price_slider_wrapper .ui-widget-header,
.price_slider_wrapper .ui-slider-handle,
.cart-sidebar-head .shop-cart-count,
.wishlist-item-count,
.shop-cart-count,
.woocommerce-message,
.woocommerce-info,
#scroll-to-top,
.woocommerce-store-notice,
.woostify-simple-subsbrice-form input[type="submit"]:hover,
.woostify-single-product-stock .woostify-single-product-stock-progress-bar,
.has-woostify-primary-background-color,
.loop-add-to-cart-on-image + .added_to_cart,
.woocommerce-cart-form__contents:not(.elementor-menu-cart__products) .actions .coupon [name="apply_coupon"],
.related .tns-controls button,
.up-sells .tns-controls button,
.woostify-product-recently-viewed-section .tns-controls button,
.button,
.woocommerce-widget-layered-nav-dropdown__submit,
.form-submit .submit,
.elementor-button-wrapper .elementor-button,
.has-woostify-contact-form input[type="submit"],
#secondary .widget a.button,
.product-loop-meta.no-transform .button,
.product-loop-meta.no-transform .added_to_cart,
[class*="elementor-kit"] .checkout-button,
.select2-container--default .select2-results__option--highlighted[aria-selected],
.select2-container--default .select2-results__option--highlighted[data-selected] {
    background-color: #FF6B00 !important;
}

/* Override Woostify default link blue */
a,
a:hover,
.review-information-link,
.review-information-link:hover,
.cart-sidebar-content .woocommerce-mini-cart__buttons a:not(.checkout),
.product-loop-meta .button,
.multi-step-checkout-button[data-action="back"],
.multi-step-checkout-button[data-action="back"] .woostify-svg-icon,
.woocommerce-thankyou-order-received,
.woostify-lightbox-button:hover,
.photoswipe-toggle-button:hover,
.woostify-simple-subsbrice-form:focus-within input[type="submit"],
.main-navigation a:hover,
.site-footer a:hover,
.zalandy-custom-footer a:hover {
    color: #FF6B00 !important;
}

/* Override Elementor global colors */
:root {
    --e-global-color-woostify_color_1: #FF6B00 !important;
    --e-global-color-woostify_color_6: #FF6B00 !important;
}

/* Border colors */
.woocommerce-thankyou-order-received,
.woostify-lightbox-button:hover,
.photoswipe-toggle-button:hover {
    border-color: #FF6B00 !important;
}
';

$existing = get_theme_mod('custom_css_post', '');
// Remove old override block if exists to avoid duplication
$existing = preg_replace('/\/\* =====+\n   Zalandy — Brand Color Overrides.*?\*\//s', '', $existing);
$existing = trim($existing);

set_theme_mod('custom_css_post', $existing . $override_css);
echo "Brand color CSS overrides appended\n";
echo "DONE\n";
