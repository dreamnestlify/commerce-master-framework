<?php
/**
 * Stripe Payment Gateway — WC_Payment_Gateway wrapper for StripeAdapter.
 *
 * @package CommerceMaster\Core\Gateway
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Gateway;

use CommerceMaster\Core\Adapter\Stripe\StripeAdapter;
use CommerceMaster\Core\Adapter\PaymentResult;
use CommerceMaster\Core\Config\PaymentConfig;

if ( ! class_exists( '\WC_Payment_Gateway' ) ) {
	return;
}

class StripeGateway extends \WC_Payment_Gateway {

	/**
	 * Stripe adapter instance.
	 */
	private ?StripeAdapter $adapter = null;

	/**
	 * Payment configuration.
	 */
	private PaymentConfig $payment_config;

	/**
	 * Constructor.
	 *
	 * @param PaymentConfig $payment_config Payment configuration.
	 */
	public function __construct( PaymentConfig $payment_config ) {
		$this->payment_config = $payment_config;
		$this->id             = 'commerce_stripe';
		$this->icon           = '';
		$this->has_fields     = true;
		$this->method_title   = __( 'Stripe', 'commerce-core' );
		$this->method_description = __( 'Accept payments via Stripe (cards, Apple Pay, Google Pay).', 'commerce-core' );
		$this->supports       = array( 'products', 'refunds' );

		$this->init_form_fields();
		$this->init_settings();

		$this->title       = $this->get_option( 'title', __( 'Credit Card (Stripe)', 'commerce-core' ) );
		$this->description = $this->get_option( 'description', __( 'Pay securely with your credit card via Stripe.', 'commerce-core' ) );
		$this->enabled     = $this->payment_config->is_stripe_enabled() ? 'yes' : 'no';

		if ( is_admin() ) {
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}
	}

	/**
	 * Initialize form fields.
	 */
	public function init_form_fields(): void {
		$this->form_fields = array(
			'enabled'     => array(
				'title'   => __( 'Enable/Disable', 'commerce-core' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Stripe', 'commerce-core' ),
				'default' => 'yes',
			),
			'title'       => array(
				'title'       => __( 'Title', 'commerce-core' ),
				'type'        => 'text',
				'description' => __( 'Payment method title shown to customers.', 'commerce-core' ),
				'default'     => __( 'Credit Card (Stripe)', 'commerce-core' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __( 'Description', 'commerce-core' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description shown to customers.', 'commerce-core' ),
				'default'     => __( 'Pay securely with your credit card via Stripe.', 'commerce-core' ),
				'desc_tip'    => true,
			),
			'mode'        => array(
				'title'       => __( 'Mode', 'commerce-core' ),
				'type'        => 'select',
				'options'     => array(
					'sandbox' => __( 'Sandbox (test)', 'commerce-core' ),
					'live'    => __( 'Live', 'commerce-core' ),
				),
				'default'     => 'sandbox',
				'description' => __( 'API keys are read from environment variables.', 'commerce-core' ),
				'desc_tip'    => false,
			),
		);
	}

	/**
	 * Get the Stripe adapter instance.
	 */
	private function get_adapter(): StripeAdapter {
		if ( null === $this->adapter ) {
			$this->adapter = new StripeAdapter( $this->payment_config );
		}

		return $this->adapter;
	}

	/**
	 * Process payment.
	 *
	 * @param int $order_id Order ID.
	 * @return array<string, mixed> Result array for WC checkout.
	 */
	public function process_payment( $order_id ): array {
		$result = $this->get_adapter()->process_payment( $order_id, array() );

		if ( $result->is_success() ) {
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( wc_get_order( $order_id ) ),
			);
		}

		$status = $result->get_status();

		if ( 'requires_action' === $status ) {
			$meta = $result->get_metadata();

			return array(
				'result'        => 'success',
				'redirect'      => '',
				'client_secret' => $meta['client_secret'] ?? '',
				'transaction_id' => $result->get_transaction_id(),
			);
		}

		wc_add_notice( $result->get_message(), 'error' );

		return array( 'result' => 'failure' );
	}

	/**
	 * Process refund.
	 *
	 * @param int    $order_id Order ID.
	 * @param float  $amount   Refund amount.
	 * @param string $reason   Refund reason.
	 * @return bool True on success.
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ): bool {
		if ( null === $amount ) {
			return false;
		}

		$result = $this->get_adapter()->process_refund( $order_id, (float) $amount, $reason );

		return $result->is_success();
	}

	/**
	 * Check if the gateway is available.
	 */
	public function is_available(): bool {
		if ( 'yes' !== $this->enabled ) {
			return false;
		}

		return $this->get_adapter()->is_configured();
	}
}
