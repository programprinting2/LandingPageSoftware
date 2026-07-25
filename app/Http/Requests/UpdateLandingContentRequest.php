<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateLandingContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->route('key')) {
            'site' => [
                'payload.name' => ['required', 'string', 'max:80'],
                'payload.tagline' => ['required', 'string', 'max:120'],
            ],
            'hero' => [
                'payload.badge' => ['required', 'string', 'max:120'],
                'payload.title' => ['required', 'string', 'max:160'],
                'payload.title_highlight' => ['required', 'string', 'max:80'],
                'payload.subtitle' => ['required', 'string', 'max:500'],
                'payload.cta_primary' => ['required', 'string', 'max:80'],
                'payload.cta_secondary' => ['nullable', 'string', 'max:80'],
                'payload.stats' => ['required', 'array', 'min:1', 'max:6'],
                'payload.stats.*.value' => ['required', 'string', 'max:40'],
                'payload.stats.*.label' => ['required', 'string', 'max:80'],
                'payload.stats.*.animate' => ['nullable', 'boolean'],
            ],
            'features', 'pricing', 'testimonials', 'contact' => [
                'payload.badge' => ['required', 'string', 'max:80'],
                'payload.title' => ['required', 'string', 'max:160'],
                'payload.subtitle' => ['required', 'string', 'max:300'],
                'payload.items' => ['required', 'array', 'min:1', 'max:12'],
            ],
            'about' => [
                'payload.badge' => ['required', 'string', 'max:80'],
                'payload.title' => ['required', 'string', 'max:160'],
                'payload.title_break' => ['nullable', 'string', 'max:160'],
                'payload.paragraphs' => ['required', 'array', 'min:1', 'max:6'],
                'payload.paragraphs.*' => ['required', 'string', 'max:1000'],
                'payload.stats' => ['required', 'array', 'min:1', 'max:6'],
                'payload.stats.*.value' => ['required', 'string', 'max:40'],
                'payload.stats.*.label' => ['required', 'string', 'max:80'],
                'payload.stats.*.animate' => ['nullable', 'boolean'],
                'payload.values' => ['required', 'array', 'min:1', 'max:8'],
            ],
            'footer' => [
                'payload.description' => ['required', 'string', 'max:500'],
                'payload.copyright' => ['required', 'string', 'max:160'],
            ],
            default => [],
        };
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $key = (string) $this->route('key');
            $items = $this->input('payload.items', []);

            if (! is_array($items)) {
                return;
            }

            foreach ($items as $index => $item) {
                if (! is_array($item)) {
                    continue;
                }

                match ($key) {
                    'features' => $this->validateFeatureItem($validator, $index, $item),
                    'pricing' => $this->validatePricingItem($validator, $index, $item),
                    'testimonials' => $this->validateTestimonialItem($validator, $index, $item),
                    'contact' => $this->validateContactItem($validator, $index, $item),
                    default => null,
                };
            }

            if ($key === 'about') {
                foreach ($this->input('payload.values', []) as $index => $item) {
                    if (! is_array($item)) {
                        continue;
                    }
                    $this->validateFeatureItem($validator, $index, $item, 'payload.values');
                }
            }
        });
    }

    private function validateFeatureItem(Validator $validator, int $index, array $item, string $prefix = 'payload.items'): void
    {
        foreach (['title' => 120, 'desc' => 500, 'icon' => 500, 'color' => 40] as $field => $max) {
            if (empty($item[$field])) {
                $validator->errors()->add("{$prefix}.{$index}.{$field}", "Field {$field} wajib diisi.");
            } elseif (strlen((string) $item[$field]) > $max) {
                $validator->errors()->add("{$prefix}.{$index}.{$field}", "Field {$field} maksimal {$max} karakter.");
            }
        }
    }

    private function validatePricingItem(Validator $validator, int $index, array $item): void
    {
        foreach (['name' => 80, 'price' => 40, 'period' => 40] as $field => $max) {
            if (empty($item[$field])) {
                $validator->errors()->add("payload.items.{$index}.{$field}", "Field {$field} wajib diisi.");
            }
        }

        if (! isset($item['features']) || ! is_array($item['features']) || count($item['features']) < 1) {
            $validator->errors()->add("payload.items.{$index}.features", 'Minimal 1 fitur paket.');
        }
    }

    private function validateTestimonialItem(Validator $validator, int $index, array $item): void
    {
        foreach (['name' => 80, 'role' => 120, 'text' => 500, 'avatar' => 4] as $field => $max) {
            if (empty($item[$field])) {
                $validator->errors()->add("payload.items.{$index}.{$field}", "Field {$field} wajib diisi.");
            }
        }
    }

    private function validateContactItem(Validator $validator, int $index, array $item): void
    {
        foreach (['title' => 80, 'detail' => 120, 'sub' => 160, 'icon' => 500, 'color' => 40] as $field => $max) {
            if (empty($item[$field])) {
                $validator->errors()->add("payload.items.{$index}.{$field}", "Field {$field} wajib diisi.");
            }
        }
    }
}
