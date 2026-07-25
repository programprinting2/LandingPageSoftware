<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMailSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mailer' => ['required', 'string', Rule::in(['log', 'smtp', 'sendmail'])],
            'host' => ['required_if:mailer,smtp', 'nullable', 'string', 'max:255'],
            'port' => ['required_if:mailer,smtp', 'nullable', 'integer', 'min:1', 'max:65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'encryption' => ['nullable', 'string', Rule::in(['tls', 'ssl', ''])],
            'from_address' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
            'app_url' => ['required', 'url', 'max:255'],
            'verification_expire' => ['required', 'integer', 'min:5', 'max:10080'],
        ];
    }

    public function messages(): array
    {
        return [
            'mailer.required' => 'Driver email wajib dipilih.',
            'host.required_if' => 'Host SMTP wajib diisi untuk driver SMTP.',
            'port.required_if' => 'Port SMTP wajib diisi untuk driver SMTP.',
            'from_address.required' => 'Email pengirim wajib diisi.',
            'from_address.email' => 'Format email pengirim tidak valid.',
            'from_name.required' => 'Nama pengirim wajib diisi.',
            'app_url.required' => 'URL aplikasi wajib diisi.',
            'app_url.url' => 'Format URL aplikasi tidak valid.',
            'verification_expire.required' => 'Masa berlaku link verifikasi wajib diisi.',
        ];
    }
}
