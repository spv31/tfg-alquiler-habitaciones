<?php

namespace Database\Factories;

use App\Models\{BillShare, UtilityBill, User};
use Illuminate\Database\Eloquent\Factories\Factory;

class BillShareFactory extends Factory
{
    protected $model = BillShare::class;

    public function definition(): array
    {
        return [
            'utility_bill_id' => UtilityBill::factory(),
            'tenant_id' => User::factory(),
            'amount' => 50,
            'status' => 'pending',
            'stripe_payment_intent_id' => null,
            'stripe_mandate_id' => null,
            'stripe_checkout_session_id' => null,
            'paid_at' => null,
        ];
    }
}
