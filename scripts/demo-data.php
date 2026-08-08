<?php
/**
 * Commerce Master — Demo Product Data Initialization
 *
 * Runs via: wp eval-file scripts/demo-data.php
 * Idempotent: safe to run multiple times. Uses post/term existence checks.
 *
 * Creates:
 * - Product attributes: Color, Size, Shoe Size
 * - Product categories: Women, Men, Shoes, Accessories
 * - Demo products:
 *   - Simple: tote bag, leather belt, silk scarf, sunglasses
 *   - Variable (apparel): cotton t-shirt, linen dress, denim jacket (color + size)
 *   - Variable (shoes): canvas sneaker, leather boot (color + shoe size)
 *
 * Uses pure-color placeholder images generated locally. No third-party assets.
 *
 * @package CommerceMaster
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit; // Should only run within WP-CLI / WordPress context.
}

// Ensure WooCommerce is active.
if (!class_exists('WooCommerce')) {
    WP_CLI::error('WooCommerce is not active. Install and activate WooCommerce first.');
}

/**
 * Demo data definition.
 */
$demo_products = [
    // ── Simple Products: Accessories ──
    [
        'name'       => 'Classic Leather Tote Bag',
        'type'       => 'simple',
        'category'   => 'Accessories',
        'sku'        => 'ACC-TOTE-001',
        'price'      => '89.00',
        'regular_price' => '89.00',
        'stock'      => 50,
        'color'      => 'black',
        'description' => 'A timeless leather tote bag with spacious interior and premium hardware. Perfect for everyday use.',
        'short_desc' => 'Premium leather tote bag for everyday elegance.',
        'weight'     => '0.8',
        'dimensions' => ['length' => '40', 'width' => '30', 'height' => '15'],
    ],
    [
        'name'       => 'Reversible Leather Belt',
        'type'       => 'simple',
        'category'   => 'Accessories',
        'sku'        => 'ACC-BELT-001',
        'price'      => '45.00',
        'regular_price' => '45.00',
        'stock'      => 80,
        'color'      => 'brown',
        'description' => 'A versatile reversible leather belt with brushed steel buckle. Black on one side, brown on the other.',
        'short_desc' => 'Two looks in one — reversible leather belt.',
        'weight'     => '0.3',
        'dimensions' => ['length' => '110', 'width' => '3.5', 'height' => '0.5'],
    ],
    [
        'name'       => 'Silk Scarf — Floral Print',
        'type'       => 'simple',
        'category'   => 'Accessories',
        'sku'        => 'ACC-SCARF-001',
        'price'      => '65.00',
        'regular_price' => '65.00',
        'stock'      => 120,
        'color'      => 'cream',
        'description' => 'Luxurious 100% silk scarf with a delicate floral print. Lightweight and soft to the touch.',
        'short_desc' => '100% silk floral print scarf.',
        'weight'     => '0.1',
        'dimensions' => ['length' => '90', 'width' => '90', 'height' => '0.1'],
    ],
    [
        'name'       => 'Polarized Sunglasses',
        'type'       => 'simple',
        'category'   => 'Accessories',
        'sku'        => 'ACC-SUN-001',
        'price'      => '120.00',
        'regular_price' => '120.00',
        'stock'      => 60,
        'color'      => 'black',
        'description' => 'UV400 polarized sunglasses with lightweight acetate frame. Includes protective case.',
        'short_desc' => 'UV400 polarized sunglasses with case.',
        'weight'     => '0.05',
        'dimensions' => ['length' => '15', 'width' => '14', 'height' => '4'],
    ],

    // ── Variable Products: Apparel (Women) ──
    [
        'name'       => 'Organic Cotton T-Shirt',
        'type'       => 'variable',
        'category'   => 'Women',
        'sku'        => 'WOM-TS-001',
        'price'      => '29.00',
        'stock'      => 200,
        'description' => 'Soft organic cotton t-shirt with a relaxed fit. Pre-washed for minimal shrinkage. Ethically made.',
        'short_desc' => 'Organic cotton tee — soft, relaxed, sustainable.',
        'attributes' => [
            'color' => ['Black', 'White', 'Sage Green', 'Navy'],
            'size'  => ['XS', 'S', 'M', 'L', 'XL'],
        ],
        'weight'     => '0.2',
    ],
    [
        'name'       => 'Linen Wrap Dress',
        'type'       => 'variable',
        'category'   => 'Women',
        'sku'        => 'WOM-DR-001',
        'price'      => '145.00',
        'stock'      => 80,
        'description' => 'Elegant linen wrap dress with adjustable waist tie. Breathable fabric perfect for warm weather. Knee-length.',
        'short_desc' => 'Breathable linen wrap dress with waist tie.',
        'attributes' => [
            'color' => ['Natural', 'Terracotta', 'Olive'],
            'size'  => ['XS', 'S', 'M', 'L'],
        ],
        'weight'     => '0.35',
    ],

    // ── Variable Products: Apparel (Men) ──
    [
        'name'       => 'Selvedge Denim Jacket',
        'type'       => 'variable',
        'category'   => 'Men',
        'sku'        => 'MEN-JK-001',
        'price'      => '185.00',
        'stock'      => 60,
        'description' => 'Japanese selvedge denim jacket with copper rivets and button fly. Raw, unwashed — breaks in beautifully over time.',
        'short_desc' => 'Japanese selvedge denim jacket — ages with character.',
        'attributes' => [
            'color' => ['Indigo', 'Black', 'Washed Blue'],
            'size'  => ['S', 'M', 'L', 'XL', 'XXL'],
        ],
        'weight'     => '0.7',
    ],
    [
        'name'       => 'Merino Wool Knit Sweater',
        'type'       => 'variable',
        'category'   => 'Men',
        'sku'        => 'MEN-KN-001',
        'price'      => '125.00',
        'stock'      => 70,
        'description' => 'Fine-gauge merino wool crew neck sweater. Temperature-regulating, breathable, and machine washable.',
        'short_desc' => 'Machine-washable fine merino wool sweater.',
        'attributes' => [
            'color' => ['Charcoal', 'Camel', 'Forest Green'],
            'size'  => ['S', 'M', 'L', 'XL'],
        ],
        'weight'     => '0.45',
    ],

    // ── Variable Products: Shoes ──
    [
        'name'       => 'Canvas Low-Top Sneaker',
        'type'       => 'variable',
        'category'   => 'Shoes',
        'sku'        => 'SHO-SN-001',
        'price'      => '75.00',
        'stock'      => 100,
        'description' => 'Classic canvas low-top sneaker with vulcanized rubber sole. Lightweight and breathable for all-day comfort.',
        'short_desc' => 'Classic canvas sneaker, vulcanized sole.',
        'attributes' => [
            'color' => ['White', 'Black', 'Navy'],
            'shoe_size' => ['38', '39', '40', '41', '42', '43', '44', '45'],
        ],
        'weight'     => '0.6',
    ],
    [
        'name'       => 'Chelsea Leather Boots',
        'type'       => 'variable',
        'category'   => 'Shoes',
        'sku'        => 'SHO-BT-001',
        'price'      => '220.00',
        'stock'      => 40,
        'description' => 'Hand-finished leather Chelsea boots with elastic side panels and pull tabs. Goodyear-welted leather sole.',
        'short_desc' => 'Goodyear-welted leather Chelsea boots.',
        'attributes' => [
            'color' => ['Tan', 'Black'],
            'shoe_size' => ['40', '41', '42', '43', '44', '45'],
        ],
        'weight'     => '1.0',
    ],
];

