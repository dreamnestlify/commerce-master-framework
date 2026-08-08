<?php
/**
 * Tests for StripeAdapter.
 *
 * @package CommerceMaster\Core\Tests\Unit
 */

declare( strict_types=1 );

namespace CommerceMaster\Core\Tests\Unit;

use PHPUnit\Framework\TestCase;
use CommerceMaster\Core\Adapter\Stripe\StripeAdapter;
use CommerceMaster\Core\Config\PaymentConfig;

class StripeAdapterTest extends TestCase {

	private StripeAdapter $adapter;
	private PaymentConfig $config;

	/**
	 * Env vars to clean up after each test.
	 *
	 * @var string[]
	 */
	private array $env_vars_to_clean = array();

	protected function setUp(): void {
		parent::setUp();
		$GLOBALS['_test_options']      = array();
		$GLOBALS['_test_wc_orders']    = array();
		$GLOBALS['_test_notices']      = array();
		$this->config                  = new PaymentConfig(
			array(
				'stripe_enabled' => true,
				'stripe_mode'    => 'sandbox',
			)
		);
		$this->adapter                 = new StripeAdapter( $this->config );
		$this->env_vars_to_clean       = array();
	}

	protected function tearDown(): void {
		foreach ( $this->env_vars_to_clean as $var ) {
			putenv( $var ); // Clear env var.
		}
		parent::tearDown();
	}

	/**
	 * Helper: set an environment variable for the test and track it for cleanup.
	 */
	private function set_env( string $key, string $value ): void {
		putenv( "$key=$value" );
		$this->env_vars_to_clean[] = $key;
	}

	public function test_get_id_returns_stripe(): void {
		$this->assertSame( 'stripe', $this->adapter->get_id() );
	}

	public function test_get_name_returns_stripe(): void {
		$this->assertSame( 'Stripe', $this->adapter->get_name() );
	}

	public function test_is_configured_false_without_env_vars(): void {
		$this->assertFalse( $this->adapter->is_configured() );
	}

	public function test_is_configured_true_with_env_vars(): void {
		$this->set_env( 'STRIPE_TEST_SECRET_KEY', 'sk_test_example' );
		$this->set_env( 'STRIPE_TEST_PUBLISHABLE_KEY', 'pk_test_example' );

		$this->assertTrue( $this->adapter->is_configured() );
	}

	public function test_is_configured_false_with_only_secret_key(): void {
		$this->set_env( 'STRIPE_TEST_SECRET_KEY', 'sk_test_example' );

		$this->assertFalse( $this->adapter->is_configured() );
	}

	public function test_is_configured_uses_live_keys_in_live_mode(): void {
		$live_config = new PaymentConfig(
			array(
				'stripe_enabled' => true,
				'stripe_mode'    => 'live',
			)
		);
		$live_adapter = new StripeAdapter( $live_config );

		// Live env vars not set → not configured.
		$this->assertFalse( $live_adapter->is_configured() );

		// Set live env vars.
		$this->set_env( 'STRIPE_LIVE_SECRET_KEY', 'sk_live_example' );
		$this->set_env( 'STRIPE_LIVE_PUBLISHABLE_KEY', 'pk_live_example' );

		$this->assertTrue( $live_adapter->is_configured() );
	}

	public function test_get_supported_currencies_contains_major_currencies(): void {
		$currencies = $this->adapter->get_supported_currencies();

		$this->assertContains( 'USD', $currencies );
		$this->assertContains( 'EUR', $currencies );
		$this->assertContains( 'GBP', $currencies );
		$this->assertContains( 'JPY', $currencies );
		$this->assertContains( 'CNY', $currencies );
	}

	public function test_get_supported_currencies_count(): void {
		$this->assertCount( 24, $this->adapter->get_supported_currencies() );
	}

	public function test_supports_currency_usd(): void {
		$this->assertTrue( $this->adapter->supports_currency( 'USD' ) );
	}

	public function test_supports_currency_lowercase(): void {
		$this->assertTrue( $this->adapter->supports_currency( 'usd' ) );
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
		$this->set_env( 'STRIPE_TEST_SECRET_KEY', 'sk_test_example' );
		$this->set_env( 'STRIPE_TEST_PUBLISHABLE_KEY', 'pk_test_example' );

		// No order added to test data.
		$result = $this->adapter->process_payment( 999, array() );

		$this->assertFalse( $result->is_success() );
		$this->assertSame( 'failed', $result->get_status() );
		$this->assertStringContainsString( 'Order not found', $result->get_message() );
	}

	public function test_process_refund_fails_when_not_configured(): void {
		$result = $this->adapter->process_refund( 1, 29.99 );

		$this->assertFalse( $result->is_success() );
		$this->assertStringContainsString( 'not configured', $result->get_message() );
		$this->assertSame( 0.0, $result->get_refunded_amount() );
	}

	public function test_process_refund_fails_when_order_not_found(): void {
		$this->set_env( 'STRIPE_TEST_SECRET_KEY', 'sk_test_example' );
		$this->set_env( 'STRIPE_TEST_PUBLISHABLE_KEY', 'pk_test_example' );

		$result = $this->adapter->process_refund( 999, 29.99 );

		$this->assertFalse( $result->is_success() );
		$this->assertStringContainsString( 'Order not found', $result->get_message() );
	}

	public function test_process_refund_fails_without_transaction_id(): void {
		$this->set_env( 'STRIPE_TEST_SECRET_KEY', 'sk_test_example' );
		$this->set_env( 'STRIPE_TEST_PUBLISHABLE_KEY', 'pk_test_example' );

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
		$this->assertStringContainsString( 'No transaction ID', $result->get_message() );
	}

	public function test_implements_payment_adapter_interface(): void {
		$this->assertInstanceOf(
			\CommerceMaster\Core\Adapter\PaymentAdapterInterface::class,
			$this->adapter
		);
	}
}
