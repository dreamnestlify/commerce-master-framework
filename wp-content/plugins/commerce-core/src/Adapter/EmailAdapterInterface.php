<?php
/**
 * Email Adapter Interface — contract for email marketing / transactional email.
 *
 * Implementations: Klaviyo, Mailchimp, SendGrid, etc.
 * Phase 0: interface only.
 *
 * @package CommerceMaster\Core\Adapter
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Adapter;

interface EmailAdapterInterface {

	/**
	 * Send a templated email.
	 *
	 * @param string               $to       Recipient email.
	 * @param string               $subject  Email subject.
	 * @param string               $template Template identifier.
	 * @param array<string, mixed> $data Template data.
	 * @return bool True on success.
	 */
	public function send( string $to, string $subject, string $template, array $data = array() ): bool;

	/**
	 * Add a subscriber to a list/audience.
	 *
	 * @param string               $email Subscriber email.
	 * @param array<string, mixed> $properties Subscriber properties.
	 * @return bool True on success.
	 */
	public function add_subscriber( string $email, array $properties = array() ): bool;

	/**
	 * Remove a subscriber from a list/audience.
	 */
	public function remove_subscriber( string $email ): bool;

	/**
	 * Get the adapter's unique identifier.
	 */
	public function get_id(): string;

	/**
	 * Check if the adapter is configured.
	 */
	public function is_configured(): bool;
}