/**
 * ────────────────────────────────────────────
 * 1. Create Product Categories
 * ────────────────────────────────────────────
 */
WP_CLI::log('📁 Creating product categories...');

$categories = [
    'Women'      => 'Women\'s apparel, including dresses, tops, and knitwear.',
    'Men'        => 'Men\'s apparel, including shirts, jackets, and knitwear.',
    'Shoes'      => 'Footwear for men and women — sneakers, boots, and more.',
    'Accessories' => 'Bags, belts, scarves, sunglasses, and other accessories.',
];

foreach ($categories as $name => $desc) {
    $existing = term_exists($name, 'product_cat');

    if (empty($existing)) {
        $term = wp_insert_term($name, 'product_cat', [
            'description' => $desc,
            'slug'        => sanitize_title($name),
        ]);

        if (!is_wp_error($term)) {
            WP_CLI::log("  ✅ Created: $name");
        }
    } else {
        WP_CLI::log("  ✓ Exists: $name");
    }
}

/**
 * ────────────────────────────────────────────
 * 2. Create Product Attributes
 * ────────────────────────────────────────────
 */
WP_CLI::log('🏷️  Creating product attributes...');

$attributes = [
    'color' => [
        'name'       => 'Color',
        'slug'       => 'pa_color',
        'type'       => 'select',
        'order_by'   => 'menu_order',
        'values'     => ['Black', 'White', 'Sage Green', 'Navy', 'Natural', 'Terracotta', 'Olive', 'Indigo', 'Washed Blue', 'Charcoal', 'Camel', 'Forest Green', 'Brown', 'Cream', 'Tan'],
    ],
    'size' => [
        'name'       => 'Size',
        'slug'       => 'pa_size',
        'type'       => 'select',
        'order_by'   => 'menu_order',
        'values'     => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
    ],
    'shoe_size' => [
        'name'       => 'Shoe Size (EU)',
        'slug'       => 'pa_shoe_size',
        'type'       => 'select',
        'order_by'   => 'menu_order',
        'values'     => ['38', '39', '40', '41', '42', '43', '44', '45'],
    ],
];

