<?php

namespace App\Services;

use App\Models\ContractTemplate;
use DB;
use Exception;
use Log;
use Storage;

class ContractTemplateService
{
	private $pdfGenerator;

	public function __construct(PdfGeneratorService $pdfGeneratorService)
	{
		$this->pdfGenerator = $pdfGeneratorService;
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
		return DB::transaction(function () use ($template, $validatedData) {
			$template->update($validatedData);

			return $template;
		});
	}

	/**
	 * It deletes a contract template
	 * 
	 * @param \App\Models\ContractTemplate $template
	 * @return bool
	 */
	public function deleteContractTemplate(ContractTemplate $template): bool
	{
		return DB::transaction(function () use ($template) {
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
