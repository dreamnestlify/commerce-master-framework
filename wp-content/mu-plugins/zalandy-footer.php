<?php
/**
 * Zalandy - Custom Footer (replaces Woostify default footer)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Remove Woostify default footer template so we don't get duplicate links
add_action( 'wp_loaded', function() {
	remove_action( 'woostify_theme_footer', 'woostify_before_footer', 0 );
	remove_action( 'woostify_theme_footer', 'woostify_template_footer' );
	remove_action( 'woostify_theme_footer', 'woostify_after_footer', 100 );
}, 99 );

// Custom footer output with company info and legal links
add_action( 'wp_footer', function() {
	$footer_html = get_option( 'zalandy_custom_footer' );
	if ( ! $footer_html ) {
		return;
	}
	echo '<footer class="zalandy-custom-footer">' . $footer_html . '</footer>';
}, 100 );

// Also hide default Woostify site-info bar via CSS as a fallback
add_action( 'wp_head', function() {
	$css = get_option( 'zalandy_footer_css' );
	echo '<style>
	footer#colophon,
	footer#colophon .site-info,
	footer#colophon .woostify-footer-menu,
	footer#colophon .privacy-policy-link,
	footer#colophon .site-infor-col {
		display: none !important;
	}
	.zalandy-custom-footer {
		background: #1a1a1a;
		color: #fff;
		margin-top: 60px;
	}
	.zalandy-custom-footer a:hover { color: #FF6B00 !important; }
	.zalandy-custom-footer img { max-width: 200px; }
	' . ( $css ? $css : '' ) . '
	</style>';
}, 10 );

// Brand color overrides - load late so it wins over Woostify generated CSS
add_action( 'wp_head', function() {
	echo '<style>
	/* Zalandy brand color overrides */
	.onsale,
	.pagination li .page-numbers.current,
	.woocommerce-pagination li .page-numbers.current,
	.cart-sidebar-head .shop-cart-count,
	.wishlist-item-count,
	.shop-cart-count,
	.woocommerce-message,
	.woocommerce-info,
	#scroll-to-top,
	.woocommerce-store-notice,
	.woostify-single-product-stock .woostify-single-product-stock-progress-bar,
	.woocommerce-cart-form__contents:not(.elementor-menu-cart__products) .actions .coupon [name="apply_coupon"],
	.button:not(.single_add_to_cart_button),
	.woocommerce-widget-layered-nav-dropdown__submit,
	.form-submit .submit,
	#secondary .widget a.button,
	.product-loop-meta.no-transform .button,
	.product-loop-meta.no-transform .added_to_cart,
	[class*="elementor-kit"] .checkout-button,
	.select2-container--default .select2-results__option--highlighted[aria-selected],
	.select2-container--default .select2-results__option--highlighted[data-selected] {
		background-color: #FF6B00 !important;
	}
	.single_add_to_cart_button.button:not(.woostify-buy-now) {
		background-color: #FF6B00 !important;
		border-color: #FF6B00 !important;
	}
	.single_add_to_cart_button.button:not(.woostify-buy-now):hover {
		background-color: #E65F00 !important;
		border-color: #E65F00 !important;
	}
	a,
	a:hover,
	.main-navigation a:hover,
	.site-footer a:hover,
	.zalandy-custom-footer a:hover,
	.review-information-link,
	.review-information-link:hover,
	.cart-sidebar-content .woocommerce-mini-cart__buttons a:not(.checkout),
	.woostify-simple-subsbrice-form:focus-within input[type="submit"] {
		color: #FF6B00 !important;
	}
	.woocommerce-thankyou-order-received,
	.woostify-lightbox-button:hover,
	.photoswipe-toggle-button:hover {
		border-color: #FF6B00 !important;
	}
	:root {
		--e-global-color-woostify_color_1: #FF6B00 !important;
		--e-global-color-woostify_color_6: #FF6B00 !important;
	}
	</style>';
}, 999 );