// Check if attributes already exist.
$existing_attributes = wc_get_attribute_taxonomies();
$existing_slugs = array_column($existing_attributes ?: [], 'attribute_name');

foreach ($attributes as $key => $attr) {
    $slug_name = str_replace('pa_', '', $attr['slug']);

    if (in_array($slug_name, $existing_slugs)) {
        WP_CLI::log("  ✓ Exists: {$attr['name']}");
    } else {
        // Create the attribute.
        $id = wc_create_attribute([
            'name'         => $attr['name'],
            'slug'         => $slug_name,
            'type'         => $attr['type'],
            'order_by'     => $attr['order_by'],
            'has_archives' => true,
        ]);

        if (!is_wp_error($id)) {
            WP_CLI::log("  ✅ Created: {$attr['name']}");
        }
    }

    // Register taxonomy terms (idempotent).
    foreach ($attr['values'] as $value) {
        $term_slug = sanitize_title($value);
        if (!term_exists($value, $attr['slug'])) {
            wp_insert_term($value, $attr['slug'], ['slug' => $term_slug]);
        }
    }
}

// Flush permalinks after attribute creation.
flush_rewrite_rules();

/**
 * ────────────────────────────────────────────
 * 3. Create Placeholder Images (solid color)
 * ────────────────────────────────────────────
 */
WP_CLI::log('🎨 Creating placeholder images...');

/**
 * Generate a solid-color placeholder image and return attachment ID.
 * Uses GD if available, otherwise creates a minimal PNG manually.
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

    $upload_dir = wp_upload_dir();
    $filename = 'placeholder-' . sanitize_title($color_name) . '.png';
    $filepath = trailingslashit($upload_dir['path']) . $filename;

    // Try GD.
    if (function_exists('imagecreate')) {
        $rgb = sscanf($hex, '#%02x%02x%02x');
        $img = imagecreatetruecolor(800, 1000);
        $color = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
        imagefill($img, 0, 0, $color);
        imagepng($img, $filepath);
        imagedestroy($img);
    } else {
        // Minimal PNG fallback (solid color via raw bytes).
        // 1x1 pixel PNG of the color.
        $rgb = sscanf($hex, '#%02x%02x%02x');
        $png_data = pack('C*', 137, 80, 78, 71, 13, 10, 26, 10);
        // IHDR chunk.
        $ihdr = pack('N', 13) . 'IHDR' . pack('NN', 1, 1) . pack('CCCCC', 8, 2, 0, 0, 0);
        $ihdr_crc = crc32('IHDR' . substr($ihdr, 4));
        $png_data .= $ihdr . pack('N', $ihdr_crc);
        // IDAT chunk.
        $raw = chr(0) . chr($rgb[0]) . chr($rgb[1]) . chr($rgb[2]);
        $compressed = gzdeflate($raw);
        $idat = pack('N', strlen($compressed)) . 'IDAT' . $compressed;
        $idat_crc = crc32('IDAT' . $compressed);
        $png_data .= $idat . pack('N', $idat_crc);
        // IEND chunk.
        $png_data .= pack('N', 0) . 'IEND' . pack('N', crc32('IEND'));
        file_put_contents($filepath, $png_data);
    }

    // Insert as attachment.
    $wp_filetype = wp_check_filetype($filename, null);
    $attachment = [
        'post_mime_type' => $wp_filetype['type'] ?? 'image/png',
        'post_title'     => 'placeholder_' . sanitize_title($color_name),
        'post_content'   => '',
        'post_status'    => 'inherit',
    ];

    $attach_id = wp_insert_attachment($attachment, $filepath);

    if ($attach_id && !is_wp_error($attach_id)) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $metadata = wp_generate_attachment_metadata($attach_id, $filepath);
        wp_update_attachment_metadata($attach_id, $metadata);
        return (int) $attach_id;
    }

    return 0;
}

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

$placeholder_images = [];
foreach ($color_map as $color_name => $hex) {
    $id = create_placeholder_image($color_name, $hex);
    if ($id > 0) {
        $placeholder_images[$color_name] = $id;
        WP_CLI::log("  ✅ Image: $color_name ($hex)");
    }
}

/**
 * ────────────────────────────────────────────
 * 4. Create Demo Products
 * ────────────────────────────────────────────
 */
WP_CLI::log('👗 Creating demo products...');

