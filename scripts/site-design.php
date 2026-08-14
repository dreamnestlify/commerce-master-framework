<?php
/**
 * Commerce Master — Site Design Setup
 *
 * Configures the Woostify homepage, navigation menu, WooCommerce settings,
 * and permalink structure. Run after jewelry-product-setup.php.
 *
 * Usage:
 *   php -d memory_limit=512M /usr/local/bin/wp eval-file scripts/site-design.php --allow-root
 *
 * @package CommerceMaster
 */

if (!defined('ABSPATH')) {
    exit;
}

WP_CLI::log('========================================');
WP_CLI::log('  Zalandy Site Design Setup');
WP_CLI::log('========================================');

// ═══════════════════════════════════════════════════════════════
// 1. Site Title & Tagline
// ═══════════════════════════════════════════════════════════════
WP_CLI::log('');
WP_CLI::log('🏷  Setting site title & tagline...');

update_option('blogname', 'Zalandy');
update_option('blogdescription', 'Fine Jewelry | Handcrafted Gemstone Collection');
update_option('siteurl', 'https://zalandy.top');
update_option('home', 'https://zalandy.top');

WP_CLI::log('  ✅ Site title: Zalandy');
WP_CLI::log('  ✅ Tagline: Fine Jewelry | Handcrafted Gemstone Collection');

// ═══════════════════════════════════════════════════════════════
// 2. WooCommerce Settings
// ═══════════════════════════════════════════════════════════════
WP_CLI::log('');
WP_CLI::log('⚙️  Configuring WooCommerce...');

// Currency
update_option('woocommerce_currency', 'USD');
update_option('woocommerce_currency_pos', 'left');
update_option('woocommerce_price_thousand_sep', ',');
update_option('woocommerce_price_decimal_sep', '.');
update_option('woocommerce_price_num_decimals', 2);

// Store address
update_option('woocommerce_store_address', 'Zalandy Jewelry');
update_option('woocommerce_store_city', 'Shanghai');
update_option('woocommerce_default_country', 'CN');
update_option('woocommerce_allowed_countries', 'all');

// Shop page
$shop_page = get_page_by_path('shop');
if (!$shop_page) {
    $shop_page_id = wp_insert_post([
        'post_title'   => 'Shop',
        'post_name'    => 'shop',
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_content' => '',
    ]);
    update_option('woocommerce_shop_page_id', $shop_page_id);
    WP_CLI::log('  ✅ Created Shop page (ID: ' . $shop_page_id . ')');
} else {
    update_option('woocommerce_shop_page_id', $shop_page->ID);
    WP_CLI::log('  ✓ Shop page exists (ID: ' . $shop_page->ID . ')');
}

// Cart, Checkout, My Account pages
$pages_to_create = [
    'cart'        => ['title' => 'Cart', 'option' => 'woocommerce_cart_page_id'],
    'checkout'    => ['title' => 'Checkout', 'option' => 'woocommerce_checkout_page_id'],
    'my-account'  => ['title' => 'My Account', 'option' => 'woocommerce_myaccount_page_id'],
];

foreach ($pages_to_create as $slug => $data) {
    $page = get_page_by_path($slug);
    if (!$page) {
        $page_id = wp_insert_post([
            'post_title'   => $data['title'],
            'post_name'    => $slug,
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => $slug === 'cart' ? '[woocommerce_cart]' : ($slug === 'checkout' ? '[woocommerce_checkout]' : ''),
        ]);
        update_option($data['option'], $page_id);
        WP_CLI::log("  ✅ Created {$data['title']} page (ID: {$page_id})");
    } else {
        update_option($data['option'], $page->ID);
        WP_CLI::log("  ✓ {$data['title']} page exists (ID: {$page->ID})");
    }
}

WP_CLI::log('  ✅ Currency: USD ($)');
WP_CLI::log('  ✅ Default country: CN');

// ═══════════════════════════════════════════════════════════════
// 3. Find a hero image from media library
// ═══════════════════════════════════════════════════════════════
WP_CLI::log('');
WP_CLI::log('🖼  Finding hero image...');

// Try to find a nice jewelry image for the hero banner
$hero_candidates = ['photo_1_', 'photo_11_', 'photo_15_', 'photo_27_', 'photo_39_'];
$hero_image_url = '';
$hero_image_id = 0;

