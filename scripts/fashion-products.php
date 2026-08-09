<?php
/**
 * Fashion Products Setup
 * Creates fashion/clothing categories and products for Zalandy
 * Run via: wp eval 'require "/tmp/fashion-products.php";' --allow-root
 */

if ( ! function_exists( 'wp_get_current_user' ) ) {
	// WP-CLI context - WP is already loaded
}

WP_CLI::log( '========================================' );
WP_CLI::log( '  Zalandy — Fashion Category Setup' );
WP_CLI::log( '========================================' );

// ─── Helper: create or get product category ─────────────────────
function _zalandy_create_cat( $name, $slug, $parent = 0 ) {
	$existing = get_term_by( 'slug', $slug, 'product_cat' );
	if ( $existing ) {
		WP_CLI::log( "  Category exists: $name (ID: {$existing->term_id})" );
		return $existing->term_id;
	}
	$args = array(
		'description' => '',
		'slug'        => $slug,
		'parent'      => $parent,
	);
	$result = wp_insert_term( $name, 'product_cat', $args );
	if ( is_wp_error( $result ) ) {
		WP_CLI::warning( "  Failed to create category $name: " . $result->get_error_message() );
		return 0;
	}
	$term_id = $result['term_id'];
	update_term_meta( $term_id, 'display_type', 'products' );
	update_term_meta( $term_id, 'product_count_product_cat', 0 );
	WP_CLI::log( "  Created category: $name (ID: $term_id)" );
	return $term_id;
}

// ─── Helper: create simple product ──────────────────────────────
function _zalandy_create_product( $data ) {
	$existing = get_page_by_title( $data['title'], OBJECT, 'product' );
	if ( $existing ) {
		// Update existing product
		$product_id = $existing->ID;
		WP_CLI::log( "  Updating product: {$data['title']} (ID: $product_id)" );
	} else {
		$product_id = wp_insert_post( array(
			'post_title'   => $data['title'],
			'post_content' => $data['description'],
			'post_status'  => 'publish',
			'post_type'    => 'product',
			'post_excerpt' => $data['short_desc'],
		) );
	}

	if ( ! $product_id ) {
		WP_CLI::warning( "  Failed to create product: {$data['title']}" );
		return;
	}

	// Set product type (simple)
	wp_set_object_terms( $product_id, 'simple', 'product_type' );

	// Set categories
	if ( ! empty( $data['categories'] ) ) {
		wp_set_post_terms( $product_id, $data['categories'], 'product_cat' );
	}

	// Set tags
	if ( ! empty( $data['tags'] ) ) {
		wp_set_post_terms( $product_id, $data['tags'], 'product_tag' );
	}

	// Update meta
	update_post_meta( $product_id, '_sku', $data['sku'] );
	update_post_meta( $product_id, '_regular_price', $data['price'] );
	update_post_meta( $product_id, '_price', $data['price'] );
	update_post_meta( $product_id, '_sale_price', '' );
	update_post_meta( $product_id, '_manage_stock', 'no' );
	update_post_meta( $product_id, '_stock_status', 'instock' );
	update_post_meta( $product_id, '_virtual', 'no' );
	update_post_meta( $product_id, '_downloadable', 'no' );
	update_post_meta( $product_id, '_visibility', 'visible' );
	update_post_meta( $product_id, '_featured', $data['featured'] ? 'yes' : 'no' );
	update_post_meta( $product_id, '_backorders', 'no' );

	// Set gallery order
	update_post_meta( $product_id, 'total_sales', '0' );

	WP_CLI::log( "  Product ready: {$data['title']} — \${$data['price']}" );
}

// ─── 1. Create Fashion Categories ───────────────────────────────
WP_CLI::log( "\n--- Creating Fashion Categories ---" );

// Parent: Fashion
$fashion_id = _zalandy_create_cat( 'Fashion', 'fashion' );

// Sub-categories
$women_id = _zalandy_create_cat( "Women's Clothing", 'womens-clothing', $fashion_id );
$men_id   = _zalandy_create_cat( "Men's Clothing", 'mens-clothing', $fashion_id );
$accessories_id = _zalandy_create_cat( 'Fashion Accessories', 'fashion-accessories', $fashion_id );

// Add description to parent
if ( $fashion_id ) {
	wp_update_term( $fashion_id, 'product_cat', array(
		'description' => 'Contemporary fashion pieces for every occasion. From everyday essentials to statement styles.',
	) );
}

