<?php
/**
 * Zalandy — Fix Footer Layout & Header Menu
 *
 * 1. Regenerates the custom footer HTML with robust horizontal-text styles.
 * 2. Restructures the Main Menu so the header does not wrap.
 * 3. Flushes rewrite rules and caches.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

WP_CLI::log( '========================================' );
WP_CLI::log( '  Zalandy — Fix Footer & Header Menu' );
WP_CLI::log( '========================================' );

// ═══════════════════════════════════════════════════════════════
// 1. Regenerate footer HTML with explicit horizontal styles
// ═══════════════════════════════════════════════════════════════
WP_CLI::log( '' );
WP_CLI::log( '1/3 — Regenerating custom footer HTML...' );

$dark_logo_id = get_option( 'zalandy_logo_dark_id' );
$logo_url     = $dark_logo_id ? wp_get_attachment_image_url( $dark_logo_id, 'medium' ) : '';
$brand_color  = get_option( 'zalandy_brand_color', '#FF6B00' );

$year = date( 'Y' );

$footer_html = '<div class="footer-container" style="max-width:1200px;margin:0 auto;padding:60px 20px 30px;">
  <div class="footer-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:40px;margin-bottom:40px;">
    <div>
      ' . ( $logo_url ? '<img src="' . esc_url( $logo_url ) . '" alt="Zalandy" style="height:36px;width:auto;margin-bottom:16px;display:block;">' : '<h3 style="font-family:Playfair Display,serif;font-size:24px;margin-bottom:16px;color:' . esc_attr( $brand_color ) . ';">Zalandy</h3>' ) . '
      <p style="color:#aaa;font-size:14px;line-height:1.7;">Fine jewelry & contemporary fashion. Handcrafted with passion in Germany.</p>
      <p style="color:#aaa;font-size:13px;margin-top:12px;line-height:1.7;">Equi international UG (haftungsbeschränkt)<br>Großenwede Siedlung 8<br>29640 Schneverdingen, Germany<br>VAT: DE312939176 | HRB: 206966</p>
      <div style="margin-top:16px;display:flex;gap:12px;flex-wrap:wrap;">
        <a href="#" style="color:#aaa;font-size:13px;text-decoration:none;white-space:nowrap;">Instagram</a>
        <a href="#" style="color:#aaa;font-size:13px;text-decoration:none;white-space:nowrap;">Facebook</a>
        <a href="#" style="color:#aaa;font-size:13px;text-decoration:none;white-space:nowrap;">TikTok</a>
        <a href="#" style="color:#aaa;font-size:13px;text-decoration:none;white-space:nowrap;">Pinterest</a>
      </div>
    </div>
    <div>
      <h4 style="font-size:14px;text-transform:uppercase;letter-spacing:1px;margin-bottom:20px;color:#fff;white-space:nowrap;">Shop</h4>
      <ul style="list-style:none;padding:0;margin:0;">
        <li style="margin-bottom:10px;"><a href="/shop/" style="color:#aaa;text-decoration:none;font-size:14px;white-space:nowrap;">All Products</a></li>
        <li style="margin-bottom:10px;"><a href="/product-category/jewelry/" style="color:#aaa;text-decoration:none;font-size:14px;white-space:nowrap;">Jewelry</a></li>
        <li style="margin-bottom:10px;"><a href="/product-category/fashion/" style="color:#aaa;text-decoration:none;font-size:14px;white-space:nowrap;">Fashion</a></li>
        <li style="margin-bottom:10px;"><a href="/product-category/accessories/" style="color:#aaa;text-decoration:none;font-size:14px;white-space:nowrap;">Accessories</a></li>
      </ul>
    </div>
    <div>
      <h4 style="font-size:14px;text-transform:uppercase;letter-spacing:1px;margin-bottom:20px;color:#fff;white-space:nowrap;">Help</h4>
      <ul style="list-style:none;padding:0;margin:0;">
        <li style="margin-bottom:10px;"><a href="/contact/" style="color:#aaa;text-decoration:none;font-size:14px;white-space:nowrap;">Contact Us</a></li>
        <li style="margin-bottom:10px;"><a href="/faq/" style="color:#aaa;text-decoration:none;font-size:14px;white-space:nowrap;">FAQ</a></li>
        <li style="margin-bottom:10px;"><a href="/size-guide/" style="color:#aaa;text-decoration:none;font-size:14px;white-space:nowrap;">Size Guide</a></li>
        <li style="margin-bottom:10px;"><a href="/shipping-policy/" style="color:#aaa;text-decoration:none;font-size:14px;white-space:nowrap;">Shipping</a></li>
        <li style="margin-bottom:10px;"><a href="/return-policy/" style="color:#aaa;text-decoration:none;font-size:14px;white-space:nowrap;">Returns</a></li>
      </ul>
    </div>
    <div>
      <h4 style="font-size:14px;text-transform:uppercase;letter-spacing:1px;margin-bottom:20px;color:#fff;white-space:nowrap;">Legal</h4>
      <ul style="list-style:none;padding:0;margin:0;">
        <li style="margin-bottom:10px;"><a href="/imprint/" style="color:#aaa;text-decoration:none;font-size:14px;white-space:nowrap;">Imprint</a></li>
        <li style="margin-bottom:10px;"><a href="/privacy-policy/" style="color:#aaa;text-decoration:none;font-size:14px;white-space:nowrap;">Privacy Policy</a></li>
        <li style="margin-bottom:10px;"><a href="/cookie-policy/" style="color:#aaa;text-decoration:none;font-size:14px;white-space:nowrap;">Cookie Policy</a></li>
        <li style="margin-bottom:10px;"><a href="/terms-and-conditions/" style="color:#aaa;text-decoration:none;font-size:14px;white-space:nowrap;">Terms & Conditions</a></li>
        <li style="margin-bottom:10px;"><a href="/withdrawal-right/" style="color:#aaa;text-decoration:none;font-size:14px;white-space:nowrap;">Withdrawal Right</a></li>
      </ul>
    </div>
  </div>
  <div style="border-top:1px solid #333;padding-top:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
    <p style="color:#888;font-size:13px;margin:0;">&copy; ' . $year . ' Zalandy. All rights reserved. Equi international UG</p>
    <p style="color:#888;font-size:13px;margin:0;">Designed with passion in Germany</p>
  </div>
</div>';

update_option( 'zalandy_custom_footer', $footer_html );
WP_CLI::log( '  ✅ Footer HTML regenerated' );

// ═══════════════════════════════════════════════════════════════
// 2. Restructure Main Menu to avoid wrapping
// ═══════════════════════════════════════════════════════════════
WP_CLI::log( '' );
WP_CLI::log( '2/3 — Restructuring Main Menu...' );

$menu_name = 'Main Menu';
$menu      = wp_get_nav_menu_object( $menu_name );

if ( $menu ) {
	wp_delete_nav_menu( $menu->term_id );
}

$menu_id = wp_create_nav_menu( $menu_name );

// Helper to add menu items.
function _zalandy_add_menu_item( $menu_id, $title, $url, $parent = 0 ) {
	return wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-title'     => $title,
			'menu-item-url'       => home_url( $url ),
			'menu-item-status'    => 'publish',
			'menu-item-parent-id' => $parent,
		)
	);
}

// Top-level items.
_zalandy_add_menu_item( $menu_id, 'Home', '/' );

// Shop dropdown.
$shop_id = _zalandy_add_menu_item( $menu_id, 'Shop', '/shop/' );
_zalandy_add_menu_item( $menu_id, 'Shop All', '/shop/', $shop_id );
_zalandy_add_menu_item( $menu_id, 'Earrings', '/product-category/earrings/', $shop_id );
_zalandy_add_menu_item( $menu_id, 'Necklaces', '/product-category/necklaces/', $shop_id );
_zalandy_add_menu_item( $menu_id, 'Bracelets', '/product-category/bracelets/', $shop_id );
_zalandy_add_menu_item( $menu_id, 'Rings', '/product-category/rings/', $shop_id );
_zalandy_add_menu_item( $menu_id, 'Jewelry Sets', '/product-category/jewelry-sets/', $shop_id );
_zalandy_add_menu_item( $menu_id, 'Fashion', '/product-category/fashion/', $shop_id );

_zalandy_add_menu_item( $menu_id, 'Size Guide', '/size-guide/' );
_zalandy_add_menu_item( $menu_id, 'Blog', '/blog/' );
_zalandy_add_menu_item( $menu_id, 'About', '/about-us/' );
_zalandy_add_menu_item( $menu_id, 'Contact', '/contact/' );

// Assign to theme locations.
$locations                  = get_theme_mod( 'nav_menu_locations' );
$locations                  = is_array( $locations ) ? $locations : array();
$locations['primary']       = $menu_id;
$locations['handheld']      = $menu_id;
$locations['mobile']        = $menu_id;
$locations['menu-primary']  = $menu_id;
$locations['menu-handheld'] = $menu_id;
set_theme_mod( 'nav_menu_locations', $locations );

WP_CLI::log( '  ✅ Main Menu restructured (Home / Shop dropdown / Size Guide / Blog / About / Contact)' );

// ═══════════════════════════════════════════════════════════════
// 3. Flush & Summary
// ═══════════════════════════════════════════════════════════════
WP_CLI::log( '' );
WP_CLI::log( '3/3 — Flushing rewrites & caches...' );

flush_rewrite_rules( true );
wp_cache_flush();

WP_CLI::log( '  ✅ Done' );
WP_CLI::log( '' );
WP_CLI::log( '========================================' );
WP_CLI::log( '  Footer & Header Menu Fixed' );
WP_CLI::log( '========================================' );
