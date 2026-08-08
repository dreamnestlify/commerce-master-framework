<?php
/**
 * Sync Result — value object for ERP sync operations.
 *
 * @package CommerceMaster\Core\Adapter
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Adapter;

class SyncResult {

	public function __construct(
		private bool $success,
		private int $records_processed = 0,
		private int $records_failed = 0,
		private string $message = '',
		/**
		 * @var array<string, mixed>
		 */
		private array $errors = array()
	) {}

	public function is_success(): bool {
		return $this->success;
	}

	public function get_records_processed(): int {
		return $this->records_processed;
	}

	public function get_records_failed(): int {
		return $this->records_failed;
	}

	public function get_message(): string {
		return $this->message;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_errors(): array {
		return $this->errors;
	}
}
