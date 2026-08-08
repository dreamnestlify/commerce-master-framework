<?php
/**
 * Logger — structured logging for plugin operations.
 *
 * Uses WordPress error_log when WC_LOGGER is not available.
 * Never logs sensitive data (passwords, payment info, PII).
 *
 * IMPORTANT: Do NOT embed PII (email, phone, address, IP, customer_name)
 * directly in the $message string. Only pass structured data via $data
 * array, which is automatically sanitized by sanitize_data().
 *
 * @package CommerceMaster\Core\Util
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Util;

class Logger {

	public const LEVEL_DEBUG   = 'debug';
	public const LEVEL_INFO    = 'info';
	public const LEVEL_WARNING = 'warning';
	public const LEVEL_ERROR   = 'error';

	private string $context;

	/**
	 * Optional custom handler for testing or advanced use.
	 * Signature: function(string $level, string $entry, string $context): void
	 *
	 * @var callable|null
	 */
	private $handler;

	public function __construct( string $context = 'commerce-core', ?callable $handler = null ) {
		$this->context = $context;
		$this->handler = $handler;
	}

	/**
	 * Log an info message.
	 *
	 * @param string               $message Log message. Do NOT embed PII (email, phone, address, IP, customer_name) here.
	 * @param array<string, mixed> $data Additional structured data (PII in $data is auto-redacted).
	 */
	public function info( string $message, array $data = array() ): void {
		$this->log( self::LEVEL_INFO, $message, $data );
	}

	/**
	 * Log a warning message.
	 *
	 * @param string               $message Log message.
	 * @param array<string, mixed> $data Additional structured data.
	 */
	public function warning( string $message, array $data = array() ): void {
		$this->log( self::LEVEL_WARNING, $message, $data );
	}

	/**
	 * Log an error message.
	 *
	 * @param string               $message Log message.
	 * @param array<string, mixed> $data Additional structured data.
	 */
	public function error( string $message, array $data = array() ): void {
		$this->log( self::LEVEL_ERROR, $message, $data );
	}

	/**
	 * Log a debug message.
	 *
	 * @param string               $message Log message.
	 * @param array<string, mixed> $data Additional structured data.
	 */
	public function debug( string $message, array $data = array() ): void {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		$this->log( self::LEVEL_DEBUG, $message, $data );
	}

	/**
	 * Write a log entry.
	 *
	 * @param string               $level   Log level.
	 * @param string               $message Log message.
	 * @param array<string, mixed> $data Structured data.
	 */
	private function log( string $level, string $message, array $data ): void {
		$entry = sprintf(
			'[%s][%s] %s',
			$this->context,
			strtoupper( $level ),
			$message
		);

		if ( ! empty( $data ) ) {
			$safe   = $this->sanitize_data( $data );
			$entry .= ' ' . wp_json_encode( $safe );
		}

		if ( null !== $this->handler ) {
			( $this->handler )( $level, $entry, $this->context );
		} elseif ( function_exists( 'wc_get_logger' ) ) {
			$logger = wc_get_logger();
			$logger->log( $level, $entry, array( 'source' => $this->context ) );
		} else {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( $entry );
		}
	}

	/**
	 * Remove sensitive data and PII from data before logging.
	 *
	 * Redacts:
	 * - Secrets: password, secret, api_key, token, card, cvv, ssn, private_key
	 * - PII: email, phone, address, customer_name, IP address
	 *
	 * @param array<string, mixed> $data Raw data.
	 * @return array<string, mixed> Sanitized data.
	 */
	public function sanitize_data( array $data ): array {
		// Substring matches (safe — these don't appear as substrings in common non-sensitive keys)
		$sensitive_substrings = array(
			'password',
			'secret',
			'api_key',
			'apikey',
			'token',
			'card',
			'cvv',
			'ssn',
			'stripe_secret',
			'paypal_secret',
			'private_key',
			'email',
			'phone',
			'address',
		);

		// Exact key matches (keys where substring matching would cause false positives)
		$sensitive_exact = array(
			'customer_name',
			'ip',
			'ip_address',
			'customer_ip',
			'client_ip',
			'remote_addr',
			'user_ip',
		);

		foreach ( $data as $key => $value ) {
			$lower_key     = strtolower( (string) $key );
			$should_redact = false;

			// Check substring matches
			foreach ( $sensitive_substrings as $sensitive ) {
				if ( str_contains( $lower_key, $sensitive ) ) {
					$should_redact = true;
					break;
				}
			}

			// Check exact matches
			if ( ! $should_redact ) {
				foreach ( $sensitive_exact as $exact ) {
					if ( $lower_key === $exact ) {
						$should_redact = true;
						break;
					}
				}
			}

			if ( $should_redact ) {
				$data[ $key ] = '[REDACTED]';
				continue;
			}

			if ( is_array( $value ) ) {
				$data[ $key ] = $this->sanitize_data( $value );
			}
		}

		return $data;
	}
}
