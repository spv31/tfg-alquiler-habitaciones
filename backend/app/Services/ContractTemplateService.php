<?php

namespace App\Services;

use App\Models\ContractTemplate;
use Auth;
use DB;
use Exception;
use Log;
use Storage;

class ContractTemplateService
{
	public function __construct(
		private PdfGeneratorService $pdfGenerator
	) {}

	private function replacePreviewPdf(ContractTemplate $contractTemplate, string $newHtml, ?string $name = null)
	{
		try {
			if ($contractTemplate->preview_path) {
				Storage::disk('private')->delete($contractTemplate->preview_path);
			}
			$newPath = $this->pdfGenerator
				->generatePdfFromHtml($newHtml, 'contract-templates', $name);

			$contractTemplate->preview_path = $newPath;
			return $newPath;
		} catch (Exception $e) {
			throw $e;
		}
	}

	/**
	 * It creates new contract template
	 * 
	 * @param array $validatedData
	 * @return \App\Models\ContractTemplate
	 */
	public function createContractTemplate(array $validatedData): ContractTemplate
	{
		DB::beginTransaction();

		try {
			$validatedData['user_id'] = Auth::id();
			$template = ContractTemplate::create($validatedData);

			$pdfPath = $this->pdfGenerator->generatePdfFromHtml(
				$validatedData['content'],
				'contract-templates',
				$validatedData['name']
			);

			$template['preview_path'] = $pdfPath;
			$template->save();

			DB::commit();
			return $template;
		} catch (Exception $e) {
			DB::rollBack();
			throw $e;
		}
	}

	/**
	 * It updates a contract template
	 * 
	 * @param \App\Models\ContractTemplate $template
	 * @param array $validatedData
	 * @return \App\Models\ContractTemplate
	 */
	public function updateContractTemplate(ContractTemplate $template, array $validatedData): ContractTemplate
	{
		if ($template->is_default) {
			$validatedData['type'] = $template->type;
			$validatedData['name'] = $validatedData['name'] . ' Personalizado';
			$newTemplate = $this->createContractTemplate($validatedData);
			return $newTemplate;
		}

		DB::beginTransaction();

		try {
			// We generate another pdf if content has changed
			if (array_key_exists('content', $validatedData) && $validatedData['content'] !== $template->content) {
				$this->replacePreviewPdf($template, $validatedData['content'], $validatedData['name'] ?? $template->name);
			}

			$template->fill($validatedData);
			$template->save();
			DB::commit();
			return $template->fresh();
		} catch (Exception $e) {
			DB::rollBack();
			throw $e;
		}
	}

	/**
	 * It deletes a contract template
	 * 
	 * @param \App\Models\ContractTemplate $template
	 * @return bool
	 */
	public function deleteContractTemplate(ContractTemplate $template): bool
	{
		if ($template->is_default) {
			throw new Exception("A default contract template cannot be deleted");
		}

		return DB::transaction(function () use ($template): bool|null {
			if ($template->preview_path) {
				Storage::disk('private')->delete($template->preview_path);
			}

			$deleted = $template->delete();

			return $deleted;
		});
	}

	public function getPreviewFile(ContractTemplate $contractTemplate)
	{
		$path = $contractTemplate->preview_path;

		if (! $path) {
			throw new Exception("No preview_path definido para el template ID {$contractTemplate->id}");
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
