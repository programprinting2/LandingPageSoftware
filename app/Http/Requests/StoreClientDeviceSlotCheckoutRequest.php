<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientDeviceSlotCheckoutRequest extends FormRequest
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
            'application_slug' => ['required', 'string', 'max:120'],
            'additional_slots' => ['required', 'integer', 'min:1', 'max:99'],
            'referral_code' => ['nullable', 'string', 'max:32'],
        ];
    }
}
