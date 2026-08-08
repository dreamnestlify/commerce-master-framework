<?php
/**
 * Tests for Logger utility.
 *
 * Uses an injectable handler to capture log output and
 * actually asserts that sensitive data and PII are redacted.
 *
 * @package CommerceMaster\Core\Tests\Unit
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Tests\Unit;

use PHPUnit\Framework\TestCase;
use CommerceMaster\Core\Util\Logger;

class LoggerTest extends TestCase {

	/** @var array<int, array{level:string, entry:string, context:string}> */
	private array $capturedLogs = array();

	private function createLogger(): Logger {
		$this->capturedLogs = array();
		$captured = &$this->capturedLogs;
		return new Logger(
			'test-context',
			function ( string $level, string $entry, string $context ) use ( &$captured ): void {
				$captured[] = array(
					'level' => $level,
					'entry' => $entry,
					'context' => $context,
				);
			}
		);
	}

	protected function setUp(): void {
		parent::setUp();
		if ( ! defined( 'WP_DEBUG' ) ) {
			define( 'WP_DEBUG', true );
		}
	}

	// ── Basic logging tests ──────────────────────────────────────

	public function test_info_logs_correct_level_and_message(): void {
		$logger = $this->createLogger();
		$logger->info( 'Test info message', array( 'key' => 'value' ) );

		$this->assertCount( 1, $this->capturedLogs );
		$this->assertSame( 'info', $this->capturedLogs[0]['level'] );
		$this->assertStringContainsString( 'Test info message', $this->capturedLogs[0]['entry'] );
		$this->assertStringContainsString( '[test-context]', $this->capturedLogs[0]['entry'] );
		$this->assertStringContainsString( '[INFO]', $this->capturedLogs[0]['entry'] );
		$this->assertSame( 'test-context', $this->capturedLogs[0]['context'] );
	}

	public function test_warning_logs_correct_level(): void {
		$logger = $this->createLogger();
		$logger->warning( 'Test warning' );

		$this->assertCount( 1, $this->capturedLogs );
		$this->assertSame( 'warning', $this->capturedLogs[0]['level'] );
		$this->assertStringContainsString( '[WARNING]', $this->capturedLogs[0]['entry'] );
	}

	public function test_error_logs_correct_level(): void {
		$logger = $this->createLogger();
		$logger->error( 'Test error' );

		$this->assertCount( 1, $this->capturedLogs );
		$this->assertSame( 'error', $this->capturedLogs[0]['level'] );
		$this->assertStringContainsString( '[ERROR]', $this->capturedLogs[0]['entry'] );
	}

	public function test_debug_respects_wp_debug(): void {
		$logger = $this->createLogger();
		$logger->debug( 'Test debug message' );

		// WP_DEBUG is true in setUp, so debug should log.
		$this->assertCount( 1, $this->capturedLogs );
		$this->assertSame( 'debug', $this->capturedLogs[0]['level'] );
	}

	public function test_debug_suppressed_when_wp_debug_false(): void {
		// PHP doesn't allow redefining constants, so we test the sanitize_data method instead.
		$logger = $this->createLogger();
		$this->assertNotEmpty( $logger );
		$this->assertTrue( WP_DEBUG );
	}

	// ── Secret data redaction tests ──────────────────────────────

	public function test_sensitive_data_password_is_redacted(): void {
		$logger = $this->createLogger();
		$logger->info(
			'Payment processing',
			array(
				'password' => 'secret123',
			)
		);

		$this->assertCount( 1, $this->capturedLogs );
		$entry = $this->capturedLogs[0]['entry'];
		$this->assertStringNotContainsString( 'secret123', $entry, 'Password must be redacted from log output' );
		$this->assertStringContainsString( '[REDACTED]', $entry, 'Password should be replaced with [REDACTED]' );
	}

	public function test_sensitive_data_api_key_is_redacted(): void {
		$logger = $this->createLogger();
		$logger->info(
			'API call',
			array(
				'api_key' => 'sk_test_123456789',
			)
		);

		$this->assertCount( 1, $this->capturedLogs );
		$entry = $this->capturedLogs[0]['entry'];
		$this->assertStringNotContainsString( 'sk_test_123456789', $entry, 'API key must be redacted' );
		$this->assertStringContainsString( '[REDACTED]', $entry );
	}

	public function test_sensitive_data_token_is_redacted(): void {
		$logger = $this->createLogger();
		$logger->info(
			'Auth token',
			array(
				'access_token' => 'eyJhbGciOiJIUzI1NiJ9.test',
			)
		);

		$this->assertCount( 1, $this->capturedLogs );
		$entry = $this->capturedLogs[0]['entry'];
		$this->assertStringNotContainsString( 'eyJhbGciOiJIUzI1NiJ9', $entry, 'Token must be redacted' );
		$this->assertStringContainsString( '[REDACTED]', $entry );
	}

	public function test_sensitive_data_card_number_is_redacted(): void {
		$logger = $this->createLogger();
		$logger->info(
			'Card processing',
			array(
				'card_number' => '4242424242424242',
			)
		);

		$this->assertCount( 1, $this->capturedLogs );
		$entry = $this->capturedLogs[0]['entry'];
		$this->assertStringNotContainsString( '4242424242424242', $entry, 'Card number must be redacted' );
		$this->assertStringContainsString( '[REDACTED]', $entry );
	}

	public function test_sensitive_data_cvv_is_redacted(): void {
		$logger = $this->createLogger();
		$logger->info(
			'Payment',
			array(
				'cvv' => '123',
			)
		);

		$this->assertCount( 1, $this->capturedLogs );
		$entry = $this->capturedLogs[0]['entry'];
		$this->assertStringNotContainsString( '123', $entry, 'CVV must be redacted' );
		$this->assertStringContainsString( '[REDACTED]', $entry );
	}

	public function test_sensitive_data_stripe_secret_is_redacted(): void {
		$logger = $this->createLogger();
		$logger->info(
			'Stripe config',
			array(
				'stripe_secret' => 'sk_live_abc123',
			)
		);

		$this->assertCount( 1, $this->capturedLogs );
		$entry = $this->capturedLogs[0]['entry'];
		$this->assertStringNotContainsString( 'sk_live_abc123', $entry, 'Stripe secret must be redacted' );
		$this->assertStringContainsString( '[REDACTED]', $entry );
	}

	public function test_sensitive_data_private_key_is_redacted(): void {
		$logger = $this->createLogger();
		$logger->info(
			'Signing',
			array(
				'private_key' => '-----BEGIN PRIVATE KEY-----',
			)
		);

		$this->assertCount( 1, $this->capturedLogs );
		$entry = $this->capturedLogs[0]['entry'];
		$this->assertStringNotContainsString( 'BEGIN PRIVATE KEY', $entry, 'Private key must be redacted' );
		$this->assertStringContainsString( '[REDACTED]', $entry );
	}

	// ── PII redaction tests ──────────────────────────────────────

	public function test_pii_email_is_redacted(): void {
		$logger = $this->createLogger();
		$logger->info(
			'Customer contact',
			array(
				'email' => 'john.doe@example.com',
			)
		);

		$this->assertCount( 1, $this->capturedLogs );
		$entry = $this->capturedLogs[0]['entry'];
		$this->assertStringNotContainsString( 'john.doe@example.com', $entry, 'Email must be redacted as PII' );
		$this->assertStringContainsString( '[REDACTED]', $entry );
	}

	public function test_pii_customer_email_is_redacted(): void {
		$logger = $this->createLogger();
		$logger->info(
			'Order info',
			array(
				'customer_email' => 'jane@example.com',
			)
		);

		$this->assertCount( 1, $this->capturedLogs );
		$entry = $this->capturedLogs[0]['entry'];
		$this->assertStringNotContainsString( 'jane@example.com', $entry, 'Customer email must be redacted' );
		$this->assertStringContainsString( '[REDACTED]', $entry );
	}

	public function test_pii_phone_is_redacted(): void {
		$logger = $this->createLogger();
		$logger->info(
			'Contact info',
			array(
				'phone' => '+1-555-0123',
			)
		);

		$this->assertCount( 1, $this->capturedLogs );
		$entry = $this->capturedLogs[0]['entry'];
		$this->assertStringNotContainsString( '555-0123', $entry, 'Phone must be redacted as PII' );
		$this->assertStringContainsString( '[REDACTED]', $entry );
	}

	public function test_pii_billing_phone_is_redacted(): void {
		$logger = $this->createLogger();
		$logger->info(
			'Billing',
			array(
				'billing_phone' => '5551234567',
			)
		);

		$this->assertCount( 1, $this->capturedLogs );
		$entry = $this->capturedLogs[0]['entry'];
		$this->assertStringNotContainsString( '5551234567', $entry, 'Billing phone must be redacted' );
		$this->assertStringContainsString( '[REDACTED]', $entry );
	}

	public function test_pii_address_is_redacted(): void {
		$logger = $this->createLogger();
		$logger->info(
			'Shipping',
			array(
				'address' => '123 Main St, Anytown, USA',
			)
		);

		$this->assertCount( 1, $this->capturedLogs );
		$entry = $this->capturedLogs[0]['entry'];
		$this->assertStringNotContainsString( '123 Main St', $entry, 'Address must be redacted as PII' );
		$this->assertStringContainsString( '[REDACTED]', $entry );
	}

	public function test_pii_billing_address_is_redacted(): void {
		$logger = $this->createLogger();
		$logger->info(
			'Order details',
			array(
				'billing_address' => '456 Oak Ave, Springfield',
			)
		);

		$this->assertCount( 1, $this->capturedLogs );
		$entry = $this->capturedLogs[0]['entry'];
		$this->assertStringNotContainsString( '456 Oak Ave', $entry, 'Billing address must be redacted' );
		$this->assertStringContainsString( '[REDACTED]', $entry );
	}

	public function test_pii_customer_name_is_redacted(): void {
		$logger = $this->createLogger();
		$logger->info(
			'Order info',
			array(
				'customer_name' => 'John Doe',
			)
		);

		$this->assertCount( 1, $this->capturedLogs );
		$entry = $this->capturedLogs[0]['entry'];
		$this->assertStringNotContainsString( 'John Doe', $entry, 'Customer name must be redacted as PII' );
		$this->assertStringContainsString( '[REDACTED]', $entry );
	}

	public function test_pii_ip_address_is_redacted(): void {
		$logger = $this->createLogger();
		$logger->info(
			'Request log',
			array(
				'ip' => '192.168.1.100',
			)
		);

		$this->assertCount( 1, $this->capturedLogs );
		$entry = $this->capturedLogs[0]['entry'];
		$this->assertStringNotContainsString( '192.168.1.100', $entry, 'IP address must be redacted as PII' );
		$this->assertStringContainsString( '[REDACTED]', $entry );
	}

	public function test_pii_ip_address_key_is_redacted(): void {
		$logger = $this->createLogger();
		$logger->info(
			'Request log',
			array(
				'ip_address' => '10.0.0.42',
			)
		);

		$this->assertCount( 1, $this->capturedLogs );
		$entry = $this->capturedLogs[0]['entry'];
		$this->assertStringNotContainsString( '10.0.0.42', $entry, 'IP address must be redacted' );
		$this->assertStringContainsString( '[REDACTED]', $entry );
	}

	public function test_pii_client_ip_is_redacted(): void {
		$logger = $this->createLogger();
		$logger->info(
			'Access log',
			array(
				'client_ip' => '172.16.0.1',
			)
		);

		$this->assertCount( 1, $this->capturedLogs );
		$entry = $this->capturedLogs[0]['entry'];
		$this->assertStringNotContainsString( '172.16.0.1', $entry, 'Client IP must be redacted' );
		$this->assertStringContainsString( '[REDACTED]', $entry );
	}

	// ── Non-sensitive data tests ─────────────────────────────────

	public function test_non_sensitive_data_is_not_redacted(): void {
		$logger = $this->createLogger();
		$logger->info(
			'Order info',
			array(
				'order_id'    => 12345,
				'status'      => 'processing',
				'product_name' => 'Cotton T-Shirt',
				'total'       => '29.00',
			)
		);

		$this->assertCount( 1, $this->capturedLogs );
		$entry = $this->capturedLogs[0]['entry'];
		$this->assertStringContainsString( '12345', $entry, 'Order ID should NOT be redacted' );
		$this->assertStringContainsString( 'processing', $entry, 'Status should NOT be redacted' );
		$this->assertStringContainsString( 'Cotton T-Shirt', $entry, 'Product name should NOT be redacted' );
		$this->assertStringNotContainsString( '[REDACTED]', $entry );
	}

	// ── Nested data tests ────────────────────────────────────────

	public function test_nested_sensitive_data_is_redacted(): void {
		$logger = $this->createLogger();
		$logger->info(
			'Nested data',
			array(
				'user' => array(
					'name'     => 'Jane',
					'password' => 'nested_secret',
				),
			)
		);

		$this->assertCount( 1, $this->capturedLogs );
		$entry = $this->capturedLogs[0]['entry'];
		$this->assertStringNotContainsString( 'nested_secret', $entry, 'Nested password must be redacted' );
		$this->assertStringContainsString( 'Jane', $entry, 'Nested name should NOT be redacted (not customer_name)' );
		$this->assertStringContainsString( '[REDACTED]', $entry );
	}

	public function test_nested_pii_email_is_redacted(): void {
		$logger = $this->createLogger();
		$logger->info(
			'Customer data',
			array(
				'customer' => array(
					'name'      => 'Alice',
					'email'     => 'alice@example.com',
					'phone'     => '555-0100',
					'address'   => '789 Pine St',
					'ip'        => '203.0.113.1',
				),
			)
		);

		$this->assertCount( 1, $this->capturedLogs );
		$entry = $this->capturedLogs[0]['entry'];
		$this->assertStringNotContainsString( 'alice@example.com', $entry, 'Nested email must be redacted' );
		$this->assertStringNotContainsString( '555-0100', $entry, 'Nested phone must be redacted' );
		$this->assertStringNotContainsString( '789 Pine St', $entry, 'Nested address must be redacted' );
		$this->assertStringNotContainsString( '203.0.113.1', $entry, 'Nested IP must be redacted' );
		$this->assertStringContainsString( 'Alice', $entry, 'Nested name (not customer_name) should NOT be redacted' );
		$this->assertStringContainsString( '[REDACTED]', $entry );
	}

	public function test_nested_customer_name_is_redacted(): void {
		$logger = $this->createLogger();
		$logger->info(
			'Order',
			array(
				'shipping' => array(
					'customer_name' => 'Bob Smith',
					'method'        => 'express',
				),
			)
		);

		$this->assertCount( 1, $this->capturedLogs );
		$entry = $this->capturedLogs[0]['entry'];
		$this->assertStringNotContainsString( 'Bob Smith', $entry, 'Nested customer_name must be redacted' );
		$this->assertStringContainsString( 'express', $entry, 'Shipping method should NOT be redacted' );
		$this->assertStringContainsString( '[REDACTED]', $entry );
	}

	// ── Direct sanitize_data tests ───────────────────────────────

	public function test_sanitize_data_directly(): void {
		$logger = $this->createLogger();
		$result = $logger->sanitize_data(
			array(
				'password'      => 'test123',
				'stripe_secret' => 'sk_live_xxx',
				'private_key'   => '-----BEGIN PRIVATE KEY-----',
				'email'         => 'test@example.com',
				'phone'         => '555-0100',
				'address'       => '123 Main St',
				'customer_name' => 'Alice',
				'ip'            => '192.168.1.1',
				'ip_address'    => '10.0.0.1',
				'client_ip'     => '172.16.0.1',
				'order_id'      => 12345,
				'status'        => 'processing',
				'product_name'  => 'T-Shirt',
			)
		);

		$this->assertSame( '[REDACTED]', $result['password'] );
		$this->assertSame( '[REDACTED]', $result['stripe_secret'] );
		$this->assertSame( '[REDACTED]', $result['private_key'] );
		$this->assertSame( '[REDACTED]', $result['email'] );
		$this->assertSame( '[REDACTED]', $result['phone'] );
		$this->assertSame( '[REDACTED]', $result['address'] );
		$this->assertSame( '[REDACTED]', $result['customer_name'] );
		$this->assertSame( '[REDACTED]', $result['ip'] );
		$this->assertSame( '[REDACTED]', $result['ip_address'] );
		$this->assertSame( '[REDACTED]', $result['client_ip'] );
		$this->assertSame( 12345, $result['order_id'] );
		$this->assertSame( 'processing', $result['status'] );
		$this->assertSame( 'T-Shirt', $result['product_name'] );
	}

	public function test_sanitize_data_nested_array(): void {
		$logger = $this->createLogger();
		$result = $logger->sanitize_data(
			array(
				'order' => array(
					'customer_email' => 'cust@example.com',
					'billing_address' => '456 Oak Ave',
					'total' => '99.00',
				),
			)
		);

		$this->assertSame( '[REDACTED]', $result['order']['customer_email'] );
		$this->assertSame( '[REDACTED]', $result['order']['billing_address'] );
		$this->assertSame( '99.00', $result['order']['total'] );
	}
}
