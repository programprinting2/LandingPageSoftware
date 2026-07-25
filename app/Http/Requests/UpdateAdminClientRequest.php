<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateAdminClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var \App\Models\Client|null $client */
        $client = $this->route('client');
        $userId = $client?->user_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'whatsapp' => ['required', 'string', 'max:20', 'regex:/^08[0-9]{8,13}$/'],
            'password' => ['nullable', 'string', Password::min(8)],
            'status' => ['required', 'in:active,trial,expired,pending'],
            'email_verified' => ['nullable', 'boolean'],
            'applications' => ['nullable', 'array'],
            'applications.*.application_id' => ['required', 'integer', 'exists:applications,id'],
            'applications.*.package_id' => ['required', 'integer', 'exists:application_packages,id'],
            'application_ids' => ['nullable', 'array'],
            'application_ids.*' => ['integer', 'exists:applications,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'company_name.required' => 'Nama perusahaan wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp.regex' => 'Nomor WhatsApp harus dimulai 08 dan 10-15 digit.',
            'status.required' => 'Status wajib dipilih.',
            'applications.*.application_id.required' => 'Aplikasi wajib dipilih.',
            'applications.*.package_id.required' => 'Paket aplikasi wajib dipilih.',
        ];
    }
}
