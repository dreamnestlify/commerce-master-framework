<?php
/**
 * Payment Result — value object for payment processing results.
 *
 * @package CommerceMaster\Core\Adapter
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Adapter;

class PaymentResult {

	public function __construct(
		private bool $success,
		private string $transaction_id = '',
		private string $message = '',
		private string $status = '',
		/**
		 * @var array<string, mixed>
		 */
		private array $metadata = array()
	) {}

	public function is_success(): bool {
		return $this->success;
	}

	public function get_transaction_id(): string {
		return $this->transaction_id;
	}

	public function get_message(): string {
		return $this->message;
	}

	public function get_status(): string {
		return $this->status;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_metadata(): array {
		return $this->metadata;
	}
}
