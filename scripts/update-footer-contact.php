<?php
/**
 * Zalandy — Add contact line (email + phone) to the footer company block.
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit;
}

WP_CLI::log( '=== Footer contact update ===' );

$footer = get_option( 'zalandy_custom_footer' );

if ( ! $footer ) {
	WP_CLI::warning( 'No zalandy_custom_footer option found.' );
	return;
}

WP_CLI::log( '  Footer length: ' . strlen( $footer ) );

// Show the company-info section for context (last 500 chars before </div> end).
WP_CLI::log( '  --- tail ---' );
	WP_CLI::log( substr( $footer, -400 ) );
WP_CLI::log( '  --- /tail ---' );

$new_email = 'indiagianina5@gmail.com';
$new_phone = '+1 929 568 3010';

if ( strpos( $footer, $new_email ) !== false ) {
	WP_CLI::log( '  New email already present, nothing to do.' );
	return;
}

$contact_line = 'Contact: <a href="mailto:' . $new_email . '" style="color:#aaa;">' . $new_email . '</a> | ' . $new_phone;

// Insert before the closing of the footer-container, after the copyright line if present.
if ( preg_match( '/(<p[^>]*>[^<]*&copy;[^<]*<\/p>)/i', $footer, $m ) ) {
	$footer = str_replace( $m[1], $m[1] . "\n" . $contact_line, $footer );
	WP_CLI::log( '  Appended after copyright line.' );
} else {
	// Fallback: insert before final closing container div.
	$pos = strrpos( $footer, '</div>' );
	$footer = substr( $footer, 0, $pos ) . $contact_line . "\n" . substr( $footer, $pos );
	WP_CLI::log( '  Appended before last closing div.' );
}

update_option( 'zalandy_custom_footer', $footer );
wp_cache_flush();
WP_CLI::log( '  Footer updated. New email present: ' . ( strpos( $footer, $new_email ) !== false ? 'YES' : 'NO' ) );
WP_CLI::log( 'Done.' );
