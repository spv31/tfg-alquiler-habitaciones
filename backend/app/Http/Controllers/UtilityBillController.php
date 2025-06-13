<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUtilityBillRequest;
use App\Http\Requests\UpdateUtilityBillRequest;
use App\Http\Resources\UtilityBillResource;
use App\Models\UtilityBill;
use App\Services\UtilityBillService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UtilityBillController extends Controller
{
    public function __construct(private UtilityBillService $bills) {}

    public function index(Request $request)
    {
        $filters = $request->only(['property_id', 'room_id', 'status', 'from', 'to']);

        $bills = $this->bills->list($filters);

        return UtilityBillResource::collection($bills);
    }


    public function store(StoreUtilityBillRequest $request)
    {
        try {
            $bill = $this->bills->create($request->validated());
            return response()->json($bill, 201);
        } catch (Exception $e) {
            return $this->error($e);
        }
    }

    public function show(UtilityBill $utilityBill): JsonResponse|UtilityBillResource
    {
        try {
            return new UtilityBillResource($this->bills->find($utilityBill->id));
        } catch (Exception $e) {
            return $this->error($e);
        }
    }

    public function update(UtilityBill $utilityBill, UpdateUtilityBillRequest $request)
    {
        try {
            $bill = $this->bills->update($utilityBill, $request->validated());
            return new UtilityBillResource($bill);
        } catch (Exception $e) {
            return $this->error($e);
        }
    }

    public function destroy(UtilityBill $utilityBill)
    {
        try {
            $this->bills->delete($utilityBill);
            return response()->json(['message' => 'Deleted'], 204);
        } catch (Exception $e) {
            return $this->error($e);
        }
    }

    private function error(Exception $e): JsonResponse
    {
        report($e);
        return response()->json([
            'error_key' => 'utility_bill_error',
            'message'   => $e->getMessage()
        ], 500);
    }
}
