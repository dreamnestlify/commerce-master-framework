<?php
/**
 * Block Patterns Registration
 *
 * Registers custom block patterns for the homepage and reusable sections.
 * Patterns are defined as PHP files that return block markup.
 *
 * @package CommerceBlockTheme
 */

declare(strict_types=1);

namespace CommerceBlockTheme;

class BlockPatterns
{
    /**
     * Register all theme block patterns.
     */
    public static function register(): void
    {
        // Register pattern category.
        if (function_exists('register_block_pattern_category')) {
            register_block_pattern_category('commerce-fse', [
                'label' => __('Commerce Master', 'commerce-block-theme'),
            ]);
        }

        $patterns = [
            'announcement-bar',
            'fashion-header',
            'hero',
            'category-grid',
            'new-arrivals',
            'editorial-campaign',
            'product-collection',
            'benefits-strip',
            'newsletter',
            'footer-info',
        ];

        foreach ($patterns as $pattern) {
            $file = get_template_directory() . '/patterns/' . $pattern . '.php';

            if (file_exists($file)) {
                $pattern_data = include $file;

                if (is_array($pattern_data)) {
                    register_block_pattern('commerce-fse/' . $pattern, $pattern_data);
                }
            }
        }
    }
}
