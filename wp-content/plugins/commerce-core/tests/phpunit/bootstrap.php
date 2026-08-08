<?php
/**
 * PHPUnit bootstrap for Commerce Core tests.
 *
 * @package CommerceMaster\Core\Tests
 */

declare(strict_types=1);

// First check for Composer's autoloader.
$composer_autoload = __DIR__ . '/../../../vendor/autoload.php';
if (file_exists($composer_autoload)) {
    require_once $composer_autoload;
} else {
    // Fall back to our internal autoloader.
    require_once __DIR__ . '/../../../src/Autoload.php';
    \CommerceMaster\Core\Autoload::register();
}

// If WordPress test suite is available, load it.
$_tests_dir = getenv('WP_TESTS_DIR');

if (!$_tests_dir) {
    $_tests_dir = sys_get_temp_dir() . '/wordpress-tests-lib';
}

// Try to load WordPress test functions.
$_test_functions = $_tests_dir . '/includes/functions.php';

if (file_exists($_test_functions)) {
    require_once $_test_functions;
    // This sets up the WordPress test environment.
}

/**
 * Minimal WordPress function stubs for unit testing without WP.
 * These allow our pure-PHP logic tests to run.
 */
if (!function_exists('get_option')) {
    /**
     * @var array<string, mixed> $wp_options_test Test options store.
     */
    $GLOBALS['_test_options'] = [];

    function get_option(string $name, $default = false)
    {
        return $GLOBALS['_test_options'][$name] ?? $default;
    }

    function add_option(string $name, $value): bool
    {
        $GLOBALS['_test_options'][$name] = $value;
        return true;
    }

    function update_option(string $name, $value): bool
    {
        $GLOBALS['_test_options'][$name] = $value;
        return true;
    }

    function delete_option(string $name): bool
    {
        unset($GLOBALS['_test_options'][$name]);
        return true;
    }

    function sanitize_text_field($value): string
    {
        return is_scalar($value) ? trim(strip_tags((string) $value)) : '';
    }

    function sanitize_email($value): string
    {
        $value = sanitize_text_field($value);
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false ? $value : '';
    }

    function absint($value): int
    {
        return abs((int) $value);
    }

    function __($text, ?string $domain = null): string
    {
        return $text;
    }

    function esc_html($text): string
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }

    function esc_html__($text, ?string $domain = null): string
    {
        return esc_html(__($text, $domain));
    }

    function esc_attr($text): string
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }

    function esc_url($url): string
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false ? $url : '';
    }

    function current_user_can(string $capability): bool
    {
        return true; // Always allow in tests.
    }

    function load_plugin_textdomain(?string $domain, $deprecated = false, $plugin_rel_path = false): bool
    {
        return true;
    }

    function wp_verify_nonce($nonce, string $action = ''): int|false
    {
        return $nonce === 'valid_nonce' ? 1 : false;
    }

    function wp_create_nonce(string $action = ''): string
    {
        return 'test_nonce';
    }

    function flush_rewrite_rules(): void
    {
        // No-op in tests.
    }

    function add_action(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): true
    {
        return true;
    }

    function add_filter(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): true
    {
        return true;
    }

    function register_activation_hook(string $file, callable $callback): void
    {
        // No-op.
    }

    function register_deactivation_hook(string $file, callable $callback): void
    {
        // No-op.
    }

    function plugin_dir_path(string $file): string
    {
        return dirname($file) . '/';
    }

    function plugin_dir_url(string $file): string
    {
        return 'http://test.example.com/' . basename(dirname($file)) . '/';
    }

    function plugin_basename(string $file): string
    {
        return basename(dirname($file)) . '/' . basename($file);
    }

    function wp_die($message = ''): void
    {
        throw new RuntimeException(is_string($message) ? $message : 'wp_die called');
    }

    function add_menu_page(string $page_title, string $menu_title, string $capability, string $menu_slug, $callback = '', string $icon = '', int $position = 10): string
    {
        return 'toplevel_page_' . $menu_slug;
    }

    function register_setting(string $group, string $name, array $args = []): void
    {
        // No-op.
    }

    function get_bloginfo(string $show = ''): string
    {
        return '6.7';
    }
}
