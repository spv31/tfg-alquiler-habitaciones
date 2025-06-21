<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\BillShareResource;
use App\Http\Resources\ContractResource;
use App\Http\Resources\PropertyResource;
use App\Http\Resources\RoomResource;
use App\Models\BillShare;
use App\Models\Contract;
use App\Models\Property;
use App\Models\PropertyTenant;
use App\Models\Room;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TenantController extends Controller
{
    /**
     * Returns contract assigned to tenant
     *
     * @param \Illuminate\Http\Request $request
     * @return ContractResource|mixed|\Illuminate\Http\JsonResponse
     */
    public function getCurrentContract(Request $request)
    {
        $user = $request->user();

        $contract = Contract::where('tenant_id', $user->id)
            ->whereIn('status', ['signed_by_owner', 'active', 'draft'])
            ->latest()
            ->first();

        if (!$contract) {
            return response()->json(['error_key' => 'contract_not_found'], 404);
        }

        return new ContractResource($contract);
    }

    public function getAssignedRentable(Request $request)
    {
        try {
            $user = $request->user();

            $assignment = PropertyTenant::where('tenant_id', $user->id)->first();

            if (!$assignment) {
                return response()->json([
                    'error_key' => 'no_rentable_assigned'
                ], 404);
            }

            $rentable = $assignment->rentable;

            if ($rentable instanceof Property) {
                $rentable->load('details');
                $resource = new PropertyResource($rentable);
            } else if ($rentable instanceof Room) {
                $rentable->load('property.owner');
                $resource = new RoomResource($rentable);
            } else {
                return response()->json([
                    'error_key' => 'invalid_rentable_type'
                ], 400);
            }

            return response()->json([
                'type'     => class_basename($rentable),
                'rentable' => $resource,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error_key' => 'unexpected_error',
            ]);
        }
    }

    /**
     * Returns all data related to tenant
     * 
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        $user = $request->user();

        $rentable = $user->propertyTenant?->rentable;

        $rentableData = null;

        if ($rentable instanceof Property) {
            $rentable = $rentable->load('details');
            $rentableData = [
                'type'     => 'Property',
                'rentable' => new PropertyResource($rentable),
            ];
        } elseif ($rentable instanceof Room) {
            $rentable->load('property.owner');
            $rentableData = [
                'type'     => 'Room',
                'rentable' => new RoomResource($rentable),
            ];
        }

        $contract = Contract::where('tenant_id', $user->id)
            ->whereIn('status', ['signed_by_owner', 'active', 'draft'])
            ->latest()->first();
        $contractData = $contract ? new ContractResource($contract) : null;

        $perPage = max(1, (int) $request->integer('per_page', 10));
        $sharesQuery = BillShare::with('utilityBill.property')
            ->where('tenant_id', $user->id)
            ->orderByDesc('created_at');
        $paginator = $sharesQuery->paginate($perPage);

        $sharesRes = BillShareResource::collection($paginator);

        return response()->json([
            'rentable'    => $rentableData,
            'contract'    => $contractData,
            'bill_shares' => $sharesRes->response()->getData(true),
        ]);
    }

    /**
     * Returns billShares related to tenant (paid or not)
     *
     * @param Request $request
     * @return void
     */
    public function billShares(Request $request)
    {
        $user = $request->user();

        $perPage = max(1, (int) $request->integer('per_page', 10));
        $status  = $request->string('status')->value();

        $q = BillShare::with(['utilityBill.property'])
            ->where('tenant_id', $user->id)
            ->orderByDesc('created_at');

        if ($status) {
            $q->where('status', $status);
        }

        return BillShareResource::collection(
            $q->paginate($perPage)->appends($request->query())
        );
    }

    public function previewPdf(Contract $contract) {}

    public function uploadSigned(Request $request) {}
}
