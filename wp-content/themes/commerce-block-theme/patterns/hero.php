<?php
/**
 * Pattern: Hero
 *
 * Full-bleed hero section with image background, headline, and CTA.
 *
 * @package CommerceBlockTheme
 */

declare(strict_types=1);

return [
    'title'       => __('Hero', 'commerce-block-theme'),
    'description' => __('Full-bleed hero section with headline and call-to-action button.', 'commerce-block-theme'),
    'categories'  => ['commerce-fse'],
    'content'     => '<!-- wp:group {"tagName":"section","className":"hero","layout":{"type":"constrained"}} -->
<section class="wp-block-group hero">
    <!-- wp:group {"className":"hero__content","layout":{"type":"constrained","contentSize":"600px"}} -->
    <div class="wp-block-group hero__content">
        <!-- wp:heading {"level":1,"className":"hero__title"} -->
        <h1 class="wp-block-heading hero__title">New Season, New Wardrobe</h1>
        <!-- /wp:heading -->

        <!-- wp:paragraph {"className":"hero__subtitle"} -->
        <p class="hero__subtitle">Discover the latest arrivals in fashion. Curated collections for the modern wardrobe.</p>
        <!-- /wp:paragraph -->

        <!-- wp:buttons -->
        <div class="wp-block-buttons">
            <!-- wp:button -->
            <div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/shop">Shop New Arrivals</a></div>
            <!-- /wp:button -->
        </div>
        <!-- /wp:buttons -->
    </div>
    <!-- /wp:group -->
</section>
<!-- /wp:group -->',
];
