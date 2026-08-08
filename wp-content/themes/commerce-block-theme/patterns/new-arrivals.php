<?php
/**
 * Pattern: New Arrivals
 *
 * WooCommerce product collection showing newest products.
 *
 * @package CommerceBlockTheme
 */

declare(strict_types=1);

return [
    'title'       => __('New Arrivals', 'commerce-block-theme'),
    'description' => __('Product collection grid showing the latest arrivals.', 'commerce-block-theme'),
    'categories'  => ['commerce-fse'],
    'content'     => '<!-- wp:group {"tagName":"section","className":"new-arrivals","layout":{"type":"constrained"}} -->
<section class="wp-block-group new-arrivals">
    <!-- wp:group {"className":"new-arrivals__header","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
    <div class="wp-block-group new-arrivals__header">
        <!-- wp:heading {"level":2} -->
        <h2 class="wp-block-heading">New Arrivals</h2>
        <!-- /wp:heading -->
        <!-- wp:paragraph -->
        <p><a href="/shop">View all</a></p>
        <!-- /wp:paragraph -->
    </div>
    <!-- /wp:group -->

    <!-- wp:woocommerce/product-collection {"queryId":0,"query":{"perPage":8,"postType":"product","order":"desc","orderBy":"date","offset":0,"pages":0,"inherit":false,"taxQuery":null,"isProductCollectionBlock":true,"woocommerceOnSale":false,"woocommerceAttributes":[],"woocommerceStockStatus":["instock"],"woocommerceVisibility":"visible","woocommerceFeatured":false}} -->
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
