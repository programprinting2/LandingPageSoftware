<?php

namespace App\Http\Controllers;

use App\Models\PaymentOrder;
use App\Models\Transaction;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class AdminTransactionController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function index(): JsonResponse
    {
        $now = now();
        $monthStart = $now->copy()->startOfMonth();

        $transactions = Transaction::query()
            ->with([
                'client:id,company_name,user_id',
                'client.user:id,name,email',
                'application:id,name,slug',
                'package:id,name',
                'paymentOrder.affiliate.user:id,name',
            ])
            ->latest('paid_at')
            ->latest('id')
            ->get()
            ->map(fn (Transaction $transaction) => $this->formatTransaction($transaction));

        $paidTransactions = Transaction::query()->where('status', 'paid');
        $monthPaid = (clone $paidTransactions)->where('paid_at', '>=', $monthStart);

        return response()->json([
            'summary' => [
                'total_count' => (int) $paidTransactions->count(),
                'total_amount' => (float) $paidTransactions->sum('amount'),
                'month_count' => (int) $monthPaid->count(),
                'month_amount' => (float) $monthPaid->sum('amount'),
            ],
            'transactions' => $transactions,
        ]);
    }

    private function formatTransaction(Transaction $transaction): array
    {
        $paidAt = $transaction->paid_at instanceof Carbon
            ? $transaction->paid_at
            : ($transaction->paid_at ? Carbon::parse($transaction->paid_at) : null);

        $gateway = $this->resolveGatewayDetails($transaction);
        $referral = $this->resolveReferralDetails($transaction);

        return [
            'id' => $transaction->id,
            'reference' => $transaction->reference,
            'client_id' => $transaction->client_id,
            'client_name' => $transaction->client?->company_name,
            'client_email' => $transaction->client?->user?->email,
            'application_id' => $transaction->application_id,
            'application_name' => $transaction->application?->name,
            'package_name' => $transaction->package?->name,
            'type' => $transaction->type,
            'type_label' => $this->typeLabel($transaction->type),
            'description' => $transaction->description,
            'amount' => (float) $transaction->amount,
            'amount_label' => $this->formatCurrency((float) $transaction->amount),
            'payment_method' => $transaction->payment_method,
            'payment_method_label' => $this->paymentMethodLabel($transaction->payment_method),
            'status' => $transaction->status,
            'status_label' => $this->statusLabel($transaction->status),
            'paid_at' => $paidAt?->format('d M Y H:i'),
            'paid_at_date' => $paidAt?->format('d M Y'),
            'referral_code' => $referral['code'],
            'affiliate_name' => $referral['name'],
            'referral_label' => $referral['label'],
            'gateway' => $gateway,
            'gateway_summary' => $gateway['summary'] ?? [],
            'has_gateway_details' => (bool) ($gateway['has_details'] ?? false),
        ];
    }

    private function resolveReferralDetails(Transaction $transaction): array
    {
        $code = $transaction->paymentOrder?->referral_code
            ?? data_get($transaction->meta, 'referral_code');

        $name = null;
        if ($transaction->paymentOrder?->affiliate) {
            $name = $transaction->paymentOrder->affiliate->user?->name
                ?? $transaction->paymentOrder->affiliate->code;
        }

        if (! $name && $code) {
            $name = data_get($transaction->meta, 'affiliate_code');
        }

        $label = '—';
        if ($code) {
            $label = $name ? "{$code} · {$name}" : $code;
        }

        return [
            'code' => $code ?: null,
            'name' => $name ?: null,
            'label' => $label,
        ];
    }

    private function resolveGatewayDetails(Transaction $transaction): array
    {
        $stored = data_get($transaction->meta, 'gateway');
        if (is_array($stored) && ! empty($stored)) {
            return $this->presentGateway($stored);
        }

        if ($transaction->paymentOrder) {
            return $this->presentGateway(
                $this->paymentService->snapshotGatewayMeta($transaction->paymentOrder)
            );
        }

        $order = PaymentOrder::query()
            ->where('transaction_id', $transaction->id)
            ->first();

        if ($order) {
            return $this->presentGateway($this->paymentService->snapshotGatewayMeta($order));
        }

        return [
            'has_details' => false,
            'message' => 'Tidak ada data payment gateway (transaksi manual/admin).',
            'summary' => [],
        ];
    }

    private function presentGateway(array $raw): array
    {
        $fulfillmentLabel = match ($raw['fulfillment_source'] ?? '') {
            'sandbox_simulate' => 'Simulasi Sandbox (Gateway)',
            'local_simulate_legacy' => 'Simulasi Lokal (Legacy — tidak sinkron gateway)',
            'webhook_or_poll' => 'Webhook / Polling Status',
            default => '—',
        };

        $summary = [
            ['label' => 'Provider', 'value' => $raw['provider'] ?? 'Komerce'],
            ['label' => 'Environment', 'value' => strtoupper((string) ($raw['environment'] ?? '—'))],
            ['label' => 'Order SoftSale', 'value' => $raw['softsale_order_id'] ?? '—'],
            ['label' => 'Payment ID Komerce', 'value' => $raw['komerce_payment_id'] ?? '—'],
            ['label' => 'External ID', 'value' => $raw['external_id'] ?? '—'],
            ['label' => 'Status Gateway', 'value' => $raw['payment_status'] ?? '—'],
            ['label' => 'Tipe Pembayaran', 'value' => strtoupper((string) ($raw['payment_type'] ?? '—'))],
            ['label' => 'Nominal', 'value' => isset($raw['amount']) ? $this->formatCurrency((float) $raw['amount']) : '—'],
            ['label' => 'Payment URL', 'value' => $raw['payment_url'] ?? '—'],
            ['label' => 'QR String', 'value' => $raw['qr_string'] ?? '—'],
            ['label' => 'Kedaluwarsa QRIS', 'value' => $this->formatIsoLabel($raw['expired_at'] ?? null)],
            ['label' => 'Dibuat (Gateway)', 'value' => $this->formatIsoLabel($raw['gateway_created_at'] ?? null)],
            ['label' => 'Dibayar', 'value' => $this->formatIsoLabel($raw['paid_at'] ?? null)],
            ['label' => 'Sumber Konfirmasi', 'value' => $fulfillmentLabel],
            ['label' => 'Simulasi', 'value' => ! empty($raw['simulated']) ? 'Ya' : 'Tidak'],
            ['label' => 'Kode Referral', 'value' => $raw['referral_code'] ?? '—'],
            ['label' => 'Affiliate', 'value' => $raw['affiliate_name'] ?? '—'],
        ];

        return [
            'has_details' => true,
            'summary' => $summary,
            'create_response' => $raw['create_response'] ?? [],
            'fulfill_response' => $raw['fulfill_response'] ?? [],
        ];
    }

    private function formatIsoLabel(?string $value): string
    {
        if (! $value) {
            return '—';
        }

        try {
            return Carbon::parse($value)->format('d M Y H:i');
        } catch (\Throwable) {
            return $value;
        }
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'purchase' => 'Pembelian',
            'upgrade' => 'Upgrade',
            'renew' => 'Perpanjang',
            default => ucfirst($type),
        };
    }

    private function paymentMethodLabel(string $method): string
    {
        return match ($method) {
            'qris' => 'QRIS',
            'admin' => 'Admin',
            default => strtoupper($method),
        };
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'paid' => 'Paid',
            'pending' => 'Pending',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
            default => ucfirst($status),
        };
    }

    private function formatCurrency(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}