// ─── 2. Create Fashion Products ─────────────────────────────────
WP_CLI::log( "\n--- Creating Fashion Products ---" );

// Women's Products
_zalandy_create_product( array(
	'title'       => "Silk Wrap Midi Dress — Romantic Floral Print",
	'sku'         => 'FS-WD-001',
	'price'       => '89.00',
	'short_desc'  => 'Elegant silk-blend wrap dress with delicate floral print. Adjustable waist tie, midi length. Perfect for brunch to evening.',
	'description' => '<p>Our signature Silk Wrap Midi Dress combines timeless elegance with modern comfort.</p>
<ul>
<li><strong>Material:</strong> 85% Silk, 15% Polyester</li>
<li><strong>Fit:</strong> True to size, adjustable wrap</li>
<li><strong>Length:</strong> Midi (knee-length)</li>
<li><strong>Care:</strong> Hand wash cold, hang dry</li>
<li><strong>Colors:</strong> Blush Rose, Sage Green, Midnight Blue</li>
</ul>
<p>The adjustable wrap design flatters every body type, while the breathable silk blend keeps you comfortable from day to night.</p>',
	'categories'  => array( $women_id, $fashion_id ),
	'tags'        => array( 'new-arrival', 'bestseller' ),
	'featured'    => true,
) );

_zalandy_create_product( array(
	'title'       => "Cotton Linen Summer Jumpsuit — Wide Leg",
	'sku'         => 'FS-WD-002',
	'price'       => '65.00',
	'short_desc'  => 'Relaxed cotton-linen jumpsuit with wide leg cut and adjustable straps. The ultimate summer essential.',
	'description' => '<p>Effortless style meets all-day comfort in our Cotton Linen Jumpsuit.</p>
<ul>
<li><strong>Material:</strong> 55% Cotton, 45% Linen</li>
<li><strong>Fit:</strong> Relaxed, wide leg</li>
<li><strong>Features:</strong> Adjustable straps, side pockets, elastic back</li>
<li><strong>Care:</strong> Machine wash cold</li>
</ul>
<p>Side pockets and a breathable weave make this your go-to piece for warm-weather adventures.</p>',
	'categories'  => array( $women_id, $fashion_id ),
	'tags'        => array( 'new-arrival' ),
	'featured'    => false,
) );

_zalandy_create_product( array(
	'title'       => "Oversized Knit Cardigan — Cozy Chunky Weave",
	'sku'         => 'FS-WD-003',
	'price'       => '72.00',
	'short_desc'  => 'Chunky knit cardigan in oversized fit. Perfect layering piece for autumn and winter.',
	'description' => '<p>Wrap yourself in warmth with our Oversized Knit Cardigan.</p>
<ul>
<li><strong>Material:</strong> 60% Acrylic, 30% Wool, 10% Mohair</li>
<li><strong>Fit:</strong> Oversized, drop shoulder</li>
<li><strong>Features:</strong> Open front, ribbed cuffs, chunky weave</li>
<li><strong>Care:</strong> Hand wash, lay flat to dry</li>
</ul>',
	'categories'  => array( $women_id, $fashion_id ),
	'tags'        => array(),
	'featured'    => false,
) );

_zalandy_create_product( array(
	'title'       => "High-Waist Wide Leg Trousers — Tailored Fit",
	'sku'         => 'FS-WD-004',
	'price'       => '58.00',
	'short_desc'  => 'High-waist wide leg trousers with pressed crease. Tailored for a sophisticated silhouette.',
	'description' => '<p>Elevate your wardrobe with our Tailored Wide Leg Trousers.</p>
<ul>
<li><strong>Material:</strong> 95% Polyester, 5% Elastane</li>
<li><strong>Fit:</strong> High waist, wide leg</li>
<li><strong>Features:</strong> Front pleats, belt loops, hidden side pockets</li>
<li><strong>Care:</strong> Machine wash cold, hang dry</li>
</ul>',
	'categories'  => array( $women_id, $fashion_id ),
	'tags'        => array(),
	'featured'    => true,
) );

