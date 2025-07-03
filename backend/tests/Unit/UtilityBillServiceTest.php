<?php

namespace Tests\Unit;

use App\Models\BillShare;
use App\Models\Property;
use App\Models\User;
use App\Models\UtilityBill;
use App\Services\UtilityBillService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class UtilityBillServiceTest extends TestCase
{
    use RefreshDatabase;

    private UtilityBillService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new UtilityBillService(Mockery::mock(\App\Services\UploadFilesService::class)->shouldIgnoreMissing());

        $this->actingAs(User::factory()->create(['role' => 'owner']));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_create_sets_period_and_owner_defaults(): void
    {
        $prop = Property::factory()->create();

        $bill = $this->service->create([
            'property_id' => $prop->id,
            'total_amount' => 120,
            'issue_date' => '2025-03-10',
            'due_date' => '2025-03-25',
            'category' => 'utility',
        ]);

        $this->assertEquals('2025-03-01', $bill->period_start->format('Y-m-d'));
        $this->assertEquals('2025-03-31', $bill->period_end->format('Y-m-d'));
        $this->assertEquals('split', $bill->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_breakdown_returns_correct_owner_and_tenant_totals(): void
    {
        $prop = Property::factory()->create();
        $bill = UtilityBill::factory()->create([
            'property_id' => $prop->id,
            'owner_id' => auth()->id(),
            'total_amount' => 100,
            'status' => 'split',
        ]);

        BillShare::factory()->create(['utility_bill_id' => $bill->id, 'amount' => 30]);
        BillShare::factory()->create(['utility_bill_id' => $bill->id, 'amount' => 20]);

        $res = $this->service->breakdown($bill);

        $this->assertEquals(50.0, $res['tenant_share']);
        $this->assertEquals(50.0, $res['owner_share']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
