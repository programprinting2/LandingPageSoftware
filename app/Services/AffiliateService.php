<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\PaymentOrder;
use App\Models\User;
use Illuminate\Support\Str;

class AffiliateService
{
    public function isAffiliatePortalActive(User $user): bool
    {
        $user->loadMissing('affiliate');

        $affiliate = $user->affiliate;

        return $affiliate !== null && (string) $affiliate->status === 'active';
    }

    public function referralUrlForCode(string $code): string
    {
        $base = rtrim((string) config('softsale.referral_base_url'), '/');

        return $base.'/?ref='.Str::upper(trim($code));
    }

    public function affiliateSummaryPayload(User $user): ?array
    {
        $user->loadMissing('affiliate');

        $affiliate = $user->affiliate;

        if (! $affiliate) {
            return null;
        }

        return [
            'id' => $affiliate->id,
            'code' => $affiliate->code,
            'status' => $affiliate->status,
            'active' => (string) $affiliate->status === 'active',
            'referral_url' => $this->referralUrlForCode($affiliate->code),
            'sales_count' => (int) $affiliate->sales_count,
            'total_commission' => (float) $affiliate->total_commission,
            'pending_commission' => (float) $affiliate->pending_commission,
            'total_commission_label' => $this->formatCurrency((float) $affiliate->total_commission),
            'pending_commission_label' => $this->formatCurrency((float) $affiliate->pending_commission),
        ];
    }

    public function dashboardPayload(User $user, int $page = 1, int $perPage = 10): array
    {
        $user->loadMissing('affiliate');

        $affiliate = $user->affiliate;

        if (! $affiliate) {
            return [
                'enabled' => false,
                'can_enroll' => true,
            ];
        }

        if ((string) $affiliate->status !== 'active') {
            return [
                'enabled' => false,
                'can_enroll' => true,
                'status' => $affiliate->status,
            ];
        }

        $perPage = max(1, min($perPage, 50));
        $page = max(1, $page);

        $paginator = PaymentOrder::query()
            ->where('affiliate_id', $affiliate->id)
            ->with([
                'client:id,company_name,user_id',
                'client.user:id,name,email',
                'application:id,name',
                'package:id,name',
                'transaction:id,reference,status,paid_at,meta',
            ])
            ->latest('created_at')
            ->latest('id')
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'enabled' => true,
            'summary' => $this->affiliateSummaryPayload($user),
            'referrals' => $paginator->getCollection()
                ->map(fn (PaymentOrder $order) => $this->formatReferralRow($order))
                ->values()
                ->all(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];
    }

    public function enrollClient(User $user): Affiliate
    {
        if (! $user->client) {
            throw new \InvalidArgumentException('Profil client diperlukan.');
        }

        return $this->provisionForUser($user, null, 'active');
    }

    public function provisionForUser(User $user, ?string $code = null, string $status = 'active'): Affiliate
    {
        $user->loadMissing(['affiliate', 'client']);

        if ($user->affiliate) {
            $user->affiliate->update([
                'status' => $status,
            ]);

            return $user->affiliate->fresh();
        }

        return Affiliate::create([
            'user_id' => $user->id,
            'code' => $this->resolveAffiliateCode($code, $user->name),
            'whatsapp' => $user->client?->whatsapp,
            'status' => $status,
        ]);
    }

    public function resolveAffiliateCode(?string $code, string $name): string
    {
        $normalized = Str::upper(trim((string) $code));

        if ($normalized !== '' && ! Affiliate::query()->where('code', $normalized)->exists()) {
            return $normalized;
        }

        return $this->generateAffiliateCode($name);
    }

    public function generateAffiliateCode(string $name): string
    {
        $letter = $this->firstNameInitial($name);
        $prefix = 'A'.$letter;

        $maxSequence = Affiliate::query()
            ->where('code', 'like', $prefix.'____')
            ->pluck('code')
            ->reduce(function (int $max, string $existingCode) use ($prefix) {
                if (! preg_match('/^'.preg_quote($prefix, '/').'(\d{4})$/', $existingCode, $matches)) {
                    return $max;
                }

                return max($max, (int) $matches[1]);
            }, 0);

        return $prefix.str_pad((string) ($maxSequence + 1), 4, '0', STR_PAD_LEFT);
    }

    private function firstNameInitial(string $name): string
    {
        $firstName = Str::of($name)->trim()->explode(' ')->first() ?? '';
        $initial = Str::upper(Str::substr($firstName, 0, 1));

        return preg_match('/^[A-Z]$/', $initial) ? $initial : 'X';
    }

    private function formatReferralRow(PaymentOrder $order): array
    {
        $customerName = $order->client?->company_name ?: $order->client?->user?->name ?: '—';
        $commissionAmount = (float) data_get($order->transaction?->meta, 'commission_amount', 0);

        if ($commissionAmount <= 0 && $order->package && $order->status === 'paid') {
            $commissionAmount = $order->package->calculateAffiliateCommission((float) $order->amount);
        }

        return [
            'customer_name' => $customerName,
            'customer_email' => $order->client?->user?->email ?? '—',
            'application_name' => $order->application?->name ?? '—',
            'package_name' => $order->package?->name ?? '—',
            'amount_label' => $this->formatCurrency((float) $order->amount),
            'commission_label' => $commissionAmount > 0
                ? $this->formatCurrency($commissionAmount)
                : '—',
            'date' => $order->paid_at?->format('d M Y') ?? $order->created_at?->format('d M Y') ?? '—',
            'status' => $order->status,
            'status_label' => match ($order->status) {
                'paid' => 'Paid',
                'pending' => 'Pending',
                default => ucfirst((string) $order->status),
            },
        ];
    }

    private function formatCurrency(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}
