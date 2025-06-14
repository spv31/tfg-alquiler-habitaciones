<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBillShareRequest;
use App\Http\Resources\BillShareResource;
use App\Models\BillShare;
use App\Models\RentPayment;
use App\Models\UtilityBill;
use App\Services\BillShareService;
use App\Services\RentPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillShareController extends Controller
{
    public function __construct(private BillShareService $shares) {}

    public function index(UtilityBill $utilityBill)
    {
        $shares = $this->shares->listByBill($utilityBill);
        return BillShareResource::collection($shares);
    }

    public function store(UtilityBill $utilityBill, StoreBillShareRequest $request)
    {
        $share = $this->shares->create($utilityBill, $request->validated());
        return new BillShareResource($share);
    }

    public function pay(BillShare $billShare): JsonResponse
    {
        $sessionId = app(BillShareService::class)->createPaySession($billShare);

        return response()->json(['sessionId' => $sessionId]);
    }
}
