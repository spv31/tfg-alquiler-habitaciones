<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContractTemplateRequest;
use App\Http\Requests\UpdateContractTemplateRequest;
use App\Http\Resources\ContractResource;
use App\Http\Resources\ContractTemplateResource;
use App\Models\ContractTemplate;
use App\Services\ContractTemplateService;
use Auth;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ContractTemplateController extends Controller
{
    private $contractTemplateServices;

    public function __construct(ContractTemplateService $contractTemplateService)
    {
        $this->contractTemplateServices = $contractTemplateService;
    }

    /**
     * It shows all contract templates
     * 
     * @return mixed|\Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        try {
            $templates = ContractTemplate::where('is_default', true)
                ->orWhere('user_id', auth()->id())
                ->get();

            return ContractTemplateResource::collection($templates);
        } catch (Exception $e) {
            return response()->json([
                'error_key' => 'fetch_contracts_failed',
            ], 500);
        }
    }

    /**
     * Saves a new contract template
     * 
     * @param \App\Http\Requests\StoreContractTemplateRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(StoreContractTemplateRequest $request)
    {
        try {
            $template = $this->contractTemplateServices->createContractTemplate($request->validated());

            return response()->json([
                'message_key' => 'contract_template_created',
                'template' => new ContractTemplateResource($template)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'error_key' => 'create_contract_template_failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(ContractTemplate $contractTemplate)
    {
        try {
            return new ContractTemplateResource($contractTemplate);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error_key' => 'contract_template_not_found'
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'error_key' => 'unauthorized_contract_template_access'
            ], 403);
        } catch (Exception $e) {
            return response()->json([
                'error_key' => 'show_contract_template_failed'
            ], 500);
        }
    }

    public function update(UpdateContractTemplateRequest $request, ContractTemplate $contractTemplate)
    {
        try {
            $updatedTemplate = $this->contractTemplateServices->updateContractTemplate($contractTemplate, $request->validated());

            return response()->json([
                'message_key' => 'contract_template_updated',
                'template' => new ContractTemplateResource($updatedTemplate)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error_key' => 'contract_template_not_found'
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'error_key' => 'unauthorized_contract_template_update'
            ], 403);
        } catch (Exception $e) {
            return response()->json([
                'error_key' => 'update_contract_template_failed'
            ], 500);
        }
    }

    public function destroy(ContractTemplate $contractTemplate) 
    {
        try {
            $this->contractTemplateServices->deleteContractTemplate($contractTemplate);

            return response()->json([
                'message_key' => 'contract_template_deleted'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error_key' => 'contract_template_not_found'
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'error_key' => 'unauthorized_contract_template_delete'
            ], 403);
        } catch (Exception $e) {
            return response()->json([
                'error_key' => 'delete_contract_template_failed'
            ], 500);
        }
    }
}
