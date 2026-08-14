<?php
/**
 * Update company info across the whole site.
 *
 * Replaces:
 *   Equi international UG (haftungsbeschränkt) / Equi international UG
 *   Großenwede Siedlung 8, 29640 Schneverdingen
 *   HRB 206966, Amtsgericht Walsrode
 *   VAT DE312939176
 * with:
 *   Seniorenpflegeheim Bevern GmbH & Co. KG
 *   Im Ziegelfeld 16, 27432 Bremervörde
 *   HRA 204407, Amtsgericht Tostedt
 * (old VAT removed — no new VAT provided)
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_CLI' ) ) {
	exit;
}

$replacements = array(
	// Full footer block first (longest match wins before individual ones)
	'Equi international UG (haftungsbeschränkt)<br>Großenwede Siedlung 8<br>29640 Schneverdingen, Germany<br>VAT: DE312939176 | HRB: 206966' => 'Seniorenpflegeheim Bevern GmbH & Co. KG<br>Im Ziegelfeld 16<br>27432 Bremervörde, Germany<br>HRA: 204407',
	'Equi international UG (haftungsbeschränkt)<br>Großenwede Siedlung 8<br>29640 Schneverdingen, Deutschland<br>USt-IdNr: DE312939176 | HRB: 206966' => 'Seniorenpflegeheim Bevern GmbH & Co. KG<br>Im Ziegelfeld 16<br>27432 Bremervörde, Deutschland<br>HRA: 204407',
	'Equi international UG (haftungsbeschränkt)<br>Großenwede Siedlung 8<br>29640 Schneverdingen, Germany<br>VAT ID: DE312939176<br>Commercial Register: HRB 206966, Amtsgericht Walsrode' => 'Seniorenpflegeheim Bevern GmbH & Co. KG<br>Im Ziegelfeld 16<br>27432 Bremervörde, Germany<br>Commercial Register: HRA 204407, Amtsgericht Tostedt',
	'Equi international UG (haftungsbeschränkt)<br/>Großenwede Siedlung 8<br/>29640 Schneverdingen<br/>Germany<br/>VAT ID: DE312939176<br/>Commercial Register: HRB 206966, Amtsgericht Walsrode' => 'Seniorenpflegeheim Bevern GmbH & Co. KG<br/>Im Ziegelfeld 16<br/>27432 Bremervörde<br/>Germany<br/>Commercial Register: HRA 204407, Amtsgericht Tostedt',
	'Equi international UG (haftungsbeschränkt)<br />Großenwede Siedlung 8<br />29640 Schneverdingen<br />Germany<br />VAT ID: DE312939176<br />Commercial Register: HRB 206966, Amtsgericht Walsrode' => 'Seniorenpflegeheim Bevern GmbH & Co. KG<br />Im Ziegelfeld 16<br />27432 Bremervörde<br />Germany<br />Commercial Register: HRA 204407, Amtsgericht Tostedt',
	'Equi international UG<br/>Großenwede Siedlung 8<br/>29640 Schneverdingen<br/>Germany<br/><strong>VAT ID:</strong> DE312939176' => 'Seniorenpflegeheim Bevern GmbH & Co. KG<br/>Im Ziegelfeld 16<br/>27432 Bremervörde<br/>Germany<br/><strong>Commercial Register:</strong> HRA 204407',
	'Equi international UG (haftungsbeschränkt)' => 'Seniorenpflegeheim Bevern GmbH & Co. KG',
	'Equi international UG' => 'Seniorenpflegeheim Bevern GmbH & Co. KG',
	'Großenwede Siedlung 8' => 'Im Ziegelfeld 16',
	'29640 Schneverdingen' => '27432 Bremervörde',
	'Schneverdingen, Niedersachsen' => 'Bremervörde, Niedersachsen',
	'Schneverdingen' => 'Bremervörde',
	'HRB 206966' => 'HRA 204407',
	'Amtsgericht Walsrode' => 'Amtsgericht Tostedt',
	'DE312939176' => '',
	'VAT: DE312939176 | HRB: 206966' => 'HRA: 204407',
	'VAT ID: DE312939176' => '',
	'VAT ID:</strong> DE312939176' => 'Commercial Register:</strong> HRA 204407',
	'USt-IdNr: DE312939176 | HRB: 206966' => 'HRA: 204407',
	'USt-IdNr DE312939176' => 'HRA 204407',
	'VAT: DE312939176' => '',
	'DE312939176 | HRB: 206966' => 'HRA 204407',
);

/**
 * Apply a set of str_replace replacements to a string.
 */
function zalandy_apply_replacements( $content, $replacements ) {
	foreach ( $replacements as $from => $to ) {
		$content = str_replace( $from, $to, $content );
	}
	return $content;
}

echo "=== 1. WooCommerce store settings ===\n";
update_option( 'woocommerce_store_address', 'Im Ziegelfeld 16' );
update_option( 'woocommerce_store_city', 'Bremervörde' );
update_option( 'woocommerce_store_postcode', '27432' );
echo '  Store address: Im Ziegelfeld 16, 27432 Bremervörde, DE' . "\n";

echo "\n=== 2. Footer HTML option (zalandy_custom_footer) ===\n";
$footer = get_option( 'zalandy_custom_footer' );
if ( $footer ) {
	$new_footer = zalandy_apply_replacements( $footer, $replacements );
	if ( $new_footer !== $footer ) {
		update_option( 'zalandy_custom_footer', $new_footer );
		echo "  Footer updated.\n";
	} else {
		echo "  Footer unchanged (no old company info found).\n";
	}
} else {
	echo "  No footer option.\n";
}

echo "\n=== 3. Scan all posts/pages (EN + DE) ===\n";
$posts = get_posts(
	array(
		'post_type'   => array( 'post', 'page' ),
		'numberposts' => -1,
		'post_status' => 'any',
	)
);

$updated = 0;
foreach ( $posts as $post ) {
	$new_content = zalandy_apply_replacements( $post->post_content, $replacements );
	if ( $new_content !== $post->post_content ) {
		wp_update_post(
			array(
				'ID'           => $post->ID,
				'post_content' => $new_content,
			)
		);
		$updated++;
		echo "  Updated post ID {$post->ID}: {$post->post_title}\n";
	}
}
echo "  Total posts updated: {$updated}\n";

echo "\n=== 4. Check options containing company info ===\n";
$scan_options = array(
	'woocommerce_email_from_name',
	'blogname',
	'blogdescription',
	'admin_email',
);
foreach ( $scan_options as $opt ) {
	$val = get_option( $opt );
	if ( $val && strpos( $val, 'Equi' ) !== false ) {
		$new_val = zalandy_apply_replacements( $val, $replacements );
		update_option( $opt, $new_val );
		echo "  Updated option: {$opt}\n";
	}
}

echo "\nDone.\n";
