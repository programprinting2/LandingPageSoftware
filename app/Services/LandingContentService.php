<?php

namespace App\Services;

use App\Models\LandingContent;

class LandingContentService
{
    public const SECTIONS = [
        'site',
        'hero',
        'video',
        'workflow',
        'features',
        'pricing',
        'faq',
        'footer',
    ];

    public function defaults(): array
    {
        return [
            'site' => [
                'name' => (string) config('softsale.name'),
                'tagline' => (string) config('softsale.tagline'),
            ],
            'hero' => [
                'title' => 'Solusi',
                'title_highlight' => 'SATSET',
                'title_suffix' => 'cetak DTF! Proses PNG siap cetak hanya dengan',
                'title_highlight_2' => '1x klik',
                'subtitle' => 'Tinggalkan proses bikin spot color & white mask manual.',
                'cta_primary' => 'Coba Gratis',
                'cta_secondary' => 'Lihat Video',
            ],
            'video' => [
                'url' => '',
            ],
            'workflow' => [
                'title' => 'Alur Kerja Mudah dan Simpel',
                'steps' => [
                    ['title' => 'Scan & Input File', 'desc' => 'Masukkan file PNG hasil layout atau nesting Anda.'],
                    ['title' => 'Atur Parameter', 'desc' => 'Atur contract sesuai kebutuhan cetak dan mesin (opsional).'],
                    ['title' => 'Proses', 'desc' => 'Klik proses dan tunggu file PDF selesai di export.'],
                    ['title' => 'Cetak', 'desc' => 'Drag and drop file PDF siap cetak ke software mesin cetak Anda.'],
                ],
            ],
            'features' => [
                'title' => 'Dirancang untuk Produktivitas Cetak',
                'items' => config('softsale.features', []),
            ],
            'pricing' => [
                'badge' => 'Harga',
                'title' => 'Pilih Paket yang Tepat',
                'subtitle' => 'Mulai gratis, upgrade kapan saja sesuai kebutuhan produksi Anda.',
                'items' => config('softsale.plans', []),
            ],
            'faq' => [
                'title' => 'Pertanyaan Umum',
                'subtitle' => 'Jawaban untuk pertanyaan yang sering ditanyakan.',
                'items' => config('softsale.faq', []),
            ],
            'footer' => [
                'description' => 'Software otomatis spot color & white mask untuk printing DTF. Hemat waktu, tingkatkan produktivitas.',
                'copyright' => '© '.date('Y').' Quick Spot Color. All rights reserved.',
            ],
        ];
    }

    public function all(): array
    {
        $defaults = $this->defaults();
        $stored = LandingContent::query()->pluck('payload', 'key');

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

        $record = LandingContent::query()->where('key', $key)->first();
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
            ['key' => $key],
            ['payload' => $payload],
        );

        return $this->section($key);
    }

    public function landingDataPayload(): array
    {
        $cms = $this->all();

        return [
            'site' => $cms['site'],
            'hero' => $cms['hero'],
            'plans' => $cms['pricing']['items'] ?? [],
            'pricingSection' => [
                'badge' => $cms['pricing']['badge'] ?? '',
                'title' => $cms['pricing']['title'] ?? '',
                'subtitle' => $cms['pricing']['subtitle'] ?? '',
            ],
            'footer' => $cms['footer'],
        ];
    }
}
