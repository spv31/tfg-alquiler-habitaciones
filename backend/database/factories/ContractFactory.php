<?php

namespace Database\Factories;

use App\Models\{Contract, Property, User, ContractTemplate};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractFactory extends Factory
{
    protected $model = Contract::class;

    public function definition(): array
    {
        $start = Carbon::today()->startOfMonth();

        return [
            'contract_template_id' => ContractTemplate::factory(),
            'property_id' => Property::factory(),
            'room_id' => null,
            'tenant_id' => User::factory(),
            'type' => 'default',
            'price' => 700,
            'deposit' => 700,
            'utilities_included' => false,
            'utilities_payer' => 'tenant',
            'utilities_proportion'=> null,
            'start_date' => $start,
            'end_date' => $start->copy()->addYear(),
            'extension_date' => null,
            'status' => 'active',
            'pdf_path' => null,
            'pdf_path_signed_owner'  => null,
            'pdf_path_signed_tenant' => null,
            'final_content' => null,
            'token_values' => null,
            'owner_iban' => null,
            'tenant_iban' => null,
            'stripe_payment_method_id' => null,
            'signed_by_owner_at' => null,
            'signed_by_tenant_at' => null,
        ];
    }
}
