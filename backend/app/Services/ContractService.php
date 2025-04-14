<?php

namespace App\Services;

use App\Models\Contract;
use Illuminate\Support\Facades\DB;

class ContractService
{
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
            $contract = Contract::create($validatedData);

            // Aquí podrías realizar lógica extra: 
            // - Enlazar registros secundarios, 
            // - crear notificaciones,
            // - sustituir placeholders en la plantilla y guardar "final_content", etc.

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
