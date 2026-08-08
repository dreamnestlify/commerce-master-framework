<?php
/**
 * Payment Gateway Module — registers Stripe and PayPal payment gateways.
 *
 * Registers WC_Payment_Gateway subclasses and handles webhook endpoints.
 * API keys are read from environment variables via PaymentConfig.
 *
 * @package CommerceMaster\Core\Module
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Module;

use CommerceMaster\Core\Config\PaymentConfig;
use CommerceMaster\Core\Gateway\StripeGateway;
use CommerceMaster\Core\Gateway\PayPalGateway;

class PaymentGatewayModule implements ModuleInterface {

	/**
	 * Payment configuration.
	 *
	 * @var PaymentConfig|null
	 */
	private ?PaymentConfig $payment_config = null;

	public function register(): void {
		add_filter( 'woocommerce_payment_gateways', array( $this, 'register_gateways' ) );
	}

	public function boot(): void {
		add_action( 'woocommerce_api_commerce-core-stripe', array( $this, 'handle_stripe_webhook' ) );
		add_action( 'woocommerce_api_commerce-core-paypal', array( $this, 'handle_paypal_webhook' ) );
	}

	public function activate(): void {
	}

	public function get_id(): string {
		return 'payment-gateway';
	}

	/**
	 * Register payment gateways with WooCommerce.
	 *
	 * @param string[] $gateways Existing gateway class names.
	 * @return string[] Updated gateway list.
	 */
	public function register_gateways( array $gateways ): array {
		$gateways[] = StripeGateway::class;
		$gateways[] = PayPalGateway::class;

		return $gateways;
	}

	/**
	 * Get payment configuration from settings.
	 */
	private function get_payment_config(): PaymentConfig {
		if ( null === $this->payment_config ) {
			$settings               = get_option( SettingsModule::OPTION_KEY, array() );
			$payment_data           = is_array( $settings ) && isset( $settings['payment'] ) && is_array( $settings['payment'] )
				? $settings['payment']
				: array();
			$this->payment_config   = new PaymentConfig( $payment_data );
		}

		return $this->payment_config;
	}

	/**
	 * Handle Stripe webhook.
	 */
	public function handle_stripe_webhook(): void {
		$payload = file_get_contents( 'php://input' );
		$sig     = isset( $_SERVER['HTTP_STRIPE_SIGNATURE'] )
			? sanitize_text_field( wp_unslash( $_SERVER['HTTP_STRIPE_SIGNATURE'] ) )
			: '';

		$config = $this->get_payment_config();
		$secret = $config->get_stripe_webhook_secret();

		if ( empty( $secret ) || empty( $payload ) || empty( $sig ) ) {
			status_header( 400 );
			exit( 'Missing webhook data or secret' );
		}

		try {
			$event = \Stripe\Webhook::constructEvent( $payload, $sig, $secret );
		} catch ( \Exception $e ) {
			status_header( 400 );
			exit( 'Invalid signature: ' . esc_html( $e->getMessage() ) );
		}

		$this->process_stripe_event( $event );

		status_header( 200 );
		exit( 'OK' );
	}

	/**
	 * Process a Stripe webhook event.
	 *
	 * @param \Stripe\Event $event Stripe event object.
	 */
	private function process_stripe_event( $event ): void {
		$type = $event->type;

		switch ( $type ) {
			case 'payment_intent.succeeded':
				$this->handle_stripe_payment_succeeded( $event );
				break;
			case 'charge.refunded':
				$this->handle_stripe_refund( $event );
				break;
			case 'payment_intent.payment_failed':
				$this->handle_stripe_payment_failed( $event );
				break;
		}
	}

	/**
	 * Handle Stripe payment_intent.succeeded event.
	 *
	 * @param \Stripe\Event $event Stripe event.
	 */
	private function handle_stripe_payment_succeeded( $event ): void {
		$intent   = $event->data->object; // @phpstan-ignore-line — Stripe SDK uses magic properties
		$order_id = isset( $intent->metadata->order_id ) ? (int) $intent->metadata->order_id : 0;

		if ( ! $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		if ( ! $order->has_status( array( 'processing', 'completed' ) ) ) {
			$order->payment_complete( $intent->id );
			$order->add_order_note( sprintf( 'Stripe webhook: payment succeeded. Transaction ID: %s', $intent->id ) );
		}
	}

	/**
	 * Handle Stripe charge.refunded event.
	 *
	 * @param \Stripe\Event $event Stripe event.
	 */
	private function handle_stripe_refund( $event ): void {
		$charge   = $event->data->object; // @phpstan-ignore-line — Stripe SDK uses magic properties
		$order_id = isset( $charge->metadata->order_id ) ? (int) $charge->metadata->order_id : 0;

		if ( ! $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$order->update_status( 'refunded', sprintf( 'Stripe webhook: charge refunded. Charge ID: %s', $charge->id ) );
	}

	/**
	 * Handle Stripe payment_intent.payment_failed event.
	 *
	 * @param \Stripe\Event $event Stripe event.
	 */
	private function handle_stripe_payment_failed( $event ): void {
		$intent   = $event->data->object; // @phpstan-ignore-line — Stripe SDK uses magic properties
		$order_id = isset( $intent->metadata->order_id ) ? (int) $intent->metadata->order_id : 0;

		if ( ! $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$order->update_status( 'failed', sprintf( 'Stripe webhook: payment failed. Intent ID: %s', $intent->id ) );
	}

	/**
	 * Handle PayPal webhook.
	 */
	public function handle_paypal_webhook(): void {
		$payload = file_get_contents( 'php://input' );

		if ( empty( $payload ) ) {
			status_header( 400 );
			exit( 'Empty payload' );
		}

		$event = json_decode( $payload, true );

		if ( ! is_array( $event ) || ! isset( $event['event_type'] ) ) {
			status_header( 400 );
			exit( 'Invalid payload format' );
		}

		$this->process_paypal_event( $event );

		status_header( 200 );
		exit( 'OK' );
	}

	/**
	 * Process a PayPal webhook event.
	 *
	 * @param array<string, mixed> $event PayPal event data.
	 */
	private function process_paypal_event( array $event ): void {
		$type = $event['event_type'];

		switch ( $type ) {
			case 'CHECKOUT.ORDER.APPROVED':
				$this->handle_paypal_order_approved( $event );
				break;
			case 'PAYMENT.CAPTURE.COMPLETED':
				$this->handle_paypal_capture_completed( $event );
				break;
			case 'PAYMENT.CAPTURE.REFUNDED':
				$this->handle_paypal_refund( $event );
				break;
		}
	}

	/**
	 * Handle PayPal CHECKOUT.ORDER.APPROVED event.
	 *
	 * @param array<string, mixed> $event PayPal event data.
	 */
	private function handle_paypal_order_approved( array $event ): void {
		$resource = $event['resource'] ?? array();
		$units    = $resource['purchase_units'] ?? array();

		if ( empty( $units ) ) {
			return;
		}

		$reference_id = $units[0]['reference_id'] ?? '';
		$order_id     = (int) $reference_id;

		if ( ! $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( $order ) {
			$order->add_order_note( __( 'PayPal webhook: order approved by buyer.', 'commerce-core' ) );
		}
	}

	/**
	 * Handle PayPal PAYMENT.CAPTURE.COMPLETED event.
	 *
	 * @param array<string, mixed> $event PayPal event data.
	 */
	private function handle_paypal_capture_completed( array $event ): void {
		$resource    = $event['resource'] ?? array();
		$custom_id   = $resource['custom_id'] ?? '';
		$capture_id  = $resource['id'] ?? '';
		$order_id    = (int) $custom_id;

		if ( ! $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		if ( ! $order->has_status( array( 'processing', 'completed' ) ) ) {
			$order->payment_complete( $capture_id );
			$order->add_order_note( sprintf( 'PayPal webhook: capture completed. Capture ID: %s', $capture_id ) );
		}
	}

	/**
	 * Handle PayPal PAYMENT.CAPTURE.REFUNDED event.
	 *
	 * @param array<string, mixed> $event PayPal event data.
	 */
	private function handle_paypal_refund( array $event ): void {
		$resource      = $event['resource'] ?? array();
		$custom_id     = $resource['custom_id'] ?? '';
		$order_id      = (int) $custom_id;

		if ( ! $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( $order ) {
			$order->update_status( 'refunded', __( 'PayPal webhook: payment refunded.', 'commerce-core' ) );
		}
	}
}
