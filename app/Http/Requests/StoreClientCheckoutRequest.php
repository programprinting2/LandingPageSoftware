<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'package_id' => ['required', 'integer', 'min:1'],
            'application_slug' => ['nullable', 'string', 'max:120'],
            'referral_code' => ['nullable', 'string', 'max:32'],
            'total_devices' => ['nullable', 'integer', 'min:1', 'max:999'],
        ];
    }
}
