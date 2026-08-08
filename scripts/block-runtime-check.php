<?php
/**
 * Block Runtime Check — verifies all block names referenced in theme
 * templates are registered in WP_Block_Type_Registry after WordPress
 * and WooCommerce have booted.
 *
 * Usage (inside wpcli container):
 *   wp eval-file /scripts/block-runtime-check.php
 *
 * Note: declare(strict_types=1) is intentionally omitted because this file
 * is executed via wp eval-file, which wraps the code in eval() where
 * strict_types has no effect and triggers a fatal error if present.
 *
 * @package CommerceMaster
 */

if (!defined('ABSPATH')) {
    exit;
}

echo "═══════════════════════════════════════════════════════════" . PHP_EOL;
echo "  Block Runtime Registration Check" . PHP_EOL;
echo "═══════════════════════════════════════════════════════════" . PHP_EOL;
echo PHP_EOL;

$theme_dir = get_stylesheetDirectory();
$scan_dirs = ['templates', 'parts', 'patterns'];

$all_blocks = [];
$errors = 0;
$checked = 0;

foreach ($scan_dirs as $dir) {
    $full_dir = $theme_dir . '/' . $dir;
    if (!is_dir($full_dir)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($full_dir, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (!in_array($file->getExtension(), ['html', 'php'], true)) {
            continue;
        }

        $rel_path = str_replace($theme_dir . '/', '', $file->getPathname());
        $content = file_get_contents($file->getPathname());
        if ($content === false) {
            continue;
        }

        // Extract all wp:namespace/block-name patterns from block comments.
        // Matches: <!-- wp:woocommerce/cart ... --> or <!-- wp:woocommerce/cart /-->
        if (preg_match_all('/wp:([a-z0-9-]+\/[a-z0-9-]+)/i', $content, $matches)) {
            foreach ($matches[1] as $block_name) {
                $key = $rel_path . ':' . $block_name;
                if (isset($all_blocks[$key])) {
                    continue; // Already checked this file:block combo.
                }
                $all_blocks[$key] = true;
                $checked++;

                if (!WP_Block_Type_Registry::get_instance()->is_registered($block_name)) {
                    echo "  ❌ UNREGISTERED: {$block_name} in {$rel_path}" . PHP_EOL;
                    $errors++;
                }
            }
        }
    }
}

echo PHP_EOL;
echo "───────────────────────────────────────────────────────────" . PHP_EOL;
echo "  Checked {$checked} block references across all templates." . PHP_EOL;

if ($errors > 0) {
    echo "  ❌ Found {$errors} unregistered block(s)." . PHP_EOL;
    echo "  These blocks are referenced in templates but not registered" . PHP_EOL;
    echo "  by WordPress core, WooCommerce, or any active plugin." . PHP_EOL;
    WP_CLI::error("Block runtime check failed: {$errors} unregistered block(s).");
} else {
    echo "  ✅ All referenced blocks are registered at runtime." . PHP_EOL;
    WP_CLI::success("Block runtime check passed: all {$checked} block(s) registered.");
}
