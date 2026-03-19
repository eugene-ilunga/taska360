<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FreshPayService
{
    public const TEST_ENDPOINT = 'https://api.gofreshpay.com/api/v1/gateway';
    public const LIVE_ENDPOINT = 'https://paydrc.gofreshbakery.net/api/v5/';
    public const METHODS = ['orange', 'mpesa', 'airtel', 'africell'];
    public const FINAL_STATES = ['complete', 'failed'];

    public function endpoint(object $credentials): string
    {
        return $credentials->freshpay_mode === 'live' ? self::LIVE_ENDPOINT : self::TEST_ENDPOINT;
    }

    public function merchantId(object $credentials): ?string
    {
        return trim((string) ($credentials->freshpay_mode === 'live'
            ? $credentials->freshpay_merchant_id
            : $credentials->test_freshpay_merchant_id));
    }

    public function merchantSecret(object $credentials): ?string
    {
        return trim((string) ($credentials->freshpay_mode === 'live'
            ? $credentials->freshpay_merchant_secret
            : $credentials->test_freshpay_merchant_secret));
    }

    public function configuredFirstName(?object $credentials, ?string $fallback = null): string
    {
        return trim((string) (($credentials->freshpay_firstname ?? null) ?: $fallback ?: 'ZAA'));
    }

    public function configuredLastName(?object $credentials, ?string $fallback = null): string
    {
        return trim((string) (($credentials->freshpay_lastname ?? null) ?: $fallback ?: 'ZAA'));
    }

    public function configuredEmail(?object $credentials, ?string $fallback = null): string
    {
        return trim((string) (($credentials->freshpay_email ?? null) ?: $fallback ?: 'kasisrael@gmail.com'));
    }

    public function configuredUsername(?object $credentials, ?string $fallback = null): string
    {
        return trim((string) (($credentials->freshpay_username ?? null) ?: $fallback ?: 'zaa.israel191'));
    }

    public function generateReference(string $type, int $entityId): string
    {
        return sprintf('fp_%s_%d_%s', $type, $entityId, Str::upper(Str::random(12)));
    }

    public function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }

    public function isValidPhone(string $phone): bool
    {
        $normalized = $this->normalizePhone($phone);
        $length = strlen($normalized);

        return $length >= 8 && $length <= 10;
    }

    public function initiate(object $credentials, array $payload): array
    {
        return $this->request($credentials, $payload);
    }

    public function verify(object $credentials, string $reference): array
    {
        return $this->request($credentials, [
            'merchant_id' => $this->merchantId($credentials),
            'merchant_secrete' => $this->merchantSecret($credentials),
            'action' => 'verify',
            'reference' => $reference,
        ]);
    }

    public function verifySignature(?string $signature, string $encryptedPayload, ?string $key): bool
    {
        if (blank($key)) {
            return true;
        }

        if (blank($signature) || blank($encryptedPayload)) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $encryptedPayload, $key), $signature);
    }

    public function decryptPayload(string $encryptedPayload, ?string $key): array
    {
        if (blank($key)) {
            return [];
        }

        $decoded = base64_decode($encryptedPayload, true);

        if ($decoded === false) {
            return [];
        }

        $attempts = [
            ['cipher' => 'AES-256-CBC', 'key' => $key, 'iv' => substr($key, 0, 16)],
            ['cipher' => 'AES-128-CBC', 'key' => substr($key, 0, 16), 'iv' => substr($key, 0, 16)],
        ];

        foreach ($attempts as $attempt) {
            $json = openssl_decrypt($decoded, $attempt['cipher'], $attempt['key'], OPENSSL_RAW_DATA, $attempt['iv']);

            if ($json === false || $json === '') {
                continue;
            }

            $data = json_decode($json, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                return $data;
            }
        }

        return [];
    }

    public function localStatus(?string $freshPayStatus, ?string $transactionStatus = null): string
    {
        $status = Str::lower((string) $transactionStatus);
        $requestStatus = Str::lower((string) $freshPayStatus);

        if (in_array($status, ['successful', 'success', 'completed', 'complete'], true)) {
            return 'complete';
        }

        if (in_array($status, ['failed', 'error', 'cancelled', 'canceled'], true)) {
            return 'failed';
        }

        if (in_array($status, ['submitted', 'pending', 'processing', 'in_progress'], true)) {
            return 'pending';
        }

        if ($requestStatus === 'success') {
            return 'pending';
        }

        if (in_array($requestStatus, ['failed', 'error'], true)) {
            return 'failed';
        }

        return 'pending';
    }

    private function request(object $credentials, array $payload): array
    {
        $maskedPayload = $payload;

        if (isset($maskedPayload['merchant_secrete'])) {
            $maskedPayload['merchant_secrete'] = '***';
        }

        Log::info('FreshPay request', [
            'mode' => $credentials->freshpay_mode,
            'endpoint' => $this->endpoint($credentials),
            'merchant_id' => $payload['merchant_id'] ?? null,
            'action' => $payload['action'] ?? null,
            'reference' => $payload['reference'] ?? null,
            'payload' => $maskedPayload,
        ]);

        $response = Http::timeout(30)
            ->acceptJson()
            ->asJson()
            ->post($this->endpoint($credentials), $payload);

        Log::info('FreshPay response', [
            'mode' => $credentials->freshpay_mode,
            'endpoint' => $this->endpoint($credentials),
            'merchant_id' => $payload['merchant_id'] ?? null,
            'action' => $payload['action'] ?? null,
            'reference' => $payload['reference'] ?? null,
            'status_code' => $response->status(),
            'successful' => $response->successful(),
            'body' => $response->json() ?? null,
            'raw_body' => $response->body(),
        ]);

        return [
            'http_ok' => $response->successful(),
            'status_code' => $response->status(),
            'body' => $response->json() ?? [],
            'raw_body' => $response->body(),
        ];
    }
}
