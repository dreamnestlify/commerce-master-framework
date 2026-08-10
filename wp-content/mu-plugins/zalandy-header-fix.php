<?php
/**
 * Zalandy Header Fix
 *
 * Woostify header-layout-1 outputs an empty .site-navigation div in some
 * configurations. This mu-plugin injects the primary menu and a visible
 * search box into the header if Woostify fails to render them.
 *
 * Uses wp_get_nav_menu_items() directly instead of wp_nav_menu() because
 * the latter returns false when called during wp_footer in some contexts.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build primary menu HTML from menu items directly.
 */
function zalandy_build_primary_menu_html() {
	// Get menu assigned to 'primary' location
	$locations = get_nav_menu_locations();
	$menu_id   = isset( $locations['primary'] ) ? (int) $locations['primary'] : 0;

	if ( ! $menu_id ) {
		// Fallback: find by name
		$menu = wp_get_nav_menu_object( 'Main Menu' );
		if ( $menu ) {
			$menu_id = $menu->term_id;
		}
	}

	if ( ! $menu_id ) {
		return '';
	}

	$items = wp_get_nav_menu_items( $menu_id );
	if ( empty( $items ) ) {
		return '';
	}

	// Build nested HTML
	$menu_html = '<nav class="main-navigation"><ul id="primary-menu" class="primary-navigation">';

	// Group items by parent
	$top_items   = array();
	$sub_items   = array();
	$has_submenu = array();

	foreach ( $items as $item ) {
		if ( (int) $item->menu_item_parent === 0 ) {
			$top_items[] = $item;
		} else {
			$sub_items[ (int) $item->menu_item_parent ][] = $item;
		}
	}

	foreach ( $top_items as $item ) {
		$has_subs = isset( $sub_items[ $item->ID ] ) && ! empty( $sub_items[ $item->ID ] );
		$class    = $has_subs ? 'menu-item menu-item-has-children' : 'menu-item';

		$menu_html .= '<li class="' . esc_attr( $class ) . '">';
		$menu_html .= '<a href="' . esc_url( $item->url ) . '">' . esc_html( $item->title ) . '</a>';

		if ( $has_subs ) {
			$menu_html .= '<ul class="sub-menu">';
			foreach ( $sub_items[ $item->ID ] as $sub ) {
				$menu_html .= '<li class="menu-item"><a href="' . esc_url( $sub->url ) . '">' . esc_html( $sub->title ) . '</a></li>';
			}
			$menu_html .= '</ul>';
		}

		$menu_html .= '</li>';
	}

	// Add Polylang language switcher
	if ( function_exists( 'pll_the_languages' ) ) {
		$languages = pll_the_languages( array( 'raw' => 1, 'hide_if_empty' => 0 ) );
		if ( ! empty( $languages ) ) {
			$menu_html .= '<li class="menu-item polylang-switcher">';
			$parts      = array();
			foreach ( $languages as $lang ) {
				$label = strtoupper( $lang['slug'] );
				if ( $lang['current_lang'] ) {
					$parts[] = '<span style="font-weight:700;color:#FF6B00;font-size:13px;">' . esc_html( $label ) . '</span>';
				} else {
					$parts[] = '<a href="' . esc_url( $lang['url'] ) . '" style="color:#666;font-size:13px;text-decoration:none;" hreflang="' . esc_attr( $lang['locale'] ) . '">' . esc_html( $label ) . '</a>';
				}
			}
			$menu_html .= implode( '<span style="color:#ddd;font-size:11px;">|</span>', $parts );
			$menu_html .= '</li>';
		}
	}

	$menu_html .= '</ul></nav>';

	return $menu_html;
}

/**
 * Inject primary navigation and search box via wp_footer.
 */
add_action( 'wp_footer', 'zalandy_inject_header_nav' );
function zalandy_inject_header_nav() {
	if ( is_admin() ) {
		return;
	}

	$menu_html = zalandy_build_primary_menu_html();

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