_zalandy_create_product( array(
	'title'       => "Pleated Mini Skirt — A-Line Cut",
	'sku'         => 'FS-WD-005',
	'price'       => '42.00',
	'short_desc'  => 'Classic pleated mini skirt with A-line silhouette. Versatile styling for any occasion.',
	'description' => '<p>A timeless pleated mini skirt that transitions effortlessly from casual to dressy.</p>
<ul>
<li><strong>Material:</strong> 100% Polyester (pleated weave)</li>
<li><strong>Fit:</strong> High waist, A-line</li>
<li><strong>Features:</strong> Elastic waistband, knife pleats</li>
<li><strong>Care:</strong> Machine wash cold, hang dry</li>
</ul>',
	'categories'  => array( $women_id, $fashion_id ),
	'tags'        => array( 'new-arrival' ),
	'featured'    => false,
) );

_zalandy_create_product( array(
	'title'       => "Cropped Blazer — Structured Shoulder",
	'sku'         => 'FS-WD-006',
	'price'       => '78.00',
	'short_desc'  => 'Cropped blazer with structured shoulders and single-button closure. Modern tailoring at its finest.',
	'description' => '<p>The Cropped Blazer brings contemporary edge to a classic silhouette.</p>
<ul>
<li><strong>Material:</strong> 65% Polyester, 33% Rayon, 2% Elastane</li>
<li><strong>Fit:</strong> Cropped, structured shoulder</li>
<li><strong>Features:</strong> Single-button closure, notched lapel, inner lining</li>
<li><strong>Care:</strong> Dry clean recommended</li>
</ul>',
	'categories'  => array( $women_id, $fashion_id ),
	'tags'        => array( 'bestseller' ),
	'featured'    => true,
) );

// Men's Products
_zalandy_create_product( array(
	'title'       => "Premium Cotton T-Shirt — Minimalist Essential",
	'sku'         => 'FS-MN-001',
	'price'       => '28.00',
	'short_desc'  => 'Premium heavyweight cotton tee with relaxed fit. The everyday essential reinvented.',
	'description' => '<p>Our Premium Cotton T-Shirt is built to last with heavyweight fabric and a timeless cut.</p>
<ul>
<li><strong>Material:</strong> 100% Combed Cotton (240gsm)</li>
<li><strong>Fit:</strong> Regular, true to size</li>
<li><strong>Features:</strong> Ribbed crew neck, reinforced shoulders, pre-shrunk</li>
<li><strong>Colors:</strong> Black, White, Heather Grey, Navy</li>
<li><strong>Care:</strong> Machine wash cold, tumble dry low</li>
</ul>',
	'categories'  => array( $men_id, $fashion_id ),
	'tags'        => array( 'bestseller' ),
	'featured'    => true,
) );

_zalandy_create_product( array(
	'title'       => "Oxford Button-Down Shirt — Classic Fit",
	'sku'         => 'FS-MN-002',
	'price'       => '49.00',
	'short_desc'  => 'Timeless Oxford button-down in breathable cotton weave. Dress up or down with ease.',
	'description' => '<p>A wardrobe staple crafted from premium Oxford cotton.</p>
<ul>
<li><strong>Material:</strong> 100% Cotton Oxford (160gsm)</li>
<li><strong>Fit:</strong> Classic, roomy through chest</li>
<li><strong>Features:</strong> Button-down collar, back yoke, box pleat, pearl buttons</li>
<li><strong>Colors:</strong> White, Light Blue, Pink, Chambray</li>
<li><strong>Care:</strong> Machine wash cold, iron medium</li>
</ul>',
	'categories'  => array( $men_id, $fashion_id ),
	'tags'        => array(),
	'featured'    => false,
) );

_zalandy_create_product( array(
	'title'       => "Slim-Fit Chino Pants — Stretch Comfort",
	'sku'         => 'FS-MN-003',
	'price'       => '55.00',
	'short_desc'  => 'Slim-fit chino pants with 4-way stretch fabric. Comfort meets sharp tailoring.',
	'description' => '<p>Our Slim-Fit Chinos deliver refined style with all-day mobility.</p>
<ul>
<li><strong>Material:</strong> 97% Cotton, 3% Elastane</li>
<li><strong>Fit:</strong> Slim, tapered leg</li>
<li><strong>Features:</strong> 4-way stretch, slash pockets, back welt pockets</li>
<li><strong>Colors:</strong> Khaki, Navy, Olive, Charcoal</li>
<li><strong>Care:</strong> Machine wash cold, hang dry</li>
</ul>',
	'categories'  => array( $men_id, $fashion_id ),
	'tags'        => array( 'new-arrival' ),
	'featured'    => false,
) );

