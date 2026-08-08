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

// WooCommerce page ID helper.
if ( ! function_exists( 'wc_get_page_id' ) ) {
	/**
	 * @param string $page Page type (shop, cart, checkout, myaccount, etc.)
	 * @return int
	 */
	function wc_get_page_id( string $page ) {}
}

// WooCommerce order factory.
if ( ! function_exists( 'wc_get_order' ) ) {
	/**
	 * @param mixed $order Order ID or object.
	 * @return \WC_Order|false
	 */
	function wc_get_order( $order = false ) {}
}

// WooCommerce notice helper.
if ( ! function_exists( 'wc_add_notice' ) ) {
	/**
	 * @param string $message Notice message.
	 * @param string $notice_type Notice type (error, success, notice).
	 */
	function wc_add_notice( string $message, string $notice_type = 'success' ) {}
}

// WordPress status header helper (used by webhook handlers).
if ( ! function_exists( 'status_header' ) ) {
	/**
	 * @param int    $code        HTTP status code.
	 * @param string $description Optional description.
	 */
	function status_header( int $code, string $description = '' ) {}
}

// WooCommerce order class stub (minimal — methods used by payment modules).
if ( ! class_exists( 'WC_Order' ) ) {
	class WC_Order {
		/**
		 * @param mixed $order Order ID or object.
		 */
		public function __construct( $order = 0 ) {}

		public function get_id(): int {}

		public function get_currency(): string {}

		/**
		 * Get order total.
		 *
		 * @return string
		 */
		public function get_total() {}

		public function get_transaction_id(): string {}

		public function get_order_number(): string {}

		/**
		 * @param string $transaction_id Optional transaction ID.
		 * @return bool
		 */
		public function payment_complete( $transaction_id = '' ) {}

		/**
		 * @param string $note Order note.
		 * @return int
		 */
		public function add_order_note( string $note ) {}

		/**
		 * @param string $status  New status.
		 * @param string $note    Optional note.
		 */
		public function update_status( string $status, string $note = '' ) {}

		/**
		 * @param string[] $statuses Statuses to check.
		 * @return bool
		 */
		public function has_status( array $statuses ): bool {}
	}
}

// WooCommerce payment gateway class stub (minimal).
if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
	abstract class WC_Payment_Gateway {
		/** @var string */
		public $id;
		/** @var string */
		public $icon;
		/** @var bool */
		public $has_fields;
		/** @var string */
		public $method_title;
		/** @var string */
		public $method_description;
		/** @var string[] */
		public $supports;
		/** @var string */
		public $title;
		/** @var string */
		public $description;
		/** @var string */
		public $enabled;
		/** @var array<string, mixed> */
		public $form_fields = array();
		/** @var array<string, mixed> */
		public $settings = array();

		abstract public function init_form_fields(): void;

		public function init_settings(): void {}

		/**
		 * @param string $key     Option key.
		 * @param mixed  $default Default value.
		 * @return mixed
		 */
		public function get_option( $key, $default = false ) {}

		/**
		 * @param int $order_id Order ID.
		 * @return array<string, mixed>
		 */
		public function process_payment( $order_id ) {}

		/**
		 * @param int        $order_id Order ID.
		 * @param float|null $amount   Refund amount.
		 * @param string     $reason   Refund reason.
		 * @return bool
		 */
		public function process_refund( $order_id, $amount = null, $reason = '' ) {}

		/**
		 * @param \WC_Order $order Order object.
		 * @return string
		 */
		public function get_return_url( $order = null ) {}

		public function is_available(): bool {}

		public function process_admin_options(): bool {}
	}
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
