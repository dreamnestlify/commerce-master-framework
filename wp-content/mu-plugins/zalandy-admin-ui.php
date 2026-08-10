<?php
/**
 * Zalandy Admin UI Customization
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Force frontend to English, while admin/dashboard can remain Chinese
add_filter( 'locale', 'zalandy_frontend_english_locale', 999 );
add_filter( 'determine_locale', 'zalandy_frontend_english_locale', 999 );
function zalandy_frontend_english_locale( $locale ) {
	if ( ! is_admin() ) {
		return 'en_US';
	}
	return $locale;
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
		max-height: 44px;
		width: auto;
	}
	.site-header .main-navigation {
		flex: 1;
		justify-content: center;
	}
	.site-header .main-navigation .menu {
		display: flex;
		align-items: center;
		gap: 6px;
		flex-wrap: nowrap;
	}
	.site-header .main-navigation .menu > li {
		position: relative;
		margin: 0 4px;
	}
	.site-header .main-navigation .menu > li > a {
		font-size: 13px;
		font-weight: 500;
		letter-spacing: 0.3px;
		text-transform: uppercase;
		padding: 10px 12px;
		white-space: nowrap;
	}
	.site-header .main-navigation .sub-menu {
		min-width: 200px;
		background: #fff;
		box-shadow: 0 8px 30px rgba(0,0,0,0.12);
		border-radius: 6px;
		padding: 10px 0;
	}
	.site-header .main-navigation .sub-menu a {
		font-size: 13px;
		padding: 8px 18px;
		white-space: nowrap;
	}
	.site-header .header-action {
		display: flex;
		align-items: center;
		gap: 14px;
		flex-shrink: 0;
	}
	@media (max-width: 1024px) {
		.site-header .main-navigation .menu > li > a {
			font-size: 12px;
			padding: 8px 8px;
		}
	}
	@media (max-width: 768px) {
		.site-header .main-navigation {
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
