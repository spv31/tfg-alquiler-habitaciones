<?php

namespace App\Services;

use Auth;
use Illuminate\Database\Eloquent\Collection;
use Stripe\StripeClient;
use App\Models\BillShare;
use App\Models\Payment;
use Arr;

class PaymentService
{
	public function __construct(
		private StripeClient $stripe,
		private UserService  $users
	) {}

	/**
	 * Returns all payments for the authenticated owner (or filtered)
	 * 
	 * @param array $filters
	 * @return Collection<int, Payment>
	 */
	public function list(array $filters = []): Collection
	{
		$q = Payment::with(['billShare.utilityBill.property', 'rentPayment.contract.property'])
			->whereHas('billShare.utilityBill.property', fn($q) => $q->where('user_id', Auth::id()))
			->orWhereHas('rentPayment.contract.property', fn($q) => $q->where('user_id', Auth::id()));

		if ($filters['property_id'] ?? null) {
			$q->whereHas('billShare.utilityBill', fn($q) => $q->where('property_id', $filters['property_id']));
		}
		if ($filters['from'] ?? null) {
			$q->whereDate('paid_at', '>=', $filters['from']);
		}
		if ($filters['to'] ?? null) {
			$q->whereDate('paid_at', '<=', $filters['to']);
		}

		return $q->latest()->get();
	}

	/**
	 * Marks a payment as paid manually and updates related BillShare
	 * 
	 * @param \App\Models\Payment $payment
	 * @return Payment
	 */
	public function markManual(Payment $payment): Payment
	{
		$payment->update([
			'method'  => 'manual_transfer',
			'paid_at' => now(),
		]);

		if ($payment->billShare) {
			$payment->billShare->update([
				'status'  => 'paid',
				'paid_at' => $payment->paid_at,
			]);
		}

		return $payment->refresh();
	}

	/**
	 * Synchronizes local Payment record when Stripe webhook notifies a succeeded intent
	 * 
	 * @param string $intentId
	 * @return void
	 */
	public function syncWithStripe(string $intentId): void
	{
		$payment = Payment::where('stripe_payment_intent_id', $intentId)->first();
		if (! $payment) {
			return;
		}

		$pi = $this->stripe->paymentIntents->retrieve($intentId);
		if ($pi->status === 'succeeded') {
			$payment->update(['paid_at' => now()]);

			if ($payment->billShare) {
				$payment->billShare->update(['status' => 'paid', 'paid_at' => now()]);
			}
			if ($payment->rentPayment) {
				app(RentPaymentService::class)->markPaid($payment->rentPayment, [
					'method'  => 'stripe',
					'paid_at' => now(),
				]);
			}
		}
	}
}
