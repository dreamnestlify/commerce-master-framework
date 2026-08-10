<?php
/**
 * Zalandy Header Fix
 *
 * Woostify header-layout-1 outputs an empty .site-navigation div in some
 * configurations. This mu-plugin injects the primary menu (with Polylang
 * language switcher attached via wp_nav_menu_items filter) and a visible
 * search box into the header if Woostify fails to render them.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inject primary navigation and search box via wp_footer.
 * Uses wp_nav_menu() so all filters (including Polylang switcher) fire.
 */
add_action( 'wp_footer', 'zalandy_inject_header_nav' );
function zalandy_inject_header_nav() {
	if ( is_admin() ) {
		return;
	}

	// Build the menu HTML — this fires wp_nav_menu_items filter so Polylang
	// language switcher gets appended automatically.
	$menu_html = wp_nav_menu( array(
		'theme_location'  => 'primary',
		'menu_class'      => 'primary-navigation',
		'container'       => 'nav',
		'container_class' => 'main-navigation',
		'echo'            => false,
		'fallback_cb'     => false,
		'depth'           => 2,
	) );

	// Build search box HTML
	$search_html = '<div class="zalandy-header-search">'
		. '<form role="search" method="get" action="' . esc_url( home_url( '/' ) ) . '">'
		. '<input type="search" name="s" placeholder="Search products..." value="' . esc_attr( get_search_query() ) . '" />'
		. '<input type="hidden" name="post_type" value="product" />'
		. '<button type="submit"><svg width="16" height="16" viewBox="0 0 17 17" fill="currentColor"><path d="M16.604 15.868l-5.173-5.173c0.975-1.137 1.569-2.611 1.569-4.223 0-3.584-2.916-6.5-6.5-6.5-1.736 0-3.369 0.676-4.598 1.903-1.227 1.228-1.903 2.861-1.902 4.597 0 3.584 2.916 6.5 6.5 6.5 1.612 0 3.087-0.594 4.224-1.569l5.173 5.173 0.707-0.708zM6.5 11.972c-3.032 0-5.5-2.467-5.5-5.5-0.001-1.47 0.571-2.851 1.61-3.889 1.038-1.039 2.42-1.611 3.89-1.611 3.032 0 5.5 2.467 5.5 5.5 0 3.032-2.468 5.5-5.5 5.5z"/></svg></button>'
		. '</form>'
		. '</div>';

	$menu_json   = wp_json_encode( $menu_html );
	$search_json = wp_json_encode( $search_html );

	?>
	<script>
	document.addEventListener( "DOMContentLoaded", function() {
		// 1. Inject primary menu into empty .site-navigation
		var navContainer = document.querySelector( ".site-header .site-navigation" );
		if ( navContainer && navContainer.children.length === 0 ) {
			navContainer.innerHTML = <?php echo $menu_json; // phpcs:ignore ?>;
		}

		// 2. Inject search box before site-tools icons
		var siteTools = document.querySelector( ".site-header .site-tools" );
		if ( siteTools ) {
			var existingSearch = siteTools.querySelector( ".zalandy-header-search" );
			if ( ! existingSearch ) {
				var wrapper = document.createElement( "div" );
				wrapper.innerHTML = <?php echo $search_json; // phpcs:ignore ?>;
				siteTools.insertBefore( wrapper.firstChild, siteTools.firstChild );
			}
		}
	} );
	</script>
	<?php
}
