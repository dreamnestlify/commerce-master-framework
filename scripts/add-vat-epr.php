<?php
/**
 * Zalandy — Add new German VAT ID + EPR Packaging Registration Number.
 *
 * New data provided by site owner:
 *   VAT ID:              DE367264918
 *   EPR Packaging Reg.:  DE1649745799617
 *
 * The old EPR placeholder "DE5821461622733" is replaced everywhere.
 * Empty "VAT ID:" / "USt-IdNr:" fields are filled with the new number.
 *
 * Usage:
 *   wp eval-file /tmp/add-vat-epr.php --allow-root
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit;
}

global $wpdb;

$NEW_VAT = 'DE367264918';
$NEW_EPR = 'DE1649745799617';
$OLD_EPR = 'DE5821461622733';

/**
 * Fill empty VAT field + swap EPR number in a content string.
 */
function zalandy_add_tax_ids( $content, $vat, $epr, $old_epr ) {
	$before = $content;

	// Fill empty VAT fields (both English and German labels).
	$patterns = array(
		'/<strong>VAT ID:<\/strong> <br\/?>/i'      => '<strong>VAT ID:</strong> ' . $vat . '<br/>',
		'/<strong>VAT ID:<\/strong>\s*<br\/?>/i'    => '<strong>VAT ID:</strong> ' . $vat . '<br/>',
		'/<strong>USt-IdNr:<\/strong> <br\/?>/i'    => '<strong>USt-IdNr:</strong> ' . $vat . '<br/>',
		'/<strong>USt-IdNr:<\/strong>\s*<br\/?>/i'  => '<strong>USt-IdNr:</strong> ' . $vat . '<br/>',
		'/<strong>VAT ID:<\/strong><br\/?>/i'       => '<strong>VAT ID:</strong> ' . $vat . '<br/>',
	);

	// Replace old EPR number wherever it appears (even without label).
	$content = str_replace( $old_epr, $epr, $content );

	// Also normalize the EPR label to the standard wording.
	$content = str_ireplace(
		'EPR Packaging Register No.',
		'EPR Registration Number (LUCID)',
		$content
	);

	foreach ( $patterns as $pattern => $replacement ) {
		$content = preg_replace( $pattern, $replacement, $content );
	}

	return $content;
}

echo "=== VAT: {$NEW_VAT} | EPR: {$NEW_EPR} ===\n";

// ─────────────────────────────────────────────
// 1. Scan all posts/pages
// ─────────────────────────────────────────────
echo "\n=== 1. Posts & Pages ===\n";
$posts = get_posts(
	array(
		'post_type'   => array( 'post', 'page' ),
		'numberposts' => -1,
		'post_status' => 'any',
	)
);

$updated = 0;
foreach ( $posts as $post ) {
	$new_content = zalandy_add_tax_ids( $post->post_content, $NEW_VAT, $NEW_EPR, $OLD_EPR );
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

// ─────────────────────────────────────────────
// 2. Scan options
// ─────────────────────────────────────────────
echo "\n=== 2. Options ===\n";
$all_opts = $wpdb->get_results( "SELECT option_id, option_name, option_value FROM {$wpdb->options}" );
$opt_updated = 0;
foreach ( $all_opts as $opt ) {
	$val = maybe_unserialize( $opt->option_value );
	if ( is_string( $val ) && ( strpos( $val, $OLD_EPR ) !== false || preg_match( '/VAT ID:<\/strong>\s*(<br\/?>)?\s*$/i', $val ) ) ) {
		$new_val = zalandy_add_tax_ids( $val, $NEW_VAT, $NEW_EPR, $OLD_EPR );
		update_option( $opt->option_name, $new_val );
		$opt_updated++;
		echo "  Updated option: {$opt->option_name}\n";
	}
}
echo "  Total options updated: {$opt_updated}\n";

// ─────────────────────────────────────────────
// 3. Verify
// ─────────────────────────────────────────────
echo "\n=== 3. Verification ===\n";
$check = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_content LIKE '%DE5821461622733%'" );
echo "  Remaining old EPR refs in posts: {$check}\n";
$check2 = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_content LIKE '%DE367264918%'" );
echo "  Posts containing new VAT DE367264918: {$check2}\n";
$check3 = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_content LIKE '%DE1649745799617%'" );
echo "  Posts containing new EPR DE1649745799617: {$check3}\n";

echo "\nDone.\n";
