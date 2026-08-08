<?php
/**
 * Pattern: Footer Info
 *
 * Additional footer block with link columns and social.
 *
 * @package CommerceBlockTheme
 */

declare(strict_types=1);

return [
    'title'       => __('Footer Info', 'commerce-block-theme'),
    'description' => __('Footer block with link columns, social links, and copyright.', 'commerce-block-theme'),
    'categories'  => ['commerce-fse'],
    'content'     => '<!-- wp:group {"tagName":"footer","className":"footer-info","layout":{"type":"constrained"}} -->
<footer class="wp-block-group footer-info">
    <!-- wp:group {"className":"footer-info__columns","layout":{"type":"grid","columnCount":4}} -->
    <div class="wp-block-group footer-info__columns">
        <!-- wp:group {"className":"footer-info__col"} -->
        <div class="wp-block-group footer-info__col">
            <!-- wp:heading {"level":3} -->
            <h3 class="wp-block-heading">Shop</h3>
            <!-- /wp:heading -->
            <!-- wp:list -->
            <ul class="wp-block-list">
                <li><a href="/product-category/women">Women</a></li>
                <li><a href="/product-category/men">Men</a></li>
                <li><a href="/product-category/shoes">Shoes</a></li>
                <li><a href="/product-category/accessories">Accessories</a></li>
            </ul>
            <!-- /wp:list -->
        </div>
        <!-- /wp:group -->

        <!-- wp:group {"className":"footer-info__col"} -->
        <div class="wp-block-group footer-info__col">
            <!-- wp:heading {"level":3} -->
            <h3 class="wp-block-heading">Help</h3>
            <!-- /wp:heading -->
            <!-- wp:list -->
            <ul class="wp-block-list">
                <li><a href="#">Shipping</a></li>
                <li><a href="#">Returns</a></li>
                <li><a href="#">Size Guide</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
            <!-- /wp:list -->
        </div>
        <!-- /wp:group -->

        <!-- wp:group {"className":"footer-info__col"} -->
        <div class="wp-block-group footer-info__col">
            <!-- wp:heading {"level":3} -->
            <h3 class="wp-block-heading">Company</h3>
            <!-- /wp:heading -->
            <!-- wp:list -->
            <ul class="wp-block-list">
                <li><a href="#">About Us</a></li>
                <li><a href="#">Careers</a></li>
                <li><a href="#">Sustainability</a></li>
            </ul>
            <!-- /wp:list -->
        </div>
        <!-- /wp:group -->

        <!-- wp:group {"className":"footer-info__col"} -->
        <div class="wp-block-group footer-info__col">
            <!-- wp:heading {"level":3} -->
            <h3 class="wp-block-heading">Follow Us</h3>
            <!-- /wp:heading -->
            <!-- wp:list -->
            <ul class="wp-block-list">
                <li><a href="#">Instagram</a></li>
                <li><a href="#">TikTok</a></li>
                <li><a href="#">Facebook</a></li>
            </ul>
            <!-- /wp:list -->
        </div>
        <!-- /wp:group -->
    </div>
    <!-- /wp:group -->

    <!-- wp:separator -->
    <hr class="wp-block-separator has-alpha-channel-opacity"/>
    <!-- /wp:separator -->

    <!-- wp:paragraph {"align":"center"} -->
    <p class="has-text-align-center">© 2026 Commerce Master. All rights reserved.</p>
    <!-- /wp:paragraph -->
</footer>
<!-- /wp:group -->',
];
