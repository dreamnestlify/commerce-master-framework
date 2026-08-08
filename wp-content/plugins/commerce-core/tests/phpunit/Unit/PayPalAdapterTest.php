<?php
/**
 * Tests for PayPalAdapter.
 *
 * @package CommerceMaster\Core\Tests\Unit
 */

declare( strict_types=1 );

namespace CommerceMaster\Core\Tests\Unit;

use PHPUnit\Framework\TestCase;
use CommerceMaster\Core\Adapter\PayPal\PayPalAdapter;
use CommerceMaster\Core\Config\PaymentConfig;

class PayPalAdapterTest extends TestCase {

	private PayPalAdapter $adapter;
	private PaymentConfig $config;

	/**
	 * Env vars to clean up after each test.
	 *
	 * @var string[]
	 */
	private array $env_vars_to_clean = array();

	protected function setUp(): void {
		parent::setUp();
		$GLOBALS['_test_options']          = array();
		$GLOBALS['_test_wc_orders']        = array();
		$GLOBALS['_test_notices']          = array();
		$GLOBALS['_test_wp_remote_response'] = null;
		$this->config                      = new PaymentConfig(
			array(
				'paypal_enabled' => true,
				'paypal_mode'    => 'sandbox',
			)
		);
		$this->adapter                     = new PayPalAdapter( $this->config );
		$this->env_vars_to_clean           = array();
	}

	protected function tearDown(): void {
		foreach ( $this->env_vars_to_clean as $var ) {
			putenv( $var ); // Clear env var.
		}
		unset( $GLOBALS['_test_wp_remote_response'] );
		parent::tearDown();
	}

	/**
	 * Helper: set an environment variable for the test and track it for cleanup.
	 */
	private function set_env( string $key, string $value ): void {
		putenv( "$key=$value" );
		$this->env_vars_to_clean[] = $key;
	}

	public function test_get_id_returns_paypal(): void {
		$this->assertSame( 'paypal', $this->adapter->get_id() );
	}

	public function test_get_name_returns_paypal(): void {
		$this->assertSame( 'PayPal', $this->adapter->get_name() );
	}

	public function test_is_configured_false_without_env_vars(): void {
		$this->assertFalse( $this->adapter->is_configured() );
	}

	public function test_is_configured_true_with_env_vars(): void {
		$this->set_env( 'PAYPAL_SANDBOX_CLIENT_ID', 'test_client_id' );
		$this->set_env( 'PAYPAL_SANDBOX_CLIENT_SECRET', 'test_client_secret' );

		$this->assertTrue( $this->adapter->is_configured() );
	}

	public function test_is_configured_false_with_only_client_id(): void {
		$this->set_env( 'PAYPAL_SANDBOX_CLIENT_ID', 'test_client_id' );

		$this->assertFalse( $this->adapter->is_configured() );
	}

	public function test_is_configured_uses_live_keys_in_live_mode(): void {
		$live_config = new PaymentConfig(
			array(
				'paypal_enabled' => true,
				'paypal_mode'    => 'live',
			)
		);
		$live_adapter = new PayPalAdapter( $live_config );

		$this->assertFalse( $live_adapter->is_configured() );

		$this->set_env( 'PAYPAL_LIVE_CLIENT_ID', 'live_client_id' );
		$this->set_env( 'PAYPAL_LIVE_CLIENT_SECRET', 'live_client_secret' );

		$this->assertTrue( $live_adapter->is_configured() );
	}

	public function test_get_supported_currencies_contains_major_currencies(): void {
		$currencies = $this->adapter->get_supported_currencies();

		$this->assertContains( 'USD', $currencies );
		$this->assertContains( 'EUR', $currencies );
		$this->assertContains( 'GBP', $currencies );
		$this->assertContains( 'JPY', $currencies );
	}

	public function test_supports_currency_usd(): void {
		$this->assertTrue( $this->adapter->supports_currency( 'USD' ) );
	}

	public function test_supports_currency_lowercase(): void {
		$this->assertTrue( $this->adapter->supports_currency( 'eur' ) );
	}

	public function test_supports_currency_unsupported(): void {
		$this->assertFalse( $this->adapter->supports_currency( 'XYZ' ) );
	}

	public function test_process_payment_fails_when_not_configured(): void {
		$result = $this->adapter->process_payment( 1, array() );

		$this->assertFalse( $result->is_success() );
		$this->assertSame( 'failed', $result->get_status() );
		$this->assertStringContainsString( 'not configured', $result->get_message() );
	}

	public function test_process_payment_fails_when_order_not_found(): void {
		$this->set_env( 'PAYPAL_SANDBOX_CLIENT_ID', 'test_client_id' );
		$this->set_env( 'PAYPAL_SANDBOX_CLIENT_SECRET', 'test_client_secret' );

		$result = $this->adapter->process_payment( 999, array() );

		$this->assertFalse( $result->is_success() );
		$this->assertSame( 'failed', $result->get_status() );
		$this->assertStringContainsString( 'Order not found', $result->get_message() );
	}

	public function test_process_payment_fails_when_oauth_fails(): void {
		$this->set_env( 'PAYPAL_SANDBOX_CLIENT_ID', 'test_client_id' );
		$this->set_env( 'PAYPAL_SANDBOX_CLIENT_SECRET', 'test_client_secret' );

		_test_add_wc_order(
			100,
			array(
				'total'    => '29.99',
				'currency' => 'USD',
			)
		);

		// wp_remote_post returns WP_Error when no mock is set.
		unset( $GLOBALS['_test_wp_remote_response'] );

		$result = $this->adapter->process_payment( 100, array() );

		$this->assertFalse( $result->is_success() );
		$this->assertSame( 'failed', $result->get_status() );
		$this->assertStringContainsString( 'Failed to create PayPal order', $result->get_message() );
	}

	public function test_process_refund_fails_when_not_configured(): void {
		$result = $this->adapter->process_refund( 1, 29.99 );

		$this->assertFalse( $result->is_success() );
		$this->assertStringContainsString( 'not configured', $result->get_message() );
		$this->assertSame( 0.0, $result->get_refunded_amount() );
	}

	public function test_process_refund_fails_when_order_not_found(): void {
		$this->set_env( 'PAYPAL_SANDBOX_CLIENT_ID', 'test_client_id' );
		$this->set_env( 'PAYPAL_SANDBOX_CLIENT_SECRET', 'test_client_secret' );

		$result = $this->adapter->process_refund( 999, 29.99 );

		$this->assertFalse( $result->is_success() );
		$this->assertStringContainsString( 'Order not found', $result->get_message() );
	}

	public function test_process_refund_fails_without_capture_id(): void {
		$this->set_env( 'PAYPAL_SANDBOX_CLIENT_ID', 'test_client_id' );
		$this->set_env( 'PAYPAL_SANDBOX_CLIENT_SECRET', 'test_client_secret' );

		_test_add_wc_order(
			100,
			array(
				'total'           => '29.99',
				'currency'        => 'USD',
				'transaction_id'  => '',
			)
		);

		$result = $this->adapter->process_refund( 100, 29.99 );

		$this->assertFalse( $result->is_success() );
		$this->assertStringContainsString( 'No capture ID', $result->get_message() );
	}

	public function test_implements_payment_adapter_interface(): void {
		$this->assertInstanceOf(
			\CommerceMaster\Core\Adapter\PaymentAdapterInterface::class,
			$this->adapter
		);
	}
}
