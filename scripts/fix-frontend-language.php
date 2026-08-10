<?php
/**
 * Fix frontend language: set site to en_US, admin to zh_CN,
 * clean up Chinese-English mixed titles on products/pages/menus
 */

// 1. Site language -> English (frontend), admin user locale -> Chinese
delete_option('WPLANG'); // default en_US
update_option('WPLANG', 'en_US');

$users = get_users();
foreach ($users as $user) {
    if (in_array('administrator', (array)$user->roles) || in_array('shop_manager', (array)$user->roles)) {
        update_user_meta($user->ID, 'locale', 'zh_CN');
        echo "Set admin locale zh_CN for user {$user->ID} ({$user->user_login})\n";
    } else {
        update_user_meta($user->ID, 'locale', 'en_US');
    }
}
echo "Site language set to en_US\n";

// 2. Fix jewelry product titles: keep only English part after " — "
$products = get_posts(array(
    'post_type' => 'product',
    'posts_per_page' => -1,
    'post_status' => 'any',
));
$title_map = array();
foreach ($products as $p) {
    $title = $p->post_title;
    if (strpos($title, ' — ') !== false) {
        $parts = explode(' — ', $title, 2);
        $new_title = trim($parts[1]);
    } elseif (preg_match('/[\x{4e00}-\x{9fff}]/u', $title)) {
        // fallback: if any Chinese remains, keep as-is but log
        $new_title = $title;
        echo "WARN: Chinese still in title: {$title}\n";
    } else {
        $new_title = $title;
    }
    if ($new_title !== $title) {
        wp_update_post(array(
            'ID' => $p->ID,
            'post_title' => $new_title,
        ));
        echo "Product {$p->ID}: {$title} -> {$new_title}\n";
    }
}

// 3. Fix page titles with Chinese
$pages = get_posts(array(
    'post_type' => 'page',
    'posts_per_page' => -1,
    'post_status' => 'any',
));
$page_title_map = array(
    '时尚博客 / Fashion Blog' => 'Fashion Blog',
    '服装尺码指南 / Clothing Size Guide' => 'Clothing Size Guide',
);
foreach ($pages as $page) {
    $title = $page->post_title;
    if (isset($page_title_map[$title])) {
        wp_update_post(array(
            'ID' => $page->ID,
            'post_title' => $page_title_map[$title],
        ));
        echo "Page {$page->ID}: {$title} -> {$page_title_map[$title]}\n";
    }
}

// 4. Fix menu item "尺码指南" -> "Size Guide"
$menu_items = wp_get_nav_menu_items('main-menu');
if ($menu_items) {
    foreach ($menu_items as $item) {
        if ($item->title === '尺码指南') {
            wp_update_nav_menu_item($item->menu_item_menu_item_parent ?: 0, $item->ID, array(
                'menu-item-title' => 'Size Guide',
                'menu-item-object-id' => $item->object_id,
                'menu-item-object' => $item->object,
                'menu-item-type' => $item->type,
                'menu-item-status' => 'publish',
            ));
            echo "Menu item {$item->ID}: 尺码指南 -> Size Guide\n";
        }
    }
}

// 5. Flush rewrite rules and caches
flush_rewrite_rules();
wp_cache_flush();
echo "DONE\n";
