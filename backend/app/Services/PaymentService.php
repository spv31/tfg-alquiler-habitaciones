<?php

namespace App\Services;

use App\Models\BillShare;
use App\Models\Payment;
use App\Models\RentPayment;
use Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

class PaymentService
{
	public function __construct(
		private StripeClient $stripe,
		private UserService $users
	) {}

	/**
	 * Retrieves all payments
	 *
	 * @param array $filters
	 * @return Collection
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
	 * Manual Payment
	 *
	 * @param Payment $payment
	 * @return Payment
	 */
	public function markManual(Payment $payment): Payment
	{
		return DB::transaction(function () use ($payment) {
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
		});
	}

	/**
	 * Webhook Stripe to update payments, billshares or rentpayments
	 *
	 * @param string $intentId
	 * @return void
	 */
	public function syncWithStripe(string $intentId): void
	{
		DB::transaction(function () use ($intentId) {
			$payment = Payment::where('stripe_payment_intent_id', $intentId)->first();
			if (! $payment) {
				return;
			}

			$pi = $this->stripe->paymentIntents->retrieve($intentId);
			if ($pi->status !== 'succeeded') {
				return;
			}

			if (! $payment->paid_at) {
				$payment->update(['paid_at' => now()]);
			}

			if ($payment->billShare && $payment->billShare->status !== 'paid') {
				$payment->billShare->update(['status' => 'paid', 'paid_at' => $payment->paid_at]);
			}

			if ($payment->rentPayment) {
				app(RentPaymentService::class)->markPaid($payment->rentPayment, [
					'method'  => 'stripe',
					'paid_at' => $payment->paid_at,
				]);
			}
		});
	}

	/**
	 * Creates Payment after checkout
	 *
	 * @param Session $session
	 * @return void
	 */
	public function createFromCheckoutSession(Session $session): void
	{
		DB::transaction(function () use ($session) {
			$billShare = BillShare::where('stripe_checkout_session_id', $session->id)->first();
			if ($billShare) {
				$exists = Payment::where('stripe_payment_intent_id', $session->payment_intent)->exists();
				if (! $exists) {
					$payment = Payment::create([
						'bill_share_id'            => $billShare->id,
						'amount'                   => $billShare->amount,
						'method'                   => 'stripe',
						'stripe_payment_intent_id' => $session->payment_intent,
						'paid_at'                  => now(),
					]);

					$billShare->update([
						'status'  => 'paid',
						'paid_at' => $payment->paid_at,
					]);

					$utilityBill = $billShare->utilityBill;
					if ($utilityBill) {
						$allPaid = $utilityBill->billShares()->where('status', '!=', 'paid')->doesntExist();
						if ($allPaid && $utilityBill->status !== 'settled') {
							$utilityBill->update(['status' => 'settled']);
						}
					}
				}

				return; 
			}

			$rentPayment = RentPayment::where('stripe_checkout_session_id', $session->id)->first();
			if ($rentPayment && ! $rentPayment->stripe_payment_intent_id) {
				$rentPayment->update(['stripe_payment_intent_id' => $session->payment_intent]);
			}
		});
	}
}
