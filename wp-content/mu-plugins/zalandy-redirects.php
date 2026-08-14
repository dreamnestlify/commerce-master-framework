<?php
/**
 * Zalandy: managed 301 redirects (stored in `zalandy_redirects` option, slug => path).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'template_redirect', function () {
	if ( is_admin() || is_404() ) {
		return;
	}
	$redirects = get_option( 'zalandy_redirects', array() );
	if ( empty( $redirects ) ) {
		return;
	}
	$slug = get_queried_object();
	if ( $slug instanceof WP_Post && isset( $redirects[ $slug->post_name ] ) ) {
		wp_safe_redirect( home_url( $redirects[ $slug->post_name ] ), 301 );
		exit;
	}
} );
