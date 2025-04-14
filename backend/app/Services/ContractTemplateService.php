<?php

namespace App\Services;

use App\Models\ContractTemplate;
use DB;

class ContractTemplateService
{
	/**
	 * It creates new contract template
	 * 
	 * @param array $validatedData
	 * @return \App\Models\ContractTemplate
	 */
	public function createContractTemplate(array $validatedData): ContractTemplate
	{
		return DB::transaction(function () use ($validatedData) {
			$template = ContractTemplate::create($validatedData);

			return $template; 
		});
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
}
