<?php
/**
 * Zalandy Admin UI Customization
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Locale handling: let Polylang manage frontend language if active.
// When Polylang is active it hooks into determine_locale to return the
// correct locale (en_US / de_DE) based on the requested URL/content.
// We only force en_US on the frontend when Polylang is NOT running, so the
// site never accidentally renders in zh_CN (admin stays zh_CN via user profile).
add_filter( 'determine_locale', 'zalandy_frontend_locale_fallback', 1000 );
function zalandy_frontend_locale_fallback( $locale ) {
	if ( is_admin() ) {
		return $locale;
	}
	// If Polylang is active, trust it to set the correct locale.
	if ( function_exists( 'pll_current_language' ) || function_exists( 'PLL' ) && PLL() ) {
		return $locale;
	}
	// Fallback: keep frontend English when Polylang is inactive.
	return 'en_US';
}

// Remove unwanted dashboard widgets
add_action( 'wp_dashboard_setup', function() {
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
} );

// Custom welcome panel
add_action( 'welcome_panel', function() {
	?>
    <div class="welcome-panel-content" style="padding: 40px 20px;">
		<div style="display: flex; align-items: center; gap: 16px; margin-bottom: 10px;">
			<img src="<?php echo esc_url( wp_get_attachment_image_url( get_option( 'zalandy_logo_dark_id' ), 'thumbnail' ) ); ?>" alt="Zalandy" style="height: 40px; width: auto;">
			<h2 style="font-family: 'Playfair Display', serif; font-size: 24px; margin: 0; color: #1a1a1a;">欢迎来到 Zalandy 管理后台</h2>
		</div>
		<p style="font-size: 14px; color: #666; max-width: 600px;">管理您的珠宝和时尚商店。添加商品、处理订单、自定义店铺外观。</p>
		<div style="display: flex; gap: 12px; margin-top: 16px; flex-wrap: wrap;">
			<a href="/wp-admin/post-new.php?post_type=product" class="button button-primary">添加商品</a>
			<a href="/wp-admin/admin.php?page=wc-orders" class="button">查看订单</a>
			<a href="/wp-admin/edit.php?post_type=product" class="button">全部商品</a>
			<a href="/wp-admin/admin.php?page=wc-settings" class="button">设置</a>
		</div>
	</div>
	<?php
} );

// Admin CSS
add_action( 'admin_head', function() {
	echo '<style>
	#wpadminbar { background: #1a1a1a; }
	#adminmenuback, #adminmenuwrap { background: #1a1a1a; }
	#adminmenu a { color: #ccc; font-size: 13px; }
	#adminmenu a:hover, #adminmenu .wp-has-current-submenu > a { color: #FF6B00; }
	#adminmenu .wp-menu-open > a { background: #FF6B00 !important; color: #fff !important; }
	#adminmenu .wp-has-current-submenu .wp-submenu-wrap { background: #111; }
	#adminmenu .wp-submenu a { color: #888; }
	#adminmenu .wp-submenu a:hover { color: #FF6B00; }
	.wp-core-ui .button-primary { background: #FF6B00; border-color: #E65F00; }
	.wp-core-ui .button-primary:hover { background: #E65F00; border-color: #D45500; }
	#dashboard-widgets .postbox { border: 1px solid #e5e5e5; border-radius: 8px; }
	#dashboard-widgets .postbox h2, #dashboard-widgets .postbox h3 { border-bottom: 2px solid #FF6B00; }
	.welcome-panel-content h2 { font-family: Playfair Display, serif; }
	#footer-upgrade { display: none; }
	#footer-thankyou { color: #FF6B00; }
	.login h1 a { background-image: none; }
	.login #nav a, .login #backtoblog a { color: #FF6B00; }
	</style>';
});

// Login page CSS
add_action( 'login_head', function() {
	$logo_url = wp_get_attachment_image_url( get_option( 'zalandy_logo_dark_id' ), 'medium' );
	?>
	<style>
	.login h1 a {
		background-image: url('<?php echo esc_url( $logo_url ); ?>');
		background-size: contain;
		background-repeat: no-repeat;
		background-position: center;
		width: 240px;
		height: 60px;
		text-indent: -9999px;
	}
	.login form { border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
	.login .button-primary { background: #FF6B00; border-color: #E65F00; }
	.login .button-primary:hover { background: #E65F00; }
	.login #nav a, .login #backtoblog a { color: #FF6B00; }
	</style>
	<?php
});

// Frontend header layout fixes
add_action( 'wp_head', function() {
	echo '<style>
	/* Zalandy header layout fixes */
	.site-header .site-branding,
	.site-header .site-logo {
		flex-shrink: 0;
	}
	.site-header .site-logo img,
	.site-header .custom-logo {
		max-height: 56px !important;
		width: auto !important;
	}
	.site-header .site-branding {
		margin-right: 20px;
	}
	.site-header .site-navigation {
		flex-grow: 1;
		display: flex !important;
		align-items: center;
		justify-content: center;
	}
	.site-header .main-navigation {
		display: block;
	}
	.site-header .primary-navigation {
		display: flex;
		align-items: center;
		gap: 4px;
		list-style: none;
		margin: 0;
		padding: 0;
	}
	.site-header .primary-navigation > li {
		position: relative;
		margin: 0 2px;
	}
	.site-header .primary-navigation > li > a {
		font-size: 13px;
		font-weight: 500;
		letter-spacing: 0.3px;
		text-transform: uppercase;
		padding: 10px 12px;
		white-space: nowrap;
		color: #2b2b2b;
		display: block;
	}
	.site-header .primary-navigation > li > a:hover {
		color: #FF6B00;
	}
	.site-header .primary-navigation .sub-menu {
		position: absolute;
		top: 100%;
		left: 0;
		min-width: 200px;
		background: #fff;
		box-shadow: 0 8px 30px rgba(0,0,0,0.12);
		border-radius: 6px;
		padding: 8px 0;
		list-style: none;
		opacity: 0;
		visibility: hidden;
		transform: translateY(10px);
		transition: all 0.2s;
		z-index: 999;
	}
	.site-header .primary-navigation > li:hover > .sub-menu {
		opacity: 1;
		visibility: visible;
		transform: translateY(0);
	}
	.site-header .primary-navigation .sub-menu a {
		font-size: 13px;
		padding: 8px 18px;
		white-space: nowrap;
		color: #2b2b2b;
		display: block;
	}
	.site-header .primary-navigation .sub-menu a:hover {
		color: #FF6B00;
		background: #f9f9f9;
	}
	/* Header search box */
	.zalandy-header-search {
		display: flex;
		align-items: center;
		margin-right: 6px;
	}
	.zalandy-header-search input[type="search"] {
		padding: 7px 14px;
		border: 1px solid #e5e5e5;
		border-radius: 20px;
		font-size: 13px;
		width: 170px;
		outline: none;
		transition: width 0.3s, border-color 0.3s;
		background: #f9f9f9;
	}
	.zalandy-header-search input[type="search"]:focus {
		width: 210px;
		border-color: #FF6B00;
		background: #fff;
	}
	.zalandy-header-search button {
		background: none;
		border: none;
		padding: 4px 6px;
		cursor: pointer;
		color: #666;
		display: flex;
		align-items: center;
	}
	.zalandy-header-search button:hover {
		color: #FF6B00;
	}
	/* Language switcher */
	.polylang-switcher {
		list-style: none !important;
		display: inline-flex !important;
		gap: 4px;
		align-items: center;
		margin-left: 10px !important;
		padding-left: 10px !important;
		border-left: 1px solid #e5e5e5;
	}
	/* Site tools layout */
	.site-header .site-tools {
		display: flex;
		align-items: center;
		gap: 10px;
		flex-shrink: 0;
	}
	@media (max-width: 1024px) {
		.site-header .primary-navigation > li > a {
			font-size: 12px;
			padding: 8px 8px;
		}
	}
	@media (max-width: 991px) {
		.site-header .site-navigation {
			display: none !important;
		}
		.zalandy-header-search {
			display: none;
		}
	}
	</style>';
}, 100 );

// Custom admin bar
add_action( 'admin_bar_menu', function( $wp_admin_bar ) {
	$wp_admin_bar->add_node( array(
		'id'     => 'zalandy-view-shop',
		'title'  => '查看店铺',
		'href'   => home_url( '/shop/' ),
		'parent' => 'site-name',
	) );
}, 50 );
