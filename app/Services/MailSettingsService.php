<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class MailSettingsService
{
    public const KEY = 'mail';

    public function defaults(): array
    {
        return [
            'mailer' => (string) env('MAIL_MAILER', 'log'),
            'host' => (string) env('MAIL_HOST', '127.0.0.1'),
            'port' => (int) env('MAIL_PORT', 2525),
            'username' => (string) (env('MAIL_USERNAME') ?? ''),
            'password' => (string) (env('MAIL_PASSWORD') ?? ''),
            'encryption' => env('MAIL_ENCRYPTION') ?: null,
            'from_address' => (string) env('MAIL_FROM_ADDRESS', 'hello@example.com'),
            'from_name' => (string) env('MAIL_FROM_NAME', config('app.name', 'SoftSale')),
            'app_url' => rtrim((string) env('APP_URL', 'http://localhost'), '/'),
            'verification_expire' => (int) env('AUTH_VERIFICATION_EXPIRE', 60),
        ];
    }

    public function get(): array
    {
        $defaults = $this->defaults();
        $record = SystemSetting::query()->where('key', self::KEY)->first();
        $stored = is_array($record?->payload) ? $record->payload : [];

        $merged = array_replace_recursive($defaults, $stored);

        if (! empty($stored['password_encrypted'])) {
            try {
                $merged['password'] = Crypt::decryptString($stored['password_encrypted']);
            } catch (\Throwable) {
                $merged['password'] = '';
            }
        }

        return $merged;
    }

    public function getForAdmin(): array
    {
        $settings = $this->get();

        return [
            'mailer' => $settings['mailer'],
            'host' => $settings['host'],
            'port' => $settings['port'],
            'username' => $settings['username'],
            'encryption' => $settings['encryption'],
            'from_address' => $settings['from_address'],
            'from_name' => $settings['from_name'],
            'app_url' => $settings['app_url'],
            'verification_expire' => $settings['verification_expire'],
            'password_set' => $settings['password'] !== '',
            'source' => SystemSetting::query()->where('key', self::KEY)->exists() ? 'database' : 'env',
        ];
    }

    public function update(array $data): array
    {
        $current = $this->get();
        $payload = [
            'mailer' => $data['mailer'] ?? $current['mailer'],
            'host' => $data['host'] ?? $current['host'],
            'port' => (int) ($data['port'] ?? $current['port']),
            'username' => $data['username'] ?? $current['username'],
            'encryption' => $data['encryption'] ?? $current['encryption'],
            'from_address' => $data['from_address'] ?? $current['from_address'],
            'from_name' => $data['from_name'] ?? $current['from_name'],
            'app_url' => rtrim($data['app_url'] ?? $current['app_url'], '/'),
            'verification_expire' => (int) ($data['verification_expire'] ?? $current['verification_expire']),
        ];

        $password = trim((string) ($data['password'] ?? ''));
        if ($password !== '') {
            $payload['password_encrypted'] = Crypt::encryptString($password);
        } elseif (SystemSetting::query()->where('key', self::KEY)->exists()) {
            $existing = SystemSetting::query()->where('key', self::KEY)->first();
            if (! empty($existing?->payload['password_encrypted'])) {
                $payload['password_encrypted'] = $existing->payload['password_encrypted'];
            }
        } elseif ($current['password'] !== '') {
            $payload['password_encrypted'] = Crypt::encryptString($current['password']);
        }

        SystemSetting::query()->updateOrCreate(
            ['key' => self::KEY],
            ['payload' => $payload],
        );

        $this->applyToConfig();

        return $this->getForAdmin();
    }

    public function applyToConfig(): void
    {
        $settings = $this->get();
        $encryption = $settings['encryption'] ?: null;

        Config::set('mail.default', $settings['mailer']);
        Config::set('mail.mailers.smtp.host', $settings['host']);
        Config::set('mail.mailers.smtp.port', $settings['port']);
        Config::set('mail.mailers.smtp.username', $settings['username'] ?: null);
        Config::set('mail.mailers.smtp.password', $settings['password'] ?: null);
        Config::set('mail.mailers.smtp.encryption', $encryption);
        Config::set('mail.from.address', $settings['from_address']);
        Config::set('mail.from.name', $settings['from_name']);
        Config::set('app.url', $settings['app_url']);
        Config::set('auth.verification.expire', $settings['verification_expire']);
    }

    public function sendTestEmail(string $recipient): void
    {
        $this->applyToConfig();

        $from = config('mail.from.address');
        $appName = config('mail.from.name');

        Mail::raw(
            "Ini email uji dari {$appName}.\n\nJika Anda menerima pesan ini, konfigurasi SMTP/email sudah benar.\n\nWaktu: ".now()->format('d M Y H:i:s'),
            function ($message) use ($recipient, $from, $appName) {
                $message->to($recipient)
                    ->from($from, $appName)
                    ->subject("Tes Email — {$appName}");
            },
        );
    }
}
