<?php
/**
 * Pattern: Benefits Strip
 *
 * Icons/text strip showing shipping, returns, secure payment, etc.
 *
 * @package CommerceBlockTheme
 */

declare(strict_types=1);

return [
    'title'       => __('Benefits Strip', 'commerce-block-theme'),
    'description' => __('Trust badges: free shipping, returns, secure payment, customer support.', 'commerce-block-theme'),
    'categories'  => ['commerce-fse'],
    'content'     => '<!-- wp:group {"tagName":"section","className":"benefits-strip","layout":{"type":"constrained"}} -->
<section class="wp-block-group benefits-strip">
    <!-- wp:columns {"className":"benefits-strip__items"} -->
    <div class="wp-block-columns benefits-strip__items">
        <!-- wp:column {"className":"benefit-item"} -->
        <div class="wp-block-column benefit-item">
            <!-- wp:paragraph {"align":"center","className":"benefit-text"} -->
            <p class="has-text-align-center benefit-text">Free Shipping<br>Over $50</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"className":"benefit-item"} -->
        <div class="wp-block-column benefit-item">
            <!-- wp:paragraph {"align":"center","className":"benefit-text"} -->
            <p class="has-text-align-center benefit-text">30-Day<br>Returns</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"className":"benefit-item"} -->
        <div class="wp-block-column benefit-item">
            <!-- wp:paragraph {"align":"center","className":"benefit-text"} -->
            <p class="has-text-align-center benefit-text">Secure<br>Payment</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- wp:column -->

        <!-- wp:column {"className":"benefit-item"} -->
        <div class="wp-block-column benefit-item">
            <!-- wp:paragraph {"align":"center","className":"benefit-text"} -->
            <p class="has-text-align-center benefit-text">24/7<br>Customer Support</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
</section>
<!-- /wp:group -->',
];
