<?php
/**
 * PHPUnit bootstrap for Commerce Core tests.
 *
 * Loads Composer autoloader (if available) or falls back to
 * the plugin's internal PSR-4 autoloader. Provides WordPress
 * and WooCommerce function stubs for headless unit testing.
 *
 * @package CommerceMaster\Core\Tests
 */

declare(strict_types=1);

// ── Autoloader ──
// bootstrap.php is at: tests/phpunit/bootstrap.php
// Plugin root is 2 directories up.
$plugin_root = dirname(__DIR__, 2);

$composer_autoload = $plugin_root . '/vendor/autoload.php';
if (file_exists($composer_autoload)) {
    require_once $composer_autoload;
} else {
    // Fall back to our internal autoloader.
    require_once $plugin_root . '/src/Autoload.php';
    \CommerceMaster\Core\Autoload::register();
}

// ── WordPress test suite (optional) ──
// If WP_TESTS_DIR is set and the test library exists, load it.
$_tests_dir = getenv('WP_TESTS_DIR');

if ($_tests_dir && file_exists($_tests_dir . '/includes/functions.php')) {
    require_once $_tests_dir . '/includes/functions.php';
    // The WP test suite sets up a real WordPress environment.
    // When it's loaded, our stubs below are not needed.
} else {
    // ── Minimal WordPress + WooCommerce function stubs for headless testing ──
    // These allow our pure-PHP logic tests to run without WordPress.

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

        function wp_unslash($value)
        {
            if (is_string($value)) {
                return stripslashes($value);
            }
            if (is_array($value)) {
                return array_map('wp_unslash', $value);
            }
            return $value;
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
            return '7.0.2';
        }

        function wp_json_encode($data, int $options = 0, int $depth = 512): string
        {
            return json_encode($data, $options, $depth);
        }

        function wp_kses_post($data): string
        {
            return is_string($data) ? strip_tags($data, '<p><br><a><strong><em><span><div>') : '';
        }

        function do_action(string $hook_name, ...$args): void
        {
            // No-op.
        }

        function apply_filters(string $hook_name, $value, ...$args)
        {
            return $value;
        }

        function rest_ensure_response($response)
        {
            return $response;
        }

        function rest_authorization_required_code(): int
        {
            return 403;
        }

        function register_rest_route(string $namespace, string $route, array $args = [], bool $override = false): bool
        {
            return true;
        }

        function is_wp_error($thing): bool
        {
            return $thing instanceof \WP_Error;
        }

        // ── WooCommerce stubs ──
        function wc_get_logger(): object
        {
            return new class {
                public function log(string $level, string $message, array $context = []): void
                {
                    // No-op.
                }
            };
        }

        function get_terms(array $args = []): array|\WP_Error
        {
            $taxonomy = $args['taxonomy'] ?? '';
            $name = $args['name'] ?? '';
            $terms = $GLOBALS['_test_terms'][$taxonomy . ':' . $name] ?? [];
            return $terms;
        }

        function wp_insert_term(string $term, string $taxonomy, array $args = []): array|\WP_Error
        {
            if (!isset($GLOBALS['_test_term_counter'])) {
                $GLOBALS['_test_term_counter'] = 1000;
            }
            $GLOBALS['_test_term_counter']++;
            $id = $GLOBALS['_test_term_counter'];

            if (!isset($GLOBALS['_test_terms'])) {
                $GLOBALS['_test_terms'] = [];
            }
            $key = $taxonomy . ':' . $term;
            $GLOBALS['_test_terms'][$key][] = (object) [
                'term_id' => $id,
                'name' => $term,
                'slug' => $args['slug'] ?? sanitize_title($term),
                'taxonomy' => $taxonomy,
            ];
            return ['term_id' => $id];
        }

        function term_exists($term, string $taxonomy = '', int $parent = 0): mixed
        {
            $key = $taxonomy . ':' . $term;
            $terms = $GLOBALS['_test_terms'][$key] ?? [];
            return empty($terms) ? null : $terms[0]->term_id;
        }

        function sanitize_title(string $title): string
        {
            return strtolower(preg_replace('/[^a-z0-9-]/', '-', $title));
        }

        function get_term($term, string $taxonomy = ''): \WP_Error|array|null
        {
            return null;
        }

        // ── WP_Error class stub ──
        if (!class_exists('WP_Error')) {
            class WP_Error
            {
                public array $errors = [];
                public array $error_data = [];

                public function __construct(string $code = '', string $message = '', $data = '')
                {
                    if ($code) {
                        $this->errors[$code][] = $message;
                        if ($data) {
                            $this->error_data[$code] = $data;
                        }
                    }
                }

                public function get_error_code(): string|int|false
                {
                    $codes = array_keys($this->errors);
                    return $codes[0] ?? false;
                }

                public function get_error_message(string $code = ''): string
                {
                    if ($code) {
                        return $this->errors[$code][0] ?? '';
                    }
                    foreach ($this->errors as $messages) {
                        return $messages[0] ?? '';
                    }
                    return '';
                }

                public function add(string $code, string $message, $data = ''): void
                {
                    $this->errors[$code][] = $message;
                    if ($data) {
                        $this->error_data[$code] = $data;
                    }
                }
            }
        }

        // ── $wpdb stub ──
        $GLOBALS['wpdb'] = new class {
            public $prefix = 'wp_';
            public $posts = 'wp_posts';
            public $postmeta = 'wp_postmeta';
            public $terms = 'wp_terms';
            public $term_taxonomy = 'wp_term_taxonomy';

            public function prepare(string $query, ...$args): string
            {
                // Naive substitution for test purposes.
                $result = $query;
                foreach ($args as $arg) {
                    $pos = strpos($result, '%s');
                    if ($pos !== false) {
                        $result = substr_replace($result, (string) $arg, $pos, 2);
                    } elseif (($pos = strpos($result, '%d')) !== false) {
                        $result = substr_replace($result, (string) (int) $arg, $pos, 2);
                    }
                }
                return $result;
            }

            public function get_var(string $query): ?string
            {
                // Parse the query to extract table/conditions for test simulation.
                // For find_post_by_title: SELECT ID FROM wp_posts WHERE post_title = 'X' AND post_type = 'Y' ...
                // For find_attribute_by_slug: SELECT attribute_id FROM wp_woocommerce_attribute_taxonomies WHERE attribute_name = 'X' ...

                $GLOBALS['_wpdb_queries'][] = $query;

                // Check for post lookup
                if (preg_match("/post_title = '([^']+)'/", $query, $m)) {
                    $title = $m[1];
                    if (preg_match("/post_type = '([^']+)'/", $query, $m2)) {
                        $type = $m2[1];
                    } else {
                        $type = 'post';
                    }
                    $posts = $GLOBALS['_test_posts'][$type] ?? [];
                    foreach ($posts as $post) {
                        if ($post['post_title'] === $title) {
                            return (string) $post['ID'];
                        }
                    }
                }

                // Check for attribute lookup
                if (preg_match("/attribute_name = '([^']+)'/", $query, $m)) {
                    $slug = $m[1];
                    $attrs = $GLOBALS['_test_attributes'] ?? [];
                    foreach ($attrs as $attr) {
                        if ($attr['attribute_name'] === $slug) {
                            return (string) $attr['attribute_id'];
                        }
                    }
                }

                return null;
            }

            public function esc_like(string $text): string
            {
                return $text;
            }

            public function insert(string $table, array $data, array $format = []): int|false
            {
                return 1;
            }
        };

        // ── Helper: simulate post exists in test data ──
        if (!function_exists('_test_add_post')) {
            function _test_add_post(string $type, string $title, int $id): void
            {
                if (!isset($GLOBALS['_test_posts'][$type])) {
                    $GLOBALS['_test_posts'][$type] = [];
                }
                $GLOBALS['_test_posts'][$type][] = ['ID' => $id, 'post_title' => $title, 'post_type' => $type, 'post_status' => 'publish'];
            }
        }

        if (!function_exists('_test_add_attribute')) {
            function _test_add_attribute(string $slug, int $id): void
            {
                if (!isset($GLOBALS['_test_attributes'])) {
                    $GLOBALS['_test_attributes'] = [];
                }
                $GLOBALS['_test_attributes'][] = ['attribute_id' => $id, 'attribute_name' => $slug];
            }
        }
    }
}
