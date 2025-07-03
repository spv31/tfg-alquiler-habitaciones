<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use App\Services\UploadFilesService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UploadFilesServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UploadFilesService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UploadFilesService();
        Storage::fake('private');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_stores_a_base64_file_and_returns_the_path(): void
    {
        $content = 'HelloWorld';
        $base64 = base64_encode($content);

        $path = $this->service->storeFile($base64, 'test/files', 'txt');

        Storage::disk('private')->assertExists($path);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_retrieves_file_content_and_mime(): void
    {
        $content = 'SampleContent';
        $base64  = base64_encode($content);
        $path = $this->service->storeFile($base64, 'test/files', 'txt');

        $file = $this->service->getFile($path);

        $this->assertEquals($content, $file['content']);
        // $this->assertEquals('text/plain', $file['mime']);
        $this->assertStringContainsString('text/plain', $file['mime']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_deletes_a_file(): void
    {
        $base64 = base64_encode('DeleteMe');
        $path = $this->service->storeFile($base64, 'test/files', 'txt');

        $this->service->deleteFile($path);
        Storage::disk('private')->assertMissing($path);
    }
}
