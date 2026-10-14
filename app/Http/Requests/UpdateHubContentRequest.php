<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHubContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $key = (string) $this->route('key');

        return match ($key) {
            'hub_site' => [
                'payload.brand' => ['required', 'string', 'max:80'],
                'payload.tagline' => ['nullable', 'string', 'max:120'],
                'payload.nav_login' => ['required', 'string', 'max:40'],
                'payload.nav_register' => ['required', 'string', 'max:40'],
            ],
            'hub_hero' => [
                'payload.badge' => ['nullable', 'string', 'max:80'],
                'payload.title' => ['required', 'string', 'max:200'],
                'payload.subtitle' => ['nullable', 'string', 'max:400'],
            ],
            'hub_catalog' => [
                'payload.title' => ['required', 'string', 'max:120'],
                'payload.subtitle' => ['nullable', 'string', 'max:300'],
                'payload.empty_text' => ['nullable', 'string', 'max:200'],
            ],
            'hub_footer' => [
                'payload.description' => ['required', 'string', 'max:500'],
                'payload.copyright' => ['required', 'string', 'max:160'],
                'payload.products_title' => ['nullable', 'string', 'max:60'],
                'payload.account_title' => ['nullable', 'string', 'max:60'],
            ],
            default => ['payload' => ['required', 'array']],
        };
    }
}
