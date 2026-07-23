<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KomercePaymentService
{
    public function isConfigured(): bool
    {
        return config('komerce.enabled')
            && filled(config('komerce.api_key'))
            && filled(config('komerce.callback_api_key'));
    }

    public function callbackUrl(): string
    {
        return rtrim((string) config('app.url'), '/').config('komerce.callback_path');
    }

    public function createQrisPayment(array $payload): array
    {
        $response = Http::timeout((int) config('komerce.timeout', 30))
            ->withHeaders([
                'x-api-key' => (string) config('komerce.api_key'),
                'Accept' => 'application/json',
            ])
            ->post($this->endpoint('/api/v1/user/payment/create'), $payload);

        $json = $response->json();

        if (! $response->successful()) {
            Log::warning('Komerce create payment failed', [
                'status' => $response->status(),
                'body' => $json,
            ]);

            $message = data_get($json, 'meta.message')
                ?? data_get($json, 'message')
                ?? 'Gagal membuat pembayaran QRIS.';

            if ($response->status() === 401) {
                $message = 'API Key Komerce ditolak (401 Unauthorized). Periksa KOMERCE_API_KEY di .env — pastikan key sandbox aktif dari dashboard Komerce/RajaOngkir.';
            }

            return [
                'ok' => false,
                'message' => $message,
                'http_status' => $response->status(),
                'response' => $json,
            ];
        }

        return [
            'ok' => true,
            'data' => data_get($json, 'data', $json),
            'response' => $json,
        ];
    }

    public function getPaymentStatus(string $paymentId): array
    {
        $response = Http::timeout((int) config('komerce.timeout', 30))
            ->withHeaders([
                'x-api-key' => (string) config('komerce.api_key'),
                'Accept' => 'application/json',
            ])
            ->get($this->endpoint('/api/v1/user/payment/status/'.urlencode($paymentId)));

        $json = $response->json();

        if (! $response->successful()) {
            return [
                'ok' => false,
                'message' => data_get($json, 'meta.message') ?? 'Gagal memeriksa status pembayaran.',
                'response' => $json,
            ];
        }

        return [
            'ok' => true,
            'data' => data_get($json, 'data', $json),
            'response' => $json,
        ];
    }

    /**
     * Sandbox-only: trigger Komerce to mark a pending payment as paid.
     * Mirrors the "Simulate Payment" button on pay-sandbox.komerce.my.id.
     */
    public function simulateSandboxPayment(string $methodSlug, string $externalId, int $amount): array
    {
        if (! $this->isSandbox()) {
            return [
                'ok' => false,
                'message' => 'Simulasi gateway hanya tersedia di sandbox.',
            ];
        }

        $methodSlug = in_array($methodSlug, ['qr', 'va'], true) ? $methodSlug : 'qr';
        $url = rtrim((string) config('komerce.sandbox_base_url'), '/')
            .'/user/api/v1/user/payment/simulate/'.$methodSlug;

        $response = Http::timeout((int) config('komerce.timeout', 30))
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post($url, [
                'external_id' => $externalId,
                'amount' => $amount,
            ]);

        $json = $response->json();

        if (! $response->successful()) {
            Log::warning('Komerce simulate payment failed', [
                'status' => $response->status(),
                'body' => $json,
                'external_id' => $externalId,
            ]);

            return [
                'ok' => false,
                'message' => data_get($json, 'meta.message')
                    ?? data_get($json, 'message')
                    ?? 'Gagal mensimulasikan pembayaran di gateway.',
                'http_status' => $response->status(),
                'response' => $json,
            ];
        }

        return [
            'ok' => true,
            'data' => data_get($json, 'data', $json),
            'response' => $json,
        ];
    }

    public function isSandbox(): bool
    {
        return str_contains(strtolower((string) config('komerce.base_url')), 'sandbox');
    }

    public function sandboxMethodSlug(string $paymentType): string
    {
        return match (strtolower($paymentType)) {
            'bank_transfer', 'va' => 'va',
            default => 'qr',
        };
    }

    public function verifyCallbackSignature(string $rawBody, ?string $signature): bool
    {
        $secret = (string) config('komerce.callback_api_key');

        if ($secret === '' || $signature === null || $signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, $secret);

        return hash_equals($expected, $signature);
    }

    public function normalizePaymentStatus(?string $status): string
    {
        $value = strtoupper(trim((string) $status));

        return match ($value) {
            'PAID', 'SUCCESS', 'SUCCEEDED', 'COMPLETED' => 'paid',
            'EXPIRED' => 'expired',
            'CANCELED', 'CANCELLED' => 'canceled',
            default => 'pending',
        };
    }

    private function endpoint(string $path): string
    {
        return rtrim((string) config('komerce.base_url'), '/').$path;
    }
}
