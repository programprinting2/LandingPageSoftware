<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:applications,slug'],
            'description' => ['nullable', 'string', 'max:2000'],
            'logo_url' => ['nullable', 'string', 'max:500'],
            'default_devices_max' => ['required', 'integer', 'min:1', 'max:99'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'packages' => ['nullable', 'array'],
            'packages.*.id' => ['nullable', 'integer', 'min:1'],
            'packages.*.name' => ['required', 'string', 'max:100'],
            'packages.*.devices_quota' => ['required', 'integer', 'min:1', 'max:999'],
            'packages.*.device_slot_price' => ['nullable', 'numeric', 'min:0'],
            'packages.*.max_extra_device_slots' => ['nullable', 'integer', 'min:0', 'max:99'],
            'packages.*.duration_days' => ['required', 'integer', 'min:0', 'max:36500'],
            'packages.*.price' => ['required', 'numeric', 'min:0'],
            'packages.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'packages.*.affiliate_commission_fixed' => ['nullable', 'numeric', 'min:0'],
            'packages.*.affiliate_commission_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'packages.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'packages.*.parallel_max' => ['nullable', 'integer', 'min:1', 'max:99'],
            'packages.*.feature_uv' => ['sometimes', 'boolean'],
            'notification_enabled' => ['sometimes', 'boolean'],
            'notification_image_url' => ['nullable', 'string', 'max:500'],
            'notification_package_ids' => ['nullable', 'array'],
            'notification_package_ids.*' => ['integer', 'min:1'],
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
