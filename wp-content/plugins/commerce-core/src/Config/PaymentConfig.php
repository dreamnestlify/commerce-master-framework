<?php
/**
 * Payment Configuration — payment gateway settings and credentials.
 *
 * API keys are read from environment variables for security.
 * Only enable/disable toggles and mode settings are stored in options.
 *
 * @package CommerceMaster\Core\Config
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Config;

class PaymentConfig {

	/**
	 * @var array<string, mixed>
	 */
	private array $data;

	/**
	 * @param array<string, mixed> $data Raw config data.
	 */
	public function __construct( array $data ) {
		$this->data = $data;
	}

	public function is_stripe_enabled(): bool {
		return (bool) ( $this->data['stripe_enabled'] ?? false );
	}

	public function is_paypal_enabled(): bool {
		return (bool) ( $this->data['paypal_enabled'] ?? false );
	}

	public function get_stripe_mode(): string {
		return (string) ( $this->data['stripe_mode'] ?? 'sandbox' );
	}

	public function get_paypal_mode(): string {
		return (string) ( $this->data['paypal_mode'] ?? 'sandbox' );
	}

	public function is_stripe_live(): bool {
		return 'live' === $this->get_stripe_mode();
	}

	public function is_paypal_live(): bool {
		return 'live' === $this->get_paypal_mode();
	}

	public function get_stripe_secret_key(): string {
		$key = $this->is_stripe_live() ? 'STRIPE_LIVE_SECRET_KEY' : 'STRIPE_TEST_SECRET_KEY';
		$val = getenv( $key );

		return ( false !== $val && '' !== $val ) ? $val : '';
	}

	public function get_stripe_publishable_key(): string {
		$key = $this->is_stripe_live() ? 'STRIPE_LIVE_PUBLISHABLE_KEY' : 'STRIPE_TEST_PUBLISHABLE_KEY';
		$val = getenv( $key );

		return ( false !== $val && '' !== $val ) ? $val : '';
	}

	public function get_stripe_webhook_secret(): string {
		$val = getenv( 'STRIPE_WEBHOOK_SECRET' );

		return ( false !== $val && '' !== $val ) ? $val : '';
	}

	public function get_paypal_client_id(): string {
		$key = $this->is_paypal_live() ? 'PAYPAL_LIVE_CLIENT_ID' : 'PAYPAL_SANDBOX_CLIENT_ID';
		$val = getenv( $key );

		return ( false !== $val && '' !== $val ) ? $val : '';
	}

	public function get_paypal_client_secret(): string {
		$key = $this->is_paypal_live() ? 'PAYPAL_LIVE_CLIENT_SECRET' : 'PAYPAL_SANDBOX_CLIENT_SECRET';
		$val = getenv( $key );

		return ( false !== $val && '' !== $val ) ? $val : '';
	}

	public function get_paypal_webhook_id(): string {
		$val = getenv( 'PAYPAL_WEBHOOK_ID' );

		return ( false !== $val && '' !== $val ) ? $val : '';
	}

	public function get_paypal_api_base_url(): string {
		return $this->is_paypal_live()
			? 'https://api-m.paypal.com'
			: 'https://api-m.sandbox.paypal.com';
	}
}
