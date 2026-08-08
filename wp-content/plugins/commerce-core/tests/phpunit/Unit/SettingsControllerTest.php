<?php
/**
 * Tests for REST SettingsController.
 *
 * Covers:
 * - Unauthenticated requests are rejected
 * - Users without manage_options are rejected
 * - Valid REST nonce + manage_options updates successfully
 * - Invalid nonce is rejected
 * - Input is sanitized
 * - Sensitive fields (credentials) are not exposed via GET
 *
 * @package CommerceMaster\Core\Tests\Unit
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Tests\Unit;

use PHPUnit\Framework\TestCase;
use CommerceMaster\Core\Rest\SettingsController;
use CommerceMaster\Core\Module\SettingsModule;
use CommerceMaster\Core\Module\SecurityModule;

class SettingsControllerTest extends TestCase {

	private SettingsController $controller;

	protected function setUp(): void {
		parent::setUp();
		$GLOBALS['_test_options'] = array();
		$GLOBALS['_test_user_can'] = true;

		// Activate settings module to set defaults.
		$module = new SettingsModule();
		$module->activate();

		$this->controller = new SettingsController();
	}

	protected function tearDown(): void {
		unset( $GLOBALS['_test_user_can'] );
		parent::tearDown();
	}

	// -- Permission callback tests -----------------------------------

	public function test_get_settings_permissions_returns_true_with_manage_options(): void {
		$GLOBALS['_test_user_can'] = true;
		$this->assertTrue( $this->controller->get_settings_permissions() );
	}

	public function test_get_settings_permissions_returns_false_without_manage_options(): void {
		$GLOBALS['_test_user_can'] = false;
		$this->assertFalse( $this->controller->get_settings_permissions() );
	}

	public function test_update_settings_permissions_returns_true_with_manage_options(): void {
		$GLOBALS['_test_user_can'] = true;
		$this->assertTrue( $this->controller->update_settings_permissions() );
	}

	public function test_update_settings_permissions_returns_false_without_manage_options(): void {
		$GLOBALS['_test_user_can'] = false;
		$this->assertFalse( $this->controller->update_settings_permissions() );
	}

	// -- Unauthenticated / no permission rejection -------------------

	public function test_unauthenticated_get_settings_rejected(): void {
		$GLOBALS['_test_user_can'] = false;
		$this->assertFalse( $this->controller->get_settings_permissions() );
	}

	public function test_unauthenticated_update_settings_rejected(): void {
		$GLOBALS['_test_user_can'] = false;
		$this->assertFalse( $this->controller->update_settings_permissions() );
	}

	public function test_no_manage_options_update_rejected(): void {
		$GLOBALS['_test_user_can'] = false;
		$this->assertFalse( $this->controller->update_settings_permissions() );
	}

	// -- Valid REST nonce + permission → success ---------------------

	public function test_valid_rest_nonce_and_permission_updates_successfully(): void {
		$GLOBALS['_test_user_can'] = true;

		$request = new \WP_REST_Request();
		$request->set_method( 'POST' );
		$request->set_header( 'X-WP-Nonce', 'valid_rest_nonce' );
		$request->set_json_params(
			array(
				'brand' => array(
					'name' => 'Updated Brand Name',
					'tagline' => 'New Tagline',
					'logo_id' => 0,
				),
				'market' => array(
					'default_locale' => 'en_US',
					'base_currency' => 'USD',
					'enabled_currencies' => array( 'USD', 'EUR' ),
					'default_market' => 'EU',
				),
				'support' => array(
					'email' => 'support@test.com',
					'phone' => '',
				),
				'analytics' => array(
					'ga4_measurement_id' => '',
					'meta_pixel_id' => '',
					'tiktok_pixel_id' => '',
					'google_ads_id' => '',
				),
				'payment' => array(
					'stripe_enabled' => true,
					'paypal_enabled' => false,
				),
			)
		);

		$response = $this->controller->update_settings( $request );

		$this->assertIsArray( $response );
		$this->assertTrue( $response['success'] );

		// Verify the option was actually updated.
		$stored = get_option( SettingsModule::OPTION_KEY );
		$this->assertSame( 'Updated Brand Name', $stored['brand']['name'] );
	}

	// -- Invalid nonce rejection -------------------------------------

	public function test_invalid_rest_nonce_rejected(): void {
		$GLOBALS['_test_user_can'] = true;

		$request = new \WP_REST_Request();
		$request->set_method( 'POST' );
		$request->set_header( 'X-WP-Nonce', 'invalid_nonce' );
		$request->set_json_params(
			array(
				'brand' => array( 'name' => 'Hacked Brand' ),
			)
		);

		$response = $this->controller->update_settings( $request );

		$this->assertInstanceOf( \WP_Error::class, $response );
		$this->assertSame( 'rest_forbidden', $response->get_error_code() );

		// Verify settings were NOT updated.
		$stored = get_option( SettingsModule::OPTION_KEY );
		$this->assertNotSame( 'Hacked Brand', $stored['brand']['name'] );
	}

	public function test_missing_rest_nonce_rejected(): void {
		$GLOBALS['_test_user_can'] = true;

		$request = new \WP_REST_Request();
		$request->set_method( 'POST' );
		// No X-WP-Nonce header set.
		$request->set_json_params(
			array(
				'brand' => array( 'name' => 'No Nonce Brand' ),
			)
		);

		$response = $this->controller->update_settings( $request );

		$this->assertInstanceOf( \WP_Error::class, $response );
		$this->assertSame( 'rest_forbidden', $response->get_error_code() );
	}

	// -- Input sanitization ------------------------------------------

	public function test_input_is_sanitized(): void {
		$GLOBALS['_test_user_can'] = true;

		$request = new \WP_REST_Request();
		$request->set_method( 'POST' );
		$request->set_header( 'X-WP-Nonce', 'valid_rest_nonce' );
		$request->set_json_params(
			array(
				'brand' => array(
					'name' => '  <script>alert(1)</script> Sanitized Brand  ',
					'tagline' => 'Great Fashion',
					'logo_id' => '99xyz',
				),
				'market' => array(
					'default_locale' => 'en_US',
					'base_currency' => 'USD',
					'enabled_currencies' => array( 'USD', 'EUR' ),
					'default_market' => 'EU',
				),
				'support' => array(
					'email' => 'test@example.com',
					'phone' => '',
				),
				'analytics' => array(
					'ga4_measurement_id' => '',
					'meta_pixel_id' => '',
					'tiktok_pixel_id' => '',
					'google_ads_id' => '',
				),
				'payment' => array(
					'stripe_enabled' => true,
					'paypal_enabled' => false,
				),
			)
		);

		$response = $this->controller->update_settings( $request );
		$this->assertTrue( $response['success'] );

		$stored = get_option( SettingsModule::OPTION_KEY );
		// Script tags and whitespace stripped.
		$this->assertSame( 'alert(1) Sanitized Brand', $stored['brand']['name'] );
		// Non-numeric chars stripped from logo_id.
		$this->assertSame( 99, $stored['brand']['logo_id'] );
	}

	// -- Sensitive fields not exposed via GET ------------------------

	public function test_sensitive_fields_not_exposed_via_get(): void {
		// Store some data that includes a 'credentials' key.
		$stored = get_option( SettingsModule::OPTION_KEY );
		$stored['credentials'] = array(
			'stripe_secret_key' => 'sk_live_super_secret',
			'paypal_client_secret' => 'paypal_secret_value',
		);
		update_option( SettingsModule::OPTION_KEY, $stored );

		$request = new \WP_REST_Request();
		$request->set_method( 'GET' );

		$response = $this->controller->get_settings( $request );

		$this->assertIsArray( $response );
		// credentials must NOT be in the REST response.
		$this->assertArrayNotHasKey( 'credentials', $response );

		// But brand name should be present.
		$this->assertArrayHasKey( 'brand', $response );
	}

	// -- REST nonce uses wp_rest action, not commerce_core_nonce -----

	public function test_rest_nonce_uses_wp_rest_action(): void {
		// Verify that the REST nonce action constant is 'wp_rest'.
		$this->assertSame( 'wp_rest', SecurityModule::REST_NONCE_ACTION );
	}

	public function test_admin_nonce_does_not_work_for_rest(): void {
		$GLOBALS['_test_user_can'] = true;

		$request = new \WP_REST_Request();
		$request->set_method( 'POST' );
		// Use admin nonce value — should NOT work for REST.
		$request->set_header( 'X-WP-Nonce', 'valid_nonce' );
		$request->set_json_params(
			array(
				'brand' => array( 'name' => 'Admin Nonce Brand' ),
			)
		);

		$response = $this->controller->update_settings( $request );

		$this->assertInstanceOf( \WP_Error::class, $response );
		$this->assertSame( 'rest_forbidden', $response->get_error_code() );
	}
}
