<?php
/**
 * Plugin Name:       Commerce Core
 * Plugin URI:        https://example.com/commerce-core
 * Description:       Core business logic plugin for the Commerce Master WordPress + WooCommerce fashion e-commerce framework.
 * Version:           0.1.0
 * Requires at least: 6.5
 * Requires PHP:      8.2
 * Author:            Commerce Master
 * License:           Proprietary
 * Text Domain:       commerce-core
 * Domain Path:       /languages
 *
 * @package CommerceMaster\Core
 */

declare(strict_types=1);

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'COMMERCE_CORE_VERSION', '0.1.0' );
define( 'COMMERCE_CORE_FILE', __FILE__ );
define( 'COMMERCE_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'COMMERCE_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'COMMERCE_CORE_BASENAME', plugin_basename( __FILE__ ) );

// PSR-4 autoloader (simple, no Composer needed for core files).
require_once COMMERCE_CORE_DIR . 'src/Autoload.php';
\CommerceMaster\Core\Autoload::register();

// Bootstrap the plugin.
$cc_plugin = new \CommerceMaster\Core\Plugin();

/**
 * Activation hook.
 */
register_activation_hook(
	__FILE__,
	function (): void {
		$cc_plugin = new \CommerceMaster\Core\Plugin();
		$cc_plugin->activate();
	}
);

/**
 * Deactivation hook.
 */
register_deactivation_hook(
	__FILE__,
	function (): void {
		$cc_plugin = new \CommerceMaster\Core\Plugin();
		$cc_plugin->deactivate();
	}
);

// Initialize on plugins_loaded.
add_action( 'plugins_loaded', array( $cc_plugin, 'boot' ) );

// Add WP-CLI commands if running in CLI context.
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	add_action(
		'cli_init',
		function () use ( $cc_plugin ): void {
			$cc_plugin->register_cli_commands();
		}
	);
}
