<?php
/**
 * Zalandy — Replace all contact info site-wide (for Mollie review).
 *
 * New contact details (provided by site owner):
 *   Email: indiagianina5@gmail.com  (replaces support@ / privacy@ / returns@zalandy.top)
 *   Phone: +1 929 568 3010          (replaces +1 706 215 4022)
 *
 * Usage:
 *   wp eval-file /tmp/update-contact-info.php --allow-root
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit;
}

global $wpdb;

$NEW_EMAIL = 'indiagianina5@gmail.com';
$NEW_PHONE = '+1 929 568 3010';

$replacements = array(
	// Emails (all aliases → one new address)
	'support@zalandy.top'  => $NEW_EMAIL,
	'privacy@zalandy.top'  => $NEW_EMAIL,
	'returns@zalandy.top'  => $NEW_EMAIL,
	// Phones (variants)
	'+1 706 215 4022'      => $NEW_PHONE,
	'+1 706 215 4022'      => $NEW_PHONE,
	'+17062154022'         => '+19295683010',
	'706 215 4022'         => '929 568 3010',
	'706-215-4022'         => '929-568-3010',
);

function zci_replace( $content, $replacements ) {
	foreach ( $replacements as $from => $to ) {
		$content = str_replace( $from, $to, $content );
	}
	return $content;
}

echo "=== Contact info update (email → {$NEW_EMAIL}, phone → {$NEW_PHONE}) ===\n";

// ── 1. Posts & pages (incl. revisions) ──────────
echo "\n=== 1. Posts & Pages ===\n";
$rows = $wpdb->get_results( "SELECT ID, post_title, post_content FROM {$wpdb->posts} WHERE post_content LIKE '%zalandy.top%' OR post_content LIKE '%706 215 4022%' OR post_content LIKE '%7062154022%'" );
$updated = 0;
foreach ( $rows as $r ) {
	$new_content = zci_replace( $r->post_content, $replacements );
	if ( $new_content !== $r->post_content ) {
		$wpdb->update( $wpdb->posts, array( 'post_content' => $new_content ), array( 'ID' => $r->ID ) );
		$updated++;
		echo "  Updated post ID {$r->ID}: {$r->post_title}\n";
	}
}
echo "  Total posts updated: {$updated}\n";

// ── 2. Options ──────────────────────────────────
echo "\n=== 2. Options ===\n";
$opts = $wpdb->get_results( "SELECT option_id, option_name, option_value FROM {$wpdb->options} WHERE option_value LIKE '%support@zalandy.top%' OR option_value LIKE '%privacy@zalandy.top%' OR option_value LIKE '%returns@zalandy.top%' OR option_value LIKE '%706 215 4022%' OR option_value LIKE '%7062154022%'" );
$opt_updated = 0;
foreach ( $opts as $o ) {
	$raw = $o->option_value;
	$new = zci_replace( $raw, $replacements );
	if ( $new !== $raw ) {
		update_option( $o->option_name, maybe_unserialize( $new ) );
		$opt_updated++;
		echo "  Updated option: {$o->option_name}\n";
	}
}
echo "  Total options updated: {$opt_updated}\n";

// ── 3. Key settings explicitly ───────────────────
echo "\n=== 3. Explicit settings ===\n";
update_option( 'woocommerce_email_from_address', $NEW_EMAIL );
update_option( 'admin_email', $NEW_EMAIL );
echo "  woocommerce_email_from_address → {$NEW_EMAIL}\n";
echo "  admin_email → {$NEW_EMAIL}\n";

// ── 4. Postmeta scan ────────────────────────────
echo "\n=== 4. Postmeta ===\n";
$meta_rows = $wpdb->get_results( "SELECT meta_id, post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_value LIKE '%@zalandy.top%' OR meta_value LIKE '%706 215 4022%'" );
$meta_updated = 0;
foreach ( $meta_rows as $m ) {
	$new = zci_replace( $m->meta_value, $replacements );
	if ( $new !== $m->meta_value ) {
		update_metadata_by_mid( 'post', $m->meta_id, $new );
		$meta_updated++;
		echo "  Updated meta {$m->meta_key} (post {$m->post_id})\n";
	}
}
echo "  Total meta updated: {$meta_updated}\n";

// ── 5. Verify ───────────────────────────────────
echo "\n=== 5. Verification ===\n";
$left_posts = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_content LIKE '%support@zalandy.top%' OR post_content LIKE '%privacy@zalandy.top%' OR post_content LIKE '%returns@zalandy.top%' OR post_content LIKE '%706 215 4022%'" );
echo "  Old contacts remaining in posts: {$left_posts}\n";
$new_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_content LIKE '%indiagianina5@gmail.com%'" );
echo "  Posts containing new email: {$new_count}\n";

echo "\nDone.\n";
