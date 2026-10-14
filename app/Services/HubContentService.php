<?php

namespace App\Services;

use App\Models\LandingContent;

class HubContentService
{
    public const SECTIONS = [
        'hub_site',
        'hub_hero',
        'hub_catalog',
        'hub_footer',
    ];

    public function defaults(): array
    {
        $year = date('Y');

        return [
            'hub_site' => [
                'brand' => 'Softsale',
                'tagline' => 'Platform lisensi software',
                'nav_login' => 'Masuk',
                'nav_register' => 'Daftar',
            ],
            'hub_hero' => [
                'badge' => 'Multi-product platform',
                'title' => 'Satu portal untuk semua software produksi Anda',
                'subtitle' => 'Trial, lisensi, dan pembayaran terpusat. Setiap produk punya landing sendiri — siap scale dari DTF hingga workflow print shop.',
            ],
            'hub_catalog' => [
                'title' => 'Produk kami',
                'subtitle' => 'Pilih aplikasi untuk melihat fitur, harga, dan mulai trial.',
                'empty_text' => 'Belum ada aplikasi aktif. Tambahkan di Master Aplikasi.',
            ],
            'hub_footer' => [
                'description' => 'Softsale menghubungkan software desktop Anda dengan lisensi, pembayaran, dan affiliate — siap skala multi-produk.',
                'copyright' => "© {$year} Softsale. All rights reserved.",
                'products_title' => 'Produk',
                'account_title' => 'Akun',
            ],
        ];
    }

    public function all(): array
    {
        $defaults = $this->defaults();
        $stored = LandingContent::query()
            ->whereNull('application_id')
            ->whereIn('key', self::SECTIONS)
            ->pluck('payload', 'key');

        $merged = [];
        foreach ($defaults as $key => $default) {
            $payload = $stored->get($key);
            $merged[$key] = is_array($payload)
                ? array_replace_recursive($default, $payload)
                : $default;
        }

        return $merged;
    }

    public function section(string $key): array
    {
        $defaults = $this->defaults();
        if (! isset($defaults[$key])) {
            return [];
        }

        $record = LandingContent::query()
            ->whereNull('application_id')
            ->where('key', $key)
            ->first();

        $payload = $record?->payload;

        return is_array($payload)
            ? array_replace_recursive($defaults[$key], $payload)
            : $defaults[$key];
    }

    public function updateSection(string $key, array $payload): array
    {
        if (! in_array($key, self::SECTIONS, true)) {
            abort(404);
        }

        LandingContent::query()->updateOrCreate(
            [
                'application_id' => null,
                'key' => $key,
            ],
            ['payload' => $payload],
        );

        return $this->section($key);
    }

    /**
     * @return array<string, mixed>
     */
    public function viewPayload(): array
    {
        $all = $this->all();

        return [
            'site' => $all['hub_site'],
            'hero' => $all['hub_hero'],
            'catalog' => $all['hub_catalog'],
            'footer' => $all['hub_footer'],
        ];
    }
}
