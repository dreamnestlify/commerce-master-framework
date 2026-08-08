<?php
/**
 * Tests for SettingsModule.
 *
 * @package CommerceMaster\Core\Tests\Unit
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Tests\Unit;

use PHPUnit\Framework\TestCase;
use CommerceMaster\Core\Module\SettingsModule;

class SettingsModuleTest extends TestCase {

	private SettingsModule $module;

	protected function setUp(): void {
		parent::setUp();
		$GLOBALS['_test_options'] = array();
		$this->module = new SettingsModule();
	}

	public function test_module_id(): void {
		$this->assertSame( 'settings', $this->module->get_id() );
	}

	public function test_activate_sets_defaults(): void {
		$this->module->activate();
		$settings = get_option( SettingsModule::OPTION_KEY );

		$this->assertIsArray( $settings );
		$this->assertArrayHasKey( 'brand', $settings );
		$this->assertArrayHasKey( 'market', $settings );
		$this->assertArrayHasKey( 'support', $settings );
		$this->assertArrayHasKey( 'analytics', $settings );
		$this->assertArrayHasKey( 'payment', $settings );
	}

	public function test_activate_is_idempotent(): void {
		// First activation.
		$this->module->activate();
		$first = get_option( SettingsModule::OPTION_KEY );

		// Modify stored value.
		$modified = $first;
		$modified['brand']['name'] = 'Custom Brand';
		update_option( SettingsModule::OPTION_KEY, $modified );

		// Second activation should not overwrite.
		$this->module->activate();
		$second = get_option( SettingsModule::OPTION_KEY );

		$this->assertSame( 'Custom Brand', $second['brand']['name'] );
	}

	public function test_get_settings_returns_merged(): void {
		$this->module->activate();

		// Overwrite a single value.
		$stored = get_option( SettingsModule::OPTION_KEY );
		$stored['brand']['name'] = 'Test Brand';
		update_option( SettingsModule::OPTION_KEY, $stored );

		$settings = $this->module->get_settings();

		$this->assertSame( 'Test Brand', $settings['brand']['name'] );
		// Defaults should still fill in unspecified keys.
		$this->assertArrayHasKey( 'tagline', $settings['brand'] );
		$this->assertArrayHasKey( 'base_currency', $settings['market'] );
	}

	public function test_get_with_dot_notation(): void {
		$this->module->activate();

		$name = $this->module->get( 'brand.name' );
		$this->assertIsString( $name );
		$this->assertNotEmpty( $name );

		$missing = $this->module->get( 'nonexistent.key', 'fallback' );
		$this->assertSame( 'fallback', $missing );
	}

	public function test_sanitize_settings(): void {
		$input = array(
			'brand' => array(
				'name'     => '  <script>Test</script> Brand  ',
				'tagline'  => 'Great Fashion',
				'logo_id'  => '42abc',
			),
			'market' => array(
				'default_locale'     => 'en_US',
				'base_currency'      => 'USD',
				'enabled_currencies' => array( 'USD', 'EUR', 'GBP' ),
				'default_market'     => 'EU',
			),
			'support' => array(
				'email' => 'test@example.com',
				'phone' => '',
			),
			'analytics' => array(
				'ga4_measurement_id' => '',
				'meta_pixel_id'      => '',
				'tiktok_pixel_id'    => '',
				'google_ads_id'      => '',
			),
			'payment' => array(
				'stripe_enabled' => true,
				// paypal_enabled omitted — unchecked checkbox is not sent in form data.
			),
		);

		$clean = $this->module->sanitize_settings( $input );

		$this->assertSame( 'Test Brand', $clean['brand']['name'] ); // Tags and whitespace stripped
		$this->assertSame( 42, $clean['brand']['logo_id'] ); // absint
		$this->assertSame( 'test@example.com', $clean['support']['email'] );
		$this->assertTrue( $clean['payment']['stripe_enabled'] );
		$this->assertFalse( $clean['payment']['paypal_enabled'] );
	}

	public function test_default_currencies_include_usd_eur_gbp(): void {
		$this->module->activate();
		$settings = $this->module->get_settings();

		$this->assertContains( 'USD', $settings['market']['enabled_currencies'] );
	}
}
