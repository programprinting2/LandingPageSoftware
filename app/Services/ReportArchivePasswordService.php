<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class ReportArchivePasswordService
{
    public function isConfigured(User $user, ?string $applicationSlug = null): bool
    {
        if ($applicationSlug !== null && $applicationSlug !== '') {
            return $this->isConfiguredForApplication($user, $applicationSlug);
        }

        return $this->isConfiguredForAnyApplication($user);
    }

    public function isConfiguredForAnyApplication(User $user): bool
    {
        $client = $this->clientForUser($user);
        if (! $client) {
            return false;
        }

        if ($this->clientHasLegacyPassword($client)) {
            return true;
        }

        return DB::table('client_applications')
            ->where('client_id', $client->id)
            ->whereNotNull('report_archive_password')
            ->where('report_archive_password', '!=', '')
            ->exists();
    }

    public function isConfiguredForApplication(User $user, string $applicationSlug): bool
    {
        $password = $this->encryptedPasswordForApplication($user, $applicationSlug);

        return $password !== null && $password !== '';
    }

    public function passwordForUser(User $user, ?string $applicationSlug = null): ?string
    {
        $slug = $applicationSlug ?: 'spot-color';

        return $this->passwordForApplication($user, $slug);
    }

    public function passwordForApplication(User $user, string $applicationSlug): ?string
    {
        $encrypted = $this->encryptedPasswordForApplication($user, $applicationSlug);
        if ($encrypted === null || $encrypted === '') {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (\Throwable) {
            return null;
        }
    }

    public function updatePassword(User $user, string $password, string $applicationSlug): array
    {
        $client = $this->clientForUser($user);
        if (! $client) {
            return [
                'status' => 'error',
                'message' => 'Profil client tidak ditemukan.',
            ];
        }

        $application = Application::query()->where('slug', $applicationSlug)->first();
        if (! $application) {
            return [
                'status' => 'error',
                'message' => 'Aplikasi tidak ditemukan.',
            ];
        }

        $ownsApplication = $client->applications()->where('applications.id', $application->id)->exists();
        if (! $ownsApplication) {
            return [
                'status' => 'error',
                'message' => 'Aplikasi ini tidak terdaftar pada akun Anda.',
            ];
        }

        $password = trim($password);
        if (strlen($password) < 8) {
            return [
                'status' => 'error',
                'message' => 'Password arsip minimal 8 karakter.',
            ];
        }

        if (strlen($password) > 128) {
            return [
                'status' => 'error',
                'message' => 'Password arsip maksimal 128 karakter.',
            ];
        }

        $client->applications()->updateExistingPivot($application->id, [
            'report_archive_password' => Crypt::encryptString($password),
        ]);

        return [
            'status' => 'success',
            'message' => 'Password arsip laporan disimpan.',
            'configured' => true,
            'application_slug' => $applicationSlug,
        ];
    }

    public function settingsPayload(User $user, string $applicationSlug): array
    {
        return [
            'application_slug' => $applicationSlug,
            'configured' => $this->isConfiguredForApplication($user, $applicationSlug),
        ];
    }

    private function encryptedPasswordForApplication(User $user, string $applicationSlug): ?string
    {
        $client = $this->clientForUser($user);
        if (! $client) {
            return null;
        }

        $application = Application::query()->where('slug', $applicationSlug)->first();
        if ($application) {
            $pivot = DB::table('client_applications')
                ->where('client_id', $client->id)
                ->where('application_id', $application->id)
                ->value('report_archive_password');

            if ($pivot !== null && $pivot !== '') {
                return (string) $pivot;
            }
        }

        if ($this->clientHasLegacyPassword($client)) {
            return (string) $client->report_archive_password;
        }

        return null;
    }

    private function clientHasLegacyPassword(Client $client): bool
    {
        return $client->report_archive_password !== null && $client->report_archive_password !== '';
    }

    private function clientForUser(User $user): ?Client
    {
        return Client::query()->where('user_id', $user->id)->first();
    }
}
