<?php
/**
 * Brand Configuration — brand name, tagline, logo.
 *
 * @package CommerceMaster\Core\Config
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Config;

class BrandConfig
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

    public function get_name(): string
    {
        return (string) ($this->data['name'] ?? 'Commerce Master');
    }

    public function get_tagline(): string
    {
        return (string) ($this->data['tagline'] ?? '');
    }

    public function get_logo_id(): int
    {
        return (int) ($this->data['logo_id'] ?? 0);
    }

    /**
     * Get logo URL (from attachment or fallback).
     */
    public function get_logo_url(int $size = 0): string
    {
        $logo_id = $this->get_logo_id();

        if ($logo_id > 0) {
            $url = wp_get_attachment_image_url($logo_id, $size > 0 ? [$size, $size] : 'full');
            if ($url) {
                return $url;
            }
        }

        return '';
    }
}
