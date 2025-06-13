<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRentPaymentRequest;
use App\Http\Requests\UpdateRentPaymentRequest;
use App\Http\Resources\RentPaymentResource;
use App\Models\BillShare;
use App\Models\RentPayment;
use App\Services\RentPaymentService;
use Illuminate\Http\Request;

class RentPaymentController extends Controller
{
    public function __construct(private RentPaymentService $rentPaymentService) {}

    public function index(Request $request)
    {
        $filters = $request->only(['property_id', 'room_id', 'tenant_id', 'status', 'from', 'to']);
        return RentPaymentResource::collection($this->rentPaymentService->list($filters));
    }

    public function show(RentPayment $rentPayment)
    {
        return new RentPaymentResource($rentPayment->load('payments', 'contract.room'));
    }

    public function store(StoreRentPaymentRequest $request)
    {
        $rentPayment = $this->rentPaymentService->create($request->validated());   // internamente crea PaymentIntent si procede
        return new RentPaymentResource($rentPayment);
    }

    public function update(RentPayment $rentPayment, UpdateRentPaymentRequest $request)
    {
        $updated = $this->rentPaymentService->markPaid($rentPayment, $request->validated());
        return new RentPaymentResource($updated);
    }
}
