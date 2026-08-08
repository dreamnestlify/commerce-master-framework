<?php
/**
 * Recently Viewed Module — tracks and displays recently viewed products.
 *
 * Stores recently viewed product IDs in user meta (logged-in) or cookie (guest).
 * Provides REST endpoint for tracking and a hook for rendering the section.
 *
 * @package CommerceMaster\Core\Module
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Module;

class RecentlyViewedModule implements ModuleInterface {

	private const META_KEY = '_commerce_recently_viewed';
	private const COOKIE_NAME = 'commerce_recently_viewed';
	private const REST_NAMESPACE = 'commerce-core/v1';
	private const COOKIE_LIFETIME = 2592000; // 30 days.
	private const MAX_ITEMS = 8;

	/**
	 * Register hooks and dependencies.
	 */
	public function register(): void {
		add_action('rest_api_init', array($this, 'register_rest_routes'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
		add_action('woocommerce_after_single_product', array($this, 'render_recently_viewed_section'));
	}

	/**
	 * Boot the module.
	 */
	public function boot(): void {
		if (!is_user_logged_in() && !headers_sent()) {
			$this->maybe_set_cookie();
		}
	}

	/**
	 * Called on plugin activation.
	 */
	public function activate(): void {
	}

	/**
	 * Module identifier.
	 */
	public function get_id(): string {
		return 'recently-viewed';
	}

	/**
	 * Register REST API routes.
	 */
	public function register_rest_routes(): void {
		register_rest_route(self::REST_NAMESPACE, '/recently-viewed/track', array(
			array(
				'methods'             => 'POST',
				'callback'            => array($this, 'track_view'),
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

		register_rest_route(self::REST_NAMESPACE, '/recently-viewed', array(
			array(
				'methods'             => 'GET',
				'callback'            => array($this, 'get_recently_viewed'),
				'permission_callback' => '__return_true',
			),
		));
	}

	/**
	 * Get recently viewed product IDs.
	 *
	 * @return int[]
	 */
	public function get_recently_viewed_ids(): array {
		if (is_user_logged_in()) {
			$ids = get_user_meta(get_current_user_id(), self::META_KEY, true);
			return is_array($ids) ? array_map('intval', $ids) : array();
		}

		if (isset($_COOKIE[self::COOKIE_NAME])) {
			$cookie_value = sanitize_text_field(wp_unslash($_COOKIE[self::COOKIE_NAME]));
			$ids = json_decode($cookie_value, true);
			return is_array($ids) ? array_map('intval', $ids) : array();
		}

		return array();
	}

	/**
	 * Track a product view.
	 *
	 * @param int $product_id Product ID being viewed.
	 * @return int[]
	 */
	public function track_product_view(int $product_id): array {
		$ids = $this->get_recently_viewed_ids();

		// Remove if already exists (move to front).
		$ids = array_values(array_filter($ids, function ($id) use ($product_id) {
			return $id !== $product_id;
		}));

		// Add to front.
		array_unshift($ids, $product_id);

		// Limit to max items.
		$ids = array_slice($ids, 0, self::MAX_ITEMS);

		$this->save_ids($ids);

		return $ids;
	}

	/**
	 * REST: Track a product view.
	 */
	public function track_view(\WP_REST_Request $request): \WP_REST_Response {
		$product_id = (int) $request->get_param('product_id');
		$product = wc_get_product($product_id);

		if (!$product) {
			return new \WP_REST_Response(array('error' => 'Product not found'), 404);
		}

		$ids = $this->track_product_view($product_id);

		return new \WP_REST_Response(array(
			'success' => true,
			'count'   => count($ids),
		), 200);
	}

	/**
	 * REST: Get recently viewed products.
	 */
	public function get_recently_viewed(\WP_REST_Request $request): \WP_REST_Response {
		$ids = $this->get_recently_viewed_ids();
		$items = array();

		foreach ($ids as $product_id) {
			$product = wc_get_product($product_id);
			if (!$product) {
				continue;
			}
			$items[] = array(
				'id'           => $product->get_id(),
				'name'         => $product->get_name(),
				'permalink'    => $product->get_permalink(),
				'price'        => $product->get_price_html(),
				'image_url'    => wp_get_attachment_image_url($product->get_image_id(), 'woocommerce_thumbnail'),
				'stock_status' => $product->get_stock_status(),
			);
		}

		return new \WP_REST_Response(array(
			'items' => $items,
			'count' => count($items),
		), 200);
	}

	/**
	 * Save recently viewed IDs.
	 *
	 * @param int[] $ids Product IDs to save.
	 */
	private function save_ids(array $ids): void {
		if (is_user_logged_in()) {
			update_user_meta(get_current_user_id(), self::META_KEY, $ids);
		} else {
			$this->set_cookie(wp_json_encode($ids));
		}
	}

	/**
	 * Set cookie for guests.
	 */
	private function set_cookie(string $value): void {
		if (headers_sent()) {
			return;
		}
		setcookie(self::COOKIE_NAME, $value, time() + self::COOKIE_LIFETIME, COOKIEPATH, COOKIE_DOMAIN);
		$_COOKIE[self::COOKIE_NAME] = $value;
	}

	/**
	 * Maybe set initial cookie.
	 */
	private function maybe_set_cookie(): void {
		if (!isset($_COOKIE[self::COOKIE_NAME])) {
			$this->set_cookie('[]');
		}
	}

	/**
	 * Enqueue tracking script on single product pages.
	 */
	public function enqueue_assets(): void {
		if (!is_singular('product')) {
			return;
		}

		wp_enqueue_script(
			'commerce-core-recently-viewed',
			plugins_url('assets/js/recently-viewed.js', dirname(__FILE__, 2)),
			array(),
			COMMERCE_CORE_VERSION,
			true
		);

		$product_id = get_the_ID();

		wp_localize_script('commerce-core-recently-viewed', 'commerceRecentlyViewed', array(
			'restUrl'    => esc_url_raw(rest_url(self::REST_NAMESPACE)),
			'nonce'      => wp_create_nonce('wp_rest'),
			'productId'  => $product_id ? (int) $product_id : 0,
		));
	}

	/**
	 * Render recently viewed section on single product page.
	 */
	public function render_recently_viewed_section(): void {
		global $product;
		if ( ! $product instanceof \WC_Product ) {
			return;
		}

		$ids = $this->get_recently_viewed_ids();
		$current_id = $product->get_id();

		// Exclude current product from display.
		$display_ids = array_values(array_filter($ids, function ($id) use ($current_id) {
			return $id !== $current_id;
		}));

		if (count($display_ids) < 1) {
			return;
		}

		$display_ids = array_slice($display_ids, 0, 4);

		echo '<section class="recently-viewed-section">';
		echo '<h2 class="recently-viewed-title">' . esc_html__('Recently Viewed', 'commerce-core') . '</h2>';
		echo '<div class="recently-viewed-grid">';

		foreach ($display_ids as $pid) {
			$p = wc_get_product($pid);
			if (!$p) {
				continue;
			}
			printf(
				'<a href="%s" class="recently-viewed-item">%s<span class="recently-viewed-name">%s</span><span class="recently-viewed-price">%s</span></a>',
				esc_url($p->get_permalink()),
				wp_kses_post($p->get_image('woocommerce_thumbnail', array('class' => 'recently-viewed-img'), false)),
				esc_html($p->get_name()),
				wp_kses_post($p->get_price_html())
			);
		}

		echo '</div>';
		echo '</section>';
	}
}
