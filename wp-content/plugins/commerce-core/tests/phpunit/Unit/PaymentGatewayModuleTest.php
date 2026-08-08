<?php
/**
 * Tests for PaymentGatewayModule.
 *
 * @package CommerceMaster\Core\Tests\Unit
 */

declare( strict_types=1 );

namespace CommerceMaster\Core\Tests\Unit;

use PHPUnit\Framework\TestCase;
use CommerceMaster\Core\Module\PaymentGatewayModule;
use CommerceMaster\Core\Module\ModuleInterface;
use CommerceMaster\Core\Gateway\StripeGateway;
use CommerceMaster\Core\Gateway\PayPalGateway;

class PaymentGatewayModuleTest extends TestCase {

	private PaymentGatewayModule $module;

	protected function setUp(): void {
		parent::setUp();
		$GLOBALS['_test_options']   = array();
		$this->module               = new PaymentGatewayModule();
	}

	public function test_module_id(): void {
		$this->assertSame( 'payment-gateway', $this->module->get_id() );
	}

	public function test_module_implements_module_interface(): void {
		$this->assertInstanceOf( ModuleInterface::class, $this->module );
	}

	public function test_register_gateways_adds_stripe(): void {
		$gateways = $this->module->register_gateways( array() );

		$this->assertContains( StripeGateway::class, $gateways );
	}

	public function test_register_gateways_adds_paypal(): void {
		$gateways = $this->module->register_gateways( array() );

		$this->assertContains( PayPalGateway::class, $gateways );
	}

	public function test_register_gateways_adds_both(): void {
		$gateways = $this->module->register_gateways( array() );

		$this->assertCount( 2, $gateways );
		$this->assertContains( StripeGateway::class, $gateways );
		$this->assertContains( PayPalGateway::class, $gateways );
	}

	public function test_register_gateways_preserves_existing(): void {
		$existing = array( 'WC_Gateway_BACS', 'WC_Gateway_Cheque' );

		$gateways = $this->module->register_gateways( $existing );

		$this->assertContains( 'WC_Gateway_BACS', $gateways );
		$this->assertContains( 'WC_Gateway_Cheque', $gateways );
		$this->assertContains( StripeGateway::class, $gateways );
		$this->assertContains( PayPalGateway::class, $gateways );
		$this->assertCount( 4, $gateways );
	}

	public function test_activate_does_not_throw(): void {
		$this->expectNotToPerformAssertions();
		$this->module->activate();
	}
}
