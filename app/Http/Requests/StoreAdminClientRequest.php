<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesWhatsappInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreAdminClientRequest extends FormRequest
{
    use NormalizesWhatsappInput;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', Password::min(8)],
            'status' => ['required', 'in:active,inactive'],
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
            'name.required' => 'Nama akun wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'status.required' => 'Status wajib dipilih.',
            'applications.*.application_id.required' => 'Aplikasi wajib dipilih.',
            'applications.*.package_id.required' => 'Paket aplikasi wajib dipilih.',
        ];
    }
}
