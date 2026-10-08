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
                'payload.title' => ['required', 'string', 'max:80'],
                'payload.title_highlight' => ['required', 'string', 'max:80'],
                'payload.title_suffix' => ['required', 'string', 'max:200'],
                'payload.title_highlight_2' => ['required', 'string', 'max:80'],
                'payload.subtitle' => ['required', 'string', 'max:500'],
                'payload.cta_primary' => ['required', 'string', 'max:80'],
                'payload.cta_secondary' => ['nullable', 'string', 'max:80'],
            ],
            'video' => [
                'payload.url' => ['nullable', 'string', 'max:500'],
            ],
            'workflow' => [
                'payload.title' => ['required', 'string', 'max:160'],
                'payload.steps' => ['required', 'array', 'min:1', 'max:8'],
                'payload.steps.*.title' => ['required', 'string', 'max:120'],
                'payload.steps.*.desc' => ['required', 'string', 'max:500'],
            ],
            'features' => [
                'payload.title' => ['required', 'string', 'max:160'],
                'payload.items' => ['required', 'array', 'min:1', 'max:12'],
                'payload.items.*.title' => ['required', 'string', 'max:120'],
                'payload.items.*.desc' => ['nullable', 'string', 'max:500'],
                'payload.items.*.image' => ['nullable', 'string', 'max:500'],
            ],
            'pricing' => [
                'payload.badge' => ['required', 'string', 'max:80'],
                'payload.title' => ['required', 'string', 'max:160'],
                'payload.subtitle' => ['required', 'string', 'max:300'],
                'payload.items' => ['required', 'array', 'min:1', 'max:12'],
            ],
            'faq' => [
                'payload.title' => ['required', 'string', 'max:160'],
                'payload.subtitle' => ['nullable', 'string', 'max:300'],
                'payload.items' => ['required', 'array', 'min:1', 'max:20'],
                'payload.items.*.question' => ['required', 'string', 'max:300'],
                'payload.items.*.answer' => ['required', 'string', 'max:1000'],
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

                if ($key === 'pricing') {
                    $this->validatePricingItem($validator, $index, $item);
                }
            }
        });
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
}
