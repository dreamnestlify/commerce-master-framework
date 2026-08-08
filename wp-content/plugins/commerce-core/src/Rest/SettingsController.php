<?php
/**
 * REST API Settings Controller — exposes plugin settings via REST.
 *
 * @package CommerceMaster\Core\Rest
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Rest;

use CommerceMaster\Core\Module\SecurityModule;
use CommerceMaster\Core\Module\SettingsModule;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class SettingsController
{
    public const NAMESPACE = 'commerce-core/v1';
    public const REST_BASE = 'settings';

    /**
     * Register REST routes.
     */
    public function register_routes(): void
    {
        register_rest_route(self::NAMESPACE, '/' . self::REST_BASE, [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_settings'],
                'permission_callback' => [$this, 'get_settings_permissions'],
            ],
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'update_settings'],
                'permission_callback' => [$this, 'update_settings_permissions'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/' . self::REST_BASE . '/brand', [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_brand'],
                'permission_callback' => '__return_true',
            ],
        ]);
    }

    /**
     * Get settings (requires manage_options).
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function get_settings(WP_REST_Request $request)
    {
        $settings = $this->get_settings_module()->get_settings();

        // Remove any sensitive fields from REST response.
        unset($settings['credentials']);

        return rest_ensure_response($settings);
    }

    /**
     * Update settings (requires manage_options + nonce).
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function update_settings(WP_REST_Request $request)
    {
        if (!SecurityModule::verify_nonce()) {
            return new WP_Error(
                'rest_forbidden',
                __('Nonce verification failed.', 'commerce-core'),
                ['status' => 403]
            );
        }

        $params = $request->get_json_params();

        $settings_module = $this->get_settings_module();
        $sanitized = $settings_module->sanitize_settings($params);

        update_option(SettingsModule::OPTION_KEY, $sanitized);

        return rest_ensure_response([
            'success' => true,
            'message' => __('Settings updated.', 'commerce-core'),
        ]);
    }

    /**
     * Get brand info (public — no auth needed).
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response
     */
    public function get_brand(WP_REST_Request $request): WP_REST_Response
    {
        $settings = $this->get_settings_module()->get_settings();
        $brand = $settings['brand'] ?? [];

        return rest_ensure_response([
            'name'    => $brand['name'] ?? '',
            'tagline' => $brand['tagline'] ?? '',
            'logo'    => $brand['logo_id'] ?? 0,
        ]);
    }

    /**
     * Permission check for GET settings.
     */
    public function get_settings_permissions(): bool
    {
        return SecurityModule::check_capability('manage_options');
    }

    /**
     * Permission check for POST settings.
     */
    public function update_settings_permissions(): bool
    {
        return SecurityModule::check_capability('manage_options');
    }

    /**
     * Get the settings module instance.
     */
    private function get_settings_module(): SettingsModule
    {
        $settings = get_option(SettingsModule::OPTION_KEY, []);

        return new SettingsModule();
    }
}
