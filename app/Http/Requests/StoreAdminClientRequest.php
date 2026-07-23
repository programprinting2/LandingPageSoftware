<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreAdminClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'whatsapp' => ['required', 'string', 'max:20', 'regex:/^08[0-9]{8,13}$/'],
            'password' => ['required', 'string', Password::min(8)],
            'status' => ['required', 'in:active,trial,expired,pending'],
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
            'password.required' => 'Password wajib diisi.',
            'status.required' => 'Status wajib dipilih.',
            'applications.*.application_id.required' => 'Aplikasi wajib dipilih.',
            'applications.*.package_id.required' => 'Paket aplikasi wajib dipilih.',
        ];
    }
}
