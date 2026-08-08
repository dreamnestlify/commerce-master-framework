<?php
/**
 * Tests for WishlistModule.
 *
 * @package CommerceMaster\Core\Tests\Unit
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Tests\Unit;

use PHPUnit\Framework\TestCase;
use CommerceMaster\Core\Module\WishlistModule;

/**
 * @SuppressWarnings(PHPMD.Superglobals)
 */
class WishlistModuleTest extends TestCase {

	private WishlistModule $module;

	protected function setUp(): void {
		parent::setUp();

		// Reset global state.
		$GLOBALS['_test_user_logged_in']      = false;
		$GLOBALS['_test_current_user_id']     = 0;
		$GLOBALS['_test_user_meta']           = array();
		$GLOBALS['_test_cookies']             = array();
		$GLOBALS['_test_wc_products']         = array();
		$GLOBALS['_test_localized_scripts']   = array();
		$GLOBALS['_test_headers_sent']        = false;
		$_COOKIE                              = array();
		$GLOBALS['_test_is_singular']         = false;
		$GLOBALS['_test_is_page']             = false;

		$this->module = new WishlistModule();
	}

	protected function tearDown(): void {
		unset(
			$GLOBALS['_test_user_logged_in'],
			$GLOBALS['_test_current_user_id'],
			$GLOBALS['_test_user_meta'],
			$GLOBALS['_test_cookies'],
			$GLOBALS['_test_wc_products'],
			$GLOBALS['_test_localized_scripts'],
			$GLOBALS['_test_headers_sent'],
			$GLOBALS['_test_is_singular'],
			$GLOBALS['_test_is_page']
		);
		$_COOKIE = array();

		parent::tearDown();
	}

	public function test_module_id(): void {
		$this->assertSame( 'wishlist', $this->module->get_id() );
	}

	// ── Guest (cookie-based) wishlist ──

	public function test_guest_get_empty_wishlist_without_cookie(): void {
		$this->assertSame( array(), $this->module->get_wishlist_ids() );
	}

	public function test_guest_get_wishlist_from_cookie(): void {
		$_COOKIE['commerce_wishlist'] = '[1,2,3]';

		$ids = $this->module->get_wishlist_ids();

		$this->assertSame( array( 1, 2, 3 ), $ids );
	}

	public function test_guest_get_wishlist_with_invalid_cookie(): void {
		$_COOKIE['commerce_wishlist'] = 'not-json';

		$this->assertSame( array(), $this->module->get_wishlist_ids() );
	}

	public function test_guest_get_wishlist_with_empty_cookie(): void {
		$_COOKIE['commerce_wishlist'] = '[]';

		$this->assertSame( array(), $this->module->get_wishlist_ids() );
	}

	// ── Logged-in (user meta) wishlist ──

	public function test_logged_in_get_empty_wishlist(): void {
		$GLOBALS['_test_user_logged_in']  = true;
		$GLOBALS['_test_current_user_id'] = 5;

		$this->assertSame( array(), $this->module->get_wishlist_ids() );
	}

	public function test_logged_in_get_wishlist_from_meta(): void {
		$GLOBALS['_test_user_logged_in']  = true;
		$GLOBALS['_test_current_user_id'] = 5;
		$GLOBALS['_test_user_meta'][5]['_commerce_wishlist'] = array( 10, 20 );

		$ids = $this->module->get_wishlist_ids();

		$this->assertSame( array( 10, 20 ), $ids );
	}

	public function test_logged_in_ignores_cookie(): void {
		$GLOBALS['_test_user_logged_in']  = true;
		$GLOBALS['_test_current_user_id'] = 5;
		$_COOKIE['commerce_wishlist']     = '[99]';

		$this->assertSame( array(), $this->module->get_wishlist_ids() );
	}

	// ── Add to wishlist ──

	public function test_add_product_to_wishlist(): void {
		_test_add_wc_product( 42, array( 'name' => 'Test Shirt' ) );

		$request = new \WP_REST_Request();
		$request->set_method( 'POST' );
		$request->set_json_params( array( 'product_id' => 42 ) );

		$response = $this->module->add_to_wishlist( $request );

		$this->assertTrue( $response->data['success'] );
		$this->assertSame( 1, $response->data['count'] );
	}