foreach ($hero_candidates as $candidate) {
    $attachments = get_posts([
        'post_type'      => 'attachment',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => [
            [
                'key'     => '_wp_attached_file',
                'value'   => $candidate,
                'compare' => 'LIKE',
            ],
        ],
    ]);
    if (!empty($attachments)) {
        $hero_image_id = $attachments[0];
        $hero_image_url = wp_get_attachment_url($hero_image_id);
        break;
    }
}

if (empty($hero_image_url)) {
    // Fallback: get any attachment
    $attachments = get_posts([
        'post_type'      => 'attachment',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'post_mime_type' => 'image',
    ]);
    if (!empty($attachments)) {
        $hero_image_id = $attachments[0];
        $hero_image_url = wp_get_attachment_url($hero_image_id);
    }
}

if ($hero_image_url) {
    WP_CLI::log("  ✅ Hero image found: {$hero_image_url}");
} else {
    WP_CLI::warning('  ⚠ No hero image found, using gradient background');
    $hero_image_url = '';
}

// ═══════════════════════════════════════════════════════════════
// 4. Create Homepage
// ═══════════════════════════════════════════════════════════════
WP_CLI::log('');
WP_CLI::log('🏠 Creating homepage...');

// Build hero section
$hero_bg = $hero_image_url ? "background-image:url('{$hero_image_url}');" : 'background:linear-gradient(135deg,#1a1a2e,#16213e,#0f3460);';

$homepage_content = <<<HTML
<!-- Hero Banner -->
<div style="position:relative;min-height:520px;{$hero_bg}background-size:cover;background-position:center;display:flex;align-items:center;justify-content:center;margin-bottom:0;">
<div style="text-align:center;background:rgba(0,0,0,0.45);padding:60px 50px;margin:20px;max-width:700px;">
<h1 style="font-size:42px;color:#fff;margin-bottom:16px;letter-spacing:2px;font-weight:300;">ZALANDY</h1>
<p style="font-size:18px;color:#e8e8e8;margin-bottom:28px;font-weight:300;letter-spacing:1px;">Handcrafted Gemstone Jewelry<br/>Timeless Elegance for Every Occasion</p>
<a href="/shop/" style="display:inline-block;padding:14px 44px;background:#c9a96e;color:#fff;text-decoration:none;font-size:15px;letter-spacing:2px;border-radius:2px;text-transform:uppercase;">Shop Collection</a>
</div>
</div>

<!-- Featured Collection -->
<div style="max-width:1200px;margin:0 auto;padding:60px 20px;">
<h2 style="text-align:center;font-size:30px;margin-bottom:10px;font-weight:400;letter-spacing:1px;">Featured Collection</h2>
<p style="text-align:center;color:#999;margin-bottom:40px;font-size:14px;">Our handpicked favorite pieces</p>
[products limit="4" columns="4" orderby="date" order="DESC" visibility="visible" tag="featured"]

<!-- New Arrivals -->
<h2 style="text-align:center;font-size:30px;margin:60px 0 10px;font-weight:400;letter-spacing:1px;">New Arrivals</h2>
<p style="text-align:center;color:#999;margin-bottom:40px;font-size:14px;">Just landed in our collection</p>
[products limit="4" columns="4" orderby="date" order="DESC" visibility="visible" tag="new-arrival"]
</div>

<!-- About Section -->
<div style="background:#f8f6f3;padding:70px 20px;text-align:center;">
<div style="max-width:650px;margin:0 auto;">
<h2 style="font-size:28px;margin-bottom:20px;font-weight:400;letter-spacing:1px;">Crafted with Passion</h2>
<p style="font-size:15px;line-height:2;color:#777;">Each piece in our collection is carefully curated and crafted by skilled artisans. From vintage-inspired palace designs to modern minimalist creations, we bring you jewelry that tells a story. Every gemstone is hand-selected for its brilliance and character.</p>
<a href="/shop/" style="display:inline-block;margin-top:24px;padding:12px 36px;border:1px solid #c9a96e;color:#c9a96e;text-decoration:none;font-size:14px;letter-spacing:1px;text-transform:uppercase;">Explore All</a>
</div>
</div>

