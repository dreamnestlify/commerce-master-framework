<?php
/**
 * Analytics Adapter Interface — contract for analytics/tracking.
 *
 * Implementations: GA4, Meta Pixel, TikTok Pixel, Google Ads.
 * Phase 0: interface only.
 *
 * @package CommerceMaster\Core\Adapter
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Adapter;

interface AnalyticsAdapterInterface {

	/**
	 * Track an event.
	 *
	 * @param string               $event_name Event name (e.g., "purchase", "add_to_cart").
	 * @param array<string, mixed> $params Event parameters.
	 * @return void
	 */
	public function track_event( string $event_name, array $params = array() ): void;

	/**
	 * Inject tracking scripts into the frontend.
	 */
	public function inject_tracking(): void;

	/**
	 * Get the adapter's unique identifier.
	 */
	public function get_id(): string;

	/**
	 * Check if the adapter is configured.
	 */
	public function is_configured(): bool;
}
