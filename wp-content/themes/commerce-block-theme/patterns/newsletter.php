<?php
/**
 * Pattern: Newsletter
 *
 * Email signup block.
 *
 * @package CommerceBlockTheme
 */

declare(strict_types=1);

return [
    'title'       => __('Newsletter Signup', 'commerce-block-theme'),
    'description' => __('Newsletter email signup block with heading and form.', 'commerce-block-theme'),
    'categories'  => ['commerce-fse'],
    'content'     => '<!-- wp:group {"tagName":"section","className":"newsletter","layout":{"type":"constrained"}} -->
<section class="wp-block-group newsletter">
    <!-- wp:group {"className":"newsletter__content","style":{"spacing":{"padding":{"top":"4rem","right":"2rem","bottom":"4rem","left":"2rem"}}},"layout":{"type":"constrained","contentSize":"500px"}} -->
    <div class="wp-block-group newsletter__content" style="padding-top:4rem;padding-right:2rem;padding-bottom:4rem;padding-left:2rem">
        <!-- wp:heading {"level":2,"align":"center","className":"newsletter__title"} -->
        <h2 class="wp-block-heading has-text-align-center newsletter__title">Join Our Newsletter</h2>
        <!-- /wp:heading -->

        <!-- wp:paragraph {"align":"center","className":"newsletter__subtitle"} -->
        <p class="has-text-align-center newsletter__subtitle">Be the first to know about new arrivals, exclusive offers, and style tips. Get 10% off your first order.</p>
        <!-- /wp:paragraph -->

        <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
        <div class="wp-block-buttons" style="justify-content:center">
            <!-- wp:button -->
            <div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#">Subscribe Now</a></div>
            <!-- /wp:button -->
        </div>
        <!-- /wp:buttons -->

        <!-- wp:paragraph {"align":"center","className":"newsletter__disclaimer"} -->
        <p class="has-text-align-center newsletter__disclaimer">By subscribing, you agree to our Privacy Policy and consent to receive marketing emails.</p>
        <!-- /wp:paragraph -->
    </div>
    <!-- /wp:group -->
</section>
<!-- /wp:group -->',
];
