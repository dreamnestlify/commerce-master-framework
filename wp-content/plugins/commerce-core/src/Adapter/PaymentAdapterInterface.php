<?php
/**
 * Payment Adapter Interface — contract for payment integrations.
 *
 * Implementations: Stripe, PayPal, Adyen, etc.
 * Phase 0: interface only, no implementation.
 *
 * @package CommerceMaster\Core\Adapter
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Adapter;

interface PaymentAdapterInterface
{
    /**
     * Process a payment for an order.
     *
     * @param int   $order_id     WooCommerce order ID.
     * @param array<string, mixed> $payment_data Payment method data (token, card, etc.).
     * @return PaymentResult Result object with success/failure + transaction details.
     */
    public function process_payment(int $order_id, array $payment_data): PaymentResult;

    /**
     * Process a refund for an order.
     *
     * @param int    $order_id WooCommerce order ID.
     * @param float  $amount   Refund amount (in order currency).
     * @param string $reason   Optional refund reason.
     * @return RefundResult Result object.
     */
    public function process_refund(int $order_id, float $amount, string $reason = ''): RefundResult;

    /**
     * Get the adapter's unique identifier.
     */
    public function get_id(): string;

    /**
     * Get the adapter's display name.
     */
    public function get_name(): string;

    /**
     * Check if the adapter is configured (credentials present).
     */
    public function is_configured(): bool;

    /**
     * Get supported currencies.
     *
     * @return string[] ISO 4217 currency codes.
     */
    public function get_supported_currencies(): array;

    /**
     * Check if the adapter supports a currency.
     */
    public function supports_currency(string $currency_code): bool;
}
