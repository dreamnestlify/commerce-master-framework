<?php
/**
 * Simple PSR-4 autoloader for Commerce Core.
 *
 * This allows the plugin to work without Composer in production.
 * When Composer is available (dev), its autoloader takes precedence.
 *
 * @package CommerceMaster\Core
 */

declare(strict_types=1);

namespace CommerceMaster\Core;

class Autoload
{
    private static ?bool $registered = null;

    /**
     * Register the autoloader.
     */
    public static function register(): void
    {
        if (self::$registered !== null) {
            return;
        }

        self::$registered = true;

        spl_autoload_register([self::class, 'load']);
    }

    /**
     * Load a class file based on its fully qualified name.
     *
     * @param string $class Fully qualified class name.
     */
    public static function load(string $class): void
    {
        $prefix = 'CommerceMaster\\Core\\';

        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            return;
        }

        // Strip the prefix.
        $relative = substr($class, strlen($prefix));

        // Convert namespace separators to directory separators.
        $file = COMMERCE_CORE_DIR . 'src/' . str_replace('\\', '/', $relative) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }
}
