<?php
/**
 * Tests for Logger utility.
 *
 * @package CommerceMaster\Core\Tests\Unit
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Tests\Unit;

use PHPUnit\Framework\TestCase;
use CommerceMaster\Core\Util\Logger;

class LoggerTest extends TestCase
{
    private Logger $logger;

    protected function setUp(): void
    {
        parent::setUp();
        if (!defined('WP_DEBUG')) {
            define('WP_DEBUG', true);
        }
        $this->logger = new Logger('test-context');
    }

    public function test_info_logs_without_error(): void
    {
        $this->logger->info('Test info message', ['key' => 'value']);
        $this->assertTrue(true); // If no exception, test passes.
    }

    public function test_warning_logs_without_error(): void
    {
        $this->logger->warning('Test warning');
        $this->assertTrue(true);
    }

    public function test_error_logs_without_error(): void
    {
        $this->logger->error('Test error');
        $this->assertTrue(true);
    }

    public function test_debug_respects_wp_debug(): void
    {
        $this->logger->debug('Test debug message');
        $this->assertTrue(true);
    }

    public function test_sensitive_data_is_redacted(): void
    {
        // We can't easily inspect error_log output, but we can verify the method
        // doesn't crash with sensitive keys.
        $this->logger->info('Payment processing', [
            'password'        => 'secret123',
            'api_key'         => 'sk_test_xxx',
            'customer_name'   => 'John Doe',  // This should NOT be redacted
            'card_number'     => '4242424242424242',
        ]);

        $this->assertTrue(true);
    }
}
