<?php

namespace Database\Factories;

use App\Models\{RentPayment, Contract};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class RentPaymentFactory extends Factory
{
    protected $model = RentPayment::class;

    public function definition(): array
    {
        $start = Carbon::today()->startOfMonth();

        return [
            'contract_id' => Contract::factory(),
            'period_start' => $start,
            'period_end' => $start->copy()->endOfMonth(),
            'due_date' => $start->copy()->addDays(5),
            'amount' => 700,
            'status' => 'pending',
            'stripe_payment_intent_id' => null,
            'stripe_mandate_id' => null,
            'stripe_checkout_session_id' => null,
            'paid_at' => null,
        ];
    }
}
