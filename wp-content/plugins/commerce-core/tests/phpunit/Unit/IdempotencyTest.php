<?php
/**
 * Tests for Idempotency utility.
 *
 * Tests both "exists" and "not found" paths.
 *
 * @package CommerceMaster\Core\Tests\Unit
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Tests\Unit;

use PHPUnit\Framework\TestCase;
use CommerceMaster\Core\Util\Idempotency;

class IdempotencyTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		$GLOBALS['_test_options'] = array();
		$GLOBALS['_test_terms'] = array();
		$GLOBALS['_test_posts'] = array();
		$GLOBALS['_test_attributes'] = array();
		$GLOBALS['_wpdb_queries'] = array();
	}

	protected function tearDown(): void {
		$GLOBALS['_test_terms'] = array();
		$GLOBALS['_test_posts'] = array();
		$GLOBALS['_test_attributes'] = array();
		parent::tearDown();
	}

	// ── "Not found" paths ──

	public function test_find_post_by_title_returns_zero_when_not_found(): void {
		$result = Idempotency::find_post_by_title( 'Nonexistent Post' );
		$this->assertSame( 0, $result );
	}

	public function test_find_term_by_name_returns_zero_when_not_found(): void {
		$result = Idempotency::find_term_by_name( 'Nonexistent Term', 'pa_color' );
		$this->assertSame( 0, $result );
	}

	public function test_find_product_by_name_returns_zero_when_not_found(): void {
		$result = Idempotency::find_product_by_name( 'Nonexistent Product' );
		$this->assertSame( 0, $result );
	}

	public function test_find_attribute_by_slug_returns_zero_when_not_found(): void {
		$result = Idempotency::find_attribute_by_slug( 'nonexistent_attr' );
		$this->assertSame( 0, $result );
	}

	// ── "Exists" paths ──

	public function test_find_post_by_title_returns_id_when_found(): void {
		_test_add_post( 'post', 'Existing Post', 42 );
		$result = Idempotency::find_post_by_title( 'Existing Post' );
		$this->assertSame( 42, $result );
	}

	public function test_find_post_by_title_returns_id_when_found_with_custom_type(): void {
		_test_add_post( 'page', 'About Us', 99 );
		$result = Idempotency::find_post_by_title( 'About Us', 'page' );
		$this->assertSame( 99, $result );
	}

	public function test_find_post_by_title_does_not_match_different_type(): void {
		_test_add_post( 'post', 'Shared Title', 42 );
		$result = Idempotency::find_post_by_title( 'Shared Title', 'page' );
		$this->assertSame( 0, $result );
	}

	public function test_find_product_by_name_returns_id_when_found(): void {
		_test_add_post( 'product', 'Cotton T-Shirt', 101 );
		$result = Idempotency::find_product_by_name( 'Cotton T-Shirt' );
		$this->assertSame( 101, $result );
	}

	public function test_find_term_by_name_returns_id_when_found(): void {
		// Simulate a term existing.
		wp_insert_term( 'Red', 'pa_color', array( 'slug' => 'red' ) );
		$result = Idempotency::find_term_by_name( 'Red', 'pa_color' );
		$this->assertGreaterThan( 0, $result );
	}

	public function test_find_attribute_by_slug_returns_id_when_found(): void {
		_test_add_attribute( 'color', 5 );
		$result = Idempotency::find_attribute_by_slug( 'color' );
		$this->assertSame( 5, $result );
	}

	// ── Idempotency behavior ──

	public function test_repeated_find_returns_same_id(): void {
		_test_add_post( 'product', 'Denim Jacket', 200 );
		$first = Idempotency::find_product_by_name( 'Denim Jacket' );
		$second = Idempotency::find_product_by_name( 'Denim Jacket' );
		$this->assertSame( $first, $second, 'Repeated lookups should return the same ID' );
	}
}
