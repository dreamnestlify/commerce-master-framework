<?php
/**
 * Tests for Idempotency utility.
 *
 * @package CommerceMaster\Core\Tests\Unit
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Tests\Unit;

use PHPUnit\Framework\TestCase;
use CommerceMaster\Core\Util\Idempotency;

class IdempotencyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['_test_options'] = [];
        $GLOBALS['_test_terms'] = [];
        $GLOBALS['_test_products'] = [];
    }

    public function test_find_post_by_title_returns_zero_when_not_found(): void
    {
        $result = Idempotency::find_post_by_title('Nonexistent Post');
        $this->assertSame(0, $result);
    }

    public function test_find_term_by_name_returns_zero_when_not_found(): void
    {
        $result = Idempotency::find_term_by_name('Nonexistent Term', 'pa_color');
        $this->assertSame(0, $result);
    }

    public function test_find_product_by_name_returns_zero_when_not_found(): void
    {
        $result = Idempotency::find_product_by_name('Nonexistent Product');
        $this->assertSame(0, $result);
    }
}
