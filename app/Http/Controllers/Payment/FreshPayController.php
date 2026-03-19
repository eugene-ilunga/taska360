<?php

namespace App\Http\Controllers\Payment;

use App\Helper\Reply;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\GlobalSetting;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Services\FreshPayService;
use App\Traits\MakeOrderInvoiceTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FreshPayController extends Controller
{
    use MakeOrderInvoiceTrait;

    public function __construct(private FreshPayService $freshPayService)
    {
        parent::__construct();
        $this->pageTitle = 'FreshPay';
    }

    public function paymentWithFreshPayPublic(Request $request, int $id, string $companyHash): array
    {
        $request->validate([
            'type' => 'required|in:invoice,order',
            'operator' => 'required|in:' . implode(',', FreshPayService::METHODS),
            'phone' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (! $this->freshPayService->isValidPhone($value)) {
                        $fail(__('modules.freshpay.invalidPhone'));
                    }
                },
            ],
        ]);

        $company = Company::where('hash', $companyHash)->firstOrFail();
        $credentials = $company->paymentGatewayCredentials;

        if (! $credentials || $credentials->freshpay_status !== 'active') {
            return Reply::error(__('modules.freshpay.gatewayInactive'));
        }

        if (blank($this->freshPayService->merchantId($credentials)) || blank($this->freshPayService->merchantSecret($credentials))) {
            return Reply::error(__('modules.freshpay.missingCredentials'));
        }

        [$invoice, $order, $amount, $currency] = $this->resolvePaymentContext($request->type, $id);
        $reference = $this->freshPayService->generateReference($request->type, $id);
        $customer = $this->resolveCustomerData($company->paymentGatewayCredentials);

        $result = $this->freshPayService->initiate($credentials, [
            'merchant_id' => $this->freshPayService->merchantId($credentials),
            'merchant_secrete' => $this->freshPayService->merchantSecret($credentials),
            'amount' => (string) $amount,
            'currency' => $currency,
            'action' => 'debit',
            'customer_number' => $this->freshPayService->normalizePhone($request->phone),
            'firstname' => $customer['firstname'],
            'lastname' => $customer['lastname'],
            'email' => $customer['email'],
            'reference' => $reference,
            'method' => $request->operator,
            'callback_url' => route('freshpay.webhook', [$companyHash]),
        ]);

        $response = $result['body'];
        $localStatus = (! $result['http_ok'] || ($response['Status'] ?? null) !== 'Success')
            ? 'failed'
            : $this->freshPayService->localStatus($response['Status'] ?? null, $response['Trans_Status'] ?? null);

        $payment = $this->storePayment(
            invoice: $invoice,
            order: $order,
            amount: $amount,
            gatewayStatus: $localStatus,
            transactionId: $response['Transaction_id'] ?? $reference,
            reference: $reference,
            gatewayResponse: $response,
            remark: $localStatus === 'pending' ? __('modules.freshpay.pendingState') : ($response['Comment'] ?? null),
        );

        if (! $result['http_ok'] || ($response['Status'] ?? null) !== 'Success') {
            Log::warning('FreshPay initiate failed', [
                'company_id' => $company->id,
                'type' => $request->type,
                'reference' => $reference,
                'payment_id' => $payment->id,
                'response' => $response,
            ]);

            return Reply::error($response['Comment'] ?? __('messages.paymentFailed'));
        }

        return Reply::successWithData(__('modules.freshpay.requestSubmitted'), [
            'payment_id' => $payment->id,
            'poll_url' => route('freshpay.payment_status', [$payment->id, $companyHash]),
            'waiting_url' => route('freshpay.waiting', [$payment->id, $companyHash]),
            'redirect_url' => $invoice
                ? url()->temporarySignedRoute('front.invoice', now()->addDays(GlobalSetting::SIGNED_ROUTE_EXPIRY), $invoice->hash)
                : route('orders.show', $order?->id),
            'reference' => $reference,
            'state_text' => __('modules.freshpay.pendingState'),
            'debug' => [
                'stage' => 'initiate',
                'mode' => $credentials->freshpay_mode,
                'endpoint' => $this->freshPayService->endpoint($credentials),
                'merchant_id' => $this->freshPayService->merchantId($credentials),
                'callback_processed' => false,
                'gateway_status' => $response['Status'] ?? null,
                'transaction_status' => $response['Trans_Status'] ?? null,
                'comment' => $response['Comment'] ?? null,
            ],
        ]);
    }

    public function handleGatewayWebhook(Request $request, string $companyHash): JsonResponse
    {
        $company = Company::where('hash', $companyHash)->firstOrFail();
        $credentials = $company->paymentGatewayCredentials;
        $encryptedPayload = (string) $request->input('data', '');

        Log::info('FreshPay webhook received', [
            'company_id' => $company->id,
            'signature_present' => filled($request->header('X-Signature')),
            'payload_length' => strlen($encryptedPayload),
        ]);

        if (! $this->freshPayService->verifySignature($request->header('X-Signature'), $encryptedPayload, $credentials->freshpay_hmac_key)) {
            Log::warning('FreshPay webhook invalid signature', ['company_id' => $company->id]);
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $payload = $this->freshPayService->decryptPayload($encryptedPayload, $credentials->freshpay_encryption_key);

        if (empty($payload)) {
            Log::warning('FreshPay webhook invalid encryption', ['company_id' => $company->id]);
            return response()->json(['message' => 'Invalid encryption'], 400);
        }

        Log::info('FreshPay webhook decrypted', [
            'company_id' => $company->id,
            'payload' => $payload,
        ]);

        $reference = $payload['Reference'] ?? $payload['reference'] ?? $payload['Transaction_id'] ?? $payload['transaction_id'] ?? null;

        if (blank($reference)) {
            return response()->json(['message' => 'Reference not found'], 400);
        }

        $payment = Payment::where('company_id', $company->id)
            ->where(function ($query) use ($reference, $payload) {
                $query->where('event_id', $reference)
                    ->orWhere('transaction_id', $reference);

                if (! empty($payload['Transaction_id'])) {
                    $query->orWhere('transaction_id', $payload['Transaction_id']);
                }
            })
            ->latest()
            ->first();

        if (! $payment) {
            Log::warning('FreshPay webhook payment not found', [
                'company_id' => $company->id,
                'reference' => $reference,
                'payload' => $payload,
            ]);
            return response()->json(['message' => 'Payment not found'], 404);
        }

        $verifyReference = $payment->event_id ?: $payment->transaction_id;
        $verification = $verifyReference ? $this->freshPayService->verify($credentials, $verifyReference) : ['body' => []];
        $resolvedPayload = ! empty($verification['body']) ? $verification['body'] : $payload;

        $this->syncPaymentOutcome($payment, $resolvedPayload);

        Log::info('FreshPay webhook synced payment', [
            'company_id' => $company->id,
            'payment_id' => $payment->id,
            'reference' => $payment->event_id,
            'transaction_id' => $payment->transaction_id,
            'status' => $payment->status,
        ]);

        return response()->json([
            'status' => 'Callback received successfully',
            'data' => $resolvedPayload,
        ]);
    }

    public function paymentStatus(int $paymentId, string $companyHash): JsonResponse
    {
        $payment = Payment::where('id', $paymentId)
            ->whereHas('company', fn ($query) => $query->where('hash', $companyHash))
            ->firstOrFail();

        if (! in_array($payment->status, FreshPayService::FINAL_STATES, true)) {
            $credentials = $payment->company?->paymentGatewayCredentials;
            $verifyReference = $payment->event_id ?: $payment->transaction_id;

            if ($credentials && filled($verifyReference)) {
                $verification = $this->freshPayService->verify($credentials, $verifyReference);
                $verifyBody = $verification['body'] ?? [];

                if (! empty($verifyBody)) {
                    $this->syncPaymentOutcome($payment, $verifyBody);
                    $payment->refresh();
                }
            }
        }

        $redirectUrl = $payment->invoice
            ? url()->temporarySignedRoute('front.invoice', now()->addDays(GlobalSetting::SIGNED_ROUTE_EXPIRY), $payment->invoice->hash)
            : route('orders.show', $payment->order_id);

        return response()->json([
            'status' => 'success',
            'payment_status' => $payment->status,
            'message' => $payment->status === 'complete'
                ? __('messages.paymentSuccessful')
                : ($payment->status === 'failed' ? __('messages.paymentFailed') : __('modules.freshpay.pendingState')),
            'is_final' => in_array($payment->status, FreshPayService::FINAL_STATES, true),
            'source' => in_array($payment->status, FreshPayService::FINAL_STATES, true) ? 'verify_or_callback' : 'polling',
            'callback_processed' => in_array($payment->status, FreshPayService::FINAL_STATES, true),
            'reference' => $payment->event_id,
            'transaction_id' => $payment->transaction_id,
            'redirect_url' => $redirectUrl,
        ]);
    }

    public function waitingPage(int $paymentId, string $companyHash)
    {
        $payment = Payment::where('id', $paymentId)
            ->whereHas('company', fn ($query) => $query->where('hash', $companyHash))
            ->firstOrFail();

        $this->company = $payment->company;
        $this->payment = $payment;
        $this->pollUrl = route('freshpay.payment_status', [$payment->id, $companyHash]);
        $this->returnUrl = $payment->invoice
            ? url()->temporarySignedRoute('front.invoice', now()->addDays(GlobalSetting::SIGNED_ROUTE_EXPIRY), $payment->invoice->hash)
            : route('orders.show', $payment->order_id);

        return view('public-payment.freshpay.waiting', $this->data);
    }

    public function getWebhook(): JsonResponse
    {
        return response()->json(['message' => 'This URL should not be accessed directly. Only POST requests are allowed.']);
    }

    private function resolvePaymentContext(string $type, int $id): array
    {
        if ($type === 'order') {
            $order = Order::findOrFail($id);
            $invoice = $this->makeOrderInvoice($order, 'pending');

            return [$invoice, $order, $order->total, $order->currency?->currency_code ?: 'CDF'];
        }

        $invoice = Invoice::findOrFail($id);

        return [$invoice, null, $invoice->amountDue(), $invoice->currency?->currency_code ?: 'CDF'];
    }

    private function resolveCustomerData($credentials): array
    {
        return [
            'firstname' => $this->freshPayService->configuredFirstName($credentials, 'ZAA'),
            'lastname' => $this->freshPayService->configuredLastName($credentials, 'ZAA'),
            'email' => $this->freshPayService->configuredEmail($credentials, 'kasisrael@gmail.com'),
            'username' => $this->freshPayService->configuredUsername($credentials, 'zaa.israel191'),
        ];
    }

    private function storePayment(Invoice $invoice, ?Order $order, float|int $amount, string $gatewayStatus, string $transactionId, string $reference, array $gatewayResponse, ?string $remark = null): Payment
    {
        $payment = Payment::where(function ($query) use ($transactionId, $reference) {
            $query->where('transaction_id', $transactionId)
                ->orWhere('event_id', $reference);
        })->latest()->first() ?: new Payment();

        $payment->project_id = $invoice->project_id;
        $payment->invoice_id = $invoice->id;
        $payment->order_id = $order?->id ?: $invoice->order_id;
        $payment->gateway = 'FreshPay';
        $payment->transaction_id = $transactionId;
        $payment->event_id = $reference;
        $payment->payload_id = $reference;
        $payment->currency_id = $invoice->currency_id;
        $payment->amount = $amount;
        $payment->paid_on = now();
        $payment->status = $gatewayStatus;
        $payment->remarks = $gatewayStatus === 'pending'
            ? __('modules.freshpay.pendingState')
            : ($remark ?: __('messages.paymentFailed'));
        $payment->payment_gateway_response = $gatewayResponse;
        $payment->save();

        return $payment;
    }

    private function syncPaymentOutcome(Payment $payment, array $gatewayResponse): void
    {
        $transactionStatus = $gatewayResponse['Trans_Status']
            ?? $gatewayResponse['trans_status']
            ?? $gatewayResponse['Status']
            ?? $gatewayResponse['status']
            ?? null;

        $payment->status = $this->freshPayService->localStatus($gatewayResponse['Status'] ?? null, $transactionStatus);
        $payment->transaction_id = $gatewayResponse['Transaction_id'] ?? $gatewayResponse['transaction_id'] ?? $payment->transaction_id;
        $payment->remarks = $payment->status === 'complete'
            ? __('messages.paymentSuccessful')
            : ($payment->status === 'failed'
                ? ($gatewayResponse['Trans_Status_Description'] ?? $gatewayResponse['Comment'] ?? __('messages.paymentFailed'))
                : __('modules.freshpay.pendingState'));
        $payment->payment_gateway_response = $gatewayResponse;
        $payment->save();

        if (! $payment->invoice) {
            return;
        }

        if ($payment->status === 'complete') {
            $payment->invoice->status = 'paid';
            $payment->invoice->save();

            if ($payment->order) {
                $payment->order->status = 'completed';
                $payment->order->save();
            }

            return;
        }

        $payment->invoice->status = 'unpaid';
        $payment->invoice->save();

        if ($payment->order) {
            $payment->order->status = $payment->status === 'failed' ? 'failed' : 'pending';
            $payment->order->save();
        }
    }
}
