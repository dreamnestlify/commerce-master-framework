<?php
/**
 * Support Configuration — email, phone, contact info.
 *
 * @package CommerceMaster\Core\Config
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Config;

class SupportConfig {

	/**
	 * @var array<string, mixed>
	 */
	private array $data;

	/**
	 * @param array<string, mixed> $data Raw config data.
	 */
	public function __construct( array $data ) {
		$this->data = $data;
	}

	public function get_email(): string {
		return (string) ( $this->data['email'] ?? '' );
	}

	public function get_phone(): string {
		return (string) ( $this->data['phone'] ?? '' );
	}

	/**
	 * Check if support email is configured.
	 */
	public function has_email(): bool {
		$email = $this->get_email();
		return '' !== $email && is_email( $email );
	}

	/**
	 * Check if support phone is configured.
	 */
	public function has_phone(): bool {
		return '' !== $this->get_phone();
	}
}
