<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\UpdateContractRequest;
use App\Http\Resources\ContractResource;
use App\Models\Contract;
use App\Services\ContractService;
use App\Services\PdfGeneratorService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Storage;

class ContractController extends Controller
{
    private $contractServices;
    private $pdfGenerator;

    public function __construct(ContractService $contractServices, PdfGeneratorService $pdfGeneratorService)
    {
        $this->contractServices = $contractServices;
        $this->pdfGenerator = $pdfGeneratorService;
    }

    /**
     * Muestra un listado de contratos.
     */
    public function index()
    {
        try {
            $user = auth()->user();

            $contracts = Contract::query()
                ->where('tenant_id', $user->id)
                ->orWhereHas('property', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->orWhereHas('room.property', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->get();

            return ContractResource::collection($contracts);
        } catch (Exception $e) {
            return response()->json([
                'error_key' => 'fetch_contracts_failed',
            ], 500);
        }
    }

    /**
     * It creates a new contract
     * 
     * @param \App\Http\Requests\StoreContractRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(StoreContractRequest $request)
    {
        try {
            $contract = $this->contractServices->createContract($request->validated());

            return response()->json([
                'message_key' => 'contract_created',
                'contract' => new ContractResource($contract)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'error_key' => 'create_contract_failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * It shows specific contract
     * 
     * @param \App\Models\Contract $contract
     * @return ContractResource|mixed|\Illuminate\Http\JsonResponse
     */
    public function show(Contract $contract)
    {
        try {
            return new ContractResource($contract);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error_key' => 'contract_not_found'
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'error_key' => 'unauthorized_contract_access'
            ], 403);
        } catch (Exception $e) {
            return response()->json([
                'error_key' => 'show_contract_failed'
            ], 500);
        }
    }

    /**
     * Updates an existent contract.
     * 
     * @param \App\Http\Requests\UpdateContractRequest $request
     * @param \App\Models\Contract $contract
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateContractRequest $request, Contract $contract)
    {
        try {
            $updatedContract = $this->contractServices->updateContract($contract, $request->validated());

            return response()->json([
                'message_key' => 'contract_updated',
                'contract' => new ContractResource($updatedContract)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error_key' => 'contract_not_found'
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'error_key' => 'unauthorized_contract_update'
            ], 403);
        } catch (Exception $e) {
            return response()->json([
                'error_key' => 'update_contract_failed'
            ], 500);
        }
    }

    /**
     * Deletes a contract
     * 
     * @param \App\Models\Contract $contract
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy(Contract $contract)
    {
        try {
            $this->contractServices->deleteContract($contract);

            return response()->json([
                'message_key' => 'contract_deleted'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error_key' => 'contract_not_found'
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'error_key' => 'unauthorized_contract_delete'
            ], 403);
        } catch (Exception $e) {
            return response()->json([
                'error_key' => 'delete_contract_failed'
            ], 500);
        }
    }

    public function previewPdf(Contract $contract)
    {
        try {
            $html = $contract->final_content;

            $name = 'contract_' . $contract->id;

            $path = $this->pdfGenerator->generatePdfFromHtml(
                $html,
                'contracts',   
                $name
            );

            $disk    = Storage::disk('private');
            $content = $disk->get($path);
            $full    = $disk->path($path);
            $mime    = mime_content_type($full);
            $fileName = basename($path);

            return response($content, 200, [
                'Content-Type'        => $mime,
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error_key' => 'preview_contract_failed',
                'message'   => $e->getMessage(),
            ], 500);
        }
    }
}
