<?php

namespace App\Services;

use App\Models\LandingContent;

class LandingContentService
{
    public const SECTIONS = [
        'site',
        'hero',
        'features',
        'pricing',
        'testimonials',
        'about',
        'contact',
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
                'badge' => 'Solusi Bisnis Digital Terdepan',
                'title' => 'Kelola Bisnis Anda',
                'title_highlight' => 'Tanpa Batas',
                'subtitle' => 'Platform manajemen bisnis all-in-one yang membantu Anda mengelola operasional, keuangan, dan tim dari satu dashboard terpadu.',
                'cta_primary' => 'Mulai Gratis 14 Hari',
                'cta_secondary' => 'Lihat Demo',
                'stats' => [
                    ['value' => '500', 'label' => 'Klien Aktif', 'animate' => true],
                    ['value' => '99.9%', 'label' => 'Uptime', 'animate' => false],
                    ['value' => '24/7', 'label' => 'Support', 'animate' => false],
                ],
            ],
            'features' => [
                'badge' => 'Fitur Unggulan',
                'title' => 'Semua yang bisnis Anda butuhkan',
                'subtitle' => 'Dirancang modular agar fleksibel mengikuti skala bisnis Anda, dari UMKM hingga korporasi.',
                'items' => config('softsale.features', []),
            ],
            'pricing' => [
                'badge' => 'Harga Transparan',
                'title' => 'Pilih paket yang tepat',
                'subtitle' => 'Tanpa biaya tersembunyi. Upgrade atau downgrade kapan saja.',
                'items' => config('softsale.plans', []),
            ],
            'testimonials' => [
                'badge' => 'Testimonial',
                'title' => 'Dipercaya ratusan bisnis',
                'subtitle' => 'Dengarkan langsung dari mereka yang sudah merasakan manfaatnya.',
                'items' => config('softsale.testimonials', []),
            ],
            'about' => [
                'badge' => 'Tentang Kami',
                'title' => 'Dibangun oleh praktisi,',
                'title_break' => 'untuk praktisi bisnis',
                'paragraphs' => [
                    'SoftSale lahir dari pengalaman nyata mengelola operasional bisnis digital di Indonesia. Kami memahami tantangan yang dihadapi UMKM hingga korporasi — mulai dari pencatatan manual yang rawan error, hingga kebutuhan akan sistem terintegrasi.',
                    'Tim kami terdiri dari engineer dan konsultan bisnis berpengalaman yang berkomitmen menghadirkan solusi teknologi berkualitas enterprise dengan harga terjangkau.',
                ],
                'stats' => [
                    ['value' => '5', 'label' => 'Tahun Pengalaman', 'animate' => true],
                    ['value' => '20', 'label' => 'Tim Profesional', 'animate' => true],
                    ['value' => '99%', 'label' => 'Kepuasan Client', 'animate' => false],
                ],
                'values' => config('softsale.about_values', []),
            ],
            'contact' => [
                'badge' => 'Kontak',
                'title' => 'Hubungi Kami',
                'subtitle' => 'Punya pertanyaan atau butuh konsultasi? Tim kami siap membantu Anda.',
                'items' => config('softsale.contacts', []),
            ],
            'footer' => [
                'description' => 'Platform manajemen bisnis all-in-one untuk era digital. Dibuat dengan teknologi terdepan untuk bisnis Indonesia.',
                'copyright' => '© '.date('Y').' SoftSale. All rights reserved.',
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
            'features' => $cms['features']['items'] ?? [],
            'featuresSection' => [
                'badge' => $cms['features']['badge'] ?? '',
                'title' => $cms['features']['title'] ?? '',
                'subtitle' => $cms['features']['subtitle'] ?? '',
            ],
            'plans' => $cms['pricing']['items'] ?? [],
            'pricingSection' => [
                'badge' => $cms['pricing']['badge'] ?? '',
                'title' => $cms['pricing']['title'] ?? '',
                'subtitle' => $cms['pricing']['subtitle'] ?? '',
            ],
            'testimonials' => $cms['testimonials']['items'] ?? [],
            'testimonialsSection' => [
                'badge' => $cms['testimonials']['badge'] ?? '',
                'title' => $cms['testimonials']['title'] ?? '',
                'subtitle' => $cms['testimonials']['subtitle'] ?? '',
            ],
            'aboutValues' => $cms['about']['values'] ?? [],
            'aboutSection' => [
                'badge' => $cms['about']['badge'] ?? '',
                'title' => $cms['about']['title'] ?? '',
                'title_break' => $cms['about']['title_break'] ?? '',
                'paragraphs' => $cms['about']['paragraphs'] ?? [],
                'stats' => $cms['about']['stats'] ?? [],
            ],
            'contacts' => $cms['contact']['items'] ?? [],
            'contactSection' => [
                'badge' => $cms['contact']['badge'] ?? '',
                'title' => $cms['contact']['title'] ?? '',
                'subtitle' => $cms['contact']['subtitle'] ?? '',
            ],
            'footer' => $cms['footer'],
        ];
    }
}
