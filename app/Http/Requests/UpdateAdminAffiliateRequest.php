<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateAdminAffiliateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var \App\Models\Affiliate|null $affiliate */
        $affiliate = $this->route('affiliate');
        $userId = $affiliate?->user_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => ['nullable', 'string', Password::min(8)],
            'code' => [
                'required',
                'string',
                'size:6',
                'regex:/^A[A-Z][0-9]{4}$/',
                Rule::unique('affiliates', 'code')->ignore($affiliate?->id),
            ],
            'whatsapp' => ['nullable', 'string', 'max:20', 'regex:/^08[0-9]{8,13}$/'],
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
            'code.required' => 'Kode affiliate wajib diisi.',
            'code.regex' => 'Format kode: A + huruf nama depan + 4 digit (contoh AA0001).',
            'code.unique' => 'Kode affiliate sudah digunakan.',
            'whatsapp.regex' => 'Nomor WhatsApp harus dimulai 08 dan 10-15 digit.',
            'status.required' => 'Status wajib dipilih.',
        ];
    }
}
