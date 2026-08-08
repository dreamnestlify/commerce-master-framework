<?php
/**
 * Commerce Master — Demo Product Data Initialization
 *
 * Runs via: wp eval-file scripts/demo-data.php
 *
 * Convergently idempotent: safe to run multiple times.
 * Each run corrects toward the target state:
 *   - Existing products are corrected (category, attributes, price, stock).
 *   - Existing variable products have missing variations added.
 *   - Duplicate runs do not create duplicates.
 *   - After a partial failure, a second run repairs to target state.
 *
 * Creates:
 * - Product attributes: Color, Size, Shoe Size
 * - Product categories: Women, Men, Shoes, Accessories
 * - Demo products:
 *   - Simple: tote bag, leather belt, silk scarf, sunglasses
 *   - Variable (apparel): cotton t-shirt, linen dress, denim jacket, merino sweater
 *   - Variable (shoes): canvas sneaker, Chelsea boots
 *
 * Uses pure-color placeholder images generated via GD. No third-party assets.
 *
 * @package CommerceMaster
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WooCommerce')) {
    WP_CLI::error('WooCommerce is not active. Install and activate WooCommerce first.');
}

// ── Helper: assert no WP_Error, fail loudly ──
function _assert_not_wp_error($result, string $context): void
{
    if (is_wp_error($result)) {
        WP_CLI::error(sprintf('FAIL [%s]: %s', $context, $result->get_error_message()));
    }
}

// ── Helper: assert product saved correctly ──
function _assert_product_saved($product, string $context): void
{
    if (!$product || !$product->get_id()) {
        WP_CLI::error("FAIL [{$context}]: Product save returned no ID.");
    }
}

// ══════════════════════════════════════════════════
// 1. Create Product Categories
// ══════════════════════════════════════════════════
WP_CLI::log('📁 Creating product categories...');

$categories = [
    'Women'       => "Women's apparel, including dresses, tops, and knitwear.",
    'Men'         => "Men's apparel, including shirts, jackets, and knitwear.",
    'Shoes'       => 'Footwear for men and women — sneakers, boots, and more.',
    'Accessories' => 'Bags, belts, scarves, sunglasses, and other accessories.',
];

$category_ids = [];
foreach ($categories as $name => $desc) {
    $existing = term_exists($name, 'product_cat');
    if (empty($existing)) {
        $term = wp_insert_term($name, 'product_cat', [
            'description' => $desc,
            'slug'        => sanitize_title($name),
        ]);
        _assert_not_wp_error($term, "create category: {$name}");
        $category_ids[$name] = (int) $term['term_id'];
        WP_CLI::log("  ✅ Created: {$name} (ID: {$category_ids[$name]})");
    } else {
        $tid = is_array($existing) ? (int) $existing['term_id'] : (int) $existing;
        // Ensure description is correct (convergent).
        wp_update_term($tid, 'product_cat', ['description' => $desc]);
        $category_ids[$name] = $tid;
        WP_CLI::log("  ✓ Exists: {$name} (ID: {$tid})");
    }
}

// ══════════════════════════════════════════════════
// 2. Create Product Attributes + Register Taxonomies
// ══════════════════════════════════════════════════
WP_CLI::log('🏷️  Creating product attributes...');

$attributes_def = [
    'color' => [
        'name'   => 'Color',
        'slug'   => 'pa_color',
        'type'   => 'select',
        'order_by' => 'menu_order',
        'values' => ['Black', 'White', 'Sage Green', 'Navy', 'Natural', 'Terracotta', 'Olive', 'Indigo', 'Washed Blue', 'Charcoal', 'Camel', 'Forest Green', 'Brown', 'Cream', 'Tan'],
    ],
    'size' => [
        'name'   => 'Size',
        'slug'   => 'pa_size',
        'type'   => 'select',
        'order_by' => 'menu_order',
        'values' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
    ],
    'shoe_size' => [
        'name'   => 'Shoe Size (EU)',
        'slug'   => 'pa_shoe_size',
        'type'   => 'select',
        'order_by' => 'menu_order',
        'values' => ['38', '39', '40', '41', '42', '43', '44', '45'],
    ],
];

$existing_attributes = wc_get_attribute_taxonomies();
$existing_slugs = array_column($existing_attributes ?: [], 'attribute_name');

foreach ($attributes_def as $key => $attr) {
    $slug_name = str_replace('pa_', '', $attr['slug']);

    if (in_array($slug_name, $existing_slugs, true)) {
        WP_CLI::log("  ✓ Exists: {$attr['name']}");
    } else {
        $id = wc_create_attribute([
            'name'         => $attr['name'],
            'slug'         => $slug_name,
            'type'         => $attr['type'],
            'order_by'     => $attr['order_by'],
            'has_archives' => true,
        ]);
        _assert_not_wp_error($id, "create attribute: {$attr['name']}");
        WP_CLI::log("  ✅ Created: {$attr['name']} (ID: {$id})");
    }

    // CRITICAL: Register the taxonomy in the current execution context
    // before creating terms. Without this, wp_insert_term will fail.
    if (!taxonomy_exists($attr['slug'])) {
        register_taxonomy(
            $attr['slug'],
            ['product'],
            [
                'hierarchical'      => false,
                'show_ui'           => false,
                'show_in_nav_menus' => false,
                'query_var'         => true,
                'rewrite'           => false,
                'public'            => false,
                'label'             => $attr['name'],
            ]
        );
    }

    // Create terms (idempotent + convergent: update slug if missing).
    foreach ($attr['values'] as $value) {
        $term_slug = sanitize_title($value);
        $existing_term = term_exists($value, $attr['slug']);
        if (empty($existing_term)) {
            $term_result = wp_insert_term($value, $attr['slug'], ['slug' => $term_slug]);
            _assert_not_wp_error($term_result, "create term: {$value} in {$attr['slug']}");
        }
    }
}

// Flush permalinks after attribute creation.
flush_rewrite_rules();

// ══════════════════════════════════════════════════
// 3. Create Placeholder Images (solid color via GD)
// ══════════════════════════════════════════════════
WP_CLI::log('🎨 Creating placeholder images...');

$color_map = [
    'Black'        => '#1a1a1a',
    'White'        => '#f5f5f5',
    'Sage Green'   => '#9caf88',
    'Navy'         => '#1e3a5f',
    'Natural'      => '#f5e6d3',
    'Terracotta'   => '#c97f5d',
    'Olive'        => '#6b7c3a',
    'Indigo'       => '#2e3a59',
    'Washed Blue'  => '#7ea3c4',
    'Charcoal'     => '#36454f',
    'Camel'        => '#c19a6b',
    'Forest Green' => '#2d5a27',
    'Brown'        => '#7a5230',
    'Cream'        => '#f5f0e1',
    'Tan'          => '#d2b48c',
];

/**
 * Generate a solid-color placeholder image and return attachment ID.
 * Requires GD extension. Fails explicitly if GD is unavailable.
 *
 * @param string $color_name Color name.
 * @param string $hex Hex color code.
 * @return int Attachment ID (0 on failure).
 */