	public function test_add_nonexistent_product_returns_404(): void {
		$request = new \WP_REST_Request();
		$request->set_method( 'POST' );
		$request->set_json_params( array( 'product_id' => 9999 ) );

		$response = $this->module->add_to_wishlist( $request );

		$this->assertSame( 404, $response->status );
		$this->assertArrayHasKey( 'error', $response->data );
	}

	public function test_add_duplicate_product_does_not_duplicate(): void {
		_test_add_wc_product( 42, array( 'name' => 'Test Shirt' ) );
		$GLOBALS['_test_user_logged_in']  = true;
		$GLOBALS['_test_current_user_id'] = 5;
		$GLOBALS['_test_user_meta'][5]['_commerce_wishlist'] = array( 42 );

		$request = new \WP_REST_Request();
		$request->set_method( 'POST' );
		$request->set_json_params( array( 'product_id' => 42 ) );

		$response = $this->module->add_to_wishlist( $request );

		$this->assertTrue( $response->data['success'] );
		$this->assertSame( 1, $response->data['count'] );
	}

	public function test_add_multiple_products(): void {
		_test_add_wc_product( 10, array( 'name' => 'A' ) );
		_test_add_wc_product( 20, array( 'name' => 'B' ) );
		_test_add_wc_product( 30, array( 'name' => 'C' ) );

		foreach ( array( 10, 20, 30 ) as $pid ) {
			$request = new \WP_REST_Request();
			$request->set_method( 'POST' );
			$request->set_json_params( array( 'product_id' => $pid ) );

			$this->module->add_to_wishlist( $request );
		}

		$ids = $this->module->get_wishlist_ids();
		$this->assertCount( 3, $ids );
		$this->assertContains( 10, $ids );
		$this->assertContains( 20, $ids );
		$this->assertContains( 30, $ids );
	}

	// ── Remove from wishlist ──

	public function test_remove_product_from_wishlist(): void {
		$GLOBALS['_test_user_logged_in']  = true;
		$GLOBALS['_test_current_user_id'] = 5;
		$GLOBALS['_test_user_meta'][5]['_commerce_wishlist'] = array( 10, 20, 30 );

		$request = new \WP_REST_Request();
		$request->set_method( 'POST' );
		$request->set_json_params( array( 'product_id' => 20 ) );

		$response = $this->module->remove_from_wishlist( $request );

		$this->assertTrue( $response->data['success'] );
		$this->assertSame( 2, $response->data['count'] );

		$ids = $this->module->get_wishlist_ids();
		$this->assertNotContains( 20, $ids );
		$this->assertContains( 10, $ids );
		$this->assertContains( 30, $ids );
	}

	public function test_remove_nonexistent_product_is_noop(): void {
		$GLOBALS['_test_user_logged_in']  = true;
		$GLOBALS['_test_current_user_id'] = 5;
		$GLOBALS['_test_user_meta'][5]['_commerce_wishlist'] = array( 10, 20 );

		$request = new \WP_REST_Request();
		$request->set_method( 'POST' );
		$request->set_json_params( array( 'product_id' => 999 ) );

		$response = $this->module->remove_from_wishlist( $request );

		$this->assertTrue( $response->data['success'] );
		$this->assertSame( 2, $response->data['count'] );
	}

	public function test_remove_from_empty_wishlist(): void {
		$request = new \WP_REST_Request();
		$request->set_method( 'POST' );
		$request->set_json_params( array( 'product_id' => 1 ) );

		$response = $this->module->remove_from_wishlist( $request );

		$this->assertTrue( $response->data['success'] );
		$this->assertSame( 0, $response->data['count'] );
	}

	// ── Count ──

	public function test_count_empty_wishlist(): void {
		$request  = new \WP_REST_Request();
		$response = $this->module->get_wishlist_count( $request );

		$this->assertSame( 0, $response->data['count'] );
	}

