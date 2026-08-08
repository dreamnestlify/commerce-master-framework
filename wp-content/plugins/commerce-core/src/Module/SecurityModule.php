<?php
/**
 * Security Module — nonce, capability, escaping helpers.
 *
 * @package CommerceMaster\Core\Module
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Module;

class SecurityModule implements ModuleInterface
{
    public const NONCE_ACTION = 'commerce_core_nonce';
    public const NONCE_NAME = '_commerce_core_nonce';

    public function register(): void
    {
        // No hooks to register in Phase 0.
    }

    public function boot(): void
    {
        // Add security headers.
        add_action('send_headers', [$this, 'add_security_headers']);
    }

    public function activate(): void
    {
        // No activation tasks in Phase 0.
    }

    public function get_id(): string
    {
        return 'security';
    }

    /**
     * Generate a nonce field for forms.
     *
     * @param bool $echo Whether to echo (true) or return (false).
     * @return string HTML nonce field.
     */
    public static function nonce_field(bool $echo = true): string
    {
        return wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME, true, $echo);
    }

    /**
     * Verify a nonce from request.
     *
     * Uses WordPress REST API standard: checks X-WP-Nonce header first,
     * falls back to request parameter. All input is unslashed and sanitized.
     *
     * @return bool True if valid.
     */
    public static function verify_nonce(): bool
    {
        // For REST API requests, the nonce is sent via X-WP-Nonce header.
        $nonce = '';
        if (isset($_SERVER['HTTP_X_WP_NONCE'])) {
            $nonce = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_WP_NONCE']));
        } elseif (isset($_REQUEST[self::NONCE_NAME])) {
            $nonce = sanitize_text_field(wp_unslash($_REQUEST[self::NONCE_NAME]));
        }

        return $nonce !== '' && wp_verify_nonce($nonce, self::NONCE_ACTION) !== false;
    }

    /**
     * Check user capability.
     *
     * @param string $capability WordPress capability.
     * @return bool True if user has capability.
     */
    public static function check_capability(string $capability = 'manage_options'): bool
    {
        return current_user_can($capability);
    }

    /**
     * Require capability — die if not allowed.
     *
     * @param string $capability WordPress capability.
     */
    public static function require_capability(string $capability = 'manage_options'): void
    {
        if (!self::check_capability($capability)) {
            wp_die(esc_html__('Insufficient permissions.', 'commerce-core'));
        }
    }

    /**
     * Add security-related HTTP headers.
     */
    public function add_security_headers(): void
    {
        if (is_admin()) {
            return;
        }

        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
}
