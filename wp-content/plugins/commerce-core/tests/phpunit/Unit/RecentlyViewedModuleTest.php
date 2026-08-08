<?php
/**
 * Tests for RecentlyViewedModule.
 *
 * @package CommerceMaster\Core\Tests\Unit
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Tests\Unit;

use PHPUnit\Framework\TestCase;
use CommerceMaster\Core\Module\RecentlyViewedModule;

/**
 * @SuppressWarnings(PHPMD.Superglobals)
 */
class RecentlyViewedModuleTest extends TestCase {

	private RecentlyViewedModule $module;

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

		$this->module = new RecentlyViewedModule();
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
			$GLOBALS['_test_is_singular']
		);
		$_COOKIE = array();

		parent::tearDown();
	}

	public function test_module_id(): void {
		$this->assertSame( 'recently-viewed', $this->module->get_id() );
	}

	// ── Guest (cookie-based) tracking ──

	public function test_guest_get_empty_without_cookie(): void {
		$this->assertSame( array(), $this->module->get_recently_viewed_ids() );
	}

	public function test_guest_get_ids_from_cookie(): void {
		$_COOKIE['commerce_recently_viewed'] = '[5,10,15]';

		$ids = $this->module->get_recently_viewed_ids();

		$this->assertSame( array( 5, 10, 15 ), $ids );
	}

	public function test_guest_get_ids_with_invalid_cookie(): void {
		$_COOKIE['commerce_recently_viewed'] = 'invalid';

		$this->assertSame( array(), $this->module->get_recently_viewed_ids() );
	}

	// ── Logged-in (user meta) tracking ──

	public function test_logged_in_get_empty(): void {
		$GLOBALS['_test_user_logged_in']  = true;
		$GLOBALS['_test_current_user_id'] = 3;

		$this->assertSame( array(), $this->module->get_recently_viewed_ids() );
	}

	public function test_logged_in_get_ids_from_meta(): void {
		$GLOBALS['_test_user_logged_in']  = true;
		$GLOBALS['_test_current_user_id'] = 3;
		$GLOBALS['_test_user_meta'][3]['_commerce_recently_viewed'] = array( 7, 14 );

		$this->assertSame( array( 7, 14 ), $this->module->get_recently_viewed_ids() );
	}

	public function test_logged_in_ignores_cookie(): void {
		$GLOBALS['_test_user_logged_in']  = true;
		$GLOBALS['_test_current_user_id'] = 3;
		$_COOKIE['commerce_recently_viewed'] = '[99]';

		$this->assertSame( array(), $this->module->get_recently_viewed_ids() );
	}

	// ── Track product view ──

	public function test_track_adds_product_to_front(): void {
		_test_add_wc_product( 10, array( 'name' => 'A' ) );

		$ids = $this->module->track_product_view( 10 );

		$this->assertSame( array( 10 ), $ids );
	}

	public function test_track_moves_existing_to_front(): void {
		_test_add_wc_product( 10, array( 'name' => 'A' ) );
		_test_add_wc_product( 20, array( 'name' => 'B' ) );
		_test_add_wc_product( 30, array( 'name' => 'C' ) );

		$this->module->track_product_view( 10 );
		$this->module->track_product_view( 20 );
		$this->module->track_product_view( 30 );

		// Now view 10 again — it should move to front.
		$ids = $this->module->track_product_view( 10 );

		$this->assertSame( 10, $ids[0] );
		$this->assertSame( 30, $ids[1] );
		$this->assertSame( 20, $ids[2] );
	}

	public function test_track_does_not_duplicate(): void {
		_test_add_wc_product( 10, array( 'name' => 'A' ) );

		$this->module->track_product_view( 10 );
		$this->module->track_product_view( 10 );

		$ids = $this->module->get_recently_viewed_ids();

		$this->assertCount( 1, $ids );
	}

	public function test_track_limits_to_max_items(): void {
		// Register 12 products.
		for ( $i = 1; $i <= 12; $i++ ) {
			_test_add_wc_product( $i, array( 'name' => 'Product ' . $i ) );
		}

		// Track all 12.
		for ( $i = 1; $i <= 12; $i++ ) {
			$this->module->track_product_view( $i );
		}

		$ids = $this->module->get_recently_viewed_ids();

		// MAX_ITEMS is 8.
		$this->assertCount( 8, $ids );
		// Most recent (12) should be at front.
		$this->assertSame( 12, $ids[0] );
		// Oldest beyond limit (5) should be gone.
		$this->assertNotContains( 1, $ids );
		$this->assertNotContains( 4, $ids );
		$this->assertContains( 5, $ids );
	}

	// ── REST: track_view ──

	public function test_rest_track_view_success(): void {
		_test_add_wc_product( 42, array( 'name' => 'Test Product' ) );

		$request = new \WP_REST_Request();
		$request->set_method( 'POST' );
		$request->set_json_params( array( 'product_id' => 42 ) );

		$response = $this->module->track_view( $request );

		$this->assertTrue( $response->data['success'] );
		$this->assertSame( 1, $response->data['count'] );
	}

	public function test_rest_track_view_nonexistent_product(): void {
		$request = new \WP_REST_Request();
		$request->set_method( 'POST' );
		$request->set_json_params( array( 'product_id' => 9999 ) );

		$response = $this->module->track_view( $request );

		$this->assertSame( 404, $response->status );
	}

	// ── REST: get_recently_viewed ──

	public function test_rest_get_recently_viewed_empty(): void {
		$request  = new \WP_REST_Request();
		$response = $this->module->get_recently_viewed( $request );

		$this->assertSame( 0, $response->data['count'] );
		$this->assertSame( array(), $response->data['items'] );
	}

	public function test_rest_get_recently_viewed_with_items(): void {
		_test_add_wc_product( 10, array(
			'name'         => 'Cotton Tee',
			'price_html'   => '$25.00',
			'stock_status' => 'instock',
		) );
		_test_add_wc_product( 20, array(
			'name'         => 'Denim Jeans',
			'price_html'   => '$65.00',
			'stock_status' => 'instock',
		) );

		$this->module->track_product_view( 10 );
		$this->module->track_product_view( 20 );

		$request  = new \WP_REST_Request();
		$response = $this->module->get_recently_viewed( $request );

		$this->assertSame( 2, $response->data['count'] );
		// Most recent first.
		$this->assertSame( 20, $response->data['items'][0]['id'] );
		$this->assertSame( 'Denim Jeans', $response->data['items'][0]['name'] );
		$this->assertSame( 10, $response->data['items'][1]['id'] );
	}

	public function test_rest_get_recently_viewed_skips_deleted_products(): void {
		_test_add_wc_product( 10, array( 'name' => 'Exists' ) );
		// Product 999 is not registered.

		$this->module->track_product_view( 10 );
		$this->module->track_product_view( 999 );

		$request  = new \WP_REST_Request();
		$response = $this->module->get_recently_viewed( $request );

		// Only product 10 should appear.
		$this->assertSame( 1, $response->data['count'] );
		$this->assertSame( 10, $response->data['items'][0]['id'] );
	}

	// ── Nonce verification ──

	public function test_verify_rest_nonce_with_valid_nonce(): void {
		$request = new \WP_REST_Request();
		$request->set_header( 'x-wp-nonce', 'valid_rest_nonce' );

		$this->assertTrue( $this->module->verify_rest_nonce( $request ) );
	}

	public function test_verify_rest_nonce_with_invalid_nonce(): void {
		$request = new \WP_REST_Request();
		$request->set_header( 'x-wp-nonce', 'bad_nonce' );

		$this->assertFalse( $this->module->verify_rest_nonce( $request ) );
	}

	public function test_verify_rest_nonce_without_nonce(): void {
		$request = new \WP_REST_Request();

		$this->assertFalse( $this->module->verify_rest_nonce( $request ) );
	}
}
