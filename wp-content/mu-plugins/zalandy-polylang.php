<?php
/**
 * Zalandy — Polylang Integration
 *
 * Adds a compact EN | DE language switcher to the primary navigation menu.
 * Also registers strings for translation and ensures the correct locale
 * is used for each language.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Append language switcher to primary navigation menu.
 */
add_filter( 'wp_nav_menu_items', 'zalandy_polylang_menu_switcher', 10, 2 );
function zalandy_polylang_menu_switcher( $items, $args ) {
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return $items;
	}

	$theme_location = isset( $args->theme_location ) ? $args->theme_location : '';
	if ( 'primary' !== $theme_location && 'handheld' !== $theme_location ) {
		return $items;
	}

	$languages = pll_the_languages( array(
		'raw'           => 1,
		'hide_if_empty' => 0,
	) );

	if ( empty( $languages ) ) {
		return $items;
	}

	$switcher  = '<li class="menu-item polylang-switcher" style="list-style:none;display:inline-flex;gap:4px;align-items:center;margin-left:14px;padding-left:14px;border-left:1px solid #e5e5e5;">';
	$parts     = array();
	foreach ( $languages as $lang ) {
		$label = strtoupper( $lang['slug'] ); // "EN" / "DE"
		if ( $lang['current_lang'] ) {
			$parts[] = '<span style="font-weight:700;color:#FF6B00;font-size:13px;">' . esc_html( $label ) . '</span>';
		} else {
			$parts[] = '<a href="' . esc_url( $lang['url'] ) . '" style="color:#666;font-size:13px;text-decoration:none;" hreflang="' . esc_attr( $lang['locale'] ) . '">' . esc_html( $label ) . '</a>';
		}
	}
	$switcher .= implode( '<span style="color:#ddd;font-size:11px;">|</span>', $parts );
	$switcher .= '</li>';

	return $items . $switcher;
}

/**
 * Register strings for the Polylang string translation panel.
 */
add_action( 'init', 'zalandy_polylang_register_strings' );
function zalandy_polylang_register_strings() {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}
	pll_register_string( 'Zalandy', 'site_title', 'Zalandy', 'Site Identity', false );
	pll_register_string( 'Zalandy', 'site_tagline', 'Fine Jewelry & Contemporary Fashion', 'Site Identity', false );
	pll_register_string( 'Zalandy', 'shop_button', 'Shop Collection', 'Buttons', false );
	pll_register_string( 'Zalandy', 'subscribe_button', 'Subscribe', 'Buttons', false );
	pll_register_string( 'Zalandy', 'newsletter_heading', 'Join the Zalandy Circle', 'Newsletter', false );
}

/**
 * Add CSS for the switcher in the footer/header areas.
 */
add_action( 'wp_head', 'zalandy_polylang_switcher_css', 50 );
function zalandy_polylang_switcher_css() {
	echo '<style>
	.polylang-switcher a:hover { color: #FF6B00 !important; }
	/* Mobile: show switcher prominently */
	@media (max-width: 768px) {
		.polylang-switcher {
			border-left: none !important;
			margin-left: 0 !important;
			padding-left: 0 !important;
		}
	}
	</style>';
}
