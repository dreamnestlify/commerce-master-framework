<?php
/**
 * Commerce Master — Jewelry Product Setup
 *
 * Maps the 48 uploaded jewelry images into 16 cohesive WooCommerce listings.
 * Run on the server with:
 *   wp eval-file scripts/jewelry-product-setup.php
 *
 * Idempotent: safe to run multiple times. Old demo fashion products are removed,
 * jewelry categories are ensured, and each product's images are re-mapped by
 * the original uploaded filename.
 *
 * @package CommerceMaster
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WooCommerce')) {
    WP_CLI::error('WooCommerce is not active.');
}

require_once ABSPATH . 'wp-admin/includes/image.php';

// ═══════════════════════════════════════════════════════════════
// 1. Ensure Jewelry Categories
// ═══════════════════════════════════════════════════════════════
WP_CLI::log('📁 Ensuring jewelry categories...');

$jewelry_categories = [
    'Earrings'    => ['description' => 'Earrings — studs, drops, hoops, and chandeliers.', 'parent' => 0],
    'Necklaces'   => ['description' => 'Necklaces — pendants, chokers, chains, and statement pieces.', 'parent' => 0],
    'Bracelets'   => ['description' => 'Bracelets — bangles, cuffs, tennis bracelets, and chain bracelets.', 'parent' => 0],
    'Rings'       => ['description' => 'Rings — cocktail rings, stacking rings, and bands.', 'parent' => 0],
    'Jewelry Sets'=> ['description' => 'Curated jewelry sets and mixed-piece collections.', 'parent' => 0],
];

$category_ids = [];
foreach ($jewelry_categories as $name => $data) {
    $existing = term_exists($name, 'product_cat');
    if (empty($existing)) {
        $term = wp_insert_term($name, 'product_cat', [
            'description' => $data['description'],
            'slug'        => sanitize_title($name),
        ]);
        _assert_not_wp_error($term, "create category: {$name}");
        $category_ids[$name] = (int) $term['term_id'];
        WP_CLI::log("  ✅ Created: {$name}");
    } else {
        $category_ids[$name] = is_array($existing) ? (int) $existing['term_id'] : (int) $existing;
        WP_CLI::log("  ✓ Exists: {$name}");
    }
}

// ═══════════════════════════════════════════════════════════════
// 2. Remove old fashion demo products
// ═══════════════════════════════════════════════════════════════
WP_CLI::log('🗑 Removing old fashion demo products...');

$old_skus = [
    'ACC-TOTE-001', 'ACC-BELT-001', 'ACC-SCARF-001', 'ACC-SUN-001',
    'WOM-TS-001', 'WOM-DR-001', 'WOM-KC-001', 'WOM-SK-001',
    'MEN-JK-001', 'MEN-KN-001', 'MEN-SH-001',
    'SHO-SN-001', 'SHO-BT-001', 'SHO-LF-001',
    'ACC-BEANIE-001', 'ACC-CH-001',
];

foreach ($old_skus as $sku) {
    $pid = wc_get_product_id_by_sku($sku);
    if ($pid) {
        $product = wc_get_product($pid);
        if ($product) {
            $product->delete(true);
            WP_CLI::log("  🗑 Deleted old product: {$sku}");
        }
    }
}

// ═══════════════════════════════════════════════════════════════
// 3. Helper: find attachment by original filename
// ═══════════════════════════════════════════════════════════════
function find_attachment_by_filename(string $filename): int {
    global $wpdb;

    $like = '%' . $wpdb->esc_like($filename) . '%';
    $attachment_id = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} 
             WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s
             LIMIT 1",
            $like
        )
    );

    if ($attachment_id) {
        return $attachment_id;
    }

    // Fallback: search by post title / guid.
    $title = sanitize_title(pathinfo($filename, PATHINFO_FILENAME));
    $found = get_posts([
        'post_type'      => 'attachment',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        's'              => $title,
    ]);

    return !empty($found) ? (int) $found[0] : 0;
}

function _assert_not_wp_error($result, string $context): void {
    if (is_wp_error($result)) {
        WP_CLI::error(sprintf('FAIL [%s]: %s', $context, $result->get_error_message()));
    }
}

function set_product_images($product, array $filenames): void {
    $ids = [];
    foreach ($filenames as $filename) {
        $id = find_attachment_by_filename($filename);
        if ($id > 0) {
            $ids[] = $id;
        } else {
            WP_CLI::warning("  Attachment not found: {$filename}");
        }
    }

    if (empty($ids)) {
        return;
    }

    $product->set_image_id($ids[0]);
    if (count($ids) > 1) {
        $product->set_gallery_image_ids(array_slice($ids, 1));
    } else {
        $product->set_gallery_image_ids([]);
    }
}

// ═══════════════════════════════════════════════════════════════
// 4. Product Definitions
// ═══════════════════════════════════════════════════════════════
$jewelry_products = [
    // ── Earrings ──
    [
        'sku'           => 'JW-ER-001',
        'name'          => 'Vintage Palace Gemstone Drop Earrings',
        'name_cn'       => '复古宫廷宝石耳坠',
        'category'      => 'Earrings',
        'type'          => 'simple',
        'regular_price' => '128.00',
        'stock'         => 50,
        'description'   => 'Handcrafted vintage palace-style drop earrings featuring rich ruby, amethyst, emerald and pearl accents set in antiqued gold-tone metal. Perfect for evening occasions and statement styling.',
        'short_desc'    => '复古宫廷风耳坠，镶嵌红宝石、紫水晶与祖母绿，华丽晚宴首选。',
        'images'        => ['photo_1_2026-08-09_07-16-27.jpg', 'photo_3_2026-08-09_07-16-27.jpg', 'photo_7_2026-08-09_07-16-27.jpg'],
        'tags'          => ['Featured'],
    ],
    [
        'sku'           => 'JW-ER-002',
        'name'          => 'Oriental Jade & Pearl Earrings',
        'name_cn'       => '东方翡翠珍珠耳饰',
        'category'      => 'Earrings',
        'type'          => 'simple',
        'regular_price' => '98.00',
        'stock'         => 60,
        'description'   => 'Elegant East-Asian inspired earrings combining green jade, luminous pearls and ruby accents in intricate gold filigree. A refined choice for both modern and traditional outfits.',
        'short_desc'    => '东方美学翡翠珍珠耳饰，精致镂空金丝工艺。',
        'images'        => ['photo_4_2026-08-09_07-16-27.jpg', 'photo_8_2026-08-09_07-16-27.jpg', 'photo_41_2026-08-09_07-16-28.jpg'],
        'tags'          => ['New Arrival'],
    ],
    [
        'sku'           => 'JW-ER-003',
        'name'          => 'Diamond & Pearl Teardrop Earrings',
        'name_cn'       => '钻石珍珠水滴耳环',
        'category'      => 'Earrings',
        'type'          => 'simple',
        'regular_price' => '145.00',
        'stock'         => 45,
        'description'   => 'Sparkling teardrop earrings crafted with brilliant-cut crystals and soft freshwater pearls. Silver-tone setting adds a contemporary finish to a timeless silhouette.',
        'short_desc'    => '璀璨水晶与淡水珍珠水滴耳环，优雅百搭。',
        'images'        => ['photo_9_2026-08-09_07-16-27.jpg', 'photo_10_2026-08-09_07-16-27.jpg', 'photo_30_2026-08-09_07-16-27.jpg'],
        'tags'          => ['Featured'],
    ],
    [
        'sku'           => 'JW-ER-004',
        'name'          => 'Modern Sapphire & Emerald Earring Set',
        'name_cn'       => '现代彩宝耳饰套装',
        'category'      => 'Earrings',
        'type'          => 'simple',
        'regular_price' => '115.00',
        'stock'         => 55,
        'description'   => 'A curated set of contemporary earrings showcasing deep sapphire blue, vivid emerald green and clear crystals. Mix-and-match pieces for everyday elegance.',
        'short_desc'    => '现代彩宝耳饰组合，蓝宝石与祖母绿碰撞。',
        'images'        => ['photo_5_2026-08-09_07-16-27.jpg', 'photo_24_2026-08-09_07-16-27.jpg', 'photo_32_2026-08-09_07-16-27.jpg'],
        'tags'          => ['New Arrival'],
    ],

    // ── Necklaces ──
    [
        'sku'           => 'JW-NK-001',
        'name'          => 'Sapphire & Emerald Pendant Necklace',
        'name_cn'       => '蓝宝石祖母绿吊坠项链',
        'category'      => 'Necklaces',
        'type'          => 'simple',
        'regular_price' => '168.00',
        'stock'         => 40,
        'description'   => 'A refined pendant necklace featuring cushion-cut sapphire and emerald stones surrounded by a halo of crystals. Available styling in silver and gold tones.',
        'short_desc'    => '蓝宝石与祖母绿吊坠项链，经典大气。',
        'images'        => ['photo_11_2026-08-09_07-16-27.jpg', 'photo_12_2026-08-09_07-16-27.jpg', 'photo_26_2026-08-09_07-16-27.jpg'],
        'tags'          => ['Featured'],
    ],
    [
        'sku'           => 'JW-NK-002',
        'name'          => 'Vintage Lace Gemstone Necklace',
        'name_cn'       => '复古蕾丝宝石项链',
        'category'      => 'Necklaces',
        'type'          => 'simple',
        'regular_price' => '198.00',
        'stock'         => 30,
        'description'   => 'Statement necklace with ornate lace-like metalwork, crowned with emerald, sapphire and ruby center stones. A showpiece for formal events.',
        'short_desc'    => '复古蕾丝宝石项链，晚宴焦点单品。',
        'images'        => ['photo_27_2026-08-09_07-16-27.jpg', 'photo_29_2026-08-09_07-16-27.jpg', 'photo_45_2026-08-09_07-16-28.jpg'],
        'tags'          => ['Featured'],
    ],
    [
        'sku'           => 'JW-NK-003',
        'name'          => 'Diamond Tennis Chain Necklace',
        'name_cn'       => '钻石网球链项链',
        'category'      => 'Necklaces',
        'type'          => 'simple',
        'regular_price' => '220.00',
        'stock'         => 35,
        'description'   => 'Classic tennis necklace with a continuous line of brilliant stones, plus layered chain accents dotted with sapphire and pearl. Effortlessly chic.',
        'short_desc'    => '经典钻石网球链，叠戴设计更显层次。',
        'images'        => ['photo_14_2026-08-09_07-16-27.jpg', 'photo_28_2026-08-09_07-16-27.jpg', 'photo_42_2026-08-09_07-16-28.jpg'],
        'tags'          => ['New Arrival'],
    ],
    [
        'sku'           => 'JW-NK-004',
        'name'          => 'Baroque Pearl Link Necklace',
        'name_cn'       => '巴洛克珍珠链环项链',
        'category'      => 'Necklaces',
        'type'          => 'simple',
        'regular_price' => '155.00',
        'stock'         => 42,
        'description'   => 'Contemporary link necklace with lustrous baroque pearls set in polished silver-tone rings. Minimalist yet sculptural, ideal for day-to-night wear.',
        'short_desc'    => '巴洛克珍珠与银环项链，极简雕塑感。',
        'images'        => ['photo_25_2026-08-09_07-16-27.jpg', 'photo_43_2026-08-09_07-16-28.jpg', 'photo_44_2026-08-09_07-16-28.jpg'],
        'tags'          => ['New Arrival'],
    ],
    [
        'sku'           => 'JW-SET-001',
        'name'          => 'Pearl Lace Choker & Earring Set',
        'name_cn'       => '珍珠蕾丝Choker耳饰套装',
        'category'      => 'Jewelry Sets',
        'type'          => 'simple',
        'regular_price' => '245.00',
        'stock'         => 25,
        'description'   => 'A romantic set pairing a pearl lace choker with matching drop earrings and a pearl ring. Coordinated elegance for weddings, anniversaries and special occasions.',
        'short_desc'    => '珍珠蕾丝Choker配耳环戒指三件套，浪漫套装。',
        'images'        => ['photo_15_2026-08-09_07-16-27.jpg', 'photo_40_2026-08-09_07-16-28.jpg', 'photo_48_2026-08-09_07-16-28.jpg'],
        'tags'          => ['Featured'],
    ],

    // ── Bracelets ──
    [
        'sku'           => 'JW-BR-001',
        'name'          => 'Gemstone Cuff Bracelet',
        'name_cn'       => '宝石镶嵌宽版手镯',
        'category'      => 'Bracelets',
        'type'          => 'simple',
        'regular_price' => '135.00',
        'stock'         => 38,
        'description'   => 'Bold cuff bracelet set with emerald, sapphire and ruby stones. Antiqued gold finish and carved detailing give it a regal, heirloom-quality look.',
        'short_desc'    => '宝石镶嵌宽版手镯，复古金做旧质感。',
        'images'        => ['photo_13_2026-08-09_07-16-27.jpg', 'photo_16_2026-08-09_07-16-27.jpg', 'photo_18_2026-08-09_07-16-27.jpg'],
        'tags'          => ['Featured'],
    ],
    [
        'sku'           => 'JW-BR-002',
        'name'          => 'Pearl & Gold Bangle Set',
        'name_cn'       => '珍珠黄金手镯套装',
        'category'      => 'Bracelets',
        'type'          => 'simple',
        'regular_price' => '118.00',
        'stock'         => 48,
        'description'   => 'Delicate bangle set in warm gold tones, accented with freshwater pearls and crystal pavé. Wear stacked or individually for versatile styling.',
        'short_desc'    => '珍珠黄金手镯套装，可叠戴可单戴。',
        'images'        => ['photo_2_2026-08-09_07-16-27.jpg', 'photo_19_2026-08-09_07-16-27.jpg', 'photo_47_2026-08-09_07-16-28.jpg'],
        'tags'          => ['New Arrival'],
    ],
    [
        'sku'           => 'JW-BR-003',
        'name'          => 'Modern Minimalist Chain Bracelet',
        'name_cn'       => '现代极简链条手链',
        'category'      => 'Bracelets',
        'type'          => 'simple',
        'regular_price' => '88.00',
        'stock'         => 65,
        'description'   => 'Clean, minimalist chain bracelets in silver, gold and rose gold, each centered with a single sapphire or pearl. Perfect for everyday layering.',
        'short_desc'    => '现代极简链条手链，单颗宝石点缀。',
        'images'        => ['photo_6_2026-08-09_07-16-27.jpg', 'photo_17_2026-08-09_07-16-27.jpg', 'photo_31_2026-08-09_07-16-27.jpg'],
        'tags'          => ['New Arrival'],
    ],

    // ── Rings ──
    [
        'sku'           => 'JW-RG-001',
        'name'          => 'Colored Gemstone Cocktail Rings',
        'name_cn'       => '彩色宝石鸡尾酒戒指',
        'category'      => 'Rings',
        'type'          => 'simple',
        'regular_price' => '108.00',
        'stock'         => 52,
        'description'   => 'Bold cocktail rings featuring emerald, sapphire, ruby and amethyst center stones in ornate gold settings. Designed to stand out at any gathering.',
        'short_desc'    => '彩色宝石鸡尾酒戒指，华丽醒目。',
        'images'        => ['photo_20_2026-08-09_07-16-27.jpg', 'photo_22_2026-08-09_07-16-27.jpg', 'photo_23_2026-08-09_07-16-27.jpg'],
        'tags'          => ['Featured'],
    ],
    [
        'sku'           => 'JW-RG-002',
        'name'          => 'Stacking Ring Collection',
        'name_cn'       => '叠戴戒指组合',
        'category'      => 'Rings',
        'type'          => 'simple',
        'regular_price' => '79.00',
        'stock'         => 70,
        'description'   => 'A curated collection of textured bands, twisted ropes and delicate pearl rings in rose gold, silver and gold. Mix, match and stack your way.',
        'short_desc'    => '叠戴戒指组合，多种材质自由搭配。',
        'images'        => ['photo_33_2026-08-09_07-16-27.jpg', 'photo_35_2026-08-09_07-16-27.jpg', 'photo_36_2026-08-09_07-16-27.jpg'],
        'tags'          => ['New Arrival'],
    ],
    [
        'sku'           => 'JW-RG-003',
        'name'          => 'Pearl & Diamond Ring Collection',
        'name_cn'       => '珍珠钻石戒指系列',
        'category'      => 'Rings',
        'type'          => 'simple',
        'regular_price' => '125.00',
        'stock'         => 46,
        'description'   => 'Romantic rings adorned with freshwater pearls and crystal halos. From teardrop silhouettes to sculptural gold forms, each piece feels like a keepsake.',
        'short_desc'    => '珍珠钻石戒指系列，浪漫收藏价值。',
        'images'        => ['photo_39_2026-08-09_07-16-27.jpg', 'photo_46_2026-08-09_07-16-28.jpg', 'photo_34_2026-08-09_07-16-27.jpg'],
        'tags'          => ['Featured'],
    ],
    [
        'sku'           => 'JW-RG-004',
        'name'          => 'Royal Sapphire & Emerald Rings',
        'name_cn'       => '皇家蓝宝石祖母绿戒指',
        'category'      => 'Rings',
        'type'          => 'simple',
        'regular_price' => '148.00',
        'stock'         => 40,
        'description'   => 'Regal ring designs showcasing deep sapphire blue, vivid emerald green and brilliant diamond-like accents. Available in gold and silver-tone settings.',
        'short_desc'    => '皇家蓝宝石祖母绿戒指，尊贵典雅。',
        'images'        => ['photo_37_2026-08-09_07-16-27.jpg', 'photo_38_2026-08-09_07-16-27.jpg', 'photo_21_2026-08-09_07-16-27.jpg'],
        'tags'          => ['Featured'],
    ],
];

// ═══════════════════════════════════════════════════════════════
// 5. Create / Update Products
// ═══════════════════════════════════════════════════════════════
WP_CLI::log('💎 Creating jewelry products...');

$tag_cache = [];
foreach (['Featured', 'New Arrival', 'Sale'] as $tag_name) {
    $existing = term_exists($tag_name, 'product_tag');
    if (empty($existing)) {
        $term = wp_insert_term($tag_name, 'product_tag', ['slug' => sanitize_title($tag_name)]);
        if (!is_wp_error($term)) {
            $tag_cache[$tag_name] = (int) $term['term_id'];
        }
    } else {
        $tag_cache[$tag_name] = is_array($existing) ? (int) $existing['term_id'] : (int) $existing;
    }
}

$created = 0;
$updated = 0;

foreach ($jewelry_products as $pd) {
    $existing_id = wc_get_product_id_by_sku($pd['sku']);
    $is_new = !$existing_id;

    if ($existing_id) {
        $product = wc_get_product($existing_id);
        if (!$product) {
            WP_CLI::warning("SKU {$pd['sku']} exists but product is invalid. Creating new.");
            $product = new WC_Product_Simple();
            $is_new = true;
        }
    } else {
        $product = new WC_Product_Simple();
    }

    // Title: Chinese name first, English second.
    $product->set_name($pd['name_cn'] . ' — ' . $pd['name']);
    $product->set_status('publish');
    $product->set_catalog_visibility('visible');
    $product->set_sku($pd['sku']);
    $product->set_description($pd['description']);
    $product->set_short_description($pd['short_desc']);
    $product->set_regular_price($pd['regular_price']);
    $product->set_manage_stock(true);
    $product->set_stock_quantity((int) $pd['stock']);
    $product->set_stock_status('instock');

    $cat_id = $category_ids[$pd['category']] ?? 0;
    if ($cat_id) {
        $product->set_category_ids([$cat_id]);
    }

    set_product_images($product, $pd['images']);

    $product->save();

    // Tags.
    $tag_ids = [];
    foreach ($pd['tags'] ?? [] as $tag_name) {
        if (isset($tag_cache[$tag_name])) {
            $tag_ids[] = $tag_cache[$tag_name];
        }
    }
    if (!empty($tag_ids)) {
        wp_set_object_terms($product->get_id(), $tag_ids, 'product_tag', false);
    }

    if ($is_new) {
        $created++;
        WP_CLI::log("  ✅ Created: {$pd['name_cn']} (ID: {$product->get_id()})");
    } else {
        $updated++;
        WP_CLI::log("  ✓ Updated: {$pd['name_cn']} (ID: {$product->get_id()})");
    }
}

// ═══════════════════════════════════════════════════════════════
// 6. Flush permalinks & summary
// ═══════════════════════════════════════════════════════════════
flush_rewrite_rules();

WP_CLI::log('');
WP_CLI::success("Jewelry setup complete. Created: {$created}, Updated: {$updated}, Total: " . count($jewelry_products));
WP_CLI::log('');
WP_CLI::log('Product URLs (frontend):');
foreach ($jewelry_products as $pd) {
    $pid = wc_get_product_id_by_sku($pd['sku']);
    if ($pid) {
        WP_CLI::log('  ' . get_permalink($pid));
    }
}
