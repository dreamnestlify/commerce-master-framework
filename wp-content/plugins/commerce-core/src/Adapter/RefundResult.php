<?php
/**
 * Refund Result — value object for refund processing results.
 *
 * @package CommerceMaster\Core\Adapter
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Adapter;

class RefundResult {

	public function __construct(
		private bool $success,
		private string $refund_id = '',
		private string $message = '',
		private float $refunded_amount = 0.0
	) {}

	public function is_success(): bool {
		return $this->success;
	}

	public function get_refund_id(): string {
		return $this->refund_id;
	}

	public function get_message(): string {
		return $this->message;
	}

	public function get_refunded_amount(): float {
		return $this->refunded_amount;
	}
}