	public function test_count_with_items(): void {
		$GLOBALS['_test_user_logged_in']  = true;
		$GLOBALS['_test_current_user_id'] = 5;
		$GLOBALS['_test_user_meta'][5]['_commerce_wishlist'] = array( 1, 2, 3 );

		$request  = new \WP_REST_Request();
		$response = $this->module->get_wishlist_count( $request );

		$this->assertSame( 3, $response->data['count'] );
	}

	// ── Get wishlist (with product data) ──

	public function test_get_wishlist_returns_product_data(): void {
		_test_add_wc_product( 10, array(
			'name'         => 'Silk Blouse',
			'price_html'   => '$89.00',
			'stock_status' => 'instock',
			'type'         => 'simple',
		) );

		$GLOBALS['_test_user_logged_in']  = true;
		$GLOBALS['_test_current_user_id'] = 5;
		$GLOBALS['_test_user_meta'][5]['_commerce_wishlist'] = array( 10 );

		$request  = new \WP_REST_Request();
		$response = $this->module->get_wishlist( $request );

		$this->assertSame( 1, $response->data['count'] );
		$this->assertCount( 1, $response->data['items'] );

		$item = $response->data['items'][0];
		$this->assertSame( 10, $item['id'] );
		$this->assertSame( 'Silk Blouse', $item['name'] );
		$this->assertSame( '$89.00', $item['price'] );
		$this->assertSame( 'instock', $item['stock_status'] );
	}

	public function test_get_wishlist_skips_deleted_products(): void {
		_test_add_wc_product( 10, array( 'name' => 'Exists' ) );
		// Product 20 is not registered — simulates a deleted product.

		$GLOBALS['_test_user_logged_in']  = true;
		$GLOBALS['_test_current_user_id'] = 5;
		$GLOBALS['_test_user_meta'][5]['_commerce_wishlist'] = array( 10, 20 );

		$request  = new \WP_REST_Request();
		$response = $this->module->get_wishlist( $request );

		// Should only return product 10, skipping deleted product 20.
		$this->assertSame( 1, $response->data['count'] );
		$this->assertCount( 1, $response->data['items'] );
		$this->assertSame( 10, $response->data['items'][0]['id'] );
	}

	public function test_get_empty_wishlist_returns_empty_items(): void {
		$request  = new \WP_REST_Request();
		$response = $this->module->get_wishlist( $request );

		$this->assertSame( 0, $response->data['count'] );
		$this->assertSame( array(), $response->data['items'] );
	}

	// ── Nonce verification ──

	public function test_verify_rest_nonce_with_valid_nonce(): void {
		$request = new \WP_REST_Request();
		$request->set_header( 'x-wp-nonce', 'valid_rest_nonce' );

		$this->assertTrue( $this->module->verify_rest_nonce( $request ) );
	}

	public function test_verify_rest_nonce_with_invalid_nonce(): void {
		$request = new \WP_REST_Request();
		$request->set_header( 'x-wp-nonce', 'invalid_nonce' );

		$this->assertFalse( $this->module->verify_rest_nonce( $request ) );
	}

	public function test_verify_rest_nonce_without_nonce(): void {
		$request = new \WP_REST_Request();

		$this->assertFalse( $this->module->verify_rest_nonce( $request ) );
	}

	// ── Wishlist page shortcode ──

	public function test_wishlist_page_shortcode_empty(): void {
		$html = $this->module->render_wishlist_page();

		$this->assertStringContainsString( 'commerce-wishlist-empty', $html );
		$this->assertStringContainsString( 'Your wishlist is empty', $html );
	}

	public function test_wishlist_page_shortcode_with_items(): void {
		_test_add_wc_product( 10, array(
			'name'       => 'Wool Coat',
			'price_html' => '$199.00',
		) );

		$GLOBALS['_test_user_logged_in']  = true;
		$GLOBALS['_test_current_user_id'] = 5;
		$GLOBALS['_test_user_meta'][5]['_commerce_wishlist'] = array( 10 );

		$html = $this->module->render_wishlist_page();

		$this->assertStringContainsString( 'commerce-wishlist-grid', $html );
		$this->assertStringContainsString( 'Wool Coat', $html );
		$this->assertStringContainsString( '$199.00', $html );
		$this->assertStringContainsString( 'data-product-id="10"', $html );
	}
}
