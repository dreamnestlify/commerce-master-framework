<?php
/**
 * WP-CLI command for Commerce Core.
 *
 * Usage:
 *   wp commerce-core settings          # Show current settings
 *   wp commerce-core self-check        # Run self-diagnostics
 *
 * @package CommerceMaster\Core\Cli
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Cli;

use CommerceMaster\Core\Module\SettingsModule;

class CoreCommand
{
    /**
     * Show current plugin settings.
     *
     * ## EXAMPLES
     *
     *     wp commerce-core settings
     *
     * @subcommand settings
     */
    public function settings(): void
    {
        $settings = get_option(SettingsModule::OPTION_KEY, []);

        if (empty($settings)) {
            \WP_CLI::warning('Settings not yet initialized. Activate the plugin first.');
            return;
        }

        \WP_CLI\Utils\format_items(
            [['key' => 'brand.name', 'value' => $settings['brand']['name'] ?? ''],
             ['key' => 'brand.tagline', 'value' => $settings['brand']['tagline'] ?? ''],
             ['key' => 'market.base_currency', 'value' => $settings['market']['base_currency'] ?? ''],
             ['key' => 'market.enabled_currencies', 'value' => implode(', ', $settings['market']['enabled_currencies'] ?? [])],
             ['key' => 'market.default_locale', 'value' => $settings['market']['default_locale'] ?? ''],
             ['key' => 'support.email', 'value' => $settings['support']['email'] ?? '']],
            ['key', 'value']
        );
    }

    /**
     * Run self-diagnostics.
     *
     * ## EXAMPLES
     *
     *     wp commerce-core self-check
     *
     * @subcommand self-check
     */
    public function self_check(): void
    {
        \WP_CLI::log('Running Commerce Core self-check...');
        \WP_CLI::log('');

        $checks = [
            'Plugin version'       => COMMERCE_CORE_VERSION,
            'PHP version'          => PHP_VERSION,
            'WordPress version'    => get_bloginfo('version'),
            'WooCommerce active'   => class_exists('WooCommerce') ? 'Yes' : 'No',
            'Plugin file'          => COMMERCE_CORE_FILE,
            'Plugin directory'     => COMMERCE_CORE_DIR,
            'Settings option key'  => SettingsModule::OPTION_KEY,
            'Settings initialized' => get_option(SettingsModule::OPTION_KEY) !== false ? 'Yes' : 'No',
        ];

        foreach ($checks as $label => $value) {
            \WP_CLI::log(sprintf('  %-25s %s', $label, $value));
        }

        \WP_CLI::log('');
        \WP_CLI::success('Self-check complete.');
    }
}
