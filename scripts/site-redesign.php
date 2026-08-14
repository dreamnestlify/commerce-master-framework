<?php
/**
 * Site Redesign — Frontend + Admin UI
 * Rebuilds homepage, adds custom CSS, optimizes admin
 * Run via: wp eval 'require "/tmp/site-redesign.php";' --allow-root
 */

WP_CLI::log( '========================================' );
WP_CLI::log( '  Zalandy — Site Redesign' );
WP_CLI::log( '========================================' );

// ─── 1. Update Site Identity ────────────────────────────────────
WP_CLI::log( "\n--- Updating Site Identity ---" );
update_option( 'blogname', 'Zalandy' );
update_option( 'blogdescription', 'Fine Jewelry & Contemporary Fashion' );

// ─── 2. Woostify Customizer Settings ────────────────────────────
WP_CLI::log( "\n--- Configuring Woostify Theme ---" );

// Set theme colors
set_theme_mod( 'woostify_color_primary', '#c9a96e' );
set_theme_mod( 'woostify_text_color', '#333333' );
set_theme_mod( 'woostify_link_color', '#c9a96e' );
set_theme_mod( 'woostify_link_hover_color', '#b8975a' );
set_theme_mod( 'woostify_heading_color', '#1a1a1a' );

// Layout
set_theme_mod( 'woostify_content_layout', 'contained' );
set_theme_mod( 'woostify_container_width', '1280' );

// Header settings
set_theme_mod( 'woostify_header_layout', 'layout-1' );
set_theme_mod( 'woostify_header_background', '#ffffff' );
set_theme_mod( 'woostify_header_text_color', '#1a1a1a' );
set_theme_mod( 'woostify_header_sticky', '1' );

// Button style
set_theme_mod( 'woostify_button_background', '#c9a96e' );
set_theme_mod( 'woostify_button_text_color', '#ffffff' );
set_theme_mod( 'woostify_button_hover_background', '#b8975a' );
set_theme_mod( 'woostify_button_border_radius', '2' );
set_theme_mod( 'woostify_button_padding_top', '14' );
set_theme_mod( 'woostify_button_padding_bottom', '14' );
set_theme_mod( 'woostify_button_padding_left', '32' );
set_theme_mod( 'woostify_button_padding_right', '32' );

// Product card
set_theme_mod( 'woostify_product_card_border_radius', '4' );
set_theme_mod( 'woostify_product_card_box_shadow', '0 2px 12px rgba(0,0,0,0.06)' );
set_theme_mod( 'woostify_product_add_to_cart_button_position', 'after-short-description' );

// Footer
set_theme_mod( 'woostify_footer_background_color', '#1a1a1a' );
set_theme_mod( 'woostify_footer_text_color', '#cccccc' );
set_theme_mod( 'woostify_footer_link_color', '#c9a96e' );
set_theme_mod( 'woostify_footer_heading_color', '#ffffff' );

// Typography
set_theme_mod( 'woostify_body_font_family', 'Inter' );
set_theme_mod( 'woostify_heading_font_family', 'Playfair Display' );
set_theme_mod( 'woostify_body_font_size', '15' );
set_theme_mod( 'woostify_h1_font_size', '42' );
set_theme_mod( 'woostify_h2_font_size', '32' );
set_theme_mod( 'woostify_h3_font_size', '24' );

// WooCommerce
set_theme_mod( 'woostify_shop_product_columns', '4' );
set_theme_mod( 'woostify_shop_product_rows', '4' );

WP_CLI::log( "  Theme settings updated" );

// ─── 3. Build Homepage ──────────────────────────────────────────
WP_CLI::log( "\n--- Rebuilding Homepage ---" );

$home_content = '<!-- wp:group {"align":"full","className":"zalandy-hero","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull zalandy-hero">