_zalandy_create_product( array(
	'title'       => "Wool Blend Overcoat — Tailored Longline",
	'sku'         => 'FS-MN-004',
	'price'       => '145.00',
	'short_desc'  => 'Wool-blend overcoat with notched lapel and tailored longline cut. Cold-weather sophistication.',
	'description' => '<p>Invest in timeless outerwear with our Wool Blend Overcoat.</p>
<ul>
<li><strong>Material:</strong> 60% Wool, 40% Polyester</li>
<li><strong>Fit:</strong> Tailored, longline (mid-thigh)</li>
<li><strong>Features:</strong> Notched lapel, two flap pockets, inner chest pocket, full lining</li>
<li><strong>Colors:</strong> Camel, Charcoal, Black</li>
<li><strong>Care:</strong> Dry clean only</li>
</ul>',
	'categories'  => array( $men_id, $fashion_id ),
	'tags'        => array(),
	'featured'    => true,
) );

_zalandy_create_product( array(
	'title'       => "Relaxed Cargo Pants — Utility Style",
	'sku'         => 'FS-MN-005',
	'price'       => '62.00',
	'short_desc'  => 'Relaxed-fit cargo pants with multiple pockets. Street-style meets functionality.',
	'description' => '<p>Utility-inspired cargo pants built for movement and style.</p>
<ul>
<li><strong>Material:</strong> 100% Cotton Twill</li>
<li><strong>Fit:</strong> Relaxed, straight leg</li>
<li><strong>Features:</strong> 6 pockets, adjustable ankle drawstring, reinforced knees</li>
<li><strong>Colors:</strong> Olive, Black, Khaki</li>
<li><strong>Care:</strong> Machine wash cold, hang dry</li>
</ul>',
	'categories'  => array( $men_id, $fashion_id ),
	'tags'        => array( 'new-arrival' ),
	'featured'    => false,
) );

// Fashion Accessories
_zalandy_create_product( array(
	'title'       => "Genuine Leather Belt — Italian Craftsmanship",
	'sku'         => 'FS-AC-001',
	'price'       => '45.00',
	'short_desc'  => 'Full-grain Italian leather belt with brushed steel buckle. Ages beautifully with wear.',
	'description' => '<p>A belt that only gets better with age.</p>
<ul>
<li><strong>Material:</strong> Full-grain Italian leather</li>
<li><strong>Buckle:</strong> Brushed stainless steel</li>
<li><strong>Width:</strong> 3.5cm (1.4 inches)</li>
<li><strong>Colors:</strong> Cognac Brown, Black, Tan</li>
</ul>',
	'categories'  => array( $accessories_id, $fashion_id ),
	'tags'        => array(),
	'featured'    => false,
) );

_zalandy_create_product( array(
	'title'       => "Cashmere Blend Scarf — Luxury Soft",
	'sku'         => 'FS-AC-002',
	'price'       => '52.00',
	'short_desc'  => 'Cashmere-blend scarf with fringe edges. Lightweight warmth for any season.',
	'description' => '<p>Wrap yourself in luxury with our Cashmere Blend Scarf.</p>
<ul>
<li><strong>Material:</strong> 70% Cashmere, 30% Merino Wool</li>
<li><strong>Size:</strong> 180cm x 30cm</li>
<li><strong>Features:</strong> Hand-finished fringe edges, ultra-soft hand feel</li>
<li><strong>Colors:</strong> Camel, Grey, Burgundy, Cream</li>
</ul>',
	'categories'  => array( $accessories_id, $fashion_id ),
	'tags'        => array( 'new-arrival' ),
	'featured'    => true,
) );

_zalandy_create_product( array(
	'title'       => "Structured Crossbody Bag — Vegan Leather",
	'sku'         => 'FS-AC-003',
	'price'       => '68.00',
	'short_desc'  => 'Structured crossbody bag in premium vegan leather. Compact size, maximum style.',
	'description' => '<p>Your new everyday companion — the Structured Crossbody Bag.</p>
<ul>
<li><strong>Material:</strong> Premium vegan leather (PU)</li>
<li><strong>Size:</strong> 22cm x 14cm x 6cm</li>
<li><strong>Features:</strong> Adjustable strap, magnetic closure, inner zip pocket, card slots</li>
<li><strong>Colors:</strong> Black, Beige, Olive</li>
</ul>',
	'categories'  => array( $accessories_id, $fashion_id ),
	'tags'        => array( 'bestseller' ),
	'featured'    => false,
) );

