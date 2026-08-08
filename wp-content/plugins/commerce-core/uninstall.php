<?php
/**
 * Uninstall Commerce Core — removes all plugin data.
 * Runs when user deletes the plugin from WordPress admin.
 *
 * @package CommerceMaster\Core
 */

declare(strict_types=1);

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin options.
delete_option( 'commerce_core_settings' );

// Delete any transients.
delete_transient( 'commerce_core_cache' );
delete_transient( 'commerce_core_status' );

// Clear any scheduled events.
$timestamp = wp_next_scheduled( 'commerce_core_daily_maintenance' );
if ( $timestamp ) {
	wp_unschedule_event( $timestamp, 'commerce_core_daily_maintenance' );
}

// Note: We do NOT delete product data, order data, or user data.
// Those are WooCommerce entities and belong to the site, not the plugin.
