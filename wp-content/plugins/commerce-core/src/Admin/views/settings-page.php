<?php
/**
 * Admin Settings Page View.
 *
 * Variables available:
 * @var array<string, mixed> $settings Current settings.
 * @package CommerceMaster\Core
 */

declare(strict_types=1);

// phpcs:disable WordPress.Security.EscapeOutput — all output is escaped inline.
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap commerce-core-admin">
    <h1><?php echo esc_html__('Commerce Core Settings', 'commerce-core'); ?></h1>

    <form method="post" action="options.php">
        <?php
        settings_fields('commerce_core_settings_group');
        do_settings_sections('commerce-core');
        ?>

        <h2><?php echo esc_html__('Brand', 'commerce-core'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="commerce_core_brand_name"><?php echo esc_html__('Brand Name', 'commerce-core'); ?></label>
                </th>
                <td>
                    <input type="text"
                           id="commerce_core_brand_name"
                           name="commerce_core_settings[brand][name]"
                           value="<?php echo esc_attr($settings['brand']['name'] ?? ''); ?>"
                           class="regular-text"
                           required />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="commerce_core_brand_tagline"><?php echo esc_html__('Brand Tagline', 'commerce-core'); ?></label>
                </th>
                <td>
                    <input type="text"
                           id="commerce_core_brand_tagline"
                           name="commerce_core_settings[brand][tagline]"
                           value="<?php echo esc_attr($settings['brand']['tagline'] ?? ''); ?>"
                           class="regular-text" />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="commerce_core_brand_logo_id"><?php echo esc_html__('Logo (Attachment ID)', 'commerce-core'); ?></label>
                </th>
                <td>
                    <input type="number"
                           id="commerce_core_brand_logo_id"
                           name="commerce_core_settings[brand][logo_id]"
                           value="<?php echo esc_attr((string) ($settings['brand']['logo_id'] ?? 0)); ?>"
                           min="0" />
                    <p class="description"><?php echo esc_html__('Enter the media attachment ID for your logo.', 'commerce-core'); ?></p>
                </td>
            </tr>
        </table>

        <h2><?php echo esc_html__('Market', 'commerce-core'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="commerce_core_market_locale"><?php echo esc_html__('Default Locale', 'commerce-core'); ?></label>
                </th>
                <td>
                    <input type="text"
                           id="commerce_core_market_locale"
                           name="commerce_core_settings[market][default_locale]"
                           value="<?php echo esc_attr($settings['market']['default_locale'] ?? 'en_US'); ?>"
                           class="regular-text" />
                    <p class="description"><?php echo esc_html__('e.g., en_US, en_GB, de_DE', 'commerce-core'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="commerce_core_market_currency"><?php echo esc_html__('Base Currency', 'commerce-core'); ?></label>
                </th>
                <td>
                    <input type="text"
                           id="commerce_core_market_currency"
                           name="commerce_core_settings[market][base_currency]"
                           value="<?php echo esc_attr($settings['market']['base_currency'] ?? 'USD'); ?>"
                           class="regular-text" />
                    <p class="description"><?php echo esc_html__('ISO 4217 code, e.g., USD, EUR, GBP', 'commerce-core'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="commerce_core_market_currencies"><?php echo esc_html__('Enabled Currencies', 'commerce-core'); ?></label>
                </th>
                <td>
                    <input type="text"
                           id="commerce_core_market_currencies"
                           name="commerce_core_settings[market][enabled_currencies]"
                           value="<?php echo esc_attr(implode(',', $settings['market']['enabled_currencies'] ?? ['USD', 'EUR', 'GBP'])); ?>"
                           class="regular-text" />
                    <p class="description"><?php echo esc_html__('Comma-separated ISO 4217 codes', 'commerce-core'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="commerce_core_market_market"><?php echo esc_html__('Default Market', 'commerce-core'); ?></label>
                </th>
                <td>
                    <select id="commerce_core_market_market" name="commerce_core_settings[market][default_market]">
                        <?php
                        $markets = ['EU' => 'Europe', 'US' => 'United States', 'UK' => 'United Kingdom', 'GLOBAL' => 'Global'];
                        $current_market = $settings['market']['default_market'] ?? 'EU';
                        foreach ($markets as $code => $label) :
                            ?>
                            <option value="<?php echo esc_attr($code); ?>" <?php selected($current_market, $code); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>

        <h2><?php echo esc_html__('Support', 'commerce-core'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="commerce_core_support_email"><?php echo esc_html__('Support Email', 'commerce-core'); ?></label>
                </th>
                <td>
                    <input type="email"
                           id="commerce_core_support_email"
                           name="commerce_core_settings[support][email]"
                           value="<?php echo esc_attr($settings['support']['email'] ?? ''); ?>"
                           class="regular-text" />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="commerce_core_support_phone"><?php echo esc_html__('Support Phone', 'commerce-core'); ?></label>
                </th>
                <td>
                    <input type="text"
                           id="commerce_core_support_phone"
                           name="commerce_core_settings[support][phone]"
                           value="<?php echo esc_attr($settings['support']['phone'] ?? ''); ?>"
                           class="regular-text" />
                </td>
            </tr>
        </table>

        <h2><?php echo esc_html__('Analytics', 'commerce-core'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="commerce_core_analytics_ga4"><?php echo esc_html__('GA4 Measurement ID', 'commerce-core'); ?></label>
                </th>
                <td>
                    <input type="text"
                           id="commerce_core_analytics_ga4"
                           name="commerce_core_settings[analytics][ga4_measurement_id]"
                           value="<?php echo esc_attr($settings['analytics']['ga4_measurement_id'] ?? ''); ?>"
                           class="regular-text"
                           placeholder="G-XXXXXXXXXX" />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="commerce_core_analytics_meta"><?php echo esc_html__('Meta Pixel ID', 'commerce-core'); ?></label>
                </th>
                <td>
                    <input type="text"
                           id="commerce_core_analytics_meta"
                           name="commerce_core_settings[analytics][meta_pixel_id]"
                           value="<?php echo esc_attr($settings['analytics']['meta_pixel_id'] ?? ''); ?>"
                           class="regular-text"
                           placeholder="123456789012345" />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="commerce_core_analytics_tiktok"><?php echo esc_html__('TikTok Pixel ID', 'commerce-core'); ?></label>
                </th>
                <td>
                    <input type="text"
                           id="commerce_core_analytics_tiktok"
                           name="commerce_core_settings[analytics][tiktok_pixel_id]"
                           value="<?php echo esc_attr($settings['analytics']['tiktok_pixel_id'] ?? ''); ?>"
                           class="regular-text" />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="commerce_core_analytics_ads"><?php echo esc_html__('Google Ads ID', 'commerce-core'); ?></label>
                </th>
                <td>
                    <input type="text"
                           id="commerce_core_analytics_ads"
                           name="commerce_core_settings[analytics][google_ads_id]"
                           value="<?php echo esc_attr($settings['analytics']['google_ads_id'] ?? ''); ?>"
                           class="regular-text"
                           placeholder="AW-XXXXXXXXX" />
                </td>
            </tr>
        </table>

        <h2><?php echo esc_html__('Payment', 'commerce-core'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php echo esc_html__('Stripe', 'commerce-core'); ?></th>
                <td>
                    <label>
                        <input type="checkbox"
                               name="commerce_core_settings[payment][stripe_enabled]"
                               <?php checked($settings['payment']['stripe_enabled'] ?? false); ?> />
                        <?php echo esc_html__('Enable Stripe (sandbox in Phase 0)', 'commerce-core'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php echo esc_html__('PayPal', 'commerce-core'); ?></th>
                <td>
                    <label>
                        <input type="checkbox"
                               name="commerce_core_settings[payment][paypal_enabled]"
                               <?php checked($settings['payment']['paypal_enabled'] ?? false); ?> />
                        <?php echo esc_html__('Enable PayPal (sandbox in Phase 0)', 'commerce-core'); ?>
                    </label>
                </td>
            </tr>
        </table>

        <?php submit_button(__('Save Settings', 'commerce-core')); ?>
    </form>

    <div class="commerce-core-system-info">
        <h2><?php echo esc_html__('System Information', 'commerce-core'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php echo esc_html__('Plugin Version', 'commerce-core'); ?></th>
                <td><code><?php echo esc_html(COMMERCE_CORE_VERSION); ?></code></td>
            </tr>
            <tr>
                <th scope="row"><?php echo esc_html__('PHP Version', 'commerce-core'); ?></th>
                <td><code><?php echo esc_html(PHP_VERSION); ?></code></td>
            </tr>
            <tr>
                <th scope="row"><?php echo esc_html__('WordPress Version', 'commerce-core'); ?></th>
                <td><code><?php echo esc_html(get_bloginfo('version')); ?></code></td>
            </tr>
            <tr>
                <th scope="row"><?php echo esc_html__('WooCommerce', 'commerce-core'); ?></th>
                <td><code><?php echo class_exists('WooCommerce') ? esc_html(WC()->version ?? 'Active') : esc_html__('Not active', 'commerce-core'); ?></code></td>
            </tr>
        </table>
    </div>
</div>
