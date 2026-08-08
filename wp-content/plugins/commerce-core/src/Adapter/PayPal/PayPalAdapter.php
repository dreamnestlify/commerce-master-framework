<?php
/**
 * PayPal Payment Adapter — implements PaymentAdapterInterface using REST API.
 *
 * Uses wp_remote_post() for PayPal REST API v2 calls.
 * No SDK dependency — direct HTTP API integration.
 *
 * @package CommerceMaster\Core\Adapter\PayPal
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Adapter\PayPal;

use CommerceMaster\Core\Adapter\PaymentAdapterInterface;
use CommerceMaster\Core\Adapter\PaymentResult;
use CommerceMaster\Core\Adapter\RefundResult;
use CommerceMaster\Core\Config\PaymentConfig;

class PayPalAdapter implements PaymentAdapterInterface {

	private PaymentConfig $config;

	/**
	 * Cached OAuth access token.
	 */
	private ?string $access_token = null;

	/**
	 * Token expiry timestamp.
	 */
	private ?int $token_expiry = null;

	public function __construct( PaymentConfig $config ) {
		$this->config = $config;
	}

	public function process_payment( int $order_id, array $payment_data ): PaymentResult {
		if ( ! $this->is_configured() ) {
			return new PaymentResult( false, '', 'PayPal is not configured', 'failed' );
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return new PaymentResult( false, '', 'Order not found', 'failed' );
		}

		$paypal_order_id = isset( $payment_data['paypal_order_id'] ) ? (string) $payment_data['paypal_order_id'] : '';

		if ( empty( $paypal_order_id ) ) {
			$created = $this->create_paypal_order( $order );

			if ( null === $created ) {
				return new PaymentResult( false, '', 'Failed to create PayPal order', 'failed' );
			}

			return new PaymentResult(
				false,
				$created,
				'PayPal order created — requires buyer approval',
				'requires_action',
				array( 'paypal_order_id' => $created )
			);
		}

		$captured = $this->capture_paypal_order( $paypal_order_id, $order );

		if ( $captured ) {
			$order->payment_complete( $paypal_order_id );
			$order->add_order_note( sprintf( 'PayPal payment captured. Transaction ID: %s', $paypal_order_id ) );

			return new PaymentResult( true, $paypal_order_id, 'Payment captured', 'succeeded' );
		}

		return new PaymentResult( false, $paypal_order_id, 'Capture failed', 'failed' );
	}

	public function process_refund( int $order_id, float $amount, string $reason = '' ): RefundResult {
		if ( ! $this->is_configured() ) {
			return new RefundResult( false, '', 'PayPal is not configured', 0.0 );
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return new RefundResult( false, '', 'Order not found', 0.0 );
		}

		$capture_id = $order->get_transaction_id();
		if ( empty( $capture_id ) ) {
			return new RefundResult( false, '', 'No capture ID to refund', 0.0 );
		}

		$token   = $this->get_access_token();
		$url     = $this->config->get_paypal_api_base_url() . '/v2/payments/captures/' . $capture_id . '/refund';
		$payload = array(
			'amount' => array(
				'value'    => number_format( $amount, 2, '.', '' ),
				'currency' => $order->get_currency(),
			),
		);

		if ( ! empty( $reason ) ) {
			$payload['note_to_payer'] = $reason;
		}

		$response = wp_remote_post(
			$url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json',
					'Accept'        => 'application/json',
				),
				'body'    => wp_json_encode( $payload ),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return new RefundResult( false, '', $response->get_error_message(), 0.0 );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$code = wp_remote_retrieve_response_code( $response );

		if ( 201 === $code && isset( $body['id'] ) ) {
			$order->add_order_note( sprintf( 'PayPal refund succeeded. Refund ID: %s, Amount: %.2f', $body['id'], $amount ) );

			return new RefundResult( true, $body['id'], 'Refund succeeded', $amount );
		}

		$msg = $body['message'] ?? 'Unknown PayPal error';
		return new RefundResult( false, '', $msg, 0.0 );
	}

	public function get_id(): string {
		return 'paypal';
	}

	public function get_name(): string {
		return __( 'PayPal', 'commerce-core' );
	}

	public function is_configured(): bool {
		return ! empty( $this->config->get_paypal_client_id() )
			&& ! empty( $this->config->get_paypal_client_secret() );
	}

	public function get_supported_currencies(): array {
		return array(
			'USD', 'EUR', 'GBP', 'AUD', 'CAD', 'JPY', 'CHF',
			'DKK', 'NOK', 'SEK', 'SGD', 'HKD', 'NZD', 'MXN', 'BRL',
			'PLN', 'CZK', 'HUF', 'INR', 'THB', 'MYR', 'PHP',
		);
	}

	public function supports_currency( string $currency_code ): bool {
		return in_array( strtoupper( $currency_code ), $this->get_supported_currencies(), true );
	}

	/**
	 * Get or refresh OAuth access token.
	 */
	private function get_access_token(): string {
		if ( null !== $this->access_token && null !== $this->token_expiry && time() < $this->token_expiry ) {
			return $this->access_token;
		}

		$base_url = $this->config->get_paypal_api_base_url();
		$auth     = base64_encode( $this->config->get_paypal_client_id() . ':' . $this->config->get_paypal_client_secret() );

		$response = wp_remote_post(
			$base_url . '/v1/oauth2/token',
			array(
				'headers' => array(
					'Authorization' => 'Basic ' . $auth,
					'Accept'        => 'application/json',
					'Content-Type'  => 'application/x-www-form-urlencoded',
				),
				'body'    => 'grant_type=client_credentials',
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return '';
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$code = wp_remote_retrieve_response_code( $response );

		if ( 200 === $code && isset( $body['access_token'] ) ) {
			$this->access_token = $body['access_token'];
			$this->token_expiry = time() + (int) ( $body['expires_in'] ?? 3600 ) - 60;

			return $this->access_token;
		}

		return '';
	}

	/**
	 * Create a PayPal order.
	 *
	 * @param \WC_Order $order WooCommerce order.
	 * @return string|null PayPal order ID or null on failure.
	 */
	private function create_paypal_order( $order ): ?string {
		$token = $this->get_access_token();
		if ( empty( $token ) ) {
			return null;
		}

		$url     = $this->config->get_paypal_api_base_url() . '/v2/checkout/orders';
		$payload = array(
			'intent'         => 'CAPTURE',
			'purchase_units' => array(
				array(
					'reference_id' => (string) $order->get_id(),
					'description'  => sprintf( 'Order #%s', $order->get_order_number() ),
					'amount'       => array(
						'currency_code' => $order->get_currency(),
						'value'         => number_format( (float) $order->get_total(), 2, '.', '' ),
					),
				),
			),
		);

		$response = wp_remote_post(
			$url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json',
					'Accept'        => 'application/json',
				),
				'body'    => wp_json_encode( $payload ),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$code = wp_remote_retrieve_response_code( $response );

		if ( 201 === $code && isset( $body['id'] ) ) {
			return $body['id'];
		}

		return null;
	}

	/**
	 * Capture a PayPal order.
	 *
	 * @param string    $paypal_order_id PayPal order ID.
	 * @param \WC_Order $order           WooCommerce order.
	 * @return bool True on success.
	 */
	private function capture_paypal_order( string $paypal_order_id, $order ): bool {
		$token = $this->get_access_token();
		if ( empty( $token ) ) {
			return false;
		}

		$url      = $this->config->get_paypal_api_base_url() . '/v2/checkout/orders/' . $paypal_order_id . '/capture';
		$response = wp_remote_post(
			$url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json',
					'Accept'        => 'application/json',
				),
				'body'    => '',
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$code = wp_remote_retrieve_response_code( $response );

		return 201 === $code;
	}
}