<!-- Shop by Category -->
<div style="max-width:1200px;margin:0 auto;padding:60px 20px;">
<h2 style="text-align:center;font-size:30px;margin-bottom:40px;font-weight:400;letter-spacing:1px;">Shop by Category</h2>
[product_categories number="5" columns="5" parent="0" hide_empty="0"]
</div>

<!-- All Products -->
<div style="max-width:1200px;margin:0 auto;padding:0 20px 60px;">
<h2 style="text-align:center;font-size:30px;margin-bottom:40px;font-weight:400;letter-spacing:1px;">All Jewelry</h2>
[products limit="8" columns="4" orderby="date" order="DESC" visibility="visible"]
</div>

<!-- Newsletter -->
<div style="background:#1a1a2e;padding:50px 20px;text-align:center;">
<h2 style="color:#fff;font-size:24px;margin-bottom:12px;font-weight:300;letter-spacing:1px;">Join the Zalandy Circle</h2>
<p style="color:#aaa;margin-bottom:24px;font-size:14px;">Subscribe for exclusive offers and new collection previews</p>
<div style="max-width:400px;margin:0 auto;display:flex;gap:8px;">
<input type="email" placeholder="Your email address" style="flex:1;padding:12px 16px;border:none;font-size:14px;background:#fff;"/>
<a href="/my-account/" style="display:inline-block;padding:12px 24px;background:#c9a96e;color:#fff;text-decoration:none;font-size:14px;">Subscribe</a>
</div>
</div>
HTML;

// Create or update homepage
$home_page = get_page_by_path('home');
if (!$home_page) {
    $home_page = get_page_by_path('zalandy-home');
}

$home_data = [
    'post_title'   => 'Zalandy Home',
    'post_name'    => 'zalandy-home',
    'post_status'  => 'publish',
    'post_type'    => 'page',
    'post_content' => $homepage_content,
];

if (!$home_page) {
    $home_page_id = wp_insert_post($home_data);
    WP_CLI::log("  ✅ Created homepage (ID: {$home_page_id})");
} else {
    $home_data['ID'] = $home_page->ID;
    wp_update_post($home_data);
    $home_page_id = $home_page->ID;
    WP_CLI::log("  ✓ Updated homepage (ID: {$home_page_id})");
}

// Set as front page
update_option('show_on_front', 'page');
update_option('page_on_front', $home_page_id);
update_option('page_for_posts', 0);
WP_CLI::log('  ✅ Set as front page');

// Set hero image as featured image
if ($hero_image_id) {
    set_post_thumbnail($home_page_id, $hero_image_id);
}

// ═══════════════════════════════════════════════════════════════
// 5. Navigation Menu
// ═══════════════════════════════════════════════════════════════
WP_CLI::log('');
WP_CLI::log('📋 Setting up navigation menu...');

$menu_name = 'Main Menu';
$menu_exists = wp_get_nav_menu_object($menu_name);

if (!$menu_exists) {
    $menu_id = wp_create_nav_menu($menu_name);
    WP_CLI::log("  ✅ Created menu: {$menu_name} (ID: {$menu_id})");
} else {
    $menu_id = $menu_exists->term_id;
    wp_delete_nav_menu($menu_id);
    $menu_id = wp_create_nav_menu($menu_name);
    WP_CLI::log("  ✓ Recreated menu: {$menu_name} (ID: {$menu_id})");
}

// Menu items
$menu_items = [
    ['title' => 'Home', 'url' => '/'],
    ['title' => 'Shop All', 'url' => '/shop/'],
    ['title' => 'Earrings', 'url' => '/product-category/earrings/'],
    ['title' => 'Necklaces', 'url' => '/product-category/necklaces/'],
    ['title' => 'Bracelets', 'url' => '/product-category/bracelets/'],
    ['title' => 'Rings', 'url' => '/product-category/rings/'],
    ['title' => 'Jewelry Sets', 'url' => '/product-category/jewelry-sets/'],
    ['title' => 'My Account', 'url' => '/my-account/'],
];

foreach ($menu_items as $item) {
    wp_update_nav_menu_item($menu_id, 0, [
        'menu-item-title'  => $item['title'],
        'menu-item-url'    => home_url($item['url']),
        'menu-item-status' => 'publish',
    ]);
    WP_CLI::log("  + {$item['title']} → {$item['url']}");
}

