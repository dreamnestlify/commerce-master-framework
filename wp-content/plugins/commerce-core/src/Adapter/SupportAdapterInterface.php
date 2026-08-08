<?php
/**
 * Support Adapter Interface — contract for live chat / customer support.
 *
 * Implementations: Intercom, Zendesk, Tawk.to, etc.
 * Phase 0: interface only.
 *
 * @package CommerceMaster\Core\Adapter
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Adapter;

interface SupportAdapterInterface
{
    /**
     * Inject the support widget into the frontend.
     */
    public function inject_widget(): void;

    /**
     * Get the adapter configuration for frontend.
     *
     * @return array<string, mixed>
     */
    public function get_config(): array;

    /**
     * Get the adapter's unique identifier.
     */
    public function get_id(): string;

    /**
     * Check if the adapter is configured.
     */
    public function is_configured(): bool;
}
