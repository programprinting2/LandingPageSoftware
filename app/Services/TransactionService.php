<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationPackage;
use App\Models\Client;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;

class TransactionService
{
    public function recordPurchase(
        Client $client,
        Application $application,
        ApplicationPackage $package,
        string $paymentMethod = 'admin',
        ?Carbon $paidAt = null,
        array $meta = [],
    ): Transaction {
        $paidAt ??= now();

        return $this->record([
            'client_id' => $client->id,
            'user_id' => $client->user_id,
            'application_id' => $application->id,
            'application_package_id' => $package->id,
            'type' => 'purchase',
            'description' => sprintf('%s — %s', $application->name, $package->name),
            'amount' => (float) $package->price,
            'payment_method' => $paymentMethod,
            'status' => 'paid',
            'paid_at' => $paidAt,
            'meta' => $meta,
        ]);
    }

    public function recordUpgrade(
        User $user,
        Application $application,
        ApplicationPackage $targetPackage,
        ?ApplicationPackage $currentPackage = null,
        string $paymentMethod = 'qris',
        array $meta = [],
    ): Transaction {
        $client = $user->client;

        return $this->record([
            'client_id' => $client?->id,
            'user_id' => $user->id,
            'application_id' => $application->id,
            'application_package_id' => $targetPackage->id,
            'type' => 'upgrade',
            'description' => sprintf(
                'Upgrade %s: %s → %s',
                $application->name,
                $currentPackage?->name ?? '—',
                $targetPackage->name
            ),
            'amount' => (float) $targetPackage->price,
            'payment_method' => $paymentMethod,
            'status' => 'paid',
            'paid_at' => now(),
            'meta' => array_merge($meta, [
                'from_package_id' => $currentPackage?->id,
                'to_package_id' => $targetPackage->id,
            ]),
        ]);
    }

    public function recordRenew(
        Client $client,
        Application $application,
        ApplicationPackage $package,
        string $paymentMethod = 'admin',
        array $meta = [],
    ): Transaction {
        return $this->record([
            'client_id' => $client->id,
            'user_id' => $client->user_id,
            'application_id' => $application->id,
            'application_package_id' => $package->id,
            'type' => 'renew',
            'description' => sprintf('Perpanjang %s — %s', $application->name, $package->name),
            'amount' => (float) $package->price,
            'payment_method' => $paymentMethod,
            'status' => 'paid',
            'paid_at' => now(),
            'meta' => $meta,
        ]);
    }

    public function record(array $data): Transaction
    {
        $paidAt = isset($data['paid_at']) && $data['paid_at'] instanceof Carbon
            ? $data['paid_at']
            : (isset($data['paid_at']) ? Carbon::parse($data['paid_at']) : now());

        return Transaction::create([
            'reference' => $data['reference'] ?? $this->nextReference($paidAt),
            'client_id' => $data['client_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'application_id' => $data['application_id'] ?? null,
            'application_package_id' => $data['application_package_id'] ?? null,
            'type' => $data['type'],
            'description' => $data['description'],
            'amount' => $data['amount'] ?? 0,
            'currency' => $data['currency'] ?? 'IDR',
            'payment_method' => $data['payment_method'] ?? 'admin',
            'status' => $data['status'] ?? 'paid',
            'paid_at' => $paidAt,
            'meta' => $data['meta'] ?? null,
        ]);
    }

    private function nextReference(Carbon $paidAt): string
    {
        $year = $paidAt->format('Y');
        $latestId = (int) Transaction::query()->max('id');

        return sprintf('TRX-%s-%05d', $year, $latestId + 1);
    }
}
