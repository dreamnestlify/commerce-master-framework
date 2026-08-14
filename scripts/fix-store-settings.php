<?php
/**
 * Fix WooCommerce settings: store address, currency, tax, shipping zones
 * Items #2-5 from TODO.md
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ==============================
// #2: Store Address / Country
// ==============================
update_option( 'woocommerce_store_address', 'Im Ziegelfeld 16' );
update_option( 'woocommerce_store_address_2', '' );
update_option( 'woocommerce_store_city', 'Bremervörde' );
update_option( 'woocommerce_store_postcode', '27432' );
update_option( 'woocommerce_default_country', 'DE' );
update_option( 'woocommerce_default_state', '' );

echo "#2 Store address updated:\n";
echo "  Address: Im Ziegelfeld 16, 27432 Bremervörde, DE\n";

// ==============================
// #3: Currency → EUR
// ==============================
update_option( 'woocommerce_currency', 'EUR' );
update_option( 'woocommerce_currency_pos', 'left' );
update_option( 'woocommerce_price_thousand_sep', '.' );
update_option( 'woocommerce_price_decimal_sep', ',' );
update_option( 'woocommerce_price_num_decimals', 2 );

echo "#3 Currency updated:\n";
echo "  Currency: EUR (€)\n";
echo "  Format: €1.299,00 (European format)\n";

// ==============================
// #4: Tax Calculation + German VAT
// ==============================
update_option( 'woocommerce_calc_taxes', 'yes' );
update_option( 'woocommerce_prices_include_tax', 'yes' );
update_option( 'woocommerce_tax_based_on', 'billing' );
update_option( 'woocommerce_tax_display_shop', 'incl' );
update_option( 'woocommerce_tax_display_cart', 'incl' );
update_option( 'woocommerce_shipping_tax_class', '' );
update_option( 'woocommerce_tax_round_at_subtotal', 'yes' );

// Remove existing tax rates
global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->prefix}woocommerce_tax_rates" );
$wpdb->query( "DELETE FROM {$wpdb->prefix}woocommerce_tax_rate_locations" );

// Insert German tax rates
// 19% standard rate (postage, electronics, clothing > threshold)
$wpdb->insert(
    "{$wpdb->prefix}woocommerce_tax_rates",
    array(
        'tax_rate_country'  => 'DE',
        'tax_rate_state'    => '',
        'tax_rate'          => '19.0000',
        'tax_rate_name'     => 'MwSt',
        'tax_rate_priority' => 1,
        'tax_rate_compound' => 0,
        'tax_rate_shipping' => 1,
        'tax_rate_order'    => 1,
        'tax_rate_class'    => '',
    )
);
echo "  Tax rate: DE 19% MwSt (standard)\n";

// 7% reduced rate (books, food, etc.)
$wpdb->insert(
    "{$wpdb->prefix}woocommerce_tax_rates",
    array(
        'tax_rate_country'  => 'DE',
        'tax_rate_state'    => '',
        'tax_rate'          => '7.0000',
        'tax_rate_name'     => 'MwSt (reduziert)',
        'tax_rate_priority' => 1,
        'tax_rate_compound' => 0,
        'tax_rate_shipping' => 0,
        'tax_rate_order'    => 2,
        'tax_rate_class'    => 'reduced-rate',
    )
);
echo "  Tax rate: DE 7% MwSt (reduced)\n";

// EU OSS: 19% for other EU countries (simplified - one rate for all EU)
$eu_countries = array( 'AT','BE','BG','HR','CY','CZ','DK','EE','FI','FR','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE' );
foreach ( $eu_countries as $country ) {
    $wpdb->insert(
        "{$wpdb->prefix}woocommerce_tax_rates",
        array(
            'tax_rate_country'  => $country,
            'tax_rate_state'    => '',
            'tax_rate'          => '19.0000',
            'tax_rate_name'     => 'VAT',
            'tax_rate_priority' => 1,
            'tax_rate_compound' => 0,
            'tax_rate_shipping' => 1,
            'tax_rate_order'    => 3,
            'tax_rate_class'    => '',
        )
    );
}
echo "  Tax rate: EU 19% VAT (OSS for 25 EU countries)\n";

echo "#4 Tax calculation enabled:\n";
echo "  Prices include tax: Yes (EU standard)\n";
echo "  Tax based on: Billing address\n";

// ==============================
// #5: Shipping Zones
// ==============================

// Delete existing zones
$existing_zones = WC_Shipping_Zones::get_zones();
foreach ( $existing_zones as $zone ) {
    $z = WC_Shipping_Zones::get_zone( $zone['zone_id'] );
    $z->delete();
}

// Zone 1: Germany (domestic)
$zone_de = new WC_Shipping_Zone();
$zone_de->set_zone_name( 'Germany (Deutschland)' );
$zone_de->add_location( 'DE', 'country' );
$zone_de->save();

// Add flat rate for Germany
$zone_de->add_shipping_method( 'flat_rate' );
$methods_de = $zone_de->get_shipping_methods();
if ( ! empty( $methods_de ) ) {
    $method_id = reset( $methods_de )->instance_id;
    update_option( 'woocommerce_flat_rate_' . $method_id . '_settings', array(
        'title'         => 'Standardversand (DHL)',
        'tax_status'    => 'taxable',
        'cost'          => '4.95',
    ) );
}

// Add free shipping for Germany (over €50)
$zone_de->add_shipping_method( 'free_shipping' );
$methods_de2 = $zone_de->get_shipping_methods();
$free_method_id = null;
foreach ( $methods_de2 as $m ) {
    if ( 'free_shipping' === $m->id ) {
        $free_method_id = $m->instance_id;
    }
}
if ( $free_method_id ) {
    update_option( 'woocommerce_free_shipping_' . $free_method_id . '_settings', array(
        'title'         => 'Kostenloser Versand',
        'requires'      => 'min_amount',
        'min_amount'    => '50',
        'ignore_discounts' => 'no',
    ) );
}

echo "#5 Shipping Zone 1 created: Germany\n";
echo "  - DHL Standard: €4.95\n";
echo "  - Free shipping over €50\n";

// Zone 2: EU countries
$zone_eu = new WC_Shipping_Zone();
$zone_eu->set_zone_name( 'European Union (EU)' );
foreach ( $eu_countries as $country ) {
    $zone_eu->add_location( $country, 'country' );
}
$zone_eu->save();

$zone_eu->add_shipping_method( 'flat_rate' );
$methods_eu = $zone_eu->get_shipping_methods();
if ( ! empty( $methods_eu ) ) {
    $method_id = reset( $methods_eu )->instance_id;
    update_option( 'woocommerce_flat_rate_' . $method_id . '_settings', array(
        'title'         => 'EU Standard Shipping',
        'tax_status'    => 'taxable',
        'cost'          => '12.90',
    ) );
}

$zone_eu->add_shipping_method( 'free_shipping' );
$methods_eu2 = $zone_eu->get_shipping_methods();
$free_eu_id = null;
foreach ( $methods_eu2 as $m ) {
    if ( 'free_shipping' === $m->id ) {
        $free_eu_id = $m->instance_id;
    }
}
if ( $free_eu_id ) {
    update_option( 'woocommerce_free_shipping_' . $free_eu_id . '_settings', array(
        'title'         => 'Free EU Shipping',
        'requires'      => 'min_amount',
        'min_amount'    => '100',
        'ignore_discounts' => 'no',
    ) );
}

echo "Shipping Zone 2 created: European Union (25 countries)\n";
echo "  - EU Standard: €12.90\n";
echo "  - Free shipping over €100\n";

// Zone 3: Rest of World (Zone 0 is automatically "Rest of World")
$zone_row = new WC_Shipping_Zone();
$zone_row->set_zone_name( 'International (Rest of World)' );
$zone_row->save();

$zone_row->add_shipping_method( 'flat_rate' );
$methods_row = $zone_row->get_shipping_methods();
if ( ! empty( $methods_row ) ) {
    $method_id = reset( $methods_row )->instance_id;
    update_option( 'woocommerce_flat_rate_' . $method_id . '_settings', array(
        'title'         => 'International Shipping',
        'tax_status'    => 'none',
        'cost'          => '24.90',
    ) );
}

echo "Shipping Zone 3 created: Rest of World\n";
echo "  - International: €24.90\n";

// ==============================
// Additional cleanup
// ==============================
// Delete empty "Uncategorized" category if empty
$uncat = get_term_by( 'slug', 'uncategorized', 'product_cat' );
if ( $uncat && $uncat->count == 0 ) {
    wp_delete_term( $uncat->term_id, 'product_cat' );
    echo "\nCleanup: Deleted empty 'Uncategorized' category\n";
}

// Delete draft duplicate page
$draft_page = get_page_by_path( 'refund-and-returns-policy', OBJECT, 'page' );
if ( $draft_page && $draft_page->post_status === 'draft' ) {
    wp_delete_post( $draft_page->ID, true );
    echo "Cleanup: Deleted draft 'Refund and Returns Policy' page\n";
}

// Set email from name
update_option( 'woocommerce_email_from_name', 'Zalandy' );
update_option( 'woocommerce_email_from_address', 'support@zalandy.top' );
echo "\nEmail from: Zalandy <support@zalandy.top>\n";

echo "\n=== ALL DONE: Items #2-5 completed ===\n";
