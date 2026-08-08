<?php
/**
 * Pattern: Fashion Header
 *
 * A styled header block for the fashion site — logo, mega navigation,
 * search, account, and mini-cart. For use in the site editor.
 *
 * @package CommerceBlockTheme
 */

declare(strict_types=1);

return [
    'title'       => __('Fashion Header', 'commerce-block-theme'),
    'description' => __('Full fashion e-commerce header with navigation, search, account, and cart.', 'commerce-block-theme'),
    'categories'  => ['commerce-fse'],
    'content'     => '<!-- wp:group {"tagName":"header","className":"fashion-header","layout":{"type":"constrained"}} -->
<header class="wp-block-group fashion-header">
    <!-- wp:group {"className":"fashion-header__inner","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
    <div class="wp-block-group fashion-header__inner">
        <!-- wp:site-title {"level":0} /-->
        <!-- wp:navigation {"overlayMenu":"mobile","className":"fashion-nav"} /-->
        <!-- wp:group {"className":"header-actions","layout":{"type":"flex","flexWrap":"nowrap"}} -->
        <div class="wp-block-group header-actions">
            <!-- wp:search {"label":"Search","buttonText":"","className":"header-search"} /-->
            <!-- wp:woocommerce/customer-account /-->
            <!-- wp:woocommerce/mini-cart /-->
        </div>
        <!-- /wp:group -->
    </div>
    <!-- /wp:group -->
</header>
<!-- /wp:group -->',
];