<!-- wp:html -->
<div class="hero-section">
<div class="hero-content">
<p class="hero-eyebrow">New Collection 2026</p>
<h1 class="hero-title">Where Jewelry Meets<br>Fashion</h1>
<p class="hero-subtitle">Handcrafted fine jewelry and contemporary fashion.<br>Curated for those who define their own style.</p>
<div class="hero-buttons">
<a href="/shop/" class="hero-btn-primary">Shop Collection</a>
<a href="/fashion/" class="hero-btn-secondary">Explore Fashion</a>
</div>
</div>
</div>
<style>
.hero-section{position:relative;width:100%;height:560px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#f8f5f0 0%,#ede6db 50%,#f8f5f0 100%);overflow:hidden}
.hero-section::before{content:"";position:absolute;top:0;left:0;right:0;bottom:0;background:radial-gradient(circle at 30% 50%,rgba(201,169,110,0.08) 0%,transparent 60%);pointer-events:none}
.hero-content{text-align:center;z-index:1;max-width:720px;padding:0 20px}
.hero-eyebrow{font-family:"Playfair Display",serif;font-size:14px;letter-spacing:3px;text-transform:uppercase;color:#c9a96e;margin-bottom:16px}
.hero-title{font-family:"Playfair Display",serif;font-size:56px;font-weight:700;color:#1a1a1a;line-height:1.2;margin:0 0 20px}
.hero-subtitle{font-size:17px;color:#555;line-height:1.7;margin:0 0 36px}
.hero-buttons{display:flex;gap:16px;justify-content:center;flex-wrap:wrap}
.hero-btn-primary{display:inline-block;padding:14px 40px;background:#c9a96e;color:#fff;text-decoration:none;font-size:15px;font-weight:600;letter-spacing:1px;border:2px solid #c9a96e;transition:all .3s ease}
.hero-btn-primary:hover{background:#b8975a;border-color:#b8975a;color:#fff}
.hero-btn-secondary{display:inline-block;padding:14px 40px;background:transparent;color:#1a1a1a;text-decoration:none;font-size:15px;font-weight:600;letter-spacing:1px;border:2px solid #1a1a1a;transition:all .3s ease}
.hero-btn-secondary:hover{background:#1a1a1a;color:#fff}
@media(max-width:768px){.hero-section{height:420px}.hero-title{font-size:36px}.hero-subtitle{font-size:15px}}
</style>
<!-- /wp:html -->

</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"60px","bottom":"60px"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull" style="padding-top:60px;padding-bottom:60px">

<!-- wp:heading {"textAlign":"center","className":"section-heading"} -->
<h2 class="wp-block-heading has-text-align-center section-heading">Shop by Category</h2>
<!-- /wp:heading -->

<!-- wp:html -->
<div class="category-grid">
<a href="/product-category/earrings/" class="cat-card">
<div class="cat-icon">💎</div>
<span class="cat-name">Earrings</span>
<span class="cat-count">4 Products</span>
</a>
<a href="/product-category/necklaces/" class="cat-card">
<div class="cat-icon">📿</div>
<span class="cat-name">Necklaces</span>
<span class="cat-count">4 Products</span>
</a>
<a href="/product-category/bracelets/" class="cat-card">
<div class="cat-icon">⌚</div>
<span class="cat-name">Bracelets</span>
<span class="cat-count">3 Products</span>
</a>
<a href="/product-category/rings/" class="cat-card">
<div class="cat-icon">💍</div>
<span class="cat-name">Rings</span>
<span class="cat-count">4 Products</span>
</a>
<a href="/product-category/womens-clothing/" class="cat-card">
<div class="cat-icon">👗</div>
<span class="cat-name">Women</span>
<span class="cat-count">6 Products</span>
</a>
<a href="/product-category/mens-clothing/" class="cat-card">
<div class="cat-icon">👔</div>
<span class="cat-name">Men</span>
<span class="cat-count">5 Products</span>
</a>
</div>
<style>
.category-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:20px;max-width:1200px;margin:32px auto;padding:0 20px}
.cat-card{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:36px 16px;background:#faf8f5;border:1px solid #eee;border-radius:8px;text-decoration:none;transition:all .3s ease;text-align:center}
.cat-card:hover{background:#fff;border-color:#c9a96e;box-shadow:0 4px 20px rgba(201,169,110,0.15);transform:translateY(-4px)}
.cat-icon{font-size:40px;margin-bottom:12px}
.cat-name{font-family:"Playfair Display",serif;font-size:18px;font-weight:600;color:#1a1a1a;margin-bottom:4px}
.cat-count{font-size:13px;color:#999}
@media(max-width:1024px){.category-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:600px){.category-grid{grid-template-columns:repeat(2,1fr)}}
</style>
<!-- /wp:html -->

</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"color":{"background":"#f8f5f0"},"spacing":{"padding":{"top":"60px","bottom":"60px"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull has-background" style="background-color:#f8f5f0;padding-top:60px;padding-bottom:60px">

<!-- wp:heading {"textAlign":"center","className":"section-heading"} -->
<h2 class="wp-block-heading has-text-align-center section-heading">Featured Jewelry</h2>
<!-- /wp:heading -->

<!-- wp:html -->
<p style="text-align:center;color:#666;font-size:16px;margin-bottom:40px;max-width:600px;margin-left:auto;margin-right:auto">Handcrafted pieces featuring genuine gemstones, each one a unique work of art designed to be treasured for generations.</p>
<!-- /wp:html -->

<!-- wp:shortcode -->
[products limit="8" columns="4" orderby="date" order="DESC" category="earrings,necklaces,bracelets,rings" visibility="visible"]
<!-- /wp:shortcode -->

</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"60px","bottom":"60px"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull" style="padding-top:60px;padding-bottom:60px">

<!-- wp:heading {"textAlign":"center","className":"section-heading"} -->
<h2 class="wp-block-heading has-text-align-center section-heading">New Fashion Arrivals</h2>
<!-- /wp:heading -->

<!-- wp:html -->
<p style="text-align:center;color:#666;font-size:16px;margin-bottom:40px;max-width:600px;margin-left:auto;margin-right:auto">Contemporary clothing and accessories for every occasion. From everyday essentials to statement pieces.</p>
<!-- /wp:html -->

<!-- wp:shortcode -->
[products limit="8" columns="4" orderby="date" order="DESC" category="womens-clothing,mens-clothing,fashion-accessories" visibility="visible"]
<!-- /wp:shortcode -->

</div>
<!-- /wp:group -->

<!-- wp:html -->
<div class="brand-story-section">
<div class="brand-story-inner">
<div class="brand-story-text">
<h2>The Zalandy Story</h2>
<p>Founded with a passion for fine craftsmanship, Zalandy brings together handcrafted jewelry and contemporary fashion under one roof. Every piece tells a story of artistry, quality, and timeless elegance.</p>
<p>From our atelier in Germany, we source the finest materials and partner with skilled artisans to create pieces that celebrate individuality. Whether it is a gemstone ring that catches the light or a perfectly tailored blazer, we believe in the power of thoughtful design.</p>
<a href="/about-us/" class="brand-story-link">Discover Our Journey →</a>
</div>
<div class="brand-story-stats">
<div class="stat-item"><span class="stat-num">16+</span><span class="stat-label">Jewelry Pieces</span></div>
<div class="stat-item"><span class="stat-num">15+</span><span class="stat-label">Fashion Items</span></div>
<div class="stat-item"><span class="stat-num">100%</span><span class="stat-label">Quality Promise</span></div>
<div class="stat-item"><span class="stat-num">14-Day</span><span class="stat-label">EU Returns</span></div>
</div>
</div>
</div>
<style>
.brand-story-section{background:#1a1a1a;padding:80px 20px;margin:0}
.brand-story-inner{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center}
.brand-story-text h2{font-family:"Playfair Display",serif;font-size:36px;color:#fff;margin-bottom:20px}
.brand-story-text p{color:#aaa;font-size:16px;line-height:1.8;margin-bottom:16px}
.brand-story-link{color:#c9a96e;font-size:15px;font-weight:600;text-decoration:none;display:inline-block;margin-top:12px;transition:color .3s}
.brand-story-link:hover{color:#e0c180}
.brand-story-stats{display:grid;grid-template-columns:1fr 1fr;gap:24px}
.stat-item{display:flex;flex-direction:column;align-items:center;padding:24px;background:rgba(255,255,255,0.04);border:1px solid rgba(201,169,110,0.2);border-radius:8px}
.stat-num{font-family:"Playfair Display",serif;font-size:36px;color:#c9a96e;font-weight:700}
.stat-label{color:#888;font-size:13px;margin-top:4px}
@media(max-width:768px){.brand-story-inner{grid-template-columns:1fr;gap:40px}.brand-story-stats{grid-template-columns:1fr 1fr}}
</style>
<!-- /wp:html -->

<!-- wp:group {"align":"full","style":{"color":{"background":"#f8f5f0"},"spacing":{"padding":{"top":"50px","bottom":"50px"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull has-background" style="background-color:#f8f5f0;padding-top:50px;padding-bottom:50px">

<!-- wp:html -->
<div class="trust-badges">
<div class="trust-item">
<span class="trust-icon">🚚</span>
<span class="trust-title">Free Shipping</span>
<span class="trust-desc">On orders over $99</span>
</div>
<div class="trust-item">
<span class="trust-icon">↩️</span>
<span class="trust-title">14-Day Returns</span>
<span class="trust-desc">EU withdrawal right</span>
</div>
<div class="trust-item">
<span class="trust-icon">🔒</span>
<span class="trust-title">Secure Payment</span>
<span class="trust-desc">SSL encrypted checkout</span>
</div>
<div class="trust-item">
<span class="trust-icon">✨</span>
<span class="trust-title">Quality Promise</span>
<span class="trust-desc">Premium materials</span>
</div>
</div>
<style>
.trust-badges{display:flex;justify-content:center;gap:48px;flex-wrap:wrap;max-width:1100px;margin:0 auto;padding:0 20px}
.trust-item{display:flex;flex-direction:column;align-items:center;text-align:center;gap:4px}
.trust-icon{font-size:36px;margin-bottom:4px}
.trust-title{font-family:"Playfair Display",serif;font-size:16px;font-weight:600;color:#1a1a1a}
.trust-desc{font-size:13px;color:#888}
</style>
<!-- /wp:html -->

</div>
<!-- /wp:group -->

<!-- wp:html -->
<div class="newsletter-section">
<div class="newsletter-inner">
<h2>Join the Zalandy Circle</h2>
<p>Be the first to know about new collections, exclusive offers, and styling tips.</p>
<form class="newsletter-form" onsubmit="return false">
<input type="email" placeholder="Enter your email address" class="newsletter-input" />
<button type="submit" class="newsletter-btn">Subscribe</button>
</form>
<p class="newsletter-note">By subscribing, you agree to our <a href="/privacy-policy/">Privacy Policy</a>. Unsubscribe anytime.</p>
</div>
</div>
<style>
.newsletter-section{background:linear-gradient(135deg,#c9a96e 0%,#b8975a 100%);padding:70px 20px;text-align:center}
.newsletter-inner{max-width:600px;margin:0 auto}
.newsletter-inner h2{font-family:"Playfair Display",serif;font-size:36px;color:#fff;margin-bottom:12px}
.newsletter-inner p{color:rgba(255,255,255,0.85);font-size:16px;margin-bottom:28px}
.newsletter-form{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
.newsletter-input{padding:14px 20px;border:none;border-radius:4px;font-size:15px;width:320px;max-width:100%;outline:none}
.newsletter-btn{padding:14px 32px;background:#1a1a1a;color:#fff;border:none;border-radius:4px;font-size:15px;font-weight:600;cursor:pointer;transition:background .3s}
.newsletter-btn:hover{background:#333}
.newsletter-note{font-size:12px;color:rgba(255,255,255,0.7);margin-top:16px}
.newsletter-note a{color:#fff;text-decoration:underline}
</style>
<!-- /wp:html -->';

// Find and update the home page
$home_page = get_page_by_path( 'zalandy-home' );
if ( ! $home_page ) {
	$home_page = get_page_by_path( 'home' );
}
if ( ! $home_page ) {
	// Create it
	$home_page_id = wp_insert_post( array(
		'post_title'   => 'Zalandy Home',
		'post_name'    => 'zalandy-home',
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_content' => $home_content,
	) );
} else {
	$home_page_id = $home_page->ID;
	wp_update_post( array(
		'ID'           => $home_page_id,
		'post_content' => $home_content,
		'post_status'  => 'publish',
	) );
}

// Set as front page
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home_page_id );

WP_CLI::log( "  Homepage updated (ID: $home_page_id)" );

// ─── 4. Add Custom CSS ──────────────────────────────────────────
WP_CLI::log( "\n--- Adding Custom CSS ---" );

$custom_css = '
/* ========================================
   Zalandy — Global Custom Styles
   ======================================== */

/* Typography Enhancement */
body {
	font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
	font-size: 15px;
	line-height: 1.7;
	color: #333;
}

h1, h2, h3, h4, h5, h6 {
	font-family: "Playfair Display", Georgia, serif;
	font-weight: 700;
	color: #1a1a1a;
}

.section-heading {
	font-size: 32px !important;
	margin-bottom: 8px !important;
	position: relative;
	padding-bottom: 16px;
}

.section-heading::after {
	content: "";
	position: absolute;
	bottom: 0;
	left: 50%;
	transform: translateX(-50%);
	width: 60px;
	height: 3px;
	background: #c9a96e;
}

/* Header Enhancement */
.site-header {
	box-shadow: 0 2px 12px rgba(0,0,0,0.04);
	transition: all 0.3s ease;
}

.site-header.sticky {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	z-index: 999;
	background: rgba(255,255,255,0.98);
	backdrop-filter: blur(10px);
}

.main-navigation a {
	font-size: 14px;
	font-weight: 500;
	letter-spacing: 0.5px;
	text-transform: uppercase;
	transition: color 0.3s ease;
}

.main-navigation a:hover {
	color: #c9a96e !important;
}

/* Logo */
.site-logo img, .custom-logo {
	max-height: 50px;
	width: auto;
}

/* Product Card Enhancement */
.woocommerce ul.products li.product {
	background: #fff;
	border: 1px solid #f0f0f0;
	border-radius: 8px;
	padding: 20px;
	margin: 0 10px 30px;
	text-align: center;
	transition: all 0.3s ease;
	overflow: hidden;
}

.woocommerce ul.products li.product:hover {
	border-color: #e0d8cc;
	box-shadow: 0 8px 30px rgba(201,169,110,0.12);
	transform: translateY(-4px);
}

.woocommerce ul.products li.product .woocommerce-loop-product__title {
	font-family: "Playfair Display", serif;
	font-size: 16px !important;
	font-weight: 600;
	color: #1a1a1a;
	padding: 12px 0 8px;
}

.woocommerce ul.products li.product .price {
	color: #c9a96e !important;
	font-size: 18px !important;
	font-weight: 600;
}

.woocommerce ul.products li.product .price del {
	color: #999 !important;
	font-size: 14px !important;
}

.woocommerce ul.products li.product img {
	border-radius: 6px;
	transition: transform 0.5s ease;
}

.woocommerce ul.products li.product:hover img {
	transform: scale(1.05);
}

.woocommerce ul.products li.product .button {
	background: #c9a96e !important;
	color: #fff !important;
	border-radius: 4px;
	font-size: 13px !important;
	font-weight: 600;
	padding: 10px 24px !important;
	text-transform: uppercase;
	letter-spacing: 1px;
	transition: all 0.3s ease;
}

.woocommerce ul.products li.product .button:hover {
	background: #b8975a !important;
}

/* Single Product Page */
.single-product .product .summary > h1 {
	font-family: "Playfair Display", serif;
	font-size: 28px;
	margin-bottom: 16px;
}

.single-product .product .price .amount {
	color: #c9a96e;
	font-size: 28px;
	font-weight: 700;
}

.single-product .product form.cart .button {
	background: #c9a96e;
	color: #fff;
	border-radius: 4px;
	padding: 14px 40px;
	font-size: 15px;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 1px;
}

.single-product .product form.cart .button:hover {
	background: #b8975a;
}

/* Cart & Checkout */
.woocommerce-cart .cart-collaterals .cart_totals h2,
.woocommerce-checkout h3 {
	font-family: "Playfair Display", serif;
}

.woocommerce-cart-form .cart .button,
.woocommerce-cart-form .cart input.button,
.checkout-button {
	background: #c9a96e !important;
	color: #fff !important;
	border-radius: 4px !important;
	font-weight: 600;
}

.woocommerce-cart-form .cart .button:hover,
.checkout-button:hover {
	background: #b8975a !important;
}

/* Breadcrumb */
.woocommerce-breadcrumb {
	font-size: 13px;
	color: #999;
	margin-bottom: 20px;
}

.woocommerce-breadcrumb a {
	color: #c9a96e;
}

/* Footer Enhancement */
.site-footer {
	background: #1a1a1a !important;
	color: #aaa !important;
	padding: 60px 0 20px !important;
}

.site-footer h1, .site-footer h2, .site-footer h3, .site-footer h4 {
	color: #fff !important;
	font-family: "Playfair Display", serif;
}

.site-footer a {
	color: #c9a96e !important;
}

.site-footer a:hover {
	color: #e0c180 !important;
}

.footer-widgets {
	border-bottom: 1px solid rgba(255,255,255,0.08);
	padding-bottom: 30px;
	margin-bottom: 30px;
}

/* Pagination */
.woocommerce nav.woocommerce-pagination ul li a,
.woocommerce nav.woocommerce-pagination ul li span {
	color: #555;
	border: 1px solid #eee;
	padding: 10px 16px;
}

.woocommerce nav.woocommerce-pagination ul li .current {
	background: #c9a96e;
	color: #fff;
	border-color: #c9a96e;
}

.woocommerce nav.woocommerce-pagination ul li a:hover {
	background: #f8f5f0;
	color: #c9a96e;
}

/* Badge */
.woocommerce span.onsale {
	background: #c9a96e;
	border-radius: 4px;
	font-size: 12px;
	font-weight: 600;
	padding: 4px 12px;
	min-height: auto;
	line-height: 1.5;
}

/* Shop Header */
.woocommerce-products-header h1 {
	font-family: "Playfair Display", serif;
	font-size: 36px;
	text-align: center;
	margin-bottom: 30px;
}

/* Responsive */
@media (max-width: 768px) {
	.woocommerce ul.products li.product {
		margin: 0 0 24px;
	}
	
	.site-header {
		position: sticky;
		top: 0;
		z-index: 999;
	}
}
';

// Save custom CSS to Additional CSS
update_option( 'custom_css_post_id', 0 );
$wp_custom_css = wp_get_custom_css();
// Use customizer additional CSS
set_theme_mod( 'custom_css_post', $custom_css );

// Also save as a post in custom_css custom post type
$existing_css = get_page_by_title( 'zalandy-custom-css', OBJECT, 'custom_css' );
if ( $existing_css ) {
	wp_update_post( array(
		'ID'           => $existing_css->ID,
		'post_content' => $custom_css,
	) );
} else {
	wp_insert_post( array(
		'post_title'   => 'zalandy-custom-css',
		'post_name'    => 'zalandy-custom-css',
		'post_content' => $custom_css,
		'post_status'  => 'publish',
		'post_type'    => 'custom_css',
	) );
}

WP_CLI::log( "  Custom CSS added" );

// ─── 5. WooCommerce Settings ────────────────────────────────────
WP_CLI::log( "\n--- WooCommerce Settings ---" );

update_option( 'woocommerce_shop_page_display', 'subcategories' );
update_option( 'woocommerce_category_archive_display', 'both' );
update_option( 'woocommerce_default_catalog_orderby', 'date-desc' );
update_option( 'woocommerce_shop_columns', 4 );
update_option( 'woocommerce_shop_rows', 12 );
update_option( 'woocommerce_catalog_columns', 4 );
update_option( 'woocommerce_catalog_rows', 12 );

// Enable AJAX add to cart
update_option( 'woocommerce_enable_ajax_add_to_cart', 'yes' );
update_option( 'woocommerce_cart_redirect_after_add', 'no' );

// Product images
update_option( 'woocommerce_single_image_width', '700' );
update_option( 'woocommerce_thumbnail_image_width', '450' );

// Enable product gallery zoom
update_option( 'woocommerce_zoom_enable', 'yes' );
update_option( 'woocommerce_single_product_archive_layout', 'gallery-first' );

WP_CLI::log( "  WooCommerce settings updated" );

// ─── 6. Admin UI Customization ──────────────────────────────────
WP_CLI::log( "\n--- Customizing Admin UI ---" );

// Remove default dashboard widgets
function _zalandy_remove_dashboard_widgets() {
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
	remove_meta_box( 'wp_mail_smtp_reports_widget', 'dashboard', 'normal' );
}
add_action( 'admin_init', '_zalandy_remove_dashboard_widgets' );

// Custom admin CSS
$admin_css = '
/* Admin UI Enhancement */
#wpadminbar { background: #1a1a1a; }

#adminmenuback, #adminmenuwrap {
	background: #1a1a1a;
}

#adminmenu a {
	color: #ccc;
	font-size: 13px;
}

#adminmenu a:hover, #adminmenu .wp-has-current-submenu > a {
	color: #c9a96e;
}

#adminmenu .wp-has-current-submenu .wp-submenu-wrap {
	background: #111;
}

#adminmenu .wp-submenu a {
	color: #888;
}

#adminmenu .wp-submenu a:hover {
	color: #c9a96e;
}

/* Welcome Panel */
.welcome-panel-content h2 {
	font-family: "Playfair Display", serif;
}

/* Dashboard */
.wp-dashboard .postbox h3, .wp-dashboard .postbox h2 {
	font-family: "Playfair Display", serif;
	border-bottom: 2px solid #c9a96e;
}

/* Product list table */
.wp-list-table .column-product_cat { width: 15%; }
.wp-list-table .column-price { width: 10%; }

/* Admin bar logo */
#wpadminbar #wp-admin-bar-site-name > a {
	color: #c9a96e;
}

/* Admin footer */
#footer-upgrade {
	display: none;
}

#footer-thankyou {
	color: #c9a96e;
}

/* WooCommerce admin */
.woocommerce_page_wc-admin .woocommerce-embed-page,
.woocommerce_page_wc-orders .woocommerce-embed-page {
	--wp-admin-theme-color: #c9a96e;
	--wp-admin-theme-color-darker-10: #b8975a;
}

/* Buttons */
.wp-core-ui .button-primary {
	background: #c9a96e;
	border-color: #b8975a;
}

.wp-core-ui .button-primary:hover {
	background: #b8975a;
	border-color: #a0874a;
}

/* Admin menu active items */
#adminmenu .wp-menu-open > a {
	background: #c9a96e !important;
	color: #fff !important;
}

#adminmenu li.current a {
	color: #c9a96e !important;
}

/* Cleaner dashboard */
#dashboard-widgets .postbox {
	border: 1px solid #e5e5e5;
	border-radius: 8px;
}

#dashboard-widgets .postbox h2, #dashboard-widgets .postbox h3 {
	border-bottom: 2px solid #c9a96e;
	padding: 12px 12px;
}

/* Login page */
.login h1 a {
	background-image: none;
}

.login #nav a, .login #backtoblog a {
	color: #c9a96e;
}
';

// Save admin CSS via mu-plugin
$admin_mu = '<?php
/**
 * Zalandy Admin UI Customization
 * Auto-generated by site-redesign.php
 */
if ( ! defined( "ABSPATH" ) ) exit;

// Remove unwanted dashboard widgets
add_action( "wp_dashboard_setup", function() {
	remove_meta_box( "dashboard_quick_press", "dashboard", "side" );
	remove_meta_box( "dashboard_primary", "dashboard", "side" );
	remove_meta_box( "dashboard_secondary", "dashboard", "side" );
	remove_meta_box( "dashboard_incoming_links", "dashboard", "normal" );
	remove_meta_box( "dashboard_plugins", "dashboard", "normal" );
	remove_meta_box( "dashboard_recent_drafts", "dashboard", "side" );
} );

// Custom welcome panel
add_action( "welcome_panel", function() {
	?>
	<div class="welcome-panel-content" style="padding: 40px 20px;">
		<h2 style="font-family: \'Playfair Display\', serif; font-size: 24px; margin-bottom: 10px;">Welcome to Zalandy Admin</h2>
		<p style="font-size: 14px; color: #666; max-width: 600px;">Manage your jewelry and fashion store. Add products, process orders, and customize your storefront.</p>
		<div style="display: flex; gap: 12px; margin-top: 16px; flex-wrap: wrap;">
			<a href="/wp-admin/post-new.php?post_type=product" class="button button-primary">Add Product</a>
			<a href="/wp-admin/admin.php?page=wc-orders" class="button">View Orders</a>
			<a href="/wp-admin/edit.php?post_type=product" class="button">All Products</a>
			<a href="/wp-admin/admin.php?page=wc-settings" class="button">Settings</a>
		</div>
	</div>
	<?php
} );

// Admin CSS
add_action( "admin_head", function() {
	echo "<style>
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
	.welcome-panel-content h2 { font-family: \'Playfair Display\', serif; }
	#footer-upgrade { display: none; }
	#footer-thankyou { color: #c9a96e; }
	.login h1 a { background-image: none; }
	.login #nav a, .login #backtoblog a { color: #c9a96e; }
	</style>";
});

// Login page CSS
add_action( "login_head", function() {
	echo "<style>
	.login h1 a { background-image: none; text-indent: 0; width: auto; height: auto; font-family: \'Playfair Display\', serif; font-size: 32px; color: #c9a96e; font-weight: 700; }
	.login form { border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
	.login .button-primary { background: #c9a96e; border-color: #b8975a; }
	.login .button-primary:hover { background: #b8975a; }
	</style>";
});

// Remove WooCommerce marketing hub distractions
add_filter( "woocommerce_admin_features", function( $features ) {
	return $features;
});

// Simplify admin menu
add_action( "admin_menu", function() {
	// Remove items we dont need
	remove_submenu_page( "tools.php", "tools.php" );
}, 999 );

// Custom admin bar
add_action( "admin_bar_menu", function( $wp_admin_bar ) {
	$wp_admin_bar->add_node( array(
		"id"    => "zalandy-view-shop",
		"title" => "View Shop",
		"href"  => home_url( "/shop/" ),
		"parent" => "site-name",
	) );
}, 50 );
';

// Write admin mu-plugin
$mu_dir = WP_CONTENT_DIR . '/mu-plugins';
if ( ! is_dir( $mu_dir ) ) {
	wp_mkdir_p( $mu_dir );
}
file_put_contents( $mu_dir . '/zalandy-admin-ui.php', $admin_mu );
WP_CLI::log( "  Admin UI mu-plugin created" );

// ─── 7. Load Google Fonts ───────────────────────────────────────
WP_CLI::log( "\n--- Loading Google Fonts ---" );

$fonts_mu = '<?php
/**
 * Zalandy — Google Fonts Loader
 */
if ( ! defined( "ABSPATH" ) ) exit;

add_action( "wp_enqueue_scripts", function() {
	wp_enqueue_style(
		"zalandy-google-fonts",
		"https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap",
		array(),
		null
	);
	wp_enqueue_style(
		"zalandy-google-fonts-admin",
		"https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap",
		array(),
		null
	);
}, 20 );

// Also load in admin
add_action( "admin_enqueue_scripts", function() {
	wp_enqueue_style(
		"zalandy-admin-fonts",
		"https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap",
		array(),
		null
	);
});
';

file_put_contents( $mu_dir . '/zalandy-fonts.php', $fonts_mu );
WP_CLI::log( "  Google Fonts loader created" );

// ─── 8. Update Footer ───────────────────────────────────────────
WP_CLI::log( "\n--- Updating Footer ---" );

// Create footer widget areas
$footer_content = '<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:40px;padding:60px 0 30px;max-width:1200px;margin:0 auto;">
<div>
<h3 style="color:#fff;font-family:Playfair Display,serif;font-size:18px;margin-bottom:16px;">Zalandy</h3>
<p style="color:#999;font-size:14px;line-height:1.8;">Fine jewelry & contemporary fashion. Handcrafted with passion in Germany.</p>
</div>
<div>
<h3 style="color:#fff;font-family:Playfair Display,serif;font-size:18px;margin-bottom:16px;">Shop</h3>
<ul style="list-style:none;padding:0;">
<li style="margin-bottom:8px;"><a href="/shop/" style="color:#c9a96e;text-decoration:none;">All Products</a></li>
<li style="margin-bottom:8px;"><a href="/product-category/earrings/" style="color:#c9a96e;text-decoration:none;">Jewelry</a></li>
<li style="margin-bottom:8px;"><a href="/product-category/fashion/" style="color:#c9a96e;text-decoration:none;">Fashion</a></li>
<li style="margin-bottom:8px;"><a href="/product-category/fashion-accessories/" style="color:#c9a96e;text-decoration:none;">Accessories</a></li>
</ul>
</div>
<div>
<h3 style="color:#fff;font-family:Playfair Display,serif;font-size:18px;margin-bottom:16px;">Help</h3>
<ul style="list-style:none;padding:0;">
<li style="margin-bottom:8px;"><a href="/contact/" style="color:#c9a96e;text-decoration:none;">Contact Us</a></li>
<li style="margin-bottom:8px;"><a href="/faq/" style="color:#c9a96e;text-decoration:none;">FAQ</a></li>
<li style="margin-bottom:8px;"><a href="/size-guide/" style="color:#c9a96e;text-decoration:none;">Size Guide</a></li>
<li style="margin-bottom:8px;"><a href="/shipping-policy/" style="color:#c9a96e;text-decoration:none;">Shipping</a></li>
<li style="margin-bottom:8px;"><a href="/return-policy/" style="color:#c9a96e;text-decoration:none;">Returns</a></li>
</ul>
</div>
<div>
<h3 style="color:#fff;font-family:Playfair Display,serif;font-size:18px;margin-bottom:16px;">Legal</h3>
<ul style="list-style:none;padding:0;">
<li style="margin-bottom:8px;"><a href="/imprint/" style="color:#c9a96e;text-decoration:none;">Imprint</a></li>
<li style="margin-bottom:8px;"><a href="/privacy-policy/" style="color:#c9a96e;text-decoration:none;">Privacy Policy</a></li>
<li style="margin-bottom:8px;"><a href="/cookie-policy/" style="color:#c9a96e;text-decoration:none;">Cookie Policy</a></li>
<li style="margin-bottom:8px;"><a href="/terms-of-service/" style="color:#c9a96e;text-decoration:none;">Terms of Service</a></li>
<li style="margin-bottom:8px;"><a href="/withdrawal-right/" style="color:#c9a96e;text-decoration:none;">Withdrawal Right</a></li>
</ul>
</div>
</div>
<div style="border-top:1px solid rgba(255,255,255,0.1);padding-top:20px;text-align:center;">
<p style="color:#666;font-size:13px;">© 2026 Zalandy — Seniorenpflegeheim Bevern GmbH & Co. KG. All rights reserved. HRA: 204407</p>
</div>';

// Save footer as a widget or option
update_option( 'zalandy_custom_footer', $footer_content );

// Footer mu-plugin to inject
$footer_mu = '<?php
/**
 * Zalandy — Custom Footer
 */
if ( ! defined( "ABSPATH" ) ) exit;

add_action( "wp_footer", function() {
	$footer = get_option( "zalandy_custom_footer" );
	if ( $footer ) {
		echo "<div class=\"zalandy-custom-footer\" style=\"background:#1a1a1a;margin-top:0;\">" . $footer . "</div>";
	}
}, 100 );
';

file_put_contents( $mu_dir . '/zalandy-footer.php', $footer_mu );
WP_CLI::log( "  Custom footer created" );

// ─── 9. Flush & Summary ─────────────────────────────────────────
WP_CLI::log( "\n--- Flushing Cache ---" );
flush_rewrite_rules();
WP_CLI::log( "  Rewrite rules flushed" );

WP_CLI::log( "\n========================================" );
WP_CLI::log( "  Site Redesign Complete!" );
WP_CLI::log( "========================================" );
WP_CLI::log( "  - Homepage rebuilt with hero, categories, featured products" );
WP_CLI::log( "  - Custom CSS for jewelry + fashion aesthetic" );
WP_CLI::log( "  - Google Fonts (Inter + Playfair Display)" );
WP_CLI::log( "  - Admin UI customized" );
WP_CLI::log( "  - WooCommerce settings optimized" );
WP_CLI::log( "  - Custom footer with legal links" );