_zalandy_create_product( array(
	'title'       => "Retro Square Sunglasses — UV400 Protection",
	'sku'         => 'FS-AC-004',
	'price'       => '35.00',
	'short_desc'  => 'Retro-inspired square sunglasses with UV400 protection. Acetate frame, polarized lenses.',
	'description' => '<p>Channel vintage glamour with our Retro Square Sunglasses.</p>
<ul>
<li><strong>Frame:</strong> Acetate</li>
<li><strong>Lens:</strong> Polarized, UV400 protection</li>
<li><strong>Includes:</strong> Hard case, microfiber cloth</li>
<li><strong>Colors:</strong> Tortoise, Black, Amber</li>
</ul>',
	'categories'  => array( $accessories_id, $fashion_id ),
	'tags'        => array( 'new-arrival' ),
	'featured'    => false,
) );

// ─── 3. Create Fashion Tags ─────────────────────────────────────
WP_CLI::log( "\n--- Creating Tags ---" );

$tags = array( 'new-arrival', 'bestseller', 'summer-collection', 'autumn-collection' );
foreach ( $tags as $slug ) {
	$name = ucwords( str_replace( '-', ' ', $slug ) );
	$existing = get_term_by( 'slug', $slug, 'product_tag' );
	if ( ! $existing ) {
		wp_insert_term( $name, 'product_tag', array( 'slug' => $slug ) );
		WP_CLI::log( "  Created tag: $name" );
	}
}

// ─── 4. Update Navigation Menu ──────────────────────────────────
WP_CLI::log( "\n--- Updating Navigation Menu ---" );

$menu_items = wp_get_nav_menu_items( 'Main Menu' );
$menu_exists = array();
if ( $menu_items ) {
	foreach ( $menu_items as $item ) {
		$menu_exists[ $item->title ] = true;
	}
}

// Add Fashion parent menu item
$fashion_term = get_term_by( 'slug', 'fashion', 'product_cat' );
if ( $fashion_term && ! isset( $menu_exists['Fashion'] ) ) {
	wp_update_nav_menu_item( 'Main Menu', 0, array(
		'menu-item-title'     => 'Fashion',
		'menu-item-url'       => get_term_link( $fashion_term ),
		'menu-item-status'    => 'publish',
		'menu-item-type'      => 'taxonomy',
		'menu-item-object'    => 'product_cat',
		'menu-item-object-id' => $fashion_term->term_id,
	) );
	WP_CLI::log( "  Added Fashion to Main Menu" );
}

// Add sub-menu items
$fashion_cats = array(
	"Women's Clothing"     => 'womens-clothing',
	"Men's Clothing"       => 'mens-clothing',
	"Fashion Accessories"  => 'fashion-accessories',
);

foreach ( $fashion_cats as $name => $slug ) {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	if ( $term && ! isset( $menu_exists[ $name ] ) ) {
		wp_update_nav_menu_item( 'Main Menu', 0, array(
			'menu-item-title'     => $name,
			'menu-item-url'       => get_term_link( $term ),
			'menu-item-status'    => 'publish',
			'menu-item-type'      => 'taxonomy',
			'menu-item-object'    => 'product_cat',
			'menu-item-object-id' => $term->term_id,
			'menu-item-parent-id' => 0,
		) );
		WP_CLI::log( "  Added $name to menu" );
	}
}

// ─── 5. Summary ─────────────────────────────────────────────────
WP_CLI::log( "\n========================================" );
WP_CLI::log( "  Fashion Setup Complete!" );
WP_CLI::log( "========================================" );

$total_products = wp_count_posts( 'product' );
WP_CLI::log( "Total products: {$total_products->publish}" );

$cats = get_terms( array(
	'taxonomy'   => 'product_cat',
	'hide_empty' => false,
	'exclude'    => array( 15 ), // exclude Uncategorized
) );
WP_CLI::log( "Total categories: " . count( $cats ) );
foreach ( $cats as $cat ) {
	WP_CLI::log( "  - {$cat->name}: {$cat->count} products" );
}
