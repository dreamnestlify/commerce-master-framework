<?php
/**
 * Idempotency Helper — for safe-repeating operations.
 *
 * Used by: demo data import, ERP sync, batch operations.
 * Prevents creating duplicate content when scripts re-run.
 *
 * @package CommerceMaster\Core\Util
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Util;

class Idempotency {

	/**
	 * Find a post by title and type (avoids duplicates).
	 *
	 * @param string $title   Post title.
	 * @param string $type    Post type.
	 * @return int Post ID if found, 0 if not.
	 */
	public static function find_post_by_title( string $title, string $type = 'post' ): int {
		global $wpdb;

		$like = '%' . $wpdb->esc_like( $title ) . '%';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = %s AND post_status IN ('publish', 'draft', 'private') LIMIT 1",
				$title,
				$type
			)
		);

		return (int) ( $id ?? 0 );
	}

	/**
	 * Find a WooCommerce product by name.
	 *
	 * @param string $name Product name/title.
	 * @return int Product ID if found, 0 if not.
	 */
	public static function find_product_by_name( string $name ): int {
		return self::find_post_by_title( $name, 'product' );
	}

	/**
	 * Find a term by name and taxonomy.
	 *
	 * @param string $name     Term name.
	 * @param string $taxonomy Taxonomy name.
	 * @return int Term ID if found, 0 if not.
	 */
	public static function find_term_by_name( string $name, string $taxonomy ): int {
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'name'       => $name,
				'hide_empty' => false,
				'number'     => 1,
			)
		);

		if ( ! is_array( $terms ) || empty( $terms ) ) {
			return 0;
		}

		return (int) $terms[0]->term_id;
	}

	/**
	 * Find an attribute by slug.
	 *
	 * @param string $slug Attribute slug (e.g., "pa_color").
	 * @return int Attribute ID if found, 0 if not.
	 */
	public static function find_attribute_by_slug( string $slug ): int {
		global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT attribute_id FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = %s LIMIT 1",
				$slug
			)
		);

		return (int) ( $id ?? 0 );
	}
}
