<?php
/**
 * Settings Module — manages all plugin configuration.
 *
 * @package CommerceMaster\Core\Module
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Module;

use CommerceMaster\Core\Config\BrandConfig;
use CommerceMaster\Core\Config\MarketConfig;
use CommerceMaster\Core\Config\SupportConfig;

class SettingsModule implements ModuleInterface {

	public const OPTION_KEY = 'commerce_core_settings';

	/**
	 * @var array<string, mixed>
	 */
	private array $defaults;

	public function __construct() {
		$this->defaults = array(
			'brand'     => array(
				'name'    => $this->env_or( 'BRAND_NAME', 'Commerce Master' ),
				'tagline' => $this->env_or( 'BRAND_TAGLINE', 'Fashion for the modern world' ),
				'logo_id' => 0,
			),
			'market'    => array(
				'default_locale'     => $this->env_or( 'DEFAULT_LOCALE', 'en_US' ),
				'base_currency'      => $this->env_or( 'BASE_CURRENCY', 'USD' ),
				'enabled_currencies' => array_filter( explode( ',', $this->env_or( 'ENABLED_CURRENCIES', 'USD,EUR,GBP' ) ) ),
				'default_market'     => $this->env_or( 'DEFAULT_MARKET', 'EU' ),
			),
			'support'   => array(
				'email' => $this->env_or( 'SUPPORT_EMAIL', 'support@example.com' ),
				'phone' => $this->env_or( 'SUPPORT_PHONE', '' ),
			),
			'analytics' => array(
				'ga4_measurement_id' => $this->env_or( 'GA4_MEASUREMENT_ID', '' ),
				'meta_pixel_id'      => $this->env_or( 'META_PIXEL_ID', '' ),
				'tiktok_pixel_id'    => $this->env_or( 'TIKTOK_PIXEL_ID', '' ),
				'google_ads_id'      => $this->env_or( 'GOOGLE_ADS_ID', '' ),
			),
			'payment'   => array(
				'stripe_enabled' => true,
				'paypal_enabled' => true,
			),
		);
	}

	public function register(): void {
		// Register the settings page in admin.
		add_action( 'admin_menu', array( $this, 'register_admin_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function boot(): void {
		// Expose settings via filter for theme and other plugins.
		add_filter( 'commerce_core_settings', array( $this, 'get_settings' ) );
	}

	public function activate(): void {
		// Only set defaults if not yet set (idempotent).
		if ( get_option( self::OPTION_KEY ) === false ) {
			add_option( self::OPTION_KEY, $this->defaults );
		}
	}

	public function get_id(): string {
		return 'settings';
	}

	/**
	 * Get merged settings (stored + defaults).
	 *
	 * @return array<string, mixed>
	 */
	public function get_settings(): array {
		$stored = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		return $this->merge_recursive( $this->defaults, $stored );
	}

	/**
	 * Get a specific setting by dot notation path.
	 *
	 * @param string $path Dot-separated path, e.g. "brand.name".
	 * @param mixed  $default Default value if not found.
	 * @return mixed
	 */
	public function get( string $path, $default = null ) {
		$settings = $this->get_settings();
		$keys     = explode( '.', $path );
		$value    = $settings;

		foreach ( $keys as $key ) {
			if ( ! is_array( $value ) || ! isset( $value[ $key ] ) ) {
				return $default;
			}
			$value = $value[ $key ];
		}

		return $value;
	}

	/**
	 * Register admin settings page.
	 */
	public function register_admin_page(): void {
		add_menu_page(
			__( 'Commerce Core', 'commerce-core' ),
			__( 'Commerce Core', 'commerce-core' ),
			'manage_options',
			'commerce-core',
			array( $this, 'render_admin_page' ),
			'dashicons-store',
			58
		);
	}

	/**
	 * Register settings, sections, and fields.
	 */
	public function register_settings(): void {
		register_setting(
			'commerce_core_settings_group',
			self::OPTION_KEY,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => $this->defaults,
			)
		);
	}

	/**
	 * Sanitize settings input.
	 *
	 * @param array<string, mixed> $input Raw input.
	 * @return array<string, mixed> Sanitized output.
	 */
	public function sanitize_settings( array $input ): array {
		$clean = $this->defaults;

		// Brand.
		$clean['brand']['name']    = sanitize_text_field( $input['brand']['name'] ?? '' );
		$clean['brand']['tagline'] = sanitize_text_field( $input['brand']['tagline'] ?? '' );
		$clean['brand']['logo_id'] = absint( $input['brand']['logo_id'] ?? 0 );

		// Market.
		$clean['market']['default_locale'] = sanitize_text_field( $input['market']['default_locale'] ?? 'en_US' );
		$clean['market']['base_currency']  = sanitize_text_field( $input['market']['base_currency'] ?? 'USD' );

		$currencies = $input['market']['enabled_currencies'] ?? array( 'USD' );
		if ( is_string( $currencies ) ) {
			$currencies = array_filter( explode( ',', $currencies ) );
		}
		$clean['market']['enabled_currencies'] = array_map( 'sanitize_text_field', array_filter( $currencies ) );
		$clean['market']['default_market']     = sanitize_text_field( $input['market']['default_market'] ?? 'EU' );

		// Support.
		$clean['support']['email'] = sanitize_email( $input['support']['email'] ?? '' );
		$clean['support']['phone'] = sanitize_text_field( $input['support']['phone'] ?? '' );

		// Analytics.
		$clean['analytics']['ga4_measurement_id'] = sanitize_text_field( $input['analytics']['ga4_measurement_id'] ?? '' );
		$clean['analytics']['meta_pixel_id']      = sanitize_text_field( $input['analytics']['meta_pixel_id'] ?? '' );
		$clean['analytics']['tiktok_pixel_id']    = sanitize_text_field( $input['analytics']['tiktok_pixel_id'] ?? '' );
		$clean['analytics']['google_ads_id']      = sanitize_text_field( $input['analytics']['google_ads_id'] ?? '' );

		// Payment.
		$clean['payment']['stripe_enabled'] = isset( $input['payment']['stripe_enabled'] );
		$clean['payment']['paypal_enabled'] = isset( $input['payment']['paypal_enabled'] );

		return $clean;
	}

	/**
	 * Render the admin settings page.
	 */
	public function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'commerce-core' ) );
		}

		$settings = $this->get_settings();

		require COMMERCE_CORE_DIR . 'src/Admin/views/settings-page.php';
	}

	/**
	 * Get environment variable or default.
	 *
	 * @param string $key Environment variable name.
	 * @param string $default Default value.
	 * @return string
	 */
	private function env_or( string $key, string $default ): string {
		$val = getenv( $key );
		return ( false !== $val && '' !== $val ) ? $val : $default;
	}

	/**
	 * Recursively merge defaults with stored values.
	 *
	 * @param array<string, mixed> $defaults Default values.
	 * @param array<string, mixed> $stored Stored values.
	 * @return array<string, mixed>
	 */
	private function merge_recursive( array $defaults, array $stored ): array {
		foreach ( $defaults as $key => $value ) {
			if ( is_array( $value ) && isset( $stored[ $key ] ) && is_array( $stored[ $key ] ) ) {
				$defaults[ $key ] = $this->merge_recursive( $value, $stored[ $key ] );
			} elseif ( isset( $stored[ $key ] ) ) {
				$defaults[ $key ] = $stored[ $key ];
			}
		}

		return $defaults;
	}
}
