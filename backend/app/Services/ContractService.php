<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractTemplate;
use Illuminate\Support\Facades\DB;

class ContractService
{
    public function __construct(
        private PdfGeneratorService $pdfGenerator
    ) {}
    private function replaceTokens(string $html, array $tokens): string
    {
        foreach ($tokens as $key => $value) {
            $replacement = $value ?: '____________';
            $pattern = '/(data-token="' . preg_quote($key, '/') . '".*?>)([\s\S]*?)(<\/span>)/';
            $html = preg_replace($pattern, '$1' . $replacement . '$3', $html);
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

            $validatedData['final_content'] = $htmlFilled;        // guardamos HTML “completo”

            $pdfPath = $this->pdfGenerator
                ->generatePdfFromHtml($htmlFilled, 'contracts', 'contrato-' . ($template->name ?? ''));
            $validatedData['pdf_path'] = $pdfPath;

            $contract = Contract::create($validatedData);

            DB::commit();
            return $contract;
        } catch (\Exception $e) {
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
            $contract->update($validatedData);

            DB::commit();
            return $contract;
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