function create_placeholder_image(string $color_name, string $hex): int
{
    // Check if image already exists by title.
    $existing = get_posts([
        'post_type'      => 'attachment',
        'title'           => 'placeholder_' . sanitize_title($color_name),
        'posts_per_page'  => 1,
        'fields'          => 'ids',
    ]);

    if (!empty($existing)) {
        return (int) $existing[0];
    }

    // GD is required. No manual PNG fallback — it produces invalid files.
    if (!function_exists('imagecreate')) {
        WP_CLI::warning("GD extension not available — skipping image for '{$color_name}'.");
        return 0;
    }

    $upload_dir = wp_upload_dir();
    $filename = 'placeholder-' . sanitize_title($color_name) . '.png';
    $filepath = trailingslashit($upload_dir['path']) . $filename;

    $rgb = sscanf($hex, '#%02x%02x%02x');
    if ($rgb === false || count($rgb) < 3) {
        WP_CLI::warning("Invalid hex color '{$hex}' for '{$color_name}'.");
        return 0;
    }

    $img = imagecreatetruecolor(800, 1000);
    if (!$img) {
        WP_CLI::warning("Failed to create image resource for '{$color_name}'.");
        return 0;
    }
    $color = imagecolorallocate($img, (int) $rgb[0], (int) $rgb[1], (int) $rgb[2]);
    imagefill($img, 0, 0, $color);

    if (!imagepng($img, $filepath)) {
        imagedestroy($img);
        WP_CLI::warning("Failed to write PNG for '{$color_name}'.");
        return 0;
    }
    imagedestroy($img);

    $wp_filetype = wp_check_filetype($filename, null);
    $attachment = [
        'post_mime_type' => $wp_filetype['type'] ?? 'image/png',
        'post_title'     => 'placeholder_' . sanitize_title($color_name),
        'post_content'   => '',
        'post_status'    => 'inherit',
    ];

    $attach_id = wp_insert_attachment($attachment, $filepath);
    _assert_not_wp_error($attach_id, "insert attachment: {$color_name}");

    if ($attach_id) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $metadata = wp_generate_attachment_metadata($attach_id, $filepath);
        wp_update_attachment_metadata($attach_id, $metadata);
        return (int) $attach_id;
    }

    return 0;
}