// Assign menu to primary location
$locations = get_theme_mod('nav_menu_locations');
if (empty($locations)) {
    $locations = [];
}
$locations['primary'] = $menu_id;
$locations['handheld'] = $menu_id;
set_theme_mod('nav_menu_locations', $locations);
WP_CLI::log('  ✅ Assigned to primary + mobile menu locations');

// ═══════════════════════════════════════════════════════════════
// 6. Woostify Theme Options
// ═══════════════════════════════════════════════════════════════
WP_CLI::log('');
WP_CLI::log('🎨 Configuring Woostify theme options...');

// Set header layout
set_theme_mod('woostify_header_layout', '1');

// Set primary color (gold/rose gold for jewelry)
set_theme_mod('woostify_color_primary', '#c9a96e');

// Set link color
set_theme_mod('woostify_color_link', '#c9a96e');

// Set button background
set_theme_mod('woostify_color_button_background', '#c9a96e');
set_theme_mod('woostify_color_button_text', '#ffffff');

// Set heading color
set_theme_mod('woostify_color_heading', '#2b2b2b');

// Set body text color
set_theme_mod('woostify_color_text', '#666666');

// Set page width
set_theme_mod('woostify_container_width', '1200');

// Disable top bar (optional)
set_theme_mod('woostify_topbar_enable', false);

// Footer
set_theme_mod('woostify_footer_layout', '1');

// Product archive columns
update_option('woocommerce_catalog_columns', 4);
update_option('woocommerce_catalog_rows', 4);

// Product images
update_option('woocommerce_single_image_width', '600');
update_option('woocommerce_thumbnail_image_width', '300');

// Enable zoom and lightbox
update_option('woocommerce_enable_ajax_add_to_cart', 'yes');
update_option('woocommerce_cart_redirect_after_add', 'no');

// Enable guest checkout
update_option('woocommerce_enable_guest_checkout', 'yes');
update_option('woocommerce_enable_checkout_login_reminder', 'yes');

// Enable signup on checkout
update_option('woocommerce_enable_signup_and_login_from_checkout', 'yes');

WP_CLI::log('  ✅ Primary color: #c9a96e (gold)');
WP_CLI::log('  ✅ Header layout: 1');
WP_CLI::log('  ✅ Product columns: 4');
WP_CLI::log('  ✅ Guest checkout: enabled');

// ═══════════════════════════════════════════════════════════════
// 7. Permalink Structure
// ═══════════════════════════════════════════════════════════════
WP_CLI::log('');
WP_CLI::log('🔗 Setting permalink structure...');

update_option('permalink_structure', '/%postname%/');
update_option('woocommerce_permalinks', [
    'product_base'       => '/product/',
    'category_base'      => 'product-category',
    'tag_base'           => 'product-tag',
    'attribute_base'     => '',
    'use_verbose_page_rules' => 0,
]);

// Product category slugs — ensure they match menu links
$cat_slugs = [
    'Earrings'     => 'earrings',
    'Necklaces'    => 'necklaces',
    'Bracelets'    => 'bracelets',
    'Rings'        => 'rings',
    'Jewelry Sets' => 'jewelry-sets',
];

foreach ($cat_slugs as $name => $slug) {
    $term = term_exists($name, 'product_cat');
    if (!empty($term) && is_array($term)) {
        wp_update_term($term['term_id'], 'product_cat', ['slug' => $slug]);
        WP_CLI::log("  ✅ Category slug: {$name} → /{$slug}/");
    }
}

WP_CLI::log('  ✅ Permalinks: /%postname%/');

// ═══════════════════════════════════════════════════════════════
// 8. Create Privacy Policy & Terms pages
// ═══════════════════════════════════════════════════════════════
WP_CLI::log('');
WP_CLI::log('📄 Creating policy pages...');

