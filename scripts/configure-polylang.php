<?php
/**
 * Zalandy — Configure Polylang English/German Bilingual
 *
 * 1. Add English (en_US, default) + German (de_DE) languages.
 * 2. Assign all existing content to English.
 * 3. Create German translations for key pages.
 * 4. Configure URL settings.
 *
 * Usage:
 *   wp eval 'require "/tmp/configure-polylang.php";' --allow-root
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

WP_CLI::log( '========================================' );
WP_CLI::log( '  Zalandy — Polylang EN/DE Configuration' );
WP_CLI::log( '========================================' );

// ═══════════════════════════════════════════════════════════════
// 0. Verify Polylang is active
// ═══════════════════════════════════════════════════════════════
if ( ! function_exists( 'PLL' ) || ! PLL() ) {
	WP_CLI::error( 'Polylang is not active. Install and activate it first.' );
}

$polylang = PLL();
$model    = $polylang->model;

WP_CLI::log( '' );

// ═══════════════════════════════════════════════════════════════
// 1. Add Languages
// ═══════════════════════════════════════════════════════════════
WP_CLI::log( '1/5 — Adding languages...' );

$existing_langs = array();
foreach ( $model->get_languages_list() as $lang ) {
	$existing_langs[ $lang->slug ] = true;
	WP_CLI::log( "  ✓ Existing: {$lang->slug} ({$lang->locale})" );
}

// English (default)
if ( empty( $existing_langs['en'] ) ) {
	$result = $model->add_language( array(
		'name'       => 'English',
		'slug'       => 'en',
		'locale'     => 'en_US',
		'rtl'        => 0,
		'flag'       => 'us',
		'term_group' => 0,
		'no_default' => false,
	) );
	if ( is_wp_error( $result ) ) {
		WP_CLI::warning( "  ⚠ English: " . $result->get_error_message() );
	} else {
		WP_CLI::log( '  ✅ Added: English (en_US)' );
	}
} else {
	WP_CLI::log( '  ✓ English already exists' );
}

// German
if ( empty( $existing_langs['de'] ) ) {
	$result = $model->add_language( array(
		'name'       => 'Deutsch',
		'slug'       => 'de',
		'locale'     => 'de_DE',
		'rtl'        => 0,
		'flag'       => 'de',
		'term_group' => 1,
	) );
	if ( is_wp_error( $result ) ) {
		WP_CLI::warning( "  ⚠ German: " . $result->get_error_message() );
	} else {
		WP_CLI::log( '  ✅ Added: Deutsch (de_DE)' );
	}
} else {
	WP_CLI::log( '  ✓ German already exists' );
}

// ═══════════════════════════════════════════════════════════════
// 2. Configure Polylang Settings
// ═══════════════════════════════════════════════════════════════
WP_CLI::log( '' );
WP_CLI::log( '2/5 — Configuring Polylang settings...' );

$options = get_option( 'polylang', array() );
if ( ! is_array( $options ) ) {
	$options = array();
}

$options['default_lang']    = 'en';
$options['force_lang']      = 1;  // redirect to correct language URL
$options['rewrite']         = 0;  // 0 = directory, 1 = subdomain, 2 = domain
$options['hide_default']    = 1;  // hide /en/ prefix for default language
$options['media_support']   = 1;  // separate media by language
$options['sync']            = array( 'taxonomies', 'post_meta' );
$options['nav_menu']        = 0;
$options['default_term']    = 0;
$options['redirect_lang']   = 0;

update_option( 'polylang', $options );
WP_CLI::log( '  ✅ Settings: default=en, URL=directory, hide_default=1' );

// ═══════════════════════════════════════════════════════════════
// 3. Assign All Existing Content to English
// ═══════════════════════════════════════════════════════════════
WP_CLI::log( '' );
WP_CLI::log( '3/5 — Assigning existing content to English...' );

$assign_to_en = function( $post_ids, $type_label ) {
	$count = 0;
	foreach ( $post_ids as $pid ) {
		$current = pll_get_post_language( $pid );
		if ( ! $current ) {
			pll_set_post_language( $pid, 'en' );
			$count++;
		}
	}
	WP_CLI::log( "  ✅ {$type_label}: {$count} assigned to English" );
};

// Pages
$assign_to_en( get_posts( array(
	'post_type' => 'page', 'post_status' => 'any',
	'posts_per_page' => -1, 'fields' => 'ids',
) ), 'Pages' );

// Posts
$assign_to_en( get_posts( array(
	'post_type' => 'post', 'post_status' => 'any',
	'posts_per_page' => -1, 'fields' => 'ids',
) ), 'Posts' );

// Products
$assign_to_en( get_posts( array(
	'post_type' => 'product', 'post_status' => 'any',
	'posts_per_page' => -1, 'fields' => 'ids',
) ), 'Products' );

// Menu items
$assign_to_en( get_posts( array(
	'post_type' => 'nav_menu_item', 'post_status' => 'any',
	'posts_per_page' => -1, 'fields' => 'ids',
) ), 'Menu items' );

// Product categories
$cats = get_terms( array(
	'taxonomy' => 'product_cat', 'hide_empty' => false, 'fields' => 'ids',
) );
$cat_count = 0;
foreach ( $cats as $tid ) {
	$current = pll_get_term_language( $tid );
	if ( ! $current ) {
		pll_set_term_language( $tid, 'en' );
		$cat_count++;
	}
}
WP_CLI::log( "  ✅ Product categories: {$cat_count} assigned to English" );

// ═══════════════════════════════════════════════════════════════
// 4. Create German Translations for Key Pages
// ═══════════════════════════════════════════════════════════════
WP_CLI::log( '' );
WP_CLI::log( '4/5 — Creating German translations for key pages...' );

function _zalandy_create_de_page( $en_slug, $de_slug, $de_title, $de_content = '' ) {
	$en_page = get_page_by_path( $en_slug );
	if ( ! $en_page ) {
		WP_CLI::warning( "  ⚠ English page '{$en_slug}' not found, skipping" );
		return 0;
	}

	$en_id = $en_page->ID;

	// Check if German translation already exists
	$de_id = pll_get_post( $en_id, 'de' );
	if ( $de_id ) {
		// Update content if provided
		if ( $de_content ) {
			wp_update_post( array( 'ID' => $de_id, 'post_content' => $de_content ) );
		}
		WP_CLI::log( "  ✓ {$de_title} (de) already exists (ID: {$de_id})" );
		return $de_id;
	}

	// Create German page
	$de_id = wp_insert_post( array(
		'post_title'   => $de_title,
		'post_name'    => $de_slug,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_content' => $de_content ? $de_content : $en_page->post_content,
	) );

	if ( is_wp_error( $de_id ) || ! $de_id ) {
		WP_CLI::warning( "  ⚠ Failed to create {$de_title}" );
		return 0;
	}

	pll_set_post_language( $de_id, 'de' );
	pll_save_post_translations( array( 'en' => $en_id, 'de' => $de_id ) );

	WP_CLI::log( "  ✅ Created: {$de_title} (ID: {$de_id}) ← {$en_slug} (ID: {$en_id})" );
	return $de_id;
}

$page_translations = array(
	array( 'zalandy-home', 'startseite', 'Startseite', '' ),
	array( 'shop', 'shop-de', 'Shop', '' ),
	array( 'about-us', 'ueber-uns', 'Über Uns',
		'<h2>Über Zalandy</h2><p>Zalandy ist Ihre Anlaufstelle für handgefertigten Schmuck und zeitgenössische Mode. Mit Leidenschaft in Deutschland gefertigt.</p><p>Seniorenpflegeheim Bevern GmbH & Co. KG<br>Im Ziegelfeld 16<br>27432 Bremervörde, Deutschland<br>HRA: 204407</p>' ),
	array( 'contact', 'kontakt', 'Kontakt',
		'<h2>Kontakt</h2><p>Haben Sie Fragen? Wir helfen Ihnen gerne weiter.</p><p>E-Mail: support@zalandy.top<br>Seniorenpflegeheim Bevern GmbH & Co. KG<br>Im Ziegelfeld 16, 27432 Bremervörde</p>' ),
	array( 'size-guide', 'grosentabelle', 'Größentabelle', '' ),
	array( 'blog', 'blog-de', 'Blog', '' ),
	array( 'faq', 'faq-de', 'FAQ', '' ),
	array( 'privacy-policy', 'datenschutzerklaerung', 'Datenschutzerklärung', '' ),
	array( 'terms-of-service', 'agb', 'AGB', '' ),
	array( 'shipping-policy', 'versandbedingungen', 'Versandbedingungen', '' ),
	array( 'return-policy', 'rueckgaberecht', 'Rückgabe & Rückerstattung', '' ),
	array( 'imprint', 'impressum', 'Impressum', '' ),
	array( 'cookie-policy', 'cookie-richtlinie', 'Cookie-Richtlinie', '' ),
	array( 'withdrawal-right', 'widerrufsrecht', 'Widerrufsrecht', '' ),
);

foreach ( $page_translations as $t ) {
	_zalandy_create_de_page( $t[0], $t[1], $t[2], $t[3] );
}

// Sync homepage German content from English (blocks/shortcodes are language-agnostic)
$en_home = get_page_by_path( 'zalandy-home' );
if ( $en_home ) {
	$de_home_id = pll_get_post( $en_home->ID, 'de' );
	if ( $de_home_id ) {
		wp_update_post( array(
			'ID'           => $de_home_id,
			'post_content' => $en_home->post_content,
		) );
		WP_CLI::log( '  ✅ Homepage German content synced from English' );
	}
}

// ═══════════════════════════════════════════════════════════════
// 5. String Translations + Flush
// ═══════════════════════════════════════════════════════════════
WP_CLI::log( '' );
WP_CLI::log( '5/5 — Registering strings + flush...' );

// Register key strings for Polylang string translation panel
if ( function_exists( 'pll_register_string' ) ) {
	pll_register_string( 'Zalandy', 'site_title', 'Zalandy', 'Site Identity', false );
	pll_register_string( 'Zalandy', 'site_tagline', 'Fine Jewelry & Contemporary Fashion', 'Site Identity', false );
	pll_register_string( 'Zalandy', 'shop_button', 'Shop Collection', 'Buttons', false );
	pll_register_string( 'Zalandy', 'subscribe_button', 'Subscribe', 'Buttons', false );
	pll_register_string( 'Zalandy', 'newsletter_heading', 'Join the Zalandy Circle', 'Newsletter', false );
	WP_CLI::log( '  ✅ Registered 5 strings for translation' );
}

flush_rewrite_rules( true );
wp_cache_flush();

WP_CLI::log( '' );
WP_CLI::log( '========================================' );
WP_CLI::log( '  Polylang EN/DE Configuration Complete' );
WP_CLI::log( '========================================' );
WP_CLI::log( '  Default: English (en_US) — https://zalandy.top/' );
WP_CLI::log( '  Second:  Deutsch (de_DE) — https://zalandy.top/de/' );
WP_CLI::log( '  Language switcher: EN | DE in header menu' );
WP_CLI::log( '========================================' );
