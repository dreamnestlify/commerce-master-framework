<?php
/**
 * PHPStan stubs for plugin constants, WooCommerce, and WP_CLI.
 *
 * The szepeviktor/phpstan-wordpress extension (auto-included by
 * extension-installer) provides WordPress core function stubs but
 * does NOT include WP_CLI or WooCommerce stubs. This file fills those gaps.
 */

// Plugin constants (defined in commerce-core.php).
define( 'COMMERCE_CORE_VERSION', '0.1.0' );
define( 'COMMERCE_CORE_FILE', __FILE__ );
define( 'COMMERCE_CORE_DIR', __DIR__ . '/' );
define( 'COMMERCE_CORE_URL', 'http://example.com/' );
define( 'COMMERCE_CORE_BASENAME', 'commerce-core/commerce-core.php' );

// WooCommerce WC() function.
if ( ! function_exists( 'WC' ) ) {
	function WC() {
		return new \WooCommerce();
	}
}

// WooCommerce class stub (minimal).
if ( ! class_exists( 'WooCommerce' ) ) {
	class WooCommerce {
		/** @var string */
		public $version = '11.0.0';
	}
}

// WP_CLI class stub (minimal — only methods used by this plugin).
if ( ! class_exists( 'WP_CLI' ) ) {
	class WP_CLI {
		/**
		 * @param string $message
		 */
		public static function log( $message ) {}

		/**
		 * @param string $message
		 */
		public static function success( $message ) {}

		/**
		 * @param string $message
		 */
		public static function warning( $message ) {}

		/**
		 * @param string $name
		 * @param mixed  $command
		 */
		public static function add_command( $name, $command, $args = array() ) {}
	}
}

namespace WP_CLI\Utils {
	/**
	 * @param array  $items
	 * @param array  $fields
	 * @param string $format
	 */
	function format_items( $items, $fields, $format = 'table' ) {}
}
