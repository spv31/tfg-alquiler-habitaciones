<?php

namespace App\Services;

use App\Models\BillShare;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\UtilityBill;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Stripe\StripeClient;
use Auth;
use DB;
use Exception;

class BillShareService
{
	public function __construct(
		private StripeClient $stripe,
	) {}

	/**
	 * Return all shares for a given bill, ensuring ownership.
	 * 
	 * @param \App\Models\UtilityBill $bill
	 * @return Collection<int, TRelatedModel>
	 */
	public function listByBill(UtilityBill $bill): Collection
	{
		$this->assertOwner($bill);
		return $bill->billShares()->with('tenant')->get();
	}

	/**
	 * Create a new share for a bill and update bill status if fully split.
	 * 
	 * @param \App\Models\UtilityBill $bill
	 * @param array $data
	 * @throws \Exception
	 * @return BillShare
	 */
	public function create(UtilityBill $bill, array $data): BillShare
	{
		$this->assertOwner($bill);

		DB::beginTransaction();
		try {
			// Remaining amount check
			$allocated = $bill->billShares()->sum('amount');
			$remaining = $bill->total_amount - $allocated;
			if ($data['amount'] > $remaining) {
				throw new Exception('Amount exceeds remaining bill total.');
			}

			/** @var BillShare $share */
			$share = $bill->billShares()->create([
				'tenant_id'                 => $data['tenant_id'],
				'amount'                    => $data['amount'],
				'status'                    => 'pending',
				'stripe_payment_intent_id'  => $data['stripe_payment_intent_id'] ?? null,
				'stripe_mandate_id'         => $data['stripe_mandate_id'] ?? null,
			]);

			// Update bill status to split if fully allocated
			if ($share->utilityBill->billShares()->sum('amount') >= $bill->total_amount) {
				$bill->update(['status' => 'split']);
			}

			DB::commit();
			return $share->load('tenant');
		} catch (Exception $e) {
			DB::rollBack();
			throw $e;
		}
	}

	/**
	 * Automatically split a utility bill based on contracts and total_rooms.
	 * Returns the created bill shares for tenants and an optional owner share.
	 * 
	 * @param \App\Models\UtilityBill $bill
	 * @return \Illuminate\Support\Collection<TKey, TValue>
	 */
	public function autoSplit(UtilityBill $bill): Collection
	{
		$this->assertOwner($bill);

		DB::beginTransaction();
		try {
			$property   = $bill->property()->with('rooms')->first();
			$totalRooms = $property->total_rooms ?: 0;
			$baseAmount = $totalRooms > 0
				? $bill->total_amount / $totalRooms
				: 0;

			$shares = collect();

			foreach ($property->rooms as $room) {
				$contract = Contract::where('property_id', $property->id)
					->where('room_id', $room->id)
					->where('status', 'active')
					->first();

				// skip if no contract or utilities not included
				if (!$contract || !$contract->utilities_included) {
					continue;
				}

				switch ($contract->utilities_payer) {
					case 'tenant':
						$shares->push($this->create($bill, [
							'tenant_id' => $contract->tenant_id,
							'amount'    => $baseAmount,
						]));
						break;

					case 'shared':
						$pctOwner = $contract->utilities_proportion / 100;
						$tenantPart  = $baseAmount * (1 - $pctOwner);
						if ($tenantPart > 0) {
							$shares->push($this->create($bill, [
								'tenant_id' => $contract->tenant_id,
								'amount'    => $tenantPart,
							]));
						}
						break;
				}
			}

			// once all tenant shares are created, mark as split
			if ($bill->billShares()->sum('amount') >= $bill->total_amount) {
				$bill->update(['status' => 'split']);
			}

			DB::commit();
			return $shares;
		} catch (Exception $e) {
			DB::rollBack();
			throw $e;
		}
	}

	/**
	 * Sync bill payment when Stripe webhook notifies intent succeeded
	 * 
	 * @param string $intentId
	 * @return void
	 */
	public function syncWithStripe(string $intentId): void
	{
		// find the Payment record
		$payment = Payment::where('stripe_payment_intent_id', $intentId)->first();
		if (! $payment || ! $payment->billShare) {
			return;
		}

		// retrieve fresh status from Stripe
		$pi = $this->stripe->paymentIntents->retrieve($intentId);
		if ($pi->status === 'succeeded') {
			// mark both payment and the BillShare itself as paid
			$payment->update(['paid_at' => now()]);
			$payment->billShare->update([
				'status'  => 'paid',
				'paid_at' => now(),
			]);
		}
	}


	/**
	 * Ensure the authenticated owner owns the bill.
	 * 
	 * @param \App\Models\UtilityBill $bill
	 * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
	 * @return void
	 */
	private function assertOwner(UtilityBill $bill): void
	{
		if ($bill->owner_id !== Auth::id()) {
			throw new ModelNotFoundException();
		}
	}
}
