<?php
/**
 * Upload logos and configure branding
 */

require_once( ABSPATH . 'wp-admin/includes/media.php' );
require_once( ABSPATH . 'wp-admin/includes/file.php' );
require_once( ABSPATH . 'wp-admin/includes/image.php' );

$upload_dir = wp_upload_dir();
$base_path = $upload_dir['basedir'];

$logos = array(
    'zalandy-icon.jpg' => 'Zalandy Icon',
    'zalandy-lockup.jpg' => 'Zalandy Logo Light',
    'zalandy-lockup-dark.jpg' => 'Zalandy Logo Dark',
);

$media_ids = array();
foreach ($logos as $file => $title) {
    $file_path = $base_path . '/' . $file;
    if (!file_exists($file_path)) {
        echo "File not found: {$file_path}\n";
        continue;
    }

    $existing = get_page_by_title($title, OBJECT, 'attachment');
    if ($existing) {
        $media_ids[$file] = $existing->ID;
        echo "Already exists: {$title} => ID {$existing->ID}\n";
        continue;
    }

    $file_array = array(
        'name' => $file,
        'tmp_name' => $file_path,
    );

    $id = media_handle_sideload($file_array, 0, $title);
    if (is_wp_error($id)) {
        echo "Error uploading {$file}: " . $id->get_error_message() . "\n";
    } else {
        $media_ids[$file] = $id;
        echo "Uploaded {$title} => ID {$id}\n";
    }
}

// Set site icon (favicon) to icon logo
if (isset($media_ids['zalandy-icon.jpg'])) {
    update_option('site_icon', $media_ids['zalandy-icon.jpg']);
    echo "Set site icon to icon logo\n";
}

// Set Woostify logo to light lockup
if (isset($media_ids['zalandy-lockup.jpg'])) {
    set_theme_mod('custom_logo', $media_ids['zalandy-lockup.jpg']);
    echo "Set Woostify custom logo to light lockup\n";
}

// Also store dark logo for sticky header / mobile use
if (isset($media_ids['zalandy-lockup-dark.jpg'])) {
    update_option('zalandy_logo_dark_id', $media_ids['zalandy-lockup-dark.jpg']);
    echo "Stored dark logo ID\n";
}

// Update brand colors to match Zalandy orange (#FF6B00)
$brand_color = '#FF6B00';
$brand_hover = '#E65F00';

// Woostify theme options
set_theme_mod('primary_color', $brand_color);
set_theme_mod('link_color', $brand_color);
set_theme_mod('link_hover_color', $brand_hover);
set_theme_mod('button_color', $brand_color);
set_theme_mod('button_hover_color', $brand_hover);

// Store colors for footer mu-plugin
update_option('zalandy_brand_color', $brand_color);
update_option('zalandy_brand_hover', $brand_hover);

echo "Updated brand colors to {$brand_color}\n";
echo "DONE\n";