$placeholder_images = [];
foreach ($color_map as $color_name => $hex) {
    $id = create_placeholder_image($color_name, $hex);
    if ($id > 0) {
        $placeholder_images[$color_name] = $id;
    }
}
WP_CLI::log('  ✅ ' . count($placeholder_images) . ' placeholder images ready.');

// ══════════════════════════════════════════════════
// 4. Demo Product Definitions
// ══════════════════════════════════════════════════

$demo_products = [
    [
        'name'          => 'Classic Leather Tote Bag',
        'type'          => 'simple',
        'category'      => 'Accessories',
        'sku'           => 'ACC-TOTE-001',
        'regular_price' => '89.00',
        'stock'         => 50,
        'color'         => 'Black',
        'description'   => 'A timeless leather tote bag with spacious interior and premium hardware. Perfect for everyday use.',
        'short_desc'    => 'Premium leather tote bag for everyday elegance.',
        'weight'        => '0.8',
        'dimensions'    => ['length' => '40', 'width' => '30', 'height' => '15'],
    ],
    [
        'name'          => 'Reversible Leather Belt',
        'type'          => 'simple',
        'category'      => 'Accessories',
        'sku'           => 'ACC-BELT-001',
        'regular_price' => '45.00',
        'stock'         => 80,
        'color'         => 'Brown',
        'description'   => 'A versatile reversible leather belt with brushed steel buckle. Black on one side, brown on the other.',
        'short_desc'    => 'Two looks in one — reversible leather belt.',
        'weight'        => '0.3',
        'dimensions'    => ['length' => '110', 'width' => '3.5', 'height' => '0.5'],
    ],
    [
        'name'          => 'Silk Scarf — Floral Print',
        'type'          => 'simple',
        'category'      => 'Accessories',
        'sku'           => 'ACC-SCARF-001',
        'regular_price' => '65.00',
        'stock'         => 120,
        'color'         => 'Cream',
        'description'   => 'Luxurious 100% silk scarf with a delicate floral print. Lightweight and soft to the touch.',
        'short_desc'    => '100% silk floral print scarf.',
        'weight'        => '0.1',
        'dimensions'    => ['length' => '90', 'width' => '90', 'height' => '0.1'],
    ],
    [
        'name'          => 'Polarized Sunglasses',
        'type'          => 'simple',
        'category'      => 'Accessories',
        'sku'           => 'ACC-SUN-001',
        'regular_price' => '120.00',
        'stock'         => 60,
        'color'         => 'Black',
        'description'   => 'UV400 polarized sunglasses with lightweight acetate frame. Includes protective case.',
        'short_desc'    => 'UV400 polarized sunglasses with case.',
        'weight'        => '0.05',
        'dimensions'    => ['length' => '15', 'width' => '14', 'height' => '4'],
    ],
    [
        'name'          => 'Organic Cotton T-Shirt',
        'type'          => 'variable',
        'category'      => 'Women',
        'sku'           => 'WOM-TS-001',
        'regular_price' => '29.00',
        'stock'         => 200,
        'description'   => 'Soft organic cotton t-shirt with a relaxed fit. Pre-washed for minimal shrinkage. Ethically made.',
        'short_desc'    => 'Organic cotton tee — soft, relaxed, sustainable.',
        'attributes'    => [
            'color' => ['Black', 'White', 'Sage Green', 'Navy'],
            'size'  => ['XS', 'S', 'M', 'L', 'XL'],
        ],
        'weight'        => '0.2',
    ],
    [
        'name'          => 'Linen Wrap Dress',
        'type'          => 'variable',
        'category'      => 'Women',
        'sku'           => 'WOM-DR-001',
        'regular_price' => '145.00',
        'stock'         => 80,
        'description'   => 'Elegant linen wrap dress with adjustable waist tie. Breathable fabric perfect for warm weather. Knee-length.',
        'short_desc'    => 'Breathable linen wrap dress with waist tie.',
        'attributes'    => [
            'color' => ['Natural', 'Terracotta', 'Olive'],
            'size'  => ['XS', 'S', 'M', 'L'],
        ],
        'weight'        => '0.35',
    ],
    [
        'name'          => 'Selvedge Denim Jacket',
        'type'          => 'variable',
        'category'      => 'Men',
        'sku'           => 'MEN-JK-001',
        'regular_price' => '185.00',
        'stock'         => 60,
        'description'   => 'Japanese selvedge denim jacket with copper rivets and button fly. Raw, unwashed — breaks in beautifully over time.',
        'short_desc'    => 'Japanese selvedge denim jacket — ages with character.',
        'attributes'    => [
            'color' => ['Indigo', 'Black', 'Washed Blue'],
            'size'  => ['S', 'M', 'L', 'XL', 'XXL'],
        ],
        'weight'        => '0.7',
    ],
    [
        'name'          => 'Merino Wool Knit Sweater',
        'type'          => 'variable',
        'category'      => 'Men',
        'sku'           => 'MEN-KN-001',
        'regular_price' => '125.00',
        'stock'         => 70,
        'description'   => 'Fine-gauge merino wool crew neck sweater. Temperature-regulating, breathable, and machine washable.',
        'short_desc'    => 'Machine-washable fine merino wool sweater.',
        'attributes'    => [
            'color' => ['Charcoal', 'Camel', 'Forest Green'],
            'size'  => ['S', 'M', 'L', 'XL'],
        ],
        'weight'        => '0.45',
    ],
    [
        'name'          => 'Canvas Low-Top Sneaker',
        'type'          => 'variable',
        'category'      => 'Shoes',
        'sku'           => 'SHO-SN-001',
        'regular_price' => '75.00',
        'stock'         => 100,
        'description'   => 'Classic canvas low-top sneaker with vulcanized rubber sole. Lightweight and breathable for all-day comfort.',
        'short_desc'    => 'Classic canvas sneaker, vulcanized sole.',
        'attributes'    => [
            'color'     => ['White', 'Black', 'Navy'],
            'shoe_size' => ['38', '39', '40', '41', '42', '43', '44', '45'],
        ],
        'weight'        => '0.6',
    ],
    [
        'name'          => 'Chelsea Leather Boots',
        'type'          => 'variable',
        'category'      => 'Shoes',
        'sku'           => 'SHO-BT-001',
        'regular_price' => '220.00',
        'stock'         => 40,
        'description'   => 'Hand-finished leather Chelsea boots with elastic side panels and pull tabs. Goodyear-welted leather sole.',
        'short_desc'    => 'Goodyear-welted leather Chelsea boots.',
        'attributes'    => [
            'color'     => ['Tan', 'Black'],
            'shoe_size' => ['40', '41', '42', '43', '44', '45'],
        ],
        'weight'        => '1.0',
    ],
];

