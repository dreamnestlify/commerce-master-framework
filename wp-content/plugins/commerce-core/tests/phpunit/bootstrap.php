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
            // Controllable via $GLOBALS['_test_user_can'] for permission tests.
            if (isset($GLOBALS['_test_user_can'])) {
                return $GLOBALS['_test_user_can'];
            }
            return true; // Default: allow in tests.
        }

        function load_plugin_textdomain(?string $domain, $deprecated = false, $plugin_rel_path = false): bool
        {
            return true;
        }

        function wp_verify_nonce($nonce, string $action = ''): int|false
        {
            // Support both admin nonce and REST nonce in tests.
            if ($action === 'wp_rest') {
                return $nonce === 'valid_rest_nonce' ? 1 : false;
            }
            return $nonce === 'valid_nonce' ? 1 : false;
        }

        function wp_create_nonce(string $action = ''): string
        {
            if ($action === 'wp_rest') {
                return 'valid_rest_nonce';
            }
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

        function add_shortcode(string $tag, callable $callback): void
        {
            // No-op.
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

        // ── WP_REST_Request stub ──
        if (!class_exists('WP_REST_Request')) {
            class WP_REST_Request
            {
                private array $headers = [];
                private array $json_params = [];
                private array $query_params = [];
                private string $method = 'GET';

                public function set_method(string $method): void
                {
                    $this->method = $method;
                }

                public function get_method(): string
                {
                    return $this->method;
                }

                public function set_header(string $key, string $value): void
                {
                    $this->headers[strtolower($key)] = $value;
                }

                public function get_header(string $key): ?string
                {
                    $key = strtolower($key);
                    return $this->headers[$key] ?? null;
                }

                public function set_json_params(array $params): void
                {
                    $this->json_params = $params;
                }

                public function get_json_params(): array
                {
                    return $this->json_params;
                }

                public function set_query_params(array $params): void
                {
                    $this->query_params = $params;
                }

                public function get_query_params(): array
                {
                    return $this->query_params;
                }

                public function get_param(string $key)
                {
                    return $this->json_params[$key] ?? $this->query_params[$key] ?? null;
                }
            }
        }

        // ── WP_REST_Response stub ──
        if (!class_exists('WP_REST_Response')) {
            class WP_REST_Response
            {
                public $data;
                public int $status = 200;

                public function __construct($data = null, int $status = 200)
                {
                    $this->data = $data;
                    $this->status = $status;
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
            // WordPress wraps %s values in quotes, so we do the same.
            $result = $query;
            foreach ($args as $arg) {
                $pos = strpos($result, '%s');
                if ($pos !== false) {
                    $result = substr_replace($result, "'" . (string) $arg . "'", $pos, 2);
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

        // ── WordPress constants needed by modules ──
        if (!defined('COOKIEPATH')) {
            define('COOKIEPATH', '/');
        }
        if (!defined('COOKIE_DOMAIN')) {
            define('COOKIE_DOMAIN', false);
        }

        // ── Plugin constants needed by module enqueue methods ──
        if (!defined('COMMERCE_CORE_VERSION')) {
            define('COMMERCE_CORE_VERSION', '0.1.0');
        }
        if (!defined('COMMERCE_CORE_FILE')) {
            define('COMMERCE_CORE_FILE', __FILE__);
        }
        if (!defined('COMMERCE_CORE_DIR')) {
            define('COMMERCE_CORE_DIR', dirname($plugin_root) . '/');
        }
        if (!defined('COMMERCE_CORE_URL')) {
            define('COMMERCE_CORE_URL', 'http://test.example.com/wp-content/plugins/commerce-core/');
        }
        if (!defined('COMMERCE_CORE_BASENAME')) {
            define('COMMERCE_CORE_BASENAME', 'commerce-core/commerce-core.php');
        }

        // ── User / auth stubs ──
        if (!function_exists('is_user_logged_in')) {
            function is_user_logged_in(): bool
            {
                return $GLOBALS['_test_user_logged_in'] ?? false;
            }
        }

        if (!function_exists('get_current_user_id')) {
            function get_current_user_id(): int
            {
                return $GLOBALS['_test_current_user_id'] ?? 0;
            }
        }

        if (!function_exists('get_user_meta')) {
            function get_user_meta(int $user_id, string $key, bool $single = false)
            {
                $meta = $GLOBALS['_test_user_meta'][$user_id][$key] ?? null;
                if ($single) {
                    return $meta;
                }
                return $meta !== null ? array($meta) : array();
            }
        }

        if (!function_exists('update_user_meta')) {
            function update_user_meta(int $user_id, string $key, $value, $prev_value = ''): int|bool
            {
                if (!isset($GLOBALS['_test_user_meta'])) {
                    $GLOBALS['_test_user_meta'] = array();
                }
                if (!isset($GLOBALS['_test_user_meta'][$user_id])) {
                    $GLOBALS['_test_user_meta'][$user_id] = array();
                }
                $GLOBALS['_test_user_meta'][$user_id][$key] = $value;
                return true;
            }
        }

        if (!function_exists('delete_user_meta')) {
            function delete_user_meta(int $user_id, string $key, $value = ''): bool
            {
                unset($GLOBALS['_test_user_meta'][$user_id][$key]);
                return true;
            }
        }

        // ── Template / page conditionals ──
        if (!function_exists('is_singular')) {
            function is_singular($types = ''): bool
            {
                return $GLOBALS['_test_is_singular'] ?? false;
            }
        }

        if (!function_exists('is_shop')) {
            function is_shop(): bool
            {
                return $GLOBALS['_test_is_shop'] ?? false;
            }
        }

        if (!function_exists('is_product_category')) {
            function is_product_category($term = ''): bool
            {
                return $GLOBALS['_test_is_product_category'] ?? false;
            }
        }

        if (!function_exists('is_admin')) {
            function is_admin(): bool
            {
                return false;
            }
        }

        if (!function_exists('is_page')) {
            function is_page($page = ''): bool
            {
                if (is_string($page) && $page !== '') {
                    return ($GLOBALS['_test_page_slug'] ?? '') === $page;
                }
                return $GLOBALS['_test_is_page'] ?? false;
            }
        }

        if (!function_exists('get_the_ID')) {
            function get_the_ID(): int|false
            {
                return $GLOBALS['_test_the_id'] ?? false;
            }
        }

        if (!function_exists('get_permalink')) {
            function get_permalink($post = 0): string
            {
                $id = is_object($post) ? ($post->ID ?? 0) : (int) $post;
                return 'http://test.example.com/?p=' . $id;
            }
        }

        // ── Asset / script stubs ──
        if (!function_exists('wp_enqueue_script')) {
            function wp_enqueue_script(string $handle, $src = '', array $deps = array(), $ver = false, $in_footer = false): void
            {
                // No-op.
            }
        }

        if (!function_exists('wp_localize_script')) {
            function wp_localize_script(string $handle, string $object_name, array $data): bool
            {
                $GLOBALS['_test_localized_scripts'][$handle] = array(
                    'object_name' => $object_name,
                    'data' => $data,
                );
                return true;
            }
        }

        if (!function_exists('plugins_url')) {
            function plugins_url($path = '', $plugin = ''): string
            {
                return 'http://test.example.com/wp-content/plugins/' . ltrim($path, '/');
            }
        }

        if (!function_exists('esc_url_raw')) {
            function esc_url_raw(string $url): string
            {
                return $url;
            }
        }

        if (!function_exists('rest_url')) {
            function rest_url(string $path = ''): string
            {
                return 'http://test.example.com/wp-json/' . ltrim($path, '/');
            }
        }

        if (!function_exists('esc_attr__')) {
            function esc_attr__(string $text, ?string $domain = null): string
            {
                return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
            }
        }

        if (!function_exists('headers_sent')) {
            function headers_sent(&$file = null, &$line = null): bool
            {
                return $GLOBALS['_test_headers_sent'] ?? false;
            }
        }

        if (!function_exists('setcookie')) {
            function setcookie(string $name, string $value = '', int $expires = 0, string $path = '', string $domain = '', bool $secure = false, bool $httponly = false): bool
            {
                $GLOBALS['_test_cookies'][$name] = array(
                    'value' => $value,
                    'expires' => $expires,
                );
                $_COOKIE[$name] = $value;
                return true;
            }
        }

        if (!function_exists('wp_get_attachment_image_url')) {
            function wp_get_attachment_image_url($attachment_id, $size = 'thumbnail', $icon = false): string|false
            {
                if (!$attachment_id) {
                    return false;
                }
                return 'http://test.example.com/wp-content/uploads/test-image-' . (int) $attachment_id . '.jpg';
            }
        }

        if (!function_exists('wc_get_page_id')) {
            function wc_get_page_id(string $page): int
            {
                return $GLOBALS['_test_wc_page_ids'][$page] ?? 1;
            }
        }

        // ── WooCommerce product stub for tests ──
        if (!function_exists('wc_get_product')) {
            /**
             * @param mixed $product_id
             * @return \TestWCProduct|false
             */
            function wc_get_product($product_id = false)
            {
                if (!$product_id) {
                    return false;
                }
                $products = $GLOBALS['_test_wc_products'] ?? array();
                return $products[(int) $product_id] ?? false;
            }
        }

        if (!class_exists('TestWCProduct')) {
            /**
             * Minimal WC_Product stub for unit testing.
             */
            class TestWCProduct
            {
                private int $id;
                private string $name;
                private string $price_html;
                private string $stock_status;
                private string $type;
                private int $image_id;

                public function __construct(array $data)
                {
                    $this->id = $data['id'] ?? 0;
                    $this->name = $data['name'] ?? 'Test Product';
                    $this->price_html = $data['price_html'] ?? '$29.99';
                    $this->stock_status = $data['stock_status'] ?? 'instock';
                    $this->type = $data['type'] ?? 'simple';
                    $this->image_id = $data['image_id'] ?? 1;
                }

                public function get_id(): int
                {
                    return $this->id;
                }

                public function get_name(): string
                {
                    return $this->name;
                }

                public function get_permalink(): string
                {
                    return 'http://test.example.com/?p=' . $this->id;
                }

                public function get_price_html(): string
                {
                    return $this->price_html;
                }

                public function get_image_id(): int
                {
                    return $this->image_id;
                }

                public function get_stock_status(): string
                {
                    return $this->stock_status;
                }

                public function get_type(): string
                {
                    return $this->type;
                }

                public function get_image($size = 'shop_thumbnail', $attr = array(), $placeholder = true): string
                {
                    return '<img src="http://test.example.com/image-' . $this->id . '.jpg" alt="' . esc_attr($this->name) . '" />';
                }
            }
        }

        // ── Helper: register a test product ──
        if (!function_exists('_test_add_wc_product')) {
            function _test_add_wc_product(int $id, array $data = array()): void
            {
                if (!isset($GLOBALS['_test_wc_products'])) {
                    $GLOBALS['_test_wc_products'] = array();
                }
                $data['id'] = $id;
                $GLOBALS['_test_wc_products'][$id] = new \TestWCProduct($data);
            }
        }

        // ── Output buffering stub ──
        if (!function_exists('ob_start')) {
            // ob_start is a PHP built-in; this is here as a safety check.
        }

        // ── Payment-related stubs ──
        if (!function_exists('wc_get_order')) {
            /**
             * @param mixed $order Order ID or object.
             * @return \TestWCOrder|false
             */
            function wc_get_order($order = false)
            {
                if (!$order) {
                    return false;
                }
                $id = is_object($order) && method_exists($order, 'get_id') ? $order->get_id() : (int) $order;
                $orders = $GLOBALS['_test_wc_orders'] ?? array();
                return $orders[$id] ?? false;
            }
        }

        if (!function_exists('wc_add_notice')) {
            function wc_add_notice(string $message, string $notice_type = 'success'): void
            {
                $GLOBALS['_test_notices'][] = array('message' => $message, 'type' => $notice_type);
            }
        }

        if (!function_exists('status_header')) {
            function status_header(int $code, string $description = ''): void
            {
                $GLOBALS['_test_status_header'] = $code;
            }
        }

        if (!function_exists('wp_remote_post')) {
            function wp_remote_post(string $url, array $args = array())
            {
                return $GLOBALS['_test_wp_remote_response'] ?? new \WP_Error('http_error', 'No mock response set');
            }
        }

        if (!function_exists('wp_remote_retrieve_body')) {
            function wp_remote_retrieve_body($response): string
            {
                if (is_array($response) && isset($response['body'])) {
                    return $response['body'];
                }
                return '';
            }
        }

        if (!function_exists('wp_remote_retrieve_response_code')) {
            function wp_remote_retrieve_response_code($response): int
            {
                if (is_array($response) && isset($response['response']['code'])) {
                    return $response['response']['code'];
                }
                return 0;
            }
        }

        if (!class_exists('TestWCOrder')) {
            /**
             * Minimal WC_Order stub for payment tests.
             */
            class TestWCOrder
            {
                private int $id;
                private string $currency = 'USD';
                private string $total = '0.00';
                private string $transaction_id = '';
                private string $order_number = '';
                private string $status = 'pending';

                public function __construct(array $data = array())
                {
                    $this->id = $data['id'] ?? 0;
                    $this->currency = $data['currency'] ?? 'USD';
                    $this->total = $data['total'] ?? '0.00';
                    $this->transaction_id = $data['transaction_id'] ?? '';
                    $this->order_number = $data['order_number'] ?? (string) $this->id;
                    $this->status = $data['status'] ?? 'pending';
                }

                public function get_id(): int { return $this->id; }
                public function get_currency(): string { return $this->currency; }
                public function get_total(): string { return $this->total; }
                public function get_transaction_id(): string { return $this->transaction_id; }
                public function get_order_number(): string { return $this->order_number; }
                public function payment_complete($transaction_id = ''): bool { $this->transaction_id = $transaction_id; $this->status = 'processing'; return true; }
                public function add_order_note(string $note): int { return 1; }
                public function update_status(string $status, string $note = ''): void { $this->status = $status; }
                public function has_status(array $statuses): bool { return in_array($this->status, $statuses, true); }
            }
        }

        if (!function_exists('_test_add_wc_order')) {
            function _test_add_wc_order(int $id, array $data = array()): void
            {
                if (!isset($GLOBALS['_test_wc_orders'])) {
                    $GLOBALS['_test_wc_orders'] = array();
                }
                $data['id'] = $id;
                $GLOBALS['_test_wc_orders'][$id] = new \TestWCOrder($data);
            }
        }

        // ── WC_Payment_Gateway stub (so gateway classes can be loaded) ──
        if (!class_exists('WC_Payment_Gateway')) {
            abstract class WC_Payment_Gateway
            {
                public $id;
                public $icon;
                public $has_fields;
                public $method_title;
                public $method_description;
                public $supports;
                public $title;
                public $description;
                public $enabled;
                public $form_fields = array();
                public $settings = array();

                abstract public function init_form_fields(): void;
                public function init_settings(): void {}
                public function get_option($key, $default = false) { return $this->settings[$key] ?? $default; }
                public function process_payment($order_id) { return array('result' => 'failure'); }
                public function process_refund($order_id, $amount = null, $reason = '') { return false; }
                public function get_return_url($order = null) { return 'http://test.example.com/checkout/order-received/'; }
                public function is_available(): bool { return true; }
                public function process_admin_options(): bool { return true; }
            }
        }

        // ── Stripe SDK stubs (for headless tests without Composer) ──
        if (!class_exists('Stripe\\Stripe')) {
            class StripeStripeStub
            {
                public static $api_key = '';
                public static function setApiKey(string $key): void { self::$api_key = $key; }
            }
        }

        if (!class_exists('Stripe\\Exception\\ApiErrorException')) {
            class StripeApiErrorExceptionStub extends \Exception {}
        }

    }
}
