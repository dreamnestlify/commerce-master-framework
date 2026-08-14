<?php
/**
 * Zalandy — Clean up remaining old EPR references in post revisions.
 *
 * The main page content is already updated; this removes old EPR refs
 * left in post revisions (history) so a full-database search is clean.
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit;
}

global $wpdb;

$old_epr = 'DE5821461622733';

echo "=== Clean old EPR refs ({$old_epr}) ===\n";

$rows = $wpdb->get_results(
	"SELECT ID, post_title, post_type, post_status FROM {$wpdb->posts} WHERE post_content LIKE '%{$old_epr}%'"
);

echo '  Found ' . count( $rows ) . " record(s):\n";
foreach ( $rows as $r ) {
	echo "    ID {$r->ID} | {$r->post_type} | {$r->post_status} | {$r->post_title}\n";
}

$updated = 0;
foreach ( $rows as $r ) {
	$new_content = str_replace( $old_epr, 'DE1649745799617', $r->post_content );
	$wpdb->update(
		$wpdb->posts,
		array( 'post_content' => $new_content ),
		array( 'ID' => $r->ID )
	);
	$updated++;
}

$left = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_content LIKE '%{$old_epr}%'" );
echo "  Updated: {$updated}, remaining: {$left}\n";
echo "Done.\n";
