<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContractResource;
use App\Http\Resources\PropertyResource;
use App\Http\Resources\RoomResource;
use App\Models\Contract;
use App\Models\Property;
use App\Models\PropertyTenant;
use App\Models\Room;
use Exception;
use Illuminate\Http\Request;

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
                $resource = new PropertyResource($rentable);
            } else if ($rentable instanceof Room) {
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
            $rentableData = [
                'type'     => 'Property',
                'rentable' => new PropertyResource($rentable),
            ];
        } elseif ($rentable instanceof Room) {
            $rentableData = [
                'type'     => 'Room',
                'rentable' => new RoomResource($rentable),
            ];
        }

        $contract = Contract::where('tenant_id', $user->id)
            ->whereIn('status', ['signed_by_owner', 'active', 'draft'])
            ->latest()->first();

        return response()->json([
            'rentable' => $rentableData,
            'contract' => $contract ? new ContractResource($contract) : null,
        ]);
    }

    public function previewPdf(Contract $contract) {}

    public function uploadSigned(Request $request) {}
}
