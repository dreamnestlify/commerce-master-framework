<?php
/**
 * Module interface — contract for all plugin modules.
 *
 * @package CommerceMaster\Core\Module
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Module;

interface ModuleInterface
{
    /**
     * Register hooks and dependencies (runs early, before boot).
     */
    public function register(): void;

    /**
     * Boot the module (runs after all modules registered).
     */
    public function boot(): void;

    /**
     * Called on plugin activation.
     */
    public function activate(): void;

    /**
     * Module identifier (machine name).
     */
    public function get_id(): string;
}
