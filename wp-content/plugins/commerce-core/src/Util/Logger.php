<?php
/**
 * Logger — structured logging for plugin operations.
 *
 * Uses WordPress error_log when WC_LOGGER is not available.
 * Never logs sensitive data (passwords, payment info, PII).
 *
 * @package CommerceMaster\Core\Util
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Util;

class Logger
{
    public const LEVEL_DEBUG = 'debug';
    public const LEVEL_INFO = 'info';
    public const LEVEL_WARNING = 'warning';
    public const LEVEL_ERROR = 'error';

    private string $context;

    public function __construct(string $context = 'commerce-core')
    {
        $this->context = $context;
    }

    /**
     * Log an info message.
     *
     * @param string $message Log message.
     * @param array<string, mixed> $data Additional structured data (no secrets!).
     */
    public function info(string $message, array $data = []): void
    {
        $this->log(self::LEVEL_INFO, $message, $data);
    }

    /**
     * Log a warning message.
     *
     * @param string $message Log message.
     * @param array<string, mixed> $data Additional structured data.
     */
    public function warning(string $message, array $data = []): void
    {
        $this->log(self::LEVEL_WARNING, $message, $data);
    }

    /**
     * Log an error message.
     *
     * @param string $message Log message.
     * @param array<string, mixed> $data Additional structured data.
     */
    public function error(string $message, array $data = []): void
    {
        $this->log(self::LEVEL_ERROR, $message, $data);
    }

    /**
     * Log a debug message.
     *
     * @param string $message Log message.
     * @param array<string, mixed> $data Additional structured data.
     */
    public function debug(string $message, array $data = []): void
    {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }

        $this->log(self::LEVEL_DEBUG, $message, $data);
    }

    /**
     * Write a log entry.
     *
     * @param string $level   Log level.
     * @param string $message Log message.
     * @param array<string, mixed> $data Structured data.
     */
    private function log(string $level, string $message, array $data): void
    {
        $entry = sprintf(
            '[%s][%s] %s',
            $this->context,
            strtoupper($level),
            $message
        );

        if (!empty($data)) {
            // Safe encoding — never log passwords or payment data.
            $safe = $this->sanitize_data($data);
            $entry .= ' ' . wp_json_encode($safe);
        }

        // Use WooCommerce logger if available, otherwise error_log.
        if (function_exists('wc_get_logger')) {
            $logger = wc_get_logger();
            $logger->log($level, $entry, ['source' => $this->context]);
        } else {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log($entry);
        }
    }

    /**
     * Remove sensitive keys from data before logging.
     *
     * @param array<string, mixed> $data Raw data.
     * @return array<string, mixed> Sanitized data.
     */
    private function sanitize_data(array $data): array
    {
        $sensitive_keys = [
            'password', 'secret', 'api_key', 'token', 'card', 'cvv', 'ssn',
            'stripe_secret', 'paypal_secret', 'private_key',
        ];

        foreach ($data as $key => $value) {
            $lower_key = strtolower($key);

            foreach ($sensitive_keys as $sensitive) {
                if (str_contains($lower_key, $sensitive)) {
                    $data[$key] = '[REDACTED]';
                    continue 2;
                }
            }

            if (is_array($value)) {
                $data[$key] = $this->sanitize_data($value);
            }
        }

        return $data;
    }
}
