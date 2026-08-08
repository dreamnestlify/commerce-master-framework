<?php
/**
 * Pattern: Category Grid
 *
 * Shop-by-category grid showing key fashion categories.
 *
 * @package CommerceBlockTheme
 */

declare(strict_types=1);

return [
    'title'       => __('Category Grid', 'commerce-block-theme'),
    'description' => __('Shop by category grid — Women, Men, Shoes, Accessories.', 'commerce-block-theme'),
    'categories'  => ['commerce-fse'],
    'content'     => '<!-- wp:group {"tagName":"section","className":"category-grid","layout":{"type":"constrained"}} -->
<section class="wp-block-group category-grid">
    <!-- wp:heading {"level":2,"align":"center","className":"category-grid__title"} -->
    <h2 class="wp-block-heading has-text-align-center category-grid__title">Shop by Category</h2>
    <!-- /wp:heading -->

    <!-- wp:columns {"className":"category-grid__items"} -->
    <div class="wp-block-columns category-grid__items">
        <!-- wp:column {"className":"category-card"} -->
        <div class="wp-block-column category-card">
            <!-- wp:group {"className":"category-card__inner","style":{"spacing":{"padding":{"top":"3rem","right":"3rem","bottom":"3rem","left":"3rem"}}}} -->
            <div class="wp-block-group category-card__inner" style="padding-top:3rem;padding-right:3rem;padding-bottom:3rem;padding-left:3rem">
                <!-- wp:heading {"level":3,"align":"center"} -->
                <h3 class="wp-block-heading has-text-align-center">Women</h3>
                <!-- /wp:heading -->
                <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
                <div class="wp-block-buttons">
                    <!-- wp:button {"className":"is-style-outline"} -->
                    <div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/product-category/women">Shop</a></div>
                    <!-- /wp:button -->
                </div>
                <!-- /wp:buttons -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"className":"category-card"} -->
        <div class="wp-block-column category-card">
            <!-- wp:group {"className":"category-card__inner","style":{"spacing":{"padding":{"top":"3rem","right":"3rem","bottom":"3rem","left":"3rem"}}}} -->
            <div class="wp-block-group category-card__inner" style="padding-top:3rem;padding-right:3rem;padding-bottom:3rem;padding-left:3rem">
                <!-- wp:heading {"level":3,"align":"center"} -->
                <h3 class="wp-block-heading has-text-align-center">Men</h3>
                <!-- /wp:heading -->
                <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
                <div class="wp-block-buttons">
                    <!-- wp:button {"className":"is-style-outline"} -->
                    <div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/product-category/men">Shop</a></div>
                    <!-- /wp:button -->
                </div>
                <!-- /wp:buttons -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"className":"category-card"} -->
        <div class="wp-block-column category-card">
            <!-- wp:group {"className":"category-card__inner","style":{"spacing":{"padding":{"top":"3rem","right":"3rem","bottom":"3rem","left":"3rem"}}}} -->
            <div class="wp-block-group category-card__inner" style="padding-top:3rem;padding-right:3rem;padding-bottom:3rem;padding-left:3rem">
                <!-- wp:heading {"level":3,"align":"center"} -->
                <h3 class="wp-block-heading has-text-align-center">Shoes</h3>
                <!-- /wp:heading -->
                <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
                <div class="wp-block-buttons">
                    <!-- wp:button {"className":"is-style-outline"} -->
                    <div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/product-category/shoes">Shop</a></div>
                    <!-- /wp:button -->
                </div>
                <!-- /wp:buttons -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"className":"category-card"} -->
        <div class="wp-block-column category-card">
            <!-- wp:group {"className":"category-card__inner","style":{"spacing":{"padding":{"top":"3rem","right":"3rem","bottom":"3rem","left":"3rem"}}}} -->
            <div class="wp-block-group category-card__inner" style="padding-top:3rem;padding-right:3rem;padding-bottom:3rem;padding-left:3rem">
                <!-- wp:heading {"level":3,"align":"center"} -->
                <h3 class="wp-block-heading has-text-align-center">Accessories</h3>
                <!-- /wp:heading -->
                <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
                <div class="wp-block-buttons">
                    <!-- wp:button {"className":"is-style-outline"} -->
                    <div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/product-category/accessories">Shop</a></div>
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
