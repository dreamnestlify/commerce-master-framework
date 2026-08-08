<?php
/**
 * Wishlist Module — product wishlist with REST API.
 *
 * Stores wishlist items in user meta (logged-in) or cookie (guest).
 * Provides REST endpoints for add/remove/get/count.
 *
 * @package CommerceMaster\Core\Module
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Module;

class WishlistModule implements ModuleInterface {

	private const META_KEY = '_commerce_wishlist';
	private const COOKIE_NAME = 'commerce_wishlist';
	private const REST_NAMESPACE = 'commerce-core/v1';
	private const COOKIE_LIFETIME = 2592000; // 30 days.

	/**
	 * Register hooks and dependencies.
	 */
	public function register(): void {
		add_action('rest_api_init', array($this, 'register_rest_routes'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
		add_action('wp_footer', array($this, 'render_wishlist_button_template'));
		add_action('woocommerce_after_add_to_cart_button', array($this, 'render_wishlist_button_inline'));
	}

	/**
	 * Boot the module.
	 */
	public function boot(): void {
		// Handle cookie for guest users.
		if (!is_user_logged_in() && !headers_sent()) {
			$this->maybe_set_cookie();
		}
	}

	/**
	 * Called on plugin activation.
	 */
	public function activate(): void {
		// No activation tasks needed.
	}

	/**
	 * Module identifier.
	 */
	public function get_id(): string {
		return 'wishlist';
	}

	/**
	 * Register REST API routes.
	 */
	public function register_rest_routes(): void {
		register_rest_route(self::REST_NAMESPACE, '/wishlist', array(
			array(
				'methods'             => 'GET',
				'callback'            => array($this, 'get_wishlist'),
				'permission_callback' => '__return_true',
			),
		));

		register_rest_route(self::REST_NAMESPACE, '/wishlist/add', array(
			array(
				'methods'             => 'POST',
				'callback'            => array($this, 'add_to_wishlist'),
				'permission_callback' => '__return_true',
				'args'                => array(
					'product_id' => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
				),
			),
		));

		register_rest_route(self::REST_NAMESPACE, '/wishlist/remove', array(
			array(
				'methods'             => 'POST',
				'callback'            => array($this, 'remove_from_wishlist'),
				'permission_callback' => '__return_true',
				'args'                => array(
					'product_id' => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
				),
			),
		));

		register_rest_route(self::REST_NAMESPACE, '/wishlist/count', array(
			array(
				'methods'             => 'GET',
				'callback'            => array($this, 'get_wishlist_count'),
				'permission_callback' => '__return_true',
			),
		));
	}

	/**
	 * Get wishlist product IDs for the current user.
	 */
	public function get_wishlist_ids(): array {
		if (is_user_logged_in()) {
			$ids = get_user_meta(get_current_user_id(), self::META_KEY, true);
			return is_array($ids) ? array_map('intval', $ids) : array();
		}

		// Guest: read from cookie.
		if (isset($_COOKIE[self::COOKIE_NAME])) {
			$ids = json_decode(stripslashes($_COOKIE[self::COOKIE_NAME]), true);
			return is_array($ids) ? array_map('intval', $ids) : array();
		}

		return array();
	}

	/**
	 * REST: Get wishlist items with product data.
	 */
	public function get_wishlist(\WP_REST_Request $request): \WP_REST_Response {
		$ids = $this->get_wishlist_ids();
		$items = array();

		foreach ($ids as $product_id) {
			$product = wc_get_product($product_id);
			if (!$product) {
				continue;
			}
			$items[] = array(
				'id'            => $product->get_id(),
				'name'          => $product->get_name(),
				'permalink'     => $product->get_permalink(),
				'price'         => $product->get_price_html(),
				'image_url'     => wp_get_attachment_image_url($product->get_image_id(), 'woocommerce_thumbnail'),
				'stock_status'  => $product->get_stock_status(),
				'type'          => $product->get_type(),
			);
		}

		return new \WP_REST_Response(array(
			'items' => $items,
			'count' => count($items),
		), 200);
	}

	/**
	 * REST: Add product to wishlist.
	 */
	public function add_to_wishlist(\WP_REST_Request $request): \WP_REST_Response {
		$product_id = (int) $request->get_param('product_id');
		$product = wc_get_product($product_id);

		if (!$product) {
			return new \WP_REST_Response(array('error' => 'Product not found'), 404);
		}

		$ids = $this->get_wishlist_ids();
		if (!in_array($product_id, $ids, true)) {
			$ids[] = $product_id;
			$this->save_wishlist_ids($ids);
		}

		return new \WP_REST_Response(array(
			'success' => true,
			'count'   => count($ids),
		), 200);
	}

	/**
	 * REST: Remove product from wishlist.
	 */
	public function remove_from_wishlist(\WP_REST_Request $request): \WP_REST_Response {
		$product_id = (int) $request->get_param('product_id');
		$ids = $this->get_wishlist_ids();
		$ids = array_values(array_filter($ids, function ($id) use ($product_id) {
			return $id !== $product_id;
		}));
		$this->save_wishlist_ids($ids);

		return new \WP_REST_Response(array(
			'success' => true,
			'count'   => count($ids),
		), 200);
	}

	/**
	 * REST: Get wishlist count.
	 */
	public function get_wishlist_count(\WP_REST_Request $request): \WP_REST_Response {
		$ids = $this->get_wishlist_ids();
		return new \WP_REST_Response(array('count' => count($ids)), 200);
	}

	/**
	 * Save wishlist IDs for the current user.
	 */
	private function save_wishlist_ids(array $ids): void {
		if (is_user_logged_in()) {
			update_user_meta(get_current_user_id(), self::META_KEY, $ids);
		} else {
			$this->set_cookie(wp_json_encode($ids));
		}
	}

	/**
	 * Set the wishlist cookie for guest users.
	 */
	private function set_cookie(string $value): void {
		if (headers_sent()) {
			return;
		}
		setcookie(self::COOKIE_NAME, $value, time() + self::COOKIE_LIFETIME, COOKIEPATH, COOKIE_DOMAIN);
		$_COOKIE[self::COOKIE_NAME] = $value;
	}

	/**
	 * Maybe set initial cookie for guests.
	 */
	private function maybe_set_cookie(): void {
		if (!isset($_COOKIE[self::COOKIE_NAME])) {
			$this->set_cookie('[]');
		}
	}

	/**
	 * Enqueue assets on product pages.
	 */
	public function enqueue_assets(): void {
		if (!is_singular('product') && !is_shop() && !is_product_category()) {
			return;
		}

		wp_enqueue_script(
			'commerce-core-wishlist',
			plugins_url('assets/js/wishlist.js', dirname(__FILE__, 2)),
			array(),
			COMMERCE_CORE_VERSION,
			true
		);

		wp_localize_script('commerce-core-wishlist', 'commerceWishlist', array(
			'restUrl'   => esc_url_raw(rest_url(self::REST_NAMESPACE)),
			'nonce'     => wp_create_nonce('wp_rest'),
			'wishlist'  => $this->get_wishlist_ids(),
			'labels'    => array(
				'add'    => __('Add to Wishlist', 'commerce-core'),
				'remove' => __('Remove from Wishlist', 'commerce-core'),
				'added'  => __('Added to wishlist', 'commerce-core'),
			),
		));
	}

	/**
	 * Render wishlist button on single product page (inline fallback).
	 */
	public function render_wishlist_button_inline(): void {
		$product_id = get_the_ID();
		if (!$product_id) {
			return;
		}
		$in_wishlist = in_array($product_id, $this->get_wishlist_ids(), true);
		printf(
			'<button type="button" class="commerce-wishlist-btn%s" data-product-id="%d" aria-label="%s">%s</button>',
			$in_wishlist ? ' is-active' : '',
			(int) $product_id,
			esc_attr__('Toggle wishlist', 'commerce-core'),
			esc_html($in_wishlist ? '♥ Wishlisted' : '♡ Add to Wishlist')
		);
	}

	/**
	 * Render wishlist button template for JS injection (product cards).
	 */
	public function render_wishlist_button_template(): void {
		if (!is_shop() && !is_product_category() && !is_singular('product')) {
			return;
		}
		echo '<script type="text/template" id="commerce-wishlist-btn-template">';
		echo '<button type="button" class="commerce-wishlist-card-btn" aria-label="Add to wishlist">♡</button>';
		echo '</script>';
	}
}
