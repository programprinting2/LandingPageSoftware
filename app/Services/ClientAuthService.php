<?php

namespace App\Services;

use App\Models\ApiToken;
use App\Models\Application;
use App\Models\ApplicationPackage;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientAuthService
{
    public function registerClient(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => strtolower(trim($data['email'])),
                'password' => $data['password'],
                'role' => 'client',
            ]);

            $client = Client::create([
                'user_id' => $user->id,
                'company_name' => $data['company_name'],
                'whatsapp' => $data['whatsapp'],
                'status' => 'active',
                'devices_max' => config('spotcolor.devices_max', 3),
            ]);

            $this->assignDefaultTrialLicense($client);

            $user->sendEmailVerificationNotification();

            return compact('user', 'client');
        });
    }

    public function attemptClientLogin(string $email, string $password): ?User
    {
        $user = User::where('email', strtolower(trim($email)))->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        if ($user->role !== 'client') {
            return null;
        }

        if (! $user->hasVerifiedEmail()) {
            return null;
        }

        $user->load('client');

        if (! $user->client) {
            return null;
        }

        if (! $this->isClientActive($user)) {
            return null;
        }

        return $user;
    }

    public function findUnverifiedClientByCredentials(string $email, string $password): ?User
    {
        $user = User::where('email', strtolower(trim($email)))->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        if ($user->role !== 'client' || $user->hasVerifiedEmail()) {
            return null;
        }

        return $user->load('client');
    }

    public function findInactiveClientByCredentials(string $email, string $password): ?User
    {
        $user = User::where('email', strtolower(trim($email)))->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        if ($user->role !== 'client' || ! $user->hasVerifiedEmail()) {
            return null;
        }

        $user->load('client');

        if (! $user->client || $this->isClientActive($user)) {
            return null;
        }

        return $user;
    }

    public function isClientActive(User $user): bool
    {
        $user->loadMissing('client');

        return $user->client && (string) $user->client->status === 'active';
    }

    public function createApiToken(User $user): array
    {
        $plainToken = Str::random(64);
        $expiresAt = now()->addDays(30);

        ApiToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $plainToken),
            'expires_at' => $expiresAt,
        ]);

        return [
            'token' => $plainToken,
            'expires_at' => $expiresAt->toIso8601String(),
            'email' => $user->email,
            'name' => $user->name,
            'created_at' => $user->created_at?->toIso8601String() ?? now()->toIso8601String(),
        ];
    }

    public function resolveApiToken(string $plainToken): ?User
    {
        $plainToken = trim($plainToken);

        if ($plainToken === '') {
            return null;
        }

        $record = ApiToken::where('token', hash('sha256', $plainToken))
            ->with(['user.client'])
            ->first();

        if (! $record || $record->isExpired()) {
            if ($record?->isExpired()) {
                $record->delete();
            }

            return null;
        }

        $user = $record->user;

        if (! $user || $user->role !== 'client' || ! $user->client) {
            return null;
        }

        if (! $this->isClientActive($user)) {
            return null;
        }

        return $user;
    }

    public function revokeApiToken(string $plainToken): void
    {
        $plainToken = trim($plainToken);

        if ($plainToken === '') {
            return;
        }

        ApiToken::where('token', hash('sha256', $plainToken))->delete();
    }

    public function clientPayload(User $user): array
    {
        return [
            'name' => $user->name,
            'company_name' => $user->client->company_name,
            'email' => $user->email,
            'whatsapp' => $user->client->whatsapp,
        ];
    }

    public function apiSessionPayload(User $user, string $message): array
    {
        return array_merge(
            [
                'status' => 'success',
                'message' => $message,
            ],
            $this->createApiToken($user)
        );
    }

    private function assignDefaultTrialLicense(Client $client): void
    {
        $applicationSlug = (string) config('softsale.default_application_slug', 'spot-color');
        $trialPackageName = strtolower((string) config('softsale.default_trial_package_name', 'trial'));

        $application = Application::query()
            ->where('slug', $applicationSlug)
            ->where('is_active', true)
            ->first();

        if (! $application) {
            return;
        }

        $package = ApplicationPackage::query()
            ->where('application_id', $application->id)
            ->whereRaw('LOWER(name) = ?', [$trialPackageName])
            ->first();

        if (! $package) {
            $package = ApplicationPackage::query()
                ->where('application_id', $application->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->first();
        }

        if (! $package) {
            return;
        }

        $purchasedAt = now();
        $durationDays = (int) $package->duration_days;
        $expiresAt = $durationDays > 0 ? $purchasedAt->copy()->addDays($durationDays) : null;

        $client->applications()->attach($application->id, [
            'application_package_id' => $package->id,
            'devices_max' => (int) $package->devices_quota,
            'status' => 'trial',
            'purchased_at' => $purchasedAt,
            'expires_at' => $expiresAt,
        ]);
    }
}