$policy_pages = [
    'privacy-policy' => [
        'title'   => 'Privacy Policy',
        'content' => '<h2>Privacy Policy</h2><p>At Zalandy, we respect your privacy and are committed to protecting your personal data. This policy explains how we collect, use, and safeguard your information when you visit our website or purchase our products.</p><h3>Information We Collect</h3><p>We collect information you provide directly to us, such as your name, email address, shipping address, and payment information when you place an order.</p><h3>How We Use Your Information</h3><p>We use your information to process orders, communicate with you about your purchases, and provide customer service.</p><h3>Data Security</h3><p>We implement appropriate security measures to protect your personal information from unauthorized access, alteration, or disclosure.</p><h3>Contact Us</h3><p>If you have questions about this policy, please contact us at indiagianina5@gmail.com</p>',
    ],
    'terms-of-service' => [
        'title'   => 'Terms of Service',
        'content' => '<h2>Terms of Service</h2><p>Welcome to Zalandy. By accessing our website and purchasing our products, you agree to the following terms and conditions.</p><h3>Products</h3><p>All jewelry products are handcrafted and may have slight variations. Product images are representative and actual colors may vary.</p><h3>Pricing</h3><p>All prices are listed in USD. We reserve the right to change prices without notice.</p><h3>Shipping</h3><p>Orders are processed within 2-3 business days. Shipping times vary by destination.</p><h3>Returns</h3><p>We accept returns within 30 days of delivery for items in original condition. Custom orders are non-returnable.</p><h3>Contact</h3><p>For questions about these terms, contact indiagianina5@gmail.com</p>',
    ],
    'shipping-policy' => [
        'title'   => 'Shipping Policy',
        'content' => '<h2>Shipping Policy</h2><h3>Processing Time</h3><p>Orders are processed within 2-3 business days after payment confirmation.</p><h3>Shipping Methods</h3><p>We offer standard shipping (7-14 business days) and express shipping (3-5 business days).</p><h3>International Shipping</h3><p>We ship worldwide. Customs duties and taxes are the responsibility of the buyer.</p><h3>Order Tracking</h3><p>Once your order ships, you will receive a tracking number via email.</p>',
    ],
    'return-policy' => [
        'title'   => 'Return & Refund Policy',
        'content' => '<h2>Return & Refund Policy</h2><h3>30-Day Return Window</h3><p>We accept returns within 30 days of delivery. Items must be in original condition with all packaging.</p><h3>How to Return</h3><p>Contact indiagianina5@gmail.com with your order number to initiate a return. We will provide a return address and instructions.</p><h3>Refund Processing</h3><p>Refunds are processed within 5-7 business days after we receive the returned item. Funds will be credited to your original payment method.</p><h3>Non-Returnable Items</h3><p>Custom-made and personalized jewelry items cannot be returned.</p>',
    ],
];

foreach ($policy_pages as $slug => $data) {
    $page = get_page_by_path($slug);
    if (!$page) {
        $page_id = wp_insert_post([
            'post_title'   => $data['title'],
            'post_name'    => $slug,
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => $data['content'],
        ]);
        WP_CLI::log("  ✅ Created: {$data['title']} (ID: {$page_id})");
    } else {
        WP_CLI::log("  ✓ Exists: {$data['title']} (ID: {$page->ID})");
    }
}

// Set WooCommerce policy pages
$privacy_page = get_page_by_path('privacy-policy');
$terms_page = get_page_by_path('terms-of-service');
if ($privacy_page) {
    update_option('wp_page_for_privacy_policy', $privacy_page->ID);
}
if ($terms_page) {
    update_option('woocommerce_terms_page_id', $terms_page->ID);
}

// ═══════════════════════════════════════════════════════════════
// 9. Flush & Summary
// ═══════════════════════════════════════════════════════════════
WP_CLI::log('');
WP_CLI::log('🔄 Flushing rewrite rules & cache...');

flush_rewrite_rules(true);
wp_cache_flush();

WP_CLI::log('');
WP_CLI::log('========================================');
WP_CLI::success('Site design complete!');
WP_CLI::log('');
WP_CLI::log('Homepage: https://zalandy.top');
WP_CLI::log('Shop:     https://zalandy.top/shop/');
WP_CLI::log('Admin:    https://zalandy.top/wp-admin');
WP_CLI::log('');
WP_CLI::log('Menu items: Home, Shop All, Earrings, Necklaces,');
WP_CLI::log('            Bracelets, Rings, Jewelry Sets, My Account');
WP_CLI::log('');
WP_CLI::log('Theme color: #c9a96e (gold)');
WP_CLI::log('Currency: USD ($) | Guest checkout: enabled');
WP_CLI::log('========================================');
