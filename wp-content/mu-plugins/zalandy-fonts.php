<?php
/**
 * Zalandy - Google Fonts Loader
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style(
		'zalandy-google-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap',
		array(),
		null
	);
}, 20 );

add_action( 'admin_enqueue_scripts', function() {
	wp_enqueue_style(
		'zalandy-admin-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap',
		array(),
		null
	);
});
