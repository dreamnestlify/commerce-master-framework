<?php
/**
 * Pattern: Product Collection
 *
 * A curated product grid block.
 *
 * @package CommerceBlockTheme
 */

declare(strict_types=1);

return [
    'title'       => __('Product Collection', 'commerce-block-theme'),
    'description' => __('Curated product collection grid.', 'commerce-block-theme'),
    'categories'  => ['commerce-fse'],
    'content'     => '<!-- wp:group {"tagName":"section","className":"product-collection","layout":{"type":"constrained"}} -->
<section class="wp-block-group product-collection">
    <!-- wp:heading {"level":2,"align":"center"} -->
    <h2 class="wp-block-heading has-text-align-center">Trending Now</h2>
    <!-- /wp:heading -->

    <!-- wp:paragraph {"align":"center","className":"product-collection__subtitle"} -->
    <p class="has-text-align-center product-collection__subtitle">Our most-loved styles this week</p>
    <!-- /wp:paragraph -->

    <!-- wp:woocommerce/product-collection {"queryId":1,"query":{"perPage":4,"postType":"product","order":"desc","orderBy":"popularity","offset":0,"pages":0,"inherit":false,"taxQuery":null,"isProductCollectionBlock":true,"woocommerceOnSale":false,"woocommerceAttributes":[],"woocommerceStockStatus":["instock"],"woocommerceVisibility":"visible","woocommerceFeatured":false}} -->
        <!-- wp:woocommerce/product-template -->
            <!-- wp:woocommerce/product-image {"showSaleBadge":true} /-->
            <!-- wp:post-title {"level":3,"isLink":true,"className":"product-title"} /-->
            <!-- wp:woocommerce/product-price /-->
            <!-- wp:woocommerce/product-button /-->
        <!-- /wp:woocommerce/product-template -->
    <!-- /wp:woocommerce/product-collection -->
</section>
<!-- /wp:group -->',
];
