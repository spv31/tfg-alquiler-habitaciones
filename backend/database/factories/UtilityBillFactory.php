<?php

namespace Database\Factories;

use App\Models\{UtilityBill, Property, User};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class UtilityBillFactory extends Factory
{
    protected $model = UtilityBill::class;

    public function definition(): array
    {
        $issue = Carbon::today()->subMonth();

        return [
            'property_id' => Property::factory(),
            'room_id' => null,
            'owner_id' => fn() => Property::factory()->create()->user_id,
            'issue_date' => $issue,
            'due_date' => $issue->copy()->addDays(15),
            'period_start' => $issue->copy()->startOfMonth(),
            'period_end' => $issue->copy()->endOfMonth(),
            'total_amount' => 120,
            'category' => 'utility',
            'description' => 'Factura de luz',
            'attachment_path' => null,
            'status' => 'pending',
            'remit_to_tenants' => true,
        ];
    }
}
