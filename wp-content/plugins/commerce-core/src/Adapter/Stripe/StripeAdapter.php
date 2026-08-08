<?php
/**
 * Stripe Payment Adapter — implements PaymentAdapterInterface using stripe-php SDK.
 *
 * @package CommerceMaster\Core\Adapter\Stripe
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Adapter\Stripe;

use CommerceMaster\Core\Adapter\PaymentAdapterInterface;
use CommerceMaster\Core\Adapter\PaymentResult;
use CommerceMaster\Core\Adapter\RefundResult;
use CommerceMaster\Core\Config\PaymentConfig;

class StripeAdapter implements PaymentAdapterInterface {

	private PaymentConfig $config;

	public function __construct( PaymentConfig $config ) {
		$this->config = $config;
	}

	public function process_payment( int $order_id, array $payment_data ): PaymentResult {
		if ( ! $this->is_configured() ) {
			return new PaymentResult( false, '', 'Stripe is not configured', 'failed' );
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return new PaymentResult( false, '', 'Order not found', 'failed' );
		}

		$this->set_api_key();

		$currency      = strtolower( $order->get_currency() );
		$amount        = (int) round( (float) $order->get_total() * 100 );
		$intent_id     = isset( $payment_data['payment_intent_id'] ) ? (string) $payment_data['payment_intent_id'] : '';
		$order_number  = $order->get_order_number();

		if ( empty( $intent_id ) ) {
			try {
				$intent = \Stripe\PaymentIntent::create(
					array(
						'amount'               => $amount,
						'currency'             => $currency,
						'description'          => sprintf( 'Order #%s', $order_number ),
						'metadata'             => array(
							'order_id' => (string) $order_id,
							'site'     => get_bloginfo( 'name' ),
						),
						'automatic_payment_methods' => array( 'enabled' => true ),
					)
				);

				return new PaymentResult(
					false,
					$intent->id,
					'PaymentIntent created — requires client confirmation',
					'requires_action',
					array( 'client_secret' => $intent->client_secret )
				);
			} catch ( \Stripe\Exception\ApiErrorException $e ) {
				return new PaymentResult( false, '', $e->getMessage(), 'failed' );
			}
		}

		try {
			$intent = \Stripe\PaymentIntent::retrieve( $intent_id );

			if ( 'succeeded' === $intent->status ) {
				$order->payment_complete( $intent->id );
				$order->add_order_note( sprintf( 'Stripe payment succeeded. Transaction ID: %s', $intent->id ) );

				return new PaymentResult( true, $intent->id, 'Payment succeeded', 'succeeded' );
			}

			return new PaymentResult(
				false,
				$intent->id,
				'Payment not completed: ' . $intent->status,
				$intent->status
			);
		} catch ( \Stripe\Exception\ApiErrorException $e ) {
			return new PaymentResult( false, $intent_id, $e->getMessage(), 'failed' );
		}
	}

	public function process_refund( int $order_id, float $amount, string $reason = '' ): RefundResult {
		if ( ! $this->is_configured() ) {
			return new RefundResult( false, '', 'Stripe is not configured', 0.0 );
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return new RefundResult( false, '', 'Order not found', 0.0 );
		}

		$transaction_id = $order->get_transaction_id();
		if ( empty( $transaction_id ) ) {
			return new RefundResult( false, '', 'No transaction ID to refund', 0.0 );
		}

		$this->set_api_key();

		try {
			$refund = \Stripe\Refund::create(
				array(
					'payment_intent' => $transaction_id,
					'amount'         => (int) round( $amount * 100 ),
					'metadata'       => array(
						'order_id' => (string) $order_id,
						'reason'   => $reason,
					),
				)
			);

			if ( 'succeeded' === $refund->status ) {
				$order->add_order_note( sprintf( 'Stripe refund succeeded. Refund ID: %s, Amount: %.2f', $refund->id, $amount ) );

				return new RefundResult( true, $refund->id, 'Refund succeeded', $amount );
			}

			return new RefundResult( false, $refund->id, 'Refund status: ' . $refund->status, 0.0 );
		} catch ( \Stripe\Exception\ApiErrorException $e ) {
			return new RefundResult( false, '', $e->getMessage(), 0.0 );
		}
	}

	public function get_id(): string {
		return 'stripe';
	}

	public function get_name(): string {
		return __( 'Stripe', 'commerce-core' );
	}

	public function is_configured(): bool {
		return ! empty( $this->config->get_stripe_secret_key() )
			&& ! empty( $this->config->get_stripe_publishable_key() );
	}

	public function get_supported_currencies(): array {
		return array(
			'USD', 'EUR', 'GBP', 'AUD', 'CAD', 'JPY', 'CNY', 'CHF',
			'DKK', 'NOK', 'SEK', 'SGD', 'HKD', 'NZD', 'MXN', 'BRL',
			'PLN', 'CZK', 'HUF', 'RON', 'INR', 'THB', 'MYR', 'PHP',
		);
	}

	public function supports_currency( string $currency_code ): bool {
		return in_array( strtoupper( $currency_code ), $this->get_supported_currencies(), true );
	}

	private function set_api_key(): void {
		$secret = $this->config->get_stripe_secret_key();

		if ( class_exists( '\Stripe\Stripe' ) && ! empty( $secret ) ) {
			\Stripe\Stripe::setApiKey( $secret );
		}
	}
}
