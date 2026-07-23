<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var \App\Models\Application|null $application */
        $application = $this->route('application');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('applications', 'slug')->ignore($application?->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'default_devices_max' => ['required', 'integer', 'min:1', 'max:99'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'packages' => ['nullable', 'array'],
            'packages.*.name' => ['required', 'string', 'max:100'],
            'packages.*.devices_quota' => ['required', 'integer', 'min:1', 'max:999'],
            'packages.*.duration_days' => ['required', 'integer', 'min:0', 'max:36500'],
            'packages.*.price' => ['required', 'numeric', 'min:0'],
            'packages.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama aplikasi wajib diisi.',
            'slug.regex' => 'Slug hanya boleh huruf kecil, angka, dan tanda strip.',
            'slug.unique' => 'Slug sudah digunakan.',
            'default_devices_max.required' => 'Limit default wajib diisi.',
        ];
    }
}
