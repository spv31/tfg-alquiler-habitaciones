<?php

namespace App\Services;

use App\Models\BillShare;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\UtilityBill;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class BillShareService
{
	public function __construct(private StripeClient $stripe) {}

	/**
	 * Fetch BillShares
	 *
	 * @param UtilityBill $bill
	 * @return Collection
	 */
	public function listByBill(UtilityBill $bill): Collection
	{
		$this->assertOwner($bill);
		return $bill->billShares()->with('tenant')->get();
	}

	/**
	 * Billshare create
	 *
	 * @param UtilityBill $bill
	 * @param array $data
	 * @return BillShare
	 */
	public function create(UtilityBill $bill, array $data): BillShare
	{
		$this->assertOwner($bill);

		return DB::transaction(function () use ($bill, $data) {
			$allocated = $bill->billShares()->sum('amount');
			$remaining = $bill->total_amount - $allocated;

			if ($data['amount'] > $remaining) {
				throw new Exception('Amount exceeds remaining bill total.');
			}

			$share = $bill->billShares()->create([
				'tenant_id'                => $data['tenant_id'],
				'amount'                   => $data['amount'],
				'status'                   => 'pending',
				'stripe_payment_intent_id' => $data['stripe_payment_intent_id'] ?? null,
				'stripe_mandate_id'        => $data['stripe_mandate_id'] ?? null,
			]);

			if ($share->utilityBill->billShares()->sum('amount') >= $bill->total_amount) {
				$bill->update(['status' => 'split']);
			}

			return $share->load('tenant');
		});
	}

	/**
	 * Split UtilityBill in BillShares
	 *
	 * @param UtilityBill $bill
	 * @return Collection
	 */
	public function autoSplit(UtilityBill $bill): Collection
	{
		$this->assertOwner($bill);

		return DB::transaction(function () use ($bill) {
			$property   = $bill->property()->with('rooms')->first();
			$totalRooms = $property->total_rooms ?: 0;
			$baseAmount = $totalRooms > 0 ? $bill->total_amount / $totalRooms : 0;

			$shares = collect();

			$full = Contract::where('property_id', $property->id)
				->whereNull('room_id')
				->where('status', 'active')
				->first();

			if ($full && ! $full->utilities_included) {
				if ($full->utilities_payer === 'tenant') {
					$shares->push($this->create($bill, [
						'tenant_id' => $full->tenant_id,
						'amount'    => $bill->total_amount,
					]));
				}

				if ($full->utilities_payer === 'shared') {
					$pctOwner   = $full->utilities_proportion / 100;
					$tenantPart = $bill->total_amount * (1 - $pctOwner);
					if ($tenantPart > 0) {
						$shares->push($this->create($bill, [
							'tenant_id' => $full->tenant_id,
							'amount'    => $tenantPart,
						]));
					}
				}
			}

			foreach ($property->rooms as $room) {
				$contract = Contract::where('property_id', $property->id)
					->where('room_id', $room->id)
					->where('status', 'active')
					->first();

				if (! $contract || $contract->utilities_included) {
					continue;
				}

				if ($contract->utilities_payer === 'tenant') {
					$shares->push($this->create($bill, [
						'tenant_id' => $contract->tenant_id,
						'amount'    => $baseAmount,
					]));
				}

				if ($contract->utilities_payer === 'shared') {
					$pctOwner   = $contract->utilities_proportion / 100;
					$tenantPart = $baseAmount * (1 - $pctOwner);
					if ($tenantPart > 0) {
						$shares->push($this->create($bill, [
							'tenant_id' => $contract->tenant_id,
							'amount'    => $tenantPart,
						]));
					}
				}
			}

			if ($bill->billShares()->sum('amount') >= $bill->total_amount) {
				$bill->update(['status' => 'split']);
			}

			return $bill->billShares()->get();
		});
	}

	/**
	 * Webhook
	 *
	 * @param string $intentId
	 * @return void
	 */
	public function syncWithStripe(string $intentId): void
	{
		DB::transaction(function () use ($intentId) {
			$payment = Payment::where('stripe_payment_intent_id', $intentId)->first();
			if (! $payment || ! $payment->billShare) {
				return;
			}

			try {
				$pi = $this->stripe->paymentIntents->retrieve($intentId);
			} catch (ApiErrorException) {
				return;
			}

			if ($pi->status !== 'succeeded') {
				return;
			}

			if (! $payment->paid_at) {
				$payment->update(['paid_at' => now()]);
			}

			if ($payment->billShare->status !== 'paid') {
				$payment->billShare->update([
					'status'  => 'paid',
					'paid_at' => $payment->paid_at,
				]);
			}

			$utilityBill = $payment->billShare->utilityBill;
			if ($utilityBill) {
				$allPaid = $utilityBill->billShares()->where('status', '!=', 'paid')->doesntExist();
				if ($allPaid && $utilityBill->status !== 'settled') {
					$utilityBill->update(['status' => 'settled']);
				}
			}
		});
	}

	/**
	 * Creates checkout session stripe
	 *
	 * @param BillShare $share
	 * @return string
	 */
	public function createPaySession(BillShare $share): string
	{
		$contract   = $share->tenant->contracts()
			->where('property_id', $share->utilityBill->property_id)
			->where('status', 'active')
			->firstOrFail();

		$customerId = app(UserService::class)->ensureTenantStripeCustomer($share->tenant);
		$accountId  = app(UserService::class)->ensureOwnerStripeAccount($share->utilityBill->property->owner);

		$bill        = $share->utilityBill;
		$description = $bill->description ?: 'Suministro';
		$category    = match ($bill->category) {
			'utility' => 'Suministro',
			'general' => 'Gasto General',
			'tax'     => 'Impuesto/Tasa',
			default   => ucfirst($bill->category),
		};
		$period = "{$bill->period_start->format('d/m/Y')} - {$bill->period_end->format('d/m/Y')}";

		$locale  = app()->getLocale();
		$session = $this->stripe->checkout->sessions->create([
			'mode'                 => 'payment',
			'customer'             => $customerId,
			'payment_method_types' => ['sepa_debit'],
			'payment_intent_data'  => [
				'on_behalf_of'  => $accountId,
				'transfer_data' => ['destination' => $accountId],
				'metadata'      => ['bill_share_id' => $share->id],
				'description'   => "{$category}: {$description} ({$period})",
			],
			'line_items' => [[
				'price_data' => [
					'currency'     => 'eur',
					'product_data' => ['name' => "{$category}: {$description} ({$period})"],
					'unit_amount'  => (int) round($share->amount * 100),
				],
				'quantity' => 1,
			]],
			'success_url' => config('app.frontend_url')."/{$locale}/tenant/dashboard?msg=payment_success",
			'cancel_url'  => config('app.frontend_url')."/{$locale}/tenant/dashboard?msg=payment_cancelled",
		]);

		$share->update(['stripe_checkout_session_id' => $session->id]);

		return $session->url;
	}

	/**
	 * Checks owner is user authenticated
	 *
	 * @param UtilityBill $bill
	 * @return void
	 */
	private function assertOwner(UtilityBill $bill): void
	{
		if ($bill->owner_id !== Auth::id()) {
			throw new ModelNotFoundException();
		}
	}
}
