<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesWhatsappInput;
use App\Rules\ValidAffiliateReferralCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateAdminAffiliateRequest extends FormRequest
{
    use NormalizesWhatsappInput;

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
                'max:32',
                new ValidAffiliateReferralCode($affiliate?->id),
            ],
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
            'code.required' => 'Kode affiliate wajib diisi.',
            'code.regex' => 'Kode harus 6 karakter huruf besar A–Z dan angka 0–9.',
            'code.unique' => 'Kode affiliate sudah digunakan.',
            'status.required' => 'Status wajib dipilih.',
        ];
    }
}
