<?php
/**
 * PHPStan stubs for plugin constants, WooCommerce, and WP_CLI.
 *
 * The szepeviktor/phpstan-wordpress extension (auto-included by
 * extension-installer) provides WordPress core function stubs but
 * does NOT include WP_CLI or WooCommerce stubs. This file fills those gaps.
 *
 * Note: WP_CLI\Utils\format_items is in a separate file because PHP
 * requires namespace declarations to be the first statement.
 */

// Plugin constants (defined in commerce-core.php).
define( 'COMMERCE_CORE_VERSION', '0.1.0' );
define( 'COMMERCE_CORE_FILE', __FILE__ );
define( 'COMMERCE_CORE_DIR', __DIR__ . '/' );
define( 'COMMERCE_CORE_URL', 'http://example.com/' );
define( 'COMMERCE_CORE_BASENAME', 'commerce-core/commerce-core.php' );

// WordPress cookie path constants (defined by WordPress core during bootstrap).
if ( ! defined( 'COOKIEPATH' ) ) {
	define( 'COOKIEPATH', '/' );
}
if ( ! defined( 'COOKIE_DOMAIN' ) ) {
	define( 'COOKIE_DOMAIN', false );
}

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

// WooCommerce conditional functions.
if ( ! function_exists( 'is_shop' ) ) {
	function is_shop() {}
}
if ( ! function_exists( 'is_product_category' ) ) {
	function is_product_category( $term = '' ) {}
}

// WooCommerce product factory.
if ( ! function_exists( 'wc_get_product' ) ) {
	/**
	 * @param mixed $product_id
	 * @return \WC_Product|false
	 */
	function wc_get_product( $product_id = false ) {}
}

// WooCommerce product class stub (minimal — methods used by plugin modules).
if ( ! class_exists( 'WC_Product' ) ) {
	class WC_Product {
		/**
		 * Get product ID.
		 *
		 * @return int
		 */
		public function get_id() {}

		/**
		 * Get product name.
		 *
		 * @return string
		 */
		public function get_name() {}

		/**
		 * Get product permalink.
		 *
		 * @return string
		 */
		public function get_permalink() {}

		/**
		 * Get formatted price HTML.
		 *
		 * @return string
		 */
		public function get_price_html() {}

		/**
		 * Get product image ID.
		 *
		 * @return int
		 */
		public function get_image_id() {}

		/**
		 * Get stock status.
		 *
		 * @return string
		 */
		public function get_stock_status() {}

		/**
		 * Get product type.
		 *
		 * @return string
		 */
		public function get_type() {}

		/**
		 * Get product image HTML.
		 *
		 * @param string $size
		 * @param array  $attr
		 * @param bool   $placeholder
		 * @return string
		 */
		public function get_image( $size = 'shop_thumbnail', $attr = array(), $placeholder = true ) {}
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
