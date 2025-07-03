<?php

namespace Database\Factories;

use App\Models\{Payment, BillShare, RentPayment};
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'bill_share_id' => BillShare::factory(),
            'rent_payment_id' => null,
            'amount' => $this->faker->numberBetween(50, 800),
            'method' => 'stripe',           
            'stripe_payment_intent_id'=> null,
            'paid_at' => now(),
        ];
    }
}
