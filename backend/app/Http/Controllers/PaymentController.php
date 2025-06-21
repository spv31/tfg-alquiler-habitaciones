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
use Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

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
     * @param Request $request
     * @return void
     */
    public function handleStripeWebhook(Request $request)
    {
        $payload   = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (UnexpectedValueException $e) {
            Log::warning('Stripe webhook – payload inválido');
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook – firma inválida');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        Log::debug('Stripe webhook recibido', ['type' => $event->type]);

        switch ($event->type) {

            // 1️⃣ El usuario terminó Checkout → creamos Payment “pendiente”
            case 'checkout.session.completed':
            case 'checkout.session.async_payment_succeeded':
                /** @var \Stripe\Checkout\Session $session */
                $session = $event->data->object;
                $this->paymentService->createFromCheckoutSession($session);
                break;

            // 2️⃣ El dinero se ha cobrado → marcamos como pagado
            case 'payment_intent.succeeded':
                $intentId = $event->data->object->id;
                $this->paymentService->syncWithStripe($intentId);
                app(BillShareService::class)->syncWithStripe($intentId);
                app(RentPaymentService::class)->syncWithStripe($intentId);
                break;

            // (opcional) si quieres ver intent.processing para debug
            default:
                // Ignorar otros eventos
        }

        return response()->json(['received' => true]);
    }
}