// ══════════════════════════════════════════════════
// 5. Create / Update Products (Convergent Idempotency)
// ══════════════════════════════════════════════════
WP_CLI::log('👗 Creating/updating demo products...');

foreach ($demo_products as $product_data) {
    // Find existing product by SKU or title.
    $existing_id = wc_get_product_id_by_sku($product_data['sku']);
    if (!$existing_id) {
        $found = get_page_by_title($product_data['name'], OBJECT, 'product');
        $existing_id = ($found && $found->ID) ? $found->ID : 0;
    }

    $is_new = !$existing_id;

    if ($existing_id) {
        $product = wc_get_product($existing_id);
        if (!$product) {
            WP_CLI::error("FAIL: Product ID {$existing_id} exists but wc_get_product returned null for '{$product_data['name']}'.");
        }
        // Correct product type if needed: delete old product + variations, create new.
        $current_type = $product->get_type();
        $target_type  = $product_data['type'];
        if ($current_type !== $target_type) {
            WP_CLI::log("  ⚠ Type mismatch for '{$product_data['name']}': {$current_type} → {$target_type}. Deleting old product and recreating.");
            // Delete all existing variations first.
            if ($current_type === 'variable') {
                foreach ($product->get_children() as $child_id) {
                    $child = wc_get_product($child_id);
                    if ($child) {
                        $child->delete(true);
                    }
                }
            }
            // Delete the old product post (force, no trash).
            $product->delete(true);
            // Create fresh product.
            $product = ($target_type === 'variable')
                ? new WC_Product_Variable()
                : new WC_Product_Simple();
            $is_new = true;
        }
    } else {
        $product = ($product_data['type'] === 'variable')
            ? new WC_Product_Variable()
            : new WC_Product_Simple();
    }

    // ── Set / correct core fields ──
    $product->set_name($product_data['name']);
    $product->set_status('publish');
    $product->set_catalog_visibility('visible');
    $product->set_sku($product_data['sku']);
    $product->set_description($product_data['description']);
    $product->set_short_description($product_data['short_desc']);
    $product->set_regular_price($product_data['regular_price']);
    $product->set_manage_stock(true);
    $product->set_stock_quantity((int) $product_data['stock']);
    $product->set_stock_status('instock');

    if (isset($product_data['weight'])) {
        $product->set_weight($product_data['weight']);
    }
    if (isset($product_data['dimensions'])) {
        $product->set_length($product_data['dimensions']['length']);
        $product->set_width($product_data['dimensions']['width']);
        $product->set_height($product_data['dimensions']['height']);
    }

    // ── Set / correct category ──
    $cat_id = $category_ids[$product_data['category']] ?? 0;
    if ($cat_id) {
        $product->set_category_ids([$cat_id]);
    }

    // ── Set / correct image ──
    $color_key = $product_data['color'] ?? null;
    if ($color_key && isset($placeholder_images[$color_key])) {
        $product->set_image_id($placeholder_images[$color_key]);
    } elseif (!empty($placeholder_images)) {
        $product->set_image_id(reset($placeholder_images));
    }

    // ── Set / correct attributes for variable products ──
    if ($product_data['type'] === 'variable' && isset($product_data['attributes'])) {
        $attributes = [];
        foreach ($product_data['attributes'] as $attr_key => $values) {
            $taxonomy = 'pa_' . $attr_key;
            $attr = new WC_Product_Attribute();
            $attr->set_name($taxonomy);
            $attr->set_visible(true);
            $attr->set_variation(true);

            $term_ids = [];
            foreach ($values as $val) {
                $term = get_term_by('name', $val, $taxonomy);
                if ($term) {
                    $term_ids[] = $term->term_id;
                } else {
                    WP_CLI::warning("  Term '{$val}' not found in {$taxonomy} for '{$product_data['name']}'.");
                }
            }
            $attr->set_options($term_ids);
            $attributes[$taxonomy] = $attr;
        }
        $product->set_attributes($attributes);
    }

    $product->save();
    _assert_product_saved($product, "save product: {$product_data['name']}");

    // ── Create / update variations for variable products ──
    if ($product_data['type'] === 'variable' && isset($product_data['attributes'])) {
        $color_attr  = $product_data['attributes']['color'] ?? [];
        $size_attr   = $product_data['attributes']['size'] ?? [];
        $shoe_attr   = $product_data['attributes']['shoe_size'] ?? [];

        $size_values   = !empty($size_attr) ? $size_attr : $shoe_attr;
        $size_taxonomy = !empty($size_attr) ? 'pa_size' : 'pa_shoe_size';

        $expected_var_count = count($color_attr) * count($size_values);
        $existing_variations = $product->get_children();
        $created_or_existing = 0;

        foreach ($color_attr as $color) {
            foreach ($size_values as $size) {
                $var_sku = $product_data['sku'] . '-' . sanitize_title($color) . '-' . sanitize_title($size);

                // Check if variation already exists by SKU.
                $found = false;
                foreach ($existing_variations as $var_id) {
                    $var = wc_get_product($var_id);
                    if ($var && $var->get_sku() === $var_sku) {
                        $found = true;
                        // Convergent: correct price and stock.
                        $var->set_regular_price($product_data['regular_price']);
                        $per_var_stock = max(1, intdiv($product_data['stock'], $expected_var_count));
                        $var->set_stock_quantity($per_var_stock);
                        $var->set_stock_status('instock');
                        $var->set_manage_stock(true);
                        if (isset($placeholder_images[$color])) {
                            $var->set_image_id($placeholder_images[$color]);
                        }
                        $var->save();
                        _assert_product_saved($var, "update variation: {$var_sku}");
                        break;
                    }
                }

                if (!$found) {
                    $variation = new WC_Product_Variation();
                    $variation->set_parent_id($product->get_id());
                    $variation->set_sku($var_sku);
                    $variation->set_regular_price($product_data['regular_price']);
                    $per_var_stock = max(1, intdiv($product_data['stock'], $expected_var_count));
                    $variation->set_stock_quantity($per_var_stock);
                    $variation->set_stock_status('instock');
                    $variation->set_manage_stock(true);

                    $variation_attributes = [];
                    $color_term = get_term_by('name', $color, 'pa_color');
                    if ($color_term) {
                        $variation_attributes['pa_color'] = $color_term->slug;
                    }
                    $size_term = get_term_by('name', $size, $size_taxonomy);
                    if ($size_term) {
                        $variation_attributes[$size_taxonomy] = $size_term->slug;
                    }
                    $variation->set_attributes($variation_attributes);

                    if (isset($placeholder_images[$color])) {
                        $variation->set_image_id($placeholder_images[$color]);
                    }

                    $variation->save();
                    _assert_product_saved($variation, "create variation: {$var_sku}");
                }
                $created_or_existing++;
            }
        }

        // Reload children after potential creation.
        $product->save();

        // ── Delete stale variations (not in expected SKU set) ──
        $expected_skus = [];
        foreach ($color_attr as $color) {
            foreach ($size_values as $size) {
                $expected_skus[] = $product_data['sku'] . '-' . sanitize_title($color) . '-' . sanitize_title($size);
            }
        }
        $stale_count = 0;
        foreach ($product->get_children() as $child_id) {
            $child = wc_get_product($child_id);
            if ($child && !in_array($child->get_sku(), $expected_skus, true)) {
                $child->delete(true);
                $stale_count++;
            }
        }
        if ($stale_count > 0) {
            WP_CLI::log("  🗑 Deleted {$stale_count} stale variation(s) for '{$product_data['name']}'.");
            $product->save();
        }

        $actual_var_count = count($product->get_children());
        if ($actual_var_count !== $expected_var_count) {
            WP_CLI::error("Variation count mismatch for '{$product_data['name']}': expected {$expected_var_count}, got {$actual_var_count}.");
        }

        if ($is_new) {
            WP_CLI::log("  ✅ Created: {$product_data['name']} (variations: {$actual_var_count}/{$expected_var_count})");
        } else {
            WP_CLI::log("  ✓ Updated: {$product_data['name']} (variations: {$actual_var_count}/{$expected_var_count})");
        }
    } else {
        if ($is_new) {
            WP_CLI::log("  ✅ Created: {$product_data['name']}");
        } else {
            WP_CLI::log("  ✓ Updated: {$product_data['name']}");
        }
    }
}

