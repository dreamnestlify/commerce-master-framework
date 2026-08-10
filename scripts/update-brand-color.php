<?php
/**
 * Update brand colors to Zalandy orange
 * This is a targeted update, not a full redesign
 */

$brand = '#FF6B00';
$hover = '#E65F00';
$light = '#FFF0E6';

// Woostify theme colors
set_theme_mod( 'woostify_color_primary', $brand );
set_theme_mod( 'woostify_link_color', $brand );
set_theme_mod( 'woostify_link_hover_color', $hover );
set_theme_mod( 'woostify_button_background', $brand );
set_theme_mod( 'woostify_button_hover_background', $hover );
set_theme_mod( 'woostify_footer_link_color', $brand );

// Update custom CSS stored by site-redesign.php
$css_post = get_page_by_title( 'zalandy-custom-css', OBJECT, 'custom_css' );
if ( $css_post ) {
    $css = $css_post->post_content;
    $css = str_replace( '#c9a96e', $brand, $css );
    $css = str_replace( '#b8975a', $hover, $css );
    $css = str_replace( '#a0874a', $hover, $css );
    $css = str_replace( '#1a1a1a', '#0F0F1A', $css );
    wp_update_post( array(
        'ID' => $css_post->ID,
        'post_content' => $css,
    ) );
}

// Also update theme_mod custom_css_post
$theme_css = get_theme_mod( 'custom_css_post', '' );
if ( $theme_css ) {
    $theme_css = str_replace( '#c9a96e', $brand, $theme_css );
    $theme_css = str_replace( '#b8975a', $hover, $theme_css );
    $theme_css = str_replace( '#a0874a', $hover, $theme_css );
    set_theme_mod( 'custom_css_post', $theme_css );
}

// Update footer HTML colors
$footer = get_option( 'zalandy_custom_footer', '' );
if ( $footer ) {
    $footer = str_replace( '#c9a96e', $brand, $footer );
    $footer = str_replace( '#b8975a', $hover, $footer );
    update_option( 'zalandy_custom_footer', $footer );
}

// Update cookie consent colors
$cookie = get_option( 'zalandy_cookie_consent_html', '' );
if ( $cookie ) {
    $cookie = str_replace( '#c9a96e', $brand, $cookie );
    $cookie = str_replace( '#b8975a', $hover, $cookie );
    update_option( 'zalandy_cookie_consent_html', $cookie );
}

// Update admin UI option colors
update_option( 'zalandy_brand_color', $brand );
update_option( 'zalandy_brand_hover', $hover );

echo "Brand colors updated to {$brand}\n";
