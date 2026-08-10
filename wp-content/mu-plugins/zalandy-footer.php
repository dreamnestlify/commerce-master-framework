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
});
