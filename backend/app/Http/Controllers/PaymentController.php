<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePaymentIntentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Services\BillShareService;
use App\Services\PaymentService;
use App\Services\RentPaymentService;
use Exception;
use Illuminate\Http\Request;
use Stripe\Webhook;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    /**
     * Returns all payments (or filtered)
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        return PaymentResource::collection(
            $this->paymentService->list($request->only(['property_id', 'from', 'to', 'status']))
        );
    }

    /**
     * Shows a payment
     * 
     * @param \App\Models\Payment $payment
     * @return PaymentResource
     */
    public function show(Payment $payment)
    {
        return new PaymentResource($payment->load('billShare', 'rentPayment'));
    }

    /**
     * Generates a new PaymentIntent 
     * 
     * @param \App\Http\Requests\CreatePaymentIntentRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function createStripeIntent(CreatePaymentIntentRequest $request)
    {
        $dto = $this->paymentService->createIntent($request->validated());
        return response()->json($dto, 201);
    }

    /**
     * Summary of capture
     * 
     * @param \App\Models\Payment $payment
     * @param \App\Http\Requests\CreatePaymentIntentRequest $request
     * @return PaymentResource
     */
    public function capture(Payment $payment, CreatePaymentIntentRequest $request)
    {
        $updated = $this->paymentService->captureIntent($payment, $request->input('receipt_email'));
        return new PaymentResource($updated);
    }

    /**
     * Manual payments
     * 
     * @param \App\Models\Payment $payment
     * @param \App\Http\Requests\CreatePaymentIntentRequest $request
     * @return PaymentResource
     */
    public function markManual(Payment $payment, CreatePaymentIntentRequest $request)
    {
        $updated = $this->paymentService->markManual($payment);
        return new PaymentResource($updated);
    }

    /**
     * Webhook for PaymentIntents
     * 
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function handleStripeWebhook(Request $request)
    {
        $payload   = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (Exception $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Intents which arrived to succeed or processing
        if (in_array($event->type, ['payment_intent.succeeded', 'payment_intent.processing'])) {
            $intentId = $event->data->object->id;

            // monthly rent 
            app(RentPaymentService::class)->syncWithStripe($intentId);

            // utilities bills
            app(BillShareService::class)->syncWithStripe($intentId);

            // 
            $this->paymentService->syncWithStripe($intentId);
        }

        return response()->json(['received' => true]);
    }
}
