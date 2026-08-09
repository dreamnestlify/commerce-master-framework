<?php
/**
 * Zalandy Admin UI Customization
 */
if ( ! defined( 'ABSPATH' ) ) exit;

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
		<h2 style="font-family: 'Playfair Display', serif; font-size: 24px; margin-bottom: 10px;">欢迎来到 Zalandy 管理后台</h2>
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
	#adminmenu a:hover, #adminmenu .wp-has-current-submenu > a { color: #c9a96e; }
	#adminmenu .wp-menu-open > a { background: #c9a96e !important; color: #fff !important; }
	#adminmenu .wp-has-current-submenu .wp-submenu-wrap { background: #111; }
	#adminmenu .wp-submenu a { color: #888; }
	#adminmenu .wp-submenu a:hover { color: #c9a96e; }
	.wp-core-ui .button-primary { background: #c9a96e; border-color: #b8975a; }
	.wp-core-ui .button-primary:hover { background: #b8975a; border-color: #a0874a; }
	#dashboard-widgets .postbox { border: 1px solid #e5e5e5; border-radius: 8px; }
	#dashboard-widgets .postbox h2, #dashboard-widgets .postbox h3 { border-bottom: 2px solid #c9a96e; }
	.welcome-panel-content h2 { font-family: Playfair Display, serif; }
	#footer-upgrade { display: none; }
	#footer-thankyou { color: #c9a96e; }
	.login h1 a { background-image: none; }
	.login #nav a, .login #backtoblog a { color: #c9a96e; }
	</style>';
});

// Login page CSS
add_action( 'login_head', function() {
	echo '<style>
	.login h1 a { background-image: none; text-indent: 0; width: auto; height: auto; font-family: Playfair Display, serif; font-size: 32px; color: #c9a96e; font-weight: 700; }
	.login form { border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
	.login .button-primary { background: #c9a96e; border-color: #b8975a; }
	.login .button-primary:hover { background: #b8975a; }
	</style>';
});

// Custom admin bar
add_action( 'admin_bar_menu', function( $wp_admin_bar ) {
	$wp_admin_bar->add_node( array(
		'id'     => 'zalandy-view-shop',
		'title'  => '查看店铺',
		'href'   => home_url( '/shop/' ),
		'parent' => 'site-name',
	) );
}, 50 );
