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
	 * Creates a Stripe PaymentIntent for a BillShare and store the local Payment record
	 * 
	 * @param array $data
	 * @return array{client_secret: mixed, payment_id: mixed}
	 */
	public function createIntent(array $data): array
	{
		/** @var BillShare $share */
		$share = BillShare::with('tenant', 'utilityBill.property.owner')
			->findOrFail($data['bill_share_id']);

		abort_unless($share->utilityBill->owner_id === Auth::id(), 404);

		$customerId = $this->users->ensureTenantStripeCustomer($share->tenant);
		$accountId  = $this->users->ensureOwnerStripeAccount($share->utilityBill->property->owner);

		$pi = $this->stripe->paymentIntents->create([
			'amount'               => (int) round($data['amount'] * 100),
			'currency'             => 'eur',
			'customer'             => $customerId,
			'payment_method_types' => ['sepa_debit'],
			'description'          => "Utility receipt #{$share->id}",
			'on_behalf_of'         => $accountId,
		]);

		$payment = Payment::create([
			'bill_share_id'            => $share->id,
			'amount'                   => $data['amount'],
			'method'                   => 'stripe',
			'stripe_payment_intent_id' => $pi->id,
		]);

		return [
			'payment_id'    => $payment->id,
			'client_secret' => $pi->client_secret,
		];
	}

	/**
	 * Captures a previously created PaymentIntent, optionally sending a receipt
	 * 
	 * @param \App\Models\Payment $payment
	 * @param mixed $receiptEmail
	 * @return Payment
	 */
	public function captureIntent(Payment $payment, ?string $receiptEmail = null): Payment
	{
		$pi = $this->stripe->paymentIntents->retrieve($payment->stripe_payment_intent_id);
		if ($pi->status === 'requires_capture') {
			$this->stripe->paymentIntents->capture($pi->id, Arr::whereNotNull(['receipt_email' => $receiptEmail]));
		}

		return $payment->refresh();
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
