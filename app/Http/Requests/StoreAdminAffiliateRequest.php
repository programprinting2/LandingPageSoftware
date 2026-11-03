<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesWhatsappInput;
use App\Rules\ValidAffiliateReferralCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreAdminAffiliateRequest extends FormRequest
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
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)],
            'code' => ['nullable', 'string', 'max:32', new ValidAffiliateReferralCode()],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account' => ['nullable', 'string', 'max:64'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,pending,suspended'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'code.regex' => 'Kode harus 6 karakter huruf besar A–Z dan angka 0–9.',
            'code.unique' => 'Kode affiliate sudah digunakan.',
            'status.required' => 'Status wajib dipilih.',
        ];
    }
}
