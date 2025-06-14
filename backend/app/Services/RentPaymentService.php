<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Payment;
use App\Models\RentPayment;
use Arr;
use Auth;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Stripe\StripeClient;


class RentPaymentService
{
    public function __construct(
        private StripeClient $stripe,
        private UserService $users
    ) {}

    /**
     * List rent payments with optional filters (owner scoped).
     * 
     * @param array $filters
     * @return Collection<int, RentPayment>
     */
    public function list(array $filters = []): Collection
    {
        $q = RentPayment::query()
            ->with(['contract.property', 'payments'])
            ->whereHas('contract.property', fn($q) => $q->where('user_id', Auth::id()));

        $q->when($filters['property_id'] ?? null, callback: fn($q, $v) => $q->whereHas('contract', fn($q) => $q->where('property_id', $v)));
        $q->when($filters['room_id'] ?? null, callback: fn($q, $v) => $q->whereHas('contract', fn($q) => $q->where('room_id', $v)));
        $q->when($filters['tenant_id'] ?? null, callback: fn($q, $v) => $q->whereHas('contract', fn($q) => $q->where('tenant_id', $v)));
        $q->when($filters['status'] ?? null, callback: fn($q, $v) => $q->where('status', $v));
        $q->when($filters['from'] ?? null, callback: fn($q, $v) => $q->whereDate('due_date', '>=', $v));
        $q->when($filters['to'] ?? null, callback: fn($q, $v) => $q->whereDate('due_date', '<=', $v));

        return $q->orderByDesc('due_date')->get();
    }

    /**
     * Creates a rent payment (pending)
     * 
     * @param array $data
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @return RentPayment
     */
    public function create(array $data): RentPayment
    {
        DB::beginTransaction();
        try {
            $contract = Contract::with('tenant')
                ->where('id', $data['contract_id'])
                ->where('status', 'active')
                ->first();

            if (!$contract || $contract->property->user_id !== Auth::id()) {
                throw new ModelNotFoundException('Contract not found or not yours');
            }

            $rp = RentPayment::create([
                'contract_id'  => $contract->id,
                'period_start' => Carbon::parse($data['period_start']),
                'period_end'   => Carbon::parse($data['period_end']),
                'due_date'     => $data['due_date'],
                'amount'       => $data['amount'],
                'status'       => 'pending',
            ]);

            DB::commit();
            return $rp->load('contract.tenant');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mark a payment as paid with(manual confirmation or after Stripe succeeds).
     * 
     * @param \App\Models\RentPayment $rp
     * @param array $attributes
     * @return RentPayment
     */
    public function markPaid(RentPayment $rp, array $attributes = []): RentPayment
    {
        if ($rp->status === 'paid') {
            return $rp; // rent is already paid
        }

        DB::transaction(function () use ($rp, $attributes) {
            // Updates rent payment status amd timestamps
            $rp->update([
                'status'   => 'paid',
                'paid_at'  => $attributes['paid_at'] ?? Carbon::now(),
            ] + Arr::only($attributes, ['stripe_payment_intent_id', 'stripe_mandate_id']));

            // creates corresponding Payment record if not exists
            $rp->payments()->create(attributes: [
                'amount'                   => $rp->amount,
                'method'                   => $attributes['method'] ?? 'manual',
                'stripe_payment_intent_id' => $rp->stripe_payment_intent_id ?? $attributes['stripe_payment_intent_id'] ?? null,
                'paid_at'                  => $rp->paid_at,
            ]);
        });

        return $rp->refresh()->load('payments');
    }

    /**
     * Sync rent payment when Stripe webhook notifies intent succeeded
     * 
     * @param string $intentId
     * @return void
     */
    public function syncWithStripe(string $intentId): void
    {
        // Retrieve intent from Stripe
        $pi = $this->stripe->paymentIntents->retrieve($intentId, []);
        if ($pi->status !== 'succeeded') {
            return; // nothing to do yet
        }

        /** @var RentPayment|null $rp */
        $rp = RentPayment::where('stripe_payment_intent_id', $intentId)->first();
        if ($rp) {
            $this->markPaid($rp, [
                'method'  => 'stripe',
                'paid_at' => Carbon::createFromTimestamp($pi->created),
            ]);
        }
    }

    /**
     * Return aggregates for dashboard: total paid & pending in range.
     * 
     * @param \Carbon\Carbon $from
     * @param \Carbon\Carbon $to
     * @return array
     */
    public function breakdownForOwner(Carbon $from, Carbon $to): array
    {
        $paid = RentPayment::whereHas('contract.property', fn($q) => $q->where('user_id', Auth::id()))
            ->where('status', 'paid')
            ->whereBetween('due_date', [$from, $to])
            ->sum('amount');

        $pending = RentPayment::whereHas('contract.property', fn($q) => $q->where('user_id', Auth::id()))
            ->where('status', 'pending')
            ->whereBetween('due_date', [$from, $to])
            ->sum('amount');

        return compact('paid', 'pending');
    }

    /**
     * Creates a Checkout Session to pay rent
     *
     * @param RentPayment $rp
     * @return string
     */
    public function createPaySession(RentPayment $rp): string
    {
        $contract   = $rp->contract;
        $customerId = $this->users->ensureTenantStripeCustomer($contract->tenant);
        $accountId  = $this->users->ensureOwnerStripeAccount($contract->property->owner);

        $session = $this->stripe->checkout->sessions->create([
            'mode'                 => 'payment',                      
            'customer'             => $customerId,
            'payment_method_types' => ['sepa_debit'],
            'line_items'           => [[
                'price_data' => [
                    'currency'     => 'eur',
                    'product_data' => ['name' => "Alquiler {$rp->period_start->format('F Y')}"],
                    'unit_amount'  => (int) round($rp->amount * 100),
                ],
                'quantity' => 1,
            ]],
            'on_behalf_of'         => $accountId,
            'payment_intent_data'  => [
                'metadata' => ['rent_payment_id' => $rp->id],
            ],
            'success_url'          => config('app.frontend_url') . '/pagos/exito',
            'cancel_url'           => config('app.frontend_url') . '/pagos/cancelado',
        ]);

        $rp->update(['stripe_checkout_session_id' => $session->id]);

        return $session->id;
    }
}