foreach ($demo_products as $product_data) {
    // Check if product already exists (by title or SKU).
    $existing_id = wc_get_product_id_by_sku($product_data['sku']);
    if (!$existing_id) {
        $existing_id = get_page_by_title($product_data['name'], OBJECT, 'product');
        $existing_id = ($existing_id && $existing_id->ID) ? $existing_id->ID : 0;
    }

    if ($existing_id) {
        WP_CLI::log("  ✓ Exists: {$product_data['name']}");
        // Ensure it has the correct category and data.
        $product = wc_get_product($existing_id);
    } else {
        // Create new product.
        $product = ($product_data['type'] === 'variable')
            ? new WC_Product_Variable()
            : new WC_Product_Simple();

        $product->set_name($product_data['name']);
        $product->set_status('publish');
        $product->set_catalog_visibility('visible');
        $product->set_sku($product_data['sku']);
        $product->set_description($product_data['description']);
        $product->set_short_description($product_data['short_desc']);

        if (isset($product_data['regular_price'])) {
            $product->set_regular_price($product_data['regular_price']);
        } else {
            $product->set_regular_price($product_data['price']);
        }

        $product->set_manage_stock(true);
        $product->set_stock_quantity($product_data['stock']);
        $product->set_stock_status('instock');

        if (isset($product_data['weight'])) {
            $product->set_weight($product_data['weight']);
        }

        if (isset($product_data['dimensions'])) {
            $product->set_length($product_data['dimensions']['length']);
            $product->set_width($product_data['dimensions']['width']);
            $product->set_height($product_data['dimensions']['height']);
        }

        // Set category.
        $cat = get_term_by('name', $product_data['category'], 'product_cat');
        if ($cat) {
            $product->set_category_ids([$cat->term_id]);
        }

        // Set placeholder image.
        $color_key = $product_data['color'] ?? null;
        if ($color_key && isset($placeholder_images[ucfirst($color_key)])) {
            $product->set_image_id($placeholder_images[ucfirst($color_key)]);
        } elseif (!empty($placeholder_images)) {
            $first_img = reset($placeholder_images);
            $product->set_image_id($first_img);
        }

        // For variable products, set attributes and create variations.
        if ($product_data['type'] === 'variable' && isset($product_data['attributes'])) {
            // Build attribute data.
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
                    }
                }
                $attr->set_options($term_ids);
                $attributes[$taxonomy] = $attr;
            }
            $product->set_attributes($attributes);
        }

        $product->save();

        // Create variations for variable products.
        if ($product_data['type'] === 'variable' && isset($product_data['attributes'])) {
            $variation_count = 0;

            // Get the attribute combinations.
            $color_attr = $product_data['attributes']['color'] ?? [];
            $size_attr = $product_data['attributes']['size'] ?? [];
            $shoe_attr = $product_data['attributes']['shoe_size'] ?? [];

            $size_values = !empty($size_attr) ? $size_attr : $shoe_attr;
            $size_taxonomy = !empty($size_attr) ? 'pa_size' : 'pa_shoe_size';

            foreach ($color_attr as $color) {
                foreach ($size_values as $size) {
                    // Check if variation already exists.
                    $existing_variations = $product->get_children();
                    $found = false;
                    foreach ($existing_variations as $var_id) {
                        $var = wc_get_product($var_id);
                        if ($var && $var->get_sku() === $product_data['sku'] . '-' . sanitize_title($color) . '-' . sanitize_title($size)) {
                            $found = true;
                            break;
                        }
                    }

                    if ($found) {
                        continue;
                    }

                    $variation = new WC_Product_Variation();
                    $variation->set_parent_id($product->get_id());
                    $variation->set_sku($product_data['sku'] . '-' . sanitize_title($color) . '-' . sanitize_title($size));
                    $variation->set_regular_price($product_data['price']);
                    $variation->set_stock_quantity(intdiv($product_data['stock'], count($color_attr) * count($size_values)) ?: 5);
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

                    // Set placeholder image for this variation.
                    if (isset($placeholder_images[$color])) {
                        $variation->set_image_id($placeholder_images[$color]);
                    }

                    $variation->save();
                    $variation_count++;
                }
            }

            WP_CLI::log("  ✅ Created: {$product_data['name']} (with $variation_count variations)");
        } else {
            WP_CLI::log("  ✅ Created: {$product_data['name']}");
        }
    }
}

/**
 * ────────────────────────────────────────────
 * 5. Final cleanup & status
 * ────────────────────────────────────────────
 */
flush_rewrite_rules();

$product_count = wp_count_posts('product');
$total = (int) ($product_count->publish ?? 0) + (int) ($product_count->draft ?? 0);

WP_CLI::log('');
WP_CLI::success("Demo data initialized. Total products: $total");
WP_CLI::log('');
WP_CLI::log('Run this script again anytime — it is idempotent and will not create duplicates.');
