<?php
/**
 * Market Configuration — locale, currencies, market region.
 *
 * @package CommerceMaster\Core\Config
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Config;

class MarketConfig
{
    /**
     * @var array<string, mixed>
     */
    private array $data;

    /**
     * @param array<string, mixed> $data Raw config data.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function get_default_locale(): string
    {
        return (string) ($this->data['default_locale'] ?? 'en_US');
    }

    public function get_base_currency(): string
    {
        return (string) ($this->data['base_currency'] ?? 'USD');
    }

    /**
     * @return string[]
     */
    public function get_enabled_currencies(): array
    {
        $currencies = $this->data['enabled_currencies'] ?? ['USD'];

        return array_map('strval', (array) $currencies);
    }

    public function get_default_market(): string
    {
        return (string) ($this->data['default_market'] ?? 'EU');
    }

    /**
     * Check if a currency is enabled.
     */
    public function is_currency_enabled(string $code): bool
    {
        return in_array(strtoupper($code), array_map('strtoupper', $this->get_enabled_currencies()), true);
    }
}
