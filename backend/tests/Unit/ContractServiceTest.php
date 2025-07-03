<?php

namespace Tests\Unit;

use App\Models\ContractTemplate;
use App\Models\Property;
use App\Models\User;
use App\Services\ContractService;
use App\Services\PdfGeneratorService;
use App\Services\UploadFilesService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ContractServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ContractService $service;
    protected $pdfMock;

    protected function setUp(): void
    {
        parent::setUp();

        // devuelve siempre la misma ruta
        $this->pdfMock = Mockery::mock(PdfGeneratorService::class);
        $this->pdfMock->shouldReceive('generatePdfFromHtml')
            ->andReturn('contracts/dummy.pdf');

        $filesMock = Mockery::mock(UploadFilesService::class)->shouldIgnoreMissing();

        $this->service = new ContractService($this->pdfMock, $filesMock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function create_contract_generates_final_content_and_pdf_path(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $prop = Property::factory()->create(['user_id' => $owner->id]);

        $tpl = ContractTemplate::factory()->create([
            'content' => '<p>Hola <span data-token="nombre"></span></p>',
            'name' => 'Plantilla-Demo',
        ]);

        $data = [
            'contract_template_id' => $tpl->id,
            'property_id' => $prop->id,
            'tenant_id' => $tenant->id,
            'price' => 500,
            'deposit' => 1000,
            'utilities_included' => false,
            'start_date' => Carbon::now()->toDateString(),
            'token_values' => ['nombre' => 'Juan'],
        ];

        $contract = $this->service->createContract($data);

        $this->assertStringContainsString('Juan', $contract->final_content);
        $this->assertEquals('contracts/dummy.pdf', $contract->pdf_path);
        $this->assertDatabaseHas('contracts', ['id' => $contract->id]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
