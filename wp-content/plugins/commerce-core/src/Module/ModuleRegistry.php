<?php
/**
 * Module Registry — discovers, registers, and boots modules.
 *
 * @package CommerceMaster\Core\Module
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Module;

class ModuleRegistry
{
    /**
     * @var array<string, ModuleInterface>
     */
    private array $modules = [];

    /**
     * Register a module.
     *
     * @param ModuleInterface $module Module instance.
     */
    public function register(ModuleInterface $module): void
    {
        $id = $module->get_id();

        if (isset($this->modules[$id])) {
            return; // Idempotent — already registered.
        }

        $this->modules[$id] = $module;
        $module->register();
    }

    /**
     * Boot all registered modules.
     */
    public function boot(): void
    {
        foreach ($this->modules as $module) {
            $module->boot();
        }
    }

    /**
     * Activate all registered modules.
     */
    public function activate(): void
    {
        foreach ($this->modules as $module) {
            $module->activate();
        }
    }

    /**
     * Get a module by ID.
     *
     * @param string $id Module ID.
     * @return ModuleInterface|null
     */
    public function get(string $id): ?ModuleInterface
    {
        return $this->modules[$id] ?? null;
    }

    /**
     * Get all registered modules.
     *
     * @return array<string, ModuleInterface>
     */
    public function all(): array
    {
        return $this->modules;
    }
}