// ══════════════════════════════════════════════════
// 6. Post-run Verification
// ══════════════════════════════════════════════════
WP_CLI::log('');
WP_CLI::log('🔍 Post-run verification...');

// Verify product count.
$product_count = wp_count_posts('product');
$total_products = (int) ($product_count->publish ?? 0) + (int) ($product_count->draft ?? 0);
if ($total_products < count($demo_products)) {
    WP_CLI::error("Expected >= " . count($demo_products) . " products, found {$total_products}.");
}
WP_CLI::log("  ✅ Products: {$total_products} (expected >= " . count($demo_products) . ")");

// Verify categories.
foreach ($categories as $name => $desc) {
    if (!term_exists($name, 'product_cat')) {
        WP_CLI::error("Category '{$name}' missing after init.");
    }
}
WP_CLI::log("  ✅ Categories: " . count($categories) . " verified");

// Verify attributes.
foreach ($attributes_def as $key => $attr) {
    $taxonomies = wc_get_attribute_taxonomies();
    $slug_name = str_replace('pa_', '', $attr['slug']);
    $found = false;
    foreach ($taxonomies as $tax) {
        if ($tax->attribute_name === $slug_name) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        WP_CLI::error("Attribute '{$attr['name']}' missing after init.");
    }
}
WP_CLI::log("  ✅ Attributes: " . count($attributes_def) . " verified");

