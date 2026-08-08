<?php
/**
 * Security Module — nonce, capability, escaping helpers.
 *
 * @package CommerceMaster\Core\Module
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Module;

class SecurityModule implements ModuleInterface {

	/** Admin form nonce (settings pages, not REST). */
	public const NONCE_ACTION = 'commerce_core_nonce';
	public const NONCE_NAME   = '_commerce_core_nonce';

	/** WordPress REST API standard nonce action. */
	public const REST_NONCE_ACTION = 'wp_rest';

	public function register(): void {
		// No hooks to register in Phase 0.
	}

	public function boot(): void {
		// Add security headers.
		add_action( 'send_headers', array( $this, 'add_security_headers' ) );
	}

	public function activate(): void {
		// No activation tasks in Phase 0.
	}

	public function get_id(): string {
		return 'security';
	}

	/**
	 * Generate a nonce field for forms.
	 *
	 * @param bool $echo Whether to echo (true) or return (false).
	 * @return string HTML nonce field.
	 */
	public static function nonce_field( bool $echo = true ): string {
		return wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME, true, $echo );
	}

	/**
	 * Verify a nonce from an admin form request (NOT REST).
	 *
	 * Uses the `commerce_core_nonce` action for backend form submissions.
	 * For REST API requests, use {@see verify_rest_nonce()} instead.
	 *
	 * @return bool True if valid.
	 */
	public static function verify_nonce(): bool {
		$nonce = '';
		if ( isset( $_REQUEST[ self::NONCE_NAME ] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_REQUEST[ self::NONCE_NAME ] ) );
		}

		return '' !== $nonce && false !== wp_verify_nonce( $nonce, self::NONCE_ACTION );
	}

	/**
	 * Verify a WordPress REST API nonce.
	 *
	 * Uses the standard `wp_rest` action and reads the nonce from the
	 * `X-WP-Nonce` header via the REST request object. This follows
	 * WordPress Cookie Authentication conventions for REST API.
	 *
	 * @param mixed $request WP_REST_Request or any object with get_header().
	 * @return bool True if the nonce is valid.
	 */
	public static function verify_rest_nonce( $request ): bool {
		$nonce = '';
		if ( is_object( $request ) && method_exists( $request, 'get_header' ) ) {
			$header = $request->get_header( 'X-WP-Nonce' );
			if ( null !== $header && '' !== $header ) {
				$nonce = sanitize_text_field( wp_unslash( $header ) );
			}
		}

		return '' !== $nonce && false !== wp_verify_nonce( $nonce, self::REST_NONCE_ACTION );
	}

	/**
	 * Check user capability.
	 *
	 * @param string $capability WordPress capability.
	 * @return bool True if user has capability.
	 */
	public static function check_capability( string $capability = 'manage_options' ): bool {
		return current_user_can( $capability );
	}

	/**
	 * Require capability — die if not allowed.
	 *
	 * @param string $capability WordPress capability.
	 */
	public static function require_capability( string $capability = 'manage_options' ): void {
		if ( ! self::check_capability( $capability ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'commerce-core' ) );
		}
	}

	/**
	 * Add security-related HTTP headers.
	 */
	public function add_security_headers(): void {
		if ( is_admin() ) {
			return;
		}

		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	}
}
