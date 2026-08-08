<?php
/**
 * Pattern: Announcement Bar
 *
 * @package CommerceBlockTheme
 */

declare(strict_types=1);

return [
    'title'       => __('Announcement Bar', 'commerce-block-theme'),
    'description' => __('A top announcement bar for promotions, shipping notices, etc.', 'commerce-block-theme'),
    'categories'  => ['commerce-fse'],
    'content'     => '<!-- wp:group {"tagName":"div","className":"announcement-bar","layout":{"type":"constrained"}} -->
<div class="wp-block-group announcement-bar">
    <!-- wp:paragraph {"align":"center","className":"announcement-text"} -->
    <p class="has-text-align-center announcement-text">Free express shipping on orders over $50 — Shop Now</p>
    <!-- /wp:paragraph -->
</div>
<!-- /wp:group -->',
];
