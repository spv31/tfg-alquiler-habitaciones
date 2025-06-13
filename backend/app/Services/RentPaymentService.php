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
     * Create a rent payment and (optionally) a Stripe SEPA PaymentIntent on behalf of the owner.
     * The Request layer decides if a PaymentIntent is needed (flag create_intent).
     * 
     * @param array $data
     * @param bool $createIntent
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @return RentPayment
     */
    public function create(array $data, bool $createIntent = true): RentPayment
    {
        DB::beginTransaction();
        try {
            /** @var Contract $contract */
            $contract = Contract::with('tenant')
                ->where('id', $data['contract_id'])
                ->where('status', 'active')
                ->first();

            if (!$contract || $contract->property->user_id !== Auth::id()) {
                throw new ModelNotFoundException('Contract not found or not yours');
            }

            $this->users->ensureTenantStripeCustomer(user: $contract->tenant);
            $this->users->ensureOwnerStripeAccount(user: $contract->property->owner);

            // Normalize dates if frontend omitted them (common case -> full calendar month)
            /**
             * Normalize datos of rental payments (will depend on contract dates or full callendar month (01 - 30))
             * @var mixed
             */
            $periodStart = Carbon::parse($data['period_start']);
            $periodEnd   = Carbon::parse($data['period_end']);

            /** @var RentPayment $rp */
            $rp = RentPayment::create([
                'contract_id' => $contract->id,
                'period_start' => $periodStart,
                'period_end'  => $periodEnd,
                'due_date'    => $data['due_date'],
                'amount'      => $data['amount'],
                'status'      => 'pending',
            ]);


            // Creates a PaymentIntent if tenant has IBAN and owner wants Stripe collection
            if ($createIntent) {
                $paymentMethodId = $this->ensureSepaPaymentMethod($contract);

                if ($paymentMethodId) {
                    $pi = $this->stripe->paymentIntents->create([
                        'amount'               => (int) round($rp->amount * 100),
                        'currency'             => 'eur',
                        'customer'             => $contract->tenant->stripe_customer_id,
                        'payment_method'       => $paymentMethodId,
                        'payment_method_types' => ['sepa_debit'],
                        'description'          => "Pago de alquiler #{$rp->id}",
                        'on_behalf_of'         => $contract->property->stripe_account_id,
                        'confirm'              => true,
                        'off_session'          => true,
                        'setup_future_usage'   => 'off_session',
                    ]);

                    $rp->update([
                        'stripe_payment_intent_id' => $pi->id,
                        'stripe_mandate_id'        => $pi->payment_method ? $pi->payment_method->sepa_debit->mandate : null,
                    ]);
                }
            }

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
     * Ensure the tenant has a SEPA PaymentMethod attached built from the IBAN stored
     * in the contract. Returns the PaymentMethod id ready to use in a PaymentIntent
     * 
     * @param \App\Models\Contract $contract
     * @return string|null
     */
    private function ensureSepaPaymentMethod(Contract $contract): ?string
    {
        if (!$contract->iban || !$contract->tenant->stripe_customer_id) {
            return null; // no IBAN in contract
        }

        if ($contract->stripe_payment_method_id) {
            return $contract->stripe_payment_method_id;
        }

        // creats PaymentMethod SEPA 
        $pm = $this->stripe->paymentMethods->create([
            'type'       => 'sepa_debit',
            'sepa_debit' => [
                'iban' => $contract->iban,
            ],
            'billing_details' => [
                'name'  => $contract->tenant->name,
                'email' => $contract->tenant->email,
            ],
        ]);

        // attachh to customer
        $this->stripe->paymentMethods->attach($pm->id, [
            'customer' => $contract->tenant->stripe_customer_id,
        ]);

        // persist payment method for following rental payments
        $contract->stripe_payment_method_id = $pm->id;
        $contract->save();

        return $pm->id;
    }
}