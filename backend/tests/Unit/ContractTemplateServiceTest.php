<?php

namespace Tests\Unit;

use App\Models\ContractTemplate;
use App\Services\ContractTemplateService;
use App\Services\PdfGeneratorService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ContractTemplateServiceTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_create_contract_template_generates_preview(): void
    {
        // mock PDF
        $pdfMock = Mockery::mock(PdfGeneratorService::class);
        $pdfMock->shouldReceive('generatePdfFromHtml')->once()
            ->andReturn('contract-templates/preview.pdf');

        $service = new ContractTemplateService($pdfMock);

        $tpl = $service->createContractTemplate([
            'name' => 'Mi plantilla',
            'content' => '<p>Hola</p>',
            'type' => 'full'
        ]);

        $this->assertDatabaseHas('contract_templates', [
            'id' => $tpl->id,
            'preview_path' => 'contract-templates/preview.pdf'
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
