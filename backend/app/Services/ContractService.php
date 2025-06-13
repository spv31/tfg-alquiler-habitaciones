<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractTemplate;
use Exception;
use Illuminate\Contracts\Support\ValidatedData;
use Illuminate\Support\Facades\DB;
use Storage;

class ContractService
{
    public function __construct(
        private PdfGeneratorService $pdfGenerator
    ) {}

    private function replacePreviewPdf(Contract $contract, string $newHtml, ?string $name = null)
    {
        try {
            if ($contract->pdf_path) {
                Storage::disk('private')->delete($contract->pdf_path);
            }
            $newPath = $this->pdfGenerator
                ->generatePdfFromHtml($newHtml, 'contracts', $name);

            $contract->pdf_path = $newPath;
            return $newPath;
        } catch (Exception $e) {
            throw $e;
        }
    }

   private function replaceTokens(string $html, array $tokens): string
{
    foreach ($tokens as $key => $value) {
        $replacement = $value !== null && $value !== '' ? $value : '____________';

        // admite cualquier orden de atributos dentro del <span>
        $pattern = '/(<span[^>]*\bdata-token="' . preg_quote($key, '/') . '"[^>]*>)(.*?)<\/span>/si';

        $html = preg_replace_callback(
            $pattern,
            fn ($m) => $m[1] . $replacement . '</span>',
            $html
        );
    }

    return $html;
}

    
    /**
     * It creates new contract 
     * 
     * @param array $validatedData
     * @return TModel|\Eloquent
     */
    public function createContract(array $validatedData): Contract
    {
        DB::beginTransaction();

        try {
            $template = ContractTemplate::findOrFail($validatedData['contract_template_id']);

            $htmlFilled = $this->replaceTokens(
                $template->content,
                $validatedData['token_values'] ?? []
            );

            $validatedData['final_content'] = $htmlFilled; 

            $pdfPath = $this->pdfGenerator
                ->generatePdfFromHtml($htmlFilled, 'contracts', 'contrato-' . ($template->name ?? ''));
            $validatedData['pdf_path'] = $pdfPath;

            $tokens = $validatedData['token_values'] ?? [];
            if (!empty($tokens['banco_iban'])) {
                $validatedData['banco_iban'] = $tokens['banco_iban'];
            }

            $contract = Contract::create($validatedData);

            DB::commit();
            return $contract;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * It updates a contract
     * 
     * @param \App\Models\Contract $contract
     * @param array $validatedData
     * @return Contract
     */
    public function updateContract(Contract $contract, array $validatedData): Contract
    {
        DB::beginTransaction();

        try {
			// We generate another pdf if content has changed
            if (array_key_exists('final_content', $validatedData) && $validatedData['final_content'] !== $contract->final_content) {
                $this->replacePreviewPdf($contract, $validatedData['final_content'], 'contrato-' . ($contract->contractTemplate->name ?? ''));
            }

            $contract->fill($validatedData);
            $contract->save();
            DB::commit();
            return $contract;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * It deletes a contract
     * 
     * @param \App\Models\Contract $contract
     * @return bool|null
     */
    public function deleteContract(Contract $contract): bool
    {
        DB::beginTransaction();

        try {
            $deleted = $contract->delete();

            DB::commit();
            return $deleted;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getPreviewFile(Contract $contract)
    {
        $path = $contract->pdf_path;

        if (!$path) {
            throw new Exception("No pdf_path definido para el template ID {$contract->id}");
        }

        try {
            $disk = Storage::disk('private');

            if (! $disk->exists($path)) {
                throw new Exception("Fichero de preview no encontrado en: {$path}");
            }

            $content = $disk->get($path);
            $fullPath = $disk->path($path);
            $mime = mime_content_type($fullPath);
            $name = basename($path);

            return [
                'content' => $content,
                'mime'    => $mime,
                'name'    => $name,
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }
}
