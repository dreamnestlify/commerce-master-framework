<?php
/**
 * Pattern: Editorial Campaign
 *
 * A lookbook / editorial block with image and text overlay.
 *
 * @package CommerceBlockTheme
 */

declare(strict_types=1);

return [
    'title'       => __('Editorial Campaign', 'commerce-block-theme'),
    'description' => __('Editorial campaign block with large image and text overlay.', 'commerce-block-theme'),
    'categories'  => ['commerce-fse'],
    'content'     => '<!-- wp:group {"tagName":"section","className":"editorial-campaign","layout":{"type":"constrained"}} -->
<section class="wp-block-group editorial-campaign">
    <!-- wp:columns {"className":"editorial-campaign__columns"} -->
    <div class="wp-block-columns editorial-campaign__columns">
        <!-- wp:column {"width":"60%","className":"editorial-campaign__image"} -->
        <div class="wp-block-column editorial-campaign__image" style="flex-basis:60%">
            <!-- wp:group {"className":"editorial-campaign__image-block","style":{"spacing":{"padding":{"top":"0","right":"0","bottom":"0","left":"0"}}}} -->
            <div class="wp-block-group editorial-campaign__image-block" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0">
                <!-- wp:paragraph {"className":"placeholder-image"} -->
                <p class="placeholder-image">[Editorial Image Placeholder]</p>
                <!-- /wp:paragraph -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"width":"40%","className":"editorial-campaign__text","verticalAlignment":"center"} -->
        <div class="wp-block-column editorial-campaign__text" style="flex-basis:40%">
            <!-- wp:group {"className":"editorial-campaign__text-content","style":{"spacing":{"padding":{"top":"3rem","right":"3rem","bottom":"3rem","left":"3rem"}}}} -->
            <div class="wp-block-group editorial-campaign__text-content" style="padding-top:3rem;padding-right:3rem;padding-bottom:3rem;padding-left:3rem">
                <!-- wp:paragraph {"className":"editorial-campaign__label"} -->
                <p class="editorial-campaign__label">Featured Collection</p>
                <!-- /wp:paragraph -->

                <!-- wp:heading {"level":2,"className":"editorial-campaign__title"} -->
                <h2 class="wp-block-heading editorial-campaign__title">Autumn Edit</h2>
                <!-- /wp:heading -->

                <!-- wp:paragraph {"className":"editorial-campaign__description"} -->
                <p class="editorial-campaign__description">Discover this season\'s most-wanted pieces, from oversized blazers to statement knits. Effortless style for every occasion.</p>
                <!-- /wp:paragraph -->

                <!-- wp:buttons -->
                <div class="wp-block-buttons">
                    <!-- wp:button -->
                    <div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/shop">Explore Collection</a></div>
                    <!-- /wp:button -->
                </div>
                <!-- /wp:buttons -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
</section>
<!-- /wp:group -->',
];
