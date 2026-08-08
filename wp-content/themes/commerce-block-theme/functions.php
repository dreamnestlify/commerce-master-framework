<?php
/**
 * Commerce Block Theme — Functions
 *
 * This theme is presentation-only. All business logic lives in the
 * commerce-core plugin. This file registers theme support, block patterns,
 * and minimal frontend assets.
 *
 * @package CommerceBlockTheme
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme setup.
 */
function commerce_block_theme_setup(): void
{
    // Add support for WordPress features.
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('responsive-embeds');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);

    // WooCommerce support.
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');

    // Register theme-specific block patterns.
    require_once get_template_directory() . '/inc/block-patterns.php';
    \CommerceBlockTheme\BlockPatterns::register();
}

add_action('after_setup_theme', 'commerce_block_theme_setup');

/**
 * Enqueue theme assets.
 */
function commerce_block_theme_assets(): void
{
    // Theme stylesheet (via wp_enqueue for potential overrides).
    wp_enqueue_style(
        'commerce-block-theme-style',
        get_stylesheet_uri(),
        [],
        '0.1.0'
    );

    // Theme custom CSS.
    wp_enqueue_style(
        'commerce-block-theme-custom',
        get_template_directory_uri() . '/assets/css/theme.css',
        ['commerce-block-theme-style'],
        '0.1.0'
    );

    // Theme JavaScript.
    wp_enqueue_script(
        'commerce-block-theme-script',
        get_template_directory_uri() . '/assets/js/theme.js',
        [],
        '0.1.0',
        true
    );

    // Pass data to JavaScript.
    wp_localize_script('commerce-block-theme-script', 'commerceThemeData', [
        'ajaxUrl'   => admin_url('admin-ajax.php'),
        'nonce'     => wp_create_nonce('commerce_theme_nonce'),
        'isMobile'  => wp_is_mobile(),
    ]);
}

add_action('wp_enqueue_scripts', 'commerce_block_theme_assets');

/**
 * Add editor assets.
 */
function commerce_block_theme_editor_assets(): void
{
    wp_enqueue_style(
        'commerce-block-theme-editor',
        get_template_directory_uri() . '/assets/css/editor.css',
        [],
        '0.1.0'
    );
}

add_action('enqueue_block_editor_assets', 'commerce_block_theme_editor_assets');

/**
 * Add WCAG accessibility enhancements.
 */
function commerce_block_theme_accessibility(): void
{
    // Skip to content link — injected before header.
    add_action('wp_body_open', function (): void {
        printf(
            '<a href="#main-content" class="skip-link screen-reader-text">%s</a>',
            esc_html__('Skip to content', 'commerce-block-theme')
        );
    });
}

add_action('wp_enqueue_scripts', 'commerce_block_theme_accessibility');
