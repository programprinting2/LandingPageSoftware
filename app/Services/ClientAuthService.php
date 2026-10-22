<?php

namespace App\Services;

use App\Models\ApiToken;
use App\Models\Application;
use App\Models\ApplicationPackage;
use App\Models\Client;
use App\Models\User;
use App\Services\AffiliateService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class ClientAuthService
{
    public function __construct(private AffiliateService $affiliateService) {}

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
                'company_name' => trim((string) ($data['company_name'] ?? '')) ?: $user->name,
                'whatsapp' => $data['whatsapp'],
                'status' => 'active',
                'devices_max' => config('spotcolor.devices_max', 3),
            ]);

            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                report($e);
            }

            return compact('user', 'client');
        });
    }

    public function findGoogleOnlyClientByEmail(string $email): ?User
    {
        $user = User::query()->where('email', strtolower(trim($email)))->first();

        if (! $user || $user->role !== 'client' || ! $user->google_id || $user->password) {
            return null;
        }

        $user->loadMissing('client');

        return $user->client ? $user : null;
    }

    public function attemptClientLogin(string $email, string $password): ?User
    {
        $user = User::where('email', strtolower(trim($email)))->first();

        if (! $user || ! $user->password || ! Hash::check($password, $user->password)) {
            return null;
        }

        if ($user->role !== 'client') {
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

    /**
     * @return array{status: string, message?: string, user?: User, created?: bool}
     */
    public function loginOrRegisterWithGoogle(SocialiteUser $googleUser): array
    {
        $googleId = (string) $googleUser->getId();
        $email = strtolower(trim((string) $googleUser->getEmail()));
        $name = trim((string) ($googleUser->getName() ?: $googleUser->getNickname() ?: 'Pengguna'));

        if ($email === '') {
            return ['status' => 'error', 'message' => 'Google tidak mengirim alamat email.'];
        }

        $raw = $googleUser->getRaw();
        $emailVerified = is_array($raw) ? ($raw['email_verified'] ?? true) : true;
        if (! $emailVerified) {
            return ['status' => 'error', 'message' => 'Email Google belum terverifikasi.'];
        }

        return DB::transaction(function () use ($googleId, $email, $name) {
            $byGoogle = User::query()->where('google_id', $googleId)->first();
            if ($byGoogle) {
                return $this->finalizeGoogleClientLogin($byGoogle, false);
            }

            $byEmail = User::query()->where('email', $email)->first();
            if ($byEmail) {
                if ($byEmail->role !== 'client') {
                    return [
                        'status' => 'error',
                        'message' => 'Email ini dipakai akun admin. Gunakan login admin, bukan Google client.',
                    ];
                }

                if ($byEmail->google_id && $byEmail->google_id !== $googleId) {
                    return [
                        'status' => 'error',
                        'message' => 'Akun Google tidak cocok dengan email terdaftar.',
                    ];
                }

                $byEmail->google_id = $googleId;
                if (! $byEmail->hasVerifiedEmail()) {
                    $byEmail->email_verified_at = now();
                }
                $byEmail->save();

                return $this->finalizeGoogleClientLogin($byEmail->fresh(['client']), false);
            }

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'google_id' => $googleId,
                'password' => null,
                'role' => 'client',
                'email_verified_at' => now(),
            ]);

            Client::create([
                'user_id' => $user->id,
                'company_name' => $name,
                'whatsapp' => '',
                'status' => 'active',
                'devices_max' => config('spotcolor.devices_max', 3),
            ]);

            return $this->finalizeGoogleClientLogin($user->fresh(['client']), true);
        });
    }

    /**
     * @return array{status: string, message?: string, user?: User, created?: bool}
     */
    private function finalizeGoogleClientLogin(User $user, bool $created): array
    {
        $user->loadMissing('client');

        if ($user->role !== 'client' || ! $user->client) {
            return ['status' => 'error', 'message' => 'Profil client tidak ditemukan.'];
        }

        if (! $this->isClientActive($user)) {
            return ['status' => 'error', 'message' => 'Akun dinonaktifkan. Hubungi admin.'];
        }

        return [
            'status' => 'success',
            'user' => $user,
            'created' => $created,
        ];
    }

    public function findUnverifiedClientByCredentials(string $email, string $password): ?User
    {
        $user = User::where('email', strtolower(trim($email)))->first();

        if (! $user || ! $user->password || ! Hash::check($password, $user->password)) {
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

        if (! $user || ! $user->password || ! Hash::check($password, $user->password)) {
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
        $user->loadMissing(['client', 'affiliate']);

        $affiliateSummary = $this->affiliateService->affiliateSummaryPayload($user);

        return [
            'name' => $user->name,
            'company_name' => $user->client?->company_name ?? $user->name,
            'email' => $user->email,
            'whatsapp' => $user->client?->whatsapp ?? '',
            'affiliate' => $affiliateSummary,
            'can_enroll_affiliate' => ! $affiliateSummary || ! ($affiliateSummary['active'] ?? false),
        ];
    }

    public function canAccessClientPortal(User $user): bool
    {
        $user->loadMissing('client');

        return $user->role === 'client'
            && $user->client
            && $this->isClientActive($user);
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

    private function assignDefaultTrialLicense(Client $client, ?int $applicationId = null): void
    {
        $trialPackageName = strtolower((string) config('softsale.default_trial_package_name', 'trial'));

        $application = null;
        if ($applicationId) {
            $application = Application::query()
                ->where('id', $applicationId)
                ->where('is_active', true)
                ->first();
        }

        if (! $application) {
            $applicationSlug = (string) config('softsale.default_application_slug', 'spot-color');
            $application = Application::query()
                ->where('slug', $applicationSlug)
                ->where('is_active', true)
                ->first();
        }

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
