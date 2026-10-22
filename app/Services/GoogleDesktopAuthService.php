<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class GoogleDesktopAuthService
{
    private const CACHE_PREFIX = 'google_desktop_exchange:';

    public function issueExchangeCode(User $user): string
    {
        $plain = Str::random(48);
        $hash = hash('sha256', $plain);

        Cache::put(self::CACHE_PREFIX.$hash, [
            'user_id' => $user->id,
        ], now()->addMinutes(5));

        return $plain;
    }

    public function consumeExchangeCode(string $plainCode): ?User
    {
        $plainCode = trim($plainCode);
        if ($plainCode === '') {
            return null;
        }

        $hash = hash('sha256', $plainCode);
        $payload = Cache::pull(self::CACHE_PREFIX.$hash);

        if (! is_array($payload) || empty($payload['user_id'])) {
            return null;
        }

        return User::query()->find((int) $payload['user_id']);
    }
}
