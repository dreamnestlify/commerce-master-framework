<?php
/**
 * ERP Adapter Interface — contract for ERP/inventory integrations.
 *
 * Implementations: specific ERP systems (Phase 4).
 * Phase 0: interface only.
 *
 * @package CommerceMaster\Core\Adapter
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Adapter;

interface ErpAdapterInterface
{
    /**
     * Sync inventory levels for given product SKUs.
     *
     * @param string[] $skus Product SKUs to sync.
     * @return SyncResult Result with sync status and details.
     */
    public function sync_inventory(array $skus): SyncResult;

    /**
     * Sync an order to ERP.
     *
     * @param int $order_id WooCommerce order ID.
     * @return SyncResult Result with sync status.
     */
    public function sync_order(int $order_id): SyncResult;

    /**
     * Get real-time stock for a SKU.
     *
     * @param string $sku Product SKU.
     * @return int|null Stock level, or null if unavailable.
     */
    public function get_stock_level(string $sku): ?int;

    /**
     * Get the adapter's unique identifier.
     */
    public function get_id(): string;

    /**
     * Check if the adapter is configured.
     */
    public function is_configured(): bool;
}
