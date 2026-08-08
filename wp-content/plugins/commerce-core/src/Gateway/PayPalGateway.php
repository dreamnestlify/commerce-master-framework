<?php
/**
 * PayPal Payment Gateway — WC_Payment_Gateway wrapper for PayPalAdapter.
 *
 * @package CommerceMaster\Core\Gateway
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Gateway;

use CommerceMaster\Core\Adapter\PayPal\PayPalAdapter;
use CommerceMaster\Core\Config\PaymentConfig;
use CommerceMaster\Core\Module\SettingsModule;

if ( ! class_exists( '\WC_Payment_Gateway' ) ) {
	return;
}

class PayPalGateway extends \WC_Payment_Gateway {

	/**
	 * PayPal adapter instance.
	 *
	 * @var PayPalAdapter|null
	 */
	private ?PayPalAdapter $adapter = null;

	/**
	 * Payment configuration.
	 *
	 * @var PaymentConfig
	 */
	private PaymentConfig $payment_config;

	/**
	 * Constructor.
	 *
	 * WooCommerce instantiates gateways with zero arguments via
	 * WC_Payment_Gateways::init(). The PaymentConfig argument is
	 * therefore optional — when omitted, a default config is built
	 * from the stored settings option.
	 *
	 * @param PaymentConfig|null $payment_config Payment configuration.
	 */
	public function __construct( ?PaymentConfig $payment_config = null ) {
		$this->payment_config = $payment_config ?? $this->create_default_config();
		$this->id             = 'commerce_paypal';
		$this->icon           = '';
		$this->has_fields     = false;
		$this->method_title   = __( 'PayPal', 'commerce-core' );
		$this->method_description = __( 'Accept payments via PayPal.', 'commerce-core' );
		$this->supports       = array( 'products', 'refunds' );

		$this->init_form_fields();
		$this->init_settings();

		$this->title       = $this->get_option( 'title', __( 'PayPal', 'commerce-core' ) );
		$this->description = $this->get_option( 'description', __( 'Pay with your PayPal account or credit card.', 'commerce-core' ) );
		$this->enabled     = $this->payment_config->is_paypal_enabled() ? 'yes' : 'no';

		if ( is_admin() ) {
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) ); // @phpstan-ignore-line — WC standard pattern, return value ignored by action API
		}
	}

	/**
	 * Create a default PaymentConfig from stored settings.
	 *
	 * Used when WooCommerce instantiates the gateway with no arguments.
	 */
	private function create_default_config(): PaymentConfig {
		$settings     = get_option( SettingsModule::OPTION_KEY, array() );
		$payment_data = is_array( $settings ) && isset( $settings['payment'] ) && is_array( $settings['payment'] )
			? $settings['payment']
			: array();

		return new PaymentConfig( $payment_data );
	}

	/**
	 * Initialize form fields.
	 */
	public function init_form_fields(): void {
		$this->form_fields = array(
			'enabled'     => array(
				'title'   => __( 'Enable/Disable', 'commerce-core' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable PayPal', 'commerce-core' ),
				'default' => 'yes',
			),
			'title'       => array(
				'title'       => __( 'Title', 'commerce-core' ),
				'type'        => 'text',
				'description' => __( 'Payment method title shown to customers.', 'commerce-core' ),
				'default'     => __( 'PayPal', 'commerce-core' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __( 'Description', 'commerce-core' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description shown to customers.', 'commerce-core' ),
				'default'     => __( 'Pay with your PayPal account or credit card.', 'commerce-core' ),
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
				'description' => __( 'API credentials are read from environment variables.', 'commerce-core' ),
				'desc_tip'    => false,
			),
		);
	}

	/**
	 * Get the PayPal adapter instance.
	 */
	private function get_adapter(): PayPalAdapter {
		if ( null === $this->adapter ) {
			$this->adapter = new PayPalAdapter( $this->payment_config );
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
				'result'          => 'success',
				'redirect'        => '',
				'paypal_order_id' => $meta['paypal_order_id'] ?? '',
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