// Verify terms for each attribute.
foreach ($attributes_def as $key => $attr) {
    $terms = get_terms([
        'taxonomy'   => $attr['slug'],
        'hide_empty' => false,
    ]);
    if (is_wp_error($terms)) {
        WP_CLI::error("Cannot get terms for {$attr['slug']}: " . $terms->get_error_message());
    }
    $expected = count($attr['values']);
    $actual = count($terms);
    if ($actual < $expected) {
        WP_CLI::error("Terms for {$attr['slug']}: expected {$expected}, got {$actual}.");
    }
}

// Verify exact product count (no duplicates from repeated runs).
if ($total_products !== count($demo_products)) {
    WP_CLI::error("Product count mismatch: expected exactly " . count($demo_products) . ", got {$total_products}. Possible duplicates from repeated runs.");
}

// Verify variable products have correct variation counts.
foreach ($demo_products as $pd) {
    if ($pd['type'] !== 'variable' || !isset($pd['attributes'])) {
        continue;
    }
    $pid = wc_get_product_id_by_sku($pd['sku']);
    if (!$pid) {
        WP_CLI::error("Cannot find product by SKU '{$pd['sku']}' for variation verification.");
    }
    $product = wc_get_product($pid);
    if (!$product || $product->get_type() !== 'variable') {
        WP_CLI::error("Product '{$pd['name']}' is not variable.");
    }
    $color_attr  = $pd['attributes']['color'] ?? [];
    $size_attr   = $pd['attributes']['size'] ?? [];
    $shoe_attr   = $pd['attributes']['shoe_size'] ?? [];
    $size_values = !empty($size_attr) ? $size_attr : $shoe_attr;
    $expected = count($color_attr) * count($size_values);
    $actual = count($product->get_children());
    if ($actual !== $expected) {
        WP_CLI::error("Variations for '{$pd['name']}': expected {$expected}, got {$actual}.");
    }
    WP_CLI::log("  ✅ '{$pd['name']}': {$actual}/{$expected} variations");
}

// ══════════════════════════════════════════════════
// 7. Final cleanup & status
// ══════════════════════════════════════════════════
flush_rewrite_rules();

WP_CLI::log('');
WP_CLI::success("Demo data initialized and verified. Total products: {$total_products}");
WP_CLI::log('');
WP_CLI::log('Run this script again anytime — it is convergently idempotent.');
